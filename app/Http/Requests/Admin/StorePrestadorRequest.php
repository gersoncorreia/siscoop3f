<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePrestadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'cnpj_cpf' => 'required|string|max:20',
            'tipo' => 'required|string|max:100',
            'endereco' => 'nullable|string|max:500',
            'especialidades' => 'nullable|array',
        ];
    }
}
