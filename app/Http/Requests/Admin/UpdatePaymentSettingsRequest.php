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
        ];
    }
}
