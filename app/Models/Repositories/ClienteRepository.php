<?php

namespace App\Models\Repositories;

use App\Models\Entities\Cliente;
use App\Services\Database;
use PDO;

class ClienteRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?Cliente
    {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findByCpf(string $cpf): ?Cliente
    {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE cpf = :cpf");
        $stmt->execute(['cpf' => $cpf]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function create(Cliente $cliente): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO clientes (nome, cpf, telefone, whatsapp, endereco)
            VALUES (:nome, :cpf, :telefone, :whatsapp, :endereco)
        ");

        $stmt->execute([
            'nome' => $cliente->getNome(),
            'cpf' => $cliente->getCpf(),
            'telefone' => $cliente->getTelefone(),
            'whatsapp' => $cliente->getWhatsapp(),
            'endereco' => $cliente->getEndereco(),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(Cliente $cliente): bool
    {
        $stmt = $this->db->prepare("
            UPDATE clientes 
            SET nome = :nome, cpf = :cpf, telefone = :telefone, 
                whatsapp = :whatsapp, endereco = :endereco
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $cliente->getId(),
            'nome' => $cliente->getNome(),
            'cpf' => $cliente->getCpf(),
            'telefone' => $cliente->getTelefone(),
            'whatsapp' => $cliente->getWhatsapp(),
            'endereco' => $cliente->getEndereco(),
        ]);
    }

    public function findOrCreate(Cliente $cliente): Cliente
    {
        $existing = $this->findByCpf($cliente->getCpf());
        
        if ($existing) {
            return $existing;
        }

        $id = $this->create($cliente);
        $cliente->setId($id);
        
        return $cliente;
    }

    /**
     * Busca cliente associado a um veículo através das intenções/comunicados de venda
     * 
     * @param string $placa Placa do veículo
     * @return Cliente|null Cliente encontrado ou null
     */
    public function buscarClientePorVeiculo(string $placa): ?Cliente
    {
        if (empty($placa)) {
            return null;
        }

        // Busca o cliente mais recente associado a este veículo
        // através das intenções de venda ou comunicados de venda
        $stmt = $this->db->prepare("
            SELECT c.* 
            FROM clientes c
            INNER JOIN intencoes_venda iv ON iv.cliente_id = c.id
            INNER JOIN veiculos v ON v.id = iv.veiculo_id
            WHERE v.placa = :placa
            ORDER BY iv.created_at DESC
            LIMIT 1
        ");
        
        $stmt->execute(['placa' => $placa]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return $this->hydrate($data);
        }

        // Tenta também pelos comunicados de venda
        $stmt = $this->db->prepare("
            SELECT c.* 
            FROM clientes c
            INNER JOIN comunicados_venda cv ON cv.cliente_id = c.id
            INNER JOIN veiculos v ON v.id = cv.veiculo_id
            WHERE v.placa = :placa
            ORDER BY cv.created_at DESC
            LIMIT 1
        ");
        
        $stmt->execute(['placa' => $placa]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return $this->hydrate($data);
        }

        return null;
    }

    private function hydrate(array $data): Cliente
    {
        $cliente = new Cliente();
        $cliente->setId($data['id']);
        $cliente->setNome($data['nome']);
        $cliente->setCpf($data['cpf']);
        $cliente->setTelefone($data['telefone']);
        $cliente->setWhatsapp($data['whatsapp']);
        $cliente->setEndereco($data['endereco']);
        $cliente->setCreatedAt($data['created_at']);
        $cliente->setUpdatedAt($data['updated_at']);

        return $cliente;
    }
}


