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
            'nascimento' => '1985-06-20',
            'endereco' => 'Av. Brasil, 500',
            'qtd_quotas' => 5,
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
    public function test_cpf_max_14_chars()
    {
        $data = $this->validData();
        $data['cpf'] = '123456789012345'; // 15 chars

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
    }

    /** @test */
    public function test_qtd_quotas_must_be_positive_integer()
    {
        $data = $this->validData();
        $data['qtd_quotas'] = 0;

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('qtd_quotas', $validator->errors()->toArray());
    }

    /** @test */
    public function test_dependente_nome_is_required()
    {
        $data = $this->validData();
        $data['dependentes'] = [
            ['parentesco' => 'filho'], // Missing nome
        ];

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dependentes.0.nome', $validator->errors()->toArray());
    }

    /** @test */
    public function test_dependente_parentesco_is_required()
    {
        $data = $this->validData();
        $data['dependentes'] = [
            ['nome' => 'Pedro'], // Missing parentesco
        ];

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dependentes.0.parentesco', $validator->errors()->toArray());
    }

    /** @test */
    public function test_empty_submission_fails_on_required_fields()
    {
        $validator = Validator::make([], $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nome', $validator->errors()->toArray());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
    }

    /** @test */
    public function test_authorize_is_public()
    {
        $request = new SolicitarAdesaoRequest();
        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function test_custom_messages_in_portuguese()
    {
        $messages = $this->messages();
        $this->assertStringContainsString('obrigatório', $messages['nome.required']);
        $this->assertStringContainsString('obrigatório', $messages['dependentes.*.nome.required']);
    }

    /** @test */
    public function test_rules_include_unique_cpf_constraint()
    {
        $originalRules = (new SolicitarAdesaoRequest())->rules();
        $this->assertTrue(
            in_array('unique:associados,cpf', $originalRules['cpf']),
            'A regra de CPF deve conter unique:associados,cpf.'
        );
    }
}
