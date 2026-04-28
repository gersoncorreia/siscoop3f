<?php

namespace App\Http\Requests\Adesao;

use Illuminate\Foundation\Http\FormRequest;

class SolicitarAdesaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Adesão form is public, so true
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', 'unique:associados,cpf'],
            'email' => ['required', 'email', 'max:255', 'unique:associados,email'],
            'matricula' => ['nullable', 'string', 'max:50'],
            'nascimento' => ['nullable', 'date'],
            'qtd_quotas' => ['nullable', 'integer', 'min:1'],
            'dependentes' => ['nullable', 'array'],
            'dependentes.*.nome' => ['nullable', 'string', 'max:255'],
            'dependentes.*.parentesco' => ['nullable', 'string', 'max:100'],
            'telefones' => ['required', 'array', 'min:1'],
            'telefones.*.numero' => ['required', 'string', 'max:20'],
            'telefones.*.tipo' => ['nullable', 'string', 'max:50'],
            'enderecos' => ['nullable', 'array'],
            'enderecos.*.tipo' => ['nullable', 'string', 'max:50'],
            'enderecos.*.logradouro' => ['nullable', 'string', 'max:255'],
            'enderecos.*.numero' => ['nullable', 'string', 'max:20'],
            'enderecos.*.bairro' => ['nullable', 'string', 'max:100'],
            'enderecos.*.cidade' => ['nullable', 'string', 'max:100'],
            'enderecos.*.uf' => ['nullable', 'string', 'size:2'],
            'enderecos.*.cep' => ['nullable', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O seu nome completo é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Este CPF já consta nos registros. Por favor, faça login ou entre em contato.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.unique' => 'Este e-mail já consta nos registros.',
            'telefones.required' => 'É necessário informar pelo menos um telefone para contato.',
            'telefones.min' => 'É necessário informar pelo menos um telefone para contato.',
            'telefones.*.numero.required' => 'O número do telefone é obrigatório se a seção for preenchida.',
        ];
    }
}
