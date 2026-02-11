<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Database;
use App\Models\Entities\Cliente;
use App\Models\Entities\Veiculo;
use App\Models\Entities\IntencaoVenda;
use App\Models\Entities\ComunicadoVenda;
use App\Models\Repositories\ClienteRepository;
use App\Models\Repositories\VeiculoRepository;
use App\Models\Repositories\IntencaoVendaRepository;
use App\Models\Repositories\ComunicadoVendaRepository;

echo "=== TESTES DO BANCO DE DADOS ===\n\n";

// Teste 1: Conexão
echo "1. Testando conexão com banco de dados...\n";
try {
    $db = Database::getConnection();
    echo "   ✓ Conexão estabelecida\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n";
    exit(1);
}

// Teste 2: ClienteRepository
echo "2. Testando ClienteRepository...\n";
try {
    $clienteRepo = new ClienteRepository();
    
    // Criar cliente
    $cliente = new Cliente();
    $cliente->setNome("João Silva");
    $cliente->setCpf("123.456.789-00");
    $cliente->setTelefone("(11) 98765-4321");
    $cliente->setWhatsapp("(11) 98765-4321");
    $cliente->setEndereco("Rua Teste, 123 - São Paulo/SP");
    
    $clienteId = $clienteRepo->create($cliente);
    echo "   ✓ Cliente criado com ID: {$clienteId}\n";
    
    // Buscar cliente
    $clienteEncontrado = $clienteRepo->findById($clienteId);
    if ($clienteEncontrado && $clienteEncontrado->getNome() === "João Silva") {
        echo "   ✓ Cliente encontrado corretamente\n";
    } else {
        echo "   ✗ Erro ao buscar cliente\n";
    }
    
    // Teste findOrCreate
    $cliente2 = new Cliente();
    $cliente2->setNome("Maria Santos");
    $cliente2->setCpf("987.654.321-00");
    $clienteExistente = $clienteRepo->findOrCreate($cliente2);
    echo "   ✓ FindOrCreate funcionando\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 3: VeiculoRepository
echo "3. Testando VeiculoRepository...\n";
try {
    $veiculoRepo = new VeiculoRepository();
    
    // Criar veículo
    $veiculo = new Veiculo();
    $veiculo->setPlaca("ABC1234");
    $veiculo->setModelo("Honda CB 600");
    $veiculo->setAno(2020);
    
    $veiculoId = $veiculoRepo->create($veiculo);
    echo "   ✓ Veículo criado com ID: {$veiculoId}\n";
    
    // Buscar veículo
    $veiculoEncontrado = $veiculoRepo->findByPlaca("ABC1234");
    if ($veiculoEncontrado && $veiculoEncontrado->getModelo() === "Honda CB 600") {
        echo "   ✓ Veículo encontrado corretamente\n";
    } else {
        echo "   ✗ Erro ao buscar veículo\n";
    }
    
    // Teste findOrCreate
    $veiculo2 = new Veiculo();
    $veiculo2->setPlaca("XYZ9876");
    $veiculo2->setModelo("Yamaha Fazer 250");
    $veiculoExistente = $veiculoRepo->findOrCreate($veiculo2);
    echo "   ✓ FindOrCreate funcionando\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 4: IntencaoVendaRepository
echo "4. Testando IntencaoVendaRepository...\n";
try {
    $intencaoRepo = new IntencaoVendaRepository();
    $clienteRepo = new ClienteRepository();
    $veiculoRepo = new VeiculoRepository();
    
    // Buscar cliente e veículo existentes
    $cliente = $clienteRepo->findById(1);
    $veiculo = $veiculoRepo->findById(1);
    
    if ($cliente && $veiculo) {
        $intencao = new IntencaoVenda();
        $intencao->setClienteId($cliente->getId());
        $intencao->setVeiculoId($veiculo->getId());
        $intencao->setNumeroCrv("123456");
        $intencao->setCodigoSegurancaCrv("ABC123");
        $intencao->setStatus("rascunho");
        
        $intencaoId = $intencaoRepo->create($intencao);
        echo "   ✓ Intenção de venda criada com ID: {$intencaoId}\n";
        
        // Buscar intenção
        $intencaoEncontrada = $intencaoRepo->findById($intencaoId);
        if ($intencaoEncontrada && $intencaoEncontrada->getNumeroCrv() === "123456") {
            echo "   ✓ Intenção encontrada corretamente\n";
        } else {
            echo "   ✗ Erro ao buscar intenção\n";
        }
    } else {
        echo "   ⚠ Cliente ou veículo não encontrado para teste\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 5: ComunicadoVendaRepository
echo "5. Testando ComunicadoVendaRepository...\n";
try {
    $comunicadoRepo = new ComunicadoVendaRepository();
    $clienteRepo = new ClienteRepository();
    $veiculoRepo = new VeiculoRepository();
    
    // Buscar cliente e veículo existentes
    $cliente = $clienteRepo->findById(1);
    $veiculo = $veiculoRepo->findById(1);
    
    if ($cliente && $veiculo) {
        $comunicado = new ComunicadoVenda();
        $comunicado->setClienteId($cliente->getId());
        $comunicado->setVeiculoId($veiculo->getId());
        $comunicado->setDataComunicado("2025-01-14");
        
        $comunicadoId = $comunicadoRepo->create($comunicado);
        echo "   ✓ Comunicado de venda criado com ID: {$comunicadoId}\n";
        
        // Buscar comunicado
        $comunicadoEncontrado = $comunicadoRepo->findById($comunicadoId);
        if ($comunicadoEncontrado && $comunicadoEncontrado->getDataComunicado() === "2025-01-14") {
            echo "   ✓ Comunicado encontrado corretamente\n";
        } else {
            echo "   ✗ Erro ao buscar comunicado\n";
        }
    } else {
        echo "   ⚠ Cliente ou veículo não encontrado para teste\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 6: Verificar dados no banco
echo "6. Verificando dados no banco...\n";
try {
    $db = Database::getConnection();
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM clientes");
    $totalClientes = $stmt->fetch()['total'];
    echo "   ✓ Total de clientes: {$totalClientes}\n";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM veiculos");
    $totalVeiculos = $stmt->fetch()['total'];
    echo "   ✓ Total de veículos: {$totalVeiculos}\n";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM intencoes_venda");
    $totalIntencoes = $stmt->fetch()['total'];
    echo "   ✓ Total de intenções: {$totalIntencoes}\n";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM comunicados_venda");
    $totalComunicados = $stmt->fetch()['total'];
    echo "   ✓ Total de comunicados: {$totalComunicados}\n\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

echo "=== TESTES CONCLUÍDOS ===\n";


