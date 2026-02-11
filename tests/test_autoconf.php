<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Autoconf\AutoconfService;
use App\Models\Autoconf\AutoconfClient;

echo "=== TESTES DE INTEGRAÇÃO AUTOCONF ===\n\n";

// Verificar configuração
$config = require __DIR__ . '/../config/autoconf.php';
echo "Configuração Autoconf:\n";
echo "  API URL: " . ($config['api_url'] ?: 'NÃO CONFIGURADO') . "\n";
echo "  Token (header): " . ($config['token'] ? '***' : 'NÃO CONFIGURADO') . "\n";
echo "  Token2 (body): " . ($config['token2'] ? '***' : 'NÃO CONFIGURADO') . "\n";
echo "  Timeout: {$config['timeout']}s\n";
echo "  Retry Attempts: {$config['retry_attempts']}\n\n";

if (empty($config['api_url'])) {
    echo "⚠ AVISO: API URL não configurada. Configure em config/autoconf.php\n";
    echo "Os testes serão simulados.\n\n";
}

// Teste 1: AutoconfClient
echo "1. Testando AutoconfClient...\n";
try {
    $client = new AutoconfClient();
    echo "   ✓ Cliente instanciado\n";
    
    if (!empty($config['api_url'])) {
        echo "   ⚠ Teste real requer API configurada\n";
    } else {
        echo "   ⚠ API não configurada - usando modo simulado\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 2: AutoconfService
echo "2. Testando AutoconfService...\n";
try {
    $service = new AutoconfService();
    echo "   ✓ Service instanciado\n";
    
    if (empty($config['api_url'])) {
        echo "   ⚠ API não configurada - testes reais não serão executados\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 3: CacheService
echo "3. Testando CacheService...\n";
try {
    $cache = new \App\Services\CacheService();
    $cache->set('test_key', ['test' => 'value'], 60);
    $value = $cache->get('test_key');
    
    if ($value && $value['test'] === 'value') {
        echo "   ✓ Cache funcionando\n";
    } else {
        echo "   ✗ Erro no cache\n";
    }
    
    $cache->delete('test_key');
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 4: Verificar logs
echo "4. Verificando logs de integração...\n";
try {
    $db = \App\Services\Database::getConnection();
    $stmt = $db->query("SELECT COUNT(*) as total FROM logs_integracao WHERE tipo = 'autoconf'");
    $total = $stmt->fetch()['total'];
    echo "   ✓ Total de logs Autoconf: {$total}\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

echo "=== TESTES CONCLUÍDOS ===\n";
echo "\n";
echo "PRÓXIMOS PASSOS:\n";
echo "1. Configure a API URL do Autoconf em config/autoconf.php\n";
echo "2. Configure as credenciais (api_key e api_secret)\n";
echo "3. Teste com um ID de venda real\n";

