-- Snapshot de vendas/veículos do Autoconf no nosso banco.
-- Quando a moto é vendida ela sai do Autoconf; esta tabela guarda uma cópia
-- para não perdermos o histórico (veículo + cliente vinculados).
CREATE TABLE IF NOT EXISTS vendas_autoconf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_autoconf VARCHAR(50) NOT NULL COMMENT 'ID da venda/veículo no Autoconf',
    veiculo_id INT NOT NULL,
    cliente_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_id_autoconf (id_autoconf),
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    INDEX idx_id_autoconf (id_autoconf),
    INDEX idx_veiculo_id (veiculo_id),
    INDEX idx_cliente_id (cliente_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
