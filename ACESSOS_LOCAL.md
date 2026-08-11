# 🔑 Acessos Locais da Cooperativa (stfsaude)

Este arquivo descreve todos os acessos locais, usuários, senhas e rotas recomendadas para testar a cooperativa de teste **stfsaude** no ambiente de desenvolvimento local.

---

## 🌐 Domínio e Portais de Acesso

Para acessar localmente, o servidor deve ser executado (ex: `php artisan serve`). Utilize os seguintes endereços no seu navegador:

*   **Painel da Cooperativa (stfsaude):** `http://stfsaude.localhost:8000`
*   **Painel Administrativo Master (Central):** `http://localhost:8000` (ou `http://127.0.0.1:8000`)

---

## 👥 Contas de Acesso (stfsaude)

Todos os usuários abaixo compartilham a **senha padrão de desenvolvimento**:

🔑 **Senha padrão:** `senha123`

### 1. Perfis Administrativos e de Gestão
Contas utilizadas para gerenciar associados, faturamento, planos de saúde, reembolsos e relatórios dentro da cooperativa **stfsaude**:

| Nome | E-mail / Login | Função / Nível de Acesso |
| :--- | :--- | :--- |
| **Administrador local** | `admin@stfsaude.com.br` | Administrador completo da Cooperativa |
| **Presidente** | `presidente@stfsaude.com.br` | Nível máximo de visualização e decisões |
| **Diretor Financeiro** | `diretor@stfsaude.com.br` | Gestão de contas, cotas e conciliação bancária |
| **Gerente de Unidade** | `gerente@stfsaude.com.br` | Gestão operacional de novos cadastros |
| **Conselho Fiscal** | `fiscal@stfsaude.com.br` | Auditoria fiscal e relatórios financeiros |
| **Consultor Externo** | `consultor@stfsaude.com.br` | Acesso de monitoramento e auditoria externa |

---

### 2. Perfis de Cooperados / Associados
Contas de demonstração para testar as funcionalidades exclusivas da área do Cooperado (visualização de reembolsos, dependentes, cota capital):

| Nome | E-mail / Login | Tipo |
| :--- | :--- | :--- |
| **João Silva Associado** | `joao.associado@gmail.com` | Associado Titular (Ativo) |

---

### 3. Perfis de Prestadores de Serviços (Parceiros/Rede)
Contas utilizadas para simular o portal do Prestador de Serviços credenciado (onde ele inicia pedidos de autorização de guias, cadastra catálogo de serviços, envia faturas, etc.). 

Para testar, faça o login e acesse a rota: `http://stfsaude.localhost:8000/prestador`

| Nome / Empresa | E-mail / Login | Tipo | Especialidade Principal |
| :--- | :--- | :--- | :--- |
| **Clínica São Lucas** | `sao.lucas@gmail.com` | Clínica | Pediatria, Clínica Geral |
| **Laboratório Central** | `lab.central@gmail.com` | Laboratório | Análises Clínicas, Exames de Imagem |
| **Dr. Marcelo Silva** | `marcelo.silva@gmail.com` | Profissional | Cardiologia |

---

## 🏥 Contas de Acesso (santa-casa - FUNDAÇÃO)

A **Santa Casa da Amazônia** é uma entidade do tipo **Fundação**. O fluxo de adesão e de faturamento é similar, porém com nomenclatura dedicada a Clientes/Associados e regras específicas.

*   **Portal da Fundação:** `http://santacasa.localhost:8000`

Todos os usuários abaixo compartilham a **senha padrão de desenvolvimento**:

🔑 **Senha padrão:** `senha123`

| Nome | E-mail / Login | Função / Nível de Acesso |
| :--- | :--- | :--- |
| **Administrador Fundação** | `admin@santacasa.com.br` | Administrador da Fundação |
| **Pedro Cliente** | `cliente@santacasa.com.br` | Cliente / Beneficiário da Fundação |

---

## 💡 Dicas de Teste Local
*   Se for testar a área de **Adesão**, acesse `http://stfsaude.localhost:8000/adesao`.
*   Para testar a gestão central das cooperativas (painel Master), acesse `http://localhost:8000/master-admin/login` usando as credenciais do super-administrador configuradas no seu arquivo `.env`.
