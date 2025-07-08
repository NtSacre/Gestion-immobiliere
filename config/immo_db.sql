-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 08 juil. 2025 à 02:51
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `immo_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `agencies`
--

CREATE TABLE `agencies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `siret` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `agencies`
--

INSERT INTO `agencies` (`id`, `name`, `address`, `siret`, `phone`, `email`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 'Agence Principale', '123 Rue Principale, 75001 Paris, France', '12345678900012', '+33 1 23 45 67 89', 'contact@agenceprincipale.fr', '2025-07-01 23:47:49', NULL, 0),
(2, 'Agence teste', 'OUAKAM, Cité Batrain', 'une siret', '776148523', 'sacrentandou2.0@gmail.com', '2025-07-02 23:00:04', NULL, 0);

-- --------------------------------------------------------

--
-- Structure de la table `apartments`
--

CREATE TABLE `apartments` (
  `id` int(11) NOT NULL,
  `building_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `agency_id` int(11) DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `number` varchar(20) NOT NULL,
  `floor` int(11) NOT NULL,
  `area` decimal(10,2) NOT NULL,
  `rooms` int(11) NOT NULL,
  `bedrooms` int(11) NOT NULL,
  `bathrooms` int(11) NOT NULL,
  `toilets` int(11) NOT NULL,
  `living_rooms` int(11) NOT NULL,
  `kitchens` int(11) NOT NULL,
  `has_balcony` tinyint(1) NOT NULL,
  `amenities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenities`)),
  `type_id` int(11) NOT NULL,
  `rent_amount` decimal(10,2) DEFAULT NULL,
  `charges_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('disponible','louer','vendu','en_renovation') NOT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `apartments`
--

INSERT INTO `apartments` (`id`, `building_id`, `agent_id`, `agency_id`, `owner_id`, `number`, `floor`, `area`, `rooms`, `bedrooms`, `bathrooms`, `toilets`, `living_rooms`, `kitchens`, `has_balcony`, `amenities`, `type_id`, `rent_amount`, `charges_amount`, `status`, `price`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 2, 2, 2, 'Apart A1', 1, 200.00, 6, 2, 2, 1, 1, 1, 0, '\"[\\\"climatisation\\\",\\\"chauffage\\\",\\\"jardin\\\"]\"', 1, 200000.00, 150.00, 'disponible', 2000000.00, '2025-07-06 16:44:09', '2025-07-06 16:44:09', 0);

-- --------------------------------------------------------

--
-- Structure de la table `apartment_types`
--

CREATE TABLE `apartment_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `apartment_types`
--

INSERT INTO `apartment_types` (`id`, `name`, `description`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'Studio', 'Appartement d\'une pièce principale', 0, NULL, NULL),
(2, 'T2', 'Appartement avec deux pièces principales', 0, NULL, NULL),
(3, 'T3', 'Appartement avec trois pièces principales', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `agency_id` int(11) DEFAULT NULL,
  `action` enum('create','update','delete','view') NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `buildings`
--

CREATE TABLE `buildings` (
  `id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `neighborhood` varchar(100) DEFAULT NULL,
  `country` varchar(100) NOT NULL,
  `floors` int(11) NOT NULL,
  `apartment_count` int(11) NOT NULL,
  `land_area` decimal(10,2) DEFAULT NULL,
  `parking` enum('aucun','souterrain','exterieur','couvert') NOT NULL,
  `type_id` int(11) NOT NULL,
  `year_built` int(11) DEFAULT NULL,
  `status` enum('disponible','vendu','en_construction','en_renovation') NOT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `buildings`
--

INSERT INTO `buildings` (`id`, `agency_id`, `agent_id`, `owner_id`, `name`, `city`, `neighborhood`, `country`, `floors`, `apartment_count`, `land_area`, `parking`, `type_id`, `year_built`, `status`, `price`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 2, 2, 2, 'batiment Nts', 'Dakar', 'Almadies', 'Sénégal', 4, 20, 1000.00, 'aucun', 1, 2022, 'disponible', 2000000.00, '2025-07-02 23:42:25', '2025-07-03 01:18:42', 0);

-- --------------------------------------------------------

--
-- Structure de la table `building_types`
--

CREATE TABLE `building_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `update_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `building_types`
--

INSERT INTO `building_types` (`id`, `name`, `description`, `is_deleted`, `created_at`, `update_at`) VALUES
(1, 'Résidentiel', 'Bâtiment à usage d\'habitation', 0, NULL, NULL),
(2, 'Commercial', 'Bâtiment à usage commercial', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `buyers`
--

CREATE TABLE `buyers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `agency_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `entity_type` enum('building','apartment','profile') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `alt_text` varchar(100) DEFAULT NULL,
  `order` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `images`
--

INSERT INTO `images` (`id`, `entity_type`, `entity_id`, `path`, `alt_text`, `order`, `created_at`, `is_deleted`) VALUES
(1, 'building', 1, '/assets/images/buildings/building_6865c3e15459e.png', 'Image du bâtiment btiment Nts', 1, '2025-07-02 23:42:25', 0),
(2, 'building', 1, '/assets/images/buildings/building_6865c3e156a71.jpg', 'Image du bâtiment btiment Nts', 2, '2025-07-02 23:42:25', 0),
(3, 'apartment', 1, '/assets/images/apartments/apartment_686aa7d956a9b.jpg', 'Image de l’appartement Apart A1', 1, '2025-07-06 16:44:09', 0),
(4, 'apartment', 1, '/assets/images/apartments/apartment_686aa7d9589ee.jpg', 'Image de l’appartement Apart A1', 2, '2025-07-06 16:44:09', 0);

-- --------------------------------------------------------

--
-- Structure de la table `leases`
--

CREATE TABLE `leases` (
  `id` int(11) NOT NULL,
  `apartment_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `agency_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `rent_amount` decimal(10,2) NOT NULL,
  `charges_amount` decimal(10,2) NOT NULL,
  `deposit_amount` decimal(10,2) NOT NULL,
  `payment_frequency` enum('mensuel','trimestriel') NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `leases`
--

INSERT INTO `leases` (`id`, `apartment_id`, `tenant_id`, `agent_id`, `agency_id`, `start_date`, `end_date`, `rent_amount`, `charges_amount`, `deposit_amount`, `payment_frequency`, `is_active`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 1, 2, 2, '2025-07-06', '2025-07-31', 200000.00, 150.00, 100000.00, '', 0, '2025-07-06 19:10:20', '2025-07-06 23:49:47', 0);

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `agency_id` int(11) DEFAULT NULL,
  `type` enum('info','alerte','rappel','action') NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `owners`
--

CREATE TABLE `owners` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `agency_id` int(11) DEFAULT NULL,
  `siret` varchar(20) DEFAULT NULL,
  `type` enum('particulier','professionnel') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `owners`
--

INSERT INTO `owners` (`id`, `user_id`, `agent_id`, `agency_id`, `siret`, `type`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 3, 2, 2, NULL, 'particulier', '2025-07-02 23:25:31', '2025-07-02 23:25:31', 0),
(2, 4, 2, 2, '12345678910112', '', '2025-07-02 23:33:45', '2025-07-02 23:33:45', 0);

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `lease_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `agency_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `due_date` date NOT NULL,
  `type` enum('loyer','charges','depot','autre') NOT NULL,
  `mode` enum('cash','mobile','carte') NOT NULL,
  `status` enum('payer','en_attente','en_retard','annuler') NOT NULL,
  `quittance_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `payments`
--

INSERT INTO `payments` (`id`, `lease_id`, `agent_id`, `agency_id`, `amount`, `payment_date`, `due_date`, `type`, `mode`, `status`, `quittance_path`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 2, 2, 200000.00, '2025-07-07', '2025-07-08', '', 'cash', '', 'quittance_1_1751933222.pdf', '2025-07-08 00:07:02', '2025-07-08 00:07:03', 0),
(2, 1, 2, 2, 150.00, '2025-07-08', '2025-07-09', 'charges', 'mobile', 'en_attente', 'quittance_2_1751935108.pdf', '2025-07-08 00:38:28', '2025-07-08 00:38:29', 0);

-- --------------------------------------------------------

--
-- Structure de la table `profile_images`
--

CREATE TABLE `profile_images` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `alt_text` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `is_deleted`) VALUES
(1, 'superadmin', 'Super Administrateur du système', '2025-06-04 20:54:00', 0),
(2, 'admin', 'Administrateur de l\'agence', '2025-06-04 20:54:00', 0),
(3, 'agent', 'Agent immobilier', '2025-06-04 20:54:00', 0),
(4, 'proprietaire', 'Propriétaire de biens', '2025-06-04 20:54:00', 0),
(5, 'locataire', 'Locataire d’un appartement', '2025-06-04 20:54:00', 0),
(6, 'acheteur', 'Acheteur potentiel', '2025-06-04 20:54:00', 0);

-- --------------------------------------------------------

--
-- Structure de la table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `agency_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tenants`
--

INSERT INTO `tenants` (`id`, `user_id`, `agent_id`, `agency_id`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 5, 2, 2, '2025-07-06 16:42:19', '2025-07-06 16:42:19', 0);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `agency_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role_id`, `agency_id`, `first_name`, `last_name`, `phone`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 'superadmin', 'superadmin@immo.fr', '$2y$10$7b7CbQZaB04YjgCBsPNDs.k1c.PGR6z3euHCyly9ujhnuIqb5kmwW', 1, 1, 'Admin', 'Principal', '+33 6 12 34 56 78', '2025-07-01 23:47:49', NULL, 0),
(2, 'NtSacre', 'sacrentandou2.0@gmail.com', '$2y$10$wusgRJ7dbn5yqgoN2RqYS.taiE.LSdXPhd3aUh1w0YJC6bDP9DVzC', 2, 2, 'Sacré', 'NTANDOU', '776148523', '2025-07-02 23:00:04', NULL, 0),
(3, 'Paul gerald', 'paulgerald@gmail.com', '$2y$10$xA7qvgbhjtPE43O/.vmC/O1eSWq03.TSdO6ymxYq5u9J5ooCd677O', 4, 2, 'paul', 'gerald', '776148523', '2025-07-02 23:25:31', '2025-07-02 23:25:31', 0),
(4, 'Oni Apili', 'ondi@gmail.com', '$2y$10$.q6WldZscrmiXe1aqzg27OjWFm6.SnSi1s4lqYKLaWdWudoBarzgK', 4, 2, 'Ondi', 'Apili', '774567898', '2025-07-02 23:33:45', '2025-07-02 23:33:45', 0),
(5, 'Nafi badji', 'nafi@gmail.com', '$2y$10$W1n9WhDFDwA8wfLaDMa1huPG8tLpTcSP1cMb2fcMOYDd.Ln9X.x7O', 5, 2, 'Nafi', 'Badji', '775477077', '2025-07-06 16:42:19', '2025-07-06 16:42:19', 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `agencies`
--
ALTER TABLE `agencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `siret` (`siret`);

--
-- Index pour la table `apartments`
--
ALTER TABLE `apartments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `building_id` (`building_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `agency_id` (`agency_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `status` (`status`),
  ADD KEY `bedrooms` (`bedrooms`);

--
-- Index pour la table `apartment_types`
--
ALTER TABLE `apartment_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `agency_id` (`agency_id`),
  ADD KEY `idx_table_record` (`table_name`,`record_id`);

--
-- Index pour la table `buildings`
--
ALTER TABLE `buildings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agency_id` (`agency_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `city` (`city`),
  ADD KEY `status` (`status`);

--
-- Index pour la table `building_types`
--
ALTER TABLE `building_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `buyers`
--
ALTER TABLE `buyers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Index pour la table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`);

--
-- Index pour la table `leases`
--
ALTER TABLE `leases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `apartment_id` (`apartment_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Index pour la table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `siret` (`siret`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Index pour la table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lease_id` (`lease_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Index pour la table `profile_images`
--
ALTER TABLE `profile_images`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `user_id_idx` (`user_id`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `agencies`
--
ALTER TABLE `agencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `apartments`
--
ALTER TABLE `apartments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `apartment_types`
--
ALTER TABLE `apartment_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `buildings`
--
ALTER TABLE `buildings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `building_types`
--
ALTER TABLE `building_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `buyers`
--
ALTER TABLE `buyers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `leases`
--
ALTER TABLE `leases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `owners`
--
ALTER TABLE `owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `profile_images`
--
ALTER TABLE `profile_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `apartments`
--
ALTER TABLE `apartments`
  ADD CONSTRAINT `apartments_ibfk_1` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`),
  ADD CONSTRAINT `apartments_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `apartments_ibfk_3` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `apartments_ibfk_4` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`),
  ADD CONSTRAINT `apartments_ibfk_5` FOREIGN KEY (`type_id`) REFERENCES `apartment_types` (`id`);

--
-- Contraintes pour la table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `audit_log_ibfk_2` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `buildings`
--
ALTER TABLE `buildings`
  ADD CONSTRAINT `buildings_ibfk_1` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`),
  ADD CONSTRAINT `buildings_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `buildings_ibfk_3` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`),
  ADD CONSTRAINT `buildings_ibfk_4` FOREIGN KEY (`type_id`) REFERENCES `building_types` (`id`);

--
-- Contraintes pour la table `buyers`
--
ALTER TABLE `buyers`
  ADD CONSTRAINT `buyers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `buyers_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `buyers_ibfk_3` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `leases`
--
ALTER TABLE `leases`
  ADD CONSTRAINT `leases_ibfk_1` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`),
  ADD CONSTRAINT `leases_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`),
  ADD CONSTRAINT `leases_ibfk_3` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leases_ibfk_4` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `owners`
--
ALTER TABLE `owners`
  ADD CONSTRAINT `owners_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `owners_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `owners_ibfk_3` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`lease_id`) REFERENCES `leases` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `profile_images`
--
ALTER TABLE `profile_images`
  ADD CONSTRAINT `profile_images_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tenants_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tenants_ibfk_3` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
