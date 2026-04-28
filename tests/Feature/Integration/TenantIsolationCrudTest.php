<?php

namespace Tests\Feature\Integration;

use Tests\TenantTestCase;

class TenantIsolationCrudTest extends TenantTestCase
{
    /** @test */
    public function test_tenant_is_created_with_domain()
    {
        $this->assertNotNull($this->tenant);
        $this->assertEquals('integracao-teste', $this->tenant->id);

        $this->assertDatabaseHas('tenants', [
            'id' => 'integracao-teste',
        ], 'mysql');

        $this->assertDatabaseHas('domains', [
            'tenant_id' => 'integracao-teste',
        ], 'mysql');
    }

    /** @test */
    public function test_tenant_database_is_isolated()
    {
        // Cria um dado no tenant
        \App\Models\Associado::create([
            'nome' => 'Dado do Tenant',
            'cpf' => '000.000.000-00',
            'status' => 'ativo',
        ]);

        // O dado deve existir no banco do tenant
        $this->assertDatabaseHas('associados', ['cpf' => '000.000.000-00']);
    }

    /** @test */
    public function test_tenant_domain_is_accessible()
    {
        $this->assertNotEmpty($this->tenantDomain);
        $this->assertStringContainsString('integracao-teste', $this->tenantDomain);
    }
}
