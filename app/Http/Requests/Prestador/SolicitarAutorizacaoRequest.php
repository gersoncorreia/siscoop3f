<?php

namespace App\Http\Requests\Prestador;

use Illuminate\Foundation\Http\FormRequest;

class SolicitarAutorizacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identificacao' => 'required|string',
            'servico_id' => 'required|exists:prestador_servicos,id',
            'observacoes' => 'nullable|string|max:1000',
        ];
    }
}
