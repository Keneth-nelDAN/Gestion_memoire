-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 02 juin 2026 à 13:08
-- Version du serveur : 8.0.31
-- Version de PHP : 8.0.26

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

DROP TABLE IF EXISTS `ancien_memoire`;
CREATE TABLE IF NOT EXISTS `ancien_memoire` (
  `idAM` int NOT NULL AUTO_INCREMENT,
  `nomAut` varchar(50) NOT NULL,
  `prenomAut` varchar(50) NOT NULL,
  `theme` varchar(255) NOT NULL,
  `idfiliere` int DEFAULT NULL,
  `idNiveau` int DEFAULT NULL,
  `idCentre` int DEFAULT NULL,
  `annee_academique` varchar(20) DEFAULT NULL,
  `maitre_memoire` varchar(150) DEFAULT NULL,
  `examinateur` varchar(150) DEFAULT NULL,
  `president_jury` varchar(150) DEFAULT NULL,
  `fichier` varchar(50) DEFAULT NULL,
  `statut` varchar(30) DEFAULT 'en_attente',
  `source` varchar(30) DEFAULT NULL,
  `idetudiant` int DEFAULT NULL,
  `date_depot` datetime DEFAULT CURRENT_TIMESTAMP,
  `publie_par` int DEFAULT NULL,
  PRIMARY KEY (`idAM`),
  KEY `idFiliere` (`idfiliere`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ancien_memoire`
--

INSERT INTO `ancien_memoire` (`idAM`, `nomAut`, `prenomAut`, `theme`, `idfiliere`, `idNiveau`, `idCentre`, `annee_academique`, `maitre_memoire`, `examinateur`, `president_jury`, `fichier`, `statut`, `source`, `idetudiant`, `date_depot`, `publie_par`) VALUES
(3, 'Sognon', 'Emile', 'Gestion d\'un restaurant', 4, NULL, NULL, '', 'Mr OLOUBO', 'JEAN', 'Mr SUZON', 'memoire_20260529183827_dfd7c850.pdf', 'publie', 'de_unitaire', NULL, '2026-05-29 20:38:27', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `centre`
--

DROP TABLE IF EXISTS `centre`;
CREATE TABLE IF NOT EXISTS `centre` (
  `idCentre` int NOT NULL AUTO_INCREMENT,
  `nomCentre` varchar(100) NOT NULL,
  PRIMARY KEY (`idCentre`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `centre`
--

INSERT INTO `centre` (`idCentre`, `nomCentre`) VALUES
(1, 'Calavi'),
(2, 'Agla'),
(3, 'Akpakpa'),
(4, 'Gbegamey'),
(5, 'Porto-novo');

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

DROP TABLE IF EXISTS `commentaire`;
CREATE TABLE IF NOT EXISTS `commentaire` (
  `idcommentaire` int NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `date_commentaire` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `idmemoire` int NOT NULL,
  `idetudiant` int NOT NULL,
  PRIMARY KEY (`idcommentaire`),
  KEY `idmemoire` (`idmemoire`),
  KEY `idetudiant` (`idetudiant`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `direction_etude`
--

DROP TABLE IF EXISTS `direction_etude`;
CREATE TABLE IF NOT EXISTS `direction_etude` (
  `idde` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motdepasse` varchar(255) NOT NULL,
  PRIMARY KEY (`idde`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etudiant`
--

DROP TABLE IF EXISTS `etudiant`;
CREATE TABLE IF NOT EXISTS `etudiant` (
  `idetudiant` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `idfiliere` int NOT NULL,
  `idCentre` int DEFAULT NULL,
  `idNiveau` int DEFAULT NULL,
  `niveau` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motdepasse` varchar(255) NOT NULL,
  `type_compte` varchar(20) NOT NULL DEFAULT 'consultant',
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idetudiant`),
  UNIQUE KEY `email` (`email`),
  KEY `idfiliere` (`idfiliere`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `etudiant`
--

INSERT INTO `etudiant` (`idetudiant`, `nom`, `prenom`, `idfiliere`, `idCentre`, `idNiveau`, `niveau`, `email`, `motdepasse`, `type_compte`, `date_creation`) VALUES
(1, 'Hounsou', 'Kossivi', 1, NULL, NULL, 'L3', 'kossivi@gmail.com', '123456', 'consultant', '2026-05-27 23:10:28'),
(2, 'Diallo', 'Abibatou', 2, NULL, NULL, 'M2', 'abibatou@gmail.com', '123456', 'consultant', '2026-05-27 23:10:28'),
(3, 'Agossou', 'Roméo', 3, NULL, NULL, 'L3', 'romeo@gmail.com', '123456', 'consultant', '2026-05-27 23:10:28'),
(7, 'pascal', 'lokossou', 4, 1, 2, 'L2', 'pascallokossou31@gmail.com', 'Etud@537642', 'consultant', '2026-06-01 16:09:19'),
(8, 'SOGNON', 'Emile', 4, 1, 3, 'L3', 'sognontossouemile@gmail.com', 'Etud@772434', 'diplome', '2026-06-01 16:40:49');

-- --------------------------------------------------------

--
-- Structure de la table `filiere`
--

DROP TABLE IF EXISTS `filiere`;
CREATE TABLE IF NOT EXISTS `filiere` (
  `idfiliere` int NOT NULL AUTO_INCREMENT,
  `nom_filiere` varchar(100) NOT NULL,
  PRIMARY KEY (`idfiliere`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `filiere`
--

INSERT INTO `filiere` (`idfiliere`, `nom_filiere`) VALUES
(1, 'Energie Renouvelable'),
(2, 'Système Industriel'),
(3, 'Réseau Informatique et Telecom'),
(4, 'Système Informatique et Logiciel'),
(5, 'Finance Comptabilité Audit'),
(6, 'Marketing Commerce Communication'),
(7, 'Droit Privé'),
(8, 'Droit Public'),
(9, 'Agro-Production Animal'),
(10, 'Agro-Production Végétale');

-- --------------------------------------------------------

--
-- Structure de la table `jury`
--

DROP TABLE IF EXISTS `jury`;
CREATE TABLE IF NOT EXISTS `jury` (
  `idjury` int NOT NULL AUTO_INCREMENT,
  `idmemoire` int NOT NULL,
  `idprof` int NOT NULL,
  `role_jury` varchar(50) NOT NULL,
  `decision` varchar(30) DEFAULT NULL,
  `observation` text,
  `date_decision` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`idjury`),
  KEY `idmemoire` (`idmemoire`),
  KEY `idprof` (`idprof`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `like_memoire`
--

DROP TABLE IF EXISTS `like_memoire`;
CREATE TABLE IF NOT EXISTS `like_memoire` (
  `idlike` int NOT NULL AUTO_INCREMENT,
  `idAM` int DEFAULT NULL,
  `idmemoire` int NOT NULL,
  `idetudiant` int NOT NULL,
  PRIMARY KEY (`idlike`),
  KEY `idmemoire` (`idmemoire`),
  KEY `idetudiant` (`idetudiant`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `like_memoire`
--

INSERT INTO `like_memoire` (`idlike`, `idAM`, `idmemoire`, `idetudiant`) VALUES
(1, NULL, 0, 1),
(2, NULL, 0, 1),
(3, NULL, 0, 1),
(4, NULL, 0, 1),
(5, NULL, 0, 1),
(6, NULL, 0, 1),
(7, NULL, 0, 1),
(8, NULL, 0, 1),
(9, NULL, 0, 1),
(10, NULL, 0, 1),
(11, NULL, 0, 1),
(12, NULL, 0, 1),
(13, NULL, 0, 1),
(14, NULL, 0, 1),
(15, NULL, 0, 1),
(16, NULL, 0, 1),
(17, NULL, 0, 1),
(18, NULL, 0, 1),
(19, NULL, 0, 1),
(20, NULL, 0, 1),
(21, NULL, 0, 1),
(22, NULL, 0, 1),
(23, NULL, 0, 1),
(30, 1, 0, 1);

-- --------------------------------------------------------

--
-- Structure de la table `niveau`
--

DROP TABLE IF EXISTS `niveau`;
CREATE TABLE IF NOT EXISTS `niveau` (
  `idNiveau` int NOT NULL AUTO_INCREMENT,
  `nomNiveau` varchar(100) NOT NULL,
  PRIMARY KEY (`idNiveau`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `niveau`
--

INSERT INTO `niveau` (`idNiveau`, `nomNiveau`) VALUES
(1, 'L1'),
(2, 'L2'),
(3, 'L3'),
(4, 'M1'),
(5, 'M2');

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `idnotification` int NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `statut_lecture` tinyint(1) DEFAULT '0',
  `date_notification` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `idetudiant` int DEFAULT NULL,
  `idprof` int DEFAULT NULL,
  PRIMARY KEY (`idnotification`),
  KEY `idetudiant` (`idetudiant`),
  KEY `idprof` (`idprof`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `professeur`
--

DROP TABLE IF EXISTS `professeur`;
CREATE TABLE IF NOT EXISTS `professeur` (
  `idprof` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motdepasse` varchar(255) NOT NULL,
  PRIMARY KEY (`idprof`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `professeur`
--

INSERT INTO `professeur` (`idprof`, `nom`, `prenom`, `email`, `motdepasse`) VALUES
(1, 'ZINSOU', 'Moise', 'fofo@gamil.com', 'Prof@4395');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
