<?php

namespace App\Models\Repositories;

use App\Services\Database;
use PDO;

/**
 * Snapshot de vendas/veículos vindos do Autoconf.
 * Guarda no nosso banco para não perder histórico quando a moto sai do Autoconf.
 */
class VendasAutoconfRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByIdAutoconf(string $idAutoconf): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM vendas_autoconf WHERE id_autoconf = :id_autoconf");
        $stmt->execute(['id_autoconf' => $idAutoconf]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Insere ou atualiza o snapshot (id_autoconf, veiculo_id, cliente_id).
     * Mantém histórico mesmo depois que a moto sair do Autoconf.
     */
    public function saveSnapshot(string $idAutoconf, int $veiculoId, ?int $clienteId = null): void
    {
        $existing = $this->findByIdAutoconf($idAutoconf);

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE vendas_autoconf 
                SET veiculo_id = :veiculo_id, cliente_id = :cliente_id, updated_at = NOW()
                WHERE id_autoconf = :id_autoconf
            ");
            $stmt->execute([
                'id_autoconf' => $idAutoconf,
                'veiculo_id' => $veiculoId,
                'cliente_id' => $clienteId,
            ]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO vendas_autoconf (id_autoconf, veiculo_id, cliente_id)
                VALUES (:id_autoconf, :veiculo_id, :cliente_id)
            ");
            $stmt->execute([
                'id_autoconf' => $idAutoconf,
                'veiculo_id' => $veiculoId,
                'cliente_id' => $clienteId,
            ]);
        }
    }
}
