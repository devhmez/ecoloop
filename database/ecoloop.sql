-- Base de données EcoLoop : module Utilisateurs
DROP DATABASE IF EXISTS ecoloop;
CREATE DATABASE ecoloop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecoloop;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'citoyen',
    points INT NOT NULL DEFAULT 0,
    reset_token VARCHAR(64) NULL,
    reset_expires DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Compte administrateur de démonstration : admin@ecoloop.tn / Admin123
INSERT INTO users (name, email, password, role) VALUES
('Admin EcoLoop', 'admin@ecoloop.tn', '$2y$12$6y4xCyCXuh/9Xbi7wyF6YuEiCju4ibKW5MYY0Tfdqgj3KzwR7diWW', 'admin');

-- Citoyens de démonstration (mot de passe : Citoyen123)
INSERT INTO users (name, email, password, role, points) VALUES
('Sami Ben Ali', 'sami@ecoloop.tn', '$2y$12$RxQRvWU3ZffmsHkz1xl.8OSeyMz7wF.cnscEE1HdWbG95RftcUVXm', 'citoyen', 120),
('Lina Trabelsi', 'lina@ecoloop.tn', '$2y$12$RxQRvWU3ZffmsHkz1xl.8OSeyMz7wF.cnscEE1HdWbG95RftcUVXm', 'citoyen', 45);
