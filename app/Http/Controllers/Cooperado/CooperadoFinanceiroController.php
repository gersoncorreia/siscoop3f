<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Contracts\AssociadoRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CooperadoFinanceiroController extends Controller
{
    protected AssociadoRepositoryInterface $associados;

    public function __construct(AssociadoRepositoryInterface $associados)
    {
        $this->associados = $associados;
    }

    /**
     * Extrato financeiro pessoal do cliente
     */
    public function index(Request $request): Response
    {
        abort_if(auth()->user()->hasRole('Dependente'), 403, 'Acesso restrito ao titular.');
        
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

        $tenant = tenant();
        $defaultConfig = [
            'pix_manual' => [
                'enabled' => true,
                'tipo_chave' => 'cnpj',
                'chave_pix' => '',
                'favorecido' => $tenant->nome ?? 'Santa Casa da Amazônia',
            ]
        ];

        $rawConfig = $tenant->payment_config;
        if (is_string($rawConfig)) {
            $rawConfig = json_decode($rawConfig, true) ?? [];
        }
        if (!is_array($rawConfig)) {
            $rawConfig = [];
        }
        $paymentConfig = array_replace_recursive($defaultConfig, $rawConfig);

        if (!empty($paymentConfig['pix_manual']['qrcode_image_path'])) {
            $paymentConfig['pix_manual']['qrcode_image_url'] = tenant_asset($paymentConfig['pix_manual']['qrcode_image_path']);
        } elseif (!empty($paymentConfig['pix_manual']['qrcode_image_url']) && !str_starts_with($paymentConfig['pix_manual']['qrcode_image_url'], 'http')) {
            $path = str_replace('/storage/', '', $paymentConfig['pix_manual']['qrcode_image_url']);
            $paymentConfig['pix_manual']['qrcode_image_url'] = tenant_asset($path);
        }

        $allLancamentos = $associado->lancamentosFinanceiros;
        $stats = [
            'totalPago' => (float) $allLancamentos->where('status', 'pago')->sum('valor'),
            'totalPendente' => (float) $allLancamentos->where('status', 'pendente')->sum('valor'),
            'qtdPendentes' => $allLancamentos->where('status', 'pendente')->count(),
        ];

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

        $query = $associado->lancamentosFinanceiros()->orderBy('data_vencimento', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $lancamentos = $query->paginate(15)->through(function ($l) use ($categoriaLabels) {
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
                'data_pagamento' => $l->data_pagamento ? \Carbon\Carbon::parse($l->data_pagamento)->format('d/m/Y') : null,
                'forma_pagamento' => $l->forma_pagamento,
                'observacoes' => $l->observacoes_pagamento ?? $l->observacoes,
                'created_at' => $l->created_at->format('d/m/Y'),
            ];
        });

        return Inertia::render('Cooperado/Financeiro', [
            'lancamentos' => $lancamentos,
            'stats' => $stats,
            'paymentConfig' => $paymentConfig,
            'filtroStatus' => $request->status,
        ]);
    }

    /**
     * Cotas de capital do cooperado
     */
    public function cotaCapital(): Response
    {
        abort_if(auth()->user()->hasRole('Dependente'), 403, 'Acesso restrito ao titular.');
        
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

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
}
