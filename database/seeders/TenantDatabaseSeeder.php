<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Associado;
use App\Models\Dependente;
use App\Models\Endereco;
use App\Models\Telefone;
use App\Models\Documento;
use App\Models\ContaBancaria;
use App\Models\CotaCapital;
use App\Models\LancamentoFinanceiro;
use App\Models\Convenio;
use App\Models\PlanoSaude;
use App\Models\PlanoAdesao;
use App\Models\PlanoServicoPreco;
use App\Models\TabelaServico;
use App\Models\Prestador;
use App\Models\PrestadorServico;
use App\Models\SolicitacaoServico;
use App\Models\FaturaPrestador;
use App\Models\Reembolso;
use App\Models\Coparticipacao;
use App\Models\Agendamento;
use App\Models\SolicitacaoInternacao;
use App\Models\ProntuarioHistorico;
use App\Models\CampanhaSaude;
use App\Models\LandingPageSetting;
use App\Models\LgpdSetting;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant's database with comprehensive, interconnected presentation data.
     */
    public function run(): void
    {
        // 1. Always setup Roles and Permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Seed Demo Prestadores de Serviços (Base catalog)
        $this->call(PrestadorSeeder::class);

        // 3. Seed complete test & presentation data
        $this->seedTestData();
    }

    /**
     * Seed test data for local development, testing, and executive presentations.
     */
    protected function seedTestData(): void
    {
        // Seed LGPD and Landing Page defaults for all tenants
        LgpdSetting::firstOrCreate(
            ['id' => 1],
            LgpdSetting::getDefault()
        );

        if (tenant('tipo') === 'fundacao') {
            $this->seedFundacaoData();
            return;
        }

        $this->seedCooperativaData();
    }

    /**
     * Seed complete dataset for Fundação (e.g. Santa Casa da Amazônia)
     */
    protected function seedFundacaoData(): void
    {
        LandingPageSetting::updateOrCreate(
            ['id' => 1],
            [
                'theme_preset' => 'emerald',
                'primary_color' => '#059669',
                'secondary_color' => '#065f46',
                'section_hero_enabled' => true,
                'hero_title' => 'Santa Casa da Amazônia — Saúde Acessível e Humanizada',
                'hero_subtitle' => 'Cartão de benefícios, consultas médicas, exames de alta precisão e internações com valores reduzidos para você e sua família.',
                'section_about_enabled' => true,
                'about_title' => 'Tradição e Inovação em Saúde na Amazônia',
                'about_text' => 'A Fundação Santa Casa da Amazônia oferece atendimento médico-hospitalar de excelência, integrando tecnologia digital, prontuário eletrônico e rede credenciada completa.',
                'section_features_enabled' => true,
                'features' => LandingPageSetting::getDefaultFeatures(),
                'section_contact_enabled' => true,
                'contact_email' => 'contato@santacasa.com.br',
                'contact_phone' => '(92) 3210-4500',
                'contact_address' => 'Av. Djalma Batista, 1500 - Chapada, Manaus - AM',
            ]
        );

        // 1. Usuários Administrativos e Operacionais da Fundação
        $staffUsers = [
            ['email' => 'admin@santacasa.com.br', 'name' => 'Admin Geral da Fundação', 'role' => 'Administrador Fundação'],
            ['email' => 'gerente@santacasa.com.br', 'name' => 'Dra. Helena Gerente Assistencial', 'role' => 'Gerente'],
            ['email' => 'atendente@santacasa.com.br', 'name' => 'Camila Recepção e Agendamento', 'role' => 'Atendente'],
            ['email' => 'conselho@santacasa.com.br', 'name' => 'Dr. Arthur Conselho Curador', 'role' => 'Conselho'],
            ['email' => 'contabil@santacasa.com.br', 'name' => 'Marcos Contabilidade', 'role' => 'Contábil'],
            ['email' => 'auditoria@santacasa.com.br', 'name' => 'Fernanda Auditora Médica', 'role' => 'Auditoria'],
        ];

        foreach ($staffUsers as $uData) {
            $u = User::updateOrCreate(
                ['email' => $uData['email']],
                [
                    'name' => $uData['name'],
                    'password' => bcrypt('senha123'),
                    'accepted_lgpd_at' => now(),
                    'has_temporary_password' => false,
                ]
            );
            $u->syncRoles([$uData['role']]);
        }

        // 2. Contas Bancárias
        ContaBancaria::updateOrCreate(
            ['banco' => 'Banco do Brasil', 'agencia' => '3560-2', 'conta' => '99450-1'],
            ['saldo' => 148500.00]
        );
        ContaBancaria::updateOrCreate(
            ['banco' => 'Caixa Econômica Federal', 'agencia' => '0821', 'conta' => '12044-8'],
            ['saldo' => 64200.00]
        );

        // 3. Tabela de Serviços inicial (20 itens)
        $servicosIniciais = [
            ['codigo' => '001', 'nome' => 'Consulta com clínico geral', 'categoria' => 'consulta', 'valor_base' => 80.00, 'ordem' => 1, 'ativo' => true],
            ['codigo' => '002', 'nome' => 'Exame laboratorial básico', 'categoria' => 'exame_laboratorial', 'valor_base' => 80.00, 'ordem' => 2, 'ativo' => true],
            ['codigo' => '003', 'nome' => 'Pronto atendimento', 'categoria' => 'pronto_atendimento', 'valor_base' => 80.00, 'ordem' => 3, 'ativo' => true],
            ['codigo' => '004', 'nome' => 'Atestado simples', 'categoria' => 'procedimento', 'valor_base' => 80.00, 'ordem' => 4, 'ativo' => true],
            ['codigo' => '005', 'nome' => 'Atestado especializado', 'categoria' => 'procedimento', 'valor_base' => 300.00, 'ordem' => 5, 'ativo' => true],
            ['codigo' => '006', 'nome' => 'Enfermaria – diária', 'categoria' => 'internacao', 'valor_base' => 100.00, 'ordem' => 6, 'ativo' => true],
            ['codigo' => '007', 'nome' => 'Apartamento – diária', 'categoria' => 'internacao', 'valor_base' => 200.00, 'ordem' => 7, 'ativo' => true],
            ['codigo' => '008', 'nome' => 'Ultrassonografia', 'categoria' => 'exame_imagem', 'valor_base' => 80.00, 'ordem' => 8, 'ativo' => true],
            ['codigo' => '009', 'nome' => 'RX de tórax', 'categoria' => 'exame_imagem', 'valor_base' => 80.00, 'ordem' => 9, 'ativo' => true],
            ['codigo' => '010', 'nome' => 'RX de membros', 'categoria' => 'exame_imagem', 'valor_base' => 50.00, 'ordem' => 10, 'ativo' => true],
            ['codigo' => '011', 'nome' => 'Tomografia', 'categoria' => 'exame_imagem', 'valor_base' => 300.00, 'ordem' => 11, 'ativo' => true],
            ['codigo' => '012', 'nome' => 'Tomografia com contraste e laudo', 'categoria' => 'exame_imagem', 'valor_base' => 500.00, 'ordem' => 12, 'ativo' => true],
            ['codigo' => '013', 'nome' => 'Pequena cirurgia', 'categoria' => 'cirurgia', 'valor_base' => 300.00, 'ordem' => 13, 'ativo' => true],
            ['codigo' => '014', 'nome' => 'Anestesia para cirurgia simples', 'categoria' => 'cirurgia', 'valor_base' => 200.00, 'ordem' => 14, 'ativo' => true],
            ['codigo' => '015', 'nome' => 'Anestesia para cirurgia normal', 'categoria' => 'cirurgia', 'valor_base' => 800.00, 'ordem' => 15, 'ativo' => true],
            ['codigo' => '016', 'nome' => 'Coleta de sangue para exames', 'categoria' => 'exame_laboratorial', 'valor_base' => 50.00, 'ordem' => 16, 'ativo' => true],
            ['codigo' => '017', 'nome' => 'Fisioterapia – sessão', 'categoria' => 'fisioterapia', 'valor_base' => 70.00, 'ordem' => 17, 'ativo' => true],
            ['codigo' => '018', 'nome' => 'Serviço odontológico – extração/limpeza simples', 'categoria' => 'odontologia', 'valor_base' => 100.00, 'ordem' => 18, 'ativo' => true],
            ['codigo' => '019', 'nome' => 'Cartão Santa Casa – pessoa física', 'categoria' => 'outros', 'valor_base' => 50.00, 'ordem' => 19, 'ativo' => true],
            ['codigo' => '020', 'nome' => 'Cartão Santa Casa – família (2+ dependentes)', 'categoria' => 'outros', 'valor_base' => 30.00, 'ordem' => 20, 'ativo' => true],
        ];

        $servicosMap = [];
        foreach ($servicosIniciais as $sData) {
            $servicosMap[$sData['codigo']] = TabelaServico::updateOrCreate(
                ['codigo' => $sData['codigo']],
                $sData
            );
        }

        // 4. Planos de Adesão
        $planoPF = PlanoAdesao::updateOrCreate(
            ['nome' => 'Cartão Santa Casa PF'],
            [
                'tipo' => 'individual',
                'valor_adesao' => 50.00,
                'valor_mensalidade' => 45.00,
                'descricao' => 'Cartão individual com acesso a toda tabela de serviços com preços reduzidos.',
                'beneficios' => ['Acesso à tabela reduzida', 'Carteirinha Digital com QR Code', 'Agendamento Online', 'Pronto Atendimento 24h'],
                'ativo' => true,
                'ordem' => 1,
            ]
        );

        $planoFamilia = PlanoAdesao::updateOrCreate(
            ['nome' => 'Cartão Santa Casa Família'],
            [
                'tipo' => 'familiar',
                'valor_adesao' => 30.00,
                'valor_mensalidade' => 89.90,
                'max_dependentes' => 5,
                'descricao' => 'Cartão familiar completo para titular e dependentes diretos com descontos especiais.',
                'beneficios' => ['Inclusão de até 5 dependentes', 'Desconto extra em consultas e exames', 'Carteirinha Digital para todos', 'Link Mágico para Dependentes'],
                'ativo' => true,
                'ordem' => 2,
            ]
        );

        $planoEmpresarial = PlanoAdesao::updateOrCreate(
            ['nome' => 'Plano Santa Casa Empresarial'],
            [
                'tipo' => 'empresarial',
                'valor_adesao' => 0.00,
                'valor_mensalidade' => 39.90,
                'min_membros' => 10,
                'descricao' => 'Plano corporativo para empresas parceiras com isenção de taxa de adesão.',
                'beneficios' => ['Isenção de taxa de adesão', 'Check-up anual ocupacional', 'Telemedicina inclusa'],
                'ativo' => true,
                'ordem' => 3,
            ]
        );

        // Preços personalizados no Plano Família
        PlanoServicoPreco::updateOrCreate(
            ['plano_adesao_id' => $planoFamilia->id, 'tabela_servico_id' => $servicosMap['001']->id],
            ['valor_personalizado' => 60.00, 'desconto_percentual' => 25.00, 'incluso' => false]
        );
        PlanoServicoPreco::updateOrCreate(
            ['plano_adesao_id' => $planoFamilia->id, 'tabela_servico_id' => $servicosMap['002']->id],
            ['valor_personalizado' => 55.00, 'desconto_percentual' => 31.25, 'incluso' => false]
        );

        // 5. Clientes e Fluxos da Fundação
        $roleCliente = Role::where('name', 'Cliente')->first();
        $roleDependente = Role::where('name', 'Dependente')->first();

        $clienteUser = User::updateOrCreate(
            ['email' => 'cliente@santacasa.com.br'],
            [
                'name' => 'Pedro Cliente da Silva',
                'password' => bcrypt('senha123'),
                'accepted_lgpd_at' => now(),
                'has_temporary_password' => false,
            ]
        );
        $clienteUser->syncRoles([$roleCliente]);

        $pedro = Associado::updateOrCreate(
            ['user_id' => $clienteUser->id],
            [
                'nome' => 'Pedro Cliente da Silva',
                'cpf' => '98765432100',
                'email' => 'cliente@santacasa.com.br',
                'nascimento' => '1990-05-20',
                'status' => 'ativo',
                'matricula' => '20260001',
                'data_adesao' => now()->subMonths(4),
                'plano_adesao_id' => $planoFamilia->id,
                'carteirinha_numero' => '2026-00001',
                'carteirinha_tipo' => 'familiar',
                'carteirinha_validade' => now()->addYear(),
                'carteirinha_status' => 'ativa',
                'carteirinha_emitida_em' => now()->subMonths(4),
            ]
        );

        $this->seedCadastroComplementar($pedro, 'Av. Constantino Nery', '2450', 'Chapada', '69050-001', 'Manaus', 'AM', '(92) 99123-4567');

        // Dependente de Pedro Cliente
        $depUser = User::updateOrCreate(
            ['email' => 'dependente.pedro@santacasa.com.br'],
            [
                'name' => 'Juliana Silva (Dependente)',
                'password' => bcrypt('senha123'),
                'accepted_lgpd_at' => now(),
                'has_temporary_password' => false,
            ]
        );
        $depUser->syncRoles([$roleDependente]);

        Dependente::updateOrCreate(
            ['associado_id' => $pedro->id, 'nome' => 'Juliana Mendes da Silva'],
            [
                'cpf' => '45678912300',
                'rg' => '2233445-6',
                'cartao_sus' => '700123456789012',
                'data_nascimento' => '1992-08-14',
                'parentesco' => 'conjuge',
                'tipo_dependencia' => 'legal',
                'autoriza_acesso' => true,
                'email' => 'dependente.pedro@santacasa.com.br',
                'user_id' => $depUser->id,
                'access_token' => 'magiclink-santacasa-juliana-2026',
                'status' => 'ativo',
            ]
        );

        // Cliente 2 (Ativa)
        $cliente2User = User::updateOrCreate(
            ['email' => 'lucia.ferreira@gmail.com'],
            [
                'name' => 'Lúcia Ferreira Gomes',
                'password' => bcrypt('senha123'),
                'accepted_lgpd_at' => now(),
                'has_temporary_password' => false,
            ]
        );
        $cliente2User->syncRoles([$roleCliente]);

        $lucia = Associado::updateOrCreate(
            ['user_id' => $cliente2User->id],
            [
                'nome' => 'Lúcia Ferreira Gomes',
                'cpf' => '32165498701',
                'email' => 'lucia.ferreira@gmail.com',
                'nascimento' => '1978-11-03',
                'status' => 'ativo',
                'matricula' => '20260002',
                'data_adesao' => now()->subMonths(2),
                'plano_adesao_id' => $planoPF->id,
                'carteirinha_numero' => '2026-00002',
                'carteirinha_tipo' => 'individual',
                'carteirinha_validade' => now()->addYear(),
                'carteirinha_status' => 'ativa',
                'carteirinha_emitida_em' => now()->subMonths(2),
            ]
        );
        $this->seedCadastroComplementar($lucia, 'Rua Paraíba', '512', 'Adrianópolis', '69057-020', 'Manaus', 'AM', '(92) 98111-2233');

        // Proposta Pendente na fila da Fundação
        $pendenteFundacao = Associado::updateOrCreate(
            ['matricula' => '20260003'],
            [
                'nome' => 'Ricardo Tavares Nascimento',
                'cpf' => '74185296300',
                'email' => 'ricardo.tavares@gmail.com',
                'nascimento' => '1984-03-19',
                'status' => 'pendente',
                'data_adesao' => now()->subDays(1),
                'plano_adesao_id' => $planoFamilia->id,
                'forma_pagamento_adesao' => 'pix',
            ]
        );
        $this->seedCadastroComplementar($pendenteFundacao, 'Av. Brasil', '1020', 'Compensa', '69036-110', 'Manaus', 'AM', '(92) 99333-4455');

        // Agendamentos, Internações, Prontuário e Campanhas para Fundação
        $primeiroPrestador = Prestador::first();
        if ($primeiroPrestador) {
            Agendamento::updateOrCreate(
                ['user_id' => $clienteUser->id, 'especialidade' => 'Clínica Geral'],
                [
                    'tipo' => 'consulta',
                    'prestador_id' => $primeiroPrestador->id,
                    'tabela_servico_id' => $servicosMap['001']->id,
                    'valor_cobrado' => 60.00,
                    'modalidade' => 'presencial',
                    'data_hora' => now()->addDays(2)->setHour(10)->setMinute(0),
                    'status' => 'confirmado',
                    'forma_pagamento' => 'pix_manual',
                    'observacoes' => 'Consulta de rotina e avaliação de exames laboratoriais.',
                ]
            );

            Agendamento::updateOrCreate(
                ['user_id' => $cliente2User->id, 'especialidade' => 'Exames Laboratoriais'],
                [
                    'tipo' => 'exame',
                    'prestador_id' => $primeiroPrestador->id,
                    'tabela_servico_id' => $servicosMap['002']->id,
                    'valor_cobrado' => 80.00,
                    'modalidade' => 'presencial',
                    'data_hora' => now()->subDays(5)->setHour(8)->setMinute(30),
                    'status' => 'realizado',
                    'forma_pagamento' => 'presencial',
                    'observacoes' => 'Jejum de 12 horas confirmado.',
                ]
            );
        }

        SolicitacaoInternacao::updateOrCreate(
            ['user_id' => $clienteUser->id, 'procedimento_principal' => 'Colecistectomia Videolaparoscópica'],
            [
                'hospital_clinica' => 'Hospital Santa Casa da Amazônia',
                'data_prevista' => now()->addDays(14)->toDateString(),
                'crm_medico' => 'CRM-AM 5890',
                'nome_medico' => 'Dr. Roberto Albuquerque',
                'justificativa_clinica' => 'Paciente com colelitíase sintomática confirmada por ultrassonografia abdominal.',
                'status' => 'analise',
            ]
        );

        ProntuarioHistorico::updateOrCreate(
            ['user_id' => $clienteUser->id, 'diagnostico_principal' => 'Check-up Clínico Preventivo Anual (Z00.0)'],
            [
                'medico_nome' => 'Dra. Camila Fontes',
                'crm' => 'CRM-AM 4412',
                'data_atendimento' => now()->subDays(20)->toDateString(),
                'prescricao' => 'Vitamina D3 7.000 UI semanal por 8 semanas; manter atividade física regular.',
                'observacoes_clinicas' => 'Pressão arterial 120/80 mmHg, FC 72 bpm. Exames laboratoriais dentro dos parâmetros da normalidade.',
            ]
        );

        CampanhaSaude::updateOrCreate(
            ['titulo' => 'Outubro Rosa & Prevenção Integral'],
            [
                'descricao' => 'Mutirão de consultas ginecológicas, mamografia e ultrassonografia com 50% de desconto adicional para titulares e dependentes.',
                'tipo' => 'prevencao',
                'publico_alvo' => 'todos',
                'data_inicio' => now()->startOfMonth()->toDateString(),
                'data_fim' => now()->addMonth()->endOfMonth()->toDateString(),
                'ativo' => true,
                'meta_participantes' => 200,
                'total_participantes' => 134,
                'notificar_membros' => true,
            ]
        );

        CampanhaSaude::updateOrCreate(
            ['titulo' => 'Campanha de Vacinação Antiviral 2026'],
            [
                'descricao' => 'Imunização contra Influenza Tetravalente disponível nas unidades credenciadas.',
                'tipo' => 'vacinacao',
                'publico_alvo' => 'familiar',
                'data_inicio' => now()->subDays(15)->toDateString(),
                'data_fim' => now()->addDays(45)->toDateString(),
                'ativo' => true,
                'meta_participantes' => 500,
                'total_participantes' => 318,
                'notificar_membros' => true,
            ]
        );

        // Histórico Financeiro de 6 meses para os gráficos da Fundação
        $this->seedHistoricoFinanceiroSeisMeses($pedro, $lucia);
    }

    /**
     * Seed complete dataset for Cooperativa (stfsaude & agrocoop)
     */
    protected function seedCooperativaData(): void
    {
        $tenantId = tenant('id');
        $isCb = $tenantId === 'cbsaude';
        $isStf = $tenantId === 'stfsaude';
        $domainSuffix = $isCb ? 'cbsaude.com.br' : ($isStf ? 'stfsaude.com.br' : 'agrocoop.com.br');

        $heroTitle = $isCb
            ? 'CB Saúde — Cooperativa de Saúde, Governança e Benefícios'
            : ($isStf
                ? 'STF Saúde Co-op — Governança, Proteção e Patrimônio Cooperativo'
                : 'Agro Valley Co-op — Força Cooperativa no Campo');

        $primaryColor = $isCb ? '#0d9488' : '#4f46e5'; // Teal elegante para CB Saúde
        $secondaryColor = $isCb ? '#0f766e' : '#3730a3';

        LandingPageSetting::updateOrCreate(
            ['id' => 1],
            [
                'theme_preset' => $isCb ? 'emerald' : 'indigo',
                'primary_color' => $primaryColor,
                'secondary_color' => $secondaryColor,
                'section_hero_enabled' => true,
                'hero_title' => $heroTitle,
                'hero_subtitle' => 'Faça parte de uma cooperativa de saúde sólida, com formação de Cota Capital, rede médica credenciada, reembolsos assistenciais e transparência total.',
                'section_about_enabled' => true,
                'about_title' => 'Nossa Missão Cooperativista na Saúde',
                'about_text' => 'Unimos gestão médica moderna, conformidade legal e benefícios reais em saúde e qualidade de vida para todos os nossos cooperados e seus dependentes.',
                'section_features_enabled' => true,
                'features' => LandingPageSetting::getDefaultFeatures(),
                'section_contact_enabled' => true,
                'contact_email' => 'contato@' . $domainSuffix,
                'contact_phone' => '(92) 3300-8000',
                'contact_address' => 'Av. Mário Ypiranga, 1000 - Adrianópolis, Manaus - AM',
            ]
        );

        // 1. Todos os Perfis Administrativos e Governança Cooperativa
        $testUsers = [
            ['email' => 'admin@' . $domainSuffix, 'name' => 'Admin Geral da Cooperativa', 'role' => 'Administrador Cooperativa'],
            ['email' => 'presidente@' . $domainSuffix, 'name' => 'Dr. Roberto Presidente', 'role' => 'Presidente'],
            ['email' => 'diretor@' . $domainSuffix, 'name' => 'Carlos Diretor Adm. Financeiro', 'role' => 'Diretor Administrativo Financeiro'],
            ['email' => 'gerente@' . $domainSuffix, 'name' => 'Hélio Gerente de Operações', 'role' => 'Gerente'],
            ['email' => 'fiscal@' . $domainSuffix, 'name' => 'Dra. Patrícia Conselho Fiscal', 'role' => 'Presidente do Conselho Fiscal'],
            ['email' => 'consultor@' . $domainSuffix, 'name' => ' Eduardo Consultor Estratégico', 'role' => 'Consultor'],
            ['email' => 'auditoria@' . $domainSuffix, 'name' => 'Sérgio Auditor Independente', 'role' => 'Auditoria'],
            ['email' => 'contabil@' . $domainSuffix, 'name' => 'Mariana Contadora', 'role' => 'Contábil'],
            ['email' => 'atendente@' . $domainSuffix, 'name' => 'Beatriz Atendimento ao Cooperado', 'role' => 'Atendente'],
            ['email' => 'conselho@' . $domainSuffix, 'name' => 'Conselheiro Vogal da Diretoria', 'role' => 'Conselho'],
        ];

        $adminUser = null;
        foreach ($testUsers as $testUser) {
            $user = User::updateOrCreate(
                ['email' => $testUser['email']],
                [
                    'name' => trim($testUser['name']),
                    'password' => bcrypt('senha123'),
                    'accepted_lgpd_at' => now(),
                    'has_temporary_password' => false,
                ]
            );
            $user->syncRoles([$testUser['role']]);
            if ($testUser['role'] === 'Administrador Cooperativa') {
                $adminUser = $user;
            }
        }

        // 2. Contas Bancárias da Cooperativa
        ContaBancaria::updateOrCreate(
            ['banco' => 'Sicoob Cooperativo', 'agencia' => '4102', 'conta' => '78900-4'],
            ['saldo' => 284750.00]
        );
        ContaBancaria::updateOrCreate(
            ['banco' => 'Sicredi', 'agencia' => '0915', 'conta' => '33210-9'],
            ['saldo' => 112400.00]
        );

        // 3. Planos de Saúde e Planos de Adesão da Cooperativa
        PlanoSaude::updateOrCreate(
            ['nome' => 'Plano Cooperado Essencial'],
            [
                'coberturas' => ['Consultas Eletivas', 'Exames Laboratoriais', 'Exames de Imagem Simples', 'Atendimento Ambulatorial'],
                'faixas_etarias' => [
                    ['faixa' => '00 a 18 anos', 'valor' => 145.00],
                    ['faixa' => '19 a 38 anos', 'valor' => 210.00],
                    ['faixa' => '39 a 58 anos', 'valor' => 320.00],
                    ['faixa' => '59+ anos', 'valor' => 490.00],
                ],
            ]
        );

        PlanoSaude::updateOrCreate(
            ['nome' => 'Plano Cooperado Executivo Master'],
            [
                'coberturas' => ['Consultas e Exames Completa', 'Internação em Apartamento', 'Cirurgias Eletivas', 'Reembolso Livre Escolha (70%)', 'Odontologia Preventiva'],
                'faixas_etarias' => [
                    ['faixa' => '00 a 18 anos', 'valor' => 240.00],
                    ['faixa' => '19 a 38 anos', 'valor' => 360.00],
                    ['faixa' => '39 a 58 anos', 'valor' => 510.00],
                    ['faixa' => '59+ anos', 'valor' => 780.00],
                ],
            ]
        );

        $planoCoopTitular = PlanoAdesao::updateOrCreate(
            ['nome' => 'Adesão Cooperado Titular'],
            [
                'tipo' => 'individual',
                'valor_adesao' => 100.00,
                'valor_mensalidade' => 150.00,
                'descricao' => 'Ingresso societário com formação de Cota Capital e acesso integral aos convênios e rede médica.',
                'beneficios' => ['Participação nas Sobras', 'Cota Capital Resgatável', 'Rede Médica Credenciada', 'Reembolso de Consultas (70%)'],
                'ativo' => true,
                'ordem' => 1,
            ]
        );

        $planoCoopFamiliar = PlanoAdesao::updateOrCreate(
            ['nome' => 'Adesão Cooperado Familiar'],
            [
                'tipo' => 'familiar',
                'valor_adesao' => 100.00,
                'valor_mensalidade' => 230.00,
                'max_dependentes' => 6,
                'descricao' => 'Proteção extensiva aos dependentes legais e agregados com Link Mágico e Carteirinha Digital.',
                'beneficios' => ['Cota Capital Titular', 'Cobertura para Dependentes', 'Carteirinha Digital Individualizada', 'Descontos em Farmácias e Ensino'],
                'ativo' => true,
                'ordem' => 2,
            ]
        );

        // Tabela de Serviços também para Cooperativa (usada em agendamentos/consultas)
        $servicosCoop = [
            ['codigo' => 'C01', 'nome' => 'Consulta Médica Especializada', 'categoria' => 'consulta', 'valor_base' => 120.00, 'ordem' => 1, 'ativo' => true],
            ['codigo' => 'C02', 'nome' => 'Check-up Cardiológico Completo', 'categoria' => 'consulta', 'valor_base' => 220.00, 'ordem' => 2, 'ativo' => true],
            ['codigo' => 'E01', 'nome' => 'Hemograma + Bioquímica Completa', 'categoria' => 'exame_laboratorial', 'valor_base' => 85.00, 'ordem' => 3, 'ativo' => true],
            ['codigo' => 'E02', 'nome' => 'Ultrassonografia Abdominal Total', 'categoria' => 'exame_imagem', 'valor_base' => 160.00, 'ordem' => 4, 'ativo' => true],
            ['codigo' => 'O01', 'nome' => 'Profilaxia e Limpeza Odontológica', 'categoria' => 'odontologia', 'valor_base' => 130.00, 'ordem' => 5, 'ativo' => true],
        ];
        $servicosMap = [];
        foreach ($servicosCoop as $sc) {
            $servicosMap[$sc['codigo']] = TabelaServico::updateOrCreate(['codigo' => $sc['codigo']], $sc);
        }

        // 4. Convênios e Parceiros de Benefícios
        $conveniosData = [
            ['nome_empresa' => 'Rede DrogaRaia & Drogasil', 'ramo' => 'Farmácia e Medicamentos', 'desconto' => 25.00, 'contato' => '0800 777 4500', 'descricao' => 'Até 25% de desconto em medicamentos tarjados e genéricos apresentando a Carteirinha Digital.', 'ativo' => true],
            ['nome_empresa' => 'Óticas Diniz Prime', 'ramo' => 'Ótica e Oftalmologia', 'desconto' => 30.00, 'contato' => '(92) 3234-9900', 'descricao' => '30% de desconto na compra de armações e lentes multifocais para cooperados e dependentes.', 'ativo' => true],
            ['nome_empresa' => 'Centro Universitário Fametro', 'ramo' => 'Educação e Pós-Graduação', 'desconto' => 20.00, 'contato' => '(92) 2101-1000', 'descricao' => 'Bolsa convênio de 20% nas mensalidades de graduação e pós-graduação.', 'ativo' => true],
            ['nome_empresa' => 'SmartFit Academias', 'ramo' => 'Bem-Estar e Condicionamento', 'desconto' => 15.00, 'contato' => 'convenios@smartfit.com.br', 'descricao' => 'Isenção de taxa de adesão e desconto corporativo no Plano Black.', 'ativo' => true],
        ];
        foreach ($conveniosData as $cData) {
            Convenio::updateOrCreate(['nome_empresa' => $cData['nome_empresa']], $cData);
        }

        if (!$isStf && !$isCb) {
            return;
        }

        // =========================================================================
        // CENÁRIOS DETALHADOS DO TENANT PRINCIPAL DE APRESENTAÇÃO: stfsaude
        // =========================================================================
        $roleAssociado = Role::where('name', 'Associado')->first();
        $roleDependente = Role::where('name', 'Dependente')->first();
        $rolePrestador = Role::findOrCreate('Prestador', 'web');

        // -------------------------------------------------------------------------
        // COOPERADO 1: JOÃO SILVA ASSOCIADO (Adimplente Exemplar — Fluxo Completo)
        // -------------------------------------------------------------------------
        $joaoUser = User::updateOrCreate(
            ['email' => 'joao.associado@gmail.com'],
            [
                'name' => 'João Silva Associado',
                'password' => bcrypt('senha123'),
                'accepted_lgpd_at' => now(),
                'has_temporary_password' => false,
            ]
        );
        $joaoUser->syncRoles([$roleAssociado]);

        $joao = Associado::updateOrCreate(
            ['user_id' => $joaoUser->id],
            [
                'nome' => 'João Silva Associado',
                'cpf' => '12345678909',
                'email' => 'joao.associado@gmail.com',
                'nascimento' => '1985-10-15',
                'status' => 'ativo',
                'matricula' => '20260101',
                'data_adesao' => now()->subMonths(6),
                'plano_adesao_id' => $planoCoopFamiliar->id,
                'carteirinha_numero' => '2026-00101',
                'carteirinha_tipo' => 'familiar',
                'carteirinha_validade' => now()->addYear(),
                'carteirinha_status' => 'ativa',
                'carteirinha_emitida_em' => now()->subMonths(6),
                'aprovado_por_user_id' => $adminUser?->id,
                'aprovado_por_nome' => $adminUser?->name ?? 'Admin Geral da Cooperativa',
                'aprovado_em' => now()->subMonths(6),
                'forma_pagamento_aprovacao' => 'pix',
            ]
        );
        $this->seedCadastroComplementar($joao, 'Av. Djalma Batista', '1850', 'Chapada', '69050-010', 'Manaus', 'AM', '(92) 99145-8899');

        // Dependentes de João Silva (com Link Mágico pronto e login próprio)
        $anaDepUser = User::updateOrCreate(
            ['email' => 'ana.dependente@gmail.com'],
            [
                'name' => 'Ana Beatriz Silva (Dependente)',
                'password' => bcrypt('senha123'),
                'accepted_lgpd_at' => now(),
                'has_temporary_password' => false,
            ]
        );
        $anaDepUser->syncRoles([$roleDependente]);

        $depEsposa = Dependente::updateOrCreate(
            ['associado_id' => $joao->id, 'nome' => 'Ana Beatriz Oliveira Silva'],
            [
                'cpf' => '52998224725', // 5 primeiros dígitos para Link Mágico: 52998
                'rg' => '1844520-1',
                'cartao_sus' => '704500112233445',
                'data_nascimento' => '1988-04-12',
                'parentesco' => 'conjuge',
                'tipo_dependencia' => 'legal',
                'autoriza_acesso' => true,
                'email' => 'ana.dependente@gmail.com',
                'user_id' => $anaDepUser->id,
                'access_token' => 'token-ana-beatriz-stfsaude-2026',
                'status' => 'ativo',
            ]
        );

        $depFilho = Dependente::updateOrCreate(
            ['associado_id' => $joao->id, 'nome' => 'Lucas Oliveira Silva'],
            [
                'cpf' => '11144477735', // 5 primeiros dígitos para Link Mágico: 11144
                'rg' => '3099881-2',
                'cartao_sus' => '704500998877665',
                'data_nascimento' => '2015-09-22',
                'parentesco' => 'filho',
                'tipo_dependencia' => 'legal',
                'autoriza_acesso' => true,
                'email' => 'lucas.silva@gmail.com',
                'access_token' => 'token-lucas-filho-stfsaude-2026',
                'status' => 'ativo',
            ]
        );

        // Documentos de João Silva (Aprovados e 1 Pendente para demonstração)
        Documento::updateOrCreate(
            ['associado_id' => $joao->id, 'titulo' => 'CNH Digital - João Silva'],
            ['tipo' => 'rg_cnh', 'arquivo_path' => 'documentos/demo_cnh_joao.pdf', 'status' => 'aprovado']
        );
        Documento::updateOrCreate(
            ['associado_id' => $joao->id, 'titulo' => 'Comprovante de Residência Atualizado'],
            ['tipo' => 'comprovante_residencia', 'arquivo_path' => 'documentos/demo_residencia_joao.pdf', 'status' => 'aprovado']
        );
        Documento::updateOrCreate(
            ['associado_id' => $joao->id, 'dependente_id' => $depEsposa->id, 'titulo' => 'Certidão de Casamento - Ana Beatriz'],
            ['tipo' => 'certidao', 'arquivo_path' => 'documentos/demo_casamento_ana.pdf', 'status' => 'pendente']
        );

        // Cotas Capital de João Silva (Evolução Patrimonial)
        CotaCapital::updateOrCreate(
            ['associado_id' => $joao->id, 'tipo_movimento' => 'integralizacao_inicial'],
            ['valor_quota' => 150.00, 'qtd_quotas' => 4, 'saldo' => 600.00]
        );
        CotaCapital::updateOrCreate(
            ['associado_id' => $joao->id, 'tipo_movimento' => 'integralizacao', 'qtd_quotas' => 10],
            ['valor_quota' => 150.00, 'saldo' => 1500.00]
        );
        CotaCapital::updateOrCreate(
            ['associado_id' => $joao->id, 'tipo_movimento' => 'integralizacao', 'qtd_quotas' => 6],
            ['valor_quota' => 150.00, 'saldo' => 900.00]
        );

        // -------------------------------------------------------------------------
        // COOPERADA 2: MARIA CLARA SOUZA (Ativa, Adimplente, com Guias e Internação)
        // -------------------------------------------------------------------------
        $mariaUser = User::updateOrCreate(
            ['email' => 'maria.cooperada@gmail.com'],
            [
                'name' => 'Maria Clara Souza',
                'password' => bcrypt('senha123'),
                'accepted_lgpd_at' => now(),
                'has_temporary_password' => false,
            ]
        );
        $mariaUser->syncRoles([$roleAssociado]);

        $maria = Associado::updateOrCreate(
            ['user_id' => $mariaUser->id],
            [
                'nome' => 'Maria Clara Souza',
                'cpf' => '39053344705',
                'email' => 'maria.cooperada@gmail.com',
                'nascimento' => '1991-02-28',
                'status' => 'ativo',
                'matricula' => '20260102',
                'data_adesao' => now()->subMonths(5),
                'plano_adesao_id' => $planoCoopTitular->id,
                'carteirinha_numero' => '2026-00102',
                'carteirinha_tipo' => 'individual',
                'carteirinha_validade' => now()->addYear(),
                'carteirinha_status' => 'ativa',
                'carteirinha_emitida_em' => now()->subMonths(5),
                'aprovado_por_user_id' => $adminUser?->id,
                'aprovado_por_nome' => $adminUser?->name ?? 'Admin Geral da Cooperativa',
                'aprovado_em' => now()->subMonths(5),
            ]
        );
        $this->seedCadastroComplementar($maria, 'Rua Rio Javari', '420', 'Nossa Senhora das Graças', '69053-110', 'Manaus', 'AM', '(92) 98234-5678');

        CotaCapital::updateOrCreate(
            ['associado_id' => $maria->id, 'tipo_movimento' => 'integralizacao'],
            ['valor_quota' => 150.00, 'qtd_quotas' => 8, 'saldo' => 1200.00]
        );

        // -------------------------------------------------------------------------
        // COOPERADO 3: CARLOS EDUARDO MENDES (INADIMPLENTE — Testa Bloqueio no Prestador!)
        // -------------------------------------------------------------------------
        // Nota importante: PrestadorService::isAssociadoInadimplente() verifica
        // lancamentosFinanceiros com categoria='cota_capital' e status='pendente'!
        $carlosUser = User::updateOrCreate(
            ['email' => 'carlos.inadimplente@gmail.com'],
            [
                'name' => 'Carlos Eduardo Mendes',
                'password' => bcrypt('senha123'),
                'accepted_lgpd_at' => now(),
                'has_temporary_password' => false,
            ]
        );
        $carlosUser->syncRoles([$roleAssociado]);

        $carlos = Associado::updateOrCreate(
            ['user_id' => $carlosUser->id],
            [
                'nome' => 'Carlos Eduardo Mendes',
                'cpf' => '85296374104',
                'email' => 'carlos.inadimplente@gmail.com',
                'nascimento' => '1979-07-10',
                'status' => 'ativo',
                'matricula' => '20260103',
                'data_adesao' => now()->subMonths(4),
                'plano_adesao_id' => $planoCoopTitular->id,
                'carteirinha_numero' => '2026-00103',
                'carteirinha_tipo' => 'individual',
                'carteirinha_validade' => now()->addYear(),
                'carteirinha_status' => 'ativa',
                'carteirinha_emitida_em' => now()->subMonths(4),
            ]
        );
        $this->seedCadastroComplementar($carlos, 'Av. Torquato Tapajós', '3100', 'Flores', '69058-830', 'Manaus', 'AM', '(92) 99456-1122');

        // Lançamento de Cota Capital Pendente/Atrasado que aciona o bloqueio automático de elegibilidade no Portal do Prestador
        LancamentoFinanceiro::updateOrCreate(
            [
                'associado_id' => $carlos->id,
                'categoria' => 'cota_capital',
                'status' => 'pendente',
            ],
            [
                'tipo' => 'receita',
                'valor' => 150.00,
                'data_vencimento' => now()->subDays(15)->toDateString(),
                'forma_pagamento' => 'pix_manual',
                'observacoes_pagamento' => 'Parcela de Cota Capital em aberto (bloqueia elegibilidade para novos procedimentos).',
            ]
        );

        LancamentoFinanceiro::updateOrCreate(
            [
                'associado_id' => $carlos->id,
                'categoria' => 'mensalidade',
                'status' => 'atrasado',
            ],
            [
                'tipo' => 'receita',
                'valor' => 150.00,
                'data_vencimento' => now()->subDays(25)->toDateString(),
                'forma_pagamento' => 'pix_manual',
                'observacoes_pagamento' => 'Mensalidade em atraso.',
            ]
        );

        // -------------------------------------------------------------------------
        // COOPERADOS 4 E 5: PROPOSTAS DE ADESÃO NA FILA (Prontas para aprovar na apresentação!)
        // -------------------------------------------------------------------------
        $proposta1 = Associado::updateOrCreate(
            ['matricula' => '20260104'],
            [
                'nome' => 'Dra. Fernanda Lima Cavalcante',
                'cpf' => '65498732109',
                'email' => 'fernanda.cavalcante@gmail.com',
                'nascimento' => '1993-12-05',
                'status' => 'pendente',
                'data_adesao' => now()->subHours(18),
                'plano_adesao_id' => $planoCoopFamiliar->id,
                'forma_pagamento_adesao' => 'pix',
            ]
        );
        $this->seedCadastroComplementar($proposta1, 'Rua Salvador', '305', 'Adrianópolis', '69057-040', 'Manaus', 'AM', '(92) 99600-7788');
        Documento::updateOrCreate(
            ['associado_id' => $proposta1->id, 'titulo' => 'Documento de Identidade (RG Frente e Verso)'],
            ['tipo' => 'rg_cnh', 'arquivo_path' => 'documentos/proposta_rg_fernanda.pdf', 'status' => 'pendente']
        );

        $proposta2 = Associado::updateOrCreate(
            ['matricula' => '20260105'],
            [
                'nome' => 'Marcos Vinícius Peixoto',
                'cpf' => '15975348602',
                'email' => 'marcos.peixoto@gmail.com',
                'nascimento' => '1982-06-18',
                'status' => 'analise_documental',
                'data_adesao' => now()->subDays(2),
                'plano_adesao_id' => $planoCoopTitular->id,
                'forma_pagamento_adesao' => 'boleto',
            ]
        );
        $this->seedCadastroComplementar($proposta2, 'Av. Constantino Nery', '980', 'São Geraldo', '69053-000', 'Manaus', 'AM', '(92) 98877-6655');

        // -------------------------------------------------------------------------
        // 5. PRESTADORES CREDENCIADOS, CATÁLOGO DE SERVIÇOS, AUTORIZAÇÕES E FATURAS
        // -------------------------------------------------------------------------
        $prestadoresCooperativa = [
            [
                'name' => 'Clínica São Lucas',
                'email' => 'sao.lucas@gmail.com',
                'especialidades' => ['Pediatria', 'Clínica Geral', 'Cardiologia'],
                'tipo' => 'clinica',
                'endereco' => 'Av. Djalma Batista, 2100 - Chapada, Manaus - AM',
                'servicos' => [
                    ['nome' => 'Consulta Clínica Geral Cooperado', 'descricao' => 'Atendimento eletivo em consultório', 'valor_acordado' => 110.00],
                    ['nome' => 'Avaliação Cardiológica + Eletrocardiograma', 'descricao' => 'Consulta com cardiologista e laudo de ECG', 'valor_acordado' => 210.00],
                    ['nome' => 'Consulta Pediátrica Completa', 'descricao' => 'Acompanhamento infantil para dependentes', 'valor_acordado' => 130.00],
                ],
            ],
            [
                'name' => 'Laboratório Central Diagnósticos',
                'email' => 'lab.central@gmail.com',
                'especialidades' => ['Análises Clínicas', 'Exames de Imagem', 'Genética'],
                'tipo' => 'hospital',
                'endereco' => 'Rua Maceió, 740 - Adrianópolis, Manaus - AM',
                'servicos' => [
                    ['nome' => 'Check-up Laboratorial Completo (15 exames)', 'descricao' => 'Hemograma, lipidograma, glicemia, função renal e hepática', 'valor_acordado' => 145.00],
                    ['nome' => 'Ultrassonografia Abdominal Total', 'descricao' => 'Exame de imagem com laudo imediato', 'valor_acordado' => 165.00],
                ],
            ],
            [
                'name' => 'Dr. Marcelo Silva (Cardiologia)',
                'email' => 'marcelo.silva@gmail.com',
                'especialidades' => ['Cardiologia', 'Ecocardiografia'],
                'tipo' => 'medico_pf',
                'endereco' => 'Ed. Cristal Tower, Sala 804 - Adrianópolis, Manaus - AM',
                'servicos' => [
                    ['nome' => 'Ecocardiograma Transtorácico com Doppler', 'descricao' => 'Exame cardiológico especializado', 'valor_acordado' => 280.00],
                    ['nome' => 'Teste Ergométrico Computadorizado', 'descricao' => 'Avaliação cardiopulmonar de esforço', 'valor_acordado' => 240.00],
                ],
            ],
        ];

        $prestadoresModels = [];
        foreach ($prestadoresCooperativa as $pData) {
            $pUser = User::updateOrCreate(
                ['email' => $pData['email']],
                [
                    'name' => $pData['name'],
                    'password' => bcrypt('senha123'),
                    'accepted_lgpd_at' => now(),
                    'has_temporary_password' => false,
                ]
            );
            $pUser->syncRoles([$rolePrestador]);

            $prestador = Prestador::updateOrCreate(
                ['user_id' => $pUser->id],
                [
                    'nome' => $pData['name'],
                    'endereco' => $pData['endereco'],
                    'tipo' => $pData['tipo'],
                    'especialidades' => $pData['especialidades'],
                ]
            );

            foreach ($pData['servicos'] as $sItem) {
                PrestadorServico::updateOrCreate(
                    ['prestador_id' => $prestador->id, 'nome' => $sItem['nome']],
                    [
                        'descricao' => $sItem['descricao'],
                        'valor_acordado' => $sItem['valor_acordado'],
                        'ativo' => true,
                    ]
                );
            }

            $prestadoresModels[] = $prestador;
        }

        $clinicaSaoLucas = $prestadoresModels[0];
        $labCentral = $prestadoresModels[1];
        $drMarcelo = $prestadoresModels[2];

        $servicoCardio = $clinicaSaoLucas->servicos()->where('nome', 'like', '%Cardiológica%')->first();
        $servicoClinico = $clinicaSaoLucas->servicos()->first();
        $servicoLab = $labCentral->servicos()->first();
        $servicoEco = $drMarcelo->servicos()->first();

        // Faturas de Prestadores (Regra do Dia 05 — Enviada, Em Auditoria e Paga)
        $faturaPaga = FaturaPrestador::updateOrCreate(
            ['prestador_id' => $clinicaSaoLucas->id, 'mes_referencia' => now()->subMonth()->format('m/Y')],
            [
                'valor_total' => 3420.00,
                'status' => 'paga',
                'data_pagamento' => now()->subMonth()->day(5)->toDateString(),
            ]
        );

        $faturaAuditoria = FaturaPrestador::updateOrCreate(
            ['prestador_id' => $labCentral->id, 'mes_referencia' => now()->format('m/Y')],
            [
                'valor_total' => 2180.00,
                'status' => 'em_auditoria',
            ]
        );

        $faturaEnviada = FaturaPrestador::updateOrCreate(
            ['prestador_id' => $clinicaSaoLucas->id, 'mes_referencia' => now()->format('m/Y')],
            [
                'valor_total' => 1890.00,
                'status' => 'enviada',
            ]
        );

        // Solicitações e Agendamentos 100% Online (Elegibilidade Instantânea)
        if ($servicoCardio && $servicoClinico && $servicoLab && $servicoEco) {
            // 1. Agendamento Aprovado Instantaneamente e Confirmado para Amanhã (João Silva)
            $dataAmanha = now()->addDay()->setHour(14)->setMinute(30)->setSecond(0);
            $agendamento1 = Agendamento::updateOrCreate(
                [
                    'user_id' => $joaoUser->id,
                    'especialidade' => $servicoCardio->nome,
                    'prestador_id' => $clinicaSaoLucas->id,
                ],
                [
                    'tipo' => 'consulta',
                    'data_hora' => $dataAmanha,
                    'status' => 'confirmado',
                    'valor_cobrado' => $servicoCardio->valor_acordado,
                    'modalidade' => 'presencial',
                    'observacoes' => 'Agendado online pelo portal do cooperado. Elegibilidade confirmada instantaneamente.',
                ]
            );

            SolicitacaoServico::updateOrCreate(
                [
                    'associado_id' => $joao->id,
                    'prestador_id' => $clinicaSaoLucas->id,
                    'servico_id' => $servicoCardio->id,
                ],
                [
                    'codigo_guia' => 'GUIA-' . now()->format('Ymd') . '-A89K2',
                    'agendamento_id' => $agendamento1->id,
                    'valor_cobrado' => $servicoCardio->valor_acordado,
                    'status' => 'aprovado_cooperativa',
                    'data_agendada' => $dataAmanha,
                    'observacoes' => 'Consulta Cardiológica agendada e autorizada automaticamente pelo sistema.',
                ]
            );

            // 2. Agendamento para a Dependente Ana Beatriz Silva (Filtrada no portal)
            $dataDepoisDeAmanha = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);
            $agendamento2 = Agendamento::updateOrCreate(
                [
                    'user_id' => $joaoUser->id,
                    'especialidade' => $servicoLab->nome,
                    'prestador_id' => $labCentral->id,
                ],
                [
                    'tipo' => 'exame',
                    'data_hora' => $dataDepoisDeAmanha,
                    'status' => 'confirmado',
                    'valor_cobrado' => $servicoLab->valor_acordado,
                    'modalidade' => 'presencial',
                    'observacoes' => 'Check-up Laboratorial agendado online para a dependente Ana Beatriz Silva.',
                ]
            );

            SolicitacaoServico::updateOrCreate(
                [
                    'associado_id' => $joao->id,
                    'dependente_id' => $depEsposa->id,
                    'prestador_id' => $labCentral->id,
                    'servico_id' => $servicoLab->id,
                ],
                [
                    'codigo_guia' => 'GUIA-' . now()->format('Ymd') . '-DEP71',
                    'agendamento_id' => $agendamento2->id,
                    'valor_cobrado' => $servicoLab->valor_acordado,
                    'status' => 'aprovado_cooperativa',
                    'data_agendada' => $dataDepoisDeAmanha,
                    'observacoes' => 'Exames laboratoriais para dependente com autorização digital instantânea.',
                ]
            );

            // 3. Atendimento já Realizado (Concluído pelo Prestador e Faturado)
            SolicitacaoServico::updateOrCreate(
                [
                    'associado_id' => $joao->id,
                    'prestador_id' => $drMarcelo->id,
                    'servico_id' => $servicoEco->id,
                ],
                [
                    'codigo_guia' => 'GUIA-20261015-EXAM1',
                    'valor_cobrado' => $servicoEco->valor_acordado,
                    'status' => 'realizado',
                    'data_agendada' => now()->subDays(6)->setHour(16)->setMinute(0),
                    'data_realizacao' => now()->subDays(6)->setHour(16)->setMinute(45),
                    'observacoes' => 'Ecocardiograma realizado com laudo anexado ao prontuário do cooperado.',
                ]
            );

            // 4. Guia Concluída na Clínica São Lucas para compor o total faturado do mês
            SolicitacaoServico::updateOrCreate(
                [
                    'associado_id' => $maria->id,
                    'prestador_id' => $clinicaSaoLucas->id,
                    'servico_id' => $servicoClinico->id,
                ],
                [
                    'codigo_guia' => 'GUIA-20261018-MED04',
                    'valor_cobrado' => $servicoClinico->valor_acordado,
                    'status' => 'realizado',
                    'data_agendada' => now()->subDays(3)->setHour(11)->setMinute(0),
                    'data_realizacao' => now()->subDays(3)->setHour(11)->setMinute(35),
                    'observacoes' => 'Consulta clínica geral realizada. Paciente medicada.',
                ]
            );
        }

        // -------------------------------------------------------------------------
        // 6. REEMBOLSOS E COPARTICIPAÇÃO (30%)
        // -------------------------------------------------------------------------
        // Reembolso 1: Pendente (pronto para aprovar ao vivo em /saude/reembolsos)
        Reembolso::updateOrCreate(
            ['associado_id' => $joao->id, 'descricao' => 'Consulta Oftalmológica Particular - Clínica da Visão'],
            [
                'valor_solicitado' => 300.00,
                'status' => 'pendente',
                'comprovantes' => ['reembolsos/nota_fiscal_oftalmo_joao.pdf'],
            ]
        );

        // Reembolso 2: Aprovado com Coparticipação gerada
        $reembolsoAprovado = Reembolso::updateOrCreate(
            ['associado_id' => $joao->id, 'descricao' => 'Exame de Ressonância Magnética Lombar'],
            [
                'valor_solicitado' => 500.00,
                'status' => 'aprovado',
                'comprovantes' => ['reembolsos/nf_ressonancia_joao.pdf'],
            ]
        );

        Coparticipacao::updateOrCreate(
            ['reembolso_id' => $reembolsoAprovado->id],
            [
                'valor_total' => 150.00, // 30% de coparticipação retida
                'percentual' => 30.00,
                'status' => 'pago',
            ]
        );

        // Reembolso 3: De Maria Clara (Pendente)
        Reembolso::updateOrCreate(
            ['associado_id' => $maria->id, 'descricao' => 'Sessões de Fisioterapia Ortopédica (5 sessões)'],
            [
                'valor_solicitado' => 450.00,
                'status' => 'pendente',
                'comprovantes' => ['reembolsos/nf_fisio_maria.pdf'],
            ]
        );

        // -------------------------------------------------------------------------
        // 7. AGENDAMENTOS, INTERNAÇÕES, PRONTUÁRIO E CAMPANHAS NA COOPERATIVA
        // -------------------------------------------------------------------------
        Agendamento::updateOrCreate(
            ['user_id' => $joaoUser->id, 'especialidade' => 'Cardiologia'],
            [
                'tipo' => 'consulta',
                'prestador_id' => $clinicaSaoLucas->id,
                'tabela_servico_id' => $servicosMap['C02']->id,
                'valor_cobrado' => 210.00,
                'modalidade' => 'presencial',
                'data_hora' => now()->addDays(3)->setHour(14)->setMinute(30),
                'status' => 'confirmado',
                'forma_pagamento' => 'pix_manual',
                'observacoes' => 'Apresentar exames anteriores e Carteirinha Digital.',
            ]
        );

        Agendamento::updateOrCreate(
            ['user_id' => $mariaUser->id, 'especialidade' => 'Análises Clínicas'],
            [
                'tipo' => 'exame',
                'prestador_id' => $labCentral->id,
                'tabela_servico_id' => $servicosMap['E01']->id,
                'valor_cobrado' => 85.00,
                'modalidade' => 'presencial',
                'data_hora' => now()->addDays(1)->setHour(7)->setMinute(30),
                'status' => 'pendente',
                'forma_pagamento' => 'pix_manual',
                'observacoes' => 'Aguardando confirmação da recepção.',
            ]
        );

        SolicitacaoInternacao::updateOrCreate(
            ['user_id' => $mariaUser->id, 'procedimento_principal' => 'Artroscopia de Joelho Direito'],
            [
                'hospital_clinica' => 'Hospital São Lucas Manaus',
                'data_prevista' => now()->addDays(10)->toDateString(),
                'crm_medico' => 'CRM-AM 3910',
                'nome_medico' => 'Dr. Henrique Vasconcelos',
                'justificativa_clinica' => 'Lesão meniscal medial documentada em ressonância magnética.',
                'status' => 'analise',
            ]
        );

        ProntuarioHistorico::updateOrCreate(
            ['user_id' => $joaoUser->id, 'diagnostico_principal' => 'Avaliação Cardiológica Preventiva (Z01.8)'],
            [
                'medico_nome' => 'Dr. Marcelo Silva',
                'crm' => 'CRM-AM 2980',
                'data_atendimento' => now()->subMonths(1)->toDateString(),
                'prescricao' => 'Manter rotina de exercícios aeróbicos 150 min/semana. Retorno em 6 meses.',
                'observacoes_clinicas' => 'Ritmo cardíaco regular em 2 tempos, sem sopros. ECG normal.',
            ]
        );

        CampanhaSaude::updateOrCreate(
            ['titulo' => 'Check-up Anual do Cooperado 2026'],
            [
                'descricao' => 'Bateria preventiva de exames laboratoriais e avaliação cardiológica sem coparticipação para cooperados adimplentes.',
                'tipo' => 'checkup',
                'publico_alvo' => 'todos',
                'data_inicio' => now()->subDays(10)->toDateString(),
                'data_fim' => now()->addDays(50)->toDateString(),
                'ativo' => true,
                'meta_participantes' => 150,
                'total_participantes' => 92,
                'notificar_membros' => true,
            ]
        );

        // -------------------------------------------------------------------------
        // 8. HISTÓRICO FINANCEIRO DE 6 MESES (Gráficos de Receita x Despesa + Conciliação)
        // -------------------------------------------------------------------------
        $this->seedHistoricoFinanceiroSeisMeses($joao, $maria);
    }

    /**
     * Cria endereço e telefone criptografados via Eloquent para garantir isCadastroCompleto() === true
     */
    protected function seedCadastroComplementar(
        Associado $associado,
        string $logradouro,
        string $numero,
        string $bairro,
        string $cep,
        string $cidade,
        string $uf,
        string $telefone
    ): void {
        if (!$associado->enderecos()->exists()) {
            Endereco::create([
                'associado_id' => $associado->id,
                'tipo' => 'residencial',
                'logradouro' => $logradouro,
                'numero' => $numero,
                'complemento' => 'Apto 101',
                'bairro' => $bairro,
                'cep' => $cep,
                'cidade' => $cidade,
                'uf' => $uf,
                'principal' => true,
            ]);
        }

        if (!$associado->telefones()->exists()) {
            Telefone::create([
                'associado_id' => $associado->id,
                'tipo' => 'whatsapp',
                'numero' => $telefone,
                'principal' => true,
            ]);
        }
    }

    /**
     * Popula lançamentos financeiros nos últimos 6 meses usando 'receita' e 'despesa'
     */
    protected function seedHistoricoFinanceiroSeisMeses(Associado $titular1, Associado $titular2): void
    {
        $serieMensal = [
            5 => ['receita' => 14200.00, 'despesa' => 6800.00],
            4 => ['receita' => 16500.00, 'despesa' => 7200.00],
            3 => ['receita' => 18900.00, 'despesa' => 8100.00],
            2 => ['receita' => 21400.00, 'despesa' => 8900.00],
            1 => ['receita' => 24800.00, 'despesa' => 9400.00],
            0 => ['receita' => 28650.00, 'despesa' => 10200.00],
        ];

        foreach ($serieMensal as $mesesAtras => $valores) {
            $dataRef = now()->subMonths($mesesAtras)->day(10);

            LancamentoFinanceiro::updateOrCreate(
                [
                    'associado_id' => $titular1->id,
                    'tipo' => 'receita',
                    'categoria' => 'mensalidade',
                    'data_vencimento' => $dataRef->toDateString(),
                ],
                [
                    'valor' => $valores['receita'],
                    'status' => 'pago',
                    'forma_pagamento' => 'pix_manual',
                    'data_pagamento' => $dataRef->copy()->subDays(1),
                    'observacoes_pagamento' => 'Faturamento consolidado de mensalidades e cotas da competência ' . $dataRef->format('m/Y'),
                ]
            );

            LancamentoFinanceiro::updateOrCreate(
                [
                    'associado_id' => $titular2->id,
                    'tipo' => 'despesa',
                    'categoria' => 'consulta_medica',
                    'data_vencimento' => $dataRef->copy()->addDays(5)->toDateString(),
                ],
                [
                    'valor' => $valores['despesa'],
                    'status' => 'pago',
                    'forma_pagamento' => 'transferencia',
                    'data_pagamento' => $dataRef->copy()->addDays(5),
                    'observacoes_pagamento' => 'Repasse à rede credenciada e custos operacionais ' . $dataRef->format('m/Y'),
                ]
            );
        }

        // Lançamento pendente no mês atual para João Silva / Pedro Cliente (para exibir no Portal do Cooperado e na tela de Conciliação!)
        LancamentoFinanceiro::updateOrCreate(
            [
                'associado_id' => $titular1->id,
                'tipo' => 'receita',
                'categoria' => 'mensalidade',
                'status' => 'pendente',
            ],
            [
                'valor' => 230.00,
                'data_vencimento' => now()->addDays(5)->toDateString(),
                'forma_pagamento' => 'pix_manual',
                'observacoes_pagamento' => 'Mensalidade do plano familiar - competência atual (pronta para conciliação).',
            ]
        );
    }
}
