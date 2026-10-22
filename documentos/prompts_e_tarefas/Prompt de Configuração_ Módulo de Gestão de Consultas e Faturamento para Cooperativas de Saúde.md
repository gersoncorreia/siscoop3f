# Prompt de Configuração: Módulo de Gestão de Consultas e Faturamento para Cooperativas de Saúde (Fluxo Invertido)

Este documento estabelece as diretrizes para a implementação de um módulo de **Gestão de Consultas e Faturamento da Rede Credenciada** dentro do sistema da cooperativa, com uma **inversão na ordem de solicitação do serviço**. O objetivo é criar um fluxo onde o **prestador de serviço inicia a solicitação**, garantindo um processo seguro e eficiente, e um ciclo de faturamento e pagamento aos prestadores que seja transparente e rigorosamente pontual. O desenvolvimento deve ser conduzido por um **Analista de Sistemas de Saúde e Cooperativismo**, com foco na viabilidade técnica e na clareza para todos os usuários envolvidos.

## 1. Fluxo de Autorização de Consultas Médicas

O processo de autorização de consultas é um ponto crítico para o controle financeiro e a garantia de acesso aos serviços pelos cooperados. O sistema deve orquestrar as seguintes etapas, com a **iniciação pelo prestador de serviço**:

1.  **Iniciação da Solicitação pelo Prestador de Serviço:** O cooperado comparece ao consultório (ex: odontológico ou cardiológico) de um profissional da **Rede Credenciada**. O atendente do prestador de serviço acessa o painel administrativo e inicia a solicitação de atendimento para o cooperado. Esta ação deve ser intuitiva e acessível através da interface do prestador.
2.  **Verificação de Elegibilidade Automática:** O sistema realizará uma validação automática da elegibilidade do cooperado. Isso inclui verificar se o cooperado está ativo na cooperativa e se suas condições financeiras (como mensalidades e cota capital em dia) permitem a utilização do serviço. Caso haja pendências críticas, o sistema deve bloquear a solicitação e informar o prestador sobre a situação, que por sua vez, comunicará o cooperado.
3.  **Aprovação Gerencial (Hélio):** Após a verificação de elegibilidade, a solicitação é encaminhada para a aprovação do gerente responsável (referido como "Hélio" no áudio). Uma interface específica deve ser desenvolvida para que o gerente possa visualizar as solicitações pendentes e realizar a aprovação ou reprovação com um clique, registrando a decisão no sistema.
4.  **Notificação e Atendimento:** Uma vez aprovada, o sistema deve gerar notificações automáticas para o cooperado (via dashboard) e para o prestador de serviço, confirmando a autorização para o atendimento. O cooperado poderá acompanhar o status da sua solicitação no seu dashboard, mas **não fará a solicitação por lá**.

## 2. Ciclo de Faturamento e Pagamento aos Prestadores

O ciclo de faturamento e pagamento aos prestadores credenciados deve seguir um modelo de "Ciclo de 30 dias" com a regra do "Dia 5", conforme detalhado na tabela abaixo. Este modelo garante previsibilidade e pontualidade nos pagamentos, fortalecendo o relacionamento com a rede credenciada.

| Etapa do Ciclo | Prazo Limite | Responsável | Ação Detalhada |
| :--- | :--- | :--- | :--- |
| **Envio de Despesas** | Até o dia 05 do mês atual | Escritório/Prestador | O escritório ou o próprio prestador deve enviar as faturas e guias das consultas e procedimentos realizados no mês anterior. O sistema deve prover um módulo de upload e validação inicial. |
| **Processamento e Auditoria** | Entre o dia 06 e o último dia útil do mês atual | Sistema/Gerência | O sistema deve realizar a conferência automática das guias enviadas versus as autorizações concedidas. A gerência deve ter acesso a ferramentas de auditoria para resolver inconsistências. |
| **Pagamento (Liquidação)** | Dia 05 do mês seguinte | Gerente (Hélio) | O sistema deve gerar automaticamente a lista de pagamentos devidos. O gerente (Hélio) autoriza a liquidação financeira, e o sistema processa o pagamento efetivo aos prestadores credenciados. |

## 3. Requisitos Técnicos e Regras de Negócio

Para suportar o fluxo descrito, o sistema deve incluir:

*   **Gestão Abrangente da Rede Credenciada:** Um módulo para cadastro e manutenção de informações detalhadas dos médicos e clínicas, incluindo especialidades, dados de contato, valores acordados para consultas e procedimentos, e status de credenciamento.
*   **Controle Financeiro Integrado:** O sistema deve estar interligado com o módulo financeiro da cooperativa para realizar as verificações de elegibilidade e para processar os pagamentos. Bloqueios automáticos de novas solicitações devem ocorrer caso o cooperado apresente pendências financeiras críticas, com mensagens claras ao usuário.
*   **Workflow de Aprovação Configurável:** A interface de aprovação para o gerente (Hélio) deve ser intuitiva, permitindo a visualização rápida de todas as solicitações pendentes, com filtros por tipo de serviço, cooperado ou prestador. A ação de aprovar/reprovar deve ser simples e registrar um log de auditoria.
*   **Relatórios de Faturamento Automatizados:** O sistema deve gerar relatórios detalhados e automatizados no dia 05 de cada mês, consolidando o montante devido a cada prestador, facilitando a conferência e o processo de pagamento.

## 4. Interface do Usuário (UX) e Experiência

A experiência do usuário deve ser otimizada para cada perfil:

*   **Para o Cooperado:** Uma área dedicada onde o cooperado pode visualizar a lista de médicos credenciados e **acompanhar o status de suas solicitações** em tempo real (Pendente, Autorizado, Realizado, Cancelado). **É importante ressaltar que o cooperado não iniciará solicitações por este dashboard.**
*   **Para o Gerente (Hélio):** Um dashboard centralizado que exiba as aprovações pendentes, um resumo dos pagamentos programados para o próximo dia 05 e alertas sobre possíveis inconsistências no faturamento.
*   **Para o Escritório/Prestador:** Um módulo para **iniciar solicitações de atendimento**, upload de documentos (faturas, guias), acompanhamento do status de processamento de suas despesas e acesso a relatórios de pagamentos recebidos.

---

**Instrução Final para o Antigravity:** Com base nas especificações acima, desenvolva a lógica de banco de dados (esquema de tabelas e relacionamentos) e o workflow de estados (state machine) que gerencie o pedido de consulta desde a iniciação pelo prestador até o pagamento final, garantindo a aderência à janela de faturamento e às regras de negócio descritas. O código ou a lógica de automação deve ser modular, escalável e de fácil manutenção.
