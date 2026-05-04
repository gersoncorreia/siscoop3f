# Prompt de Configuração: Fluxo de Adesão e Gestão de Cota Capital

Você deve atuar como um **Arquiteto de Sistemas Sênior** especializado em soluções para o setor cooperativista. O objetivo é configurar e implementar o módulo de adesão de cooperados, integrando de forma nativa a gestão de **Cota Capital** e **Taxas Administrativas**. A implementação deve priorizar a viabilidade técnica e a clareza didática para o usuário final, garantindo que o novo cooperado compreenda sua posição como sócio-proprietário da entidade.

## Estrutura de Custos e Capitalização

A tabela abaixo detalha os parâmetros financeiros que devem ser incorporados à lógica do sistema para o processo de adesão:

| Componente | Valor Referência | Periodicidade | Natureza Contábil |
| :--- | :--- | :--- | :--- |
| **Cota Capital (Total)** | R$ 600,00 | Única (Integralizada) | Patrimônio Líquido (Ativo do Sócio) |
| **Integralização Mensal** | R$ 10,00 | Mensal | Aporte Patrimonial |
| **Taxa Adm. Titular** | R$ 100,00 | Adesão / Mensal | Receita Operacional (Custo do Sócio) |
| **Taxa Adm. Dependente** | A definir | Adesão / Mensal | Receita Operacional (Custo do Sócio) |

## Diretrizes de Implementação do Fluxo

O fluxo de adesão deve ser dividido em etapas lógicas que facilitem a conversão do interessado em cooperado ativo. No momento da coleta de dados, o sistema deve permitir o cadastro do titular e de seus respectivos dependentes, calculando automaticamente o somatório das taxas administrativas devidas.

Para a **Cota Capital**, deve-se aplicar a estratégia de "Capitalização Suave". Em vez de exigir o aporte integral de R$ 600,00 no ato da adesão, o sistema deve configurar uma recorrência mínima (sugerida em R$ 10,00) que será descontada ou cobrada mensalmente. Esta abordagem visa reduzir a barreira de entrada e evitar o endividamento imediato do novo membro.

## Requisitos de Lógica e Dados

O modelo de dados deve suportar a segregação clara entre o que é despesa de manutenção e o que é investimento patrimonial. Recomenda-se a criação das seguintes entidades e funcionalidades:

1.  **Gestão de Contas Virtuais:** O cooperado deve possuir uma "Conta Capital" vinculada ao seu perfil, onde o saldo acumulado é rastreável.
2.  **Automação de Integralização:** O sistema deve gerar lançamentos automáticos de aporte até que a meta de R$ 600,00 seja atingida.
3.  **Injeção de Resultados:** Deve haver uma função para permitir que as "Sobras" (lucros da cooperativa) sejam destinadas total ou parcialmente para a Cota Capital do cooperado, acelerando o processo de integralização.

## Interface e Experiência do Usuário (UX)

A interface deve ser educativa. No painel do cooperado, o saldo da Cota Capital deve ser exibido como **"Meu Patrimônio na Cooperativa"**, reforçando o conceito de que aquele valor pertence a ele e está sendo investido no negócio comum. O extrato financeiro deve utilizar cores ou etiquetas distintas para diferenciar "Taxas de Serviço" de "Aportes de Capital", garantindo total transparência sobre o destino de cada real pago.

---

**Instrução Final:** Utilize as diretrizes acima para gerar o código de backend (Python/Node.js) ou o fluxo de automação necessário para processar a adesão, integrando os cálculos de taxas e a criação da conta capital de forma síncrona.
