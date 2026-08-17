<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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

        $validated = $request->validate([
            'forma_pagamento' => 'nullable|string|in:dinheiro,cartao_debito,cartao_credito,pix_manual,transferencia,asaas_pix,asaas_boleto',
            'data_pagamento' => 'nullable|date',
            'observacoes_pagamento' => 'nullable|string|max:500',
            'comprovante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = [
            'status' => 'pago',
            'forma_pagamento' => $validated['forma_pagamento'] ?? 'dinheiro',
            'data_pagamento' => $validated['data_pagamento'] ?? now(),
            'observacoes_pagamento' => $validated['observacoes_pagamento'] ?? null,
        ];

        if ($request->hasFile('comprovante')) {
            $path = $request->file('comprovante')->store('comprovantes_pagamento', 'public');
            $data['comprovante_path'] = \Illuminate\Support\Facades\Storage::url($path);
        }

        $lancamento->update($data);

        return redirect()->back()->with('success', 'Lançamento marcado como pago com sucesso!');
    }
}
