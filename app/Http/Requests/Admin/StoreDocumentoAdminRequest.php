<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentoAdminRequest extends FormRequest
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
        ];
    }
}
