<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Comven\ComvenService;
use App\Models\Comven\ComvenClient;

echo "=== TESTES DE INTEGRAÇÃO COMVEN ===\n\n";

$config = require __DIR__ . '/../config/comven.php';
echo "Configuração COMven:\n";
echo "  API URL: " . ($config['api_url'] ?: 'NÃO CONFIGURADO') . "\n";
echo "  API Key: " . ($config['api_key'] ? '***' : 'NÃO CONFIGURADO') . "\n";
echo "  Enabled: " . ($config['enabled'] ? 'SIM' : 'NÃO') . "\n";
echo "  Timeout: {$config['timeout']}s\n";
echo "  Retry Attempts: {$config['retry_attempts']}\n\n";

if (!$config['enabled']) {
    echo "⚠ AVISO: COMven está desabilitado.\n";
    echo "Para habilitar, configure COMVEN_ENABLED=true no arquivo .env\n\n";
}

echo "1. Testando ComvenService...\n";
try {
    $service = new ComvenService();
    echo "   ✓ Service instanciado\n";
    echo "   ✓ Habilitado: " . ($service->isEnabled() ? 'SIM' : 'NÃO') . "\n";
    
    if (!$service->isEnabled()) {
        echo "   ⚠ COMven desabilitado - testes reais não serão executados\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

if ($config['enabled'] && !empty($config['api_url'])) {
    echo "2. Testando ComvenClient...\n";
    try {
        $client = new ComvenClient();
        echo "   ✓ Cliente instanciado\n";
        echo "   ⚠ Teste real requer API configurada e funcionando\n";
        echo "\n";
    } catch (Exception $e) {
        echo "   ✗ Erro: " . $e->getMessage() . "\n";
        echo "   ⚠ Verifique a configuração da API\n\n";
    }
} else {
    echo "2. ComvenClient não testado (API não configurada)\n\n";
}

echo "3. Verificando logs de integração COMven...\n";
try {
    $db = \App\Services\Database::getConnection();
    $stmt = $db->query("SELECT COUNT(*) as total FROM logs_integracao WHERE tipo = 'comven'");
    $total = $stmt->fetch()['total'];
    echo "   ✓ Total de logs COMven: {$total}\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

echo "=== TESTES CONCLUÍDOS ===\n";
echo "\n";
echo "PRÓXIMOS PASSOS:\n";
echo "1. Configure a API URL do COMven em .env: COMVEN_API_URL=...\n";
echo "2. Configure as credenciais: COMVEN_API_KEY=... e COMVEN_API_SECRET=...\n";
echo "3. Habilite o COMven: COMVEN_ENABLED=true\n";
echo "4. Teste com um comunicado de venda real\n";


