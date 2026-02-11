# Fluxograma do Sistema – Gestão de Vendas e Comunicações de Motocicletas

Diagramas em [Mermaid](https://mermaid.js.org/). Para visualizar: abra no VS Code com extensão Mermaid, no GitHub, ou use [mermaid.live](https://mermaid.live).

---

## 1. Visão geral – Navegação e roteamento

```mermaid
flowchart TD
    A[Usuário acessa o sistema] --> B{URL / rota}
    B -->|/ ou /home| C[HomeController]
    B -->|/intencao-venda| D[IntencaoVendaController]
    B -->|/comunicado-venda| E[ComunicadoVendaController]
    
    C --> F[Página Inicial]
    F --> G[Links: Intenção de Venda | Comunicado de Venda]
    
    D --> H[Formulário Intenção de Venda]
    E --> I[Formulário Comunicado de Venda]
    
    G --> D
    G --> E
```

---

## 2. Fluxo completo – Intenção de Venda

```mermaid
flowchart TD
    subgraph Entrada
        A1[Usuário acessa Intenção de Venda]
        A2[Informa ID da venda no Autoconf]
        A3[Clica em Buscar Dados]
    end
    
    subgraph Backend_Busca
        B1[POST /intencao-venda/buscarAutoconf]
        B2[AutoconfService.buscarESalvarDados]
        B3[AutoconfClient: veículo + cliente]
        B4[Salva/atualiza Cliente e Veículo no BD]
        B5[Retorna JSON com dados]
    end
    
    subgraph Formulário
        C1[Formulário preenchido com dados]
        C2[Usuário revisa / preenche CRV]
        C3[Clica em Gerar PDF]
    end
    
    subgraph Geração
        D1[POST /intencao-venda/gerar]
        D2[Valida dados obrigatórios]
        D3[ClienteRepository.findOrCreate]
        D4[VeiculoRepository.findOrCreate]
        D5[IntencaoVendaRepository.create]
        D6[IntencaoVendaGenerator.gerar]
        D7[Salva PDF em storage/documentos/intencoes]
        D8[Atualiza intenção com caminho do PDF]
    end
    
    subgraph Autoconf_Opcional
        E1[AutoconfService.anexarDocumento]
        E2[Envia PDF para Autoconf]
    end
    
    subgraph Saída
        F1[Resposta: sucesso + link download]
        F2[Usuário baixa ou imprime PDF]
    end
    
    A1 --> A2 --> A3 --> B1 --> B2 --> B3 --> B4 --> B5
    B5 --> C1 --> C2 --> C3 --> D1 --> D2 --> D3 --> D4 --> D5 --> D6 --> D7 --> D8
    D8 --> E1
    E1 --> E2
    D8 --> F1 --> F2
```

---

## 3. Fluxo completo – Comunicado de Venda

```mermaid
flowchart TD
    subgraph Entrada
        A1[Usuário acessa Comunicado de Venda]
        A2[Opcional: informa ID venda e Buscar Dados]
        A3[Preenche cliente, veículo, data]
        A4[Clica em Gerar Comunicado ou Gerar Etiqueta]
    end
    
    subgraph Busca_Autoconf_Opcional
        B1[POST /comunicado-venda/buscarAutoconf]
        B2[Mesmo fluxo AutoconfService.buscarESalvarDados]
        B3[Preenche nome, CPF, placa, modelo]
    end
    
    subgraph Geração_Comunicado
        C1[POST /comunicado-venda/gerar]
        C2[Valida nome, CPF, placa]
        C3[Cliente e Veículo findOrCreate]
        C4[ComunicadoVendaRepository.create]
        C5[ComunicadoVendaGenerator.gerar]
        C6[PDF em storage/documentos/comunicados]
        C7[ComvenService.registrarComunicadoVenda]
        C8[Confirmação de venda no COMven]
    end
    
    subgraph Geração_Etiqueta
        D1[POST /comunicado-venda/gerarEtiqueta]
        D2[Busca cliente e veículo no BD]
        D3[EtiquetaEnvelopeGenerator.gerar]
        D4[PDF etiqueta em storage/documentos/etiquetas]
    end
    
    subgraph Saída
        E1[Resposta com arquivo e ID]
        E2[Download comunicado ou etiqueta]
    end
    
    A1 --> A2
    A2 --> B1
    B1 --> B2 --> B3
    B3 --> A3
    A2 --> A3
    A3 --> A4
    A4 --> C1
    C1 --> C2 --> C3 --> C4 --> C5 --> C6 --> C7 --> C8 --> E1
    A4 --> D1
    D1 --> D2 --> D3 --> D4 --> E1
    E1 --> E2
```

---

## 4. Busca de dados no Autoconf (buscarESalvarDados)

```mermaid
flowchart TD
    A[buscarESalvarDados vendaId] --> B[Cache?]
    B -->|Hit| C[Retorna cache]
    B -->|Miss| D[AutoconfClient.buscarVenda]
    
    D --> E[API /veiculo ou relatório CSV]
    E --> F[Normaliza dados veículo + cliente]
    F --> G[VeiculoRepository.findOrCreate]
    G --> H{Cliente encontrado?}
    
    H -->|Sim por CPF no BD| I[Usa cliente do BD]
    H -->|Não| J[Busca por placa no BD]
    J -->|Encontrou| I
    J -->|Não| K[AutoconfClient.buscarCliente]
    K --> L[Relatórios financeiro/atendimentos]
    L -->|Dados cliente| M[ClienteRepository.findOrCreate]
    L -->|Sem dados| N[Usa dados venda ou vazio]
    N --> M
    
    I --> O[Retorna cliente + veículo]
    M --> O
    C --> O
```

---

## 5. Roteamento (index.php na raiz)

```mermaid
flowchart LR
    A[Requisição HTTP] --> B[Parse URL path]
    B --> C{path}
    C -->|vazio / index.php| D[home / index]
    C -->|intencao-venda| E[intencao-venda / action]
    C -->|comunicado-venda| F[comunicado-venda / action]
    C -->|outro| D
    
    D --> G[HomeController]
    E --> H[IntencaoVendaController]
    F --> I[ComunicadoVendaController]
    
    G --> J{action}
    H --> K{action}
    I --> L{action}
    
    J -->|index| K1[Página inicial]
    K -->|index| K2[Formulário]
    K -->|buscarAutoconf| K3[JSON dados]
    K -->|gerar| K4[Gera PDF]
    K -->|download| K5[Download PDF]
    L -->|index| L1[Formulário]
    L -->|buscarAutoconf| L2[JSON dados]
    L -->|gerar| L3[Gera comunicado + COMven]
    L -->|gerarEtiqueta| L4[Gera etiqueta]
    L -->|download| L5[Download PDF]
```

---

## 6. Integrações externas (resumo)

```mermaid
flowchart LR
    subgraph Sistema
        S[App PHP]
    end
    
    subgraph Autoconf
        A1[GET/POST API veículo]
        A2[Relatórios CSV]
        A3[Upload documento]
    end
    
    subgraph COMven
        C1[Registro comunicado de venda]
    end
    
    subgraph Banco local
        DB[(MySQL)]
    end
    
    S -->|buscarVenda / buscarCliente| A1
    S -->|fallback dados| A2
    S -->|anexar PDF intenção| A3
    S -->|registrarComunicadoVenda| C1
    S -->|CRUD clientes, veículos, intenções, comunicados| DB
```

---

## Legenda rápida

| Elemento        | Significado                                      |
|-----------------|--------------------------------------------------|
| **Intenção de Venda** | Documento inicial: cliente declara intenção com dados do veículo e CRV. |
| **Comunicado de Venda** | Documento e etiqueta para envio; pode registrar no COMven. |
| **Autoconf**    | Origem dos dados da venda (veículo e, quando há, cliente). |
| **COMven**      | Confirmação/registro do comunicado de venda (se configurado). |
| **BD**          | Banco de dados local (clientes, veículos, intenções, comunicados). |

Para ver os diagramas renderizados: use a extensão **Mermaid** no VS Code ou cole o bloco de código em [mermaid.live](https://mermaid.live).
