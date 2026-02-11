# Status do Projeto – Onde paramos e o que falta

Arquivo para retomar o trabalho após reiniciar o note. Última atualização: 31/01/2026.

---

## 1. O que já está pronto

### Funcionalidades
- **Intenção de Venda:** formulário com ID da venda no Autoconf; busca dados (cliente/veículo) no Autoconf; gera PDF; opção “Listar vendas do Autoconf” quando não sabe o ID; download do PDF.
- **Comunicado de Venda:** mesmo fluxo (buscar por ID ou listar vendas); gera comunicado e etiqueta; integração COMven (registro da venda); download.
- **Autoconf:** busca por ID (`/api/v1/veiculo` ou relatório CSV); listagem de veículos (`POST /api/v1/veiculos` ou relatório estoque); anexar PDF da intenção; busca cliente (relatórios financeiro/atendimentos).
- **Banco nosso:** `clientes`, `veiculos`, `intencoes_venda`, `comunicados_venda`, `logs_integracao`. Tudo que é gerado no sistema fica gravado aqui.
- **URL em produção:** sistema configurado para `https://segen1.com.br/motos` (base `/motos`). Sem pasta `public`; entrada é `index.php` na raiz; assets em `assets/` na raiz.
- **Mensagens:** área de mensagem (erro/sucesso) **acima** do formulário em Intenção e Comunicado.
- **Documentação:** `PROCESSO_VENDAS_INTENCAO_COMUNICADO.md` (fluxo vendas → intenção → comunicado; papel do Autoconf; que “quem foi vendido” pode vir só do nosso banco). `FLUXOGRAMA_SISTEMA.md` (fluxogramas Mermaid). `database/schema_producao.sql` (SQL único para produção).

### Decisões importantes
- **Autoconf não é obrigatório para “saber o que foi vendido”.** Podemos armazenar e listar tudo no nosso banco; Autoconf serve para preencher dados por ID e (opcional) anexar PDF.
- **Lista de clientes vendidos a partir do dia X:** pode ser feita só com nosso banco (`intencoes_venda` / `comunicados_venda` + `created_at`).

---

## 2. O que o cliente pediu (conversa 31/01/2026)

- Lista de **clientes vendidos a partir do dia que o sistema começar a funcionar**.
- Ao fazer intenção, o item **sair** da lista “a fazer” e ir para **“Intenção feita”**; depois **“Comunicado feito”**.
- Fluxo: Pendente intenção → Intenção feita → Comunicado feito (tudo pode ser controlado no nosso banco).

---

## 3. O que falta para finalizar

### Prioridade alta (para fechar o uso diário)
1. **Listas por status (nosso banco):**
   - Tela ou seção **“Pendentes de intenção”** (ex.: cadastros/vendas que ainda não têm registro em `intencoes_venda` – pode ser lista manual ou importada, sem depender do Autoconf).
   - Tela ou seção **“Intenção feita”** (registros em `intencoes_venda`).
   - Tela ou seção **“Pendentes de comunicado”** (quem tem intenção mas ainda não tem em `comunicados_venda`).
   - Tela ou seção **“Comunicado feito”** (registros em `comunicados_venda`).
   - **Relatório “Clientes vendidos a partir de [data]”** (consulta em `intencoes_venda`/`comunicados_venda` por `created_at >= data`).

### Prioridade média (segurança e qualidade)
2. **Validação:** CPF (formato + dígitos), placa (formato) no backend e no frontend.
3. **Segurança:** token CSRF nos formulários; escape (XSS) nas views ao exibir dados dinâmicos.
4. **Base URL no JS:** já está usando `window.APP_BASE` (vindo do config) nas chamadas fetch; conferir em produção se está correto para `/motos`.

### Prioridade baixa / opcional
5. **Autenticação:** se o sistema for multiusuário ou exposto, definir login e permissões.
6. **.env.example:** criar com variáveis documentadas (DB, Autoconf, COMven).
7. **README:** como rodar testes, configurar servidor, variáveis de ambiente.
8. **Integração Serpro:** na especificação original (consulta CRV pela placa); não implementada; só se o cliente exigir.
9. **Limpeza:** remover ou mover para `tests/` os scripts de teste na raiz (`teste_*.php`, `testar_*.php`, etc.) se ainda existirem.

---

## 4. Onde estão as coisas no projeto

| O quê | Onde |
|-------|------|
| Entrada da aplicação | `index.php` (raiz) |
| Roteamento | `index.php` (base `/motos`) |
| Config base URL | `config/app.php` → `base_url` = `/motos` |
| Controllers | `app/Controllers/` (IntencaoVendaController, ComunicadoVendaController, HomeController) |
| Autoconf (API) | `app/Models/Autoconf/AutoconfClient.php` (buscarVenda, listarVeiculos, buscarCliente, anexarDocumento) |
| Serviço Autoconf | `app/Models/Autoconf/AutoconfService.php` |
| Formulários | `app/Views/intencao-venda/formulario.php`, `app/Views/comunicado-venda/formulario.php` |
| Layout (menu, APP_BASE) | `app/Views/layout/header.php` |
| Credenciais | `.env` (e `.env_prod` em produção); Autoconf: `AUTOCONF_TOKEN`, `AUTOCONF_TOKEN2`, `AUTOCONF_API_URL` |
| Banco produção | `database/schema_producao.sql` |
| Processo (vendas/intenção/comunicado) | `PROCESSO_VENDAS_INTENCAO_COMUNICADO.md` |
| Fluxogramas | `FLUXOGRAMA_SISTEMA.md` |
| Este status | `STATUS_PROJETO.md` |

---

## 5. Como retomar

1. Abrir o projeto em `/var/www/motos` (ou o caminho da hospedagem).
2. Ler este arquivo (`STATUS_PROJETO.md`) e o `PROCESSO_VENDAS_INTENCAO_COMUNICADO.md`.
3. Próximo passo sugerido: implementar as **listas por status** e o **relatório “Clientes vendidos a partir de [data]”** usando só o nosso banco (sem depender do Autoconf para “o que foi vendido”).

---

*Atualize este arquivo quando concluir itens ou mudar prioridades.*
