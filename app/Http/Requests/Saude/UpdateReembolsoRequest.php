<?php

namespace App\Http\Requests\Saude;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReembolsoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pendente,aprovado,recusado'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'Status inválido.',
        ];
    }
}
