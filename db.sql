-- ============================================================
-- Banco de Dados: Dia das Crianças - Evento Promocional
-- ============================================================

CREATE DATABASE IF NOT EXISTS diadascriancas
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE diadascriancas;

-- -----------------------------------------------------------
-- Tabela: admins
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Tabela: photos
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS photos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  photo_name VARCHAR(255) NOT NULL COMMENT 'Nome real da pessoa na foto',
  photo_filename VARCHAR(255) NOT NULL COMMENT 'Arquivo da foto em /uploads',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Tabela: participants
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS participants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL COMMENT 'Nome do participante',
  phone VARCHAR(20) NOT NULL UNIQUE COMMENT 'Celular (xx) xxxxx-xxxx',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Tabela: guesses
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS guesses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  participant_id INT NOT NULL,
  photo_id INT NOT NULL,
  guessed_name VARCHAR(255) NOT NULL,
  is_correct TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
  FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- Admin padrão
-- Usuário: admin  |  Senha: admin123
-- -----------------------------------------------------------
INSERT INTO admins (username, password_hash)
VALUES ('admin', '$2y$12$NyHKYw8qPeDgbhn/cpWIDu9q3GRN.i5s9EvNoU9WJMZ24IFIdjtJa');