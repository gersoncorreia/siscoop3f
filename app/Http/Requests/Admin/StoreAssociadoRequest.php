<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssociadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|max:14',
            'email' => 'required|email|max:255',
            'status' => 'required|string|in:ativo,inativo,pendente',
        ];
    }
}
