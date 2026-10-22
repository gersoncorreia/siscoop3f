<?php

namespace App\Http\Controllers;

use App\Models\Prestador;
use App\Models\PrestadorServico;
use App\Http\Requests\Prestador\BuscarCooperadoRequest;
use App\Http\Requests\Prestador\SolicitarAutorizacaoRequest;
use App\Http\Requests\Prestador\StoreServicoRequest;
use App\Http\Requests\Prestador\UpdatePerfilRequest;
use App\Http\Requests\Prestador\UpdateSenhaRequest;
use App\Services\PrestadorService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class PrestadorPortalController extends Controller
{
    protected PrestadorService $prestadorService;

    public function __construct(PrestadorService $prestadorService)
    {
        $this->prestadorService = $prestadorService;
    }

    /**
     * Obter o Prestador vinculado ao usuário logado
     */
    protected function getPrestador(): Prestador
    {
        $prestador = $this->prestadorService->getPrestadorByUserId(auth()->id());

        if (!$prestador) {
            abort(403, 'Acesso restrito: Nenhum cadastro de Prestador/Parceiro vinculado ao seu usuário.');
        }

        return $prestador;
    }

    /**
     * Dashboard do Prestador / Parceiro
     */
    public function dashboard(): Response
    {
        $prestador = $this->getPrestador();
        
        $solicitacoes = $this->prestadorService->getDashboardSolicitacoes($prestador->id, 10)
            ->map(fn($s) => [
                'id' => $s->id,
                'paciente' => $s->associado->nome ?? 'N/A',
                'matricula' => str_pad((string)($s->associado->id ?? 0), 4, '0', STR_PAD_LEFT),
                'servico' => $s->servico->nome ?? 'Outros',
                'status' => $s->status,
                'data' => $s->created_at->format('d/m/Y H:i'),
            ]);

        $servicos = $prestador->servicos()->where('ativo', true)->get();

        return Inertia::render('Prestador/Dashboard', [
            'prestador' => $prestador,
            'solicitacoes' => $solicitacoes,
            'servicos' => $servicos,
        ]);
    }

    public function buscarCooperado(BuscarCooperadoRequest $request): JsonResponse
    {
        $associados = $this->prestadorService->buscarCooperadosAtivos($request->validated()['q']);
        return response()->json($associados);
    }

    /**
     * Fluxo Invertido: Prestador inicia o pedido de autorização
     */
    public function solicitarAutorizacao(SolicitarAutorizacaoRequest $request): RedirectResponse
    {
        $prestador = $this->getPrestador();
        $data = $request->validated();

        $associado = $this->prestadorService->findAssociadoByIdentificacao($data['identificacao']);

        if (!$associado) {
            return back()->with('error', 'cooperado_nao_encontrado');
        }

        if ($associado->status !== 'ativo') {
            return back()->with('error', 'cooperado_inativo');
        }

        if ($this->prestadorService->isAssociadoInadimplente($associado)) {
            return back()->with('error', 'inadimplente');
        }

        $servico = PrestadorServico::findOrFail($data['servico_id']);

        $solicitacao = $this->prestadorService->criarSolicitacao($associado, $prestador, $servico, $data['observacoes'] ?? null);

        // Notificar o Prestador
        $prestadorUser = auth()->user();
        if ($prestadorUser) {
            $prestadorUser->notify(new \App\Notifications\AppDatabaseNotification(
                'Solicitação Enviada',
                "Pedido de autorização para {$associado->nome} ({$servico->nome}) enviado com sucesso.",
                route('prestador.dashboard'),
                'ClipboardDocumentListIcon'
            ));
        }

        // Notificar o Paciente / Associado se possuir usuário
        if ($associado->user) {
            $associado->user->notify(new \App\Notifications\AppDatabaseNotification(
                'Solicitação de Atendimento',
                "{$prestador->nome} registrou solicitação de {$servico->nome} para seu perfil.",
                route('cooperado.minhas-solicitacoes'),
                'CheckBadgeIcon'
            ));
        }

        return back()->with('success', 'Pedido de autorização enviado com sucesso. Aguarde a liberação da cooperativa.');
    }

    /**
     * Tela de Catálogo de Serviços/Produtos
     */
    public function servicos(): Response
    {
        $prestador = $this->getPrestador();
        $servicos = $prestador->servicos;

        return Inertia::render('Prestador/Servicos', [
            'prestador' => $prestador,
            'servicos' => $servicos,
        ]);
    }

    public function storeServico(StoreServicoRequest $request): RedirectResponse
    {
        $prestador = $this->getPrestador();
        $this->prestadorService->criarServico($prestador, $request->validated());

        return back()->with('success', 'Item adicionado ao catálogo com sucesso!');
    }

    /**
     * Atualizar um serviço/produto do catálogo
     */
    public function updateServico(StoreServicoRequest $request, $id): RedirectResponse
    {
        $prestador = $this->getPrestador();
        $this->prestadorService->atualizarServico($prestador, $id, $request->validated());

        return back()->with('success', 'Item atualizado com sucesso!');
    }

    /**
     * Excluir um serviço/produto do catálogo
     */
    public function deleteServico($id): RedirectResponse
    {
        $prestador = $this->getPrestador();
        $this->prestadorService->excluirServico($prestador, $id);

        return back()->with('success', 'Item removido do catálogo.');
    }

    /**
     * Tela de Faturamento (Regra do dia 05)
     */
    public function faturas(): Response
    {
        $prestador = $this->getPrestador();
        $faturas = $prestador->faturas()->orderBy('created_at', 'desc')->get();

        return Inertia::render('Prestador/Faturas', [
            'prestador' => $prestador,
            'faturas' => $faturas,
        ]);
    }

    /**
     * Enviar nova fatura para análise
     */
    public function storeFatura(Request $request): RedirectResponse
    {
        $prestador = $this->getPrestador();

        $validated = $request->validate([
            'mes_referencia' => 'required|string|max:20',
            'valor_total' => 'required|numeric|min:0.01',
            'arquivo_guia' => 'nullable|file|mimes:pdf,xml,png,jpg,jpeg|max:10240',
        ], [
            'mes_referencia.required' => 'O mês de referência / competência é obrigatório.',
            'valor_total.required' => 'Informe o valor total da fatura.',
            'arquivo_guia.mimes' => 'O arquivo da nota fiscal deve ser PDF, XML ou Imagem (máx 10MB).',
        ]);

        $anexoPath = null;
        if ($request->hasFile('arquivo_guia')) {
            $anexoPath = $request->file('arquivo_guia')->store('faturas_prestador', 'public');
        }

        $prestador->faturas()->create([
            'mes_referencia' => $validated['mes_referencia'],
            'valor_total' => $validated['valor_total'],
            'status' => 'enviada',
            'arquivo_guia' => $anexoPath,
        ]);

        return back()->with('success', 'Fatura enviada com sucesso para análise da administração!');
    }

    /**
     * Perfil do Prestador
     */
    public function perfil(): Response
    {
        $prestador = $this->getPrestador();
        $user = auth()->user();

        return Inertia::render('Prestador/Perfil', [
            'prestador' => [
                'id' => $prestador->id,
                'nome' => $prestador->nome,
                'tipo' => $prestador->tipo,
                'especialidades' => $prestador->especialidades ?? [],
                'endereco' => $prestador->endereco,
            ],
            'usuario' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Atualizar dados do perfil (prestador + user)
     */
    public function updatePerfil(UpdatePerfilRequest $request): RedirectResponse
    {
        $prestador = $this->getPrestador();
        $user = auth()->user();
        $data = $request->validated();

        $this->prestadorService->atualizarPerfil($prestador, $user, $data);

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Alterar senha de acesso
     */
    public function updateSenha(UpdateSenhaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'A senha atual está incorreta.']);
        }

        $this->prestadorService->alterarSenha($user, $data['password']);

        return back()->with('success', 'Senha alterada com sucesso!');
    }
}
