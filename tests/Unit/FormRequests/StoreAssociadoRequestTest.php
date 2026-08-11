<?php

namespace Tests\Unit\FormRequests;

use App\Http\Requests\Associado\StoreAssociadoRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreAssociadoRequestTest extends TestCase
{
    /**
     * Retorna as regras de validação SEM as regras que dependem do banco de dados
     * (ex: unique, exists), para que o teste unitário funcione sem migrar tabelas.
     */
    private function rules(): array
    {
        $rules = (new StoreAssociadoRequest())->rules();

        // Remover regras que dependem de banco de dados para testes unitários puros
        // A regra 'unique:associados,cpf' precisa de uma tabela, então a removemos aqui.
        // Ela será testada em um teste específico de integração quando necessário.
        $rules['cpf'] = array_filter($rules['cpf'], fn($rule) => !str_starts_with((string)$rule, 'unique'));

        return $rules;
    }

    private function messages(): array
    {
        return (new StoreAssociadoRequest())->messages();
    }

    private function validData(): array
    {
        return [
            'nome' => 'João da Silva',
            'cpf' => '123.456.789-00',
            'nascimento' => '1990-01-15',
            'endereco' => 'Rua Teste, 123',
            'status' => 'ativo',
            'data_adesao' => '2026-01-01',
        ];
    }

    /** @test */
    public function test_valid_data_passes()
    {
        $validator = Validator::make($this->validData(), $this->rules(), $this->messages());
        $this->assertTrue($validator->passes(), 'Dados válidos devem passar: ' . json_encode($validator->errors()->toArray()));
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
    public function test_nome_max_255()
    {
        $data = $this->validData();
        $data['nome'] = str_repeat('A', 256);

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
    public function test_status_is_required()
    {
        $data = $this->validData();
        unset($data['status']);

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function test_status_must_be_valid_option()
    {
        $data = $this->validData();
        $data['status'] = 'invalido';

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function test_status_accepts_ativo_inativo_pendente()
    {
        foreach (['ativo', 'inativo', 'pendente'] as $status) {
            $data = $this->validData();
            $data['status'] = $status;

            $validator = Validator::make($data, $this->rules(), $this->messages());
            $this->assertFalse($validator->errors()->has('status'), "Status '{$status}' deveria ser aceito.");
        }
    }

    /** @test */
    public function test_nascimento_must_be_valid_date()
    {
        $data = $this->validData();
        $data['nascimento'] = 'nao-e-data';

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nascimento', $validator->errors()->toArray());
    }

    /** @test */
    public function test_empty_submission_fails_required_fields()
    {
        $validator = Validator::make([], $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());

        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('nome', $errors);
        $this->assertArrayHasKey('cpf', $errors);
        $this->assertArrayHasKey('status', $errors);
    }

    /** @test */
    public function test_custom_messages_are_in_portuguese()
    {
        $messages = $this->messages();
        $this->assertStringContainsString('obrigatório', $messages['nome.required']);
        $this->assertStringContainsString('CPF', $messages['cpf.required']);
    }

}
