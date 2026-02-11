<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Entities\Cliente;
use App\Models\Entities\Veiculo;
use App\Models\Entities\IntencaoVenda;
use App\Models\Entities\ComunicadoVenda;
use App\Models\Repositories\ClienteRepository;
use App\Models\Repositories\VeiculoRepository;
use App\Models\Repositories\IntencaoVendaRepository;
use App\Models\Repositories\ComunicadoVendaRepository;
use App\Models\Documentos\IntencaoVendaGenerator;
use App\Models\Documentos\ComunicadoVendaGenerator;
use App\Models\Documentos\EtiquetaEnvelopeGenerator;

echo "=== TESTES DE GERAÇÃO DE PDFs ===\n\n";

// Teste 1: Intenção de Venda
echo "1. Testando geração de PDF - Intenção de Venda...\n";
try {
    $clienteRepo = new ClienteRepository();
    $veiculoRepo = new VeiculoRepository();
    $intencaoRepo = new IntencaoVendaRepository();

    $cliente = new Cliente();
    $cliente->setNome("Carlos Teste PDF");
    $cliente->setCpf("555.666.777-88");
    $cliente->setTelefone("(11) 99999-1111");
    $cliente->setWhatsapp("(11) 99999-1111");
    $cliente->setEndereco("Rua Teste PDF, 789 - São Paulo/SP");
    $cliente = $clienteRepo->findOrCreate($cliente);

    $veiculo = new Veiculo();
    $veiculo->setPlaca("PDF1234");
    $veiculo->setModelo("Yamaha MT-07");
    $veiculo->setAno(2022);
    $veiculo = $veiculoRepo->findOrCreate($veiculo);

    $intencao = new IntencaoVenda();
    $intencao->setClienteId($cliente->getId());
    $intencao->setVeiculoId($veiculo->getId());
    $intencao->setNumeroCrv("888777");
    $intencao->setCodigoSegurancaCrv("XYZ999");
    $intencao->setStatus("rascunho");

    $intencaoId = $intencaoRepo->create($intencao);
    $intencao->setId($intencaoId);

    $generator = new IntencaoVendaGenerator();
    $caminhoPdf = $generator->gerar($intencao);

    if (file_exists($caminhoPdf)) {
        $tamanho = filesize($caminhoPdf);
        echo "   ✓ PDF gerado com sucesso\n";
        echo "   ✓ Caminho: {$caminhoPdf}\n";
        echo "   ✓ Tamanho: " . number_format($tamanho / 1024, 2) . " KB\n";

        $intencao->setArquivoPdf($caminhoPdf);
        $intencao->setStatus("gerado");
        $intencaoRepo->update($intencao);
        echo "   ✓ Status atualizado no banco\n";
    } else {
        echo "   ✗ Arquivo não foi criado\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 2: Comunicado de Venda
echo "2. Testando geração de PDF - Comunicado de Venda...\n";
try {
    $clienteRepo = new ClienteRepository();
    $veiculoRepo = new VeiculoRepository();
    $comunicadoRepo = new ComunicadoVendaRepository();

    $cliente = $clienteRepo->findByCpf("555.666.777-88");
    $veiculo = $veiculoRepo->findByPlaca("PDF1234");

    if ($cliente && $veiculo) {
        $comunicado = new ComunicadoVenda();
        $comunicado->setClienteId($cliente->getId());
        $comunicado->setVeiculoId($veiculo->getId());
        $comunicado->setDataComunicado("2025-01-14");

        $comunicadoId = $comunicadoRepo->create($comunicado);
        $comunicado->setId($comunicadoId);

        $generator = new ComunicadoVendaGenerator();
        $caminhoPdf = $generator->gerar($comunicado);

        if (file_exists($caminhoPdf)) {
            $tamanho = filesize($caminhoPdf);
            echo "   ✓ PDF gerado com sucesso\n";
            echo "   ✓ Caminho: {$caminhoPdf}\n";
            echo "   ✓ Tamanho: " . number_format($tamanho / 1024, 2) . " KB\n";

            $comunicado->setArquivoPdf($caminhoPdf);
            $comunicadoRepo->update($comunicado);
            echo "   ✓ Salvo no banco de dados\n";
        } else {
            echo "   ✗ Arquivo não foi criado\n";
        }
    } else {
        echo "   ⚠ Cliente ou veículo não encontrado\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 3: Etiqueta de Envelope
echo "3. Testando geração de PDF - Etiqueta de Envelope...\n";
try {
    $clienteRepo = new ClienteRepository();
    $veiculoRepo = new VeiculoRepository();

    $cliente = $clienteRepo->findByCpf("555.666.777-88");
    $veiculo = $veiculoRepo->findByPlaca("PDF1234");

    if ($cliente && $veiculo) {
        $generator = new EtiquetaEnvelopeGenerator();
        $caminhoPdf = $generator->gerar($cliente->getId(), $veiculo->getId(), "2025-01-14");

        if (file_exists($caminhoPdf)) {
            $tamanho = filesize($caminhoPdf);
            echo "   ✓ Etiqueta gerada com sucesso\n";
            echo "   ✓ Caminho: {$caminhoPdf}\n";
            echo "   ✓ Tamanho: " . number_format($tamanho / 1024, 2) . " KB\n";
        } else {
            echo "   ✗ Arquivo não foi criado\n";
        }
    } else {
        echo "   ⚠ Cliente ou veículo não encontrado\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ✗ Erro: " . $e->getMessage() . "\n\n";
}

// Teste 4: Verificar arquivos gerados
echo "4. Verificando arquivos gerados...\n";
$config = require __DIR__ . '/../config/app.php';
$diretorios = [
    'intencoes' => $config['documents_path'] . '/intencoes',
    'comunicados' => $config['documents_path'] . '/comunicados',
    'etiquetas' => $config['documents_path'] . '/etiquetas',
];

foreach ($diretorios as $tipo => $diretorio) {
    if (is_dir($diretorio)) {
        $arquivos = glob($diretorio . '/*.pdf');
        echo "   ✓ {$tipo}: " . count($arquivos) . " arquivo(s)\n";
        if (count($arquivos) > 0) {
            $ultimo = basename(end($arquivos));
            echo "      Último: {$ultimo}\n";
        }
    } else {
        echo "   ⚠ {$tipo}: diretório não existe\n";
    }
}
echo "\n";

echo "=== TESTES CONCLUÍDOS ===\n";


