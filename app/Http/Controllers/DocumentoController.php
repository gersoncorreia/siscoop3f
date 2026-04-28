<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Associado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentoController extends Controller
{
    public function store(Request $request, Associado $associado)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // max 10MB
        ]);

        $path = $request->file('arquivo')->store('documentos', 'public');

        $associado->documentos()->create([
            'titulo' => $request->titulo,
            'tipo' => $request->tipo,
            'arquivo_path' => $path,
        ]);

        return redirect()->back()->with('success', 'Documento anexado com sucesso.');
    }

    public function destroy(Associado $associado, Documento $documento)
    {
        if ($documento->arquivo_path && Storage::disk('public')->exists($documento->arquivo_path)) {
            Storage::disk('public')->delete($documento->arquivo_path);
        }

        $documento->delete();

        return redirect()->back()->with('success', 'Documento excluído com sucesso.');
    }

    public function download(Associado $associado, Documento $documento)
    {
        if (!Storage::disk('public')->exists($documento->arquivo_path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return Storage::disk('public')->download($documento->arquivo_path, $documento->titulo . '.' . pathinfo($documento->arquivo_path, PATHINFO_EXTENSION));
    }

    public function aprovar(Associado $associado, Documento $documento)
    {
        $documento->update([
            'status' => 'aprovado',
            'motivo_rejeicao' => null,
        ]);

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

    public function rejeitar(Request $request, Associado $associado, Documento $documento)
    {
        $request->validate([
            'motivo_rejeicao' => 'required|string|max:1000',
        ]);

        $documento->update([
            'status' => 'rejeitado',
            'motivo_rejeicao' => $request->motivo_rejeicao,
        ]);

        // Trigger Notification
        if ($associado->user) {
            $associado->user->notify(new \App\Notifications\AppDatabaseNotification(
                'Documento Rejeitado',
                "Seu documento ({$documento->titulo}) foi rejeitado pela análise técnica. Motivo: {$request->motivo_rejeicao}",
                route('cooperado.documentos'),
                'ExclamationTriangleIcon'
            ));
        }

        return redirect()->back()->with('success', 'Documento rejeitado. O cooperado foi notificado para providenciar a correção.');
    }
}
