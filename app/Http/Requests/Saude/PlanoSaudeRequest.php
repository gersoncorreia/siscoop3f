<?php

namespace App\Http\Requests\Saude;

use Illuminate\Foundation\Http\FormRequest;

class PlanoSaudeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Simplificado para MVP, mas idealmente seria Gate::check('saude.update')
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'coberturas' => ['nullable', 'array'],
            'faixas_etarias' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do plano de saúde é obrigatório.',
        ];
    }
}
