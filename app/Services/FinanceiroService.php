<?php

namespace App\Services;

use App\Models\Associado;
use App\Models\LancamentoFinanceiro;
use App\Models\Reembolso;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceiroService
{
    /**
     * Start initial billing for a new active member
     */
    public function gerarCobrancaInicial(Associado $associado)
    {
        // 1. Mensalidade Inicial
        LancamentoFinanceiro::create([
            'associado_id' => $associado->id,
            'tipo' => 'receita', // mensalidade
            'valor' => 150.00, // configurable Plan Value
            'data_vencimento' => Carbon::now()->addDays(5),
            'status' => 'pendente'
        ]);

        // 2. Integralização Cota
        LancamentoFinanceiro::create([
            'associado_id' => $associado->id,
            'tipo' => 'receita', // cota
            'valor' => $associado->cotasCapital()->first()?->valor_quota ?? 100.00,
            'data_vencimento' => Carbon::now()->addDays(5),
            'status' => 'pendente'
        ]);

    // Em um cenário real, integraria com gateway / geraria boleto API.
    }

    /**
     * Batch job to Generate Monthly Billings (Mensalidades)
     */
    public function gerarLoteMensalidades()
    {
        $associados = Associado::where('status', 'ativo')->get();
    // Disparar Laravel Jobs no Horizon:
    // foreach($associados as $associado) {
    //     dispatch(new GerarBoletoMensalJob($associado));
    // }
    }

    /**
     * Reembolso (Health specific Module example)
     */
    public function solicitarReembolso(Associado $associado, $valor, array $arquivos)
    {
        return DB::transaction(function () use ($associado, $valor, $arquivos) {
            $reembolso = Reembolso::create([
                'associado_id' => $associado->id,
                'valor_solicitado' => $valor,
                'status' => 'analise_auditoria', // Auditoria Médica 
                'comprovantes' => json_encode($arquivos),
            ]);

            return $reembolso;
        });
    }

    /**
     * Aprovação do reembolso (triggers coparticipacao calculation)
     */
    public function aprovarReembolso(Reembolso $reembolso, $percentualCoparticipacao = 30)
    {
        $reembolso->update(['status' => 'aprovado']);

        // Calcula coparticipacao se aplicável
        if ($percentualCoparticipacao > 0) {
            $reembolso->coparticipacoes()->create([
                'valor_total' => $reembolso->valor_solicitado,
                'percentual' => $percentualCoparticipacao,
                'status' => 'pendente'
            ]);

            // Gera conta a receber (Copart) do Associado
            LancamentoFinanceiro::create([
                'associado_id' => $reembolso->associado_id,
                'tipo' => 'receita', // coparticipacao
                'valor' => ($reembolso->valor_solicitado * $percentualCoparticipacao) / 100,
                'data_vencimento' => Carbon::now()->endOfMonth()->addDays(10), // Vence mês seguinte
                'status' => 'pendente'
            ]);
        }

        // Gera conta a pagar (Reembolso ao Associado)
        LancamentoFinanceiro::create([
            'associado_id' => $reembolso->associado_id,
            'tipo' => 'despesa', // reembolso
            'valor' => $reembolso->valor_solicitado,
            'data_vencimento' => Carbon::now()->addDays(15),
            'status' => 'pendente' // Aguardando tesouraria pagar e conciliar
        ]);
    }
}
