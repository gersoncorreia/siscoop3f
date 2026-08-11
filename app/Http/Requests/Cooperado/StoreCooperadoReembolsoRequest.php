<?php

namespace App\Http\Requests\Cooperado;

use Illuminate\Foundation\Http\FormRequest;

class StoreCooperadoReembolsoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'valor_solicitado' => 'required|numeric|min:0.01',
            'descricao' => 'required|string|max:1000',
            'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }
}
