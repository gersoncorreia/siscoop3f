<?php

namespace App\Http\Controllers\Cooperado;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;
use App\Contracts\AssociadoRepositoryInterface;
use App\Contracts\ReembolsoRepositoryInterface;
use App\Services\StorageService;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Cooperado\StoreCooperadoReembolsoRequest;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CooperadoReembolsoController extends Controller
{
    protected CooperativeService $cooperativeService;
    protected AssociadoRepositoryInterface $associados;
    protected ReembolsoRepositoryInterface $reembolsos;
    protected StorageService $storage;

    public function __construct(
        AssociadoRepositoryInterface $associados,
        ReembolsoRepositoryInterface $reembolsos,
        StorageService $storage,
        CooperativeService $cooperativeService
    ) {
        $this->associados = $associados;
        $this->reembolsos = $reembolsos;
        $this->storage = $storage;
        $this->cooperativeService = $cooperativeService;
    }

    /**
     * Reembolsos do cooperado
     */
    public function index(): Response
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

        $paginated = $this->reembolsos->getPaginatedForAssociado($associado, 15);
        
        $reembolsos = $paginated->through(fn ($r) => [
            'id' => $r->id,
            'valor_solicitado' => (float) $r->valor_solicitado,
            'descricao' => $r->descricao,
            'status' => $r->status,
            'motivo_recusa' => $r->motivo_recusa,
            'comprovante' => is_array($r->comprovantes) && count($r->comprovantes) > 0 ? $r->comprovantes[0] : null,
            'created_at' => $r->created_at->format('d/m/Y'),
        ]);

        return Inertia::render('Cooperado/Reembolsos', [
            'reembolsos' => $reembolsos,
        ]);
    }

    /**
     * Solicitar um novo reembolso
     */
    public function store(StoreCooperadoReembolsoRequest $request): RedirectResponse
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

        $validated = $request->validated();

        $path = $this->storage->store($request->file('arquivo'), 'reembolsos', 'local');

        $this->cooperativeService->criarReembolso([
            'associado_id' => $associado->id,
            'valor_solicitado' => $validated['valor_solicitado'],
            'descricao' => $validated['descricao'],
            'status' => 'pendente',
            'comprovantes' => [$path],
        ]);

        $admins = \App\Models\User::role(['Administrador Cooperativa', 'Presidente', 'Gerente'])->get();
        if ($admins->count() > 0) {
            \Illuminate\Support\Facades\Notification::send(
                $admins, 
                new \App\Notifications\AppDatabaseNotification(
                    'Novo Pedido de Reembolso',
                    "O cooperado {$associado->nome} enviou uma solicitação de reembolso no valor de R$ " . number_format($validated['valor_solicitado'], 2, ',', '.') . ".",
                    route('saude.reembolsos.index'),
                    'BanknotesIcon'
                )
            );
        }

        return redirect()->back()->with('success', 'Solicitação de reembolso enviada com sucesso para análise.');
    }

    /**
     * Download a specific reimbursement receipt
     */
    public function download($id): StreamedResponse
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());
        $reembolso = $this->reembolsos->findOrFail($id);

        if ($reembolso->associado_id !== $associado->id) {
            abort(403, 'Acesso não autorizado.');
        }

        if (empty($reembolso->comprovantes) || !$this->storage->exists($reembolso->comprovantes[0], 'local')) {
            abort(404, 'Comprovante não encontrado.');
        }

        $path = $reembolso->comprovantes[0];
        return $this->storage->download(
            $path,
            'comprovante_reembolso_' . $reembolso->id . '.' . pathinfo($path, PATHINFO_EXTENSION),
            'local'
        );
    }

    /**
     * Delete a rejected reimbursement request
     */
    public function destroy($id): RedirectResponse
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());
        $reembolso = $this->reembolsos->findOrFail($id);

        if ($reembolso->associado_id !== $associado->id) {
            abort(403, 'Acesso não autorizado.');
        }

        if ($reembolso->status !== 'rejeitado') {
            abort(403, 'Apenas reembolsos rejeitados podem ser excluídos pelo cooperado.');
        }

        if (!empty($reembolso->comprovantes)) {
            $this->storage->delete($reembolso->comprovantes[0], 'local');
        }

        $this->cooperativeService->excluirReembolso($reembolso->id);

        return redirect()->back()->with('success', 'Solicitação de reembolso excluída. Você já pode encaminhar uma nova.');
    }
}
