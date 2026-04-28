<?php

namespace App\Http\Requests\Saude;

use Illuminate\Foundation\Http\FormRequest;

class StoreReembolsoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'associado_id' => ['required', 'exists:associados,id'],
            'valor_solicitado' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:pendente,aprovado,recusado'],
            'comprovantes' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'associado_id.required' => 'O associado é obrigatório.',
            'valor_solicitado.required' => 'O valor do reembolso é obrigatório.',
            'valor_solicitado.min' => 'O valor não pode ser negativo.',
        ];
    }
}
