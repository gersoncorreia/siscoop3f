# Prompt para Implementação de Fluxo Financeiro (Cota Capital e Taxas)

Você é um Arquiteto de Sistemas Sênior especializado em Cooperativismo. Sua tarefa é implementar o módulo de **Adesão de Cooperados** com foco na gestão de **Cota Capital** e **Taxas Administrativas**, garantindo que o fluxo seja fluido, didático e tecnicamente viável.

## 1. Conceitos Fundamentais
*   **Cota Capital (Patrimônio):** É o investimento do cooperado que o torna dono. Valor total: **R$ 600,00**.
*   **Taxa Administrativa (Receita):** Valor para manutenção da cooperativa. Titular: **R$ 100,00**. Dependentes: [Definir valor unitário].
*   **Estratégia de "Capitalização Suave":** Não cobrar os R$ 600,00 à vista. Diluir em parcelas mínimas (ex: R$ 10,00/mês) para não onerar o cooperado na entrada.

## 2. Requisitos de Dados (Data Schema)
Crie ou atualize as seguintes entidades:
*   **Cooperado:** ID, Nome, Status, Data de Adesão.
*   **Dependente:** ID, Cooperado_ID, Nome, Parentesco.
*   **Conta_Capital:** Cooperado_ID, Saldo_Atual, Meta_Total (600.00), Valor_Mensal_Integralizacao (padrão 10.00).
*   **Lancamentos_Financeiros:** ID, Cooperado_ID, Tipo (Taxa/Cota), Valor, Data, Status (Pago/Pendente).

## 3. Fluxo de Adesão (Step-by-Step)
1.  **Coleta de Dados:** Cadastro do titular e seus dependentes.
2.  **Cálculo de Taxas Iniciais:**
    *   Soma da Taxa Titular (100.00) + Taxas de Dependentes.
    *   Apresentar como "Taxa de Manutenção e Serviços".
3.  **Configuração da Cota Capital:**
    *   Explicar de forma didática: "Como sócio, você constrói seu patrimônio. Sugerimos uma reserva mensal de R$ 10,00 para integralizar sua parte na cooperativa."
    *   Permitir que o cooperado veja o progresso (0/600).
4.  **Fechamento do Primeiro Pagamento:**
    *   Total = Taxas Administrativas + Primeira Parcela da Cota.

## 4. Lógica de Negócio (Backend)
*   **Integralização Automática:** Todo mês, o sistema deve gerar um lançamento de R$ 10,00 (ou valor definido) para a Conta Capital até atingir R$ 600,00.
*   **Destinação de Excedentes:** Implementar função para que "Sobras" (resultados positivos da cooperativa) possam ser injetadas automaticamente na Cota Capital do cooperado, acelerando a quitação dos R$ 600,00.
*   **Separação Contábil:** Garantir que o dinheiro da Taxa (Gasto) e da Cota (Investimento/Patrimônio) fiquem em contas virtuais distintas no extrato do cooperado.

## 5. UI/UX - Interface Didática
*   **Dashboard do Cooperado:** Exibir um card de "Meu Patrimônio" mostrando o saldo da Cota Capital.
*   **Extrato Transparente:** Diferenciar claramente o que é "Pagamento de Serviço" (Taxas) e o que é "Aporte de Sócio" (Cota).

---
**Instrução de Implementação:** Proponha o código (ou lógica de workflow) para processar a adesão seguindo estas premissas, priorizando a clareza para o usuário final.
