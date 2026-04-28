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
        $tenantSaude = Tenant::updateOrCreate(['id' => 'stfsaude'], ['nome' => 'STF Saúde Co-op', 'ramo' => 'saude']);
        $tenantSaude->domains()->firstOrCreate(['domain' => 'stfsaude.localhost']);

        $tenantAgro = Tenant::updateOrCreate(['id' => 'agrocoop'], ['nome' => 'Agro Valley Co-op', 'ramo' => 'agricola']);
        $tenantAgro->domains()->firstOrCreate(['domain' => 'agrocoop.localhost']);
    }
}
