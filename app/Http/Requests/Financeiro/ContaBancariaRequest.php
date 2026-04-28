<?php

namespace App\Http\Requests\Financeiro;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ContaBancariaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return Gate::check('financeiro.create');
        }
        return Gate::check('financeiro.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'banco' => ['required', 'string', 'max:255'],
            'agencia' => ['required', 'string', 'max:20'],
            'conta' => ['required', 'string', 'max:20'],
            'saldo' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'banco.required' => 'O nome/número do banco é obrigatório.',
            'agencia.required' => 'A agência é obrigatória.',
            'conta.required' => 'O número da conta é obrigatório.',
            'saldo.required' => 'O saldo inicial é obrigatório.',
            'saldo.min' => 'O saldo não pode ser negativo no cadastro.',
        ];
    }
}
