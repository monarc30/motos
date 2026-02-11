<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Services/EnvService.php';

use App\Services\EnvService;
use GuzzleHttp\Client;

EnvService::load();

$token = $_ENV['AUTOCONF_TOKEN'] ?? '';
$token2 = $_ENV['AUTOCONF_TOKEN2'] ?? '';
$apiUrl = $_ENV['AUTOCONF_API_URL'] ?? 'https://api.autoconf.com.br';

echo "=== TESTE DE ENDPOINTS AUTOCONF ===\n\n";
echo "Token 1 (Bearer): " . substr($token, 0, 20) . "...\n";
echo "Token 2 (Body): " . substr($token2, 0, 20) . "...\n";
echo "API URL: $apiUrl\n\n";

$client = new Client([
    'base_uri' => $apiUrl,
    'timeout' => 30,
]);

// Endpoints para testar
$endpoints = [
    [
        'nome' => 'Revenda (POST /api/v1/revenda)',
        'method' => 'POST',
        'url' => '/api/v1/revenda',
        'headers' => ['Authorization' => 'Bearer ' . $token],
        'body' => ['token' => $token2],
    ],
    [
        'nome' => 'Lista de Veículos (POST /api/v1/veiculos)',
        'method' => 'POST',
        'url' => '/api/v1/veiculos',
        'headers' => ['Authorization' => 'Bearer ' . $token],
        'body' => [
            'token' => $token2,
            'tipo' => 'motos',
            'pagina' => '1',
            'registros_por_pagina' => '10',
        ],
    ],
    [
        'nome' => 'Veículo Específico (POST /api/v1/veiculo)',
        'method' => 'POST',
        'url' => '/api/v1/veiculo',
        'headers' => ['Authorization' => 'Bearer ' . $token],
        'body' => [
            'token' => $token2,
            'id' => '892161',
        ],
    ],
    [
        'nome' => 'Veículo Específico - Alternativa (POST /api/v1/veiculos/{id})',
        'method' => 'POST',
        'url' => '/api/v1/veiculos/892161',
        'headers' => ['Authorization' => 'Bearer ' . $token],
        'body' => ['token' => $token2],
    ],
    [
        'nome' => 'Veículo Específico - GET (GET /api/v1/veiculo/{id})',
        'method' => 'GET',
        'url' => '/api/v1/veiculo/892161',
        'headers' => ['Authorization' => 'Bearer ' . $token],
        'query' => ['token' => $token2],
    ],
];

$resultados = [];

foreach ($endpoints as $endpoint) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Testando: {$endpoint['nome']}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    try {
        $options = [
            'headers' => $endpoint['headers'],
        ];
        
        if ($endpoint['method'] === 'POST') {
            $options['form_params'] = $endpoint['body'];
        } else {
            $options['query'] = $endpoint['query'] ?? [];
        }
        
        $response = $client->request($endpoint['method'], $endpoint['url'], $options);
        
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        
        echo "✓ Status: $statusCode\n";
        
        if ($statusCode === 200) {
            echo "✓ SUCESSO!\n";
            echo "Tamanho da resposta: " . strlen($body) . " bytes\n";
            
            if ($data) {
                echo "Formato: JSON\n";
                if (isset($data['veiculos'])) {
                    echo "Tipo: Lista de veículos (" . count($data['veiculos']) . " veículos)\n";
                } elseif (isset($data['id'])) {
                    echo "Tipo: Veículo específico (ID: {$data['id']})\n";
                } else {
                    echo "Tipo: " . print_r(array_keys($data), true) . "\n";
                }
                
                // Mostra preview dos dados
                if (isset($data['veiculos']) && count($data['veiculos']) > 0) {
                    $primeiro = $data['veiculos'][0];
                    echo "\nPreview do primeiro veículo:\n";
                    echo "  ID: " . ($primeiro['id'] ?? 'N/A') . "\n";
                    echo "  Modelo: " . ($primeiro['modelo_nome'] ?? 'N/A') . "\n";
                }
            }
            
            $resultados[] = [
                'endpoint' => $endpoint['nome'],
                'status' => 'SUCESSO',
                'code' => $statusCode,
            ];
        } else {
            echo "⚠ Status inesperado: $statusCode\n";
            $resultados[] = [
                'endpoint' => $endpoint['nome'],
                'status' => "Status $statusCode",
                'code' => $statusCode,
            ];
        }
        
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $statusCode = $e->getResponse()->getStatusCode();
        $body = $e->getResponse()->getBody()->getContents();
        
        echo "✗ ERRO: $statusCode\n";
        
        // Tenta decodificar como JSON primeiro
        $errorData = json_decode($body, true);
        if ($errorData) {
            echo "Resposta JSON:\n";
            echo json_encode($errorData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "Resposta HTML/texto (primeiros 500 caracteres):\n";
            echo substr($body, 0, 500) . "\n";
        }
        
        $resultados[] = [
            'endpoint' => $endpoint['nome'],
            'status' => "ERRO $statusCode",
            'code' => $statusCode,
        ];
        
    } catch (\Exception $e) {
        echo "✗ ERRO: " . $e->getMessage() . "\n";
        
        $resultados[] = [
            'endpoint' => $endpoint['nome'],
            'status' => 'ERRO',
            'code' => 0,
        ];
    }
    
    echo "\n";
}

// Resumo
echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "RESUMO DOS TESTES\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
foreach ($resultados as $resultado) {
    $statusIcon = $resultado['code'] === 200 ? '✓' : '✗';
    echo "$statusIcon {$resultado['endpoint']}: {$resultado['status']}\n";
}

