-- Module 4 : Centres de recyclage (tables centre et centre_categorie)
-- A importer dans phpMyAdmin APRES database/ecoloop.sql (ne supprime rien, peut etre relance sans danger).
USE ecoloop;

CREATE TABLE IF NOT EXISTS centre (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    adresse VARCHAR(255) NOT NULL,
    ville VARCHAR(80) NOT NULL,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL,
    horaires VARCHAR(80) NOT NULL,
    telephone VARCHAR(20) NULL,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Matieres acceptees par chaque centre (liste des matieres definie dans models/Centre.php)
CREATE TABLE IF NOT EXISTS centre_categorie (
    centre_id INT NOT NULL,
    categorie VARCHAR(20) NOT NULL,
    PRIMARY KEY (centre_id, categorie),
    FOREIGN KEY (centre_id) REFERENCES centre(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Centres de demonstration (noms, adresses et horaires fictifs)
INSERT IGNORE INTO centre (nom, adresse, ville, latitude, longitude, horaires, telephone, actif) VALUES
('EcoPoint Ariana Centre', 'Avenue Habib Bourguiba', 'Ariana', 36.866000, 10.165000, 'Lun-Sam 08:00-18:00', '+216 00 000 001', 1),
('Lac Green Hub', 'Rue du Lac Biwa', 'Les Berges du Lac', 36.833000, 10.233000, 'Tous les jours 09:00-20:00', '+216 00 000 002', 1),
('Menzah Recycle Station', 'Avenue Taieb Mhiri', 'El Menzah', 36.843000, 10.176000, 'Lun-Ven 07:30-17:00', '+216 00 000 003', 1),
('Marsa Glass & Paper Depot', 'Rue Hedi Chaker', 'La Marsa', 36.878000, 10.325000, 'Mar-Dim 08:00-16:00', '+216 00 000 004', 1),
('Ben Arous E-Waste Lab', 'Zone industrielle', 'Ben Arous', 36.753000, 10.219000, 'Lun-Sam 09:00-17:00', '+216 00 000 005', 0),
('Carthage Compost Garden', 'Route de la Goulette', 'Carthage', 36.853000, 10.323000, 'Mer-Sam 08:00-14:00', '+216 00 000 006', 1);

INSERT IGNORE INTO centre_categorie (centre_id, categorie)
SELECT c.id, m.categorie FROM centre c JOIN (
    SELECT 'EcoPoint Ariana Centre' AS nom, 'plastique' AS categorie UNION ALL
    SELECT 'EcoPoint Ariana Centre', 'papier' UNION ALL
    SELECT 'EcoPoint Ariana Centre', 'verre' UNION ALL
    SELECT 'EcoPoint Ariana Centre', 'metal' UNION ALL
    SELECT 'Lac Green Hub', 'plastique' UNION ALL
    SELECT 'Lac Green Hub', 'verre' UNION ALL
    SELECT 'Lac Green Hub', 'metal' UNION ALL
    SELECT 'Lac Green Hub', 'electronique' UNION ALL
    SELECT 'Menzah Recycle Station', 'papier' UNION ALL
    SELECT 'Menzah Recycle Station', 'plastique' UNION ALL
    SELECT 'Menzah Recycle Station', 'metal' UNION ALL
    SELECT 'Marsa Glass & Paper Depot', 'verre' UNION ALL
    SELECT 'Marsa Glass & Paper Depot', 'papier' UNION ALL
    SELECT 'Ben Arous E-Waste Lab', 'electronique' UNION ALL
    SELECT 'Ben Arous E-Waste Lab', 'metal' UNION ALL
    SELECT 'Ben Arous E-Waste Lab', 'encombrants' UNION ALL
    SELECT 'Carthage Compost Garden', 'organique'
) m ON m.nom = c.nom;
