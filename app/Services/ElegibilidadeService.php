<?php

namespace App\Services;

use App\Models\Associado;
use App\Models\Dependente;
use App\Models\Prestador;
use App\Models\PrestadorServico;
use App\Models\User;

class ElegibilidadeService
{
    /**
     * Validação atômica e completa de elegibilidade para solicitação e agendamento online.
     * Retorna array com:
     * - 'apto': bool
     * - 'motivo': string|null
     * - 'detalhes': array
     */
    public function verificarElegibilidade(User $user, Prestador $prestador, PrestadorServico $servico, ?int $dependenteId = null): array
    {
        // 1. Localizar o Associado Titular
        $associado = Associado::where('user_id', $user->id)->first();
        if (!$associado) {
            return [
                'apto' => false,
                'codigo_erro' => 'associado_nao_encontrado',
                'motivo' => 'Cadastro de cooperado titular não localizado para este usuário.',
                'detalhes' => [],
            ];
        }

        // 2. Validação do Paciente (Se for Titular ou Dependente)
        $beneficiarioNome = $associado->nome;
        if ($dependenteId) {
            $dependente = Dependente::where('id', $dependenteId)
                ->where('associado_id', $associado->id)
                ->first();

            if (!$dependente) {
                return [
                    'apto' => false,
                    'codigo_erro' => 'dependente_invalido',
                    'motivo' => 'Dependente informado não pertence ao cadastro do cooperado.',
                    'detalhes' => [],
                ];
            }

            if ($dependente->status !== 'ativo') {
                return [
                    'apto' => false,
                    'codigo_erro' => 'dependente_inativo',
                    'motivo' => "O dependente {$dependente->nome} encontra-se com o cadastro inativo ou pendente de documentos.",
                    'detalhes' => ['dependente_id' => $dependente->id],
                ];
            }

            $beneficiarioNome = $dependente->nome;
        }

        // 3. Validação do Titular (Status Cadastral Geral)
        if ($associado->status !== 'ativo') {
            $statusLabels = [
                'pendente' => 'em análise de proposta',
                'analise_documental' => 'em análise documental',
                'inativo' => 'inativo',
                'rejeitado' => 'não homologado',
            ];
            $statusFormatado = $statusLabels[$associado->status] ?? $associado->status;

            return [
                'apto' => false,
                'codigo_erro' => 'titular_inativo',
                'motivo' => "Seu cadastro societário encontra-se atualmente {$statusFormatado}. Entre em contato com a cooperativa.",
                'detalhes' => ['status_associado' => $associado->status],
            ];
        }

        // 4. Validação Financeira: Cota Capital em Atraso
        $cotaPendente = $associado->lancamentosFinanceiros()
            ->where('categoria', 'cota_capital')
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->first();

        if ($cotaPendente) {
            return [
                'apto' => false,
                'codigo_erro' => 'inadimplente_cota_capital',
                'motivo' => 'Consta parcela de Cota Capital vencida em aberto. Regularize sua contribuição para emissão imediata da guia.',
                'detalhes' => [
                    'vencimento' => $cotaPendente->data_vencimento,
                    'valor' => (float)$cotaPendente->valor,
                ],
            ];
        }

        // 5. Validação Financeira: Mensalidades com Atraso Crítico (> 10 dias)
        $mensalidadeAtrasada = $associado->lancamentosFinanceiros()
            ->where('categoria', 'mensalidade')
            ->whereIn('status', ['atrasado', 'pendente'])
            ->whereDate('data_vencimento', '<', now()->subDays(10)->toDateString())
            ->first();

        if ($mensalidadeAtrasada) {
            return [
                'apto' => false,
                'codigo_erro' => 'inadimplente_mensalidade',
                'motivo' => 'Consta mensalidade em atraso superior ao prazo de tolerância. Regularize o débito para prosseguir.',
                'detalhes' => [
                    'vencimento' => $mensalidadeAtrasada->data_vencimento,
                    'valor' => (float)$mensalidadeAtrasada->valor,
                ],
            ];
        }

        // 6. Validação do Prestador de Serviços
        if ($servico->prestador_id !== $prestador->id) {
            return [
                'apto' => false,
                'codigo_erro' => 'servico_prestador_divergente',
                'motivo' => 'O serviço selecionado não pertence a este prestador credenciado.',
                'detalhes' => [],
            ];
        }

        if (!$servico->ativo) {
            return [
                'apto' => false,
                'codigo_erro' => 'servico_inativo',
                'motivo' => 'Este procedimento está temporariamente indisponível no parceiro credenciado.',
                'detalhes' => [],
            ];
        }

        // 7. Resultado 100% Aprovado e Elegível
        return [
            'apto' => true,
            'codigo_erro' => null,
            'motivo' => 'Elegibilidade confirmada com sucesso! Procedimento pré-autorizado.',
            'detalhes' => [
                'associado_id' => $associado->id,
                'beneficiario_nome' => $beneficiarioNome,
                'tipo_beneficiario' => $dependenteId ? 'dependente' : 'titular',
                'prestador_nome' => $prestador->nome,
                'servico_nome' => $servico->nome,
                'valor_acordado' => (float)($servico->valor_acordado ?? 0),
            ],
        ];
    }
}
