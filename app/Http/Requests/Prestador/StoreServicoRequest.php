<?php

namespace App\Http\Requests\Prestador;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'valor_acordado' => 'nullable|numeric|min:0',
            'ativo' => 'boolean',
        ];
    }
}
