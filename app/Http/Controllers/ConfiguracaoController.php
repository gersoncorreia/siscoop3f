<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracoes/Index', [
            'tenant' => tenant()
        ]);
    }

    public function update(Request $request)
    {
        $tenant = tenant();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'primary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

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

        $tenant->update($data);

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
    public function updatePagamentos(Request $request)
    {
        $tenant = tenant();

        $validated = $request->validate([
            'active_gateway' => 'required|string|in:interno,asaas,stripe,mercadopago',
            'asaas' => 'nullable|array',
            'asaas.api_key' => 'nullable|string|max:500',
            'asaas.environment' => 'nullable|string|in:sandbox,production',
            'asaas.webhook_token' => 'nullable|string|max:500',
            'stripe' => 'nullable|array',
            'stripe.public_key' => 'nullable|string|max:500',
            'stripe.secret_key' => 'nullable|string|max:500',
            'stripe.webhook_secret' => 'nullable|string|max:500',
            'mercadopago' => 'nullable|array',
            'mercadopago.access_token' => 'nullable|string|max:500',
            'mercadopago.public_key' => 'nullable|string|max:500',
        ]);

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
}
