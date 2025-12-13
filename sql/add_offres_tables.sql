-- Script pour ajouter les tables de gestion des offres à la base peacelink
USE peacelink;

-- Table des offres (créées par les organisations)
CREATE TABLE IF NOT EXISTS offers (
    id INT NOT NULL AUTO_INCREMENT,
    id_organisation INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'publiée',
    max_candidates INT NOT NULL DEFAULT 10,
    keywords VARCHAR(500) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (id),
    CONSTRAINT fk_offers_organisation FOREIGN KEY (id_organisation) 
        REFERENCES Organisation(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des candidatures (soumises par les clients)
CREATE TABLE IF NOT EXISTS applications (
    id INT NOT NULL AUTO_INCREMENT,
    offer_id INT NOT NULL,
    id_client INT NOT NULL,
    candidate_name VARCHAR(255) NOT NULL,
    candidate_email VARCHAR(255) NOT NULL,
    motivation TEXT NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'en attente',
    score INT DEFAULT 0,
    sentiment VARCHAR(50) DEFAULT 'Neutre',
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (id),
    UNIQUE KEY uq_application_client_offer (offer_id, id_client),
    CONSTRAINT fk_applications_offer FOREIGN KEY (offer_id) 
        REFERENCES offers(id) ON DELETE CASCADE,
    CONSTRAINT fk_applications_client FOREIGN KEY (id_client) 
        REFERENCES Client(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Index pour améliorer les performances
CREATE INDEX idx_offers_organisation ON offers(id_organisation);
CREATE INDEX idx_offers_status ON offers(status);
CREATE INDEX idx_applications_offer ON applications(offer_id);
CREATE INDEX idx_applications_client ON applications(id_client);
CREATE INDEX idx_applications_status ON applications(status);

-- Exemple d'offre créée par GreenEarth (id_utilisateur = 50)
INSERT INTO offers (id_organisation, title, description, status, max_candidates, keywords, created_at)
VALUES 
    (50, 'Coordinateur de projet écologique', 
     'Nous recherchons un coordinateur passionné pour superviser nos initiatives de reforestation.', 
     'publiée', 5, 
     'écologie, coordination, gestion de projet, environnement', 
     NOW());

-- Auto increment
ALTER TABLE offers AUTO_INCREMENT = 2;
ALTER TABLE applications AUTO_INCREMENT = 1;
