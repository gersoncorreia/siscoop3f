<?php

uses(Tests\TenantTestCase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('usuario sem aceite lgpd eh redirecionado', function () {
    $user = \App\Models\User::factory()->create([
        'accepted_lgpd_at' => null,
    ]);

    // Ao tentar acessar o dashboard do inquilino
    $response = $this->tenantGet('/dashboard', $user);

    $response->assertRedirect('/lgpd');
});

test('usuario pode aceitar lgpd e navegar livremente', function () {
    $user = \App\Models\User::factory()->create([
        'accepted_lgpd_at' => null,
    ]);

    // Faz o aceite submetendo o POST
    $response = $this->tenantPost('/lgpd', ['accept' => true], $user);

    // O controller redireciona de volta para onde o user tentou ir, ou /dashboard
    $response->assertRedirect('/dashboard');

    $this->assertNotNull($user->fresh()->accepted_lgpd_at);

    // Agora pode navegar
    $this->tenantGet('/dashboard', $user)->assertStatus(200);
});

test('lgpd aceita dinamicamente envia para master-admin ou tenant route', function () {
    tenancy()->end();
    $user = \App\Models\User::factory()->create([
        'accepted_lgpd_at' => null,
    ]);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    $user->assignRole('Super Admin');

    // Aqui testando acesso a rota central
    $response = $this->actingAs($user)->get('/master-admin/dashboard');

    $response->assertRedirect(route('master-admin.lgpd.show'));
});
