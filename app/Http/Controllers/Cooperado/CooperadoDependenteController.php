<?php

namespace App\Http\Controllers\Cooperado;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;
use App\Contracts\AssociadoRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Cooperado\StoreCooperadoDependenteRequest;
use App\Http\Requests\Cooperado\UpdateCooperadoDependenteRequest;
use Inertia\Inertia;
use Inertia\Response;

class CooperadoDependenteController extends Controller
{
    protected CooperativeService $cooperativeService;
    protected AssociadoRepositoryInterface $associados;

    public function __construct(
        AssociadoRepositoryInterface $associados,
        CooperativeService $cooperativeService
    ) {
        $this->associados = $associados;
        $this->cooperativeService = $cooperativeService;
    }

    /**
     * Dependentes do cooperado
     */
    public function index(): Response
    {
        abort_if(auth()->user()->hasRole('Dependente'), 403, 'Acesso restrito ao titular.');
        
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

        $dependentes = $associado->dependentes()
            ->with(['documentos'])
            ->orderBy('nome')
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'nome' => $d->nome,
                'cpf' => $d->cpf ? substr($d->cpf, 0, 3) . '.***.***-' . substr($d->cpf, -2) : null,
                'raw_cpf' => $d->cpf,
                'rg' => $d->rg,
                'cartao_sus' => $d->cartao_sus,
                'data_nascimento' => $d->data_nascimento ? $d->data_nascimento->format('Y-m-d') : null,
                'data_nascimento_formatada' => $d->data_nascimento ? $d->data_nascimento->format('d/m/Y') : null,
                'parentesco' => $d->parentesco,
                'tipo_dependencia' => $d->tipo_dependencia,
                'autoriza_acesso' => $d->autoriza_acesso,
                'email' => $d->email,
                'status' => $d->status,
                'access_token' => $d->getMagicLinkToken(),
                'documentos' => $d->documentos->map(fn ($doc) => [
                    'id' => $doc->id,
                    'titulo' => $doc->titulo,
                    'tipo' => $doc->tipo,
                    'status' => $doc->status,
                    'motivo_rejeicao' => $doc->motivo_rejeicao,
                ]),
            ]);

        return Inertia::render('Cooperado/Dependentes', [
            'dependentes' => $dependentes,
        ]);
    }

    /**
     * Store a new dependent
     */
    public function store(StoreCooperadoDependenteRequest $request): RedirectResponse
    {
        abort_if(auth()->user()->hasRole('Dependente'), 403, 'Acesso restrito ao titular.');
        
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

        $validated = $request->validated();

        $this->cooperativeService->criarDependente(array_merge($validated, ['associado_id' => $associado->id]));

        return redirect()->back()->with('success', 'Dependente adicionado com sucesso.');
    }

    /**
     * Update an existing dependent
     */
    public function update(UpdateCooperadoDependenteRequest $request, $id): RedirectResponse
    {
        abort_if(auth()->user()->hasRole('Dependente'), 403, 'Acesso restrito ao titular.');
        
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());
        $dependente = $associado->dependentes()->findOrFail($id);

        $validated = $request->validated();

        $this->cooperativeService->atualizarDependente($dependente->id, $validated);

        return redirect()->back()->with('success', 'Dados do dependente atualizados.');
    }

    /**
     * Delete a dependent
     */
    public function destroy($id): RedirectResponse
    {
        abort_if(auth()->user()->hasRole('Dependente'), 403, 'Acesso restrito ao titular.');
        
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());
        $dependente = $associado->dependentes()->findOrFail($id);

        $this->cooperativeService->excluirDependente($dependente->id);

        return redirect()->back()->with('success', 'Dependente removido.');
    }
}
