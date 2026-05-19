-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 17 mai 2026 à 00:28
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `niveau` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motdepasse` varchar(255) NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idetudiant`),
  UNIQUE KEY `email` (`email`),
  KEY `idfiliere` (`idfiliere`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `filiere`
--

DROP TABLE IF EXISTS `filiere`;
CREATE TABLE IF NOT EXISTS `filiere` (
  `idfiliere` int NOT NULL AUTO_INCREMENT,
  `nom_filiere` varchar(100) NOT NULL,
  PRIMARY KEY (`idfiliere`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `like_memoire`
--

DROP TABLE IF EXISTS `like_memoire`;
CREATE TABLE IF NOT EXISTS `like_memoire` (
  `idlike` int NOT NULL AUTO_INCREMENT,
  `idmemoire` int NOT NULL,
  `idetudiant` int NOT NULL,
  PRIMARY KEY (`idlike`),
  KEY `idmemoire` (`idmemoire`),
  KEY `idetudiant` (`idetudiant`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `memoire`
--

DROP TABLE IF EXISTS `memoire`;
CREATE TABLE IF NOT EXISTS `memoire` (
  `idmemoire` int NOT NULL AUTO_INCREMENT,
  `theme` varchar(255) NOT NULL,
  `centre` varchar(150) NOT NULL,
  `idfiliere` int NOT NULL,
  `annee_academique` varchar(20) NOT NULL,
  `mots_cles` text,
  `fichier_memoire` varchar(255) NOT NULL,
  `statut` varchar(30) DEFAULT 'en_attente',
  `datesoumission` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `idetudiant` int NOT NULL,
  PRIMARY KEY (`idmemoire`),
  KEY `idetudiant` (`idetudiant`),
  KEY `idfiliere` (`idfiliere`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `publication`
--

DROP TABLE IF EXISTS `publication`;
CREATE TABLE IF NOT EXISTS `publication` (
  `idpublication` int NOT NULL AUTO_INCREMENT,
  `idmemoire` int NOT NULL,
  `idde` int NOT NULL,
  `date_publication` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idpublication`),
  KEY `idmemoire` (`idmemoire`),
  KEY `idde` (`idde`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
