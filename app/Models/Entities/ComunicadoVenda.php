<?php

namespace App\Models\Entities;

class ComunicadoVenda
{
    private ?int $id = null;
    private int $clienteId;
    private int $veiculoId;
    private string $dataComunicado;
    private ?string $arquivoPdf = null;
    private ?string $etiquetaPdf = null;
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

    public function getDataComunicado(): string
    {
        return $this->dataComunicado;
    }

    public function setDataComunicado(string $dataComunicado): void
    {
        $this->dataComunicado = $dataComunicado;
    }

    public function getArquivoPdf(): ?string
    {
        return $this->arquivoPdf;
    }

    public function setArquivoPdf(?string $arquivoPdf): void
    {
        $this->arquivoPdf = $arquivoPdf;
    }

    public function getEtiquetaPdf(): ?string
    {
        return $this->etiquetaPdf;
    }

    public function setEtiquetaPdf(?string $etiquetaPdf): void
    {
        $this->etiquetaPdf = $etiquetaPdf;
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
            'data_comunicado' => $this->dataComunicado,
            'arquivo_pdf' => $this->arquivoPdf,
            'etiqueta_pdf' => $this->etiquetaPdf,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}


