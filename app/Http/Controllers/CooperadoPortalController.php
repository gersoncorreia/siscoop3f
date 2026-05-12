<?php

namespace App\Http\Controllers;

use App\Models\Associado;
use App\Models\Reembolso;
use App\Models\Prestador;
use App\Models\SolicitacaoServico;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CooperadoPortalController extends Controller
{
    /**
     * Get the Associado record for the authenticated user.
     * Aborts with 403 if user has no linked associado.
     */
    protected function getAssociado(): Associado
    {
        $associado = auth()->user()->associado;

        if (!$associado) {
            abort(403, 'Nenhum cadastro de associado vinculado a este usuário.');
        }

        return $associado;
    }

    /**
     * Dashboard principal do cooperado
     */
    public function dashboard()
    {
        $associado = $this->getAssociado();
        $associado->load(['dependentes', 'cotasCapital', 'lancamentosFinanceiros', 'reembolsos']);

        $saldoCotaCapital = $associado->cotasCapital->sum('saldo');
        $mensalidadesPendentes = $associado->lancamentosFinanceiros
            ->where('status', 'pendente')
            ->count();
        $totalDependentes = $associado->dependentes->count();
        $reembolsosEmAnalise = $associado->reembolsos
            ->where('status', 'pendente')
            ->count();

        // Últimas 5 atividades (lançamentos recentes)
        $atividadesRecentes = $associado->lancamentosFinanceiros()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'tipo' => $l->tipo,
                'valor' => (float) $l->valor,
                'status' => $l->status,
                'data_vencimento' => $l->data_vencimento,
                'created_at' => $l->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Cooperado/Dashboard', [
            'associado' => [
                'id' => $associado->id,
                'nome' => $associado->nome,
                'status' => $associado->status,
                'data_adesao' => $associado->data_adesao?->format('d/m/Y'),
            ],
            'stats' => [
                'saldoCotaCapital' => (float) $saldoCotaCapital,
                'mensalidadesPendentes' => $mensalidadesPendentes,
                'totalDependentes' => $totalDependentes,
                'reembolsosEmAnalise' => $reembolsosEmAnalise,
            ],
            'atividadesRecentes' => $atividadesRecentes,
        ]);
    }

    /**
     * Extrato financeiro pessoal
     */
    public function financeiro(Request $request)
    {
        $associado = $this->getAssociado();

        $query = $associado->lancamentosFinanceiros()->orderBy('data_vencimento', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $lancamentos = $query->paginate(15)->through(fn ($l) => [
            'id' => $l->id,
            'tipo' => $l->tipo,
            'valor' => (float) $l->valor,
            'status' => $l->status,
            'data_vencimento' => $l->data_vencimento,
            'created_at' => $l->created_at->format('d/m/Y'),
        ]);

        return Inertia::render('Cooperado/Financeiro', [
            'lancamentos' => $lancamentos,
            'filtroStatus' => $request->status,
        ]);
    }

    /**
     * Cotas de capital do cooperado
     */
    public function cotaCapital()
    {
        $associado = $this->getAssociado();

        $cotas = $associado->cotasCapital()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'valor_quota' => (float) $c->valor_quota,
                'qtd_quotas' => $c->qtd_quotas,
                'saldo' => (float) $c->saldo,
                'tipo_movimento' => $c->tipo_movimento,
                'created_at' => $c->created_at->format('d/m/Y'),
            ]);

        $saldoTotal = $associado->cotasCapital->sum('saldo');

        return Inertia::render('Cooperado/Cotas', [
            'cotas' => $cotas,
            'saldoTotal' => (float) $saldoTotal,
        ]);
    }

    /**
     * Reembolsos do cooperado
     */
    public function reembolsos()
    {
        $associado = $this->getAssociado();

        $reembolsos = $associado->reembolsos()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($r) => [
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
    public function solicitarReembolso(\Illuminate\Http\Request $request)
    {
        $associado = $this->getAssociado();

        $validated = $request->validate([
            'valor_solicitado' => 'required|numeric|min:0.01',
            'descricao' => 'required|string|max:1000',
            'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // max 10MB
        ]);

        $path = $request->file('arquivo')->store('reembolsos', 'local');

        $associado->reembolsos()->create([
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

    public function downloadComprovanteReembolso($id)
    {
        $associado = $this->getAssociado();
        $reembolso = $associado->reembolsos()->findOrFail($id);

        if (empty($reembolso->comprovantes) || !\Illuminate\Support\Facades\Storage::disk('local')->exists($reembolso->comprovantes[0])) {
            abort(404, 'Comprovante não encontrado.');
        }

        $path = $reembolso->comprovantes[0];
        return \Illuminate\Support\Facades\Storage::disk('local')->download(
            $path,
            'comprovante_reembolso_' . $reembolso->id . '.' . pathinfo($path, PATHINFO_EXTENSION)
        );
    }

    public function deleteReembolso($id)
    {
        $associado = $this->getAssociado();
        $reembolso = $associado->reembolsos()->findOrFail($id);

        if ($reembolso->status !== 'rejeitado') {
            abort(403, 'Apenas reembolsos rejeitados podem ser excluídos pelo cooperado.');
        }

        if (!empty($reembolso->comprovantes) && \Illuminate\Support\Facades\Storage::disk('local')->exists($reembolso->comprovantes[0])) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($reembolso->comprovantes[0]);
        }

        $reembolso->delete();

        return redirect()->back()->with('success', 'Solicitação de reembolso excluída. Você já pode encaminhar uma nova.');
    }

    /**
     * Dependentes do cooperado
     */
    public function dependentes()
    {
        $associado = $this->getAssociado();

        $dependentes = $associado->dependentes()
            ->with(['documentos'])
            ->orderBy('nome')
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'nome' => $d->nome,
                'cpf' => $d->cpf ? substr($d->cpf, 0, 3) . '.***.***-' . substr($d->cpf, -2) : null,
                'data_nascimento' => $d->data_nascimento,
                'parentesco' => $d->parentesco,
                'status' => $d->status,
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
    public function storeDependente(Request $request)
    {
        $associado = $this->getAssociado();

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'nullable|string|max:14',
            'data_nascimento' => 'nullable|date',
            'parentesco' => 'required|string|max:100',
        ]);

        $associado->dependentes()->create($validated);

        return redirect()->back()->with('success', 'Dependente adicionado com sucesso.');
    }

    /**
     * Update an existing dependent
     */
    public function updateDependente(Request $request, $id)
    {
        $associado = $this->getAssociado();
        $dependente = $associado->dependentes()->findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'nullable|string|max:14',
            'data_nascimento' => 'nullable|date',
            'parentesco' => 'required|string|max:100',
        ]);

        $dependente->update($validated);

        return redirect()->back()->with('success', 'Dados do dependente atualizados.');
    }

    /**
     * Delete a dependent
     */
    public function deleteDependente($id)
    {
        $associado = $this->getAssociado();
        $dependente = $associado->dependentes()->findOrFail($id);

        // Optional: restriction check if they can be deleted
        $dependente->delete();

        return redirect()->back()->with('success', 'Dependente removido.');
    }

    /**
     * Documentos do cooperado
     */
    public function documentos()
    {
        $associado = $this->getAssociado();

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
    public function uploadDocumento(\Illuminate\Http\Request $request)
    {
        $associado = $this->getAssociado();

        $request->validate([
            'titulo' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // max 10MB
            'dependente_id' => 'nullable|exists:dependentes,id',
        ]);

        // If dependente_id is provided, verify it belongs to this associado
        if ($request->dependente_id) {
            $associado->dependentes()->findOrFail($request->dependente_id);
        }

        $path = $request->file('arquivo')->store('documentos', 'local');

        $associado->documentos()->create([
            'titulo' => $request->titulo,
            'tipo' => $request->tipo,
            'arquivo_path' => $path,
            'status' => 'pendente',
            'dependente_id' => $request->dependente_id,
        ]);

        $admins = \App\Models\User::role(['Administrador Cooperativa', 'Presidente', 'Gerente'])->get();
        if ($admins->count() > 0) {
            \Illuminate\Support\Facades\Notification::send(
                $admins, 
                new \App\Notifications\AppDatabaseNotification(
                    'Novo Documento Enviado',
                    "O cooperado {$associado->nome} enviou um novo documento ({$request->titulo}) para análise.",
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
    public function downloadDocumento($id)
    {
        $associado = $this->getAssociado();
        $documento = $associado->documentos()->findOrFail($id);

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($documento->arquivo_path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->download(
            $documento->arquivo_path, 
            $documento->titulo . '.' . pathinfo($documento->arquivo_path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Delete a rejected document
     */
    public function deleteDocumento($id)
    {
        $associado = $this->getAssociado();
        $documento = $associado->documentos()->findOrFail($id);

        if ($documento->status !== 'rejeitado') {
            abort(403, 'Apenas documentos rejeitados podem ser excluídos pelo cooperado.');
        }

        if ($documento->arquivo_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($documento->arquivo_path)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($documento->arquivo_path);
        }

        $documento->delete();

        return redirect()->back()->with('success', 'Documento excluído. Você já pode enviar um novo documento.');
    }

    /**
     * Lista de Prestadores da Rede Credenciada
     */
    public function prestadores()
    {
        $associado = $this->getAssociado();

        $prestadores = Prestador::orderBy('nome')->get()->map(fn($p) => [
            'id' => $p->id,
            'nome' => $p->nome,
            'tipo' => $p->tipo,
            'endereco' => $p->endereco,
            'especialidades' => is_array($p->especialidades) ? implode(', ', $p->especialidades) : $p->especialidades,
        ]);

        return Inertia::render('Cooperado/Prestadores', [
            'prestadores' => $prestadores,
        ]);
    }

    /**
     * Detalhes de um Prestador + Solicitações do Cooperado naquele parceiro
     */
    public function prestadorDetalhe($id)
    {
        $associado = $this->getAssociado();
        $prestador = Prestador::findOrFail($id);

        $prestadorData = [
            'id' => $prestador->id,
            'nome' => $prestador->nome,
            'tipo' => $prestador->tipo,
            'endereco' => $prestador->endereco,
            'especialidades' => is_array($prestador->especialidades) ? $prestador->especialidades : [],
        ];

        // Serviços/Produtos oferecidos por este prestador
        $servicos = $prestador->servicos()
            ->where('ativo', true)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'nome' => $s->nome,
                'descricao' => $s->descricao,
                'valor_acordado' => (float) ($s->valor_acordado ?? 0),
            ]);

        // Solicitações do cooperado neste prestador
        $solicitacoes = SolicitacaoServico::with('servico')
            ->where('associado_id', $associado->id)
            ->where('prestador_id', $prestador->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'servico_produto' => $s->servico->nome ?? 'Outros',
                'valor' => (float) ($s->valor_cobrado ?? 0),
                'observacoes' => $s->observacoes,
                'status' => $s->status,
                'motivo_recusa' => $s->motivo_recusa,
                'data' => $s->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Cooperado/PrestadorDetalhe', [
            'prestador' => $prestadorData,
            'servicos' => $servicos,
            'solicitacoes' => $solicitacoes,
        ]);
    }

    /**
     * Exibe as solicitações (autorizações) feitas para este cooperado
     */
    public function minhasSolicitacoes()
    {
        $associado = $this->getAssociado();

        $solicitacoes = SolicitacaoServico::with(['prestador', 'servico'])
            ->where('associado_id', $associado->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'prestador' => $s->prestador->nome ?? 'Desconhecido',
                'servico_produto' => $s->servico->nome ?? 'Outros',
                'observacoes' => $s->observacoes,
                'status' => $s->status,
                'motivo_recusa' => $s->motivo_recusa,
                'data' => $s->created_at->format('d/m/Y H:i'),
                'data_realizacao' => $s->data_realizacao ? \Carbon\Carbon::parse($s->data_realizacao)->format('d/m/Y H:i') : null,
            ]);

        return Inertia::render('Cooperado/MinhasSolicitacoes', [
            'solicitacoes' => $solicitacoes,
        ]);
    }

    /**
     * Exibe a página de perfil do cooperado
     */
    public function perfil()
    {
        return \Inertia\Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => request()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail,
            'status' => session('status'),
        ]);
    }
}
