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
            // Fundação — módulos de saúde
            'saude.agendamentos.manage', 'saude.internacoes.manage', 'saude.prontuario.manage',
            'saude.tabela_servicos.manage', 'saude.campanhas.manage', 'saude.indicadores.view',
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

        // Novos Perfis
        $roleAuditoria = Role::findOrCreate('Auditoria', 'web');
        $roleContabil = Role::findOrCreate('Contábil', 'web');
        $roleDependente = Role::findOrCreate('Dependente', 'web');
        $rolePrestador = Role::findOrCreate('Prestador', 'web');

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

        // 4. Gerente (Tático / Gerente Operacional na Fundação)
        $roleGerente->syncPermissions([
            'associados.view', 'associados.create', 'associados.update',
            'saude.view', 'saude.create', 'saude.update', 'saude.approve_refund',
            'saude.agendamentos.manage', 'saude.internacoes.manage',
            'saude.tabela_servicos.manage', 'saude.campanhas.manage', 'saude.indicadores.view',
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

        // 7. Auditoria (Apenas relatórios gerais, sem acesso a dados operacionais)
        $roleAuditoria->syncPermissions([
            'relatorios.view',
        ]);

        // 8. Contábil (Acesso completo ao financeiro e exportação para contabilidade)
        $roleContabil->syncPermissions([
            'financeiro.view',
            'contabil.view',
            'contabil.export',
            'relatorios.view',
        ]);

        // 9. Dependente (Mesma visão do cooperado, exceto finanças)
        $roleDependente->syncPermissions([
            'associados.view',
            'saude.view',
        ]);

        // 10. Fundação Roles
        $roleAdminFundacao = Role::findOrCreate('Administrador Fundação', 'web');
        $roleCliente = Role::findOrCreate('Cliente', 'web');

        $roleAdminFundacao->syncPermissions(Permission::all());
        $roleCliente->syncPermissions([
            'associados.view',
            'financeiro.view',
            'saude.view',
        ]);

        // 11. Atendente (Operacional — registra atendimentos e agendamentos)
        $roleAtendente = Role::findOrCreate('Atendente', 'web');
        $roleAtendente->syncPermissions([
            'associados.view',
            'saude.view', 'saude.create',
            'saude.agendamentos.manage',
        ]);

        // 12. Conselho (Estratégico — visão gerencial e relatórios)
        $roleConselho = Role::findOrCreate('Conselho', 'web');
        $roleConselho->syncPermissions([
            'associados.view',
            'financeiro.view',
            'saude.view',
            'saude.indicadores.view',
            'relatorios.view',
        ]);
    }
}
