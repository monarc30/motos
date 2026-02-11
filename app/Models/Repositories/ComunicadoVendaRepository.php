<?php

namespace App\Models\Repositories;

use App\Models\Entities\ComunicadoVenda;
use App\Services\Database;
use PDO;

class ComunicadoVendaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?ComunicadoVenda
    {
        $stmt = $this->db->prepare("SELECT * FROM comunicados_venda WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function create(ComunicadoVenda $comunicado): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO comunicados_venda 
            (cliente_id, veiculo_id, data_comunicado, arquivo_pdf, etiqueta_pdf)
            VALUES (:cliente_id, :veiculo_id, :data_comunicado, :arquivo_pdf, :etiqueta_pdf)
        ");

        $stmt->execute([
            'cliente_id' => $comunicado->getClienteId(),
            'veiculo_id' => $comunicado->getVeiculoId(),
            'data_comunicado' => $comunicado->getDataComunicado(),
            'arquivo_pdf' => $comunicado->getArquivoPdf(),
            'etiqueta_pdf' => $comunicado->getEtiquetaPdf(),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(ComunicadoVenda $comunicado): bool
    {
        $stmt = $this->db->prepare("
            UPDATE comunicados_venda 
            SET data_comunicado = :data_comunicado,
                arquivo_pdf = :arquivo_pdf,
                etiqueta_pdf = :etiqueta_pdf
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $comunicado->getId(),
            'data_comunicado' => $comunicado->getDataComunicado(),
            'arquivo_pdf' => $comunicado->getArquivoPdf(),
            'etiqueta_pdf' => $comunicado->getEtiquetaPdf(),
        ]);
    }

    private function hydrate(array $data): ComunicadoVenda
    {
        $comunicado = new ComunicadoVenda();
        $comunicado->setId($data['id']);
        $comunicado->setClienteId($data['cliente_id']);
        $comunicado->setVeiculoId($data['veiculo_id']);
        $comunicado->setDataComunicado($data['data_comunicado']);
        $comunicado->setArquivoPdf($data['arquivo_pdf']);
        $comunicado->setEtiquetaPdf($data['etiqueta_pdf']);
        $comunicado->setCreatedAt($data['created_at']);
        $comunicado->setUpdatedAt($data['updated_at']);

        return $comunicado;
    }
}


