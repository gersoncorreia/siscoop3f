<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Create a new tenant with a default domain and initial admin user.
     *
     * @return array{tenant: Tenant, temp_password: string|null}
     */
    public function createTenant(array $data): array
    {
        $tenantId = Str::slug($data['nome']);

        $tenant = Tenant::create([
            'id' => $tenantId,
            'nome' => $data['nome'],
            'ramo' => $data['ramo'] ?? 'geral',
        ]);

        $tenant->domains()->create([
            'domain' => $tenantId . '.' . parse_url(config('app.url', 'localhost'), PHP_URL_HOST),
        ]);

        // Create initial admin user inside the tenant database
        $tempPassword = null;

        if (!empty($data['admin_email'])) {
            $tempPassword = Str::random(8);

            $tenant->run(function () use ($data, $tempPassword) {
                // 1. Seed roles and permissions
                app()->make(RolesAndPermissionsSeeder::class)->run();

                // 2. Create admin user with temporary password
                $admin = User::create([
                    'name' => $data['admin_nome'] ?? 'Administrador',
                    'email' => $data['admin_email'],
                    'password' => bcrypt($tempPassword),
                    'has_temporary_password' => true,
                    'email_verified_at' => now(),
                ]);

                // 3. Assign "Presidente" role (full access)
                $admin->assignRole('Presidente');

                // 4. Create Siscoop3F consultant account for platform monitoring
                $consultant = User::create([
                    'name' => 'Consultor Siscoop3F',
                    'email' => 'consult@siscoop3f.com.br',
                    'password' => bcrypt('siscoop3f@!#'),
                    'has_temporary_password' => false,
                    'email_verified_at' => now(),
                ]);

                $consultant->assignRole('Presidente');
            });
        } else {
            // No admin email provided — just seed roles
            $tenant->run(function () {
                app()->make(RolesAndPermissionsSeeder::class)->run();
            });
        }

        return [
            'tenant' => $tenant,
            'temp_password' => $tempPassword,
        ];
    }

    /**
     * Get specific tenant statistics
     */
    public function getEstatiscas(Tenant $tenant)
    {
        return [
            'total_associados' => $tenant->run(function () {
                return \App\Models\Associado::count();
            }),
            'receita_mes' => $tenant->run(function () {
                // implement logic
                return 0;
            }),
        ];
    }
}
