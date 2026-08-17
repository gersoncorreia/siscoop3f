<?php

namespace App\Services;

use App\Models\Associado;
use App\Models\Dependente;
use App\Services\FinanceiroService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PaymentFactory;

class AdesaoService
{
    /**
     * Process digital onboarding (Adesão Digital)
     */
    public function processarAdesao(array $dadosAdesao): Associado
    {
        return DB::transaction(function () use ($dadosAdesao) {

            // 1. Create Associado (Higienizar CPF para apenas dígitos)
            $cleanCpf = preg_replace('/\D/', '', $dadosAdesao['cpf']);

            $associado = Associado::create([
                'nome' => $dadosAdesao['nome'],
                'cpf' => $cleanCpf,
                'email' => mb_strtolower(trim($dadosAdesao['email'])),
                'matricula' => $dadosAdesao['matricula'] ?? null,
                'nascimento' => $dadosAdesao['nascimento'] ?? null,
                'status' => 'analise_documental',
                'data_adesao' => now(),
                'forma_pagamento_adesao' => $dadosAdesao['forma_pagamento_adesao'] ?? null,
                'autoriza_desconto_folha' => $dadosAdesao['autoriza_desconto_folha'] ?? false,
            ]);

            // 2. Add Telefones
            if (!empty($dadosAdesao['telefones'])) {
                foreach ($dadosAdesao['telefones'] as $idx => $tel) {
                    $associado->telefones()->create([
                        'numero' => $tel['numero'],
                        'tipo' => $tel['tipo'],
                        'principal' => $idx === 0,
                    ]);
                }
            }

            // 3. Add Enderecos
            if (!empty($dadosAdesao['enderecos'])) {
                foreach ($dadosAdesao['enderecos'] as $idx => $end) {
                    $associado->enderecos()->create(array_merge($end, [
                        'principal' => $idx === 0,
                    ]));
                }
            }

            // 4. Add Dependentes (com novos campos de tipo e acesso)
            if (!empty($dadosAdesao['dependentes'])) {
                foreach ($dadosAdesao['dependentes'] as $dep) {
                    $associado->dependentes()->create([
                        'nome' => $dep['nome'] ?? null,
                        'cpf' => $dep['cpf'] ?? null,
                        'parentesco' => $dep['parentesco'] ?? null,
                        'tipo_dependencia' => $dep['tipo_dependencia'] ?? 'legal',
                        'autoriza_acesso' => $dep['autoriza_acesso'] ?? false,
                        'email' => $dep['email'] ?? null,
                    ]);
                }
            }

            // 5. Setup Cota Capital inicial
            $associado->cotasCapital()->create([
                'meta_total' => 600.00,
                'valor_mensal' => 10.00,
                'valor_quota' => 10.00,
                'qtd_quotas' => 60,
                'saldo' => 0,
                'tipo_movimento' => 'integralizacao_inicial',
            ]);

            // 6. Log the activity for audit
            activity('adesao')
                ->performedOn($associado)
                ->log('Nova solicitação de adesão digital recebida');

            // 7. Notify the admins
            $admins = \App\Models\User::role(['Administrador Cooperativa', 'Administrador Fundação', 'Presidente', 'Gerente'])->get();
            if ($admins->count() > 0) {
                \Illuminate\Support\Facades\Notification::send(
                    $admins, 
                    new \App\Notifications\AppDatabaseNotification(
                        'Nova Solicitação de Adesão',
                        "Foi recebida uma nova solicitação de adesão de {$associado->nome} ({$associado->cpf}).",
                        route('associados.adesoes.index'),
                        'UserPlusIcon'
                    )
                );
            }

            // 8. Notify the client via E-mail
            try {
                \Illuminate\Support\Facades\Notification::route('mail', $associado->email)
                    ->notify(new \App\Notifications\AdesaoRecebidaNotification($associado->nome));
            } catch (\Exception $e) {
                Log::error("Erro ao enviar e-mail de adesão recebida para {$associado->email}: " . $e->getMessage());
            }

            return $associado;
        });
    }

    /**
     * Aprovar adesão pendente
     */
    public function aprovarAdesao(Associado $associado, ?string $formaPagamento = null, ?string $observacoes = null)
    {
        return DB::transaction(function () use ($associado, $formaPagamento, $observacoes) {
            $associado->update([
                'status' => 'ativo',
                'data_adesao' => now()
            ]);

            $temporaryPassword = str()->random(8);

            // 1. Create User Account for the Associate
            $user = \App\Models\User::create([
                'name' => $associado->nome,
                'email' => $associado->email,
                'password' => bcrypt($temporaryPassword),
                'email_verified_at' => now(),
                'has_temporary_password' => true,
            ]);

            // Assign Associate Role
            $user->assignRole('Associado');

            // Link User to Associate
            $associado->update(['user_id' => $user->id]);

            // 2. Setup initial billing (Taxa Titular + Taxa Uso Sistema + Dependentes + 1ª Cota)
            $this->gerarCobrancaInicial($associado, $formaPagamento, $observacoes);

            // 3. Create User Accounts for Dependentes with access authorization
            $this->criarUsuariosDependentes($associado);

            // 4. Send Notification to the Titular
            try {
                $user->notify(new \App\Notifications\AdesaoAprovadaNotification(
                    $associado->nome,
                    $associado->email,
                    $temporaryPassword
                ));
            } catch (\Exception $ne) {
                Log::error("Erro ao enviar notificação de aprovação: " . $ne->getMessage());
            }

            // 5. Log the approval
            activity('adesao')
                ->performedOn($associado)
                ->log('Adesão aprovada e conta de usuário criada');

            return $associado;
        });
    }

