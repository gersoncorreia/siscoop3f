<?php

use App\Models\User;
use App\Models\Associado;
use App\Models\Dependente;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $tenantService = new TenantService();
    $tenantData = $tenantService->createTenant([
        'nome' => 'Cooperativa de Teste ' . \Illuminate\Support\Str::random(5),
        'ramo' => 'saude'
    ]);
    $this->tenant = $tenantData['tenant'];
    
    tenancy()->initialize($this->tenant);
    $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    
    // Criar o usuário Titular
    $this->titularUser = User::factory()->create([
        'accepted_lgpd_at' => now(),
        'has_temporary_password' => false
    ]);
    $this->titularUser->assignRole('Associado');
    
    $this->associado = Associado::create([
        'nome' => 'Titular Teste',
        'cpf' => '11122233344',
        'email' => $this->titularUser->email,
        'user_id' => $this->titularUser->id,
        'status' => 'ativo',
        'forma_pagamento_adesao' => 'PIX'
    ]);
    
    // Criar o usuário Dependente
    $this->dependenteUser = User::factory()->create([
        'accepted_lgpd_at' => now(),
        'has_temporary_password' => false
    ]);
    $this->dependenteUser->assignRole('Dependente');
    
    $this->dependente = Dependente::create([
        'associado_id' => $this->associado->id,
        'nome' => 'Dependente Teste',
        'parentesco' => 'Filho',
        'autoriza_acesso' => true,
        'user_id' => $this->dependenteUser->id,
        'email' => $this->dependenteUser->email,
        'status' => 'ativo'
    ]);
});

it('permite que o dependente acesse o dashboard carregando os dados do titular', function () {
    $url = 'http://' . $this->tenant->domains->first()->domain . '/cooperado';
    $response = $this->actingAs($this->dependenteUser)->get($url);
    
    $response->assertStatus(200);
});

it('bloqueia o dependente de acessar rotas financeiras e de dependentes', function () {
    $baseUrl = 'http://' . $this->tenant->domains->first()->domain;
    
    $responseFin = $this->actingAs($this->dependenteUser)->get($baseUrl . '/cooperado/financeiro');
    $responseFin->assertStatus(403);
    
    $responseCotas = $this->actingAs($this->dependenteUser)->get($baseUrl . '/cooperado/cotas');
    $responseCotas->assertStatus(403);
    
    $responseDep = $this->actingAs($this->dependenteUser)->get($baseUrl . '/cooperado/dependentes');
    $responseDep->assertStatus(403);
});

it('permite que o titular acesse rotas financeiras', function () {
    // Para acessar finanças o associado precisa ter o cadastro "completo"
    // Vamos simular passando na middleware de cadastro ou ignorando a validação de endereços por hora
    $this->withoutMiddleware(\App\Http\Middleware\CheckCadastroCompleto::class);
    
    $baseUrl = 'http://' . $this->tenant->domains->first()->domain;
    
    $responseFin = $this->actingAs($this->titularUser)->get($baseUrl . '/cooperado/financeiro');
    $responseFin->assertStatus(200);
    
    $responseCotas = $this->actingAs($this->titularUser)->get($baseUrl . '/cooperado/cotas');
    $responseCotas->assertStatus(200);
});
