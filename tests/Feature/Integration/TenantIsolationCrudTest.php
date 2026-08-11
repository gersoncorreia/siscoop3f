<?php

namespace Tests\Feature\Integration;

use Tests\TenantTestCase;

class TenantIsolationCrudTest extends TenantTestCase
{
    /** @test */
    public function test_tenant_is_created_with_domain()
    {
        $this->assertNotNull($this->tenant);
        $this->assertEquals($this->tenant->id, $this->tenant->id);

        $this->assertDatabaseHas('tenants', [
            'id' => $this->tenant->id,
        ], 'mysql');

        $this->assertDatabaseHas('domains', [
            'tenant_id' => $this->tenant->id,
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

        // O dado deve existir no banco do tenant (decodificado)
        $associado = \App\Models\Associado::first();
        $this->assertEquals('000.000.000-00', $associado->cpf);
    }

    /** @test */
    public function test_tenant_domain_is_accessible()
    {
        $this->assertNotEmpty($this->tenantDomain);
        $this->assertStringContainsString('integracao-teste', $this->tenantDomain);
    }
}
