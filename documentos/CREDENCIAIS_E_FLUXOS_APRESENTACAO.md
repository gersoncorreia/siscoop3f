# 🎯 Guia de Credenciais, Perfis e Fluxos da Apresentação (Quinta-Feira)

Este documento reúne **todos os usuários, senhas, CPFs de teste e Links Mágicos** gerados pela nova Seed Executiva (`DatabaseSeeder` + `TenantDatabaseSeeder`) para a demonstração ao vivo do **Siscoop3F**.

> **Senha Padrão para Todos os Perfis do Tenant:** `senha123`
> **Senha do Super Admin Central:** `master123`

---

## 1. Domínio Central (Master Admin)
- **URL Local:** `http://localhost:8000/master-admin/login` (ou `https://siscoop3f.com.br/master-admin/login`)
- **Login:** `master@siscoop3f.com.br`
- **Senha:** `master123`
- **O que mostrar:**
  - Visão Global das 3 Entidades provisionadas (`STF Saúde Co-op`, `Agro Valley Co-op`, `Santa Casa da Amazônia`).
  - Gestão de Contratos entre Entidades (`/master-admin/contratos`): já possui 1 contrato **Ativo** (`STF Saúde` <-> `Santa Casa`) e 1 contrato **Pendente** (`Agro Valley` <-> `STF Saúde`).

---

## 2. Entidade Cooperativa Principal (`stfsaude`)
- **URL Local:** `http://stfsaude.localhost:8000/login` (ou `https://stfsaude.siscoop3f.com.br/login`)

### A. Perfis de Governança e Administração (`stfsaude`)
| Papel / Cargo | E-mail de Acesso | Senha | Foco na Demonstração |
| :--- | :--- | :---: | :--- |
| **Administrador Geral** | `admin@stfsaude.com.br` | `senha123` | Visão total, aprovação de adesões, configurações, LGPD e OnePage |
| **Presidente** | `presidente@stfsaude.com.br` | `senha123` | Dashboard executivo, evolução patrimonial, relatórios gerenciais |
| **Gerente Operacional (Hélio)** | `gerente@stfsaude.com.br` | `senha123` | Aprovação de guias médicas (`/saude/autorizacoes`), reembolsos e faturas do dia 05 |
| **Diretor Adm. Financeiro** | `diretor@stfsaude.com.br` | `senha123` | Fluxo de caixa, contas bancárias (Sicoob/Sicredi), conciliação e Cota Capital |
| **Conselho Fiscal** | `fiscal@stfsaude.com.br` | `senha123` | Fiscalização financeira, contábil e exportação de relatórios |
| **Consultor Estratégico** | `consultor@stfsaude.com.br` | `senha123` | Consulta de indicadores, associados e saúde |
| **Contabilidade** | `contabil@stfsaude.com.br` | `senha123` | Exportação contábil e conferência de lançamentos |
| **Auditoria** | `auditoria@stfsaude.com.br` | `senha123` | Acesso exclusivo a relatórios de auditoria |
| **Atendente / Recepção** | `atendente@stfsaude.com.br` | `senha123` | Triagem de agendamentos e consulta de associados |
| **Conselho Diretor** | `conselho@stfsaude.com.br` | `senha123` | Indicadores estratégicos e relatórios |

---

### B. Perfis de Cooperados e Cenários de Fluxo Vivo (`stfsaude`)
| Cooperado | E-mail de Login | CPF (p/ Busca no Prestador) | Matrícula | Status / Cenário Preparado na Seed |
| :--- | :--- | :---: | :---: | :--- |
| **João Silva Associado** | `joao.associado@gmail.com` | `12345678909` | `20260101` | **Cooperado Exemplar (Adimplente)**: R$ 3.000,00 em Cota Capital, Carteirinha Familiar Ativa (`2026-00101`), 2 Dependentes com Link Mágico, 1 Reembolso aprovado (com coparticipação de 30%) + 1 Reembolso pendente, 1 Guia Cardiológica aguardando autorização, Prontuário e Agendamento confirmado. |
| **Maria Clara Souza** | `maria.cooperada@gmail.com` | `39053344705` | `20260102` | **Cooperada Ativa**: R$ 1.200,00 em Cota Capital, Carteirinha Individual Ativa (`2026-00102`), 1 Solicitação de Internação Eletiva em análise, 1 Guia Laboratorial pendente e 1 Reembolso de Fisioterapia pendente. |
| **Carlos Eduardo Mendes** | `carlos.inadimplente@gmail.com` | `85296374104` | `20260103` | **Cooperado INADIMPLENTE (Bloqueio Automático)**: Possui Cota Capital e mensalidade em aberto. Ao digitar o CPF `85296374104` no Portal do Prestador, o sistema **bloqueia a solicitação na hora**! |
| **Dra. Fernanda Lima Cavalcante** | *(Na fila de Adesão)* | `65498732109` | `20260104` | **Proposta Pendente (`/associados/adesoes`)**: Pronta para você clicar em **"Aprovar"** ao vivo durante a apresentação! |
| **Marcos Vinícius Peixoto** | *(Na fila de Adesão)* | `15975348602` | `20260105` | **Proposta em Análise Documental (`/associados/adesoes`)**: Pronta para triagem ao vivo! |

---

