<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContaBancariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'banco' => 'required|string|max:255',
            'agencia' => 'required|string|max:50',
            'conta' => 'required|string|max:50',
            'operacao' => 'nullable|string|max:10',
            'tipo' => 'required|string|max:50',
            'saldo_inicial' => 'required|numeric',
        ];
    }
}
