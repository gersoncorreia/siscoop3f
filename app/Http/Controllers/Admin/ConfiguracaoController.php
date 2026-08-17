<?php

namespace App\Http\Controllers\Admin;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\UpdateTenantSettingsRequest;
use App\Http\Requests\Admin\UpdatePaymentSettingsRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ConfiguracaoController extends Controller
{
    protected CooperativeService $cooperativeService;

    public function __construct(CooperativeService $cooperativeService)
    {
        $this->cooperativeService = $cooperativeService;
    }

    public function index()
    {
        return Inertia::render('Configuracoes/Index', [
            'tenant' => tenant()
        ]);
    }

    public function update(UpdateTenantSettingsRequest $request)
    {
        $tenant = tenant();
        
        $validated = $request->validated();

        $data = [
            'name' => $validated['name'],
            'primary_color' => $validated['primary_color'],
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo_path'] = $path;
        }

        $this->cooperativeService->atualizarUsuario($tenant->id, $data); // Note: or general tenant update

        return redirect()->back()->with('success', 'Configurações atualizadas com sucesso!');
    }

    /**
     * Página de configuração de Gateways de Pagamento
     */
    public function pagamentos()
    {
        $tenant = tenant();

        // Configurações de pagamento salvas no tenant
        $defaultConfig = [
            'active_gateway' => 'interno',
            'enabled_gateways' => ['interno', 'pix_manual', 'presencial'],
            'pix_manual' => [
                'enabled' => true,
                'tipo_chave' => 'cnpj',
                'chave_pix' => '',
                'favorecido' => $tenant->nome ?? '',
                'qrcode_image_url' => null,
                'instrucoes' => 'Efetue o pagamento via PIX apontando a câmera para o QR Code ou copiando a chave abaixo.',
            ],
            'presencial' => [
                'enabled' => true,
                'aceita_dinheiro' => true,
                'aceita_maquininha' => true,
                'instrucoes' => 'Você também pode realizar o pagamento presencialmente na recepção em dinheiro ou cartão.',
            ],
            'asaas' => ['enabled' => false, 'api_key' => '', 'environment' => 'sandbox', 'webhook_token' => ''],
            'stripe' => ['enabled' => false, 'public_key' => '', 'secret_key' => '', 'webhook_secret' => ''],
            'mercadopago' => ['enabled' => false, 'access_token' => '', 'public_key' => ''],
            'taxas_adesao' => [
                ['id' => '1', 'titulo' => 'Taxa Administrativa (Titular)', 'valor' => 100.00, 'ativo' => true],
                ['id' => '2', 'titulo' => 'Taxa de Uso do Sistema', 'valor' => 10.00, 'ativo' => true],
            ],
            'taxa_dependente_legal' => 80.00,
            'taxa_dependente_nao_legal' => 100.00,
            'cota_capital_config' => [
                'meta_total' => 600.00,
                'valor_quota' => 10.00,
                'qtd_quotas' => 60,
                'valor_mensal' => 10.00,
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

        // Se o array de taxas_adesao existir no banco, ele deve sobrescrever a lista default
        if (isset($rawConfig['taxas_adesao']) && is_array($rawConfig['taxas_adesao'])) {
            $paymentConfig['taxas_adesao'] = $rawConfig['taxas_adesao'];
        }

        if (!empty($paymentConfig['pix_manual']['qrcode_image_path'])) {
            $paymentConfig['pix_manual']['qrcode_image_url'] = tenant_asset($paymentConfig['pix_manual']['qrcode_image_path']);
        } elseif (!empty($paymentConfig['pix_manual']['qrcode_image_url']) && !str_starts_with($paymentConfig['pix_manual']['qrcode_image_url'], 'http')) {
            $path = str_replace('/storage/', '', $paymentConfig['pix_manual']['qrcode_image_url']);
            $paymentConfig['pix_manual']['qrcode_image_url'] = tenant_asset($path);
        }

        // Mascarar chaves sensíveis antes de enviar para o frontend
        $maskedConfig = $paymentConfig;
        foreach (['asaas' => ['api_key', 'webhook_token'], 'stripe' => ['secret_key', 'webhook_secret'], 'mercadopago' => ['access_token']] as $gw => $fields) {
            if (isset($maskedConfig[$gw]) && is_array($maskedConfig[$gw])) {
                foreach ($fields as $field) {
                    if (!empty($maskedConfig[$gw][$field])) {
                        $maskedConfig[$gw][$field] = '********************' . substr($maskedConfig[$gw][$field], -4);
                    }
                }
            }
        }

        return Inertia::render('Configuracoes/Pagamentos', [
            'paymentConfig' => $maskedConfig,
        ]);
    }

    /**
     * Salvar configurações de Gateway de Pagamento e Taxas de Adesão
     */
    public function updatePagamentos(UpdatePaymentSettingsRequest $request)
    {
        $tenant = tenant();

        $validated = $request->validated();
        
        $currentConfig = $tenant->payment_config;
        if (is_string($currentConfig)) {
            $currentConfig = json_decode($currentConfig, true) ?? [];
        }
        if (!is_array($currentConfig)) {
            $currentConfig = [];
        }

        $data = array_replace_recursive($currentConfig, $validated);

        if (isset($validated['taxas_adesao'])) {
            $data['taxas_adesao'] = array_values($validated['taxas_adesao']);
        }

        if (isset($validated['taxa_dependente_legal'])) {
            $data['taxa_dependente_legal'] = (float) $validated['taxa_dependente_legal'];
        }

        if (isset($validated['taxa_dependente_nao_legal'])) {
            $data['taxa_dependente_nao_legal'] = (float) $validated['taxa_dependente_nao_legal'];
        }

        if (isset($validated['cota_capital_config'])) {
            $data['cota_capital_config'] = $validated['cota_capital_config'];
        }

        // Upload do QR Code do PIX Manual se enviado
        if ($request->hasFile('pix_manual.qrcode_image')) {
            if (!empty($currentConfig['pix_manual']['qrcode_image_path'])) {
                Storage::disk('public')->delete($currentConfig['pix_manual']['qrcode_image_path']);
            } elseif (!empty($currentConfig['pix_manual']['qrcode_image_url'])) {
                $oldPath = str_replace('/storage/', '', $currentConfig['pix_manual']['qrcode_image_url']);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('pix_manual.qrcode_image')->store('pix_qrcodes', 'public');
            $data['pix_manual']['qrcode_image_path'] = $path;
            $data['pix_manual']['qrcode_image_url'] = tenant_asset($path);
        }

        // Não sobrescrever com as máscaras (estrelinhas) vindas do frontend
        foreach (['asaas' => ['api_key', 'webhook_token'], 'stripe' => ['secret_key', 'webhook_secret'], 'mercadopago' => ['access_token']] as $gw => $fields) {
            if (isset($validated[$gw])) {
                foreach ($fields as $field) {
                    if (isset($validated[$gw][$field]) && str_contains($validated[$gw][$field], '***')) {
                        $data[$gw][$field] = $currentConfig[$gw][$field] ?? '';
                    }
                }
            }
        }

        // Normalizar booleans em $data
        if (isset($data['pix_manual']['enabled'])) {
            $data['pix_manual']['enabled'] = filter_var($data['pix_manual']['enabled'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($data['presencial']['enabled'])) {
            $data['presencial']['enabled'] = filter_var($data['presencial']['enabled'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($data['presencial']['aceita_dinheiro'])) {
            $data['presencial']['aceita_dinheiro'] = filter_var($data['presencial']['aceita_dinheiro'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($data['presencial']['aceita_maquininha'])) {
            $data['presencial']['aceita_maquininha'] = filter_var($data['presencial']['aceita_maquininha'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($data['asaas']['enabled'])) {
            $data['asaas']['enabled'] = filter_var($data['asaas']['enabled'], FILTER_VALIDATE_BOOLEAN);
        }

        // Remover o objeto File do pix_manual se presente no array para não corromper o JSON
        if (isset($data['pix_manual']['qrcode_image'])) {
            unset($data['pix_manual']['qrcode_image']);
        }

        $tenant->payment_config = $data;
        $tenant->save();

        return redirect()->back()->with('success', 'Configurações de pagamento atualizadas com sucesso!');
    }

    /**
     * Página de configuração dos Termos de LGPD
     */
    public function lgpd()
    {
        $settings = \App\Models\LgpdSetting::first() ?? (object) \App\Models\LgpdSetting::getDefault();
        return Inertia::render('Configuracoes/Lgpd', [
            'settings' => $settings,
        ]);
    }

    /**
     * Atualizar Termos de LGPD
     */
    public function updateLgpd(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'introducao' => 'required|string',
            'secoes' => 'required|array',
            'secoes.*.titulo' => 'required|string|max:255',
            'secoes.*.conteudo' => 'required|string',
            'botao_texto' => 'required|string|max:255',
        ]);

        $settings = \App\Models\LgpdSetting::first();
        if ($settings) {
            $settings->update($validated);
        } else {
            \App\Models\LgpdSetting::create($validated);
        }

        return redirect()->back()->with('success', 'Termos de LGPD atualizados com sucesso!');
    }
}
