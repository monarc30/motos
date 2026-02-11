<?php

// Carrega .env manualmente
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Ignora comentários
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Remove aspas se houver
        $value = trim($value, '"\'');
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

$token = $_ENV['AUTOCONF_TOKEN'] ?? '';
$token2 = $_ENV['AUTOCONF_TOKEN2'] ?? '';

echo "=== TESTE DETALHADO AUTOCONF ===\n\n";
echo "Token 1 (Bearer) - Primeiros 50 chars: " . substr($token, 0, 50) . "...\n";
echo "Token 1 (Bearer) - Tamanho total: " . strlen($token) . " caracteres\n\n";
echo "Token 2 (Body) - Primeiros 30 chars: " . substr($token2, 0, 30) . "...\n";
echo "Token 2 (Body) - Tamanho total: " . strlen($token2) . " caracteres\n\n";

// Testa endpoint de revenda (mais simples)
$url = "https://api.autoconf.com.br/api/v1/revenda";
$headers = [
    "Authorization: $token",  // Sem "Bearer " conforme Postman
    "Content-Type: application/x-www-form-urlencoded",
];
$data = "token=" . urlencode($token2);

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Testando: POST /api/v1/revenda\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "URL: $url\n";
echo "Headers:\n";
foreach ($headers as $header) {
    $headerDisplay = $header;
    if (strpos($header, 'Authorization') !== false) {
        $headerDisplay = "Authorization: " . substr($token, 0, 30) . "...";
    }
    echo "  $headerDisplay\n";
}
echo "Body: token=" . substr($token2, 0, 30) . "...\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Status HTTP: $httpCode\n";

if ($httpCode === 200) {
    echo "✓ SUCESSO!\n\n";
    $json = json_decode($response, true);
    if ($json) {
        echo "Resposta JSON:\n";
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "Resposta (primeiros 500 chars):\n$response\n";
    }
} else {
    echo "✗ ERRO\n";
    if ($error) {
        echo "Erro cURL: $error\n";
    }
    echo "\nResposta (primeiros 500 chars):\n";
    echo substr($response, 0, 500) . "\n";
}

