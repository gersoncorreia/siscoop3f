<?php

namespace App\Http\Requests\Dependente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DependenteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Both store and update will use this request
        if ($this->isMethod('post')) {
            return Gate::check('associados.create');
        }
        return Gate::check('associados.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'associado_id' => ['required', 'exists:associados,id'],
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'rg' => ['nullable', 'string', 'max:30'],
            'cartao_sus' => ['nullable', 'string', 'max:30'],
            'data_nascimento' => ['nullable', 'date'],
            'parentesco' => ['required', 'string', 'max:100'],
            'tipo_dependencia' => ['nullable', 'string', 'in:legal,nao_legal'],
            'autoriza_acesso' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'string', 'in:ativo,inativo'],
        ];
    }

    /**
     * Custom messages for validation
     */
    public function messages(): array
    {
        return [
            'associado_id.required' => 'O associado titular é obrigatório.',
            'associado_id.exists' => 'O associado selecionado não é válido.',
            'nome.required' => 'O nome do dependente é obrigatório.',
            'parentesco.required' => 'O grau de parentesco é obrigatório.',
            'status.in' => 'O status informado é inválido.',
        ];
    }
}
