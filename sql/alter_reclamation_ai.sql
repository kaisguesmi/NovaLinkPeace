-- Adds AI assessment fields to reclamation table
ALTER TABLE reclamation
    ADD COLUMN ai_score DECIMAL(5,2) NULL AFTER id_commentaire_cible,
    ADD COLUMN ai_analysis TEXT NULL AFTER ai_score,
    ADD COLUMN ai_model VARCHAR(50) DEFAULT 'heuristic-v1' AFTER ai_analysis,
    ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER ai_model;
