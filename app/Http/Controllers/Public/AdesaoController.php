<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

use App\Models\Associado;
use App\Services\AdesaoService;
use App\Http\Requests\Adesao\SolicitarAdesaoRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;

class AdesaoController extends Controller
{
    /**
     * Exibe o formulário público de adesão no tenant
     */
    public function create()
    {
        $tenant = tenant();
        $defaultConfig = [
            'taxas_adesao' => [
                ['id' => '1', 'titulo' => 'Taxa Administrativa (Titular)', 'valor' => 100.00, 'ativo' => true],
                ['id' => '2', 'titulo' => 'Taxa de Uso do Sistema', 'valor' => 10.00, 'ativo' => true],
            ],
            'taxa_dependente_legal' => 80.00,
            'taxa_dependente_nao_legal' => 100.00,
            'cota_capital_config' => [
                'aporte_inicial' => 10.00,
            ],
        ];

        $rawConfig = $tenant->payment_config;
        if (is_string($rawConfig)) {
            $rawConfig = json_decode($rawConfig, true) ?? [];
        }
        if (!is_array($rawConfig)) {
            $rawConfig = [];
        }

        $paymentConfig = array_replace_recursive($defaultConfig, $rawConfig);

        if (isset($rawConfig['taxas_adesao']) && is_array($rawConfig['taxas_adesao'])) {
            $paymentConfig['taxas_adesao'] = $rawConfig['taxas_adesao'];
        }

        return Inertia::render('Adesao/Form', [
            'tenant' => $tenant,
            'paymentConfig' => $paymentConfig,
        ]);
    }

    /**
     * Processa os dados do formulário público de adesão
     */
    public function store(SolicitarAdesaoRequest $request, AdesaoService $adesaoService)
    {
        $associado = $adesaoService->processarAdesao($request->validated());

        session(['ultimo_associado_id' => $associado->id]);

        return Redirect::route('adesao.success')->with('success', 'Cadastro realizado com sucesso!');
    }

    /**
     * Exibe tela de sucesso e opções de pagamento após o cadastro público
     */
    public function success()
    {
        $tenant = tenant();
        $associadoId = session('ultimo_associado_id');
        $associado = $associadoId ? Associado::find($associadoId) : null;
        $lancamento = $associado ? \App\Models\LancamentoFinanceiro::where('associado_id', $associado->id)->latest()->first() : null;

        $defaultPaymentConfig = [
            'active_gateway' => 'interno',
            'pix_manual' => [
                'enabled' => true,
                'tipo_chave' => 'cnpj',
                'chave_pix' => '',
                'favorecido' => $tenant->nome ?? 'Fundação Santa Casa da Amazônia',
                'qrcode_image_url' => null,
                'instrucoes' => 'Efetue o pagamento via PIX apontando a câmera para o QR Code ou copiando a chave abaixo.',
            ],
            'presencial' => [
                'enabled' => true,
                'aceita_dinheiro' => true,
                'aceita_maquininha' => true,
                'instrucoes' => 'Você também pode realizar o pagamento presencialmente na recepção em dinheiro ou máquina de cartão.',
            ],
        ];

        $rawConfig = $tenant->payment_config;
        if (is_string($rawConfig)) {
            $rawConfig = json_decode($rawConfig, true) ?? [];
        }
        if (!is_array($rawConfig)) {
            $rawConfig = [];
        }

        $paymentConfig = array_replace_recursive($defaultPaymentConfig, $rawConfig);

        if (!empty($paymentConfig['pix_manual']['qrcode_image_path'])) {
            $paymentConfig['pix_manual']['qrcode_image_url'] = tenant_asset($paymentConfig['pix_manual']['qrcode_image_path']);
        } elseif (!empty($paymentConfig['pix_manual']['qrcode_image_url']) && !str_starts_with($paymentConfig['pix_manual']['qrcode_image_url'], 'http')) {
            $path = str_replace('/storage/', '', $paymentConfig['pix_manual']['qrcode_image_url']);
            $paymentConfig['pix_manual']['qrcode_image_url'] = tenant_asset($path);
        }

        return Inertia::render('Adesao/Success', [
            'associado' => $associado ? [
                'id' => $associado->id,
                'nome' => $associado->nome,
                'email' => $associado->email,
                'status' => $associado->status,
            ] : null,
            'lancamento' => $lancamento ? [
                'id' => $lancamento->id,
                'valor' => (float)$lancamento->valor,
                'status' => $lancamento->status,
                'categoria' => $lancamento->categoria,
                'data_vencimento' => $lancamento->data_vencimento ? \Carbon\Carbon::parse($lancamento->data_vencimento)->format('d/m/Y') : null,
            ] : null,
            'paymentConfig' => $paymentConfig,
        ]);
    }

