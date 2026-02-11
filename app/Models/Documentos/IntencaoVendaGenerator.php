<?php

namespace App\Models\Documentos;

use TCPDF;
use App\Models\Entities\IntencaoVenda;
use App\Models\Repositories\ClienteRepository;
use App\Models\Repositories\VeiculoRepository;

class IntencaoVendaGenerator
{
    private ClienteRepository $clienteRepository;
    private VeiculoRepository $veiculoRepository;

    public function __construct()
    {
        $this->clienteRepository = new ClienteRepository();
        $this->veiculoRepository = new VeiculoRepository();
    }

    public function gerar(IntencaoVenda $intencao): string
    {
        $cliente = $this->clienteRepository->findById($intencao->getClienteId());
        $veiculo = $this->veiculoRepository->findById($intencao->getVeiculoId());

        if (!$cliente || !$veiculo) {
            throw new \RuntimeException('Cliente ou veículo não encontrado');
        }

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator('Sistema de Gerenciamento de Vendas');
        $pdf->SetAuthor('Sistema de Motos');
        $pdf->SetTitle('Intenção de Venda');
        $pdf->SetSubject('Intenção de Venda de Motocicleta');

        $pdf->SetMargins(15, 20, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);

        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();

        $this->adicionarCabecalho($pdf);
        $this->adicionarDadosCliente($pdf, $cliente);
        $this->adicionarDadosVeiculo($pdf, $veiculo);
        $this->adicionarDadosCRV($pdf, $intencao);
        $this->adicionarRodape($pdf, $intencao);

        $configPath = dirname(__DIR__, 3) . '/config/app.php';
        $config = require $configPath;
        $diretorio = $config['documents_path'] . '/intencoes';
        
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $nomeArquivo = 'intencao_venda_' . $intencao->getId() . '_' . date('YmdHis') . '.pdf';
        $caminhoCompleto = $diretorio . '/' . $nomeArquivo;

        $pdf->Output($caminhoCompleto, 'F');

        return $caminhoCompleto;
    }

    private function adicionarCabecalho(TCPDF $pdf): void
    {
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'INTENÇÃO DE VENDA DE MOTOCICLETA', 0, 1, 'C');
        $pdf->Ln(5);
    }

    private function adicionarDadosCliente(TCPDF $pdf, $cliente): void
    {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'DADOS DO COMPRADOR', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        
        $pdf->Cell(40, 6, 'Nome:', 0, 0, 'L');
        $pdf->Cell(0, 6, $cliente->getNome(), 0, 1, 'L');
        
        $pdf->Cell(40, 6, 'CPF:', 0, 0, 'L');
        $pdf->Cell(0, 6, $cliente->getCpf(), 0, 1, 'L');
        
        if ($cliente->getTelefone()) {
            $pdf->Cell(40, 6, 'Telefone:', 0, 0, 'L');
            $pdf->Cell(0, 6, $cliente->getTelefone(), 0, 1, 'L');
        }
        
        if ($cliente->getWhatsapp()) {
            $pdf->Cell(40, 6, 'WhatsApp:', 0, 0, 'L');
            $pdf->Cell(0, 6, $cliente->getWhatsapp(), 0, 1, 'L');
        }
        
        if ($cliente->getEndereco()) {
            $pdf->Cell(40, 6, 'Endereço:', 0, 0, 'L');
            $pdf->MultiCell(0, 6, $cliente->getEndereco(), 0, 'L');
        }
        
        $pdf->Ln(5);
    }

    private function adicionarDadosVeiculo(TCPDF $pdf, $veiculo): void
    {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'DADOS DA MOTOCICLETA', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        
        $pdf->Cell(40, 6, 'Modelo:', 0, 0, 'L');
        $pdf->Cell(0, 6, $veiculo->getModelo(), 0, 1, 'L');
        
        $pdf->Cell(40, 6, 'Placa:', 0, 0, 'L');
        $pdf->Cell(0, 6, $veiculo->getPlaca(), 0, 1, 'L');
        
        if ($veiculo->getAno()) {
            $pdf->Cell(40, 6, 'Ano:', 0, 0, 'L');
            $pdf->Cell(0, 6, (string) $veiculo->getAno(), 0, 1, 'L');
        }
        
        $pdf->Ln(5);
    }

    private function adicionarDadosCRV(TCPDF $pdf, IntencaoVenda $intencao): void
    {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'DADOS DO CRV', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        
        if ($intencao->getNumeroCrv()) {
            $pdf->Cell(60, 6, 'Número do CRV:', 0, 0, 'L');
            $pdf->Cell(0, 6, $intencao->getNumeroCrv(), 0, 1, 'L');
        }
        
        if ($intencao->getCodigoSegurancaCrv()) {
            $pdf->Cell(60, 6, 'Código de Segurança:', 0, 0, 'L');
            $pdf->Cell(0, 6, $intencao->getCodigoSegurancaCrv(), 0, 1, 'L');
        }
        
        $pdf->Ln(10);
    }

    private function adicionarRodape(TCPDF $pdf, IntencaoVenda $intencao): void
    {
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetY(-30);
        $pdf->Cell(0, 5, 'Documento gerado em: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'ID da Intenção: ' . $intencao->getId(), 0, 1, 'C');
    }
}

