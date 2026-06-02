-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mar. 02 juin 2026 à 13:52
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
-- Base de données : `gestion_memoires`
--

-- --------------------------------------------------------

--
-- Structure de la table `ancien_memoire`
--

CREATE TABLE `ancien_memoire` (
  `idAM` int(11) NOT NULL,
  `nomAut` varchar(50) NOT NULL,
  `prenomAut` varchar(50) NOT NULL,
  `theme` varchar(255) NOT NULL,
  `idfiliere` int(11) DEFAULT NULL,
  `idNiveau` int(11) DEFAULT NULL,
  `idCentre` int(11) DEFAULT NULL,
  `idAnnee` int(11) DEFAULT NULL,
  `maitre_memoire` varchar(150) DEFAULT NULL,
  `examinateur` varchar(150) DEFAULT NULL,
  `president_jury` varchar(150) DEFAULT NULL,
  `fichier` varchar(50) DEFAULT NULL,
  `statut` varchar(30) DEFAULT 'en_attente',
  `source` varchar(30) DEFAULT NULL,
  `idetudiant` int(11) DEFAULT NULL,
  `date_depot` datetime DEFAULT current_timestamp(),
  `publie_par` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `idcommentaire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_commentaire` timestamp NULL DEFAULT current_timestamp(),
  `idmemoire` int(11) NOT NULL,
  `idetudiant` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `direction_etude`
--

CREATE TABLE `direction_etude` (
  `idde` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motdepasse` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `direction_etude`
--

INSERT INTO `direction_etude` (`idde`, `nom`, `prenom`, `email`, `motdepasse`) VALUES
(1, '', '', 'admin@gmail.com', 'Admin123');

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

CREATE TABLE `etudiant` (
  `idetudiant` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `idfiliere` int(11) NOT NULL,
  `idCentre` int(11) DEFAULT NULL,
  `idNiveau` int(11) DEFAULT NULL,
  `niveau` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motdepasse` varchar(255) NOT NULL,
  `type_compte` varchar(20) NOT NULL DEFAULT 'consultant',
  `date_creation` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `etudiant`
--

INSERT INTO `etudiant` (`idetudiant`, `nom`, `prenom`, `idfiliere`, `idCentre`, `idNiveau`, `niveau`, `email`, `motdepasse`, `type_compte`, `date_creation`) VALUES
(1, 'DANNON', 'Keneth', 2, NULL, NULL, 'Licence 2', 'nellysdannon@gmail.com', 'Keneth2006', 'consultant', '2026-05-29 13:02:26');

-- --------------------------------------------------------

--
-- Structure de la table `filiere`
--

CREATE TABLE `filiere` (
  `idfiliere` int(11) NOT NULL,
  `nom_filiere` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `filiere`
--

INSERT INTO `filiere` (`idfiliere`, `nom_filiere`) VALUES
(1, 'Système Industrielle'),
(2, 'Système Informatique et Logiciel'),
(3, 'Réseau Informatique et Télécommunication');

-- --------------------------------------------------------

--
-- Structure de la table `jury`
--

CREATE TABLE `jury` (
  `idjury` int(11) NOT NULL,
  `idmemoire` int(11) NOT NULL,
  `idprof` int(11) NOT NULL,
  `role_jury` varchar(50) NOT NULL,
  `decision` varchar(30) DEFAULT NULL,
  `observation` text DEFAULT NULL,
  `date_decision` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `like_memoire`
--

CREATE TABLE `like_memoire` (
  `idlike` int(11) NOT NULL,
  `idmemoire` int(11) NOT NULL,
  `idetudiant` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `memoire`
--

CREATE TABLE `memoire` (
  `idmemoire` int(11) NOT NULL,
  `theme` varchar(255) NOT NULL,
  `centre` varchar(150) NOT NULL,
  `idfiliere` int(11) NOT NULL,
  `annee_academique` varchar(20) NOT NULL,
  `mots_cles` text DEFAULT NULL,
  `fichier_memoire` varchar(255) NOT NULL,
  `statut` varchar(30) DEFAULT 'en_attente',
  `datesoumission` timestamp NULL DEFAULT current_timestamp(),
  `idetudiant` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `idnotification` int(11) NOT NULL,
  `message` text NOT NULL,
  `statut_lecture` tinyint(1) DEFAULT 0,
  `date_notification` timestamp NULL DEFAULT current_timestamp(),
  `idetudiant` int(11) DEFAULT NULL,
  `idprof` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `professeur`
--

CREATE TABLE `professeur` (
  `idprof` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motdepasse` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `professeur`
--

INSERT INTO `professeur` (`idprof`, `nom`, `prenom`, `email`, `motdepasse`) VALUES
(1, 'Test', 'User', 'test@example.com', '123456');

-- --------------------------------------------------------

--
-- Structure de la table `publication`
--

CREATE TABLE `publication` (
  `idpublication` int(11) NOT NULL,
  `idmemoire` int(11) NOT NULL,
  `idde` int(11) NOT NULL,
  `date_publication` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `ancien_memoire`
--
ALTER TABLE `ancien_memoire`
  ADD PRIMARY KEY (`idAM`),
  ADD KEY `idFiliere` (`idfiliere`);

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`idcommentaire`),
  ADD KEY `idmemoire` (`idmemoire`),
  ADD KEY `idetudiant` (`idetudiant`);

--
-- Index pour la table `direction_etude`
--
ALTER TABLE `direction_etude`
  ADD PRIMARY KEY (`idde`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `etudiant`
--
ALTER TABLE `etudiant`
  ADD PRIMARY KEY (`idetudiant`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idfiliere` (`idfiliere`);

--
-- Index pour la table `filiere`
--
ALTER TABLE `filiere`
  ADD PRIMARY KEY (`idfiliere`);

--
-- Index pour la table `jury`
--
ALTER TABLE `jury`
  ADD PRIMARY KEY (`idjury`),
  ADD KEY `idmemoire` (`idmemoire`),
  ADD KEY `idprof` (`idprof`);

--
-- Index pour la table `like_memoire`
--
ALTER TABLE `like_memoire`
  ADD PRIMARY KEY (`idlike`),
  ADD KEY `idmemoire` (`idmemoire`),
  ADD KEY `idetudiant` (`idetudiant`);

--
-- Index pour la table `memoire`
--
ALTER TABLE `memoire`
  ADD PRIMARY KEY (`idmemoire`),
  ADD KEY `idetudiant` (`idetudiant`),
  ADD KEY `idfiliere` (`idfiliere`);

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`idnotification`),
  ADD KEY `idetudiant` (`idetudiant`),
  ADD KEY `idprof` (`idprof`);

--
-- Index pour la table `professeur`
--
ALTER TABLE `professeur`
  ADD PRIMARY KEY (`idprof`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `publication`
--
ALTER TABLE `publication`
  ADD PRIMARY KEY (`idpublication`),
  ADD KEY `idmemoire` (`idmemoire`),
  ADD KEY `idde` (`idde`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `ancien_memoire`
--
ALTER TABLE `ancien_memoire`
  MODIFY `idAM` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `idcommentaire` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `direction_etude`
--
ALTER TABLE `direction_etude`
  MODIFY `idde` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `etudiant`
--
ALTER TABLE `etudiant`
  MODIFY `idetudiant` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `filiere`
--
ALTER TABLE `filiere`
  MODIFY `idfiliere` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `jury`
--
ALTER TABLE `jury`
  MODIFY `idjury` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `like_memoire`
--
ALTER TABLE `like_memoire`
  MODIFY `idlike` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `memoire`
--
ALTER TABLE `memoire`
  MODIFY `idmemoire` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
  MODIFY `idnotification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `professeur`
--
ALTER TABLE `professeur`
  MODIFY `idprof` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `publication`
--
ALTER TABLE `publication`
  MODIFY `idpublication` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
