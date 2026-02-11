<?php
/**
 * Script para testar e validar credenciais do Autoconf
 */

require __DIR__ . '/vendor/autoload.php';

\App\Services\EnvService::load();

$config = require __DIR__ . '/config/autoconf.php';

echo "=== TESTE DE CREDENCIAIS AUTOCONF ===\n\n";

echo "1. Verificando configuração:\n";
echo "   API URL: " . $config['api_url'] . "\n";
echo "   Token Header (Bearer): " . (empty($config['token']) ? 'VAZIO' : substr($config['token'], 0, 30) . '... (tamanho: ' . strlen($config['token']) . ')') . "\n";
echo "   Token Body (revenda): " . (empty($config['token2']) ? 'VAZIO' : $config['token2'] . ' (tamanho: ' . strlen($config['token2']) . ')') . "\n\n";

echo "2. Testando endpoint /api/v1/revenda (endpoint mais simples):\n";
try {
    $client = new \App\Models\Autoconf\AutoconfClient();
    $resultado = $client->buscarRevenda();
    echo "   ✓ SUCESSO! Credenciais válidas.\n";
    echo "   Resposta: " . json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
} catch (Exception $e) {
    $mensagem = $e->getMessage();
    echo "   ✗ ERRO: " . substr($mensagem, 0, 200) . "\n";
    
    if (strpos($mensagem, '403') !== false) {
        echo "   → Erro 403: Credenciais podem estar incorretas ou sem permissão.\n";
    } elseif (strpos($mensagem, '401') !== false) {
        echo "   → Erro 401: Token Bearer inválido ou expirado.\n";
    } elseif (strpos($mensagem, '404') !== false) {
        echo "   → Erro 404: Endpoint não encontrado.\n";
    }
    echo "\n";
}

echo "3. Testando endpoint /api/v1/veiculo com ID de teste:\n";
try {
    $client = new \App\Models\Autoconf\AutoconfClient();
    $resultado = $client->buscarVenda('295110');
    echo "   ✓ SUCESSO! Dados obtidos.\n";
    echo "   Cliente: " . ($resultado['cliente']['nome'] ?? 'N/A') . "\n";
    echo "   Veículo: " . ($resultado['veiculo']['modelo'] ?? 'N/A') . "\n\n";
} catch (Exception $e) {
    $mensagem = $e->getMessage();
    echo "   ✗ ERRO: " . substr($mensagem, 0, 200) . "\n";
    
    if (strpos($mensagem, '403') !== false) {
        echo "   → Erro 403: Pode ser credenciais ou ID do veículo não existe/sem permissão.\n";
    }
    echo "\n";
}

echo "=== CONCLUSÃO ===\n";
echo "Se ambos os testes retornaram 403, o problema está nas credenciais.\n";
echo "Verifique:\n";
echo "  - Se os tokens estão corretos no arquivo .env\n";
echo "  - Se os tokens não expiraram\n";
echo "  - Se você tem permissão para acessar a API\n";
echo "  - Se o token Bearer e o token revenda estão corretos\n";


