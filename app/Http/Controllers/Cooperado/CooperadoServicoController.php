<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Contracts\AssociadoRepositoryInterface;
use App\Models\Prestador;
use App\Models\SolicitacaoServico;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;

class CooperadoServicoController extends Controller
{
    protected AssociadoRepositoryInterface $associados;

    public function __construct(AssociadoRepositoryInterface $associados)
    {
        $this->associados = $associados;
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
     * Detalhes de um Prestador + Solicitações do Cooperado naquele parceiro
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
    public function minhasSolicitacoes(): Response
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

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
