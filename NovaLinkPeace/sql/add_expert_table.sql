-- Migration : Ajout de la table Expert
-- Permet de transformer automatiquement les clients acceptés en experts
-- Date: 2025-12-14

-- Création de la table Expert
CREATE TABLE IF NOT EXISTS `expert` (
  `id_utilisateur` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `specialite` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `date_devenu_expert` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Contrainte de clé étrangère
ALTER TABLE `expert`
  ADD CONSTRAINT `fk_expert_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;
