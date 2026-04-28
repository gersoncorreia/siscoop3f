<?php

namespace Tests\Unit\FormRequests;

use App\Http\Requests\Dependente\DependenteRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DependenteRequestTest extends TestCase
{
    /**
     * Retorna as regras SEM regras que dependem do banco (exists).
     */
    private function rules(): array
    {
        $rules = (new DependenteRequest())->rules();
        // Remove 'exists:associados,id' que necessita da tabela no banco
        $rules['associado_id'] = array_filter($rules['associado_id'], fn($rule) => !str_starts_with((string)$rule, 'exists'));
        return $rules;
    }

    private function messages(): array
    {
        return (new DependenteRequest())->messages();
    }

    /** @test */
    public function test_nome_is_required()
    {
        $validator = Validator::make([], $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nome', $validator->errors()->toArray());
    }

    /** @test */
    public function test_nome_max_255()
    {
        $data = ['nome' => str_repeat('X', 256), 'associado_id' => 1, 'parentesco' => 'filho', 'status' => 'ativo'];
        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nome', $validator->errors()->toArray());
    }

    /** @test */
    public function test_parentesco_is_required()
    {
        $validator = Validator::make([], $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('parentesco', $validator->errors()->toArray());
    }

    /** @test */
    public function test_status_must_be_ativo_or_inativo()
    {
        $data = ['associado_id' => 1, 'nome' => 'Teste', 'parentesco' => 'filho', 'status' => 'invalido'];
        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function test_status_accepts_ativo_and_inativo()
    {
        foreach (['ativo', 'inativo'] as $status) {
            $data = ['associado_id' => 1, 'nome' => 'Teste', 'parentesco' => 'filho', 'status' => $status];
            $validator = Validator::make($data, $this->rules(), $this->messages());
            $this->assertFalse($validator->errors()->has('status'), "Status '{$status}' deveria ser aceito.");
        }
    }

    /** @test */
    public function test_associado_id_is_required()
    {
        $validator = Validator::make([], $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('associado_id', $validator->errors()->toArray());
    }

    /** @test */
    public function test_rules_include_exists_constraint()
    {
        $originalRules = (new DependenteRequest())->rules();
        $this->assertTrue(
            in_array('exists:associados,id', $originalRules['associado_id']),
            'A regra associado_id deve conter exists:associados,id.'
        );
    }

    /** @test */
    public function test_custom_messages_in_portuguese()
    {
        $messages = $this->messages();
        $this->assertStringContainsString('obrigatório', $messages['nome.required']);
        $this->assertStringContainsString('obrigatório', $messages['parentesco.required']);
    }
}
