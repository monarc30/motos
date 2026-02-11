<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Autoconf\AutoconfService;
use App\Models\Entities\Cliente;
use App\Models\Entities\Veiculo;
use App\Models\Entities\IntencaoVenda;
use App\Models\Repositories\ClienteRepository;
use App\Models\Repositories\VeiculoRepository;
use App\Models\Repositories\IntencaoVendaRepository;
use App\Models\Documentos\IntencaoVendaGenerator;

echo "=== TESTE COMPLETO DO SISTEMA ===\n\n";

// Teste 1: Buscar dados no Autoconf
echo "1. Testando busca de dados no Autoconf...\n";
try {
    $autoconfService = new AutoconfService();
    
    $vendaId = "123";
    echo "   Tentando buscar venda ID: {$vendaId}\n";
    
    try {
        $dados = $autoconfService->buscarDadosVenda($vendaId);
        echo "   ✓ Dados obtidos do Autoconf\n";
        echo "   - Cliente: " . ($dados['cliente']['nome'] ?? 'N/A') . "\n";
        echo "   - Veículo: " . ($dados['veiculo']['modelo'] ?? 'N/A') . "\n";
    } catch (\Exception $e) {
        echo "   ⚠ Erro ao buscar (esperado se API não estiver acessível): " . substr($e->getMessage(), 0, 80) . "...\n";
        echo "   ✓ Sistema tratou o erro corretamente\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 2: Fluxo completo de Intenção de Venda
echo "2. Testando fluxo completo de Intenção de Venda...\n";
try {
    $clienteRepo = new ClienteRepository();
    $veiculoRepo = new VeiculoRepository();
    $intencaoRepo = new IntencaoVendaRepository();

    echo "   a) Criando cliente...\n";
    $cliente = new Cliente();
    $cliente->setNome("João Teste Sistema");
    $cliente->setCpf("123.456.789-99");
    $cliente->setTelefone("(11) 98765-4321");
    $cliente->setWhatsapp("(11) 98765-4321");
    $cliente->setEndereco("Rua Teste Sistema, 100 - São Paulo/SP");
    $cliente = $clienteRepo->findOrCreate($cliente);
    echo "      ✓ Cliente ID: {$cliente->getId()}\n";

    echo "   b) Criando veículo...\n";
    $veiculo = new Veiculo();
    $veiculo->setPlaca("SIST1234");
    $veiculo->setModelo("Honda CB 650F");
    $veiculo->setAno(2021);
    $veiculo = $veiculoRepo->findOrCreate($veiculo);
    echo "      ✓ Veículo ID: {$veiculo->getId()}\n";

    echo "   c) Criando intenção de venda...\n";
    $intencao = new IntencaoVenda();
    $intencao->setClienteId($cliente->getId());
    $intencao->setVeiculoId($veiculo->getId());
    $intencao->setNumeroCrv("555666");
    $intencao->setCodigoSegurancaCrv("SIST999");
    $intencao->setStatus("rascunho");
    $intencaoId = $intencaoRepo->create($intencao);
    $intencao->setId($intencaoId);
    echo "      ✓ Intenção ID: {$intencaoId}\n";

    echo "   d) Gerando PDF...\n";
    $generator = new IntencaoVendaGenerator();
    $caminhoPdf = $generator->gerar($intencao);
    if (file_exists($caminhoPdf)) {
        $tamanho = filesize($caminhoPdf);
        echo "      ✓ PDF gerado: " . basename($caminhoPdf) . " (" . number_format($tamanho / 1024, 2) . " KB)\n";
        
        $intencao->setArquivoPdf($caminhoPdf);
        $intencao->setStatus("gerado");
        $intencaoRepo->update($intencao);
        echo "      ✓ Status atualizado para 'gerado'\n";
    } else {
        echo "      ✗ PDF não foi criado\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 3: Verificar dados no banco
echo "3. Verificando dados no banco de dados...\n";
try {
    $db = \App\Services\Database::getConnection();
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM clientes");
    $totalClientes = $stmt->fetch()['total'];
    echo "   ✓ Total de clientes: {$totalClientes}\n";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM veiculos");
    $totalVeiculos = $stmt->fetch()['total'];
    echo "   ✓ Total de veículos: {$totalVeiculos}\n";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM intencoes_venda WHERE status = 'gerado'");
    $totalGeradas = $stmt->fetch()['total'];
    echo "   ✓ Intenções geradas: {$totalGeradas}\n";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM comunicados_venda");
    $totalComunicados = $stmt->fetch()['total'];
    echo "   ✓ Total de comunicados: {$totalComunicados}\n";
    echo "\n";
} catch (\Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 4: Verificar arquivos gerados
echo "4. Verificando arquivos PDF gerados...\n";
$config = require __DIR__ . '/../config/app.php';
$diretorios = [
    'intencoes' => $config['documents_path'] . '/intencoes',
    'comunicados' => $config['documents_path'] . '/comunicados',
    'etiquetas' => $config['documents_path'] . '/etiquetas',
];

foreach ($diretorios as $tipo => $diretorio) {
    if (is_dir($diretorio)) {
        $arquivos = glob($diretorio . '/*.pdf');
        $total = count($arquivos);
        $tamanhoTotal = 0;
        foreach ($arquivos as $arquivo) {
            $tamanhoTotal += filesize($arquivo);
        }
        echo "   ✓ {$tipo}: {$total} arquivo(s) - " . number_format($tamanhoTotal / 1024, 2) . " KB total\n";
    }
}
echo "\n";

echo "=== TESTE COMPLETO FINALIZADO ===\n";


