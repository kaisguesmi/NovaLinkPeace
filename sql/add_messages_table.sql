-- Création de la table pour les messages privés entre experts et clients
-- Les experts peuvent contacter les clients qui publient des histoires

CREATE TABLE IF NOT EXISTS message_prive (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_expert INT NOT NULL,
    id_client INT NOT NULL,
    id_histoire INT NULL,  -- L'histoire qui a motivé le contact (optionnel)
    contenu TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT FALSE,
    
    -- Contraintes de clés étrangères
    CONSTRAINT fk_message_expert FOREIGN KEY (id_expert) REFERENCES Expert(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_message_client FOREIGN KEY (id_client) REFERENCES Client(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_message_histoire FOREIGN KEY (id_histoire) REFERENCES histoire(id_histoire) ON DELETE SET NULL,
    
    -- Index pour améliorer les performances
    INDEX idx_expert_client (id_expert, id_client),
    INDEX idx_client_messages (id_client, date_envoi),
    INDEX idx_unread (id_client, lu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pour gérer les conversations (pour grouper les messages entre un expert et un client)
CREATE TABLE IF NOT EXISTS conversation (
    id_conversation INT AUTO_INCREMENT PRIMARY KEY,
    id_expert INT NOT NULL,
    id_client INT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    derniere_activite DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_conv_expert FOREIGN KEY (id_expert) REFERENCES Expert(id_utilisateur) ON DELETE CASCADE,
    CONSTRAINT fk_conv_client FOREIGN KEY (id_client) REFERENCES Client(id_utilisateur) ON DELETE CASCADE,
    
    -- Une conversation unique par paire expert-client
    UNIQUE KEY unique_conversation (id_expert, id_client),
    INDEX idx_client_conversations (id_client, derniere_activite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modifier la table message_prive pour ajouter la référence à conversation
ALTER TABLE message_prive 
ADD COLUMN id_conversation INT NULL AFTER id_client,
ADD CONSTRAINT fk_message_conversation FOREIGN KEY (id_conversation) REFERENCES conversation(id_conversation) ON DELETE CASCADE;
