<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Database;
use App\Models\Entities\Cliente;
use App\Models\Entities\Veiculo;
use App\Models\Entities\IntencaoVenda;
use App\Models\Repositories\ClienteRepository;
use App\Models\Repositories\VeiculoRepository;
use App\Models\Repositories\IntencaoVendaRepository;

echo "=== TESTE DE INTEGRAÇÃO COMPLETA ===\n\n";

echo "Simulando fluxo completo de Intenção de Venda...\n\n";

try {
    // 1. Criar/Buscar Cliente
    echo "1. Criando cliente...\n";
    $clienteRepo = new ClienteRepository();
    $cliente = new Cliente();
    $cliente->setNome("Pedro Teste");
    $cliente->setCpf("111.222.333-44");
    $cliente->setTelefone("(11) 99999-8888");
    $cliente->setWhatsapp("(11) 99999-8888");
    $cliente->setEndereco("Av. Teste, 456 - São Paulo/SP");
    
    $cliente = $clienteRepo->findOrCreate($cliente);
    echo "   ✓ Cliente ID: {$cliente->getId()}\n";
    echo "   ✓ Nome: {$cliente->getNome()}\n";
    echo "   ✓ CPF: {$cliente->getCpf()}\n\n";
    
    // 2. Criar/Buscar Veículo
    echo "2. Criando veículo...\n";
    $veiculoRepo = new VeiculoRepository();
    $veiculo = new Veiculo();
    $veiculo->setPlaca("TEST1234");
    $veiculo->setModelo("Honda CB 1000");
    $veiculo->setAno(2023);
    
    $veiculo = $veiculoRepo->findOrCreate($veiculo);
    echo "   ✓ Veículo ID: {$veiculo->getId()}\n";
    echo "   ✓ Placa: {$veiculo->getPlaca()}\n";
    echo "   ✓ Modelo: {$veiculo->getModelo()}\n\n";
    
    // 3. Criar Intenção de Venda
    echo "3. Criando intenção de venda...\n";
    $intencaoRepo = new IntencaoVendaRepository();
    $intencao = new IntencaoVenda();
    $intencao->setClienteId($cliente->getId());
    $intencao->setVeiculoId($veiculo->getId());
    $intencao->setNumeroCrv("999888");
    $intencao->setCodigoSegurancaCrv("XYZ789");
    $intencao->setStatus("rascunho");
    
    $intencaoId = $intencaoRepo->create($intencao);
    echo "   ✓ Intenção criada com ID: {$intencaoId}\n\n";
    
    // 4. Buscar Intenção completa
    echo "4. Buscando intenção criada...\n";
    $intencaoBusca = $intencaoRepo->findById($intencaoId);
    $clienteBusca = $clienteRepo->findById($intencaoBusca->getClienteId());
    $veiculoBusca = $veiculoRepo->findById($intencaoBusca->getVeiculoId());
    
    echo "   ✓ Intenção ID: {$intencaoBusca->getId()}\n";
    echo "   ✓ Cliente: {$clienteBusca->getNome()}\n";
    echo "   ✓ Veículo: {$veiculoBusca->getModelo()} - {$veiculoBusca->getPlaca()}\n";
    echo "   ✓ CRV: {$intencaoBusca->getNumeroCrv()}\n";
    echo "   ✓ Status: {$intencaoBusca->getStatus()}\n\n";
    
    // 5. Atualizar status
    echo "5. Atualizando status para 'gerado'...\n";
    $intencaoBusca->setStatus("gerado");
    $intencaoBusca->setArquivoPdf("/storage/documentos/intencoes/intencao_{$intencaoId}.pdf");
    $intencaoRepo->update($intencaoBusca);
    
    $intencaoAtualizada = $intencaoRepo->findById($intencaoId);
    echo "   ✓ Status atualizado: {$intencaoAtualizada->getStatus()}\n";
    echo "   ✓ PDF: {$intencaoAtualizada->getArquivoPdf()}\n\n";
    
    echo "=== TESTE DE INTEGRAÇÃO CONCLUÍDO COM SUCESSO ===\n";
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}


