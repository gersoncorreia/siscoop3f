<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant's database.
     *
     * In production, roles/permissions are seeded by TenantService
     * when the admin user is created. This seeder only creates
     * test data in local/testing environments.
     */
    public function run(): void
    {
        // 1. Always setup Roles and Permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Only create test users in development/testing
        if (!app()->environment('production')) {
            $this->seedTestData();
        }
    }

    /**
     * Seed test data for local development and testing.
     */
    protected function seedTestData(): void
    {
        $roleTenantAdmin = Role::where('name', 'Administrador Cooperativa')->first();
        $roleAssociado = Role::where('name', 'Associado')->first();

        // Administrative Test Users
        $testUsers = [
            ['email' => 'presidente@stfsaude.com.br', 'name' => 'Presidente da Cooperativa', 'role' => 'Presidente'],
            ['email' => 'diretor@stfsaude.com.br', 'name' => 'Diretor Financeiro', 'role' => 'Diretor Administrativo Financeiro'],
            ['email' => 'consultor@stfsaude.com.br', 'name' => 'Consultor Externo', 'role' => 'Consultor'],
            ['email' => 'gerente@stfsaude.com.br', 'name' => 'Gerente de Unidade', 'role' => 'Gerente'],
            ['email' => 'fiscal@stfsaude.com.br', 'name' => 'Conselho Fiscal', 'role' => 'Presidente do Conselho Fiscal'],
        ];

        foreach ($testUsers as $testUser) {
            $user = User::updateOrCreate(
                ['email' => $testUser['email']],
                [
                    'name' => $testUser['name'],
                    'password' => bcrypt('senha123'),
                ]
            );
            $user->assignRole($testUser['role']);
        }

        // Legacy Tenant Admin
        $adminEmail = $this->getAdminEmail();
        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin local da Cooperativa',
                'password' => bcrypt('senha123'),
            ]
        );
        $admin->assignRole($roleTenantAdmin);

        // Associado Exemplo (only for stfsaude tenant)
        if (tenant('id') === 'stfsaude') {
            $associado = User::updateOrCreate(
                ['email' => 'joao.associado@gmail.com'],
                [
                    'name' => 'João Silva Associado',
                    'password' => bcrypt('senha123'),
                ]
            );
            $associado->assignRole($roleAssociado);

            $perfilAssociado = \App\Models\Associado::create([
                'user_id' => $associado->id,
                'nome' => 'João Silva Associado',
                'cpf' => '12345678900',
                'email' => 'joao.associado@gmail.com',
                'nascimento' => '1985-10-15',
                'status' => 'ativo',
                'data_adesao' => now()->subMonths(6),
            ]);

            \App\Models\CotaCapital::create([
                'associado_id' => $perfilAssociado->id,
                'valor_quota' => 150.00,
                'qtd_quotas' => 10,
                'saldo' => 1500.00,
                'tipo_movimento' => 'integralizacao',
            ]);

            \App\Models\LancamentoFinanceiro::create([
                'associado_id' => $perfilAssociado->id,
                'tipo' => 'entrada',
                'valor' => 150.00,
                'status' => 'pago',
                'data_vencimento' => now()->startOfMonth(),
            ]);

            \App\Models\LancamentoFinanceiro::create([
                'associado_id' => $perfilAssociado->id,
                'tipo' => 'saida',
                'valor' => 50.00,
                'status' => 'pendente',
                'data_vencimento' => now()->addDays(5),
            ]);
        }
    }

    protected function getAdminEmail()
    {
        return tenant('id') === 'stfsaude' ? 'admin@stfsaude.com.br' : 'admin@agrocoop.com.br';
    }
}
