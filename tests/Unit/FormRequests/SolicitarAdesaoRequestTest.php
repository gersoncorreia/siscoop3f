<?php

namespace Tests\Unit\FormRequests;

use App\Http\Requests\Adesao\SolicitarAdesaoRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SolicitarAdesaoRequestTest extends TestCase
{
    /**
     * Retorna as regras SEM regras que dependem do banco (unique, exists).
     */
    private function rules(): array
    {
        $rules = (new SolicitarAdesaoRequest())->rules();
        $rules['cpf'] = array_filter($rules['cpf'], fn($rule) => !str_starts_with((string)$rule, 'unique'));
        $rules['email'] = array_filter($rules['email'], fn($rule) => !str_starts_with((string)$rule, 'unique'));
        return $rules;
    }

    private function messages(): array
    {
        return (new SolicitarAdesaoRequest())->messages();
    }

    private function validData(): array
    {
        return [
            'nome' => 'Maria da Silva',
            'cpf' => '987.654.321-00',
            'email' => 'maria@gmail.com',
            'nascimento' => '1985-06-20',
            'endereco' => 'Av. Brasil, 500',
            'qtd_quotas' => 5,
            'forma_pagamento_adesao' => 'boleto',
            'autoriza_desconto_folha' => true,
            'telefones' => [
                ['numero' => '11999999999', 'tipo' => 'celular']
            ],
            'dependentes' => [
                ['nome' => 'Pedro Silva', 'parentesco' => 'filho'],
            ],
        ];
    }

    /** @test */
    public function test_valid_data_passes()
    {
        $validator = Validator::make($this->validData(), $this->rules(), $this->messages());
        $this->assertTrue($validator->passes(), json_encode($validator->errors()->toArray()));
    }

    /** @test */
    public function test_nome_is_required()
    {
        $data = $this->validData();
        unset($data['nome']);

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nome', $validator->errors()->toArray());
    }

    /** @test */
    public function test_cpf_is_required()
    {
        $data = $this->validData();
        unset($data['cpf']);

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
    }

    /** @test */
    public function test_empty_submission_fails_on_required_fields()
    {
        $validator = Validator::make([], $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nome', $validator->errors()->toArray());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('forma_pagamento_adesao', $validator->errors()->toArray());
        $this->assertArrayHasKey('telefones', $validator->errors()->toArray());
    }
}
