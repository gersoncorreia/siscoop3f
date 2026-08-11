<?php

uses(Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    // Create an admin user for central 
    $this->admin = \App\Models\User::factory()->create([
        'accepted_lgpd_at' => now(),
    ]);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    $this->admin->assignRole('Super Admin');
});

test('master admin can suspend a tenant soft delete', function () {
    // 1. Arrange
    $tenantService = new \App\Services\TenantService();
    $tenantData = $tenantService->createTenant([
        'nome' => 'Cooperativa Delete Test',
        'ramo' => 'Teste',
    ]);
    $tenant = $tenantData['tenant'];

    // 2. Act
    $response = $this->actingAs($this->admin)->delete(route('tenants.destroy', $tenant->id));

    // 3. Assert
    $response->assertRedirect(route('master-admin.dashboard'));
    $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);
    $this->assertNotNull($tenant->fresh()->deleted_at);
});

test('master admin can restore a suspended tenant', function () {
    $tenantService = new \App\Services\TenantService();
    $tenantData = $tenantService->createTenant([
        'nome' => 'Cooperativa Restore Test',
        'ramo' => 'Teste',
    ]);
    $tenant = $tenantData['tenant'];
    
    // Supondo que ela já foi deletada via Soft Delete
    $tenant->delete();
    $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);

    // Executa a rota de restauracao
    $this->withoutExceptionHandling();
    $response = $this->actingAs($this->admin)->post(route('tenants.restore', $tenant->id));

    $response->assertRedirect(route('master-admin.dashboard'));
    $this->assertNotSoftDeleted('tenants', ['id' => $tenant->id]);
    $this->assertNull($tenant->fresh()->deleted_at);
});

test('master admin can permanently force delete a tenant obeying lgpd', function () {
    $tenantService = new \App\Services\TenantService();
    $tenantData = $tenantService->createTenant([
        'nome' => 'Cooperativa Hard Delete Test',
        'ramo' => 'Teste',
    ]);
    $tenant = $tenantData['tenant'];
    
    // A cooperativa deve estar suspensa primeiro ou vai direto
    $tenant->delete();

    // Rota destrutiva
    $response = $this->actingAs($this->admin)->delete(route('tenants.forceDelete', $tenant->id));

    $response->assertRedirect(route('master-admin.dashboard'));
    $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
});

test('master admin can export tenant data payload directly', function () {
    $tenantService = new \App\Services\TenantService();
    $tenantData = $tenantService->createTenant([
        'nome' => 'Cooperativa Download Test',
        'ramo' => 'Teste',
    ]);
    $tenant = $tenantData['tenant'];
    
    $tenant->delete(); // It's soft deleted

    $response = $this->actingAs($this->admin)->get(route('tenants.export', $tenant->id));

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'message' => 'Exportação da base LGPD solicitada',
    ]);
});
