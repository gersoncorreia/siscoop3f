<?php

namespace App\Http\Controllers;

use App\Models\ContaBancaria;
use App\Http\Requests\Financeiro\ContaBancariaRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;

class ContaBancariaController extends Controller
{
    public function index()
    {
        Gate::authorize('financeiro.view');

        return Inertia::render('Financeiro/Contas/Index', [
            'contas' => ContaBancaria::latest('created_at')->get()
        ]);
    }

    public function store(ContaBancariaRequest $request)
    {
        ContaBancaria::create($request->validated());

        return Redirect::back()->with('success', 'Conta bancária criada com sucesso!');
    }

    public function update(ContaBancariaRequest $request, ContaBancaria $contaBancaria)
    {
        $contaBancaria->update($request->validated());

        return Redirect::back()->with('success', 'Conta bancária atualizada!');
    }

    public function destroy(ContaBancaria $contaBancaria)
    {
        Gate::authorize('financeiro.delete');

        $contaBancaria->delete();
        return Redirect::back()->with('success', 'Conta excluída!');
    }
}
