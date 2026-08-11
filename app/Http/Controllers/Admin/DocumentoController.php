<?php

namespace App\Http\Controllers\Admin;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;

use App\Models\Documento;
use App\Models\Associado;
use App\Http\Requests\Admin\StoreDocumentoAdminRequest;
use App\Http\Requests\Admin\RejeitarDocumentoRequest;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentoController extends Controller
{
    protected CooperativeService $cooperativeService;

    public function __construct(CooperativeService $cooperativeService)
    {
        $this->cooperativeService = $cooperativeService;
    }

    public function store(StoreDocumentoAdminRequest $request, Associado $associado)
    {
        // Armazenar no disco privado 'local'
        $path = $request->file('arquivo')->store('documentos', 'local');

        $this->cooperativeService->criarDocumento($associado->id, $request->validated(), $request->file('arquivo'));

        return redirect()->back()->with('success', 'Documento anexado com sucesso.');
    }

    public function destroy(Associado $associado, Documento $documento)
    {
        // Verificar se o documento pertence ao associado
        if ($documento->associado_id !== $associado->id) {
            abort(404);
        }

        $this->authorize('delete', $documento);

        if ($documento->arquivo_path && Storage::disk('local')->exists($documento->arquivo_path)) {
            
        }

        $this->cooperativeService->excluirDocumento($documento->id);

        return redirect()->back()->with('success', 'Documento excluído com sucesso.');
    }

    public function download(Associado $associado, Documento $documento)
    {
        // Verificar se o documento pertence ao associado
        if ($documento->associado_id !== $associado->id) {
            abort(404);
        }

        $this->authorize('view', $documento);

        if (!Storage::disk('local')->exists($documento->arquivo_path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return Storage::disk('local')->download($documento->arquivo_path, $documento->titulo . '.' . pathinfo($documento->arquivo_path, PATHINFO_EXTENSION));
    }

    public function aprovar(Associado $associado, Documento $documento)
    {
        $this->authorize('update', $documento);

        $this->cooperativeService->aprovarDocumento($documento->id);

        if ($associado->user) {
            $associado->user->notify(new \App\Notifications\AppDatabaseNotification(
                'Documento Aprovado',
                "Seu documento ({$documento->titulo}) foi avaliado e aprovado com sucesso.",
                route('cooperado.documentos'),
                'CheckBadgeIcon'
            ));
        }

        return redirect()->back()->with('success', 'Documento aprovado com sucesso.');
    }

    public function rejeitar(RejeitarDocumentoRequest $request, Associado $associado, Documento $documento)
    {
        $this->authorize('update', $documento);

        $validated = $request->validated();

        $this->cooperativeService->rejeitarDocumento($documento->id, $validated['motivo_rejeicao']);

        // Trigger Notification
        if ($associado->user) {
            $associado->user->notify(new \App\Notifications\AppDatabaseNotification(
                'Documento Rejeitado',
                "Seu documento ({$documento->titulo}) foi rejeitado pela análise técnica. Motivo: {$validated['motivo_rejeicao']}",
                route('cooperado.documentos'),
                'ExclamationTriangleIcon'
            ));
        }

        return redirect()->back()->with('success', 'Documento rejeitado. O cooperado foi notificado para providenciar a correção.');
    }
}
