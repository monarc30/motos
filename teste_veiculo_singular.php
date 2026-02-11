<?php

// Carrega .env
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
$veiculoId = '892161'; // ID para testar

echo "=== TESTE ENDPOINT VEÍCULO SINGULAR ===\n\n";
echo "Testando ID: $veiculoId\n\n";

// Testa endpoint de veículo singular (para buscar um veículo específico)
$url = "https://api.autoconf.com.br/api/v1/veiculo";
$headers = [
    "Authorization: $token",  // Sem "Bearer "
    "Content-Type: application/x-www-form-urlencoded",
];

// Testa diferentes formatos de parâmetros
$testes = [
    [
        'nome' => 'Formato 1: token + id no body',
        'data' => http_build_query([
            'token' => $token2,
            'id' => $veiculoId,
        ]),
    ],
    [
        'nome' => 'Formato 2: apenas token e id',
        'data' => "token=" . urlencode($token2) . "&id=" . urlencode($veiculoId),
    ],
];

foreach ($testes as $teste) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Testando: {$teste['nome']}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $teste['data']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status HTTP: $httpCode\n";
    
    if ($httpCode === 200) {
        echo "✓ SUCESSO!\n\n";
        $json = json_decode($response, true);
        if ($json) {
            echo "Dados retornados:\n";
            echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            break; // Se funcionou, para aqui
        }
    } else {
        echo "✗ ERRO\n";
        echo "Resposta: " . substr($response, 0, 300) . "\n\n";
    }
}

