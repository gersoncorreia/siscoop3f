<?php

namespace App\Http\Controllers\Admin;
use App\Http\Requests\Admin\UpdateContaBancariaRequest;
use App\Http\Requests\Admin\StoreContaBancariaRequest;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;

use App\Models\ContaBancaria;
use App\Http\Requests\Financeiro\ContaBancariaRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;

class ContaBancariaController extends Controller
{
    protected CooperativeService $cooperativeService;

    public function __construct(CooperativeService $cooperativeService)
    {
        $this->cooperativeService = $cooperativeService;
    }

    public function index()
    {
        Gate::authorize('financeiro.view');

        return Inertia::render('Financeiro/Contas/Index', [
            'contas' => ContaBancaria::latest('created_at')->get()
        ]);
    }

    public function store(ContaBancariaRequest $request)
    {
        $this->cooperativeService->criarConta($request->validated());

        return Redirect::back()->with('success', 'Conta bancária criada com sucesso!');
    }

    public function update(ContaBancariaRequest $request, ContaBancaria $conta)
    {
        $this->cooperativeService->atualizarConta($conta->id, $request->validated());

        return Redirect::back()->with('success', 'Conta bancária atualizada!');
    }

    public function destroy(ContaBancaria $conta)
    {
        Gate::authorize('financeiro.delete');

        $this->cooperativeService->excluirConta($conta->id);
        return Redirect::back()->with('success', 'Conta excluída!');
    }
}
