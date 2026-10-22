<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Contracts\AssociadoRepositoryInterface;
use App\Models\Agendamento;
use App\Models\Prestador;
use App\Models\PrestadorServico;
use App\Models\SolicitacaoServico;
use App\Services\ElegibilidadeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\ProfileUpdateRequest;

class CooperadoServicoController extends Controller
{
    protected AssociadoRepositoryInterface $associados;
    protected ElegibilidadeService $elegibilidadeService;

    public function __construct(
        AssociadoRepositoryInterface $associados,
        ElegibilidadeService $elegibilidadeService
    ) {
        $this->associados = $associados;
        $this->elegibilidadeService = $elegibilidadeService;
    }

    /**
     * Lista de Prestadores da Rede Credenciada
     */
    public function prestadores(): Response
    {
        $this->associados->getActiveAssociadoForUser(auth()->user());

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
     * Detalhes de um Prestador + Serviços + Dependentes para Seleção
     */
    public function prestadorDetalhe($id): Response
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());
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

        // Dependentes aptos do associado
        $dependentes = $associado->dependentes()
            ->where('status', 'ativo')
            ->get(['id', 'nome', 'parentesco']);

        // Solicitações do cooperado neste prestador
        $solicitacoes = SolicitacaoServico::with(['servico', 'dependente'])
            ->where('associado_id', $associado->id)
            ->where('prestador_id', $prestador->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'codigo_guia' => $s->codigo_guia,
                'beneficiario' => $s->dependente ? $s->dependente->nome . ' (Dependente)' : $associado->nome . ' (Titular)',
                'servico_produto' => $s->servico->nome ?? 'Outros',
                'valor' => (float) ($s->valor_cobrado ?? 0),
                'observacoes' => $s->observacoes,
                'status' => $s->status,
                'data_agendada' => $s->data_agendada ? \Carbon\Carbon::parse($s->data_agendada)->format('d/m/Y H:i') : null,
                'motivo_recusa' => $s->motivo_recusa,
                'data' => $s->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Cooperado/PrestadorDetalhe', [
            'prestador' => $prestadorData,
            'servicos' => $servicos,
            'dependentes' => $dependentes,
            'solicitacoes' => $solicitacoes,
        ]);
    }

    /**
     * Endpoint para checagem de elegibilidade prévia (via AJAX)
     */
    public function checarElegibilidade(Request $request, $prestadorId, $servicoId): JsonResponse
    {
        $prestador = Prestador::findOrFail($prestadorId);
        $servico = PrestadorServico::findOrFail($servicoId);
        $dependenteId = $request->query('dependente_id');

        $resultado = $this->elegibilidadeService->verificarElegibilidade(
            auth()->user(),
            $prestador,
            $servico,
            $dependenteId ? (int)$dependenteId : null
        );

        return response()->json($resultado);
    }

    /**
     * Disparo da Solicitação e Agendamento 100% Online
     */
    public function solicitarEAgendar(Request $request, $prestadorId): RedirectResponse
    {
        $validated = $request->validate([
            'servico_id' => 'required|exists:prestador_servicos,id',
            'dependente_id' => 'nullable|exists:dependentes,id',
            'data_agendada' => 'required|date|after_or_equal:today',
            'turno' => 'required|in:manha,tarde,noite',
            'observacoes' => 'nullable|string|max:500',
        ]);

        $prestador = Prestador::findOrFail($prestadorId);
        $servico = PrestadorServico::findOrFail($validated['servico_id']);
        $user = auth()->user();
        $associado = $this->associados->getActiveAssociadoForUser($user);

        // 1. Executa o Motor de Elegibilidade Automática
        $elegibilidade = $this->elegibilidadeService->verificarElegibilidade(
            $user,
            $prestador,
            $servico,
            $validated['dependente_id'] ?? null
        );

        if (!$elegibilidade['apto']) {
            return back()->with('error', $elegibilidade['motivo']);
        }

        // 2. Formatar data e hora do agendamento
        $horaPadrao = match ($validated['turno']) {
            'manha' => '09:00:00',
            'tarde' => '14:30:00',
            'noite' => '18:30:00',
        };
        $dataHora = \Carbon\Carbon::parse($validated['data_agendada'])->format('Y-m-d') . ' ' . $horaPadrao;

        // 3. Gerar Código Único de Guia Digital Autorizada
        $codigoGuia = 'GUIA-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        // 4. Criar registro na tabela de Agendamento
        $agendamento = Agendamento::create([
            'user_id' => $user->id,
            'tipo' => 'consulta',
            'especialidade' => $servico->nome,
            'prestador_id' => $prestador->id,
            'data_hora' => $dataHora,
            'status' => 'confirmado', // Já nasce confirmado e pré-autorizado!
            'valor_cobrado' => $servico->valor_acordado ?? 0,
            'modalidade' => 'presencial',
            'observacoes' => $validated['observacoes'] ?? "Agendado via portal do cooperado. Turno: {$validated['turno']}",
        ]);

        // 5. Criar a Solicitação de Serviço vinculada ao Agendamento
        SolicitacaoServico::create([
            'associado_id' => $associado->id,
            'dependente_id' => $validated['dependente_id'] ?? null,
            'prestador_id' => $prestador->id,
            'servico_id' => $servico->id,
            'agendamento_id' => $agendamento->id,
            'codigo_guia' => $codigoGuia,
            'valor_cobrado' => $servico->valor_acordado ?? 0,
            'status' => 'aprovado_cooperativa', // Liberado instantaneamente pelo sistema
            'data_agendada' => $dataHora,
            'observacoes' => $validated['observacoes'] ?? null,
        ]);

        return back()->with('success', "Agendamento realizado e autorizado com sucesso! Guia: {$codigoGuia}. Compareça na data informada.");
    }

    /**
     * Exibe as solicitações (autorizações) feitas para este cooperado
     */
    public function minhasSolicitacoes(): Response
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

        $solicitacoes = SolicitacaoServico::with(['prestador', 'servico', 'dependente'])
            ->where('associado_id', $associado->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'codigo_guia' => $s->codigo_guia ?? "GUIA-{$s->id}",
                'beneficiario' => $s->dependente ? $s->dependente->nome . ' (Dependente)' : $associado->nome . ' (Titular)',
                'prestador' => $s->prestador->nome ?? 'Desconhecido',
                'servico_produto' => $s->servico->nome ?? 'Outros',
                'observacoes' => $s->observacoes,
                'status' => $s->status,
                'motivo_recusa' => $s->motivo_recusa,
                'data_agendada' => $s->data_agendada ? \Carbon\Carbon::parse($s->data_agendada)->format('d/m/Y H:i') : null,
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
    public function perfil(): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => request()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Atualiza as informações de perfil do cooperado
     */
    public function updatePerfil(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return redirect()->route('cooperado.perfil')->with('success', 'Perfil atualizado com sucesso.');
    }
}

