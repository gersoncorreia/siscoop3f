<?php

namespace Tests\Unit\FormRequests;

use App\Http\Requests\Financeiro\LancamentoFinanceiroRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LancamentoFinanceiroRequestTest extends TestCase
{
    /**
     * Retorna as regras SEM regras que dependem do banco (exists).
     */
    private function rules(): array
    {
        $rules = (new LancamentoFinanceiroRequest())->rules();
        // Remove 'exists:associados,id' se presente
        if (isset($rules['associado_id'])) {
            $rules['associado_id'] = array_filter($rules['associado_id'], fn($rule) => !str_starts_with((string)$rule, 'exists'));
        }
        return $rules;
    }

    private function messages(): array
    {
        return (new LancamentoFinanceiroRequest())->messages();
    }

    private function validData(): array
    {
        return [
            'tipo' => 'receita',
            'valor' => 150.50,
            'data_vencimento' => '2026-04-01',
            'status' => 'pendente',
        ];
    }

    /** @test */
    public function test_valid_data_passes()
    {
        $validator = Validator::make($this->validData(), $this->rules(), $this->messages());
        $this->assertTrue($validator->passes(), json_encode($validator->errors()->toArray()));
    }

    /** @test */
    public function test_valor_cannot_be_negative()
    {
        $data = $this->validData();
        $data['valor'] = -150.00;

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('valor', $validator->errors()->toArray());
    }

    /** @test */
    public function test_valor_must_be_numeric()
    {
        $data = $this->validData();
        $data['valor'] = 'abc';

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('valor', $validator->errors()->toArray());
    }

    /** @test */
    public function test_tipo_must_be_receita_or_despesa()
    {
        $data = $this->validData();
        $data['tipo'] = 'tipo_invalido';

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tipo', $validator->errors()->toArray());
    }

    /** @test */
    public function test_tipo_accepts_receita_and_despesa()
    {
        foreach (['receita', 'despesa'] as $tipo) {
            $data = $this->validData();
            $data['tipo'] = $tipo;

            $validator = Validator::make($data, $this->rules(), $this->messages());
            $this->assertFalse($validator->errors()->has('tipo'), "Tipo '{$tipo}' deveria ser aceito.");
        }
    }

    /** @test */
    public function test_status_must_be_valid()
    {
        $data = $this->validData();
        $data['status'] = 'anulado'; // Invalid

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function test_status_accepts_valid_options()
    {
        foreach (['pago', 'pendente', 'atrasado'] as $status) {
            $data = $this->validData();
            $data['status'] = $status;

            $validator = Validator::make($data, $this->rules(), $this->messages());
            $this->assertFalse($validator->errors()->has('status'), "Status '{$status}' deveria ser aceito.");
        }
    }

    /** @test */
    public function test_data_vencimento_is_required()
    {
        $data = $this->validData();
        unset($data['data_vencimento']);

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('data_vencimento', $validator->errors()->toArray());
    }

    /** @test */
    public function test_data_vencimento_must_be_a_date()
    {
        $data = $this->validData();
        $data['data_vencimento'] = 'nao-e-data';

        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('data_vencimento', $validator->errors()->toArray());
    }

    /** @test */
    public function test_empty_submission_fails()
    {
        $validator = Validator::make([], $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('tipo', $errors);
        $this->assertArrayHasKey('valor', $errors);
        $this->assertArrayHasKey('data_vencimento', $errors);
        $this->assertArrayHasKey('status', $errors);
    }

    /** @test */
    public function test_custom_messages_in_portuguese()
    {
        $messages = $this->messages();
        $this->assertStringContainsString('obrigatório', $messages['tipo.required']);
        $this->assertStringContainsString('negativo', $messages['valor.min']);
    }
}
