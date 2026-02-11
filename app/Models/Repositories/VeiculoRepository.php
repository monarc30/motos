<?php

namespace App\Models\Repositories;

use App\Models\Entities\Veiculo;
use App\Services\Database;
use PDO;

class VeiculoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?Veiculo
    {
        $stmt = $this->db->prepare("SELECT * FROM veiculos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findByPlaca(string $placa): ?Veiculo
    {
        $stmt = $this->db->prepare("SELECT * FROM veiculos WHERE placa = :placa");
        $stmt->execute(['placa' => $placa]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function create(Veiculo $veiculo): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO veiculos (placa, modelo, ano)
            VALUES (:placa, :modelo, :ano)
        ");

        $stmt->execute([
            'placa' => $veiculo->getPlaca(),
            'modelo' => $veiculo->getModelo(),
            'ano' => $veiculo->getAno(),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(Veiculo $veiculo): bool
    {
        $stmt = $this->db->prepare("
            UPDATE veiculos 
            SET placa = :placa, modelo = :modelo, ano = :ano
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $veiculo->getId(),
            'placa' => $veiculo->getPlaca(),
            'modelo' => $veiculo->getModelo(),
            'ano' => $veiculo->getAno(),
        ]);
    }

    public function findOrCreate(Veiculo $veiculo): Veiculo
    {
        $existing = $this->findByPlaca($veiculo->getPlaca());
        
        if ($existing) {
            return $existing;
        }

        $id = $this->create($veiculo);
        $veiculo->setId($id);
        
        return $veiculo;
    }

    private function hydrate(array $data): Veiculo
    {
        $veiculo = new Veiculo();
        $veiculo->setId($data['id']);
        $veiculo->setPlaca($data['placa']);
        $veiculo->setModelo($data['modelo']);
        $veiculo->setAno($data['ano'] ?? null);
        $veiculo->setCreatedAt($data['created_at']);
        $veiculo->setUpdatedAt($data['updated_at']);

        return $veiculo;
    }
}


