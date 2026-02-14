<?php

namespace App\Controllers;

class ComunicadoVendaController extends BaseController
{
    public function index(): void
    {
        $this->render('comunicado-venda/formulario');
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
                    ],
                    'veiculo' => [
                        'placa' => $dados['veiculo']['placa'] ?? '',
                        'modelo' => $dados['veiculo']['modelo'] ?? '',
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
            $comunicadoRepo = new \App\Models\Repositories\ComunicadoVendaRepository();

            $nomeCliente = $_POST['nome_cliente'] ?? '';
            $cpfCliente = $_POST['cpf_cliente'] ?? '';
            $placaMoto = $_POST['placa_moto'] ?? '';
            $dataComunicado = $_POST['data_comunicado'] ?? date('Y-m-d');

            if (empty($nomeCliente) || empty($cpfCliente) || empty($placaMoto)) {
                $this->json(['erro' => 'Dados incompletos'], 400);
                return;
            }

            $cliente = new \App\Models\Entities\Cliente();
            $cliente->setNome($nomeCliente);
            $cliente->setCpf($cpfCliente);
            $cliente = $clienteRepo->findOrCreate($cliente);

            $veiculo = new \App\Models\Entities\Veiculo();
            $veiculo->setPlaca($placaMoto);
            $veiculo->setModelo($_POST['modelo_moto'] ?? '');
            $veiculo = $veiculoRepo->findOrCreate($veiculo);

            $comunicado = new \App\Models\Entities\ComunicadoVenda();
            $comunicado->setClienteId($cliente->getId());
            $comunicado->setVeiculoId($veiculo->getId());
            $comunicado->setDataComunicado($dataComunicado);

            $comunicadoId = $comunicadoRepo->create($comunicado);

            $generator = new \App\Models\Documentos\ComunicadoVendaGenerator();
            $caminhoPdf = $generator->gerar($comunicado);

            $comunicado->setId($comunicadoId);
            $comunicado->setArquivoPdf($caminhoPdf);
            $comunicadoRepo->update($comunicado);

            $resultadoComven = null;
            $protocoloComven = null;

            try {
                $comvenService = new \App\Models\Comven\ComvenService();
                if ($comvenService->isEnabled()) {
                    $resultadoComven = $comvenService->registrarComunicadoVenda($comunicadoId);
                    $protocoloComven = $resultadoComven['protocolo'] ?? $resultadoComven['id'] ?? null;
                }
            } catch (\Exception $e) {
                error_log("Erro ao registrar no COMven: " . $e->getMessage());
            }

            $resposta = [
                'sucesso' => true,
                'mensagem' => 'Comunicado gerado com sucesso',
                'arquivo' => basename($caminhoPdf),
                'caminho' => $caminhoPdf,
                'id' => $comunicadoId,
            ];

            if ($protocoloComven) {
                $resposta['comven'] = [
                    'registrado' => true,
                    'protocolo' => $protocoloComven,
                ];
            } else {
                $resposta['comven'] = [
                    'registrado' => false,
                    'mensagem' => 'COMven não configurado ou desabilitado',
                ];
            }

            $this->json($resposta);
        } catch (\Exception $e) {
            $this->json(['erro' => $e->getMessage()], 500);
        }
    }

    public function gerarEtiqueta(): void
    {
        try {
            $clienteRepo = new \App\Models\Repositories\ClienteRepository();
            $veiculoRepo = new \App\Models\Repositories\VeiculoRepository();

            $nomeCliente = $_POST['nome_cliente'] ?? '';
            $cpfCliente = $_POST['cpf_cliente'] ?? '';
            $placaMoto = $_POST['placa_moto'] ?? '';
            $dataComunicado = $_POST['data_comunicado'] ?? date('Y-m-d');

            if (empty($nomeCliente) || empty($cpfCliente) || empty($placaMoto)) {
                $this->json(['erro' => 'Dados incompletos'], 400);
                return;
            }

            $cliente = $clienteRepo->findByCpf($cpfCliente);
            $veiculo = $veiculoRepo->findByPlaca($placaMoto);

            if (!$cliente || !$veiculo) {
                $this->json(['erro' => 'Cliente ou veículo não encontrado'], 404);
                return;
            }

            $generator = new \App\Models\Documentos\EtiquetaEnvelopeGenerator();
            $caminhoPdf = $generator->gerar($cliente->getId(), $veiculo->getId(), $dataComunicado);

            $this->json([
                'sucesso' => true,
                'mensagem' => 'Etiqueta gerada com sucesso',
                'arquivo' => basename($caminhoPdf),
                'caminho' => $caminhoPdf,
            ]);
        } catch (\Exception $e) {
            $this->json(['erro' => $e->getMessage()], 500);
        }
    }

    public function download(): void
    {
        $id = $_GET['id'] ?? null;
        $tipo = $_GET['tipo'] ?? 'comunicado';
        
        if (!$id) {
            http_response_code(400);
            echo 'ID não informado';
            return;
        }

        try {
            $comunicadoRepo = new \App\Models\Repositories\ComunicadoVendaRepository();
            $comunicado = $comunicadoRepo->findById((int) $id);

            if (!$comunicado) {
                http_response_code(404);
                echo 'Comunicado não encontrado';
                return;
            }

            $arquivo = $tipo === 'etiqueta' ? $comunicado->getEtiquetaPdf() : $comunicado->getArquivoPdf();

            if (!$arquivo || !file_exists($arquivo)) {
                http_response_code(404);
                echo 'Arquivo não encontrado';
                return;
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($arquivo) . '"');
            header('Content-Length: ' . filesize($arquivo));
            readfile($arquivo);
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro: ' . $e->getMessage();
        }
    }
}

