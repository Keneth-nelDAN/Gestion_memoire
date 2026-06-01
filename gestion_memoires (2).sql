-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 29 mai 2026 à 22:46
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
  `idAnnee` int DEFAULT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ancien_memoire`
--

INSERT INTO `ancien_memoire` (`idAM`, `nomAut`, `prenomAut`, `theme`, `idfiliere`, `idNiveau`, `idCentre`, `idAnnee`, `maitre_memoire`, `examinateur`, `president_jury`, `fichier`, `statut`, `source`, `idetudiant`, `date_depot`, `publie_par`) VALUES
(1, 'Dossou', 'Jean', 'Mise en place d un système de gestion scolaire intégré en milieu universitaire', 1, 1, 1, 1, 'Dr Fatou Anael', 'Dr Raff Jadj', 'Pr Oudza Paul', 'memoire.pdf', 'en_attente', NULL, NULL, '2026-05-29 14:21:05', NULL),
(2, 'Fanou', 'Dieudonne', 'Conception d un site web institutionnel pour GASA FORMATION ', 4, 2, 2, 2, 'Dr Kossi Adonis', 'Dr Dossou Vladmir', 'Pr Aho Frédéric', 'memoire1.pdf', 'en_attente', NULL, NULL, '2026-05-29 14:21:05', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `annee_scolaire`
--

DROP TABLE IF EXISTS `annee_scolaire`;
CREATE TABLE IF NOT EXISTS `annee_scolaire` (
  `idAnnee` int NOT NULL AUTO_INCREMENT,
  `annee` int NOT NULL,
  PRIMARY KEY (`idAnnee`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `annee_scolaire`
--

INSERT INTO `annee_scolaire` (`idAnnee`, `annee`) VALUES
(1, 2000),
(2, 2001);

-- --------------------------------------------------------

--
-- Structure de la table `centre`
--

DROP TABLE IF EXISTS `centre`;
CREATE TABLE IF NOT EXISTS `centre` (
  `idCentre` int NOT NULL AUTO_INCREMENT,
  `nomCentre` varchar(100) NOT NULL,
  PRIMARY KEY (`idCentre`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `centre`
--

INSERT INTO `centre` (`idCentre`, `nomCentre`) VALUES
(1, 'Calavi'),
(2, 'Agla');

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `etudiant`
--

INSERT INTO `etudiant` (`idetudiant`, `nom`, `prenom`, `idfiliere`, `niveau`, `email`, `motdepasse`, `date_creation`) VALUES
(1, 'Hounsou', 'Kossivi', 1, 'L3', 'kossivi@gmail.com', '123456', '2026-05-27 23:10:28'),
(2, 'Diallo', 'Abibatou', 2, 'M2', 'abibatou@gmail.com', '123456', '2026-05-27 23:10:28'),
(3, 'Agossou', 'Roméo', 3, 'L3', 'romeo@gmail.com', '123456', '2026-05-27 23:10:28');

-- --------------------------------------------------------

--
-- Structure de la table `filiere`
--

DROP TABLE IF EXISTS `filiere`;
CREATE TABLE IF NOT EXISTS `filiere` (
  `idfiliere` int NOT NULL AUTO_INCREMENT,
  `nom_filiere` varchar(100) NOT NULL,
  PRIMARY KEY (`idfiliere`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `like_memoire`
--

DROP TABLE IF EXISTS `like_memoire`;
CREATE TABLE IF NOT EXISTS `like_memoire` (
  `idlike` int NOT NULL AUTO_INCREMENT,
  `idAM` int DEFAULT NULL,
  `idetudiant` int NOT NULL,
  PRIMARY KEY (`idlike`),
  KEY `idetudiant` (`idetudiant`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `like_memoire`
--

INSERT INTO `like_memoire` (`idlike`, `idAM`, `idetudiant`) VALUES
(1, NULL, 1),
(2, NULL, 1),
(3, NULL, 1),
(4, NULL, 1),
(5, NULL, 1),
(6, NULL, 1),
(7, NULL, 1),
(8, NULL, 1),
(9, NULL, 1),
(10, NULL, 1),
(11, NULL, 1),
(12, NULL, 1),
(13, NULL, 1),
(14, NULL, 1),
(15, NULL, 1),
(16, NULL, 1),
(17, NULL, 1),
(18, NULL, 1),
(19, NULL, 1),
(20, NULL, 1),
(21, NULL, 1),
(22, NULL, 1),
(23, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `niveau`
--

DROP TABLE IF EXISTS `niveau`;
CREATE TABLE IF NOT EXISTS `niveau` (
  `idNiveau` int NOT NULL AUTO_INCREMENT,
  `nomNiveau` varchar(100) NOT NULL,
  PRIMARY KEY (`idNiveau`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `niveau`
--

INSERT INTO `niveau` (`idNiveau`, `nomNiveau`) VALUES
(1, 'L3'),
(2, 'M2');

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
