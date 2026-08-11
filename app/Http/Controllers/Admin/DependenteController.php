<?php

namespace App\Http\Controllers\Admin;
use App\Http\Requests\Admin\UpdateDependenteRequest;
use App\Http\Requests\Admin\StoreDependenteRequest;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;

use App\Models\Dependente;
use App\Models\Associado;
use App\Http\Requests\Dependente\DependenteRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;

class DependenteController extends Controller
{
    protected CooperativeService $cooperativeService;

    public function __construct(CooperativeService $cooperativeService)
    {
        $this->cooperativeService = $cooperativeService;
    }

    public function index()
    {
        Gate::authorize('associados.view');

        return Inertia::render('Associados/Dependentes/Index', [
            'dependentes' => Dependente::with('associado')->latest()->paginate(10),
            'associados' => Associado::select('id', 'nome')->get()
        ]);
    }

    public function store(DependenteRequest $request)
    {
        $this->cooperativeService->criarDependente($request->validated());

        return Redirect::back()->with('success', 'Dependente criado com sucesso!');
    }

    public function update(DependenteRequest $request, Dependente $dependente)
    {
        $this->cooperativeService->atualizarDependente($dependente->id, $request->validated());

        return Redirect::back()->with('success', 'Dependente atualizado com sucesso!');
    }

    public function destroy(Dependente $dependente)
    {
        Gate::authorize('associados.delete');

        $this->cooperativeService->excluirDependente($dependente->id);

        return Redirect::back()->with('success', 'Dependente excluído com sucesso!');
    }
}
