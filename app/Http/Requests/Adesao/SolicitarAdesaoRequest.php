<?php

namespace App\Http\Requests\Adesao;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Associado;
use Illuminate\Validation\Validator;

class SolicitarAdesaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Preparar dados para validação (sanitizar Nome, CPF e e-mail)
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('nome')) {
            $this->merge([
                'nome' => preg_replace('/\s+/', ' ', trim((string) $this->nome)),
            ]);
        }

        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/\D/', '', (string) $this->cpf),
            ]);
        }

        if ($this->has('email')) {
            $this->merge([
                'email' => mb_strtolower(trim((string) $this->email)),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => [
                'required', 
                'string', 
                'min:5', 
                'max:255', 
                'regex:/^[\p{L}\s\-\'\.]+$/u',
                function ($attribute, $value, $fail) {
                    $parts = array_filter(explode(' ', trim((string) $value)));
                    if (count($parts) < 2) {
                        $fail('Por favor, informe seu nome completo (nome e sobrenome).');
                    }
                }
            ],
            'cpf' => [
                'required', 
                'string', 
                'size:11',
                function ($attribute, $value, $fail) {
                    if (!$this->validarCpfMatematico((string) $value)) {
                        $fail('O CPF informado é inválido. Por favor, verifique os números digitados.');
                    }
                }
            ],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'matricula' => ['nullable', 'string', 'max:50'],
            'nascimento' => ['nullable', 'date'],
            'qtd_quotas' => ['nullable', 'integer', 'min:1'],

            // Novos campos de controle financeiro
            'forma_pagamento_adesao' => ['nullable', 'string'],
            'autoriza_desconto_folha' => (tenant() && tenant()->tipo === 'fundacao') ? ['nullable', 'boolean'] : ['required', 'accepted'],

            // Dependentes
            'dependentes' => ['nullable', 'array'],
            'dependentes.*.nome' => ['nullable', 'string', 'max:255'],
            'dependentes.*.cpf' => ['nullable', 'string', 'max:14'],
            'dependentes.*.parentesco' => ['nullable', 'string', 'max:100'],
            'dependentes.*.tipo_dependencia' => ['nullable', 'string', 'in:legal,nao_legal'],
            'dependentes.*.autoriza_acesso' => ['nullable', 'boolean'],
            'dependentes.*.email' => ['nullable', 'required_if:dependentes.*.autoriza_acesso,true', 'email', 'max:255', 'unique:users,email'],

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

    /**
     * Validação customizada pós-regras para tratar a unicidade de campos criptografados (Nome, CPF, E-mail).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $inputNomeNorm = $this->normalizarTexto((string) $this->nome);
            $inputCpf = preg_replace('/\D/', '', (string) $this->cpf);
            $inputEmail = mb_strtolower(trim((string) $this->email));

            if (!$inputCpf && !$inputEmail && !$inputNomeNorm) {
                return;
            }

            // Descriptografa e verifica se o Nome, CPF ou E-mail já existem na base do tenant
            $associados = Associado::all();

            $genericMessage = 'Identificamos que estes dados já constam em nossos registros. Para verificar a situação da sua solicitação ou regularizar seu cadastro, por favor, entre em contato diretamente com a equipe de atendimento.';

            $nomeExists = false;
            $cpfExists = false;
            $emailExists = false;

            foreach ($associados as $associado) {
                $dbNomeNorm = $this->normalizarTexto((string) $associado->nome);
                $dbCpf = preg_replace('/\D/', '', (string) $associado->cpf);
                $dbEmail = mb_strtolower(trim((string) $associado->email));

                if ($inputNomeNorm && strlen($inputNomeNorm) > 4 && $dbNomeNorm === $inputNomeNorm) {
                    $nomeExists = true;
                }
                if ($inputCpf && $dbCpf === $inputCpf) {
                    $cpfExists = true;
                }
                if ($inputEmail && $dbEmail === $inputEmail) {
                    $emailExists = true;
                }
            }

            if ($nomeExists) {
                $validator->errors()->add('nome', $genericMessage);
            }
            if ($cpfExists) {
                $validator->errors()->add('cpf', $genericMessage);
            }
            if ($emailExists) {
                $validator->errors()->add('email', $genericMessage);
            }
        });
    }

    /**
     * Valida o algoritmo dos 2 dígitos verificadores do CPF oficial (Receita Federal)
     */
    private function validarCpfMatematico(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        // Rejeita sequências de dígitos iguais (ex: 11111111111)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Valida dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += (int) $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int) $cpf[$c] !== $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Auxiliar para remover acentos e normalizar textos para comparação
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));
        $unaccented = preg_replace(
            ['/[áàãâä]/u', '/[éèêë]/u', '/[íìîï]/u', '/[óòõôö]/u', '/[úùûü]/u', '/[ç]/u'],
            ['a', 'e', 'i', 'o', 'u', 'c'],
            $texto
        );
        return preg_replace('/\s+/', ' ', $unaccented ?? '');
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O seu nome completo é obrigatório.',
            'nome.regex' => 'O nome não deve conter números ou caracteres especiais.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve possuir exatamente 11 dígitos.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.unique' => 'Este e-mail já consta em nossos registros de usuários.',
            'telefones.required' => 'É necessário informar pelo menos um telefone para contato.',
            'telefones.min' => 'É necessário informar pelo menos um telefone para contato.',
            'telefones.*.numero.required' => 'O número do telefone é obrigatório se a seção for preenchida.',
            'autoriza_desconto_folha.required' => 'A autorização de desconto em folha é obrigatória.',
            'autoriza_desconto_folha.accepted' => 'Você precisa autorizar o desconto em folha de pagamento para finalizar a adesão.',
            'dependentes.*.email.required_if' => 'O e-mail é obrigatório quando o acesso ao portal é autorizado para o dependente.',
            'dependentes.*.email.unique' => 'Um dos e-mails de dependente já está cadastrado em nosso sistema.',
        ];
    }
}
