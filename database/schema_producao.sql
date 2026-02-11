-- =============================================================================
-- Sistema de Gerenciamento de Vendas e Comunicações de Motocicletas
-- Schema completo para produção
-- =============================================================================
-- Como rodar:
--   mysql -u USUARIO -p < database/schema_producao.sql
-- ou importar pelo phpMyAdmin / cliente MySQL de sua preferência.
--
-- Ajuste o nome do banco e o usuário conforme seu .env (DB_DATABASE, DB_USERNAME).
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Banco de dados
-- -----------------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS motos_vendas
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE motos_vendas;

-- -----------------------------------------------------------------------------
-- Tabela: clientes
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    whatsapp VARCHAR(20),
    endereco TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabela: veiculos
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(8) NOT NULL UNIQUE,
    modelo VARCHAR(255) NOT NULL,
    ano INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_placa (placa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabela: intencoes_venda
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS intencoes_venda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    veiculo_id INT NOT NULL,
    numero_crv VARCHAR(50),
    codigo_seguranca_crv VARCHAR(50),
    status ENUM('rascunho', 'gerado', 'finalizado') DEFAULT 'rascunho',
    arquivo_pdf VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_veiculo (veiculo_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabela: comunicados_venda
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS comunicados_venda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    veiculo_id INT NOT NULL,
    data_comunicado DATE NOT NULL,
    arquivo_pdf VARCHAR(500),
    etiqueta_pdf VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_veiculo (veiculo_id),
    INDEX idx_data_comunicado (data_comunicado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabela: vendas_autoconf (snapshot para não perder histórico quando moto sai do Autoconf)
-- -----------------------------------------------------------------------------
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

-- -----------------------------------------------------------------------------
-- Tabela: logs_integracao
-- -----------------------------------------------------------------------------
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

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Fim do schema
-- =============================================================================
