<?php

uses(Tests\TenantTestCase::class);

beforeEach(function () {
    $this->withoutVite();
    // Generate role since the seeder is only seeded if we call it
    // Wait, RefreshDatabase automatically runs migrations but not seeds unless we tell it to.
    // TenantTestCase doesn't run tenant seeds by default, we need to create the roles or seed.
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Associado', 'guard_name' => 'web']);
});

test('redirecionado do portal cooperado se nao tiver role associado', function () {
    $user = $this->createTenantUser(); // Plain user

    $response = $this->tenantGet('/cooperado', $user);

    $response->assertRedirect('/dashboard');
});

test('acesso negado para usuario com role mas sem registro na tabela associados', function () {
    $user = $this->createTenantUser();
    $user->assignRole('Associado');

    // No Associado record tied to this user

    $response = $this->tenantGet('/cooperado', $user);

    $response->assertStatus(403);
});

test('sucesso para usuario completo acessando dashboard', function () {
    $user = $this->createTenantUser();
    $user->assignRole('Associado');

    $associado = \App\Models\Associado::create([
        'user_id' => $user->id,
        'nome' => 'Cooperado Teste',
        'cpf' => '00000000000',
        'email' => $user->email,
        'status' => 'ativo',
    ]);

    $response = $this->tenantGet('/cooperado', $user);

    $response->assertStatus(200);
    // Inertia returns 200, we can assert inertia structure if we want
});

test('consistencia das views extras carregando corretamente', function () {
    $user = $this->createTenantUser();
    $user->assignRole('Associado');

    $associado = \App\Models\Associado::create([
        'user_id' => $user->id,
        'nome' => 'Cooperado Teste',
        'cpf' => '00000000000',
        'email' => $user->email,
        'status' => 'ativo',
    ]);

    $this->tenantGet('/cooperado/financeiro', $user)->assertStatus(200);
    $this->tenantGet('/cooperado/cotas', $user)->assertStatus(200);
    $this->tenantGet('/cooperado/reembolsos', $user)->assertStatus(200);
    $this->tenantGet('/cooperado/dependentes', $user)->assertStatus(200);
    $this->tenantGet('/cooperado/documentos', $user)->assertStatus(200);
});
