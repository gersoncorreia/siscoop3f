<?php

namespace App\Http\Controllers;

use App\Models\CotaCapital;
use App\Models\Associado;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;

class CotaCapitalController extends Controller
{
    public function index()
    {
        Gate::authorize('financeiro.view');

        return Inertia::render('Financeiro/Capital/Index', [
            'cotas' => CotaCapital::with('associado')->latest()->paginate(15),
            'associados' => Associado::select('id', 'nome')->get()
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('financeiro.create');

        $validated = $request->validate([
            'associado_id' => 'required|exists:associados,id',
            'valor_quota' => 'required|numeric',
            'qtd_quotas' => 'required|integer',
            'saldo' => 'required|numeric',
            'tipo_movimento' => 'nullable|string',
        ]);

        CotaCapital::create($validated);

        return Redirect::back()->with('success', 'Cota de capital registrada!');
    }

    public function destroy(CotaCapital $cotaCapital)
    {
        Gate::authorize('financeiro.delete');

        $cotaCapital->delete();
        return Redirect::back()->with('success', 'Registro de capital excluído!');
    }
}
