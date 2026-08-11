<?php

use App\Models\Associado;
use App\Models\Prestador;
use App\Models\PrestadorServico;
use App\Models\SolicitacaoServico;
use App\Models\FaturaPrestador;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TenantTestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = $this->createTenantUser();
    $this->adminUser->assignRole('Administrador Cooperativa');
    
    // Create base models needed
    $this->associado = Associado::create([
        'nome' => 'Titular Saude',
        'cpf' => '00011122233',
        'email' => 'saude@teste.com',
        'status' => 'ativo',
        'forma_pagamento_adesao' => 'PIX'
    ]);
    
    $this->prestador = Prestador::create([
        'nome' => 'Clinica XYZ',
        'tipo' => 'pj',
        'email' => 'contato@clinicaxyz.com',
        'documento' => '12345678000199',
        'status' => 'ativo',
    ]);
    
    $this->servico = PrestadorServico::create([
        'prestador_id' => $this->prestador->id,
        'nome' => 'Consulta Geral',
        'valor_acordado' => 150.00,
        'ativo' => true
    ]);
});

it('admin pode ver autorizacoes pendentes', function () {
    SolicitacaoServico::create([
        'associado_id' => $this->associado->id,
        'prestador_id' => $this->prestador->id,
        'servico_id' => $this->servico->id,
        'status' => 'pendente_autorizacao',
        'valor_cobrado' => 150.00
    ]);
    
    $response = $this->tenantGet('/saude/autorizacoes', $this->adminUser);
    
    $response->assertStatus(200);
});

it('admin pode aprovar autorizacao', function () {
    $solicitacao = SolicitacaoServico::create([
        'associado_id' => $this->associado->id,
        'prestador_id' => $this->prestador->id,
        'servico_id' => $this->servico->id,
        'status' => 'pendente_autorizacao',
        'valor_cobrado' => 150.00
    ]);
    
    $response = $this->tenantPost("/saude/autorizacoes/{$solicitacao->id}/aprovar", [], $this->adminUser);
    
    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect($solicitacao->fresh()->status)->toBe('aprovado_cooperativa');
});

it('admin pode rejeitar autorizacao com motivo', function () {
    $solicitacao = SolicitacaoServico::create([
        'associado_id' => $this->associado->id,
        'prestador_id' => $this->prestador->id,
        'servico_id' => $this->servico->id,
        'status' => 'pendente_autorizacao',
        'valor_cobrado' => 150.00
    ]);
    
    $response = $this->tenantPost("/saude/autorizacoes/{$solicitacao->id}/rejeitar", [
        'motivo' => 'Falta de carência'
    ], $this->adminUser);
    
    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    $solicitacao->refresh();
    expect($solicitacao->status)->toBe('negado');
    expect($solicitacao->motivo_recusa)->toBe('Falta de carência');
});

it('admin pode auditar faturas', function () {
    $fatura = FaturaPrestador::create([
        'prestador_id' => $this->prestador->id,
        'mes_referencia' => '2023-10',
        'valor_total' => 1000.00,
        'status' => 'enviada'
    ]);
    
    $response = $this->tenantPost("/saude/faturas/{$fatura->id}/auditar", [], $this->adminUser);
    
    $response->assertRedirect();
    expect($fatura->fresh()->status)->toBe('em_auditoria');
});

it('admin pode pagar faturas', function () {
    $fatura = FaturaPrestador::create([
        'prestador_id' => $this->prestador->id,
        'mes_referencia' => '2023-10',
        'valor_total' => 1000.00,
        'status' => 'em_auditoria'
    ]);
    
    $response = $this->tenantPost("/saude/faturas/{$fatura->id}/pagar", [], $this->adminUser);
    
    $response->assertRedirect();
    
    $fatura->refresh();
    expect($fatura->status)->toBe('paga');
    expect($fatura->data_pagamento)->not->toBeNull();
});