### C. Dependentes e Acesso via Link Mágico (`stfsaude`)
| Dependente (Titular: João Silva) | Parentesco | URL do Link Mágico | Desafio (5 primeiros dígitos do CPF) | Login Direto (Opcional) |
| :--- | :---: | :--- | :---: | :--- |
| **Ana Beatriz Oliveira Silva** | Cônjuge | `/acesso-dependente/token-ana-beatriz-stfsaude-2026` | **`52998`** (CPF: `52998224725`) | `ana.dependente@gmail.com` / `senha123` |
| **Lucas Oliveira Silva** | Filho | `/acesso-dependente/token-lucas-filho-stfsaude-2026` | **`11144`** (CPF: `11144477735`) | *(Apenas Link Mágico)* |

---

### D. Portal do Prestador Credenciado (`stfsaude`)
| Prestador / Clínica | E-mail de Login | Senha | O Que Já Está Cadastrado para Mostrar |
| :--- | :--- | :---: | :--- |
| **Clínica São Lucas** | `sao.lucas@gmail.com` | `senha123` | 3 serviços no catálogo, 1 guia pendente (João Silva), 1 guia negada (histórico), 1 Fatura Paga (R$ 3.420) e 1 Fatura Enviada no mês atual (R$ 1.890). |
| **Laboratório Central Diagnósticos** | `lab.central@gmail.com` | `senha123` | 2 serviços laboratoriais/imagem, 1 guia pendente (Maria Clara) e 1 Fatura Em Auditoria (R$ 2.180). |
| **Dr. Marcelo Silva (Cardiologia)** | `marcelo.silva@gmail.com` | `senha123` | 2 exames cardiológicos no catálogo e 1 guia já aprovada pela cooperativa (João Silva). |

---

## 3. Entidade Fundação (`santa-casa` — Santa Casa da Amazônia)
- **URL Local:** `http://santacasa.localhost:8000/login` (ou `https://santacasa.siscoop3f.com.br/login`)

### A. Perfis Administrativos (`santa-casa`)
- **Admin Geral:** `admin@santacasa.com.br` / `senha123`
- **Gerente Assistencial:** `gerente@santacasa.com.br` / `senha123`
- **Atendente / Recepção:** `atendente@santacasa.com.br` / `senha123`
- **Conselho Curador:** `conselho@santacasa.com.br` / `senha123`

### B. Clientes, Dependentes e Prestadores (`santa-casa`)
- **Cliente Titular (Plano Família):** `cliente@santacasa.com.br` / `senha123` — CPF: `98765432100` (Carteirinha `2026-00001`).
- **Dependente com Link Mágico:** Juliana Mendes da Silva — URL: `/acesso-dependente/magiclink-santacasa-juliana-2026` — 5 primeiros dígitos do CPF: **`45678`**.
- **Cliente 2 (Plano PF):** `lucia.ferreira@gmail.com` / `senha123` — CPF: `32165498701`.
- **Proposta na Fila para Aprovar:** Ricardo Tavares Nascimento (`/associados/adesoes`).
- **Prestadores da Fundação (Senha `prestador123`):**
  - `clinica.lucas@santacasa.com.br`
  - `laboratorio.amazonia@santacasa.com.br`
  - `sorrisoreal@santacasa.com.br`

---

## 🎬 Roteiro Rápido de Cliques (Fluxo Conectado para Quinta-Feira)
1. **Comece pelo Admin / Presidente (`admin@stfsaude.com.br`)**:
   - Mostre o **Dashboard Executivo** com o gráfico de Receitas x Despesas dos últimos 6 meses, Saldo Bancário (R$ 397 mil) e Capital Social.
   - Entre em **Associados > Adesões Pendentes (`/associados/adesoes`)** e aprove ao vivo a proposta da **Dra. Fernanda Lima Cavalcante**.
   - Abra o **Perfil 360° do João Silva** e mostre as abas de Cota Capital, Financeiro, Dependentes (copie o Link Mágico da esposa Ana Beatriz) e Documentos (aprove a Certidão de Casamento pendente).
2. **Mostre o Fluxo do Prestador (`sao.lucas@gmail.com`)**:
   - Na busca de elegibilidade, digite **`12345678909`** (João Silva) $\rightarrow$ mostre que está **Ativo e Adimplente**.
   - Agora digite **`85296374104`** (Carlos Eduardo) e tente solicitar um exame $\rightarrow$ mostre o **Bloqueio Automático por Inadimplência**!
   - Mostre a aba **Faturas (Regra do Dia 05)** com faturas pagas e enviadas.
3. **Volte ao Gerente/Admin em Saúde (`/saude/autorizacoes` e `/saude/reembolsos`)**:
   - Aprove a guia cardiológica do João Silva solicitada pela Clínica São Lucas.
   - Aprove o reembolso de R$ 300,00 do João Silva (o sistema retém 30% de coparticipação automaticamente).
4. **Finalize no Portal do Cooperado (`joao.associado@gmail.com`) e Link Mágico**:
   - Mostre o painel **Meu Patrimônio (Cotas Capital)**, a **Carteirinha Digital com QR Code** e abra o **Link Mágico da dependente Ana Beatriz** (`/acesso-dependente/token-ana-beatriz-stfsaude-2026`) digitando os 5 dígitos **`52998`**.
