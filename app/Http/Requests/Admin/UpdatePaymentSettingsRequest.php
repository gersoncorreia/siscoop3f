<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active_gateway' => 'required|string|in:interno,pix_manual,presencial,asaas,stripe,mercadopago',
            'enabled_gateways' => 'nullable|array',
            'pix_manual' => 'nullable|array',
            'pix_manual.enabled' => 'nullable|boolean',
            'pix_manual.tipo_chave' => 'nullable|string|in:cpf,cnpj,email,telefone,evp',
            'pix_manual.chave_pix' => 'nullable|string|max:255',
            'pix_manual.favorecido' => 'nullable|string|max:255',
            'pix_manual.instrucoes' => 'nullable|string|max:1000',
            'pix_manual.qrcode_image' => 'nullable|image|max:4096',
            'presencial' => 'nullable|array',
            'presencial.enabled' => 'nullable|boolean',
            'presencial.aceita_dinheiro' => 'nullable|boolean',
            'presencial.aceita_maquininha' => 'nullable|boolean',
            'presencial.instrucoes' => 'nullable|string|max:1000',
            'asaas' => 'nullable|array',
            'asaas.enabled' => 'nullable|boolean',
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
            'taxas_adesao' => 'nullable|array',
            'taxas_adesao.*.id' => 'nullable|string',
            'taxas_adesao.*.titulo' => 'nullable|string|max:255',
            'taxas_adesao.*.valor' => 'nullable|numeric|min:0',
            'taxas_adesao.*.ativo' => 'nullable|boolean',
            'taxa_dependente_legal' => 'nullable|numeric|min:0',
            'taxa_dependente_nao_legal' => 'nullable|numeric|min:0',
            'cota_capital_config' => 'nullable|array',
            'cota_capital_config.meta_total' => 'nullable|numeric|min:0',
            'cota_capital_config.valor_quota' => 'nullable|numeric|min:0',
            'cota_capital_config.qtd_quotas' => 'nullable|integer|min:1',
            'cota_capital_config.aporte_inicial' => 'nullable|numeric|min:0',
        ];
    }
}
