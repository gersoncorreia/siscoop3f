<?php

use App\Models\Associado;
use App\Models\ContaBancaria;
use App\Models\LancamentoFinanceiro;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TenantTestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = $this->createTenantUser();
    $this->adminUser->assignRole('Administrador Cooperativa');
    
    $this->associado = Associado::create([
        'nome' => 'Titular Financeiro',
        'cpf' => '00011122299',
        'email' => 'fin@teste.com',
        'status' => 'ativo',
        'forma_pagamento_adesao' => 'PIX'
    ]);
});

it('admin pode ver o dashboard financeiro', function () {
    ContaBancaria::create([
        'banco' => 'Banco do Brasil',
        'agencia' => '1234',
        'conta' => '5678-9',
        'saldo' => 5000.00
    ]);
    
    $response = $this->tenantGet('/financeiro', $this->adminUser);
    
    $response->assertStatus(200);
});

it('admin pode ver tela de conciliacao', function () {
    LancamentoFinanceiro::create([
        'associado_id' => $this->associado->id,
        'tipo' => 'receita',
        'categoria' => 'mensalidade',
        'valor' => 100.00,
        'status' => 'pendente',
        'data_vencimento' => now()
    ]);
    
    $response = $this->tenantGet('/financeiro/conciliacao', $this->adminUser);
    
    $response->assertStatus(200);
});

it('admin pode conciliar lancamento pendente', function () {
    $lancamento = LancamentoFinanceiro::create([
        'associado_id' => $this->associado->id,
        'tipo' => 'receita',
        'categoria' => 'mensalidade',
        'valor' => 100.00,
        'status' => 'pendente',
        'data_vencimento' => now()
    ]);
    
    $response = $this->tenantPost("/financeiro/conciliacao/{$lancamento->id}", [], $this->adminUser);
    
    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    expect($lancamento->fresh()->status)->toBe('pago');
});
