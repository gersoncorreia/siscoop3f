<?php

use App\Models\Associado;
use App\Models\User;
use App\Models\LancamentoFinanceiro;
use App\Services\AdesaoService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

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
    
    $this->adesaoService = new AdesaoService();
    
    // Criar um admin para notificações
    $admin = User::factory()->create();
    $admin->assignRole('Administrador Cooperativa');
});

it('calcula a taxa de adesão padrão com taxa de uso do sistema', function () {
    $dados = [
        'nome' => 'João Silva',
        'cpf' => '12345678901',
        'email' => 'joao@example.com',
        'forma_pagamento_adesao' => 'PIX',
    ];

    $associado = $this->adesaoService->processarAdesao($dados);
    $this->adesaoService->aprovarAdesao($associado);

    // Titular: 100 + Sistema: 10 + Cota: 10 = 120
    $totalLancado = LancamentoFinanceiro::where('associado_id', $associado->id)->sum('valor');
    expect((float) $totalLancado)->toBe(120.0);
    
    // Verifica lançamentos individuais
    expect(LancamentoFinanceiro::where('categoria', 'taxa_adm_titular')->first()->valor)->toEqual(100.00);
    expect(LancamentoFinanceiro::where('categoria', 'taxa_uso_sistema')->first()->valor)->toEqual(10.00);
    expect(LancamentoFinanceiro::where('categoria', 'cota_capital')->first()->valor)->toEqual(10.00);
});

it('inclui dependente legal somando R$ 80,00', function () {
    $dados = [
        'nome' => 'Maria Silva',
        'cpf' => '09876543210',
        'email' => 'maria@example.com',
        'forma_pagamento_adesao' => 'CARTAO',
        'dependentes' => [
            [
                'nome' => 'Filho Legal',
                'parentesco' => 'Filho',
                'tipo_dependencia' => 'legal',
                'autoriza_acesso' => false,
            ]
        ]
    ];

    $associado = $this->adesaoService->processarAdesao($dados);
    $this->adesaoService->aprovarAdesao($associado);

    // Titular (120) + DepLegal (80) = 200
    $totalLancado = LancamentoFinanceiro::where('associado_id', $associado->id)->sum('valor');
    expect((float) $totalLancado)->toBe(200.0);
    expect(LancamentoFinanceiro::where('categoria', 'taxa_adm_dependente')->first()->valor)->toEqual(80.00);
});

it('inclui dependente não legal somando R$ 100,00', function () {
    $dados = [
        'nome' => 'Jose Silva',
        'cpf' => '11122233344',
        'email' => 'jose@example.com',
        'forma_pagamento_adesao' => 'BOLETO',
        'dependentes' => [
            [
                'nome' => 'Sobrinho Não Legal',
                'parentesco' => 'Sobrinho',
                'tipo_dependencia' => 'nao_legal',
                'autoriza_acesso' => false,
            ]
        ]
    ];

    $associado = $this->adesaoService->processarAdesao($dados);
    $this->adesaoService->aprovarAdesao($associado);

    // Titular (120) + DepNaoLegal (100) = 220
    $totalLancado = LancamentoFinanceiro::where('associado_id', $associado->id)->sum('valor');
    expect((float) $totalLancado)->toBe(220.0);
    expect(LancamentoFinanceiro::where('categoria', 'taxa_adm_dependente_nao_legal')->first()->valor)->toEqual(100.00);
});

it('cria usuario automaticamente se autoriza_acesso do dependente for marcado', function () {
    Notification::fake();
    
    $dados = [
        'nome' => 'Carlos Silva',
        'cpf' => '55566677788',
        'email' => 'carlos@example.com',
        'forma_pagamento_adesao' => 'PIX',
        'dependentes' => [
            [
                'nome' => 'Esposa Legal',
                'parentesco' => 'Esposa',
                'tipo_dependencia' => 'legal',
                'autoriza_acesso' => true,
                'email' => 'esposa@example.com'
            ]
        ]
    ];

    $associado = $this->adesaoService->processarAdesao($dados);
    $this->adesaoService->aprovarAdesao($associado);

    $dependente = $associado->dependentes()->first();
    expect($dependente->user_id)->not->toBeNull();
    
    $userDep = User::find($dependente->user_id);
    expect($userDep->email)->toBe('esposa@example.com');
    expect($userDep->hasRole('Dependente'))->toBeTrue();
    
    Notification::assertSentTo(
        [$userDep],
        \App\Notifications\DependenteCriadoNotification::class
    );
});
