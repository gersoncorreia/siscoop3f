<?php

namespace App\Services;

use App\Models\Associado;
use App\Services\FinanceiroService;
use Illuminate\Support\Facades\DB;

class AdesaoService
{
    /**
     * Process digital onboarding (Adesão Digital)
     */
    public function processarAdesao(array $dadosAdesao): Associado
    {
        return DB::transaction(function () use ($dadosAdesao) {

            // 1. Create Associado
            $associado = Associado::create([
                'nome' => $dadosAdesao['nome'],
                'cpf' => $dadosAdesao['cpf'],
                'email' => $dadosAdesao['email'], // Added email to fillable previously
                'matricula' => $dadosAdesao['matricula'] ?? null,
                'nascimento' => $dadosAdesao['nascimento'] ?? null,
                'status' => 'analise_documental',
                'data_adesao' => now(),
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

            // 4. Add Dependentes (if any)
            if (!empty($dadosAdesao['dependentes'])) {
                foreach ($dadosAdesao['dependentes'] as $dep) {
                    $associado->dependentes()->create($dep);
                }
            }

            // 3. Setup Cota Capital inicial
            $associado->cotasCapital()->create([
                'valor_quota' => 100.00, // Config parameter per tenant 
                'qtd_quotas' => $dadosAdesao['qtd_quotas'] ?? 1,
                'saldo' => 0, // Until paid
                'tipo_movimento' => 'integralizacao_inicial',
            ]);

            // 4. Log the activity for audit
            activity('adesao')
                ->performedOn($associado)
                ->log('Nova solicitação de adesão digital recebida');

            // 5. Notify the admins
            $admins = \App\Models\User::role(['Administrador Cooperativa', 'Presidente', 'Gerente'])->get();
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

            return $associado;
        });
    }

    /**
     * Aprovar adesão pendente
     */
    public function aprovarAdesao(Associado $associado)
    {
        return DB::transaction(function () use ($associado) {
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

            // Assign Associate Role (assuming 'Associado' role exists)
            $user->assignRole('Associado');

            // Link User to Associate
            $associado->update(['user_id' => $user->id]);

            // 2. Setup initial billing (Removido automatizado)
            // Cota capital e mensalidade inicial devem ser solicitadas pela cooperativa ou ativadas no 1° acesso do cooperado.
            // $financeiroService = app(FinanceiroService::class);
            // $financeiroService->gerarCobrancaInicial($associado);

            // 3. Send Notification
            try {
                $user->notify(new \App\Notifications\AdesaoAprovadaNotification(
                    $associado->nome,
                    $associado->cpf,
                    $temporaryPassword
                ));
            } catch (\Exception $ne) {
                \Illuminate\Support\Facades\Log::error("Erro ao enviar notificação de aprovação: " . $ne->getMessage());
            }

            // 4. Log the approval
            activity('adesao')
                ->performedOn($associado)
                ->log('Adesão aprovada e conta de usuário criada');

            return $associado;
        });
    }
}
