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
     * Dashboard principal do cliente/cooperado
     */
    public function index(): Response
    {
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());
        $associado->load(['dependentes', 'cotasCapital', 'lancamentosFinanceiros', 'reembolsos']);

        $saldoCotaCapital = $associado->cotasCapital->sum('saldo');
        
        $lancamentos = $associado->lancamentosFinanceiros;
        $mensalidadesPendentes = $lancamentos->where('status', 'pendente')->count();
        $totalPago = $lancamentos->where('status', 'pago')->sum('valor');
        $totalPendente = $lancamentos->where('status', 'pendente')->sum('valor');

        $totalDependentes = $associado->dependentes->count();
        $reembolsosEmAnalise = $associado->reembolsos->where('status', 'pendente')->count();

        $categoriaLabels = [
            'taxa_adm_titular' => 'Taxa Administrativa (Titular)',
            'taxa_uso_sistema' => 'Taxa de Uso do Sistema',
            'taxa_adm_dependente' => 'Taxa Dependente Legal',
            'taxa_adm_dependente_nao_legal' => 'Taxa Dependente Não Legal',
            'cota_capital' => 'Cota Capital (Aporte Inicial)',
            'consulta_medica' => 'Consulta Médica Especializada',
            'exame' => 'Exame Laboratorial / Imagem',
            'mensalidade' => 'Mensalidade do Plano',
        ];

        // Últimas 5 atividades (lançamentos recentes com nomes humanizados)
        $atividadesRecentes = $associado->lancamentosFinanceiros()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($l) use ($categoriaLabels) {
                $catSlug = $l->categoria;
                $label = $categoriaLabels[$catSlug] ?? null;
                if (!$label) {
                    $label = ucwords(str_replace(['_', '-'], ' ', $catSlug ?: 'Taxa / Serviço'));
                }

                return [
                    'id' => $l->id,
                    'tipo' => $l->tipo,
                    'categoria' => $l->categoria,
                    'categoria_label' => $label,
                    'valor' => (float) $l->valor,
                    'status' => $l->status,
                    'data_vencimento' => $l->data_vencimento ? \Carbon\Carbon::parse($l->data_vencimento)->format('d/m/Y') : null,
                    'created_at' => $l->created_at->format('d/m/Y'),
                    'observacoes' => $l->observacoes_pagamento ?? $l->observacoes,
                ];
            });

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
                'totalPago' => (float) $totalPago,
                'totalPendente' => (float) $totalPendente,
                'totalDependentes' => $totalDependentes,
                'reembolsosEmAnalise' => $reembolsosEmAnalise,
                'agendamentosPendentes' => $agendamentosPendentes,
                'solicitacoesPendentes' => $solicitacoesPendentes,
            ],
            'atividadesRecentes' => $atividadesRecentes,
        ]);
    }
}
