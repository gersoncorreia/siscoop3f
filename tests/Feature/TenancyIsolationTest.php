<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TenancyIsolationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_it_creates_tenant_and_domain(): void
    {
        $tenantService = new \App\Services\TenantService();
        $tenant = $tenantService->createTenant([
            'nome' => 'Cooperativa Teste',
            'ramo' => 'saude'
        ]);

        $this->assertDatabaseHas('tenants', [
            'id' => 'cooperativa-teste',
        ]);

        $this->assertDatabaseHas('domains', [
            'domain' => 'cooperativa-teste.localhost'
        ]);

        $this->assertEquals('Cooperativa Teste', $tenant['tenant']->nome);

        $this->assertDatabaseHas('domains', [
            'domain' => 'cooperativa-teste.localhost'
        ]);
    }
}
