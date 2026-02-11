<?php

namespace App\Models\Autoconf;

use App\Models\Entities\Cliente;
use App\Models\Entities\Veiculo;
use App\Models\Repositories\ClienteRepository;
use App\Models\Repositories\VeiculoRepository;
use App\Models\Repositories\VendasAutoconfRepository;
use App\Services\CacheService;

class AutoconfService
{
    private AutoconfClient $client;
    private ClienteRepository $clienteRepository;
    private VeiculoRepository $veiculoRepository;
    private VendasAutoconfRepository $vendasAutoconfRepository;
    private ?CacheService $cacheService;

    public function __construct()
    {
        $this->client = new AutoconfClient();
        $this->clienteRepository = new ClienteRepository();
        $this->veiculoRepository = new VeiculoRepository();
        $this->vendasAutoconfRepository = new VendasAutoconfRepository();
        $this->cacheService = class_exists(CacheService::class) ? new CacheService() : null;
    }

    public function buscarDadosVenda(string $vendaId): array
    {
        $cacheKey = "autoconf_venda_{$vendaId}";

        if ($this->cacheService) {
            $cached = $this->cacheService->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        try {
            $dados = $this->client->buscarVenda($vendaId);

            if ($this->cacheService) {
                $this->cacheService->set($cacheKey, $dados, 3600);
            }

            $this->salvarLog('autoconf', 'buscar_venda', ['venda_id' => $vendaId], $dados, 'sucesso');

            return $dados;

        } catch (\Exception $e) {
            $this->salvarLog('autoconf', 'buscar_venda', ['venda_id' => $vendaId], null, 'erro', $e->getMessage());
            throw $e;
        }
    }

    public function buscarESalvarDados(string $vendaId): array
    {
        $dados = $this->buscarDadosVenda($vendaId);

        // Busca veículo primeiro
        $veiculo = new Veiculo();
        $veiculo->setPlaca($dados['veiculo']['placa']);
        $veiculo->setModelo($dados['veiculo']['modelo']);
        $veiculo->setAno($dados['veiculo']['ano'] ? (int) $dados['veiculo']['ano'] : null);
        $veiculo = $this->veiculoRepository->findOrCreate($veiculo);

        // Estratégia de busca de cliente (em ordem de prioridade):
        // 1. Busca no banco de dados próprio (por CPF ou por veículo)
        // 2. Se não encontrar, tenta buscar no Autoconf (caso exista endpoint)
        // 3. Se não encontrar, usa dados retornados pela venda (podem estar vazios)
        // 4. Se ainda não tiver dados, deixa vazio para preenchimento manual

        $cliente = null;

        // 1. Tenta buscar no banco por CPF (se tiver CPF nos dados)
        if (!empty($dados['cliente']['cpf'])) {
            $cliente = $this->clienteRepository->findByCpf($dados['cliente']['cpf']);
        }

        // 2. Se não encontrou por CPF, tenta buscar por veículo
        if (!$cliente && !empty($dados['veiculo']['placa'])) {
            $cliente = $this->clienteRepository->buscarClientePorVeiculo($dados['veiculo']['placa']);
        }

        // 3. Se não encontrou no banco, tenta buscar no Autoconf (caso exista endpoint)
        if (!$cliente && !empty($vendaId)) {
            try {
                // Tenta buscar cliente diretamente no Autoconf
                // Nota: Este método retorna null se o endpoint não existir ainda
                $dadosClienteAutoconf = $this->client->buscarCliente($vendaId);
                
                if ($dadosClienteAutoconf && !empty($dadosClienteAutoconf['cpf'])) {
                    // Se encontrou no Autoconf, cria/atualiza no banco
                    $cliente = new Cliente();
                    $cliente->setNome($dadosClienteAutoconf['nome']);
                    $cliente->setCpf($dadosClienteAutoconf['cpf']);
                    $cliente->setTelefone($dadosClienteAutoconf['telefone']);
                    $cliente->setWhatsapp($dadosClienteAutoconf['whatsapp']);
                    $cliente->setEndereco($dadosClienteAutoconf['endereco']);
                    $cliente = $this->clienteRepository->findOrCreate($cliente);
                }
            } catch (\Exception $e) {
                // Endpoint não disponível ou erro - continua com dados da venda
                error_log("Erro ao buscar cliente no Autoconf: " . $e->getMessage());
            }
        }

        // 4. Se ainda não encontrou, usa dados retornados pela venda
        // (podem estar vazios se o Autoconf não retornar dados de cliente)
        if (!$cliente) {
            $cliente = new Cliente();
            $cliente->setNome($dados['cliente']['nome'] ?? '');
            $cliente->setCpf($dados['cliente']['cpf'] ?? '');
            $cliente->setTelefone($dados['cliente']['telefone'] ?? '');
            $cliente->setWhatsapp($dados['cliente']['whatsapp'] ?? '');
            $cliente->setEndereco($dados['cliente']['endereco'] ?? '');
            
            // Só salva no banco se tiver pelo menos CPF
            if (!empty($cliente->getCpf())) {
                $cliente = $this->clienteRepository->findOrCreate($cliente);
            }
        }

        // Guarda snapshot em vendas_autoconf para não perder histórico quando a moto sair do Autoconf
        $this->vendasAutoconfRepository->saveSnapshot($vendaId, $veiculo->getId(), $cliente->getId());

        return [
            'cliente' => $cliente->toArray(),
            'veiculo' => $veiculo->toArray(),
        ];
    }

    /**
     * Sincroniza a lista de veículos do Autoconf no nosso banco (vendas_autoconf + veiculos).
     * Assim não perdemos o histórico quando a moto for vendida e sair do Autoconf.
     * Cada item da lista deve ter: id, placa, modelo, ano.
     */
    public function sincronizarListaNoBanco(array $veiculos): void
    {
        foreach ($veiculos as $v) {
            $idAutoconf = (string) ($v['id'] ?? '');
            if ($idAutoconf === '') {
                continue;
            }
            $placa = trim($v['placa'] ?? '');
            if ($placa === '') {
                $placa = 'AC-' . $idAutoconf;
            }
            $veiculo = new Veiculo();
            $veiculo->setPlaca($placa);
            $veiculo->setModelo(trim($v['modelo'] ?? ''));
            $ano = $v['ano'] ?? '';
            $veiculo->setAno($ano !== '' ? (int) $ano : null);
            $veiculo = $this->veiculoRepository->findOrCreate($veiculo);
            $this->vendasAutoconfRepository->saveSnapshot($idAutoconf, $veiculo->getId(), null);
        }
    }

    public function anexarDocumento(string $vendaId, string $caminhoArquivo, string $nomeArquivo): bool
    {
        try {
            $resultado = $this->client->anexarDocumento($vendaId, $caminhoArquivo, $nomeArquivo);

            $this->salvarLog(
                'autoconf',
                'anexar_documento',
                ['venda_id' => $vendaId, 'arquivo' => $nomeArquivo],
                ['sucesso' => $resultado],
                $resultado ? 'sucesso' : 'erro'
            );

            return $resultado;

        } catch (\Exception $e) {
            $this->salvarLog(
                'autoconf',
                'anexar_documento',
                ['venda_id' => $vendaId, 'arquivo' => $nomeArquivo],
                null,
                'erro',
                $e->getMessage()
            );
            throw $e;
        }
    }

    private function salvarLog(
        string $tipo,
        string $operacao,
        array $parametros,
        ?array $resposta,
        string $status,
        ?string $mensagemErro = null
    ): void {
        try {
            $db = \App\Services\Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO logs_integracao 
                (tipo, operacao, parametros, resposta, status, mensagem_erro)
                VALUES (:tipo, :operacao, :parametros, :resposta, :status, :mensagem_erro)
            ");

            $stmt->execute([
                'tipo' => $tipo,
                'operacao' => $operacao,
                'parametros' => json_encode($parametros),
                'resposta' => $resposta ? json_encode($resposta) : null,
                'status' => $status,
                'mensagem_erro' => $mensagemErro,
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao salvar log de integração: " . $e->getMessage());
        }
    }
}


