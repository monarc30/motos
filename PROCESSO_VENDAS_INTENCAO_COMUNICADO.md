# Processo: Vendas, Intenção de Venda e Comunicado de Venda

Documento que descreve o fluxo desejado pelo cliente e o papel do sistema e do Autoconf.

---

## 1. Quem é quem

- **Revenda (loja):** quem vende as motos para o cliente final. Ex.: Prado Motos.
- **Cliente final:** quem compra a moto da revenda (comprador da loja).
- **Intenção de venda:** documento em que o **cliente final** declara a intenção de venda (dados do CRV, etc.). É de quem **comprou da revenda**.
- **Autoconf:** sistema externo da revenda (estoque, vendas, contratos). Quando a revenda “bate o contrato” com o comprador, a venda é registrada no Autoconf e a moto **sai do estoque** lá.

---

## 2. Fluxo desejado pelo cliente

1. **Venda na loja**  
   A revenda vende a moto e registra a venda no Autoconf (contrato com o comprador). No Autoconf a moto **sai do estoque**.

2. **No nosso sistema – Intenção de venda**  
   A revenda usa nosso sistema para gerar a **Intenção de Venda** (cliente final, veículo, CRV).  
   Quando essa intenção é feita, o item deve **sair** da lista “a fazer” e ir para **“Intenção de venda feita”**.

3. **No nosso sistema – Comunicado de venda**  
   Depois, a revenda gera o **Comunicado de Venda** (e etiqueta). O item passa a constar como **“Comunicado feito”**.

4. **Lista de clientes vendidos**  
   O cliente quer a **lista de clientes vendidos a partir do dia em que o sistema começar a funcionar**, para controle e para saber o que ainda está pendente de intenção/comunicado.

Resumo do fluxo no nosso sistema:

```
[Pendente intenção]  →  (usuário gera intenção)  →  [Intenção feita]
[Intenção feita]     →  (usuário gera comunicado) →  [Comunicado feito]
```

---

## 3. Precisamos do Autoconf para saber o que foi vendido?

**Não.** Não é obrigatório usar o Autoconf para saber quais vendas foram feitas.

- **Tudo pode ficar no nosso banco de dados:**
  - Cada vez que o usuário gera uma **Intenção de Venda** no sistema, já gravamos: cliente, veículo e a intenção na tabela `intencoes_venda`.
  - Cada vez que gera **Comunicado de Venda**, gravamos em `comunicados_venda`.
  - Com isso, temos no nosso banco:
    - Quem foi vendido (cliente + veículo).
    - O que já teve intenção de venda.
    - O que já teve comunicado de venda.
  - A **lista de clientes vendidos a partir do dia X** pode ser montada só com dados do nosso banco (intenções e/ou comunicados com `created_at >= data_inicio`).

- **Papel do Autoconf (opcional):**
  - **Preencher dados:** se o usuário informar o ID da venda/veículo no Autoconf, buscamos lá (estoque ou outro endpoint) e preenchemos cliente/veículo.
  - **Anexar documento:** após gerar o PDF da intenção, podemos anexar no processo do Autoconf (se a API permitir).
  - O Autoconf **não** é necessário para “saber o que foi vendido” no nosso sistema: quem define isso é o uso do nosso sistema (gerar intenção/comunicado), e isso já fica registrado nas nossas tabelas.

---

## 4. O que já temos no banco (nosso sistema)

| Tabela             | O que guarda |
|--------------------|----------------|
| `clientes`         | Nome, CPF, telefone, WhatsApp, endereço. |
| `veiculos`         | Placa, modelo, ano. |
| `intencoes_venda`  | Cliente + veículo + CRV + status (rascunho/gerado/finalizado) + PDF. Cada registro = uma “intenção de venda feita”. |
| `comunicados_venda`| Cliente + veículo + data do comunicado + PDF + etiqueta. Cada registro = um “comunicado feito”. |

Com isso já dá para:
- Listar **clientes/veículos que já tiveram intenção** (e a partir de qual data).
- Listar **clientes/veículos que já tiveram comunicado**.
- Montar a **lista de vendas (clientes vendidos) a partir do dia que o sistema entrou no ar** usando apenas nossas tabelas.

---

## 5. Possível evolução do sistema (sem depender do Autoconf para “o que foi vendido”)

1. **Lista “Pendentes de intenção”**  
   Mostrar vendas/cliente+veículo que ainda **não** têm registro em `intencoes_venda`.  
   - Pode vir só do nosso banco (ex.: cadastros manuais ou importação), ou  
   - Opcionalmente buscar do Autoconf (estoque ou vendas) e, ao escolher um item, criar/atualizar cliente e veículo no nosso banco e depois gerar a intenção.

2. **Lista “Intenção feita”**  
   Registros que já têm linha em `intencoes_venda` (status gerado/finalizado). Fonte: nosso banco.

3. **Lista “Pendentes de comunicado”**  
   Quem já tem intenção mas ainda **não** tem registro em `comunicados_venda`. Fonte: nosso banco.

4. **Lista “Comunicado feito”**  
   Registros em `comunicados_venda`. Fonte: nosso banco.

5. **Relatório “Clientes vendidos a partir de [data]”**  
   Consultar `intencoes_venda` e/ou `comunicados_venda` com `created_at >= data_inicio`, juntando cliente e veículo. Tudo no nosso banco.

Nada disso exige que o Autoconf seja a fonte da verdade para “o que foi vendido”; podemos armazenar e consultar tudo no nosso banco.

---

## 6. Resumo

| Pergunta | Resposta |
|----------|----------|
| Precisamos do Autoconf para saber quais foram vendidas? | **Não.** Podemos armazenar e listar tudo no nosso banco. |
| Onde fica “o que foi vendido” e “quem já teve intenção/comunicado”? | Nas tabelas `intencoes_venda` e `comunicados_venda` (e em `clientes` / `veiculos`). |
| Para que serve o Autoconf então? | Opcional: trazer dados por ID (preencher formulário) e anexar PDF no processo deles. |
| Lista de clientes vendidos a partir do dia X? | Sim, usando apenas o nosso banco (filtrar por data de criação nas intenções/comunicados). |

---

*Documento criado a partir do processo descrito pelo cliente (conversa 31/01/2026).*
