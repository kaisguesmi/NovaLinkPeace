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

-- ==========================================================
-- Module Histoires / Réclamations (adapté depuis Jasser)
-- Les FK pointent vers la table Utilisateur (existante).
-- ==========================================================

CREATE TABLE IF NOT EXISTS histoire (
  id_histoire INT NOT NULL AUTO_INCREMENT,
  titre VARCHAR(255) NOT NULL,
  contenu TEXT NOT NULL,
  statut VARCHAR(50) NOT NULL DEFAULT 'submitted',
  id_auteur INT NOT NULL,
  date_publication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id_histoire),
  KEY fk_histoire_auteur (id_auteur),
  CONSTRAINT fk_histoire_auteur FOREIGN KEY (id_auteur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS commentaire (
  id_commentaire INT NOT NULL AUTO_INCREMENT,
  contenu TEXT NOT NULL,
  date_publication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  id_utilisateur INT NOT NULL,
  id_histoire INT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (id_commentaire),
  KEY fk_commentaire_user (id_utilisateur),
  KEY fk_commentaire_histoire (id_histoire),
  CONSTRAINT fk_commentaire_user FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
  CONSTRAINT fk_commentaire_histoire FOREIGN KEY (id_histoire) REFERENCES histoire(id_histoire) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS cause_signalement (
  id_cause INT NOT NULL AUTO_INCREMENT,
  libelle VARCHAR(255) NOT NULL,
  PRIMARY KEY (id_cause)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO cause_signalement (id_cause, libelle) VALUES
  (1, 'Discours haineux'),
  (2, 'Spam'),
  (3, 'Violation des règles'),
  (4, 'Contenu sensible');

CREATE TABLE IF NOT EXISTS reclamation (
  id_reclamation INT NOT NULL AUTO_INCREMENT,
  description_personnalisee TEXT NOT NULL,
  statut VARCHAR(50) NOT NULL DEFAULT 'nouvelle',
  id_auteur INT NOT NULL,
  id_histoire_cible INT DEFAULT NULL,
  id_commentaire_cible INT DEFAULT NULL,
  PRIMARY KEY (id_reclamation),
  KEY fk_reclamation_auteur (id_auteur),
  KEY fk_reclamation_histoire (id_histoire_cible),
  KEY fk_reclamation_commentaire (id_commentaire_cible),
  CONSTRAINT fk_reclamation_auteur FOREIGN KEY (id_auteur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE,
  CONSTRAINT fk_reclamation_histoire FOREIGN KEY (id_histoire_cible) REFERENCES histoire(id_histoire) ON DELETE CASCADE,
  CONSTRAINT fk_reclamation_commentaire FOREIGN KEY (id_commentaire_cible) REFERENCES commentaire(id_commentaire) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS reclamation_cause (
  id_reclamation INT NOT NULL,
  id_cause INT NOT NULL,
  PRIMARY KEY (id_reclamation, id_cause),
  KEY fk_rc_cause (id_cause),
  CONSTRAINT fk_rc_reclamation FOREIGN KEY (id_reclamation) REFERENCES reclamation(id_reclamation) ON DELETE CASCADE,
  CONSTRAINT fk_rc_cause FOREIGN KEY (id_cause) REFERENCES cause_signalement(id_cause) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS notifications (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  title VARCHAR(190) NOT NULL DEFAULT 'Notification',
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `read` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_notifications_user_read (user_id, `read`),
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Reactions on stories
CREATE TABLE IF NOT EXISTS reaction_histoire (
  id_reaction INT NOT NULL AUTO_INCREMENT,
  id_histoire INT NOT NULL,
  id_utilisateur INT NOT NULL,
  type ENUM('like','dislike','love','laugh','angry') NOT NULL DEFAULT 'like',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id_reaction),
  KEY idx_reaction_story_user (id_histoire, id_utilisateur),
  KEY idx_reaction_story (id_histoire),
  CONSTRAINT fk_reaction_histoire_story FOREIGN KEY (id_histoire) REFERENCES histoire(id_histoire) ON DELETE CASCADE,
  CONSTRAINT fk_reaction_histoire_user FOREIGN KEY (id_utilisateur) REFERENCES Utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Auto-increment safety for new tables
ALTER TABLE histoire AUTO_INCREMENT = 1;
ALTER TABLE commentaire AUTO_INCREMENT = 1;
ALTER TABLE cause_signalement AUTO_INCREMENT = 5;
ALTER TABLE reclamation AUTO_INCREMENT = 1;
ALTER TABLE notifications AUTO_INCREMENT = 1;
ALTER TABLE reaction_histoire AUTO_INCREMENT = 1;
