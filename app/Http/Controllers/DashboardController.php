<?php

namespace App\Http\Controllers;

use App\Models\Associado;
use App\Models\LancamentoFinanceiro;
use App\Models\ContaBancaria;
use App\Models\CotaCapital;
use App\Models\PlanoSaude;
use App\Models\Prestador;
use App\Models\Reembolso;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Redirect Cooperado to their dedicated portal
        if (auth()->user()->hasRole('Associado') && !auth()->user()->hasRole('Administrador Cooperativa')) {
            return redirect('/cooperado');
        }

        $now = Carbon::now();
        $mesAtual = $now->month;
        $anoAtual = $now->year;

        // KPIs principais
        $totalAssociados = Associado::count();
        $associadosAtivos = Associado::where('status', 'ativo')->count();

        $receitasMes = LancamentoFinanceiro::where('tipo', 'entrada')
            ->whereMonth('data_vencimento', $mesAtual)
            ->whereYear('data_vencimento', $anoAtual)
            ->sum('valor');

        $despesasMes = LancamentoFinanceiro::where('tipo', 'saida')
            ->whereMonth('data_vencimento', $mesAtual)
            ->whereYear('data_vencimento', $anoAtual)
            ->sum('valor');

        $saldoBancario = ContaBancaria::sum('saldo');
        $capitalSocial = CotaCapital::sum('saldo');

        // Saúde
        $totalPlanos = PlanoSaude::count();
        $totalPrestadores = Prestador::count();
        $reembolsosPendentes = Reembolso::where('status', 'pendente')->count();
        $valorReembolsosPendentes = Reembolso::where('status', 'pendente')->sum('valor_solicitado');

        // Gráfico: Receitas vs Despesas dos últimos 6 meses
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $mes = $date->month;
            $ano = $date->year;

            $receitas = LancamentoFinanceiro::where('tipo', 'entrada')
                ->whereMonth('data_vencimento', $mes)
                ->whereYear('data_vencimento', $ano)
                ->sum('valor');

            $despesas = LancamentoFinanceiro::where('tipo', 'saida')
                ->whereMonth('data_vencimento', $mes)
                ->whereYear('data_vencimento', $ano)
                ->sum('valor');

            $chartData[] = [
                'label' => $date->translatedFormat('M/y'),
                'receitas' => (float) $receitas,
                'despesas' => (float) $despesas,
            ];
        }

        // Últimos 5 lançamentos
        $ultimosLancamentos = LancamentoFinanceiro::with('associado')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'tipo' => $l->tipo,
                    'valor' => $l->valor,
                    'status' => $l->status,
                    'data_vencimento' => $l->data_vencimento,
                    'associado_nome' => $l->associado?->nome ?? 'N/A',
                ];
            });

        return Inertia::render('Dashboard', [
            'tenant' => tenant(),
            'isTenant' => true,
            'stats' => [
                'totalAssociados' => $totalAssociados,
                'associadosAtivos' => $associadosAtivos,
                'receitasMes' => (float) $receitasMes,
                'despesasMes' => (float) $despesasMes,
                'saldoBancario' => (float) $saldoBancario,
                'capitalSocial' => (float) $capitalSocial,
                'totalPlanos' => $totalPlanos,
                'totalPrestadores' => $totalPrestadores,
                'reembolsosPendentes' => $reembolsosPendentes,
                'valorReembolsosPendentes' => (float) $valorReembolsosPendentes,
            ],
            'chartData' => $chartData,
            'ultimosLancamentos' => $ultimosLancamentos,
        ]);
    }
}
