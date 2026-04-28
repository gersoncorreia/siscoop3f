<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissões
        $permissions = [
            'associados.view', 'associados.create', 'associados.update', 'associados.delete',
            'financeiro.view', 'financeiro.create', 'financeiro.update', 'financeiro.delete', 'financeiro.reconcile',
            'saude.view', 'saude.create', 'saude.update', 'saude.delete', 'saude.approve_refund',
            'contabil.view', 'contabil.export',
            'fiscal.view', 'fiscal.export',
            'relatorios.view',
            'configuracoes.manage', 'configuracoes.users',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Roles Principais
        $rolePresidente = Role::findOrCreate('Presidente', 'web');
        $roleDiretorFin = Role::findOrCreate('Diretor Administrativo Financeiro', 'web');
        $roleConsultor = Role::findOrCreate('Consultor', 'web');
        $roleGerente = Role::findOrCreate('Gerente', 'web');
        $roleConselhoFiscal = Role::findOrCreate('Presidente do Conselho Fiscal', 'web');
        $roleAssociado = Role::findOrCreate('Associado', 'web');

        // Legado / Compatibilidade
        $roleAdmin = Role::findOrCreate('Administrador Cooperativa', 'web');

        // Atribuição de Permissões
        
        // 1. Presidente / Admin (Tudo)
        $allPermissions = Permission::all();
        $rolePresidente->syncPermissions($allPermissions);
        $roleAdmin->syncPermissions($allPermissions);

        // 2. Diretor Adm Financeiro
        $roleDiretorFin->syncPermissions([
            'associados.view',
            'financeiro.view', 'financeiro.create', 'financeiro.update', 'financeiro.reconcile',
            'contabil.view', 'contabil.export',
            'fiscal.view', 'fiscal.export',
            'relatorios.view',
            'configuracoes.manage',
        ]);

        // 3. Consultor
        $roleConsultor->syncPermissions([
            'associados.view',
            'financeiro.view',
            'saude.view',
            'relatorios.view',
        ]);

        // 4. Gerente
        $roleGerente->syncPermissions([
            'associados.view', 'associados.create', 'associados.update',
            'saude.view', 'saude.create', 'saude.update', 'saude.approve_refund',
            'financeiro.view',
            'relatorios.view',
        ]);

        // 5. Conselho Fiscal
        $roleConselhoFiscal->syncPermissions([
            'financeiro.view',
            'contabil.view', 'contabil.export',
            'fiscal.view', 'fiscal.export',
            'relatorios.view',
        ]);

        // 6. Associado
        $roleAssociado->syncPermissions([
            'associados.view',
            'financeiro.view',
            'saude.view',
        ]);
    }
}
