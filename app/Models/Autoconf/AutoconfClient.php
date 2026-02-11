<?php

namespace App\Models\Autoconf;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AutoconfClient
{
    private Client $httpClient;
    private array $config;
    private int $retryAttempts;

    public function __construct()
    {
        $configPath = dirname(__DIR__, 3) . '/config/autoconf.php';
        if (!file_exists($configPath)) {
            throw new \RuntimeException("Arquivo de configuração não encontrado: {$configPath}");
        }
        $this->config = require $configPath;
        $this->retryAttempts = $this->config['retry_attempts'] ?? 3;

        $this->httpClient = new Client([
            'base_uri' => $this->config['api_url'],
            'timeout' => $this->config['timeout'] ?? 30,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function buscarVenda(string $vendaId): array
    {
        if (empty($vendaId)) {
            throw new \InvalidArgumentException('ID da venda é obrigatório');
        }
        
        // Tenta primeiro o endpoint direto de veículo
        try {
            $formData = [
                'id' => $vendaId,
            ];
            
            if (!empty($this->config['token2'])) {
                $formData['token'] = $this->config['token2'];
            }
            
            $response = $this->httpClient->post('/api/v1/veiculo', [
                'headers' => $this->getAuthHeaders(),
                'form_params' => $formData,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() === 200 && $data) {
                return $this->normalizarDados($data);
            }
        } catch (GuzzleException $e) {
            // Se der 403 ou 404, tenta buscar no relatório de veículos
            if ($e->getCode() === 403 || $e->getCode() === 404) {
                $veiculo = $this->buscarVeiculoPorRelatorio($vendaId);
                if ($veiculo) {
                    return $veiculo;
                }
            }
        }
        
        // Se falhar tudo, tenta buscar na lista de veículos
        try {
            $formData = [
                'termo' => $vendaId,
                'tipo' => 'motos',
                'pagina' => '1',
                'registros_por_pagina' => '100',
            ];
            
            if (!empty($this->config['token2'])) {
                $formData['token'] = $this->config['token2'];
            }
            
            $response = $this->httpClient->post('/api/v1/veiculos', [
                'headers' => $this->getAuthHeaders(),
                'form_params' => $formData,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() === 200 && isset($data['veiculos']) && is_array($data['veiculos'])) {
                foreach ($data['veiculos'] as $veiculo) {
                    if (isset($veiculo['id']) && (string)$veiculo['id'] === (string)$vendaId) {
                        return $this->normalizarDadosLista($veiculo);
                    }
                }
            }
        } catch (GuzzleException $e) {
            // Continua para o relatório
        }
        
        // Última tentativa: relatório de veículos
        $veiculo = $this->buscarVeiculoPorRelatorio($vendaId);
        if ($veiculo) {
            return $veiculo;
        }

        throw new \RuntimeException(
            "Erro ao buscar venda no Autoconf. Veículo ID {$vendaId} não encontrado."
        );
    }

    /**
     * Lista veículos/vendas do Autoconf para o usuário escolher (quando não sabe o ID).
     * Retorna array com itens: id, placa, modelo, ano (para exibir em select/lista).
     */
    public function listarVeiculos(int $pagina = 1, string $termo = '', int $porPagina = 50): array
    {
        // 1. Tenta endpoint de lista POST /api/v1/veiculos
        try {
            $formData = [
                'termo' => $termo,
                'tipo' => 'motos',
                'pagina' => (string) $pagina,
                'registros_por_pagina' => (string) $porPagina,
            ];
            if (!empty($this->config['token2'])) {
                $formData['token'] = $this->config['token2'];
            }

            $response = $this->httpClient->post('/api/v1/veiculos', [
                'headers' => $this->getAuthHeaders(),
                'form_params' => $formData,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            if ($response->getStatusCode() === 200 && isset($data['veiculos']) && is_array($data['veiculos'])) {
                $lista = [];
                foreach ($data['veiculos'] as $v) {
                    $lista[] = [
                        'id' => (string) ($v['id'] ?? ''),
                        'placa' => $this->limparPlaca($v['placa'] ?? $v['plate'] ?? ''),
                        'modelo' => trim($v['modelo_nome'] ?? $v['modelo'] ?? ''),
                        'ano' => (string) ($v['ano'] ?? $v['year'] ?? $v['ano_fabricacao'] ?? ''),
                    ];
                }
                $total = isset($data['total']) ? (int) $data['total'] : count($lista);
                return ['veiculos' => $lista, 'total' => $total];
            }
        } catch (GuzzleException $e) {
            // Continua para o relatório
        }

        // 2. Fallback: relatório CSV de estoque
        try {
            $mes = date('m');
            $ano = date('Y');
            $queryParams = [
                'Authorization' => $this->config['token'] ?? '',
                'token' => $this->config['token2'] ?? '',
                'mes' => $mes,
                'ano' => $ano,
            ];
            $response = $this->httpClient->get('/api/v1/relatorio/estoque/veiculos', ['query' => $queryParams]);
            if ($response->getStatusCode() !== 200) {
                return ['veiculos' => [], 'total' => 0];
            }
            $csv = $response->getBody()->getContents();
            $rows = $this->parseCsv($csv);
            $filtrados = [];
            foreach ($rows as $row) {
                $id = trim($row['ID Veículo'] ?? $row['ID'] ?? '');
                if ($id === '') {
                    continue;
                }
                $modelo = trim(($row['Marca'] ?? '') . ' ' . ($row['Modelo'] ?? '') . ' ' . ($row['Versão'] ?? ''));
                $placa = $this->limparPlaca($row['Placa'] ?? '');
                if ($termo !== '' && stripos($modelo, $termo) === false && stripos($placa, $termo) === false && stripos($id, $termo) === false) {
                    continue;
                }
                $filtrados[] = [
                    'id' => $id,
                    'placa' => $placa,
                    'modelo' => $modelo,
                    'ano' => trim($row['Ano Modelo'] ?? $row['Ano Fabricação'] ?? ''),
                ];
            }
            $total = count($filtrados);
            $offset = ($pagina - 1) * $porPagina;
            $lista = array_slice($filtrados, $offset, $porPagina);
            return ['veiculos' => $lista, 'total' => $total];
        } catch (GuzzleException $e) {
            return ['veiculos' => [], 'total' => 0];
        }
    }
    
    /**
     * Busca veículo no relatório de veículos
     */
    private function buscarVeiculoPorRelatorio(string $veiculoId): ?array
    {
        try {
            $mes = date('m');
            $ano = date('Y');
            
            $queryParams = [
                'Authorization' => $this->config['token'] ?? '',
                'token' => $this->config['token2'] ?? '',
                'mes' => $mes,
                'ano' => $ano,
            ];

            $response = $this->httpClient->get('/api/v1/relatorio/estoque/veiculos', [
                'query' => $queryParams,
            ]);

            if ($response->getStatusCode() === 200) {
                $csv = $response->getBody()->getContents();
                $veiculos = $this->parseCsv($csv);
                
                // Busca veículo pelo ID
                foreach ($veiculos as $veiculo) {
                    $id = $veiculo['ID Veículo'] ?? null;
                    if ((string)$id === (string)$veiculoId) {
                        // Encontrou o veículo - normaliza os dados
                        return $this->normalizarDadosVeiculoRelatorio($veiculo);
                    }
                }
            }
        } catch (GuzzleException $e) {
            // Silencioso - retorna null
        }

        return null;
    }
    
    /**
     * Normaliza dados do veículo retornados pelo relatório
     */
    private function normalizarDadosVeiculoRelatorio(array $data): array
    {
        return [
            'cliente' => [
                'nome' => '',
                'cpf' => '',
                'telefone' => '',
                'whatsapp' => '',
                'endereco' => '',
            ],
            'veiculo' => [
                'modelo' => trim(($data['Marca'] ?? '') . ' ' . ($data['Modelo'] ?? '') . ' ' . ($data['Versão'] ?? '')),
                'placa' => $this->limparPlaca($data['Placa'] ?? ''),
                'ano' => $data['Ano Modelo'] ?? $data['Ano Fabricação'] ?? '',
            ],
        ];
    }
    
    /**
     * Busca detalhes completos de um veículo específico
     */
    private function buscarDetalhesVeiculo(string $vendaId, array $veiculoBasico): array
    {
        // Tenta buscar detalhes completos usando o endpoint singular
        // Se não funcionar, normaliza com os dados básicos que já temos
        try {
            $formData = [];
            if (!empty($this->config['token2'])) {
                $formData['token'] = $this->config['token2'];
            }
            $formData['id'] = $vendaId;

            $response = $this->httpClient->post('/api/v1/veiculo', [
                'headers' => $this->getAuthHeaders(),
                'form_params' => $formData,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() === 200 && $data) {
                return $this->normalizarDados($data);
            }
        } catch (\Exception $e) {
            // Se falhar, usa os dados básicos da lista
        }
        
        // Normaliza com os dados básicos disponíveis
        return $this->normalizarDadosLista($veiculoBasico);
    }
    
    /**
     * Normaliza dados de um veículo retornado da lista
     */
    private function normalizarDadosLista(array $data): array
    {
        return [
            'cliente' => [
                'nome' => $data['cliente']['nome'] ?? '',
                'cpf' => $data['cliente']['cpf'] ?? '',
                'telefone' => $data['cliente']['telefone'] ?? '',
                'whatsapp' => $data['cliente']['whatsapp'] ?? '',
                'endereco' => $this->formatarEndereco($data['cliente'] ?? []),
            ],
            'veiculo' => [
                'modelo' => $data['modelo_nome'] ?? $data['modelo'] ?? '',
                'placa' => $this->limparPlaca($data['placa'] ?? $data['plate'] ?? ''),
                'ano' => $data['ano'] ?? $data['year'] ?? $data['ano_fabricacao'] ?? '',
            ],
        ];
    }

    public function anexarDocumento(string $vendaId, string $caminhoArquivo, string $nomeArquivo): bool
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                if (!file_exists($caminhoArquivo)) {
                    throw new \RuntimeException("Arquivo não encontrado: {$caminhoArquivo}");
                }

                $multipart = [
                    [
                        'name' => 'arquivo',
                        'contents' => fopen($caminhoArquivo, 'r'),
                        'filename' => $nomeArquivo,
                    ],
                ];

                if (!empty($this->config['token2'])) {
                    $multipart[] = [
                        'name' => 'token',
                        'contents' => $this->config['token2'],
                    ];
                }

                $response = $this->httpClient->post("/api/v1/veiculos/{$vendaId}/anexos", [
                    'headers' => $this->getAuthHeaders(),
                    'multipart' => $multipart,
                ]);

                return $response->getStatusCode() === 200 || $response->getStatusCode() === 201;

            } catch (GuzzleException $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt < $this->retryAttempts) {
                    sleep(1);
                    continue;
                }
            }
        }

        throw new \RuntimeException(
            "Erro ao anexar documento no Autoconf após {$this->retryAttempts} tentativas: " . 
            ($lastException ? $lastException->getMessage() : 'Erro desconhecido')
        );
    }

    private function getAuthHeaders(): array
    {
        $headers = [];

        if (!empty($this->config['token'])) {
            // Conforme teste no Postman: Authorization NÃO usa "Bearer ", apenas o token direto
            $headers['Authorization'] = $this->config['token'];
        }

        return $headers;
    }

    public function buscarRevenda(): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $formData = [];
                if (!empty($this->config['token2'])) {
                    $formData['token'] = $this->config['token2'];
                }

                $response = $this->httpClient->post("/api/v1/revenda", [
                    'headers' => $this->getAuthHeaders(),
                    'form_params' => $formData,
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if ($response->getStatusCode() === 200 && $data) {
                    return $data;
                }

                throw new \RuntimeException('Resposta inválida da API Autoconf');

            } catch (GuzzleException $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt < $this->retryAttempts) {
                    sleep(1);
                    continue;
                }
            }
        }

        throw new \RuntimeException(
            "Erro ao buscar revenda no Autoconf após {$this->retryAttempts} tentativas: " . 
            ($lastException ? $lastException->getMessage() : 'Erro desconhecido')
        );
    }

    /**
     * Busca dados do cliente no Autoconf usando API de Relatórios
     * 
     * Estratégia de busca:
     * 1. Busca em Negociações de Entrada para encontrar o ID do cliente associado ao veículo
     * 2. Com o ID do cliente, busca os dados completos na lista de clientes
     * 3. Se não encontrar, tenta buscar em Atendimentos Recebidos
     * 
     * @param string $veiculoId ID do veículo no Autoconf (usado para buscar cliente associado)
     * @return array|null Dados do cliente ou null se não encontrado
     */
    public function buscarCliente(string $veiculoId): ?array
    {
        if (empty($veiculoId)) {
            return null;
        }

        // 1. Tenta buscar em Negociações de Entrada (tem Cliente Negociação ID associado ao Veiculo ID)
        $clienteId = $this->buscarClienteIdPorNegociacaoEntrada($veiculoId);
        if ($clienteId) {
            // Com o ID do cliente, busca dados completos na lista de clientes
            $cliente = $this->buscarClientePorId($clienteId);
            if ($cliente) {
                return $cliente;
            }
        }

        // 2. Tenta buscar em Atendimentos Recebidos (tem dados de cliente por placa)
        $cliente = $this->buscarClientePorAtendimentos($veiculoId);
        if ($cliente) {
            return $cliente;
        }

        return null;
    }

    /**
     * Busca o ID do cliente associado a um veículo através de Negociações de Entrada
     */
    private function buscarClienteIdPorNegociacaoEntrada(string $veiculoId): ?string
    {
        try {
            $mes = date('m');
            $ano = date('Y');
            
            $queryParams = [
                'Authorization' => $this->config['token'] ?? '',
                'token' => $this->config['token2'] ?? '',
                'mes' => $mes,
                'ano' => $ano,
            ];

            $response = $this->httpClient->get('/api/v1/relatorio/financeiro/negociacao-entrada', [
                'query' => $queryParams,
            ]);

            if ($response->getStatusCode() === 200) {
                $csv = $response->getBody()->getContents();
                $negociacoes = $this->parseCsv($csv);
                
                // Busca negociação com o Veiculo ID correspondente
                foreach ($negociacoes as $negociacao) {
                    $veiculoIdRelatorio = $negociacao['Veiculo ID'] ?? null;
                    
                    if ((string)$veiculoIdRelatorio === (string)$veiculoId) {
                        // Encontrou negociação com este veículo - retorna o ID do cliente
                        $clienteId = $negociacao['Cliente Negociação ID'] ?? null;
                        if ($clienteId) {
                            return (string)$clienteId;
                        }
                    }
                }
            }
        } catch (GuzzleException $e) {
            if ($e->getCode() !== 404 && $e->getCode() !== 403) {
                error_log("Erro ao buscar cliente ID em negociações de entrada: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Busca dados completos do cliente pelo ID na lista de clientes
     */
    private function buscarClientePorId(string $clienteId): ?array
    {
        try {
            $queryParams = [
                'Authorization' => $this->config['token'] ?? '',
                'token' => $this->config['token2'] ?? '',
            ];

            $response = $this->httpClient->get('/api/v1/relatorio/financeiro/clientes', [
                'query' => $queryParams,
            ]);

            if ($response->getStatusCode() === 200) {
                $csv = $response->getBody()->getContents();
                $clientes = $this->parseCsv($csv);
                
                // Busca cliente pelo ID
                foreach ($clientes as $cliente) {
                    $id = $cliente['id'] ?? null;
                    if ((string)$id === (string)$clienteId) {
                        // Encontrou o cliente - normaliza os dados
                        return $this->normalizarDadosClienteRelatorio($cliente);
                    }
                }
            }
        } catch (GuzzleException $e) {
            if ($e->getCode() !== 404 && $e->getCode() !== 403) {
                error_log("Erro ao buscar cliente por ID: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Normaliza dados do cliente retornados pelo relatório de clientes
     */
    private function normalizarDadosClienteRelatorio(array $data): array
    {
        // Monta endereço completo
        $endereco = [];
        if (!empty($data['endereco'])) {
            $endereco[] = $data['endereco'];
        }
        if (!empty($data['numero'])) {
            $endereco[] = 'Nº ' . $data['numero'];
        }
        if (!empty($data['complemento'])) {
            $endereco[] = $data['complemento'];
        }
        if (!empty($data['desc_bairro'])) {
            $endereco[] = $data['desc_bairro'];
        }
        if (!empty($data['desc_municipio'])) {
            $endereco[] = $data['desc_municipio'];
        }
        if (!empty($data['sigla_uf'])) {
            $endereco[] = $data['sigla_uf'];
        }
        if (!empty($data['cep'])) {
            $endereco[] = 'CEP: ' . $data['cep'];
        }
        
        $enderecoCompleto = implode(', ', array_filter($endereco));
        
        return [
            'nome' => $data['nome_razao_social'] ?? '',
            'cpf' => $data['cpf_cnpj'] ?? '',
            'telefone' => $data['telefone'] ?? '',
            'whatsapp' => $data['telefone'] ?? '', // Usa telefone como WhatsApp se não tiver campo específico
            'endereco' => $enderecoCompleto,
        ];
    }


    /**
     * Busca cliente em Atendimentos Recebidos (pode ter dados de cliente)
     */
    private function buscarClientePorAtendimentos(string $veiculoId): ?array
    {
        try {
            $mes = date('m');
            $ano = date('Y');
            
            $queryParams = [
                'Authorization' => $this->config['token'] ?? '',
                'token' => $this->config['token2'] ?? '',
                'mes' => $mes,
                'ano' => $ano,
            ];

            $response = $this->httpClient->get('/api/v1/relatorio/crm/atendimentos-recebidos', [
                'query' => $queryParams,
            ]);

            if ($response->getStatusCode() === 200) {
                $csv = $response->getBody()->getContents();
                $atendimentos = $this->parseCsv($csv);
                
                // Busca atendimento com placa correspondente ao veículo
                // Primeiro precisa buscar a placa do veículo
                $dadosVeiculo = $this->buscarVenda($veiculoId);
                $placaVeiculo = $dadosVeiculo['veiculo']['placa'] ?? '';
                
                if (empty($placaVeiculo)) {
                    return null;
                }
                
                foreach ($atendimentos as $atendimento) {
                    $placaInteresse = $atendimento['Veículo Interesse - Placa'] ?? '';
                    $placaAvaliada = $atendimento['Veículo Avaliado - Placa'] ?? '';
                    
                    if ($placaInteresse === $placaVeiculo || $placaAvaliada === $placaVeiculo) {
                        // Encontrou atendimento com esta placa - extrai dados do cliente
                        $nomeCliente = $atendimento['Cliente'] ?? '';
                        $celular = $atendimento['Celular'] ?? '';
                        $telefone = $atendimento['Telefone'] ?? '';
                        $email = $atendimento['E-mail'] ?? '';
                        
                        if (!empty($nomeCliente)) {
                            return [
                                'nome' => $nomeCliente,
                                'cpf' => '', // Não vem no relatório
                                'telefone' => $telefone ?: $celular,
                                'whatsapp' => $celular,
                                'endereco' => '', // Não vem no relatório
                            ];
                        }
                    }
                }
            }
        } catch (GuzzleException $e) {
            if ($e->getCode() !== 404) {
                error_log("Erro ao buscar cliente em atendimentos recebidos: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Faz parse de CSV retornado pela API de Relatórios
     */
    private function parseCsv(string $csv): array
    {
        if (empty($csv)) {
            return [];
        }

        $lines = explode("\n", trim($csv));
        if (count($lines) < 2) {
            return [];
        }

        // Primeira linha são os headers
        $headers = str_getcsv($lines[0]);
        $headers = array_map('trim', $headers);

        $data = [];
        for ($i = 1; $i < count($lines); $i++) {
            if (empty(trim($lines[$i]))) {
                continue;
            }
            
            $values = str_getcsv($lines[$i]);
            if (count($values) !== count($headers)) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = isset($values[$index]) ? trim($values[$index]) : '';
            }
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Normaliza dados do cliente retornados pela API Autoconf
     */
    private function normalizarDadosCliente(array $data): array
    {
        return [
            'nome' => $data['nome'] ?? $data['name'] ?? '',
            'cpf' => $data['cpf'] ?? $data['documento'] ?? '',
            'telefone' => $data['telefone'] ?? $data['phone'] ?? '',
            'whatsapp' => $data['whatsapp'] ?? $data['telefone'] ?? $data['phone'] ?? '',
            'endereco' => $this->formatarEndereco($data),
        ];
    }

    private function normalizarDados(array $data): array
    {
        // Suporta estrutura de feed de estoque (quando não há dados de cliente)
        $isFeedEstoque = isset($data['make']) || isset($data['model']);
        
        if ($isFeedEstoque) {
            // Estrutura do feed de estoque - apenas dados do veículo
            return [
                'cliente' => [
                    'nome' => '',
                    'cpf' => '',
                    'telefone' => $data['phone'] ?? '',
                    'whatsapp' => $data['phone'] ?? '',
                    'endereco' => $this->formatarEnderecoFeed($data),
                ],
                'veiculo' => [
                    'modelo' => trim(($data['make'] ?? '') . ' ' . ($data['model'] ?? '') . ' ' . ($data['version'] ?? '')),
                    'placa' => $this->limparPlaca($data['plate'] ?? ''),
                    'ano' => $data['year'] ?? $data['fabric_year'] ?? '',
                ],
            ];
        }
        
        // Estrutura padrão da API - endpoint /api/v1/veiculo retorna apenas dados do veículo
        // Dados do cliente não vêm do Autoconf, precisam ser preenchidos manualmente ou buscados no banco
        return [
            'cliente' => [
                'nome' => $data['cliente']['nome'] ?? $data['comprador']['nome'] ?? $data['cliente_nome'] ?? '',
                'cpf' => $data['cliente']['cpf'] ?? $data['comprador']['cpf'] ?? $data['cliente_cpf'] ?? '',
                'telefone' => $data['cliente']['telefone'] ?? $data['comprador']['telefone'] ?? $data['cliente_telefone'] ?? '',
                'whatsapp' => $data['cliente']['whatsapp'] ?? $data['comprador']['whatsapp'] ?? $data['cliente']['telefone'] ?? $data['cliente_whatsapp'] ?? '',
                'endereco' => $this->formatarEndereco($data['cliente'] ?? $data['comprador'] ?? []),
            ],
            'veiculo' => [
                'modelo' => $data['modelo_nome'] ?? $data['veiculo']['modelo'] ?? $data['moto']['modelo'] ?? $data['modelo'] ?? '',
                'placa' => $this->limparPlaca($data['placa_completa'] ?? $data['placa'] ?? $data['veiculo']['placa'] ?? $data['moto']['placa'] ?? $data['plate'] ?? ''),
                'ano' => $data['anofabricacao'] ?? $data['anomodelo'] ?? $data['veiculo']['ano'] ?? $data['moto']['ano'] ?? $data['ano'] ?? $data['year'] ?? '',
            ],
        ];
    }
    
    private function formatarEnderecoFeed(array $data): string
    {
        $endereco = [];
        
        if (!empty($data['location_city'])) {
            $endereco[] = $data['location_city'];
        }
        if (!empty($data['location_state'])) {
            $endereco[] = $data['location_state'];
        }
        if (!empty($data['neighborhood'])) {
            $endereco[] = $data['neighborhood'];
        }
        if (!empty($data['zip_code'])) {
            $endereco[] = 'CEP: ' . $data['zip_code'];
        }
        
        return implode(', ', array_filter($endereco));
    }
    
    private function limparPlaca(string $placa): string
    {
        // Remove máscara de placa (ex: "A**-***0" -> "")
        $placa = str_replace(['*', '-', ' '], '', $placa);
        return $placa;
    }

    private function formatarEndereco(array $dados): string
    {
        $endereco = [];
        
        if (isset($dados['endereco']) && is_array($dados['endereco'])) {
            $end = $dados['endereco'];
            
            if (!empty($end['logradouro'])) {
                $endereco[] = $end['logradouro'];
            }
            if (!empty($end['numero'])) {
                $endereco[] = $end['numero'];
            }
            if (!empty($end['complemento'])) {
                $endereco[] = $end['complemento'];
            }
            if (isset($end['bairro']) && is_array($end['bairro'])) {
                if (!empty($end['bairro']['nome'])) {
                    $endereco[] = $end['bairro']['nome'];
                }
                if (isset($end['bairro']['cidade']) && is_array($end['bairro']['cidade'])) {
                    if (!empty($end['bairro']['cidade']['nome'])) {
                        $endereco[] = $end['bairro']['cidade']['nome'];
                    }
                    if (!empty($end['bairro']['cidade']['UF'])) {
                        $endereco[] = $end['bairro']['cidade']['UF'];
                    }
                }
            }
            if (!empty($end['cep'])) {
                $endereco[] = 'CEP: ' . $end['cep'];
            }
        } else {
            if (!empty($dados['logradouro'])) {
                $endereco[] = $dados['logradouro'];
            }
            if (!empty($dados['numero'])) {
                $endereco[] = $dados['numero'];
            }
            if (!empty($dados['bairro'])) {
                $endereco[] = is_array($dados['bairro']) ? ($dados['bairro']['nome'] ?? '') : $dados['bairro'];
            }
            if (!empty($dados['cidade'])) {
                $endereco[] = is_array($dados['cidade']) ? ($dados['cidade']['nome'] ?? '') : $dados['cidade'];
            }
            if (!empty($dados['estado'])) {
                $endereco[] = is_array($dados['estado']) ? ($dados['estado']['UF'] ?? '') : $dados['estado'];
            }
            if (!empty($dados['cep'])) {
                $endereco[] = 'CEP: ' . $dados['cep'];
            }
        }

        return implode(', ', array_filter($endereco));
    }
}

