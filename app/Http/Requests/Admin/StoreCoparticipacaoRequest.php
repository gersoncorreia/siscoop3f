<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoparticipacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'valor_total' => 'required|numeric',
            'percentual' => 'required|numeric|min:0|max:100',
            'status' => 'required|string',
        ];
    }
}
