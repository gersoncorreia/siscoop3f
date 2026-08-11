<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDependenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'associado_id' => 'required|exists:associados,id',
            'nome' => 'required|string|max:255',
            'cpf' => 'nullable|string|max:14',
            'data_nascimento' => 'nullable|date',
            'parentesco' => 'required|string|max:100',
        ];
    }
}
