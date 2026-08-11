<?php

namespace App\Http\Requests\Cooperado;

use Illuminate\Foundation\Http\FormRequest;

class StoreCooperadoDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'dependente_id' => 'nullable|exists:dependentes,id',
        ];
    }
}
