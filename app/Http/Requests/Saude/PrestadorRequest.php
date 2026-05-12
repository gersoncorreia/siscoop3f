<?php

namespace App\Http\Requests\Saude;

use Illuminate\Foundation\Http\FormRequest;

class PrestadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string'],
            'nome' => ['required', 'string', 'max:255'],
            'especialidades' => ['nullable', 'array'],
            'endereco' => ['nullable', 'string', 'max:1000'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'O tipo de prestador é obrigatório (Ex: Clínica, Hospital).',
            'nome.required' => 'O nome do prestador é obrigatório.',
        ];
    }
}
