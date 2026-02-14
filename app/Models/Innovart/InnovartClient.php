<?php

namespace App\Models\Innovart;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Cliente para o webhook Innovart (N8N) que busca dados na Autoconf por placa.
 * Retorna veículo e comprador para preencher Intenção de Venda / Comunicado.
 */
class InnovartClient
{
    private string $baseUrl;
    private string $user;
    private string $password;
    private int $timeout;

    public function __construct()
    {
        $config = require dirname(__DIR__, 3) . '/config/innovart.php';
        $this->baseUrl = $config['webhook_url'];
        $this->user = $config['user'];
        $this->password = $config['password'];
        $this->timeout = $config['timeout'];
    }

    /**
     * Busca dados por placa (getplaca).
     * Retorna array normalizado: ['cliente' => [...], 'veiculo' => [...], 'venda_id' => ?]
     */
    public function getPorPlaca(string $placa): array
    {
        $placa = preg_replace('/\s+/', '', strtoupper($placa));
        if ($placa === '') {
            throw new \InvalidArgumentException('Placa não informada.');
        }

        $url = $this->baseUrl . '/getplaca?placa=' . rawurlencode($placa);
        $client = new Client([
            'timeout' => $this->timeout,
            'verify' => true,
            'auth' => [$this->user, $this->password],
            'headers' => ['Accept' => 'application/json'],
        ]);

        try {
            $response = $client->get($url);
            $body = (string) $response->getBody();
            $data = json_decode($body, true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Erro ao buscar dados na Innovart por placa: ' . $e->getMessage(), 0, $e);
        }

        return $this->normalizarRespostaGetplaca($data, $placa);
    }

    /**
     * Normaliza a resposta do getplaca para o formato do sistema (cliente + veiculo).
     * Aceita estruturas variadas (array com vendas, objeto único, etc.).
     */
    private function normalizarRespostaGetplaca($data, string $placa): array
    {
        $cliente = ['nome' => '', 'cpf' => '', 'telefone' => '', 'whatsapp' => '', 'endereco' => ''];
        $veiculo = ['modelo' => '', 'placa' => $placa, 'ano' => null];
        $vendaId = null;

        if (!is_array($data)) {
            return ['cliente' => $cliente, 'veiculo' => $veiculo, 'venda_id' => $vendaId];
        }

        // Resposta pode ser array de itens (ex.: [ { vendas: [...], ... } ]) ou objeto único
        $lista = isset($data[0]) ? $data : [$data];
        $primeiro = null;
        foreach ($lista as $item) {
            if (!is_array($item)) {
                continue;
            }
            $vendas = $item['vendas'] ?? $item['venda'] ?? [];
            if (isset($item['placa']) || isset($item['veiculo'])) {
                $primeiro = $item;
                break;
            }
            if (is_array($vendas) && count($vendas) > 0) {
                $primeiro = $vendas[0];
                break;
            }
            $primeiro = $item;
            break;
        }
        if ($primeiro === null) {
            return ['cliente' => $cliente, 'veiculo' => $veiculo, 'venda_id' => $vendaId];
        }

        $v = $primeiro;
        // Veículo
        $veiculo['placa'] = $v['placa'] ?? $placa;
        $veiculo['modelo'] = $v['modelo'] ?? $v['veiculo_modelo'] ?? '';
        if (isset($v['ano'])) {
            $veiculo['ano'] = is_numeric($v['ano']) ? (int) $v['ano'] : null;
        }
        if (isset($v['veiculoId'])) {
            $vendaId = (string) $v['veiculoId'];
        }
        if (isset($v['id'])) {
            $vendaId = $vendaId ?? (string) $v['id'];
        }

        // Comprador/cliente (Innovart traz dados do comprador)
        $comprador = $v['comprador'] ?? $v['cliente'] ?? $v['buyer'] ?? [];
        if (is_array($comprador)) {
            $cliente['nome'] = $comprador['nome'] ?? $comprador['name'] ?? '';
            $cliente['cpf'] = $comprador['cpf'] ?? $comprador['documento'] ?? '';
            $cliente['telefone'] = $comprador['telefone'] ?? $comprador['phone'] ?? '';
            $cliente['whatsapp'] = $comprador['whatsapp'] ?? $comprador['celular'] ?? $cliente['telefone'];
            $cliente['endereco'] = is_string($comprador['endereco'] ?? null)
                ? $comprador['endereco']
                : $this->montarEndereco($comprador);
        }

        // Se comprador veio no primeiro nível
        if (empty($cliente['nome']) && isset($v['nome'])) {
            $cliente['nome'] = $v['nome'] ?? '';
            $cliente['cpf'] = $v['cpf'] ?? $cliente['cpf'];
            $cliente['telefone'] = $v['telefone'] ?? $v['celular'] ?? $cliente['telefone'];
            $cliente['whatsapp'] = $v['whatsapp'] ?? $v['celular'] ?? $cliente['whatsapp'];
            $cliente['endereco'] = $v['endereco'] ?? $cliente['endereco'];
        }

        return ['cliente' => $cliente, 'veiculo' => $veiculo, 'venda_id' => $vendaId];
    }

    private function montarEndereco(array $comprador): string
    {
        $partes = array_filter([
            $comprador['logradouro'] ?? $comprador['rua'] ?? '',
            $comprador['numero'] ?? '',
            $comprador['complemento'] ?? $comprador['complemento'] ?? '',
            $comprador['bairro'] ?? '',
            $comprador['cidade'] ?? $comprador['municipio'] ?? '',
            $comprador['uf'] ?? $comprador['estado'] ?? '',
            $comprador['cep'] ?? '',
        ]);
        return implode(', ', $partes);
    }
}
