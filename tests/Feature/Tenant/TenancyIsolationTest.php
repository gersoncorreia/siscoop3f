<?php

uses(Tests\TenantTestCase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('tentar acessar master-admin pelo dominio do tenant falha', function () {
    $user = \App\Models\User::factory()->create(['accepted_lgpd_at' => now()]);

    // Rota central mas acessada com o domínio de Tenant
    // tenantGet builda a URL com http://{tenantDomain}
    $response = $this->tenantGet('/master-admin/dashboard', $user);

    // O sistema de rotas não deve permitir acesso ao painel master
    $this->assertTrue(in_array($response->getStatusCode(), [403, 404]));
});

test('links dinamicos evitam loops retornando para dominio seguro', function () {
    tenancy()->end();
    $user = \App\Models\User::factory()->create(['accepted_lgpd_at' => now()]);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    $user->assignRole('Super Admin');

    // No master admin, tentando acessar dashboard da central
    $response = $this->actingAs($user)->get('/master-admin/dashboard');

    $response->assertStatus(200);
});
