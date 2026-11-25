-- Base de données EcoLoop : module Utilisateurs
DROP DATABASE IF EXISTS ecoloop;
CREATE DATABASE ecoloop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecoloop;
SET NAMES utf8mb4;

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

-- =====================================================================
-- Module Scanner IA : matériaux (avec mots-clés reconnus par l'IA) et scans
-- =====================================================================
CREATE TABLE materiau (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(40) NOT NULL UNIQUE,
    poubelle VARCHAR(40) NOT NULL,
    couleur CHAR(7) NOT NULL,
    points INT NOT NULL DEFAULT 0,
    mots_cles VARCHAR(500) NOT NULL,
    conseil VARCHAR(255) NOT NULL
);

CREATE TABLE scan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    materiau_id INT NOT NULL,
    objet VARCHAR(120) NOT NULL,
    confiance TINYINT NOT NULL,
    points INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (materiau_id) REFERENCES materiau(id)
);

-- Les mots-clés sont en anglais car l'IA (MobileNet) donne ses résultats en anglais
INSERT INTO materiau (nom, poubelle, couleur, points, mots_cles, conseil) VALUES
('Plastique', 'Poubelle jaune', '#f2b705', 5, 'plastic, water bottle, pop bottle, soda bottle, water jug, pill bottle, packet, plastic bag, lotion, sunscreen, bucket', 'Vide la bouteille et écrase-la avant de la jeter.'),
('Verre', 'Poubelle verte', '#27ae60', 4, 'wine bottle, beer bottle, beer glass, goblet, vase, jar, perfume, whiskey jug, red wine', 'Pas besoin de laver le verre, enlève juste le bouchon.'),
('Métal', 'Poubelle grise', '#7f8c8d', 6, 'can, tin, milk can, can opener, frying pan, wok, ladle, spatula, padlock, nail, screw', 'Les canettes et boîtes de conserve se recyclent à l''infini.'),
('Papier et carton', 'Poubelle bleue', '#2d7dd2', 3, 'carton, envelope, paper, book jacket, comic book, menu, newspaper, crossword, toilet tissue, paper towel, binder', 'Aplatis les cartons pour gagner de la place.'),
('Organique', 'Composteur', '#8d6e63', 2, 'banana, orange, lemon, granny smith, apple, strawberry, pineapple, pomegranate, fig, corn, broccoli, cauliflower, cabbage, mushroom, cucumber, bell pepper, pizza, bagel, french loaf, pretzel', 'Les restes de fruits et légumes deviennent du compost.'),
('Électronique', 'Point de collecte DEEE', '#8e44ad', 8, 'cellular telephone, cellphone, ipod, remote control, mouse, computer keyboard, laptop, notebook computer, hand-held computer, modem, hard disc, joystick', 'Ne le jette jamais à la poubelle : dépose-le dans un point de collecte.');

-- Quelques scans de démonstration
INSERT INTO scan (user_id, materiau_id, objet, confiance, points, created_at) VALUES
(2, 1, 'water bottle', 91, 5, NOW() - INTERVAL 3 DAY),
(2, 4, 'carton', 78, 3, NOW() - INTERVAL 2 DAY),
(3, 2, 'wine bottle', 85, 4, NOW() - INTERVAL 1 DAY),
(2, 3, 'can opener', 64, 6, NOW() - INTERVAL 5 HOUR);
