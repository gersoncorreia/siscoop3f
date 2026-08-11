<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConvenioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome_empresa' => 'required|string|max:255',
            'ramo' => 'nullable|string|max:255',
            'desconto' => 'nullable|string|max:255',
            'contato' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
        ];
    }
}
