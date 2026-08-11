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
        $paymentConfig = $tenant->payment_config ?? [
            'active_gateway' => 'interno',
            'asaas' => ['api_key' => '', 'environment' => 'sandbox', 'webhook_token' => ''],
            'stripe' => ['public_key' => '', 'secret_key' => '', 'webhook_secret' => ''],
            'mercadopago' => ['access_token' => '', 'public_key' => ''],
        ];

        // Mascarar chaves sensíveis antes de enviar para o frontend
        $maskedConfig = $paymentConfig;
        foreach (['asaas' => ['api_key', 'webhook_token'], 'stripe' => ['secret_key', 'webhook_secret'], 'mercadopago' => ['access_token']] as $gw => $fields) {
            if (isset($maskedConfig[$gw])) {
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

        $data = $validated;
        $currentConfig = $tenant->payment_config ?? [];

        // Não sobrescrever com as máscaras (estrelinhas) vindas do frontend
        foreach (['asaas' => ['api_key', 'webhook_token'], 'stripe' => ['secret_key', 'webhook_secret'], 'mercadopago' => ['access_token']] as $gw => $fields) {
            if (isset($data[$gw])) {
                foreach ($fields as $field) {
                    if (isset($data[$gw][$field]) && str_contains($data[$gw][$field], '***')) {
                        // Restaurar o valor real do banco
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
