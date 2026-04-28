<?php

namespace App\Http\Controllers;

use App\Models\LancamentoFinanceiro;
use App\Models\ContaBancaria;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class FinanceiroController extends Controller
{
    public function index()
    {
        Gate::authorize('financeiro.view');

        $hoje = Carbon::now();

        $receitas = LancamentoFinanceiro::where('tipo', 'receita')
            ->whereMonth('data_vencimento', $hoje->month)
            ->whereYear('data_vencimento', $hoje->year)
            ->sum('valor');

        $despesas = LancamentoFinanceiro::where('tipo', 'despesa')
            ->whereMonth('data_vencimento', $hoje->month)
            ->whereYear('data_vencimento', $hoje->year)
            ->sum('valor');

        return Inertia::render('Financeiro/Dashboard', [
            'kpis' => [
                'saldoTotal' => ContaBancaria::sum('saldo'),
                'receitasMes' => $receitas,
                'despesasMes' => $despesas,
            ]
        ]);
    }

    public function conciliacao()
    {
        Gate::authorize('financeiro.update');

        $pendentes = LancamentoFinanceiro::with('associado')
            ->where('status', 'pendente')
            ->orderBy('data_vencimento', 'asc')
            ->get();

        return Inertia::render('Financeiro/Conciliacao/Index', [
            'lancamentos' => $pendentes
        ]);
    }

    public function conciliar(Request $request, LancamentoFinanceiro $lancamento)
    {
        Gate::authorize('financeiro.update');

        $lancamento->update([
            'status' => 'pago',
            // In a real scenario, we would save the actual payment date and perhaps link to a bank account transaction
        ]);

        return redirect()->back()->with('success', 'Lançamento conciliado com sucesso.');
    }
}
