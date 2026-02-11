CREATE TABLE IF NOT EXISTS logs_integracao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('autoconf', 'serpro', 'comven') NOT NULL,
    operacao VARCHAR(100) NOT NULL,
    parametros JSON,
    resposta JSON,
    status ENUM('sucesso', 'erro') NOT NULL,
    mensagem_erro TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


