-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2025 at 06:22 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `peacelink`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_utilisateur` int(11) NOT NULL,
  `niveau_permission` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_utilisateur`, `niveau_permission`) VALUES
(2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `candidature`
--

CREATE TABLE `candidature` (
  `id_candidature` int(11) NOT NULL,
  `motivation` text NOT NULL,
  `statut` varchar(50) NOT NULL DEFAULT 'pending',
  `id_client` int(11) NOT NULL,
  `id_offre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cause_signalement`
--

CREATE TABLE `cause_signalement` (
  `id_cause` int(11) NOT NULL,
  `libelle` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cause_signalement`
--

INSERT INTO `cause_signalement` (`id_cause`, `libelle`) VALUES
(1, 'Discours haineux'),
(2, 'Spam'),
(3, 'Violation des règles'),
(4, 'Contenu sensible');

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `id_utilisateur` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`id_utilisateur`, `nom_complet`, `bio`, `avatar`) VALUES
(1, 'sdfgdfdf dfgdf', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `commentaire`
--

CREATE TABLE `commentaire` (
  `id_commentaire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_publication` datetime NOT NULL DEFAULT current_timestamp(),
  `id_utilisateur` int(11) NOT NULL,
  `id_histoire` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `histoire`
--

CREATE TABLE `histoire` (
  `id_histoire` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `statut` varchar(50) NOT NULL DEFAULT 'submitted',
  `id_client` int(11) NOT NULL,
  `date_publication` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `initiative`
--

CREATE TABLE `initiative` (
  `id_initiative` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `statut` varchar(50) NOT NULL DEFAULT 'en_attente',
  `date_evenement` datetime NOT NULL,
  `id_createur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(190) NOT NULL DEFAULT 'Notification',
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `created_at`, `read`) VALUES
(1, 1, 'Post soumis', 'Votre post \"sdfsdfsd\" a été soumis et est en attente de modération.', '2025-12-13 19:34:00', 1),
(2, 1, 'Post soumis', 'Votre post \"sfsdfsdf\" a été soumis et est en attente de modération.', '2025-12-14 10:28:35', 1),
(3, 1, 'Post rejeté', 'Votre post \"Votre publication\" a été rejeté : Contenu inapproprié', '2025-12-14 10:28:54', 1),
(4, 1, 'Post approuvé', 'Votre post \"Votre publication\" a été approuvé et est maintenant visible.', '2025-12-14 10:28:55', 1),
(5, 1, 'Post soumis', 'Votre post \"fsdfsdfsdfsdfdssdfsd\" a été soumis et est en attente de modération.', '2025-12-14 13:00:47', 1),
(6, 1, 'Post soumis', 'Votre post \"sdfsd\" a été soumis et est en attente de modération.', '2025-12-14 13:22:57', 1),
(7, 1, 'Post rejeté', 'Votre post \"Votre publication\" a été rejeté : Contenu inapproprié', '2025-12-14 13:23:49', 1),
(8, 1, 'Post approuvé', 'Votre post \"Votre publication\" a été approuvé et est maintenant visible.', '2025-12-14 13:23:59', 1),
(9, 1, 'Publication supprimée', 'Votre publication a été supprimée par un administrateur pour non-respect de nos conditions d\'utilisation.', '2025-12-14 14:26:05', 1),
(10, 1, 'Publication supprimée', 'Votre publication a été supprimée par un administrateur pour non-respect de nos conditions d\'utilisation.', '2025-12-14 14:48:28', 1),
(11, 1, 'Post soumis', 'Votre post \"test 1\" a été soumis et est en attente de modération.', '2025-12-14 14:58:39', 1),
(12, 1, 'Post approuvé', 'Votre post \"Votre publication\" a été approuvé et est maintenant visible.', '2025-12-14 14:58:46', 1),
(13, 1, 'Publication supprimée', 'Votre publication a été supprimée par un administrateur pour non-respect de nos conditions d\'utilisation.', '2025-12-14 14:59:07', 1),
(14, 1, 'Post soumis', 'Votre post \"test1\" a été soumis et est en attente de modération.', '2025-12-14 14:59:23', 1),
(15, 1, 'Post soumis', 'Votre post \"test2\" a été soumis et est en attente de modération.', '2025-12-14 14:59:32', 1),
(16, 1, 'Post soumis', 'Votre post \"Test3\" a été soumis et est en attente de modération.', '2025-12-14 14:59:40', 1),
(17, 1, 'Post rejeté', 'Votre post \"Votre publication\" a été rejeté : Contenu inapproprié', '2025-12-14 14:59:48', 1),
(18, 1, 'Post approuvé', 'Votre post \"Votre publication\" a été approuvé et est maintenant visible.', '2025-12-14 15:00:03', 1),
(19, 1, 'Post rejeté', 'Votre post \"Votre publication\" a été rejeté : Contenu inapproprié', '2025-12-14 15:00:58', 1),
(20, 1, 'Post soumis', 'Votre post \"sdfsdfdsfsdfsdfsd\" a été soumis et est en attente de modération.', '2025-12-14 15:03:00', 1),
(21, 1, 'Post approuvé', 'Votre post \"Votre publication\" a été approuvé et est maintenant visible.', '2025-12-14 15:03:04', 1),
(22, 1, 'Post modifié soumis', 'Votre post \"ffffffffffffffffffffffffffffffffffffff\" modifié est en attente de modération.', '2025-12-14 15:08:48', 1),
(23, 1, 'Post approuvé', 'Votre post \"Votre publication\" a été approuvé et est maintenant visible.', '2025-12-14 15:08:57', 1),
(24, 1, 'Post approuvé', 'Votre post \"Votre publication\" a été approuvé et est maintenant visible.', '2025-12-14 15:46:36', 1),
(25, 1, 'Post soumis', 'Votre post \"Test final\" a été soumis et est en attente de modération.', '2025-12-14 15:47:44', 1),
(26, 1, 'Post approuvé', 'Votre post \"Votre publication\" a été approuvé et est maintenant visible.', '2025-12-14 15:47:50', 1);

-- --------------------------------------------------------

--
-- Table structure for table `offre`
--

CREATE TABLE `offre` (
  `id_offre` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `statut` varchar(50) NOT NULL DEFAULT 'draft',
  `id_admin` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organisation`
--

CREATE TABLE `organisation` (
  `id_utilisateur` int(11) NOT NULL,
  `nom_organisation` varchar(255) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `statut_verification` varchar(50) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participation`
--

CREATE TABLE `participation` (
  `id_client` int(11) NOT NULL,
  `id_initiative` int(11) NOT NULL,
  `date_inscription` datetime NOT NULL DEFAULT current_timestamp(),
  `statut` varchar(50) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `id_post` int(11) NOT NULL,
  `id_auteur` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `date_mise_a_jour` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `est_publie` tinyint(1) DEFAULT 0,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `moderation_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`id_post`, `id_auteur`, `titre`, `contenu`, `date_creation`, `date_mise_a_jour`, `est_publie`, `status`, `moderation_notes`) VALUES
(6, 1, 'test1', 'sdfsjdofijdmolqkfjgjnpdomsfihgnoml', '2025-12-14 14:59:23', '2025-12-14 14:59:48', 0, 'rejected', 'Contenu inapproprié'),
(7, 1, 'sssss', 'ok sdofjnsdfsdfsdf', '2025-12-14 14:59:32', '2025-12-14 15:00:58', 0, 'rejected', 'Contenu inapproprié'),
(8, 1, 'Test3', 'sfdgsrdfpgoijdgdfgdfg', '2025-12-14 14:59:40', '2025-12-14 15:46:36', 1, 'approved', ''),
(10, 1, 'Test final', 'sdffffffffffffffffffffffffffffffffff', '2025-12-14 15:47:44', '2025-12-14 15:47:50', 1, 'approved', '');

-- --------------------------------------------------------

--
-- Table structure for table `postcomment`
--

CREATE TABLE `postcomment` (
  `id_comment` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reaction`
--

CREATE TABLE `reaction` (
  `id_reaction` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `type` enum('like','love','laugh','sad','angry') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reclamation`
--

CREATE TABLE `reclamation` (
  `id_reclamation` int(11) NOT NULL,
  `description_personnalisee` text NOT NULL,
  `statut` varchar(50) NOT NULL DEFAULT 'nouvelle',
  `id_auteur` int(11) NOT NULL,
  `id_histoire_cible` int(11) DEFAULT NULL,
  `id_commentaire_cible` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reclamation_cause`
--

CREATE TABLE `reclamation_cause` (
  `id_reclamation` int(11) NOT NULL,
  `id_cause` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` int(11) NOT NULL,
  `email` varchar(190) NOT NULL,
  `mot_de_passe_hash` varchar(255) NOT NULL,
  `date_inscription` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `email`, `mot_de_passe_hash`, `date_inscription`) VALUES
(1, 'salemoualaykom@gmail.com', '$2y$10$ckoy3FoagPeLd0kW2pk8g.YUoJfIHL0WnNIi03Ekhf8.c8UUUZoDq', '2025-12-13'),
(2, 'admin@peacelink.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-12-13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- Indexes for table `candidature`
--
ALTER TABLE `candidature`
  ADD PRIMARY KEY (`id_candidature`),
  ADD KEY `fk_candidature_client` (`id_client`),
  ADD KEY `fk_candidature_offre` (`id_offre`);

--
-- Indexes for table `cause_signalement`
--
ALTER TABLE `cause_signalement`
  ADD PRIMARY KEY (`id_cause`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- Indexes for table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id_commentaire`),
  ADD KEY `fk_commentaire_user` (`id_utilisateur`),
  ADD KEY `fk_commentaire_histoire` (`id_histoire`);

--
-- Indexes for table `histoire`
--
ALTER TABLE `histoire`
  ADD PRIMARY KEY (`id_histoire`),
  ADD KEY `fk_histoire_client` (`id_client`);

--
-- Indexes for table `initiative`
--
ALTER TABLE `initiative`
  ADD PRIMARY KEY (`id_initiative`),
  ADD KEY `fk_initiative_client` (`id_createur`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`read`);

--
-- Indexes for table `offre`
--
ALTER TABLE `offre`
  ADD PRIMARY KEY (`id_offre`),
  ADD KEY `fk_offre_admin` (`id_admin`);

--
-- Indexes for table `organisation`
--
ALTER TABLE `organisation`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- Indexes for table `participation`
--
ALTER TABLE `participation`
  ADD PRIMARY KEY (`id_client`,`id_initiative`),
  ADD KEY `fk_participation_initiative` (`id_initiative`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id_post`),
  ADD KEY `idx_post_auteur` (`id_auteur`),
  ADD KEY `idx_post_date` (`date_creation`),
  ADD KEY `idx_post_status` (`status`);

--
-- Indexes for table `postcomment`
--
ALTER TABLE `postcomment`
  ADD PRIMARY KEY (`id_comment`),
  ADD KEY `fk_comment_post` (`post_id`),
  ADD KEY `fk_comment_user` (`user_id`);

--
-- Indexes for table `reaction`
--
ALTER TABLE `reaction`
  ADD PRIMARY KEY (`id_reaction`),
  ADD UNIQUE KEY `unique_reaction` (`post_id`,`id_utilisateur`),
  ADD KEY `fk_reaction_user` (`id_utilisateur`);

--
-- Indexes for table `reclamation`
--
ALTER TABLE `reclamation`
  ADD PRIMARY KEY (`id_reclamation`),
  ADD KEY `fk_reclamation_auteur` (`id_auteur`),
  ADD KEY `fk_reclamation_histoire` (`id_histoire_cible`),
  ADD KEY `fk_reclamation_commentaire` (`id_commentaire_cible`);

--
-- Indexes for table `reclamation_cause`
--
ALTER TABLE `reclamation_cause`
  ADD PRIMARY KEY (`id_reclamation`,`id_cause`),
  ADD KEY `fk_rc_cause` (`id_cause`);

--
-- Indexes for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidature`
--
ALTER TABLE `candidature`
  MODIFY `id_candidature` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cause_signalement`
--
ALTER TABLE `cause_signalement`
  MODIFY `id_cause` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id_commentaire` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `histoire`
--
ALTER TABLE `histoire`
  MODIFY `id_histoire` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `initiative`
--
ALTER TABLE `initiative`
  MODIFY `id_initiative` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `offre`
--
ALTER TABLE `offre`
  MODIFY `id_offre` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `id_post` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `postcomment`
--
ALTER TABLE `postcomment`
  MODIFY `id_comment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reaction`
--
ALTER TABLE `reaction`
  MODIFY `id_reaction` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reclamation`
--
ALTER TABLE `reclamation`
  MODIFY `id_reclamation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `fk_admin_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Constraints for table `candidature`
--
ALTER TABLE `candidature`
  ADD CONSTRAINT `fk_candidature_client` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_candidature_offre` FOREIGN KEY (`id_offre`) REFERENCES `offre` (`id_offre`) ON DELETE CASCADE;

--
-- Constraints for table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `fk_client_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Constraints for table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `fk_commentaire_histoire` FOREIGN KEY (`id_histoire`) REFERENCES `histoire` (`id_histoire`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_commentaire_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Constraints for table `histoire`
--
ALTER TABLE `histoire`
  ADD CONSTRAINT `fk_histoire_client` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Constraints for table `initiative`
--
ALTER TABLE `initiative`
  ADD CONSTRAINT `fk_initiative_client` FOREIGN KEY (`id_createur`) REFERENCES `client` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Constraints for table `offre`
--
ALTER TABLE `offre`
  ADD CONSTRAINT `fk_offre_admin` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Constraints for table `organisation`
--
ALTER TABLE `organisation`
  ADD CONSTRAINT `fk_organisation_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Constraints for table `participation`
--
ALTER TABLE `participation`
  ADD CONSTRAINT `fk_participation_client` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_participation_initiative` FOREIGN KEY (`id_initiative`) REFERENCES `initiative` (`id_initiative`) ON DELETE CASCADE;

--
-- Constraints for table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `fk_post_utilisateur` FOREIGN KEY (`id_auteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Constraints for table `postcomment`
--
ALTER TABLE `postcomment`
  ADD CONSTRAINT `fk_comment_post` FOREIGN KEY (`post_id`) REFERENCES `post` (`id_post`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Constraints for table `reaction`
--
ALTER TABLE `reaction`
  ADD CONSTRAINT `fk_reaction_post` FOREIGN KEY (`post_id`) REFERENCES `post` (`id_post`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reaction_user` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Constraints for table `reclamation`
--
ALTER TABLE `reclamation`
  ADD CONSTRAINT `fk_reclamation_auteur` FOREIGN KEY (`id_auteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reclamation_commentaire` FOREIGN KEY (`id_commentaire_cible`) REFERENCES `commentaire` (`id_commentaire`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reclamation_histoire` FOREIGN KEY (`id_histoire_cible`) REFERENCES `histoire` (`id_histoire`) ON DELETE SET NULL;

--
-- Constraints for table `reclamation_cause`
--
ALTER TABLE `reclamation_cause`
  ADD CONSTRAINT `fk_rc_cause` FOREIGN KEY (`id_cause`) REFERENCES `cause_signalement` (`id_cause`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rc_reclamation` FOREIGN KEY (`id_reclamation`) REFERENCES `reclamation` (`id_reclamation`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
