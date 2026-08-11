<?php

namespace App\Http\Controllers\Admin;
use App\Http\Requests\Admin\UpdatePlanoSaudeRequest;
use App\Http\Requests\Admin\StorePlanoSaudeRequest;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;

use App\Models\PlanoSaude;
use App\Http\Requests\Saude\PlanoSaudeRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanoSaudeController extends Controller
{
    protected CooperativeService $cooperativeService;

    public function __construct(CooperativeService $cooperativeService)
    {
        $this->cooperativeService = $cooperativeService;
    }

    public function index()
    {
        return Inertia::render('Saude/Planos/Index', [
            'planos' => PlanoSaude::latest('created_at')->get()
        ]);
    }

    public function store(PlanoSaudeRequest $request)
    {
        $this->cooperativeService->criarPlano($request->validated());

        return redirect()->back()->with('success', 'Plano de saúde criado com sucesso!');
    }

    public function update(PlanoSaudeRequest $request, PlanoSaude $planoSaude)
    {
        $this->cooperativeService->atualizarPlano($planoSaude->id, $request->validated());

        return redirect()->back()->with('success', 'Plano de saúde atualizado com sucesso!');
    }

    public function destroy(PlanoSaude $planoSaude)
    {
        $this->cooperativeService->excluirPlano($planoSaude->id);

        return redirect()->back()->with('success', 'Plano de saúde excluído com sucesso!');
    }
}
