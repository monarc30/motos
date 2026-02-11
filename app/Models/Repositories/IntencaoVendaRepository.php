<?php

namespace App\Models\Repositories;

use App\Models\Entities\IntencaoVenda;
use App\Services\Database;
use PDO;

class IntencaoVendaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?IntencaoVenda
    {
        $stmt = $this->db->prepare("SELECT * FROM intencoes_venda WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function create(IntencaoVenda $intencao): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO intencoes_venda 
            (cliente_id, veiculo_id, numero_crv, codigo_seguranca_crv, status, arquivo_pdf)
            VALUES (:cliente_id, :veiculo_id, :numero_crv, :codigo_seguranca_crv, :status, :arquivo_pdf)
        ");

        $stmt->execute([
            'cliente_id' => $intencao->getClienteId(),
            'veiculo_id' => $intencao->getVeiculoId(),
            'numero_crv' => $intencao->getNumeroCrv(),
            'codigo_seguranca_crv' => $intencao->getCodigoSegurancaCrv(),
            'status' => $intencao->getStatus(),
            'arquivo_pdf' => $intencao->getArquivoPdf(),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(IntencaoVenda $intencao): bool
    {
        $stmt = $this->db->prepare("
            UPDATE intencoes_venda 
            SET numero_crv = :numero_crv, 
                codigo_seguranca_crv = :codigo_seguranca_crv,
                status = :status,
                arquivo_pdf = :arquivo_pdf
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $intencao->getId(),
            'numero_crv' => $intencao->getNumeroCrv(),
            'codigo_seguranca_crv' => $intencao->getCodigoSegurancaCrv(),
            'status' => $intencao->getStatus(),
            'arquivo_pdf' => $intencao->getArquivoPdf(),
        ]);
    }

    private function hydrate(array $data): IntencaoVenda
    {
        $intencao = new IntencaoVenda();
        $intencao->setId($data['id']);
        $intencao->setClienteId($data['cliente_id']);
        $intencao->setVeiculoId($data['veiculo_id']);
        $intencao->setNumeroCrv($data['numero_crv']);
        $intencao->setCodigoSegurancaCrv($data['codigo_seguranca_crv']);
        $intencao->setStatus($data['status']);
        $intencao->setArquivoPdf($data['arquivo_pdf']);
        $intencao->setCreatedAt($data['created_at']);
        $intencao->setUpdatedAt($data['updated_at']);

        return $intencao;
    }
}


