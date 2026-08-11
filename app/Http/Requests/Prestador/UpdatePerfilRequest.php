<?php

namespace App\Http\Requests\Prestador;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'tipo' => 'required|string',
            'especialidades' => 'nullable|array',
            'endereco' => 'nullable|string|max:1000',
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255',
        ];
    }
}
