<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class UpdateTenantUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');
        $isRestricted = $usuario->hasRole(['Associado', 'Prestador']);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $usuario->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ];

        if (!$isRestricted) {
            $rules['role'] = 'required|exists:roles,name|not_in:Associado,Prestador';
            $rules['permissions'] = 'array';
        }

        return $rules;
    }
}
