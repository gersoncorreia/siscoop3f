<?php

namespace Tests\Unit\FormRequests;

use App\Http\Requests\Financeiro\ContaBancariaRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContaBancariaRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new ContaBancariaRequest())->rules();
    }

    private function messages(): array
    {
        return (new ContaBancariaRequest())->messages();
    }

    private function validData(): array
    {
        return [
            'banco' => 'Banco do Brasil',
            'agencia' => '1234-5',
            'conta' => '12345-6',
            'saldo' => 1000.00,
        ];
    }

    /** @test */
    public function test_valid_data_passes()
    {
        $validator = Validator::make($this->validData(), $this->rules(), $this->messages());
        $this->assertTrue($validator->passes(), json_encode($validator->errors()->toArray()));
    }

    /** @test */
    public function test_banco_is_required()
    {
        $data = $this->validData();
        unset($data['banco']);
        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('banco', $validator->errors()->toArray());
    }

    /** @test */
    public function test_agencia_is_required()
    {
        $data = $this->validData();
        unset($data['agencia']);
        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('agencia', $validator->errors()->toArray());
    }

    /** @test */
    public function test_conta_is_required()
    {
        $data = $this->validData();
        unset($data['conta']);
        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('conta', $validator->errors()->toArray());
    }

    /** @test */
    public function test_saldo_cannot_be_negative()
    {
        $data = $this->validData();
        $data['saldo'] = -500;
        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('saldo', $validator->errors()->toArray());
    }

    /** @test */
    public function test_saldo_must_be_numeric()
    {
        $data = $this->validData();
        $data['saldo'] = 'abc';
        $validator = Validator::make($data, $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('saldo', $validator->errors()->toArray());
    }

    /** @test */
    public function test_empty_submission_fails()
    {
        $validator = Validator::make([], $this->rules(), $this->messages());
        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('banco', $errors);
        $this->assertArrayHasKey('agencia', $errors);
        $this->assertArrayHasKey('conta', $errors);
        $this->assertArrayHasKey('saldo', $errors);
    }
}
