<?php

namespace App\Http\Controllers\Cooperado;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;
use App\Contracts\AssociadoRepositoryInterface;
use App\Services\StorageService;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Cooperado\StoreCooperadoDocumentoRequest;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CooperadoDocumentoController extends Controller
{
    protected CooperativeService $cooperativeService;
    protected AssociadoRepositoryInterface $associados;
    protected StorageService $storage;

    public function __construct(
        AssociadoRepositoryInterface $associados,
        StorageService $storage,
        CooperativeService $cooperativeService
    ) {
        $this->associados = $associados;
        $this->storage = $storage;
        $this->cooperativeService = $cooperativeService;
    }

    /**
     * Documentos do cooperado
     */
    public function index(): Response
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

        $documentos = $associado->documentos()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'titulo' => $d->titulo,
                'tipo' => $d->tipo,
                'status' => $d->status,
                'motivo_rejeicao' => $d->motivo_rejeicao,
                'created_at' => $d->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Cooperado/Documentos', [
            'documentos' => $documentos,
        ]);
    }

    /**
     * Upload a new document (Cooperado)
     */
    public function store(StoreCooperadoDocumentoRequest $request): RedirectResponse
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

        $validated = $request->validated();

        if ($validated['dependente_id'] ?? null) {
            $associado->dependentes()->findOrFail($validated['dependente_id'] ?? null);
        }

        $path = $this->storage->store($request->file('arquivo'), 'documentos', 'local');

        $this->cooperativeService->criarDocumento($associado->id, $validated, $request->file('arquivo'));

        $admins = \App\Models\User::role(['Administrador Cooperativa', 'Presidente', 'Gerente'])->get();
        if ($admins->count() > 0) {
            \Illuminate\Support\Facades\Notification::send(
                $admins, 
                new \App\Notifications\AppDatabaseNotification(
                    'Novo Documento Enviado',
                    "O cooperado {$associado->nome} enviou um novo documento ({$validated['titulo']}) para análise.",
                    route('associados.index'),
                    'DocumentTextIcon'
                )
            );
        }

        return redirect()->back()->with('success', 'Documento enviado com sucesso para análise.');
    }

    /**
     * Download a specific document
     */
    public function download($id): StreamedResponse
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());
        $documento = $associado->documentos()->findOrFail($id);

        if (!$this->storage->exists($documento->arquivo_path, 'local')) {
            abort(404, 'Arquivo não encontrado.');
        }

        return $this->storage->download(
            $documento->arquivo_path, 
            $documento->titulo . '.' . pathinfo($documento->arquivo_path, PATHINFO_EXTENSION),
            'local'
        );
    }

    /**
     * Delete a rejected document
     */
    public function destroy($id): RedirectResponse
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());
        $documento = $associado->documentos()->findOrFail($id);

        if ($documento->status !== 'rejeitado') {
            abort(403, 'Apenas documentos rejeitados podem ser excluídos pelo cooperado.');
        }

        if ($documento->arquivo_path) {
            $this->storage->delete($documento->arquivo_path, 'local');
        }

        $this->cooperativeService->excluirDocumento($documento->id);

        return redirect()->back()->with('success', 'Documento excluído. Você já pode enviar um novo documento.');
    }
}
