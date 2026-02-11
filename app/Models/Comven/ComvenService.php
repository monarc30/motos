<?php

namespace App\Models\Comven;

use App\Models\Entities\ComunicadoVenda;
use App\Models\Repositories\ClienteRepository;
use App\Models\Repositories\VeiculoRepository;
use App\Models\Repositories\ComunicadoVendaRepository;
use App\Services\CacheService;

class ComvenService
{
    private ?ComvenClient $client;
    private ClienteRepository $clienteRepository;
    private VeiculoRepository $veiculoRepository;
    private ComunicadoVendaRepository $comunicadoRepository;
    private bool $enabled;

    public function __construct()
    {
        $configPath = dirname(__DIR__, 3) . '/config/comven.php';
        $config = require $configPath;
        $this->enabled = $config['enabled'] ?? false;

        if ($this->enabled) {
            try {
                $this->client = new ComvenClient();
            } catch (\RuntimeException $e) {
                $this->enabled = false;
                error_log("COMven desabilitado: " . $e->getMessage());
            }
        } else {
            $this->client = null;
        }

        $this->clienteRepository = new ClienteRepository();
        $this->veiculoRepository = new VeiculoRepository();
        $this->comunicadoRepository = new ComunicadoVendaRepository();
    }

    public function registrarComunicadoVenda(int $comunicadoId): ?array
    {
        if (!$this->enabled || !$this->client) {
            return null;
        }

        $comunicado = $this->comunicadoRepository->findById($comunicadoId);
        if (!$comunicado) {
            throw new \RuntimeException('Comunicado de venda não encontrado');
        }

        $cliente = $this->clienteRepository->findById($comunicado->getClienteId());
        $veiculo = $this->veiculoRepository->findById($comunicado->getVeiculoId());

        if (!$cliente || !$veiculo) {
            throw new \RuntimeException('Cliente ou veículo não encontrado');
        }

        $dados = [
            'data_comunicado' => $comunicado->getDataComunicado(),
            'cliente' => [
                'nome' => $cliente->getNome(),
                'cpf' => $cliente->getCpf(),
                'telefone' => $cliente->getTelefone(),
                'whatsapp' => $cliente->getWhatsapp(),
                'endereco' => $cliente->getEndereco(),
            ],
            'veiculo' => [
                'placa' => $veiculo->getPlaca(),
                'modelo' => $veiculo->getModelo(),
                'ano' => $veiculo->getAno(),
            ],
        ];

        try {
            $resultado = $this->client->registrarComunicadoVenda($dados);

            $this->salvarLog(
                'comven',
                'registrar_comunicado',
                ['comunicado_id' => $comunicadoId],
                $resultado,
                'sucesso'
            );

            return $resultado;

        } catch (\Exception $e) {
            $this->salvarLog(
                'comven',
                'registrar_comunicado',
                ['comunicado_id' => $comunicadoId],
                null,
                'erro',
                $e->getMessage()
            );
            throw $e;
        }
    }

    public function consultarStatus(string $protocolo): ?array
    {
        if (!$this->enabled || !$this->client) {
            return null;
        }

        try {
            $resultado = $this->client->consultarStatus($protocolo);

            $this->salvarLog(
                'comven',
                'consultar_status',
                ['protocolo' => $protocolo],
                $resultado,
                'sucesso'
            );

            return $resultado;

        } catch (\Exception $e) {
            $this->salvarLog(
                'comven',
                'consultar_status',
                ['protocolo' => $protocolo],
                null,
                'erro',
                $e->getMessage()
            );
            throw $e;
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
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


