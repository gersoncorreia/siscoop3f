# Especificação do Sistema Siscoop3F

Você é um especialista sênior em desenvolvimento de software SaaS multi-tenant, com expertise em PHP Laravel 12, bancos de dados PostgreSQL/MySQL, e integração de ferramentas de IA para prototipagem (Lovable.dev, Google AI Studio, Stitch AI, Antigravity com Claude Opus 4). Desenvolva um sistema completo chamado "Siscoop3F", uma plataforma SaaS para gestão de cooperativas de qualquer ramo (saúde, crédito, agrícola, trabalho, consumo etc.), baseado em análises de documentos manuais de uma cooperativa de saúde (planilhas Excel para conciliação financeira, descontos em folha, cotas capital, pagamentos, mensalidades, recuperação de despesas; PDFs para fichas de adesão e guias médicas; Word para casos de associados). O objetivo é automatizar todos os processos manuais identificados, criando um MVP escalável com arquitetura modular: núcleo genérico + módulos específicos por ramo.

## Requisitos Gerais do Sistema:

*   **Arquitetura Multi-Tenant**: Cada cooperativa é um tenant isolado, com dados privados, branding personalizado e dashboards adaptados ao segmento. Use pacote `stancl/tenancy` ou `tenancyforlaravel` para central domain + subdomínios (ex.: `cooperativa.siscoop3f.com`). Isolamento de dados via `tenant_id` em todas as tabelas ou DB por tenant.
*   **Princípios Cooperativistas**: Ênfase em governança democrática (votações online), transparência (relatórios auditáveis), legalidade (conformidade LGPD, Lei 9.609/1998 para software), sustentabilidade e escalabilidade para crescimento (ex.: 300 novos cooperados/mês por tenant).
*   **Público-Alvo**: Administradores de cooperativas (super admin para o dono do SaaS), admins de tenants, cooperados. Público inicial: Servidores públicos (como no plano PPSAUDE), mas genérico.
*   **Objetivos do Sistema**: Automatizar adesão digital, gestão de membros, financeiro, governança; posicionar como referência em cooperativismo digital no Brasil (Acre/Tocantins inicial).
*   **Conformidade e Segurança**: Total conformidade LGPD (consentimentos explícitos, anonimização, direitos de acesso/exclusão, criptografia end-to-end AES-256); níveis de permissão (RBAC via Spatie Laravel-Permission); auditoria de logs (Spatie Activity Log); 2FA (Laravel Fortify); backups automáticos e recuperação de desastres.
*   **Requisitos Não Funcionais**: Performance (resposta <2s, suporte 100+ usuários simultâneos); disponibilidade SLA 99.5%; acessibilidade WCAG 2.1 AA; mobile-first design.
*   **Modelo de Negócio SaaS**: Super admin gerencia assinaturas (planos básico/pro/enterprise via Laravel Cashier com Stripe/PagSeguro); receita recorrente por tenant (R$800-3.000/mês estimado).
*   **Expansibilidade**: Comece com MVP básico (núcleo genérico); inclua expansões para módulos específicos (ex.: saúde) como opcionais.
*   **Integrações**: CNAB 240/400 para bancos (remessa/retorno); API Receita Federal (validação CPF/CNPJ); Google Maps (rede credenciada); eSocial/SEFAZ (relatórios fiscais); TISS/ANS (para saúde); WhatsApp Business API (atendimento); Dialogflow/Grok API (chatbot e IA básica para churn/predições).
*   **Ferramentas de Desenvolvimento**: Use IAs para prototipagem (gerar wireframes, código boilerplate, testes); integre Filament v3 (painéis admin), Livewire + Inertia.js (Vue 3 para frontend reativo), Laravel Horizon (filas), Telescope (debug), Intervention Image (branding/uploads).
*   **Referências de Sistemas Semelhantes**: Inspire-se em Coopersystem (módulos financeiro/governança custom por ramo), COOPERASIS (contábil/fiscal multi-ramo), EasyCOOP (adesão online/portal cooperado), TOTVS Saúde (billing/reembolsos para Unimeds). Diferencie com multi-tenancy puro SaaS e automação IA.

## Estrutura de Pastas Recomendada (Padrão Laravel + Customizações):

*   `app/`
    *   `Console/Commands/` (ex.: `ConciliaBancariaCommand` para automação mensal)
    *   `Http/`
        *   `Controllers/` (TenantController, AssociadoController, FinanceiroController, etc.)
        *   `Middleware/` (IdentifyTenant.php, EnsureTenantActive.php, LgpdConsent.php)
        *   `Requests/` (AdesaoRequest, ReembolsoRequest com validações)
    *   `Models/` (Tenant, Associado, Dependente, CotaCapital, LancamentoFinanceiro, Prestador, PlanoSaude, Reembolso, Coparticipacao, etc.)
    *   `Services/` (TenantService, AdesaoService, FinanceiroService, RelatorioService)
    *   `Providers/` (TenancyServiceProvider.php, FilamentServiceProvider.php)
*   `config/` (tenancy.php, lgpd.php, filament.php)
*   `database/`
    *   `migrations/` (create_tenants_table.php, add_tenant_id_to_tables.php, create_associados_table.php)
    *   `seeders/` (TenantSeeder.php, RamoSeeder.php com ramos como saúde/agrícola)
    *   `factories/` (AssociadoFactory.php para testes)
