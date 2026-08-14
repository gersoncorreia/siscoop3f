<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Tenant;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clean up existing tenant databases on fresh migration
        if (\DB::connection('central')->getDriverName() === 'pgsql') {
            $pdo = \DB::connection('central')->getPdo();
            foreach (['tenantstfsaude', 'tenantagrocoop', 'tenantsanta-casa'] as $dbName) {
                try {
                    $pdo->exec("DROP DATABASE IF EXISTS \"{$dbName}\" WITH (FORCE)");
                } catch (\Throwable $e) {
                    // Ignore if not present
                }
            }
        } elseif (\DB::connection('central')->getDriverName() === 'mysql') {
            foreach (['tenantstfsaude', 'tenantagrocoop', 'tenantsanta-casa'] as $dbName) {
                try {
                    \DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
                } catch (\Throwable $e) {
                    // Ignore if not present
                }
            }
        }

        // 1. Setup Super Admin (Central Only)
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $master = User::updateOrCreate(
            ['email' => 'master@siscoop3f.com.br'],
            [
                'name' => 'Gerson Super Admin',
                'password' => bcrypt('master123'),
            ]
        );
        $master->assignRole($roleSuperAdmin);

        // 2. Setup Tenants
        $centralDomain = env('CENTRAL_DOMAIN', 'localhost');
        $baseDomain = str_contains($centralDomain, 'localhost') ? 'localhost' : $centralDomain;

        $tenantsToSetup = [
            ['id' => 'stfsaude', 'data' => ['nome' => 'STF Saúde Co-op', 'ramo' => 'saude', 'tipo' => 'cooperativa'], 'domain' => 'stfsaude.' . $baseDomain],
            ['id' => 'agrocoop', 'data' => ['nome' => 'Agro Valley Co-op', 'ramo' => 'agricola', 'tipo' => 'cooperativa'], 'domain' => 'agrocoop.' . $baseDomain],
            [
                'id' => 'santa-casa',
                'data' => [
                    'nome'  => 'Santa Casa da Amazônia',
                    'ramo'  => 'saude',
                    'tipo'  => 'fundacao',
                    'servicos' => [
                        'adesao_online',
                        'gestao_consultas',
                        'gestao_internacoes',
                        'gestao_exames',
                        'prontuario_eletronico',
                        'controle_financeiro',
                        'relatorios_gerenciais',
                    ],
                ],
                'domain' => 'santacasa.' . $baseDomain,
            ],
        ];

        foreach ($tenantsToSetup as $tInfo) {
            $tenant = Tenant::find($tInfo['id']);
            if (!$tenant) {
                $tenant = Tenant::create(array_merge(['id' => $tInfo['id']], $tInfo['data']));
            } else {
                $tenant->update($tInfo['data']);
                if (!$tenant->database()->manager()->databaseExists($tenant->database()->getName())) {
                    $tenant->database()->manager()->createDatabase($tenant);
                }
            }

            // Update or Create domain for tenant
            $domainRecord = $tenant->domains()->first();
            if ($domainRecord) {
                $domainRecord->update(['domain' => $tInfo['domain']]);
            } else {
                $tenant->domains()->create(['domain' => $tInfo['domain']]);
            }
        }
    }
}
