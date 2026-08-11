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

        $defaultServicos = [
            'adesao_online', 'gestao_consultas', 'gestao_internacoes',
            'gestao_exames', 'prontuario_eletronico', 'controle_financeiro', 'relatorios_gerenciais',
            'tabela_servicos', 'carteirinha_digital', 'indicadores_desempenho', 'campanhas_saude',
        ];

        $tipo = $data['tipo'] ?? 'cooperativa';

        $tenant = Tenant::create([
            'id' => $tenantId,
            'tipo' => $tipo,
            'nome' => $data['nome'],
            'ramo' => $data['ramo'] ?? 'geral',
            'servicos' => $data['servicos'] ?? ($tipo === 'fundacao' ? $defaultServicos : null),
            'possui_landing_page' => isset($data['possui_landing_page']) ? (bool) $data['possui_landing_page'] : true,
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

                // 3. Assign role based on entity type
                $roleName = ($data['tipo'] ?? 'cooperativa') === 'fundacao' ? 'Administrador Fundação' : 'Presidente';
                $admin->assignRole($roleName);

                // 4. Create Siscoop3F consultant account for platform monitoring
                $consultant = User::create([
                    'name' => 'Consultor Siscoop3F',
                    'email' => 'consult@siscoop3f.com.br',
                    'password' => bcrypt('siscoop3f@!#'),
                    'has_temporary_password' => false,
                    'email_verified_at' => now(),
                ]);

                $consultant->assignRole($roleName);
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

    public function updateTenant(Tenant $tenant, array $data): bool
    {
        $updateData = [
            'nome' => $data['nome'],
            'tipo' => $data['tipo'] ?? $tenant->tipo,
            'ramo' => $data['ramo'] ?? $tenant->ramo,
        ];
        if (array_key_exists('servicos', $data)) {
            $updateData['servicos'] = $data['servicos'];
        }
        if (array_key_exists('possui_landing_page', $data)) {
            $updateData['possui_landing_page'] = (bool) $data['possui_landing_page'];
        }
        return $tenant->update($updateData);
    }

    public function deleteTenant(Tenant $tenant): bool
    {
        return $tenant->delete();
    }

    public function restoreTenant($id): Tenant
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $tenant->restore();
        return $tenant;
    }

    public function forceDeleteTenant($id): void
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        try {
            $tenant->forceDelete();
        } catch (\Illuminate\Database\QueryException $e) {
            if (strpos($e->getMessage(), 'database doesn\'t exist') === false && $e->getCode() != 'HY000') {
                throw $e;
            }
            \Illuminate\Support\Facades\DB::table('tenants')->where('id', $id)->delete();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::table('tenants')->where('id', $id)->delete();
        }
    }
}
