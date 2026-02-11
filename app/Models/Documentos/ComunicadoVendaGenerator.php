<?php

namespace App\Models\Documentos;

use TCPDF;
use App\Models\Entities\ComunicadoVenda;
use App\Models\Repositories\ClienteRepository;
use App\Models\Repositories\VeiculoRepository;

class ComunicadoVendaGenerator
{
    private ClienteRepository $clienteRepository;
    private VeiculoRepository $veiculoRepository;

    public function __construct()
    {
        $this->clienteRepository = new ClienteRepository();
        $this->veiculoRepository = new VeiculoRepository();
    }

    public function gerar(ComunicadoVenda $comunicado): string
    {
        $cliente = $this->clienteRepository->findById($comunicado->getClienteId());
        $veiculo = $this->veiculoRepository->findById($comunicado->getVeiculoId());

        if (!$cliente || !$veiculo) {
            throw new \RuntimeException('Cliente ou veículo não encontrado');
        }

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator('Sistema de Gerenciamento de Vendas');
        $pdf->SetAuthor('Sistema de Motos');
        $pdf->SetTitle('Comunicado de Venda');
        $pdf->SetSubject('Comunicado de Venda de Motocicleta');

        $pdf->SetMargins(15, 20, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);

        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();

        $this->adicionarCabecalho($pdf);
        $this->adicionarConteudo($pdf, $cliente, $veiculo, $comunicado);
        $this->adicionarRodape($pdf, $comunicado);

        $configPath = dirname(__DIR__, 3) . '/config/app.php';
        $config = require $configPath;
        $diretorio = $config['documents_path'] . '/comunicados';
        
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $nomeArquivo = 'comunicado_venda_' . $comunicado->getId() . '_' . date('YmdHis') . '.pdf';
        $caminhoCompleto = $diretorio . '/' . $nomeArquivo;

        $pdf->Output($caminhoCompleto, 'F');

        return $caminhoCompleto;
    }

    private function adicionarCabecalho(TCPDF $pdf): void
    {
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'COMUNICADO DE VENDA', 0, 1, 'C');
        $pdf->Ln(5);
    }

    private function adicionarConteudo(TCPDF $pdf, $cliente, $veiculo, ComunicadoVenda $comunicado): void
    {
        $pdf->SetFont('helvetica', '', 11);
        
        $dataFormatada = date('d/m/Y', strtotime($comunicado->getDataComunicado()));
        
        $texto = "Comunicamos a venda da motocicleta abaixo relacionada:\n\n";
        $texto .= "Comprador: " . $cliente->getNome() . "\n";
        $texto .= "CPF: " . $cliente->getCpf() . "\n\n";
        $texto .= "Veículo: " . $veiculo->getModelo() . "\n";
        $texto .= "Placa: " . $veiculo->getPlaca() . "\n\n";
        $texto .= "Data do Comunicado: " . $dataFormatada . "\n\n";
        $texto .= "Este comunicado tem por finalidade informar a venda do veículo acima descrito.\n";

        $pdf->MultiCell(0, 6, $texto, 0, 'L');
        $pdf->Ln(10);
    }

    private function adicionarRodape(TCPDF $pdf, ComunicadoVenda $comunicado): void
    {
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetY(-30);
        $pdf->Cell(0, 5, 'Documento gerado em: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'ID do Comunicado: ' . $comunicado->getId(), 0, 1, 'C');
    }
}

