<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanoSaudeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'codigo_ans' => 'nullable|string|max:50',
            'tipo_cobertura' => 'required|string|max:100',
            'mensalidade_base' => 'required|numeric',
        ];
    }
}
