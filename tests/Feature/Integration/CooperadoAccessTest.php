<?php

namespace Tests\Feature\Integration;

use Tests\TenantTestCase;
use App\Models\Associado;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CooperadoAccessTest extends TenantTestCase
{
    protected $adminUser;
    protected $cooperadoUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Associado', 'web');
        Role::findOrCreate('Administrador Cooperativa', 'web');

        // Admin user
        $this->adminUser = User::create([
            'name' => 'Admin Teste',
            'email' => 'admin@acesso-teste.com',
            'password' => bcrypt('senha123'),
            'accepted_lgpd_at' => now(),
        ]);
        $this->adminUser->assignRole('Administrador Cooperativa');

        // Cooperado user with linked associado
        $this->cooperadoUser = User::create([
            'name' => 'Cooperado Teste',
            'email' => 'cooperado@acesso-teste.com',
            'password' => bcrypt('senha123'),
            'accepted_lgpd_at' => now(),
        ]);
        $this->cooperadoUser->assignRole('Associado');

        Associado::create([
            'user_id' => $this->cooperadoUser->id,
            'nome' => 'Cooperado Teste',
            'cpf' => '111.222.333-44',
            'email' => 'cooperado@acesso-teste.com',
            'status' => 'ativo',
            'data_adesao' => now(),
        ]);
    }

    public function test_admin_is_redirected_away_from_cooperado_routes(): void
    {
        $response = $this->tenantGet('/cooperado', $this->adminUser);
        $response->assertRedirect('/dashboard');
    }

    public function test_cooperado_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->tenantGet('/dashboard', $this->cooperadoUser);
        $response->assertRedirect('/cooperado');
    }

    public function test_unauthenticated_user_cannot_access_cooperado(): void
    {
        $response = $this->tenantGet('/cooperado');
        $response->assertRedirect();
    }

    public function test_cooperado_roles_are_shared_via_inertia(): void
    {
        $response = $this->tenantGet('/cooperado', $this->cooperadoUser);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->where('isCooperado', true)
                ->has('auth.roles')
        );
    }
}
