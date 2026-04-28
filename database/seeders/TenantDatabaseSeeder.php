<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant's database.
     */
    public function run(): void
    {
        // 1. Setup Roles inside Tenant
        $this->call(RolesAndPermissionsSeeder::class);
        $roleTenantAdmin = Role::where('name', 'Administrador Cooperativa')->first();
        $roleAssociado = Role::where('name', 'Associado')->first();

        // 2. Setup Administrative Test Users
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

        // 3. Setup Legacy Tenant Admin (for compatibility with previous tests)
        $adminEmail = $this->getAdminEmail();
        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin local da Cooperativa',
                'password' => bcrypt('senha123'),
            ]
        );
        $admin->assignRole($roleTenantAdmin);

        // 3. Setup Associado Exemplo
        if (tenant('id') === 'stfsaude') {
            $associado = User::updateOrCreate(
            ['email' => 'joao.associado@gmail.com'],
            [
                'name' => 'João Silva Associado',
                'password' => bcrypt('senha123'),
            ]
            );
            $associado->assignRole($roleAssociado);

            // Create linked Profiler Associado
            $perfilAssociado = \App\Models\Associado::create([
                'user_id' => $associado->id,
                'nome' => 'João Silva Associado',
                'cpf' => '12345678900', // Fake CPF, model casts to encrypted
                'email' => 'joao.associado@gmail.com',
                'nascimento' => '1985-10-15',
                'status' => 'ativo',
                'data_adesao' => now()->subMonths(6),
            ]);

            // Add fake capital quotas
            \App\Models\CotaCapital::create([
                'associado_id' => $perfilAssociado->id,
                'valor_quota' => 150.00,
                'qtd_quotas' => 10,
                'saldo' => 1500.00,
                'tipo_movimento' => 'integralizacao',
            ]);

            // Add fake recent financial records
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
        // Logic to return different emails based on tenant for testing
        return tenant('id') === 'stfsaude' ? 'admin@stfsaude.com.br' : 'admin@agrocoop.com.br';
    }
}
