<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCotaCapitalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'associado_id' => 'required|exists:associados,id',
            'valor_quota' => 'required|numeric',
            'qtd_quotas' => 'required|integer',
            'saldo' => 'required|numeric',
            'tipo_movimento' => 'nullable|string',
        ];
    }
}
