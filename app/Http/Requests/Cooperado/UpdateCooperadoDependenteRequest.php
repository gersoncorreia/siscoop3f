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
            'data_nascimento' => 'nullable|date',
            'parentesco' => 'required|string|max:100',
        ];
    }
}
