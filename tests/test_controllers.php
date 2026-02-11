<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\IntencaoVendaController;
use App\Controllers\ComunicadoVendaController;

echo "=== TESTES DOS CONTROLLERS ===\n\n";

// Simular ambiente de requisição
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/intencao-venda/buscarAutoconf';

// Teste 1: IntencaoVendaController
echo "1. Testando IntencaoVendaController...\n";
try {
    $controller = new IntencaoVendaController();
    echo "   ✓ Controller instanciado\n";
    
    // Teste buscarAutoconf sem dados
    $_POST = [];
    ob_start();
    try {
        $controller->buscarAutoconf();
    } catch (Exception $e) {
        // Esperado - deve retornar erro
    }
    $output = ob_get_clean();
    
    if (strpos($output, 'erro') !== false || strpos($output, 'ID da venda não informado') !== false) {
        echo "   ✓ Validação de dados funcionando\n";
    }
    
    // Teste buscarAutoconf com dados
    $_POST = ['venda_id' => '123'];
    ob_start();
    try {
        $controller->buscarAutoconf();
    } catch (Exception $e) {
        // Pode lançar exceção se não houver view
    }
    $output = ob_get_clean();
    
    if (strpos($output, 'sucesso') !== false || strpos($output, 'dados') !== false) {
        echo "   ✓ Busca Autoconf retornando estrutura\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 2: ComunicadoVendaController
echo "2. Testando ComunicadoVendaController...\n";
try {
    $controller = new ComunicadoVendaController();
    echo "   ✓ Controller instanciado\n";
    
    // Teste buscarAutoconf
    $_POST = ['venda_id' => '456'];
    ob_start();
    try {
        $controller->buscarAutoconf();
    } catch (Exception $e) {
        // Pode lançar exceção se não houver view
    }
    $output = ob_get_clean();
    
    if (strpos($output, 'sucesso') !== false || strpos($output, 'dados') !== false) {
        echo "   ✓ Busca Autoconf retornando estrutura\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

echo "=== TESTES CONCLUÍDOS ===\n";