*   `resources/`
    *   `js/` (Vue components para dashboards interativos)
    *   `views/` (Livewire components, ex.: adesao-form.blade.php)
*   `routes/`
    *   `web.php` (rotas super admin)
    *   `tenant.php` (rotas tenant-aware, ex.: /associados, /financeiro)
*   `tests/` (Feature/ (MultiTenancyTest.php, AdesaoTest.php); Unit/)
*   `public/` (assets para branding por tenant)
*   `storage/` (app/public para uploads de docs/imagens, com isolamento por tenant)

## Modelos de Dados (ER Simplificado):

*   **ASSOCIADO** (`id`, `tenant_id`, `nome`, `cpf`, `nascimento`, `endereco`, `status`, `data_adesao`)
*   **DEPENDENTE** (`id`, `associado_id`, `nome`, `parentesco`, `status`)
*   **COTA_CAPITAL** (`id`, `associado_id`, `valor_quota`, `qtd_quotas`, `saldo`, `tipo_movimento`)
*   **LANCAMENTO_FINANCEIRO** (`id`, `tenant_id`, `tipo`, `valor`, `data_vencimento`, `status`, `associado_id`)
*   **CONTA_BANCARIA** (`id`, `tenant_id`, `banco`, `agencia`, `conta`, `saldo`)
*   **PRESTADOR** (`id`, `tenant_id`, `tipo`, `nome`, `especialidades[]`, `endereco`) – Para saúde
*   **PLANO_SAUDE** (`id`, `tenant_id`, `nome`, `coberturas[]`, `faixas_etarias[]`)
*   **REEMBOLSO** (`id`, `associado_id`, `valor_solicitado`, `status`, `comprovantes[]`)
*   **COPARTICIPACAO** (`id`, `reembolso_id`, `valor_total`, `percentual`, `status`)
*   **Outros**: `TENANT` (`id`, `nome`, `ramo`, `branding_logo`), `CONVENIO` (`id`, `empresa`, `cnpj`, `dia_corte`)

## Fluxos de Processos Principais:

*   **Adesão**: Solicitação → Análise Documental → Aprovação → Definição Cota → Cobrança Inicial → Ativação.
*   **Cobrança Mensalidade**: Geração Lote → Tipo (Boleto/Desconto Folha) → Envio Remessa → Retorno Pagamento → Atualização Status → Régua Cobrança se Inadimplente.
*   **Reembolso (Saúde)**: Solicitação + Upload → Análise → Cálculo Valor → Aprovação → Pagamento.
*   **Conciliação Financeira**: Import Extrato → Matching Automático → Relatório Divergências → Fechamento.

## Módulos e Funcionalidades Completas:

1.  **Super Admin**: Criação tenants, gerenciamento assinaturas (Cashier), KPIs globais (total cooperados, receita, churn).
2.  **Configuração Tenant**: Seleção ramo (ativa módulos), branding, taxas, regras governança.
3.  **Gestão Associados**: Adesão digital (formulário, upload docs, assinatura eletrônica, workflow), dependentes, histórico, auditoria.
4.  **Cotas Capital**: Integralizações, devoluções, saldo, correção monetária, relatórios.
5.  **Financeiro**: Contas receber (boletos/Pix, descontos folha, inadimplência), pagar (fornecedores, notas fiscais), conciliação automática (import extratos CNAB/OFX), fluxo caixa.
6.  **Convênios/Parcerias**: Cadastro empresas, contratos, vigência.
7.  **Gestão Documentos**: Upload, versionamento, templates, assinatura digital.
8.  **Relatórios/BI**: Dashboards KPIs, export (PDF/Excel/CSV), gráficos interativos; IA para predições (churn, inadimplência).
9.  **Governança**: Relatórios auditáveis, votações online, fórum discussões.
10. **Automação**: Chatbot (Dialogflow), notificações (email/SMS/push), filas (Horizon) para cobranças.
11. **Módulos Específicos Saúde**: Rede Credenciada (prestadores, procedimentos, busca Maps), Planos (faixas, carências), Reembolsos (solicitação, análise, glosas), Guias/Autorizações (emissão, workflow), Coparticipação.
12. **Expansões por Ramo**: Crédito (empréstimos, poupança), Agrícola (produção, insumos), Trabalho (escalas, projetos), Consumo (comercialização).

## Instruções para Desenvolvimento:

*   **Inicie com MVP Básico**: Núcleo (associados, cotas, financeiro) + multi-tenancy.
*   **Use IAs para prototipagem**: Gere wireframes (Lovable.dev), fluxos (Google AI Studio), código boilerplate (Antigravity/Claude), integrações (Stitch AI).
*   **Implemente passo a passo**: Setup Laravel + tenancy; models/migrations; controllers/routes; Filament resources; testes unitários/feature.
*   **Entregáveis**: Código completo (Git repo), diagramas ER/Mermaid, plano implantação (Forge/Vapor), documentação (README, API docs).
*   **Custos Estimados (Referência)**: MVP Básico R$5.550 (entrada 30% R$1.665); Completo R$8.000 (entrada adicional 30% do acréscimo); Manutenção 10% mensal.
*   **Propriedade Intelectual**: Licença perpétua no básico; cessão total em aditivo para pacote completo (Lei 9.609/1998).

Gere o sistema completo: estrutura pastas, código chave (models, migrations, controllers, middleware), fluxos, integrações, testes e sugestões expansão.
