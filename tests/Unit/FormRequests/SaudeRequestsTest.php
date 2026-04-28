<?php

namespace Tests\Unit\FormRequests;

use App\Http\Requests\Saude\PlanoSaudeRequest;
use App\Http\Requests\Saude\PrestadorRequest;
use App\Http\Requests\Saude\StoreReembolsoRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SaudeRequestsTest extends TestCase
{
    /**
     * Helper para remover regras que dependem do banco de dados.
     */
    private function stripDbRules(array $rules): array
    {
        foreach ($rules as $field => &$fieldRules) {
            if (is_array($fieldRules)) {
                $fieldRules = array_values(array_filter($fieldRules, function ($rule) {
                    $ruleStr = (string)$rule;
                    return !str_starts_with($ruleStr, 'unique') && !str_starts_with($ruleStr, 'exists');
                }));
            }
        }
        return $rules;
    }

    // ========== PlanoSaudeRequest ==========

    /** @test */
    public function test_plano_saude_requires_nome()
    {
        $rules = (new PlanoSaudeRequest())->rules();
        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nome', $validator->errors()->toArray());
    }

    /** @test */
    public function test_plano_saude_valid_data_passes()
    {
        $rules = (new PlanoSaudeRequest())->rules();
        $validator = Validator::make([
            'nome' => 'Plano Premium',
            'coberturas' => ['consultas', 'exames'],
            'faixas_etarias' => ['0-18', '19-35'],
        ], $rules);
        $this->assertTrue($validator->passes(), json_encode($validator->errors()->toArray()));
    }

    /** @test */
    public function test_plano_saude_nome_max_255()
    {
        $rules = (new PlanoSaudeRequest())->rules();
        $validator = Validator::make(['nome' => str_repeat('A', 256)], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nome', $validator->errors()->toArray());
    }

    /** @test */
    public function test_plano_saude_coberturas_must_be_array()
    {
        $rules = (new PlanoSaudeRequest())->rules();
        $validator = Validator::make(['nome' => 'Teste', 'coberturas' => 'string-invalida'], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('coberturas', $validator->errors()->toArray());
    }

    // ========== PrestadorRequest ==========

    /** @test */
    public function test_prestador_requires_tipo_and_nome()
    {
        $rules = (new PrestadorRequest())->rules();
        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('tipo', $errors);
        $this->assertArrayHasKey('nome', $errors);
    }

    /** @test */
    public function test_prestador_valid_data_passes()
    {
        $rules = (new PrestadorRequest())->rules();
        $validator = Validator::make([
            'tipo' => 'Hospital',
            'nome' => 'Hospital São Lucas',
            'especialidades' => ['Cardiologia', 'Ortopedia'],
            'endereco' => 'Rua da Saúde, 100',
        ], $rules);
        $this->assertTrue($validator->passes(), json_encode($validator->errors()->toArray()));
    }

    /** @test */
    public function test_prestador_nome_max_255()
    {
        $rules = (new PrestadorRequest())->rules();
        $validator = Validator::make([
            'tipo' => 'Clínica',
            'nome' => str_repeat('X', 256),
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nome', $validator->errors()->toArray());
    }

    /** @test */
    public function test_prestador_endereco_max_1000()
    {
        $rules = (new PrestadorRequest())->rules();
        $validator = Validator::make([
            'tipo' => 'Clínica',
            'nome' => 'Clínica Teste',
            'endereco' => str_repeat('Z', 1001),
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('endereco', $validator->errors()->toArray());
    }

    // ========== StoreReembolsoRequest ==========

    /** @test */
    public function test_reembolso_requires_associado_and_valor()
    {
        $rules = $this->stripDbRules((new StoreReembolsoRequest())->rules());
        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('associado_id', $errors);
        $this->assertArrayHasKey('valor_solicitado', $errors);
        $this->assertArrayHasKey('status', $errors);
    }

    /** @test */
    public function test_reembolso_valor_cannot_be_negative()
    {
        $rules = $this->stripDbRules((new StoreReembolsoRequest())->rules());
        $validator = Validator::make([
            'associado_id' => 1,
            'valor_solicitado' => -100,
            'status' => 'pendente',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('valor_solicitado', $validator->errors()->toArray());
    }

    /** @test */
    public function test_reembolso_status_must_be_valid()
    {
        $rules = $this->stripDbRules((new StoreReembolsoRequest())->rules());
        $validator = Validator::make([
            'associado_id' => 1,
            'valor_solicitado' => 100,
            'status' => 'cancelado', // Invalid
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function test_reembolso_status_accepts_valid_options()
    {
        $rules = $this->stripDbRules((new StoreReembolsoRequest())->rules());
        foreach (['pendente', 'aprovado', 'recusado'] as $status) {
            $validator = Validator::make([
                'associado_id' => 1,
                'valor_solicitado' => 200,
                'status' => $status,
            ], $rules);
            $this->assertFalse($validator->errors()->has('status'), "Status '{$status}' deveria ser aceito.");
        }
    }

    /** @test */
    public function test_reembolso_comprovantes_must_be_array()
    {
        $rules = $this->stripDbRules((new StoreReembolsoRequest())->rules());
        $validator = Validator::make([
            'associado_id' => 1,
            'valor_solicitado' => 100,
            'status' => 'pendente',
            'comprovantes' => 'nao-e-array',
        ], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('comprovantes', $validator->errors()->toArray());
    }

    /** @test */
    public function test_reembolso_rules_include_exists_constraint()
    {
        $originalRules = (new StoreReembolsoRequest())->rules();
        $this->assertTrue(
            in_array('exists:associados,id', $originalRules['associado_id']),
            'A regra associado_id deve conter exists:associados,id.'
        );
    }
}
