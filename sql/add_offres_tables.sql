-- Script pour ajouter/mettre à jour les tables de gestion des offres à la base peacelink
USE peacelink;

-- Table des offres (déjà existante, on ajoute juste la colonne org_id si elle n'existe pas)
-- ALTER TABLE offers ADD COLUMN IF NOT EXISTS org_id INT NULL;
-- ALTER TABLE offers ADD CONSTRAINT fk_offers_org FOREIGN KEY (org_id) REFERENCES organisation(id_utilisateur) ON DELETE SET NULL;

-- Table des candidatures (déjà existante, on ajoute juste la colonne candidate_id si elle n'existe pas)
-- ALTER TABLE applications ADD COLUMN IF NOT EXISTS candidate_id INT NULL;
-- ALTER TABLE applications ADD CONSTRAINT fk_app_candidate FOREIGN KEY (candidate_id) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL;

-- Index pour améliorer les performances
CREATE INDEX IF NOT EXISTS idx_offers_org ON offers(org_id);
CREATE INDEX IF NOT EXISTS idx_offers_status ON offers(status);
CREATE INDEX IF NOT EXISTS idx_applications_offer ON applications(offer_id);
CREATE INDEX IF NOT EXISTS idx_applications_candidate ON applications(candidate_id);
CREATE INDEX IF NOT EXISTS idx_applications_status ON applications(status);

-- Exemple d'offre créée par GreenEarth (id_utilisateur = 50)
-- INSERT INTO offers (org_id, title, description, status, max_candidates, keywords, created_at)
-- VALUES 
--     (50, 'Coordinateur de projet écologique', 
--      'Nous recherchons un coordinateur passionné pour superviser nos initiatives de reforestation.', 
--      'publiée', 5, 
--      'écologie, coordination, gestion de projet, environnement', 
--      NOW());
