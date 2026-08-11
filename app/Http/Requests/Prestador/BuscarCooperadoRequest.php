<?php

namespace App\Http\Requests\Prestador;

use Illuminate\Foundation\Http\FormRequest;

class BuscarCooperadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => 'required|string|min:3',
        ];
    }
}
