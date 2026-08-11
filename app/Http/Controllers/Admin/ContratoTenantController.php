<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContratoEntidade;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ContratoTenantController extends Controller
{
    public function index()
    {
        $currentTenantId = tenant('id');

        // Get contracts related to this tenant
        $contratos = ContratoEntidade::with(['contratante', 'prestador'])
            ->where(function ($query) use ($currentTenantId) {
                $query->where('tenant_contratante_id', $currentTenantId)
                      ->orWhere('tenant_prestador_id', $currentTenantId);
            })
            ->get();

        // Get other tenants to request new contract
        $outrasEntidades = Tenant::where('id', '!=', $currentTenantId)->get();

        return Inertia::render('Configuracoes/Contratos', [
            'contratos' => $contratos,
            'outrasEntidades' => $outrasEntidades,
            'currentTenantId' => $currentTenantId,
        ]);
    }

    public function solicitar(Request $request)
    {
        $validated = $request->validate([
            'partner_tenant_id' => ['required', \Illuminate\Validation\Rule::exists('central.tenants', 'id')],
            'minha_funcao' => 'required|in:contratante,prestador',
            'descricao' => 'nullable|string',
            'arquivos' => 'nullable|array',
            'arquivos.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $currentTenantId = tenant('id');

        if ($validated['minha_funcao'] === 'contratante') {
            $contratanteId = $currentTenantId;
            $prestadorId = $validated['partner_tenant_id'];
        } else {
            $contratanteId = $validated['partner_tenant_id'];
            $prestadorId = $currentTenantId;
        }

        $arquivosData = [];
        if ($request->hasFile('arquivos')) {
            foreach ($request->file('arquivos') as $file) {
                if ($file && $file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $size = $file->getSize();
                    $path = $file->store('contratos', 'public');
                    $arquivosData[] = [
                        'nome_original' => $originalName,
                        'path' => $path,
                        'tamanho' => $size,
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }
        }

        ContratoEntidade::create([
            'tenant_contratante_id' => $contratanteId,
            'tenant_prestador_id' => $prestadorId,
            'status' => 'pendente',
            'data_inicio' => now()->toDateString(),
            'descricao' => $validated['descricao'] ?? 'Solicitado via Painel do Tenant.',
            'arquivos' => $arquivosData,
        ]);

        return Redirect::route('configuracoes.contratos')->with('success', 'Solicitação de contrato com anexos enviada para aprovação do master admin!');
    }

    public function downloadArquivo($id, $index)
    {
        $contrato = ContratoEntidade::findOrFail($id);
        $currentTenantId = tenant('id');

        // Authorization check: User tenant must be contratante or prestador
        if ($contrato->tenant_contratante_id !== $currentTenantId && $contrato->tenant_prestador_id !== $currentTenantId) {
            abort(403, 'Ação não autorizada.');
        }

        $arquivos = $contrato->arquivos ?? [];
        if (!isset($arquivos[$index])) {
            abort(404, 'Arquivo não encontrado.');
        }

        $fileData = $arquivos[$index];
        $filePath = storage_path('app/public/' . $fileData['path']);

        if (!file_exists($filePath)) {
            abort(404, 'Arquivo físico não encontrado no servidor.');
        }

        return response()->download($filePath, $fileData['nome_original']);
    }

    public function cancelar($id)
    {
        $contrato = ContratoEntidade::findOrFail($id);

        // Verify authorization
        $currentTenantId = tenant('id');
        if ($contrato->tenant_contratante_id !== $currentTenantId && $contrato->tenant_prestador_id !== $currentTenantId) {
            abort(403, 'Ação não autorizada.');
        }

        if ($contrato->status !== 'pendente') {
            return Redirect::route('configuracoes.contratos')->with('error', 'Apenas solicitações pendentes podem ser canceladas.');
        }

        $contrato->delete();

        return Redirect::route('configuracoes.contratos')->with('success', 'Solicitação de contrato cancelada.');
    }
}
