-- Arquivo para inicialização do banco MySQL no Docker
-- Este arquivo é executado quando o container MySQL é criado

-- Criar o banco de dados se não existir
CREATE DATABASE IF NOT EXISTS tiao_carreiro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Garantir que o usuário laravel tenha as permissões corretas
GRANT ALL PRIVILEGES ON tiao_carreiro.* TO 'laravel'@'%';
FLUSH PRIVILEGES;