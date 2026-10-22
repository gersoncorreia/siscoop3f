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
            foreach (['tenantstfsaude', 'tenantagrocoop', 'tenantsanta-casa', 'tenantcbsaude'] as $dbName) {
                try {
                    $pdo->exec("DROP DATABASE IF EXISTS \"{$dbName}\" WITH (FORCE)");
                } catch (\Throwable $e) {
                    // Ignore if not present
                }
            }
        } elseif (\DB::connection('central')->getDriverName() === 'mysql') {
            foreach (['tenantstfsaude', 'tenantagrocoop', 'tenantsanta-casa', 'tenantcbsaude'] as $dbName) {
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
        $baseDomain = preg_replace('#^https?://#', '', $centralDomain);
        $baseDomain = rtrim($baseDomain, '/');
        $baseDomain = str_contains($baseDomain, 'localhost') ? 'localhost' : $baseDomain;

        $tenantsToSetup = [
            ['id' => 'cbsaude', 'data' => ['nome' => 'CB Saúde Cooperativa de Saúde', 'ramo' => 'saude', 'tipo' => 'cooperativa'], 'domain' => 'cbsaude.' . $baseDomain],
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
                        'tabela_servicos',
                        'carteirinha_digital',
                        'indicadores_desempenho',
                        'campanhas_saude',
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

        // 3. Seed Contratos entre Entidades (Inter-Tenant Contracts)
        \App\Models\ContratoEntidade::updateOrCreate(
            [
                'tenant_contratante_id' => 'cbsaude',
                'tenant_prestador_id' => 'santa-casa',
            ],
            [
                'status' => 'ativo',
                'data_inicio' => now()->subMonths(3)->toDateString(),
                'data_fim' => now()->addMonths(9)->toDateString(),
                'descricao' => 'Convênio Hospitalar e Diagnóstico entre CB Saúde e Fundação Santa Casa da Amazônia.',
                'arquivos' => [],
            ]
        );

        \App\Models\ContratoEntidade::updateOrCreate(
            [
                'tenant_contratante_id' => 'stfsaude',
                'tenant_prestador_id' => 'santa-casa',
            ],
            [
                'status' => 'ativo',
                'data_inicio' => now()->subMonths(4)->toDateString(),
                'data_fim' => now()->addMonths(8)->toDateString(),
                'descricao' => 'Convênio de Intercâmbio Assistencial e Hospitalar entre STF Saúde Co-op e Fundação Santa Casa da Amazônia.',
                'arquivos' => [],
            ]
        );

        \App\Models\ContratoEntidade::updateOrCreate(
            [
                'tenant_contratante_id' => 'agrocoop',
                'tenant_prestador_id' => 'stfsaude',
            ],
            [
                'status' => 'pendente',
                'data_inicio' => now()->toDateString(),
                'data_fim' => now()->addYear()->toDateString(),
                'descricao' => 'Solicitação de Convênio para Cobertura Médica dos Cooperados Rurais da Agro Valley Co-op.',
                'arquivos' => [],
            ]
        );
    }
}
