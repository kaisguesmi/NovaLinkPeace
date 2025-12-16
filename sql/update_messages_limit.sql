-- Ajouter les champs pour gérer la limite de 5 messages et le statut de la conversation

ALTER TABLE conversation 
ADD COLUMN message_count INT DEFAULT 0 AFTER derniere_activite,
ADD COLUMN statut ENUM('ouverte', 'fermee') DEFAULT 'ouverte' AFTER message_count;

-- Index pour filtrer par statut
ALTER TABLE conversation ADD INDEX idx_statut (statut);
