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

        // 2. Seed Demo Prestadores de Serviços
        $this->call(PrestadorSeeder::class);

        // 2. Seed test data for foundation and cooperative tenants
        $this->seedTestData();
    }

    /**
     * Seed test data for local development and testing.
     */
    protected function seedTestData(): void
    {
        if (tenant('tipo') === 'fundacao') {
            $roleTenantAdmin = Role::where('name', 'Administrador Fundação')->first();
            $roleCliente = Role::where('name', 'Cliente')->first();

            $adminEmail = 'admin@santacasa.com.br';
            $admin = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Admin local da Fundação',
                    'password' => bcrypt('senha123'),
                ]
            );
            $admin->assignRole($roleTenantAdmin);

            $cliente = User::updateOrCreate(
                ['email' => 'cliente@santacasa.com.br'],
                [
                    'name' => 'Pedro Cliente',
                    'password' => bcrypt('senha123'),
                ]
            );
            $cliente->assignRole($roleCliente);

            $associado = \App\Models\Associado::create([
                'user_id' => $cliente->id,
                'nome' => 'Pedro Cliente',
                'cpf' => '98765432100',
                'email' => 'cliente@santacasa.com.br',
                'nascimento' => '1990-05-20',
                'status' => 'ativo',
                'data_adesao' => now()->subMonths(3),
            ]);

            // Emitir carteirinha para Pedro Cliente
            $associado->emitirCarteirinha('individual');

            // 3. Seed Tabela de Serviços inicial (Santa Casa da Amazônia - 20 itens do documento)
            $servicosIniciais = [
                ['codigo' => '001', 'nome' => 'Consulta com clínico geral', 'categoria' => 'consulta', 'valor_base' => 80.00, 'ordem' => 1],
                ['codigo' => '002', 'nome' => 'Exame laboratorial básico', 'categoria' => 'exame_laboratorial', 'valor_base' => 80.00, 'ordem' => 2],
                ['codigo' => '003', 'nome' => 'Pronto atendimento', 'categoria' => 'pronto_atendimento', 'valor_base' => 80.00, 'ordem' => 3],
                ['codigo' => '004', 'nome' => 'Atestado simples', 'categoria' => 'procedimento', 'valor_base' => 80.00, 'ordem' => 4],
                ['codigo' => '005', 'nome' => 'Atestado especializado', 'categoria' => 'procedimento', 'valor_base' => 300.00, 'ordem' => 5],
                ['codigo' => '006', 'nome' => 'Enfermaria – diária', 'categoria' => 'internacao', 'valor_base' => 100.00, 'ordem' => 6],
                ['codigo' => '007', 'nome' => 'Apartamento – diária', 'categoria' => 'internacao', 'valor_base' => 200.00, 'ordem' => 7],
                ['codigo' => '008', 'nome' => 'Ultrassonografia', 'categoria' => 'exame_imagem', 'valor_base' => 80.00, 'ordem' => 8],
                ['codigo' => '009', 'nome' => 'RX de tórax', 'categoria' => 'exame_imagem', 'valor_base' => 80.00, 'ordem' => 9],
                ['codigo' => '010', 'nome' => 'RX de membros', 'categoria' => 'exame_imagem', 'valor_base' => 50.00, 'ordem' => 10],
                ['codigo' => '011', 'nome' => 'Tomografia', 'categoria' => 'exame_imagem', 'valor_base' => 300.00, 'ordem' => 11],
                ['codigo' => '012', 'nome' => 'Tomografia com contraste e laudo', 'categoria' => 'exame_imagem', 'valor_base' => 500.00, 'ordem' => 12],
                ['codigo' => '013', 'nome' => 'Pequena cirurgia', 'categoria' => 'cirurgia', 'valor_base' => 300.00, 'ordem' => 13],
                ['codigo' => '014', 'nome' => 'Anestesia para cirurgia simples', 'categoria' => 'cirurgia', 'valor_base' => 200.00, 'ordem' => 14],
                ['codigo' => '015', 'nome' => 'Anestesia para cirurgia normal', 'categoria' => 'cirurgia', 'valor_base' => 800.00, 'ordem' => 15],
                ['codigo' => '016', 'nome' => 'Coleta de sangue para exames', 'categoria' => 'exame_laboratorial', 'valor_base' => 50.00, 'ordem' => 16],
                ['codigo' => '017', 'nome' => 'Fisioterapia – sessão', 'categoria' => 'fisioterapia', 'valor_base' => 70.00, 'ordem' => 17],
                ['codigo' => '018', 'nome' => 'Serviço odontológico – extração/limpeza simples', 'categoria' => 'odontologia', 'valor_base' => 100.00, 'ordem' => 18],
                ['codigo' => '019', 'nome' => 'Cartão Santa Casa – pessoa física', 'categoria' => 'outros', 'valor_base' => 50.00, 'ordem' => 19],
                ['codigo' => '020', 'nome' => 'Cartão Santa Casa – família (2+ dependentes)', 'categoria' => 'outros', 'valor_base' => 30.00, 'ordem' => 20],
            ];

            foreach ($servicosIniciais as $sData) {
                \App\Models\TabelaServico::updateOrCreate(
                    ['codigo' => $sData['codigo']],
                    $sData
                );
            }

            // 4. Seed Planos de Adesão iniciais
            \App\Models\PlanoAdesao::updateOrCreate(
                ['nome' => 'Cartão Santa Casa PF'],
                [
                    'tipo' => 'individual',
                    'valor_adesao' => 50.00,
                    'valor_mensalidade' => 0.00,
                    'descricao' => 'Cartão individual com acesso a toda tabela de serviços com preços reduzidos.',
                    'beneficios' => ['Acesso à tabela reduzida', 'Carteirinha Digital com QR Code', 'Agendamento Online'],
                    'ativo' => true,
                    'ordem' => 1,
                ]
            );

            \App\Models\PlanoAdesao::updateOrCreate(
                ['nome' => 'Cartão Santa Casa Família'],
                [
                    'tipo' => 'familiar',
                    'valor_adesao' => 30.00,
                    'valor_mensalidade' => 0.00,
                    'max_dependentes' => 0,
                    'descricao' => 'Cartão familiar para 2 ou mais dependentes.',
                    'beneficios' => ['Inclusão de dependentes', 'Acesso à tabela reduzida', 'Carteirinha Digital para todos'],
                    'ativo' => true,
                    'ordem' => 2,
                ]
            );

            return;
        }

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

            // Seed Prestadores and assign role
            $rolePrestador = Role::findOrCreate('Prestador', 'web');

            $prestadoresData = [
                ['name' => 'Clínica São Lucas', 'email' => 'sao.lucas@gmail.com', 'especialidades' => ['Pediatria', 'Clínica Geral'], 'tipo' => 'clinica'],
                ['name' => 'Laboratório Central', 'email' => 'lab.central@gmail.com', 'especialidades' => ['Análises Clínicas', 'Exames'], 'tipo' => 'hospital'],
                ['name' => 'Dr. Marcelo Silva', 'email' => 'marcelo.silva@gmail.com', 'especialidades' => ['Cardiologia'], 'tipo' => 'medico_pf'],
            ];

            foreach ($prestadoresData as $pData) {
                $pUser = User::updateOrCreate(
                    ['email' => $pData['email']],
                    [
                        'name' => $pData['name'],
                        'password' => bcrypt('senha123'),
                    ]
                );
                $pUser->assignRole($rolePrestador);

                \Illuminate\Support\Facades\DB::table('prestadors')->updateOrInsert(
                    ['user_id' => $pUser->id],
                    [
                        'nome' => $pData['name'],
                        'endereco' => 'Rua de Teste, 123',
                        'tipo' => $pData['tipo'],
                        'especialidades' => json_encode($pData['especialidades']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    protected function getAdminEmail()
    {
        return tenant('id') === 'stfsaude' ? 'admin@stfsaude.com.br' : 'admin@agrocoop.com.br';
    }
}
