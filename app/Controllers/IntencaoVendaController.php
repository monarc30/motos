<?php

namespace App\Controllers;

class IntencaoVendaController extends BaseController
{
    public function index(): void
    {
        $this->render('intencao-venda/formulario');
    }

    public function buscarAutoconf(): void
    {
        $vendaId = $_POST['venda_id'] ?? null;
        
        if (!$vendaId) {
            $this->json(['erro' => 'ID da venda não informado'], 400);
            return;
        }

        try {
            $autoconfService = new \App\Models\Autoconf\AutoconfService();
            $dados = $autoconfService->buscarESalvarDados($vendaId);

            $this->json([
                'sucesso' => true,
                'dados' => [
                    'cliente' => [
                        'nome' => $dados['cliente']['nome'] ?? '',
                        'cpf' => $dados['cliente']['cpf'] ?? '',
                        'telefone' => $dados['cliente']['telefone'] ?? '',
                        'whatsapp' => $dados['cliente']['whatsapp'] ?? '',
                        'endereco' => $dados['cliente']['endereco'] ?? '',
                    ],
                    'veiculo' => [
                        'modelo' => $dados['veiculo']['modelo'] ?? '',
                        'placa' => $dados['veiculo']['placa'] ?? '',
                        'ano' => $dados['veiculo']['ano'] ?? '',
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            $this->json(['erro' => $e->getMessage()], 500);
        }
    }

    /**
     * Lista vendas/veículos do Autoconf para o usuário escolher (quando não sabe o ID).
     * GET: ?termo=opcional&pagina=1
     */
    public function listarVendasAutoconf(): void
    {
        try {
            $termo = trim($_GET['termo'] ?? '');
            $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
            $client = new \App\Models\Autoconf\AutoconfClient();
            $resultado = $client->listarVeiculos($pagina, $termo, 50);
            $veiculos = $resultado['veiculos'] ?? [];
            if (!empty($veiculos)) {
                $autoconfService = new \App\Models\Autoconf\AutoconfService();
                $autoconfService->sincronizarListaNoBanco($veiculos);
            }
            $this->json([
                'sucesso' => true,
                'veiculos' => $veiculos,
                'total' => $resultado['total'] ?? 0,
            ]);
        } catch (\Exception $e) {
            $this->json(['erro' => $e->getMessage(), 'veiculos' => [], 'total' => 0], 500);
        }
    }

    /**
     * Busca dados por placa via webhook Innovart (veículo + comprador).
     * POST: placa=XXX
     */
    public function buscarPorPlaca(): void
    {
        $placa = trim($_POST['placa'] ?? $_GET['placa'] ?? '');
        if ($placa === '') {
            $this->json(['erro' => 'Placa não informada.'], 400);
            return;
        }

        $config = require dirname(__DIR__, 2) . '/config/innovart.php';
        if (empty($config['enabled'])) {
            $this->json(['erro' => 'Busca por placa (Innovart) não está configurada. Configure INNOVART_USER e INNOVART_PASSWORD no .env.'], 503);
            return;
        }

        try {
            $client = new \App\Models\Innovart\InnovartClient();
            $dados = $client->getPorPlaca($placa);

            $this->json([
                'sucesso' => true,
                'dados' => [
                    'cliente' => [
                        'nome' => $dados['cliente']['nome'] ?? '',
                        'cpf' => $dados['cliente']['cpf'] ?? '',
                        'telefone' => $dados['cliente']['telefone'] ?? '',
                        'whatsapp' => $dados['cliente']['whatsapp'] ?? '',
                        'endereco' => $dados['cliente']['endereco'] ?? '',
                    ],
                    'veiculo' => [
                        'modelo' => $dados['veiculo']['modelo'] ?? '',
                        'placa' => $dados['veiculo']['placa'] ?? $placa,
                        'ano' => $dados['veiculo']['ano'] ?? null,
                    ],
                    'venda_id' => $dados['venda_id'] ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            $this->json(['erro' => $e->getMessage()], 500);
        }
    }

    public function gerar(): void
    {
        try {
            $clienteRepo = new \App\Models\Repositories\ClienteRepository();
            $veiculoRepo = new \App\Models\Repositories\VeiculoRepository();
            $intencaoRepo = new \App\Models\Repositories\IntencaoVendaRepository();
            $autoconfService = new \App\Models\Autoconf\AutoconfService();

            $nomeCliente = $_POST['nome_cliente'] ?? '';
            $cpfCliente = $_POST['cpf_cliente'] ?? '';
            $numeroCrv = $_POST['numero_crv'] ?? '';
            $codigoSegurancaCrv = $_POST['codigo_seguranca_crv'] ?? '';
            $placaMoto = $_POST['placa_moto'] ?? '';
            $vendaId = $_POST['venda_id'] ?? '';

            if (empty($nomeCliente) || empty($cpfCliente) || empty($numeroCrv) || empty($codigoSegurancaCrv)) {
                $this->json(['erro' => 'Dados incompletos'], 400);
                return;
            }

            $cliente = new \App\Models\Entities\Cliente();
            $cliente->setNome($nomeCliente);
            $cliente->setCpf($cpfCliente);
            $cliente->setTelefone($_POST['telefone_cliente'] ?? null);
            $cliente->setWhatsapp($_POST['whatsapp_cliente'] ?? null);
            $cliente->setEndereco($_POST['endereco_cliente'] ?? null);
            $cliente = $clienteRepo->findOrCreate($cliente);

            $veiculo = new \App\Models\Entities\Veiculo();
            $veiculo->setPlaca($placaMoto);
            $veiculo->setModelo($_POST['modelo_moto'] ?? '');
            $veiculo->setAno(!empty($_POST['ano_moto']) ? (int) $_POST['ano_moto'] : null);
            $veiculo = $veiculoRepo->findOrCreate($veiculo);

            $intencao = new \App\Models\Entities\IntencaoVenda();
            $intencao->setClienteId($cliente->getId());
            $intencao->setVeiculoId($veiculo->getId());
            $intencao->setNumeroCrv($numeroCrv);
            $intencao->setCodigoSegurancaCrv($codigoSegurancaCrv);
            $intencao->setStatus('rascunho');

            $intencaoId = $intencaoRepo->create($intencao);

            $generator = new \App\Models\Documentos\IntencaoVendaGenerator();
            $caminhoPdf = $generator->gerar($intencao);

            $intencao->setId($intencaoId);
            $intencao->setArquivoPdf($caminhoPdf);
            $intencao->setStatus('gerado');
            $intencaoRepo->update($intencao);

            if (!empty($vendaId)) {
                try {
                    $autoconfService->anexarDocumento($vendaId, $caminhoPdf, basename($caminhoPdf));
                } catch (\Exception $e) {
                    error_log("Erro ao anexar documento no Autoconf: " . $e->getMessage());
                }
            }

            $this->json([
                'sucesso' => true,
                'mensagem' => 'PDF gerado com sucesso',
                'arquivo' => basename($caminhoPdf),
                'caminho' => $caminhoPdf,
                'id' => $intencaoId,
            ]);
        } catch (\Exception $e) {
            $this->json(['erro' => $e->getMessage()], 500);
        }
    }

    public function download(): void
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo 'ID não informado';
            return;
        }

        try {
            $intencaoRepo = new \App\Models\Repositories\IntencaoVendaRepository();
            $intencao = $intencaoRepo->findById((int) $id);

            if (!$intencao || !$intencao->getArquivoPdf() || !file_exists($intencao->getArquivoPdf())) {
                http_response_code(404);
                echo 'Arquivo não encontrado';
                return;
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($intencao->getArquivoPdf()) . '"');
            header('Content-Length: ' . filesize($intencao->getArquivoPdf()));
            readfile($intencao->getArquivoPdf());
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro: ' . $e->getMessage();
        }
    }
}

