<?php

namespace App\Http\Requests\Cooperado;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCooperadoDependenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'cpf' => 'nullable|string|max:14',
            'rg' => 'nullable|string|max:30',
            'cartao_sus' => 'nullable|string|max:30',
            'data_nascimento' => 'nullable|date',
            'parentesco' => 'required|string|max:100',
            'tipo_dependencia' => 'nullable|string|in:legal,nao_legal',
            'autoriza_acesso' => 'nullable|boolean',
            'email' => 'nullable|email|max:255',
        ];
    }
}
