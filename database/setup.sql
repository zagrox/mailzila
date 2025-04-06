-- Drop database if exists and create new one
DROP DATABASE IF EXISTS mailzila;
CREATE DATABASE mailzila;
USE mailzila;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    avatar_url VARCHAR(255),
    provider VARCHAR(50) NULL,
    provider_id VARCHAR(255) NULL,
    access_token VARCHAR(255) NULL,
    refresh_token VARCHAR(255) NULL,
    token_expires_at DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert admin user with password: Admin@123
INSERT INTO users (email, password, first_name, last_name, is_active) 
VALUES (
    'admin@mailzila.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin',
    'User',
    1
); 