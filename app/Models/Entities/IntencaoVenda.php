<?php

namespace App\Models\Entities;

class IntencaoVenda
{
    private ?int $id = null;
    private int $clienteId;
    private int $veiculoId;
    private ?string $numeroCrv = null;
    private ?string $codigoSegurancaCrv = null;
    private string $status = 'rascunho';
    private ?string $arquivoPdf = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getClienteId(): int
    {
        return $this->clienteId;
    }

    public function setClienteId(int $clienteId): void
    {
        $this->clienteId = $clienteId;
    }

    public function getVeiculoId(): int
    {
        return $this->veiculoId;
    }

    public function setVeiculoId(int $veiculoId): void
    {
        $this->veiculoId = $veiculoId;
    }

    public function getNumeroCrv(): ?string
    {
        return $this->numeroCrv;
    }

    public function setNumeroCrv(?string $numeroCrv): void
    {
        $this->numeroCrv = $numeroCrv;
    }

    public function getCodigoSegurancaCrv(): ?string
    {
        return $this->codigoSegurancaCrv;
    }

    public function setCodigoSegurancaCrv(?string $codigoSegurancaCrv): void
    {
        $this->codigoSegurancaCrv = $codigoSegurancaCrv;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getArquivoPdf(): ?string
    {
        return $this->arquivoPdf;
    }

    public function setArquivoPdf(?string $arquivoPdf): void
    {
        $this->arquivoPdf = $arquivoPdf;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'cliente_id' => $this->clienteId,
            'veiculo_id' => $this->veiculoId,
            'numero_crv' => $this->numeroCrv,
            'codigo_seguranca_crv' => $this->codigoSegurancaCrv,
            'status' => $this->status,
            'arquivo_pdf' => $this->arquivoPdf,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}


