-- Minimal creation script for histoires / reclamations
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
