<?php

namespace App\Http\Requests\Financeiro;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class LancamentoFinanceiroRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return Gate::check('financeiro.create');
        }
        return Gate::check('financeiro.update');
    }

    public function rules(): array
    {
        return [
            'associado_id' => ['nullable', 'exists:associados,id'],
            'tipo' => ['required', 'in:receita,despesa'],
            'valor' => ['required', 'numeric', 'min:0'], // Security: Cannot be negative
            'data_vencimento' => ['required', 'date'],
            'status' => ['required', 'in:pago,pendente,atrasado'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required' => 'O tipo do lançamento é obrigatório.',
            'tipo.in' => 'O tipo deve ser receita ou despesa.',
            'valor.required' => 'O valor é obrigatório.',
            'valor.numeric' => 'O valor deve ser um número válido.',
            'valor.min' => 'O valor não pode ser negativo.',
            'data_vencimento.required' => 'A data de vencimento é obrigatória.',
            'status.in' => 'O status informado é inválido.',
        ];
    }
}
