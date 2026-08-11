# 🔑 Credenciais de Teste Organizadas - Siscoop3F

Este documento reúne todas as credenciais de teste ativas no banco de dados para acesso aos diferentes portais, entidades (Fundação e Cooperativa) e níveis de permissão do sistema **Siscoop3F**.

---

## 🌐 1. Painel Central Master Admin (Landlord)
- **URL de Acesso**: `http://localhost:8000/master-admin`
- **Perfil**: Super Admin Geral do Sistema (Gestão de Entidades / Tenants)

| Nome | E-mail | Senha | Função |
| :--- | :--- | :--- | :--- |
| Gerson Super Admin | `master@siscoop3f.com.br` | `master123` | Super Admin |

---

## 🏥 2. Entidade Fundação: Santa Casa da Amazônia
- **Domínio de Acesso**: `http://santacasa.localhost:8000`
- **Tipo de Entidade**: Fundação Hospitalar / Clube de Saúde

### 👑 Painel Administrativo da Fundação
| Nome | E-mail | Senha | Função |
| :--- | :--- | :--- | :--- |
| Admin local da Fundação | `admin@santacasa.com.br` | `senha123` | Administrador Fundação |

### 👤 Portal do Cliente (`/cooperado`)
- **URL**: `http://santacasa.localhost:8000/cooperado`

| Nome | E-mail | Senha | Perfil |
| :--- | :--- | :--- | :--- |
| Pedro Cliente | `cliente@santacasa.com.br` | `cliente123` | Cliente / Paciente (Matrícula #0001) |

### 🏥 Portal do Prestador / Parceiro Credenciado (`/prestador`)
- **URL**: `http://santacasa.localhost:8000/prestador`

| Nome da Clínica / Laboratório | E-mail | Senha | Especialidades / Serviços |
| :--- | :--- | :--- | :--- |
| **Clínica Médica & Hospital São Lucas** | `clinica.lucas@santacasa.com.br` | `prestador123` | Clínica Geral, Cardiologia, Exames de Imagem *(5 serviços no catálogo)* |
| **Laboratório Diagnósticos da Amazônia** | `laboratorio.amazonia@santacasa.com.br` | `prestador123` | Análises Clínicas, Patologia, Genética *(5 exames no catálogo)* |
| **Centro Odontológico Sorriso Real** | `sorrisoreal@santacasa.com.br` | `prestador123` | Odontologia Geral, Ortodontia, Endodontia *(4 procedimentos no catálogo)* |

---

## 🏛️ 3. Entidade Cooperativa: STF Saúde Co-op
- **Domínio de Acesso**: `http://stfsaude.localhost:8000`
- **Tipo de Entidade**: Cooperativa de Saúde

### 👑 Corpo Diretivo & Painel Administrativo
| Nome | E-mail | Senha | Cargo / Perfil |
| :--- | :--- | :--- | :--- |
| Presidente da Cooperativa | `presidente@stfsaude.com.br` | `senha123` | Presidente |
| Diretor Financeiro | `diretor@stfsaude.com.br` | `senha123` | Diretor Administrativo Financeiro |
| Gerente de Unidade | `gerente@stfsaude.com.br` | `senha123` | Gerente |
| Presidente do Conselho Fiscal | `fiscal@stfsaude.com.br` | `senha123` | Conselho Fiscal |
| Consultor Externo | `consultor@stfsaude.com.br` | `senha123` | Consultor |
| Admin local da Cooperativa | `admin@stfsaude.com.br` | `senha123` | Administrador Cooperativa |

### 🤝 Portal do Cooperado / Associado (`/cooperado`)
- **URL**: `http://stfsaude.localhost:8000/cooperado`

| Nome | E-mail | Senha | Perfil / Matrícula |
| :--- | :--- | :--- | :--- |
| João Silva Associado | `joao.associado@gmail.com` | `senha123` | Associado (Cotas & Financeiro ativos) |

### 🩺 Portal do Prestador Credenciado (`/prestador`)
- **URL**: `http://stfsaude.localhost:8000/prestador`

| Nome do Prestador | E-mail | Senha | Tipo |
| :--- | :--- | :--- | :--- |
| Clínica São Lucas | `sao.lucas@gmail.com` | `senha123` | Clínica |
| Laboratório Central | `lab.central@gmail.com` | `senha123` | Laboratório / Hospital |
| Dr. Marcelo Silva | `marcelo.silva@gmail.com` | `senha123` | Médico PF (Cardiologia) |

---

## 🌾 4. Entidade Cooperativa Agrícola: Agro Valley Co-op
- **Domínio de Acesso**: `http://agrocoop.localhost:8000`
- **Tipo de Entidade**: Cooperativa Agrícola

| Nome | E-mail | Senha | Perfil |
| :--- | :--- | :--- | :--- |
| Admin local da Cooperativa | `admin@agrocoop.com.br` | `senha123` | Administrador Cooperativa |

---
**Siscoop3F v1.0** — Guia Oficial de Credenciais de Teste.
