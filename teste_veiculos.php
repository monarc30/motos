<?php

// Carrega .env manualmente
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, '"\'');
        $_ENV[$key] = $value;
    }
}

$token = $_ENV['AUTOCONF_TOKEN'] ?? '';
$token2 = $_ENV['AUTOCONF_TOKEN2'] ?? '';

echo "=== TESTE ENDPOINT VEÍCULOS ===\n\n";

// Testa endpoint de veículos (lista)
$url = "https://api.autoconf.com.br/api/v1/veiculos";
$headers = [
    "Authorization: $token",  // Sem "Bearer "
    "Content-Type: application/x-www-form-urlencoded",
];
$data = http_build_query([
    'token' => $token2,
    'tipo' => 'motos',
    'pagina' => '1',
    'registros_por_pagina' => '10',
]);

echo "URL: $url\n";
echo "Body: $data\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status HTTP: $httpCode\n\n";

if ($httpCode === 200) {
    echo "✓ SUCESSO!\n\n";
    $json = json_decode($response, true);
    if ($json && isset($json['veiculos'])) {
        echo "Total de veículos: {$json['count']}\n";
        echo "Página atual: {$json['pagina_atual']}\n";
        echo "Última página: {$json['ultima_pagina']}\n";
        echo "Veículos retornados: " . count($json['veiculos']) . "\n\n";
        
        if (count($json['veiculos']) > 0) {
            echo "Primeiro veículo:\n";
            $primeiro = $json['veiculos'][0];
            echo "  ID: " . ($primeiro['id'] ?? 'N/A') . "\n";
            echo "  Modelo: " . ($primeiro['modelo_nome'] ?? 'N/A') . "\n";
        }
    }
} else {
    echo "✗ ERRO\n";
    echo substr($response, 0, 500) . "\n";
}

