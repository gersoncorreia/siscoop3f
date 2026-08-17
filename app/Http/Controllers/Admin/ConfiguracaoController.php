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
        ];

        $rawConfig = $tenant->payment_config;
        if (is_string($rawConfig)) {
            $rawConfig = json_decode($rawConfig, true) ?? [];
        }
        if (!is_array($rawConfig)) {
            $rawConfig = [];
        }

        $paymentConfig = array_replace_recursive($defaultConfig, $rawConfig);

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
     * Salvar configurações de Gateway de Pagamento
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

        // Upload do QR Code do PIX Manual se enviado
        if ($request->hasFile('pix_manual.qrcode_image')) {
            if (!empty($currentConfig['pix_manual']['qrcode_image_url'])) {
                $oldPath = str_replace('/storage/', '', $currentConfig['pix_manual']['qrcode_image_url']);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('pix_manual.qrcode_image')->store('pix_qrcodes', 'public');
            $data['pix_manual']['qrcode_image_url'] = Storage::url($path);
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

        $tenant->update([
            'payment_config' => $data,
        ]);

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