    /**
     * PAINEL ADMIN: Lista adesões pendentes
     */
    public function index()
    {
        Gate::authorize('associados.view');

        $tenant = tenant();
        $defaultConfig = [
            'taxas_adesao' => [
                ['id' => '1', 'titulo' => 'Taxa Administrativa (Titular)', 'valor' => 100.00, 'ativo' => true],
                ['id' => '2', 'titulo' => 'Taxa de Uso do Sistema', 'valor' => 10.00, 'ativo' => true],
            ],
            'taxa_dependente_legal' => 80.00,
            'taxa_dependente_nao_legal' => 100.00,
            'cota_capital_config' => [
                'aporte_inicial' => 10.00,
            ],
        ];

        $rawConfig = $tenant->payment_config;
        if (is_string($rawConfig)) {
            $rawConfig = json_decode($rawConfig, true) ?? [];
        }
        if (!is_array($rawConfig)) {
            $rawConfig = [];
        }

        $paymentConfig = array_replace_recursive($defaultConfig, $rawConfig);
        if (isset($rawConfig['taxas_adesao']) && is_array($rawConfig['taxas_adesao'])) {
            $paymentConfig['taxas_adesao'] = $rawConfig['taxas_adesao'];
        }

        $pendentes = Associado::with(['dependentes'])
            ->whereIn('status', ['analise_documental', 'pendente'])
            ->whereNull('aprovado_em')
            ->latest()
            ->paginate(10, ['*'], 'page_pendentes');

        $aprovadas = Associado::with(['dependentes', 'aprovador'])
            ->whereNotNull('aprovado_em')
            ->where('status', 'ativo')
            ->latest('aprovado_em')
            ->paginate(10, ['*'], 'page_aprovadas')
            ->through(function ($item) {
                return [
                    'id' => $item->id,
                    'nome' => $item->nome,
                    'cpf' => $item->cpf,
                    'email' => $item->email,
                    'status' => $item->status,
                    'data_adesao' => $item->data_adesao ? $item->data_adesao->format('d/m/Y') : null,
                    'aprovado_por_nome' => $item->aprovado_por_nome ?? $item->aprovador?->name ?? 'Administrador',
                    'aprovado_em' => $item->aprovado_em ? $item->aprovado_em->format('d/m/Y H:i:s') : null,
                    'forma_pagamento_aprovacao' => $item->forma_pagamento_aprovacao,
                    'observacoes_aprovacao' => $item->observacoes_aprovacao,
                    'parcelas_aprovacao' => $item->parcelas_aprovacao ?? 1,
                    'comprovante_pix_url' => $item->comprovante_pix_path ? tenant_asset($item->comprovante_pix_path) : null,
                    'detalhes_financeiro_aprovacao' => $item->detalhes_financeiro_aprovacao,
                    'dependentes' => $item->dependentes,
                    'autoriza_desconto_folha' => $item->autoriza_desconto_folha,
                ];
            });

        return Inertia::render('Associados/Adesoes/Index', [
            'pendentes' => $pendentes,
            'aprovadas' => $aprovadas,
            'paymentConfig' => $paymentConfig,
        ]);
    }

