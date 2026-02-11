<?php

namespace App\Models\Documentos;

use TCPDF;
use App\Models\Repositories\ClienteRepository;
use App\Models\Repositories\VeiculoRepository;

class EtiquetaEnvelopeGenerator
{
    private ClienteRepository $clienteRepository;
    private VeiculoRepository $veiculoRepository;

    public function __construct()
    {
        $this->clienteRepository = new ClienteRepository();
        $this->veiculoRepository = new VeiculoRepository();
    }

    public function gerar(int $clienteId, int $veiculoId, string $dataReconhecimento): string
    {
        $cliente = $this->clienteRepository->findById($clienteId);
        $veiculo = $this->veiculoRepository->findById($veiculoId);

        if (!$cliente || !$veiculo) {
            throw new \RuntimeException('Cliente ou veículo não encontrado');
        }

        $pdf = new TCPDF('P', 'mm', [100, 60], true, 'UTF-8', false);

        $pdf->SetCreator('Sistema de Gerenciamento de Vendas');
        $pdf->SetAuthor('Sistema de Motos');
        $pdf->SetTitle('Etiqueta de Envelope');
        $pdf->SetSubject('Etiqueta para Envelope');

        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        $this->adicionarConteudo($pdf, $cliente, $veiculo, $dataReconhecimento);

        $configPath = dirname(__DIR__, 3) . '/config/app.php';
        $config = require $configPath;
        $diretorio = $config['documents_path'] . '/etiquetas';
        
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $nomeArquivo = 'etiqueta_' . $clienteId . '_' . date('YmdHis') . '.pdf';
        $caminhoCompleto = $diretorio . '/' . $nomeArquivo;

        $pdf->Output($caminhoCompleto, 'F');

        return $caminhoCompleto;
    }

    private function adicionarConteudo(TCPDF $pdf, $cliente, $veiculo, string $dataReconhecimento): void
    {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, $cliente->getNome(), 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 4, 'CPF: ' . $cliente->getCpf(), 0, 1, 'L');
        $pdf->Cell(0, 4, 'Placa: ' . $veiculo->getPlaca(), 0, 1, 'L');
        $pdf->Cell(0, 4, 'Modelo: ' . $veiculo->getModelo(), 0, 1, 'L');
        
        $dataFormatada = date('d/m/Y', strtotime($dataReconhecimento));
        $pdf->Cell(0, 4, 'Data Reconhecimento: ' . $dataFormatada, 0, 1, 'L');
    }
}

