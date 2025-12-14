-- phpMyAdmin SQL Dump
-- Base de données complète : peacelink avec module gestion des offres
-- Généré le : sam. 13 déc. 2025
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `peacelink`
--
CREATE DATABASE IF NOT EXISTS `peacelink` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `peacelink`;

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe_hash` varchar(255) NOT NULL,
  `niveau_permission` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id_admin`, `email`, `mot_de_passe_hash`, `niveau_permission`) VALUES
(65, 'kapoxxkaisxx@gmail.com', 'AZERqsdf123456789$$', 1),
(67, 'admin@peacelink.test', '$2y$10$.7AUCYUPl1JbtZ1iEZvZku9BQoZbdn..1i.QQN5xBAXlzF17BC4yO', 5);

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
  `id_utilisateur` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`id_utilisateur`, `nom_complet`, `bio`) VALUES
(12, 'Sami', 'Citoyen engagé'),
(60, 'kais guesmi', 'azertyuiopsdfgh'),
(71, 'kais guesmi', 'azertyuiopsdfghjkl'),
(72, 'kais guesmi', 'azertyuiop');

-- --------------------------------------------------------

--
-- Structure de la table `expert` (Clients devenus experts après acceptation)
--

CREATE TABLE `expert` (
  `id_utilisateur` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `specialite` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `date_devenu_expert` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `capacity` int(11) NOT NULL,
  `description` text NOT NULL,
  `status` enum('en_attente','validé','refusé') DEFAULT 'en_attente',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `org_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `events`
--

INSERT INTO `events` (`id`, `title`, `category`, `location`, `date`, `capacity`, `description`, `status`, `created_by`, `created_at`, `org_id`) VALUES
(1, 'Nettoyage de la plage', 'Écologie', 'Tunis', '2025-07-12', 100, 'Journée de nettoyage des plages avec tri des déchets.', 'validé', 50, '2025-12-13 14:21:30', 50),
(2, 'dfghjkl', 'Écologie', 'dfghjklmù', '2025-12-17', 20, 'aqzsedrtfhyupoqsdfgthjukmlù^dfgvhjnm:ù!', 'en_attente', 62, '2025-12-13 16:59:16', 62);

-- --------------------------------------------------------

--
-- Structure de la table `organisation`
--

CREATE TABLE `organisation` (
  `id_utilisateur` int(11) NOT NULL,
  `nom_organisation` varchar(255) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `statut_verification` varchar(50) NOT NULL DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `organisation`
--

INSERT INTO `organisation` (`id_utilisateur`, `nom_organisation`, `adresse`, `statut_verification`) VALUES
(50, 'GreenEarth', 'Tunis', 'Verifié'),
(62, 'kais', 'jardien el manzah 2', 'Verifié');

-- --------------------------------------------------------

--
-- Structure de la table `participations`
--

CREATE TABLE `participations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `participations`
--

INSERT INTO `participations` (`id`, `event_id`, `id_client`, `message`, `created_at`, `full_name`, `email`) VALUES
(1, 1, 12, 'Je viens aider avec des sacs et des gants.', '2025-12-13 14:21:30', 'Sami', 'sami@example.com'),
(2, 1, 71, 'nhib nji', '2025-12-13 17:31:28', NULL, NULL),
(3, 1, 72, 'azertyuiopqsdfghjk', '2025-12-13 18:00:44', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe_hash` varchar(255) NOT NULL,
  `date_inscription` datetime NOT NULL DEFAULT current_timestamp(),
  `photo_profil` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `est_banni` tinyint(1) DEFAULT 0,
  `raison_bannissement` text DEFAULT NULL,
  `date_fin_bannissement` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `email`, `mot_de_passe_hash`, `date_inscription`, `photo_profil`, `reset_token`, `reset_expires`, `est_banni`, `raison_bannissement`, `date_fin_bannissement`) VALUES
(12, 'sami@example.com', '$2y$10$Zt0K8GZ98PBZJKp5fV54jOuQj0MkWAMzU9XMu8xV.70p24XLg8gdq', '2025-01-05 10:00:00', NULL, NULL, NULL, 0, NULL, NULL),
(50, 'greenearth@example.com', '$2y$10$Wszrj/ubly0P/5gH7R1HjelYl5AY7ICTYx7OH9tuXdN47t9brCeqe', '2025-01-04 09:00:00', NULL, NULL, NULL, 0, NULL, NULL),
(60, 'kapoxxkaisxx@gmail.com', '$2y$10$FJS.wcOvzI2nDiE9EohH6eeg7wzFxNVmPNXMzLpKnfZSNHCZI/BOO', '2025-12-13 15:21:52', NULL, NULL, NULL, 0, NULL, NULL),
(62, 'kadynaxxkapoxx@gmail.com', '$2y$10$vYtP8.nMCK6I5OJwCEqncOLY.HnzhEHw2BJXaKWg1JzYI8mw71eLS', '2025-12-13 15:56:33', '789c45e84e00641859ff1b91d9ac823e.png', '114223288acd443075a693c8c2a52209853f98f7468f5d1c7e4fef1e3ced05a0', '2025-12-13 18:10:30', 0, NULL, NULL),
(66, 'admin@example.com', '$2y$10$abcdefghijklmnopqrstuv', '2025-12-13 16:18:20', NULL, NULL, NULL, 0, NULL, NULL),
(69, 'admin@peacelink.test', '$2y$10$2cWyZS4nVrLOT9K6x6tEN.t9EjoPp5h7xMT3XEfVbnxDCdiAloMIO', '2025-12-13 16:38:25', NULL, NULL, NULL, 0, NULL, NULL),
(71, 'kais@gmail.com', '$2y$10$9mQADU1WtNkB0Kr.j2whMuM/WpKjgcKrFX7ddiIOS0h3KFnqMI5Dy', '2025-12-13 17:26:26', NULL, NULL, NULL, 0, NULL, NULL),
(72, 'kadyna123@gmail.com', '$2y$10$FWm5NAXz4dq2FYlbteGHbeeRLKXIfGgn16NsQlcELnxkOwNjNpCba', '2025-12-13 17:39:04', 'bf6dad047fdb52bdef08377aa3610d94.png', NULL, NULL, 0, NULL, NULL);

-- --------------------------------------------------------
-- ========================================================
-- MODULE GESTION DES OFFRES
-- ========================================================

--
-- Structure de la table `offers` (Offres créées par les organisations)
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('en_attente','ouverte','cloturee','pause','publiée') DEFAULT 'publiée',
  `created_at` datetime DEFAULT current_timestamp(),
  `max_candidates` int(11) DEFAULT 10,
  `keywords` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `offers`
--

INSERT INTO `offers` (`id`, `org_id`, `title`, `description`, `status`, `created_at`, `max_candidates`, `keywords`) VALUES
(1, 50, 'Coordinateur de projet écologique', 'Nous recherchons un coordinateur passionné pour superviser nos initiatives de reforestation. Vous serez responsable de la planification, de la coordination des bénévoles et du suivi des objectifs environnementaux.', 'publiée', '2025-12-13 20:00:00', 5, 'écologie, coordination, gestion de projet, environnement, reforestation'),
(2, 62, 'Développeur Web Full Stack', 'Rejoignez notre équipe pour développer des solutions web innovantes pour la paix et l\'environnement. Expérience en PHP, MySQL, JavaScript requise.', 'publiée', '2025-12-13 20:05:00', 3, 'développement, web, PHP, JavaScript, MySQL, fullstack');

-- --------------------------------------------------------

--
-- Structure de la table `applications` (Candidatures des clients)
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `offer_id` int(11) NOT NULL,
  `candidate_id` int(11) DEFAULT NULL,
  `candidate_name` varchar(255) NOT NULL,
  `candidate_email` varchar(255) NOT NULL,
  `motivation` text NOT NULL,
  `status` enum('en_attente','acceptée','refusée') DEFAULT 'en_attente',
  `score` int(11) DEFAULT 0,
  `sentiment` varchar(50) DEFAULT 'Neutre',
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `applications` (Exemples)
--

INSERT INTO `applications` (`id`, `offer_id`, `candidate_id`, `candidate_name`, `candidate_email`, `motivation`, `status`, `score`, `sentiment`, `submitted_at`) VALUES
(1, 1, 12, 'Sami', 'sami@example.com', 'Je suis passionné par l\'écologie et j\'ai une forte expérience en gestion de projet. J\'ai coordonné plusieurs initiatives de reforestation dans ma région et je maîtrise les techniques de gestion d\'équipes bénévoles. Mon engagement pour l\'environnement est total et je souhaite mettre mes compétences au service de votre organisation.', 'en_attente', 85, 'Confiant', '2025-12-13 20:10:00');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `uq_admin_email` (`email`);

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- Index pour la table `expert`
--
ALTER TABLE `expert`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- Index pour la table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_events_creator` (`created_by`),
  ADD KEY `fk_events_org` (`org_id`);

--
-- Index pour la table `organisation`
--
ALTER TABLE `organisation`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- Index pour la table `participations`
--
ALTER TABLE `participations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_participation_client` (`event_id`,`id_client`),
  ADD UNIQUE KEY `uq_participation_email` (`event_id`,`email`),
  ADD KEY `fk_participations_client` (`id_client`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `uq_utilisateur_email` (`email`);

--
-- Index pour la table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_offers_org` (`org_id`),
  ADD KEY `idx_offers_status` (`status`);

--
-- Index pour la table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_application_client_offer` (`offer_id`,`candidate_id`),
  ADD KEY `fk_app_offer` (`offer_id`),
  ADD KEY `fk_app_candidate` (`candidate_id`),
  ADD KEY `idx_applications_status` (`status`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT pour la table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `participations`
--
ALTER TABLE `participations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT pour la table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `fk_client_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `expert`
--
ALTER TABLE `expert`
  ADD CONSTRAINT `fk_expert_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_creator` FOREIGN KEY (`created_by`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_events_org` FOREIGN KEY (`org_id`) REFERENCES `organisation` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `organisation`
--
ALTER TABLE `organisation`
  ADD CONSTRAINT `fk_organisation_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `participations`
--
ALTER TABLE `participations`
  ADD CONSTRAINT `fk_participations_client` FOREIGN KEY (`id_client`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_participations_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `offers`
--
ALTER TABLE `offers`
  ADD CONSTRAINT `fk_offers_org` FOREIGN KEY (`org_id`) REFERENCES `organisation` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `fk_app_offer` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_app_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `client` (`id_utilisateur`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
