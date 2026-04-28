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

    // O sistema de rotas não achará /master-admin/dashboard no arquivo routes/tenant.php
    $response->assertStatus(404);
});

test('links dinamicos evitam loops retornando para dominio seguro', function () {
    $user = \App\Models\User::factory()->create(['accepted_lgpd_at' => now()]);
    tenancy()->end();

    // No master admin, tentando acessar dashboard da central
    $response = $this->actingAs($user)->get('/master-admin/dashboard');

    $response->assertStatus(200);
});
