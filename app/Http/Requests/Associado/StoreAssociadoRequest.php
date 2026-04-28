<?php

namespace App\Http\Requests\Associado;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreAssociadoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('associados.create');
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
            'nascimento' => ['nullable', 'date'],
            'endereco' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:ativo,inativo,pendente'],
            'data_adesao' => ['nullable', 'date'],
        ];
    }

    /**
     * Get customized error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome completo é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Este CPF já está cadastrado em nossa base.',
            'status.in' => 'O status informado é inválido.',
        ];
    }
}
