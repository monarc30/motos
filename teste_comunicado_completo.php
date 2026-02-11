<?php

require_once __DIR__ . '/vendor/autoload.php';

// Carrega .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim(trim($value), '"\'');
    }
}

use App\Services\EnvService;
use App\Services\Database;

EnvService::load();

echo "=== TESTE COMPLETO - COMUNICADO DE VENDA ===\n\n";

// 1. Buscar dados do Autoconf
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ETAPA 1: Buscar dados do Autoconf\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$veiculoId = '887438';
$token = $_ENV['AUTOCONF_TOKEN'] ?? '';
$token2 = $_ENV['AUTOCONF_TOKEN2'] ?? '';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.autoconf.com.br/api/v1/veiculo');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['token' => $token2, 'id' => $veiculoId]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: $token", 'Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code === 200) {
    $veiculoData = json_decode($response, true);
    echo "✓ Dados do veículo encontrados!\n";
    echo "  ID: " . ($veiculoData['id'] ?? 'N/A') . "\n";
    echo "  Modelo: " . ($veiculoData['modelo_nome'] ?? 'N/A') . "\n";
    echo "  Placa: " . ($veiculoData['placa_completa'] ?? 'N/A') . "\n";
    echo "  Ano: " . ($veiculoData['anofabricacao'] ?? 'N/A') . "\n\n";
    
    $modelo = $veiculoData['modelo_nome'] ?? '';
    $placa = $veiculoData['placa_completa'] ?? '';
    $ano = $veiculoData['anofabricacao'] ?? '';
} else {
    echo "✗ Erro ao buscar veículo: $code\n";
    exit(1);
}

// 2. Verificar se cliente existe no banco
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ETAPA 2: Verificar cliente no banco próprio\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

try {
    $db = Database::getConnection();
    
    // Busca cliente por veículo (através de intenções ou comunicados anteriores)
    $stmt = $db->prepare("
        SELECT c.* 
        FROM clientes c
        INNER JOIN intencoes_venda iv ON iv.cliente_id = c.id
        INNER JOIN veiculos v ON v.id = iv.veiculo_id
        WHERE v.placa = :placa
        ORDER BY iv.created_at DESC
        LIMIT 1
    ");
    $stmt->execute(['placa' => $placa]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        echo "✓ Cliente encontrado no banco!\n";
        echo "  Nome: " . $cliente['nome'] . "\n";
        echo "  CPF: " . $cliente['cpf'] . "\n";
        $nomeCliente = $cliente['nome'];
        $cpfCliente = $cliente['cpf'];
    } else {
        echo "⚠ Cliente não encontrado no banco\n";
        echo "  Será necessário preencher manualmente\n";
        $nomeCliente = 'Cliente Teste';
        $cpfCliente = '123.456.789-00';
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Erro ao buscar cliente: " . $e->getMessage() . "\n";
    $nomeCliente = 'Cliente Teste';
    $cpfCliente = '123.456.789-00';
}

// 3. Testar geração via API do sistema
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ETAPA 3: Gerar Comunicado de Venda\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$dataComunicado = date('Y-m-d');
$postData = http_build_query([
    'nome_cliente' => $nomeCliente,
    'cpf_cliente' => $cpfCliente,
    'placa_moto' => $placa,
    'modelo_moto' => $modelo,
    'data_comunicado' => $dataComunicado,
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://motos/comunicado-venda/gerar');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$resultado = json_decode($response, true);

if ($code === 200 && isset($resultado['sucesso']) && $resultado['sucesso']) {
    echo "✓ Comunicado gerado com sucesso!\n";
    echo "  ID: " . ($resultado['id'] ?? 'N/A') . "\n";
    echo "  Arquivo: " . ($resultado['arquivo'] ?? 'N/A') . "\n";
    echo "  Caminho: " . ($resultado['caminho'] ?? 'N/A') . "\n";
    
    if (isset($resultado['comven'])) {
        if ($resultado['comven']['registrado']) {
            echo "  COMven: Registrado (Protocolo: " . ($resultado['comven']['protocolo'] ?? 'N/A') . ")\n";
        } else {
            echo "  COMven: " . ($resultado['comven']['mensagem'] ?? 'Não registrado') . "\n";
        }
    }
    
    // Verifica se arquivo existe
    if (isset($resultado['caminho']) && file_exists($resultado['caminho'])) {
        echo "  ✓ Arquivo PDF existe no servidor\n";
        echo "  Tamanho: " . filesize($resultado['caminho']) . " bytes\n";
    }
    
    echo "\n";
    
    // 4. Testar geração de etiqueta
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "ETAPA 4: Gerar Etiqueta do Envelope (Opcional)\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $postDataEtiqueta = http_build_query([
        'nome_cliente' => $nomeCliente,
        'cpf_cliente' => $cpfCliente,
        'placa_moto' => $placa,
        'modelo_moto' => $modelo,
        'data_comunicado' => $dataComunicado,
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://motos/comunicado-venda/gerarEtiqueta');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataEtiqueta);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $responseEtiqueta = curl_exec($ch);
    $codeEtiqueta = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $resultadoEtiqueta = json_decode($responseEtiqueta, true);
    
    if ($codeEtiqueta === 200 && isset($resultadoEtiqueta['sucesso']) && $resultadoEtiqueta['sucesso']) {
        echo "✓ Etiqueta gerada com sucesso!\n";
        echo "  Arquivo: " . ($resultadoEtiqueta['arquivo'] ?? 'N/A') . "\n";
        echo "  Caminho: " . ($resultadoEtiqueta['caminho'] ?? 'N/A') . "\n";
        
        if (isset($resultadoEtiqueta['caminho']) && file_exists($resultadoEtiqueta['caminho'])) {
            echo "  ✓ Arquivo PDF existe no servidor\n";
            echo "  Tamanho: " . filesize($resultadoEtiqueta['caminho']) . " bytes\n";
        }
    } else {
        echo "⚠ Etiqueta não gerada (pode precisar de cliente/veículo no banco)\n";
        echo "  Resposta: " . substr($responseEtiqueta, 0, 200) . "\n";
    }
    
} else {
    echo "✗ Erro ao gerar comunicado\n";
    echo "  Status: $code\n";
    echo "  Resposta: " . substr($response, 0, 500) . "\n";
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "TESTE CONCLUÍDO\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

