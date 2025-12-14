-- Unified PeaceLink schema (users + initiatives)
-- Database: peacelink

CREATE DATABASE IF NOT EXISTS peacelink CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE peacelink;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = '+00:00';

-- Core user tables --------------------------------------------------------
CREATE TABLE IF NOT EXISTS Utilisateur (
  id_utilisateur INT NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  mot_de_passe_hash VARCHAR(255) NOT NULL,
  date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  photo_profil VARCHAR(255) DEFAULT NULL,
  reset_token VARCHAR(255) DEFAULT NULL,
  reset_expires DATETIME DEFAULT NULL,
  est_banni TINYINT(1) DEFAULT 0,
  raison_bannissement TEXT DEFAULT NULL,
  date_fin_bannissement DATETIME DEFAULT NULL,
  PRIMARY KEY (id_utilisateur),
  UNIQUE KEY uq_utilisateur_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS admin (
  id_admin INT NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  mot_de_passe_hash VARCHAR(255) NOT NULL,
  niveau_permission INT NOT NULL DEFAULT 1,
  PRIMARY KEY (id_admin),
  UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS Client (
  id_utilisateur INT NOT NULL,
  nom_complet VARCHAR(255) NOT NULL,
  bio TEXT DEFAULT NULL,
  PRIMARY KEY (id_utilisateur),
  CONSTRAINT fk_client_user FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS Organisation (
  id_utilisateur INT NOT NULL,
  nom_organisation VARCHAR(255) NOT NULL,
  adresse VARCHAR(255) NOT NULL,
  statut_verification VARCHAR(50) NOT NULL DEFAULT 'en_attente',
  PRIMARY KEY (id_utilisateur),
  CONSTRAINT fk_organisation_user FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Initiatives -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
  id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  category VARCHAR(100) NOT NULL,
  location VARCHAR(255) NOT NULL,
  date DATE NOT NULL,
  capacity INT NOT NULL,
  description TEXT NOT NULL,
  status ENUM('en_attente','validé','refusé') DEFAULT 'en_attente',
  created_by INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  org_id INT NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_events_creator FOREIGN KEY (created_by) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
  CONSTRAINT fk_events_org FOREIGN KEY (org_id) REFERENCES Organisation(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Participations ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS participations (
  id INT NOT NULL AUTO_INCREMENT,
  event_id INT NOT NULL,
  id_client INT NOT NULL,
  message TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
  full_name VARCHAR(100) DEFAULT NULL,
  email VARCHAR(150) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_participation_client (event_id, id_client),
  UNIQUE KEY uq_participation_email (event_id, email),
  CONSTRAINT fk_participations_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  CONSTRAINT fk_participations_client FOREIGN KEY (id_client) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed data aligned with the scenario ------------------------------------
INSERT INTO Utilisateur (id_utilisateur, email, mot_de_passe_hash, date_inscription, est_banni)
VALUES
  (12, 'sami@example.com', '$2y$10$Zt0K8GZ98PBZJKp5fV54jOuQj0MkWAMzU9XMu8xV.70p24XLg8gdq', '2025-01-05 10:00:00', 0),
  (50, 'greenearth@example.com', '$2y$10$Wszrj/ubly0P/5gH7R1HjelYl5AY7ICTYx7OH9tuXdN47t9brCeqe', '2025-01-04 09:00:00', 0);

-- Compte administrateur seedé (mot de passe : Admin!234)
INSERT INTO admin (id_admin, email, mot_de_passe_hash, niveau_permission)
VALUES (1, 'admin@peacelink.test', '$2y$10$2cWyZS4nVrLOT9K6x6tEN.t9EjoPp5h7xMT3XEfVbnxDCdiAloMIO', 1);

INSERT INTO Organisation (id_utilisateur, nom_organisation, adresse, statut_verification)
VALUES
  (50, 'GreenEarth', 'Tunis', 'Verifié');

INSERT INTO Client (id_utilisateur, nom_complet, bio)
VALUES
  (12, 'Sami', 'Citoyen engagé');

-- Exemple d'initiative créée par GreenEarth
INSERT INTO events (id, title, category, location, date, capacity, description, status, created_by, created_at, org_id)
VALUES
  (1, 'Nettoyage de la plage', 'Écologie', 'Tunis', '2025-07-12', 100, 'Journée de nettoyage des plages avec tri des déchets.', 'validé', 50, NOW(), 50);

-- Exemple de participation de Sami
INSERT INTO participations (event_id, id_client, message, full_name, email)
VALUES
  (1, 12, 'Je viens aider avec des sacs et des gants.', 'Sami', 'sami@example.com');

-- Auto increment adjustments
ALTER TABLE Utilisateur AUTO_INCREMENT = 60;
ALTER TABLE admin AUTO_INCREMENT = 2;
ALTER TABLE events AUTO_INCREMENT = 2;
ALTER TABLE participations AUTO_INCREMENT = 2;