    /**
     * Cria contas de usuário para dependentes que receberam autorização de acesso.
     * O login é feito por e-mail para simplificar (mesmo padrão dos Prestadores).
     */
    protected function criarUsuariosDependentes(Associado $associado): void
    {
        $dependentes = $associado->dependentes()->where('autoriza_acesso', true)->whereNotNull('email')->get();

        foreach ($dependentes as $dependente) {
            // Evitar duplicação se já tiver user_id
            if ($dependente->user_id) {
                continue;
            }

            $temporaryPassword = str()->random(8);

            try {
                $userDep = \App\Models\User::create([
                    'name' => $dependente->nome,
                    'email' => $dependente->email,
                    'password' => bcrypt($temporaryPassword),
                    'email_verified_at' => now(),
                    'has_temporary_password' => true,
                ]);

                $userDep->assignRole('Dependente');

                // Link User to Dependente
                $dependente->update(['user_id' => $userDep->id]);

                // Send notification with credentials
                $userDep->notify(new \App\Notifications\DependenteCriadoNotification(
                    $dependente->nome,
                    $dependente->email,
                    $temporaryPassword,
                    $associado->nome
                ));
            } catch (\Exception $e) {
                Log::error("Erro ao criar usuário para dependente {$dependente->nome}: " . $e->getMessage());
            }
        }
    }

    /**
     * Gera a cobrança inicial englobando:
     * - Taxa Administrativa do Titular (R$ 100,00)
     * - Taxa de Uso do Sistema (R$ 10,00)
     * - Taxa de Dependentes (R$ 80,00 legais / R$ 100,00 não-legais cada)
     * - Primeira parcela da Cota Capital (R$ 10,00)
     *
     * Utiliza o Strategy de Gateway (PaymentFactory) para gerar a cobrança.
     */
    protected function gerarCobrancaInicial(Associado $associado, ?string $formaPagamento = null, ?string $observacoes = null): void
    {
        $taxaTitular = 100.00;
        $taxaUsoSistema = 10.00;
        $primeiraCota = 10.00;

        // Calcular taxas de dependentes de forma individualizada por tipo
        $dependentes = $associado->dependentes;
        $taxaDependentesTotal = 0;
        foreach ($dependentes as $dep) {
            $taxaDependentesTotal += $dep->getTaxaAdesao();
        }

        $valorTotal = $taxaTitular + $taxaUsoSistema + $taxaDependentesTotal + $primeiraCota;

        $statusInicial = $formaPagamento ? 'pago' : 'pendente';
        $dataPagamento = $formaPagamento ? now() : null;

        // Resolve gateway strategy
        $gateway = PaymentFactory::resolve();
        
        $gatewayData = [
            'associado_id' => $associado->id,
            'total' => $valorTotal,
            'descricao' => 'Adesão Cooperativa Siscoop3F',
        ];

        $response = $gateway->gerarCobranca($gatewayData);

        $gatewayId = $response['gateway_id'] ?? null;
        $gatewayName = get_class($gateway) === \App\Services\Gateways\InternalBillingGateway::class ? 'interno' : 'asaas';

        // Lançar Taxa Titular
        \App\Models\LancamentoFinanceiro::create([
            'associado_id' => $associado->id,
            'tipo' => 'receita',
            'categoria' => 'taxa_adm_titular',
            'valor' => $taxaTitular,
            'data_vencimento' => now()->addDays(3),
            'status' => $statusInicial,
            'forma_pagamento' => $formaPagamento,
            'data_pagamento' => $dataPagamento,
            'observacoes_pagamento' => $observacoes,
            'gateway' => $gatewayName,
            'gateway_id' => $gatewayId,
        ]);

        // Lançar Taxa de Uso do Sistema
        \App\Models\LancamentoFinanceiro::create([
            'associado_id' => $associado->id,
            'tipo' => 'receita',
            'categoria' => 'taxa_uso_sistema',
            'valor' => $taxaUsoSistema,
            'data_vencimento' => now()->addDays(3),
            'status' => $statusInicial,
            'forma_pagamento' => $formaPagamento,
            'data_pagamento' => $dataPagamento,
            'observacoes_pagamento' => $observacoes,
            'gateway' => $gatewayName,
            'gateway_id' => $gatewayId,
        ]);

        // Lançar Dependentes se houver (individual por tipo)
        if ($dependentes->count() > 0) {
            foreach ($dependentes as $dep) {
                $categoriaDep = $dep->tipo_dependencia === 'nao_legal'
                    ? 'taxa_adm_dependente_nao_legal'
                    : 'taxa_adm_dependente';

                \App\Models\LancamentoFinanceiro::create([
                    'associado_id' => $associado->id,
                    'tipo' => 'receita',
                    'categoria' => $categoriaDep,
                    'valor' => $dep->getTaxaAdesao(),
                    'data_vencimento' => now()->addDays(3),
                    'status' => $statusInicial,
                    'forma_pagamento' => $formaPagamento,
                    'data_pagamento' => $dataPagamento,
                    'observacoes_pagamento' => $observacoes,
                    'gateway' => $gatewayName,
                    'gateway_id' => $gatewayId,
                ]);
            }
        }

        // Lançar Primeira Parcela da Cota Capital
        \App\Models\LancamentoFinanceiro::create([
            'associado_id' => $associado->id,
            'tipo' => 'receita',
            'categoria' => 'cota_capital',
            'valor' => $primeiraCota,
            'data_vencimento' => now()->addDays(3),
            'status' => $statusInicial,
            'forma_pagamento' => $formaPagamento,
            'data_pagamento' => $dataPagamento,
            'observacoes_pagamento' => $observacoes,
            'gateway' => $gatewayName,
            'gateway_id' => $gatewayId,
        ]);
    }
}
