<?php

namespace App\Models\Comven;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ComvenClient
{
    private Client $httpClient;
    private array $config;
    private int $retryAttempts;

    public function __construct()
    {
        $configPath = dirname(__DIR__, 3) . '/config/comven.php';
        $this->config = require $configPath;
        $this->retryAttempts = $this->config['retry_attempts'] ?? 3;

        if (empty($this->config['api_url'])) {
            throw new \RuntimeException('API URL do COMven não configurada');
        }

        $this->httpClient = new Client([
            'base_uri' => $this->config['api_url'],
            'timeout' => $this->config['timeout'] ?? 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function registrarComunicadoVenda(array $dados): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = $this->httpClient->post('/comunicado-venda', [
                    'headers' => $this->getAuthHeaders(),
                    'json' => $dados,
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                    return $data;
                }

                throw new \RuntimeException('Resposta inválida da API COMven');

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
            "Erro ao registrar comunicado de venda no COMven após {$this->retryAttempts} tentativas: " . 
            ($lastException ? $lastException->getMessage() : 'Erro desconhecido')
        );
    }

    public function consultarStatus(string $protocolo): array
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = $this->httpClient->get("/comunicado-venda/{$protocolo}", [
                    'headers' => $this->getAuthHeaders(),
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if ($response->getStatusCode() === 200) {
                    return $data;
                }

                throw new \RuntimeException('Resposta inválida da API COMven');

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
            "Erro ao consultar status no COMven após {$this->retryAttempts} tentativas: " . 
            ($lastException ? $lastException->getMessage() : 'Erro desconhecido')
        );
    }

    private function getAuthHeaders(): array
    {
        $headers = [];

        if (!empty($this->config['api_key'])) {
            $headers['X-API-Key'] = $this->config['api_key'];
        }

        if (!empty($this->config['api_secret'])) {
            $headers['X-API-Secret'] = $this->config['api_secret'];
        }

        if (!empty($this->config['api_key']) && !empty($this->config['api_secret'])) {
            $headers['Authorization'] = 'Basic ' . base64_encode(
                $this->config['api_key'] . ':' . $this->config['api_secret']
            );
        }

        return $headers;
    }
}


