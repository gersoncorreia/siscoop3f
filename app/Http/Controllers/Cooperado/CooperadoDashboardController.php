<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Contracts\AssociadoRepositoryInterface;
use Inertia\Inertia;
use Inertia\Response;

class CooperadoDashboardController extends Controller
{
    protected AssociadoRepositoryInterface $associados;

    public function __construct(AssociadoRepositoryInterface $associados)
    {
        $this->associados = $associados;
    }

    /**
     * Dashboard principal do cooperado
     */
    public function index(): Response
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());
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

        $agendamentosPendentes = \App\Models\Agendamento::where('user_id', auth()->id())->where('status', 'pendente')->count();
        $solicitacoesPendentes = \App\Models\Agendamento::where('user_id', auth()->id())->count();

        return Inertia::render('Cooperado/Dashboard', [
            'associado' => [
                'id' => $associado->id,
                'nome' => $associado->nome,
                'status' => $associado->status,
                'carteirinha_numero' => $associado->carteirinha_numero,
                'carteirinha_status' => $associado->carteirinha_status,
                'data_adesao' => $associado->data_adesao?->format('d/m/Y'),
            ],
            'stats' => [
                'saldoCotaCapital' => (float) $saldoCotaCapital,
                'mensalidadesPendentes' => $mensalidadesPendentes,
                'totalDependentes' => $totalDependentes,
                'reembolsosEmAnalise' => $reembolsosEmAnalise,
                'agendamentosPendentes' => $agendamentosPendentes,
                'solicitacoesPendentes' => $solicitacoesPendentes,
            ],
            'atividadesRecentes' => $atividadesRecentes,
        ]);
    }
}
