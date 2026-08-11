<?php

namespace Tests;

use App\Services\TenantService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Classe base para testes de integração que precisam de Tenancy.
 * Cria um tenant de teste, inicializa o contexto, e limpa tudo no final.
 */
abstract class TenantTestCase extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected string $tenantDomain;

    protected function setUp(): void
    {
        parent::setUp();

        // Gates are active to test actual authorization rules

        // Criar tenant de teste
        $tenantService = new TenantService();
        $tenantData = $tenantService->createTenant([
            'nome' => 'Integração Teste ' . \Illuminate\Support\Str::random(5),
            'ramo' => 'saude'
        ]);
        $this->tenant = $tenantData['tenant'];

        $this->tenantDomain = $this->tenant->domains->first()->domain;

        // Inicializar tenancy para que Models usem o banco do tenant
        tenancy()->initialize($this->tenant);
    }

    protected function tearDown(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        parent::tearDown();
    }

    /**
     * Cria um usuário no banco central.
     */
    protected function createTenantUser(array $attributes = [])
    {
        return User::factory()->create(array_merge([
            'accepted_lgpd_at' => now(),
        ], $attributes));
    }

    /**
     * Helper: faz POST para uma rota do tenant.
     */
    protected function tenantPost(string $path, array $data = [], $user = null)
    {
        $url = "http://{$this->tenantDomain}{$path}";
        $request = $user ? $this->actingAs($user) : $this;
        return $request->post($url, $data);
    }

    /**
     * Helper: faz GET para uma rota do tenant.
     */
    protected function tenantGet(string $path, $user = null)
    {
        $url = "http://{$this->tenantDomain}{$path}";
        $request = $user ? $this->actingAs($user) : $this;
        return $request->get($url);
    }

    /**
     * Helper: faz PUT/PATCH para uma rota do tenant.
     */
    protected function tenantPut(string $path, array $data = [], $user = null)
    {
        $url = "http://{$this->tenantDomain}{$path}";
        $request = $user ? $this->actingAs($user) : $this;
        return $request->put($url, $data);
    }

    /**
     * Helper: faz DELETE para uma rota do tenant.
     */
    protected function tenantDelete(string $path, $user = null)
    {
        $url = "http://{$this->tenantDomain}{$path}";
        $request = $user ? $this->actingAs($user) : $this;
        return $request->delete($url);
    }
}