    /**
     * PAINEL ADMIN: Aprova uma adesão
     */
    public function aprovar(Associado $associado, Request $request, AdesaoService $adesaoService)
    {
        Gate::authorize('associados.update');

        $validated = $request->validate([
            'forma_pagamento' => 'required|string',
            'observacoes_pagamento' => 'nullable|string|max:1000',
            'parcelas' => 'nullable|integer|min:1|max:12',
            'comprovante_pix' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $comprovantePath = null;
        if ($request->hasFile('comprovante_pix')) {
            $comprovantePath = $request->file('comprovante_pix')->store('comprovantes_pix', 'public');
        }

        $tenant = tenant();
        $defaultConfig = [
            'taxas_adesao' => [
                ['id' => '1', 'titulo' => 'Taxa Administrativa (Titular)', 'valor' => 100.00, 'ativo' => true],
                ['id' => '2', 'titulo' => 'Taxa de Uso do Sistema', 'valor' => 10.00, 'ativo' => true],
            ],
            'taxa_dependente_legal' => 80.00,
            'taxa_dependente_nao_legal' => 100.00,
            'cota_capital_config' => [
                'aporte_inicial' => 10.00,
            ],
        ];

        $rawConfig = $tenant->payment_config;
        if (is_string($rawConfig)) {
            $rawConfig = json_decode($rawConfig, true) ?? [];
        }
        if (!is_array($rawConfig)) {
            $rawConfig = [];
        }

        $paymentConfig = array_replace_recursive($defaultConfig, $rawConfig);
        if (isset($rawConfig['taxas_adesao']) && is_array($rawConfig['taxas_adesao'])) {
            $paymentConfig['taxas_adesao'] = $rawConfig['taxas_adesao'];
        }

        $user = auth()->user();

        // Gerar snapshot dos detalhes financeiros no momento da aprovação
        $taxasAdesao = $paymentConfig['taxas_adesao'] ?? [];
        $taxaDepLegal = (float) ($paymentConfig['taxa_dependente_legal'] ?? 80.00);
        $taxaDepNaoLegal = (float) ($paymentConfig['taxa_dependente_nao_legal'] ?? 100.00);
        $cotaAporteInicial = (float) ($paymentConfig['cota_capital_config']['aporte_inicial'] ?? 10.00);

        $itensAtivos = [];
        $totalFixos = 0;
        foreach ($taxasAdesao as $taxa) {
            if (!empty($taxa['ativo'])) {
                $val = (float) ($taxa['valor'] ?? 0);
                $totalFixos += $val;
                $itensAtivos[] = ['titulo' => $taxa['titulo'], 'valor' => $val];
            }
        }

        $dependentes = $associado->dependentes;
        $qtdLegais = 0;
        $qtdNaoLegais = 0;
        foreach ($dependentes as $dep) {
            if ($dep->tipo_dependencia === 'nao_legal') {
                $qtdNaoLegais++;
            } else {
                $qtdLegais++;
            }
        }
        $totalDepLegal = $qtdLegais * $taxaDepLegal;
        $totalDepNaoLegal = $qtdNaoLegais * $taxaDepNaoLegal;

        $isCooperativa = $tenant->tipo !== 'fundacao';
        $cotaVal = $isCooperativa ? $cotaAporteInicial : 0;

        $totalGeral = $totalFixos + $totalDepLegal + $totalDepNaoLegal + $cotaVal;

        $detalhesSnapshot = [
            'itensAtivos' => $itensAtivos,
            'qtdLegais' => $qtdLegais,
            'taxaDepLegal' => $taxaDepLegal,
            'totalDepLegal' => $totalDepLegal,
            'qtdNaoLegais' => $qtdNaoLegais,
            'taxaDepNaoLegal' => $taxaDepNaoLegal,
            'totalDepNaoLegal' => $totalDepNaoLegal,
            'isCooperativa' => $isCooperativa,
            'cotaVal' => $cotaVal,
            'totalGeral' => $totalGeral,
        ];

        try {
            $adesaoService->aprovarAdesao(
                $associado,
                $validated['forma_pagamento'] ?? 'dinheiro',
                $validated['observacoes_pagamento'] ?? null,
                (int) ($validated['parcelas'] ?? 1),
                $comprovantePath,
                $user,
                $detalhesSnapshot
            );
            return Redirect::back()->with('success', 'Adesão aprovada com sucesso!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro ao aprovar adesão no Controller: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return Redirect::back()->with('error', 'Erro ao aprovar adesão: ' . $e->getMessage());
        }
    }

    /**
     * PAINEL ADMIN: Rejeita uma adesão
     */
    public function rejeitar(Associado $associado)
    {
        Gate::authorize('associados.delete');

        $associado->update(['status' => 'recusado']);

        return Redirect::back()->with('success', 'Adesão recusada.');
    }
}
