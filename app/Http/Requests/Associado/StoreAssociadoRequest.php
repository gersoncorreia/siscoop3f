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
            'cpf' => ['required', 'string', 'max:14'],
            'nascimento' => ['nullable', 'date'],
            'endereco' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:ativo,inativo,pendente'],
            'data_adesao' => ['nullable', 'date'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cpf = $this->input('cpf');
            if ($cpf) {
                $exists = \App\Models\Associado::all()->contains(function ($associado) use ($cpf) {
                    return $associado->cpf === $cpf;
                });

                if ($exists) {
                    $validator->errors()->add('cpf', 'Este CPF já está cadastrado em nossa base.');
                }
            }
        });
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
