-- EcoLoop : pickups et véhicules (MySQL / MariaDB)
CREATE DATABASE IF NOT EXISTS ecoloop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecoloop;

DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS collection_categories;
DROP TABLE IF EXISTS collections;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS collectors;

CREATE TABLE collectors (
  id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name   VARCHAR(100) NOT NULL UNIQUE,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE vehicles (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(20) NOT NULL UNIQUE,            -- ex. T-14, V-02
  type        ENUM('Truck','Van') NOT NULL DEFAULT 'Truck',
  capacity_kg INT UNSIGNED NOT NULL DEFAULT 1000,
  status      ENUM('Available','Maintenance') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB;

CREATE TABLE collections (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code         VARCHAR(20) NULL UNIQUE,                -- ex. C-1049
  owner_name   VARCHAR(100) NOT NULL,
  pickup_date  DATE NOT NULL,
  slot         VARCHAR(20) NOT NULL,
  address      VARCHAR(255) NOT NULL,
  note         TEXT NULL,
  status       ENUM('Scheduled','On the way','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  collector_id INT UNSIGNED NULL,
  vehicle_id   INT UNSIGNED NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_col_collector FOREIGN KEY (collector_id) REFERENCES collectors(id) ON DELETE SET NULL,
  CONSTRAINT fk_col_vehicle   FOREIGN KEY (vehicle_id)   REFERENCES vehicles(id)   ON DELETE SET NULL,
  INDEX idx_vehicle_slot (vehicle_id, pickup_date, slot),
  INDEX idx_owner (owner_name)
) ENGINE=InnoDB;

CREATE TABLE collection_categories (
  collection_id INT UNSIGNED NOT NULL,
  category      ENUM('plastic','paper','glass','metal','organic','electronics') NOT NULL,
  PRIMARY KEY (collection_id, category),
  CONSTRAINT fk_cc_collection FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Comptes de connexion (admin, chauffeur, citoyen). Un chauffeur est lié à un collecteur.
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','driver','citizen') NOT NULL,
  collector_id  INT UNSIGNED NULL UNIQUE,              -- renseigné pour les chauffeurs
  active        TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_collector FOREIGN KEY (collector_id) REFERENCES collectors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Données de départ (reprises du prototype)
INSERT INTO collectors (name) VALUES ('Hichem Mansouri'), ('Nour Gharbi'), ('Amine Jlassi');

INSERT INTO vehicles (code, type, capacity_kg, status) VALUES
  ('T-09', 'Truck', 2500, 'Available'),
  ('T-14', 'Truck', 3000, 'Available'),
  ('V-02', 'Van',    900, 'Available'),
  ('V-05', 'Van',    900, 'Maintenance');

INSERT INTO collections (id, code, owner_name, pickup_date, slot, address, status, collector_id, vehicle_id) VALUES
  (1, 'C-1049', 'Amira B.',  CURDATE(),                      '11:00 – 13:00', '12 Rue Ibn Khaldoun, Ariana', 'On the way', 1, 2),
  (2, 'C-1051', 'Amira B.',  DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00 – 11:00', '12 Rue Ibn Khaldoun, Ariana', 'Scheduled',  2, 1),
  (3, 'C-1052', 'Amira B.',  DATE_ADD(CURDATE(), INTERVAL 5 DAY), '14:00 – 16:00', '12 Rue Ibn Khaldoun, Ariana', 'Scheduled',  1, 2),
  (4, 'C-1040', 'Amira B.',  DATE_SUB(CURDATE(), INTERVAL 7 DAY), '09:00 – 11:00', '12 Rue Ibn Khaldoun, Ariana', 'Completed',  2, 1),
  (5, 'C-1027', 'Amira B.',  DATE_SUB(CURDATE(), INTERVAL 27 DAY),'09:00 – 11:00', '12 Rue Ibn Khaldoun, Ariana', 'Completed',  3, 3),
  (6, 'C-1053', 'Sana K.',   CURDATE(),                      '09:00 – 11:00', 'Rue de Marseille, Lac 1',        'On the way', 3, 3),
  (7, 'C-1054', 'Mehdi B.',  DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00 – 16:00', 'Rue Habib Bourguiba, Ariana',   'Scheduled',  NULL, NULL),
  (8, 'C-1055', 'Ines T.',   DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00 – 11:00', 'Avenue Taieb Mhiri, La Marsa',  'Scheduled',  NULL, NULL);

INSERT INTO collection_categories (collection_id, category) VALUES
  (1,'plastic'),(1,'metal'),(2,'plastic'),(2,'paper'),(3,'glass'),(4,'paper'),
  (5,'electronics'),(6,'organic'),(7,'paper'),(7,'plastic'),(8,'glass');

-- Les comptes de démonstration (admin, chauffeurs, citoyen) sont créés par install_users.php
