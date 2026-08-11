<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReembolsoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'associado_id' => 'required|exists:associados,id',
            'valor_solicitado' => 'required|numeric',
            'status' => 'required|string',
            'descricao' => 'nullable|string|max:1000',
        ];
    }
}
