<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\LancamentoFinanceiro;
use App\Models\Associado;
use App\Http\Requests\Financeiro\LancamentoFinanceiroRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;

class LancamentoFinanceiroController extends Controller
{
    public function index()
    {
        Gate::authorize('financeiro.view');

        return Inertia::render('Financeiro/Lancamentos/Index', [
            'lancamentos' => LancamentoFinanceiro::with('associado')->latest()->paginate(15),
            'associados' => Associado::select('id', 'nome')->get()
        ]);
    }

    public function store(LancamentoFinanceiroRequest $request)
    {
        LancamentoFinanceiro::create($request->validated());

        return Redirect::back()->with('success', 'Lançamento registrado com sucesso!');
    }

    public function update(LancamentoFinanceiroRequest $request, LancamentoFinanceiro $lancamento)
    {
        $lancamento->update($request->validated());

        return Redirect::back()->with('success', 'Lançamento atualizado!');
    }

    public function destroy(LancamentoFinanceiro $lancamento)
    {
        Gate::authorize('financeiro.delete');
        $lancamento->delete();
        return Redirect::back()->with('success', 'Lançamento excluído!');
    }
}
