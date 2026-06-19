<?php

class DashboardController
{
    public function __construct()
    {
        // Le constructeur est vide car la connexion DB sera faite dans getDEData
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: /Gestion_memoire/public/login.php');
            exit;
        }

        $userType = $_SESSION['userType'] ?? null;

        if ($userType === 'etudiant') {
            header('Location: /Gestion_memoire/app/views/etudiant/dashboard_etudiant.php');
        } elseif ($userType === 'professeur') {
            header('Location: /Gestion_memoire/app/views/professeur/dashboard_professeur.php');
        } elseif ($userType === 'directeur') {
            header('Location: /Gestion_memoire/app/views/direction_etude/dashboard_de.php');
        } else {
            header('Location: /Gestion_memoire/public/login.php');
        }
        exit;
    }

    public function getDEData($filters)
    {
        require_once __DIR__ . '/../../config/database.php';
        $database = new Database();
        $db = $database->connect();

        // 1. Statistiques
        $stats = [
            'nb_publies' => $db->query("SELECT COUNT(*) FROM ancien_memoire WHERE statut = 'publie'")->fetchColumn(),
            'nb_attente' => $db->query("SELECT COUNT(*) FROM ancien_memoire WHERE statut = 'en_attente'")->fetchColumn(),
            'nb_mois' => $db->query("SELECT COUNT(*) FROM ancien_memoire WHERE MONTH(date_depot) = MONTH(NOW()) AND YEAR(date_depot) = YEAR(NOW())")->fetchColumn(),
            'nb_etudiants' => $db->query("SELECT COUNT(*) FROM etudiant")->fetchColumn(),
            'nb_consultants' => $db->query("SELECT COUNT(*) FROM etudiant WHERE type_compte = 'consultant'")->fetchColumn(),
            'nb_diplomes' => $db->query("SELECT COUNT(*) FROM etudiant WHERE type_compte = 'diplome'")->fetchColumn(),
            'nb_professeurs' => $db->query("SELECT COUNT(*) FROM professeur")->fetchColumn(),
            'nb_memoires' => $db->query("SELECT COUNT(*) FROM ancien_memoire")->fetchColumn(),
        ];

        // 2. Données pour les filtres
        $filieres = $db->query("SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere ASC")->fetchAll(PDO::FETCH_ASSOC);
        $annees = $db->query("SELECT DISTINCT annee_academique FROM ancien_memoire WHERE annee_academique IS NOT NULL AND annee_academique != '' ORDER BY annee_academique DESC")->fetchAll(PDO::FETCH_ASSOC);

        // 3. Professeurs récents
        $recent_professeurs = $db->query("SELECT nom, prenom, email FROM professeur ORDER BY idprof DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        // 4. Liste des mémoires avec filtres
        $sql_memoires = "SELECT am.idAM, am.theme, am.date_depot, 
                                CONCAT(am.prenomAut, ' ', am.nomAut) as auteur,
                                f.nom_filiere,
                                CONCAT(am.president_jury, ', ', am.examinateur) as jury,
                                (SELECT COUNT(*) FROM like_memoire WHERE idAM = am.idAM) as likes,
                                (SELECT COUNT(*) FROM commentaire WHERE idmemoire = am.idAM) as commentaires
                         FROM ancien_memoire am
                         LEFT JOIN filiere f ON am.idfiliere = f.idfiliere
                         WHERE 1=1";
        
        $params = [];
        if (!empty($filters['q'])) {
            $sql_memoires .= " AND (am.theme LIKE :q OR am.nomAut LIKE :q OR am.prenomAut LIKE :q OR am.president_jury LIKE :q OR am.examinateur LIKE :q OR f.nom_filiere LIKE :q)";
            $params[':q'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['filiere'])) {
            $sql_memoires .= " AND am.idfiliere = :filiere";
            $params[':filiere'] = $filters['filiere'];
        }
        if (!empty($filters['annee'])) {
            $sql_memoires .= " AND am.annee_academique = :annee";
            $params[':annee'] = $filters['annee'];
        }
        $sql_memoires .= " ORDER BY am.date_depot DESC";
        $stmt_memoires = $db->prepare($sql_memoires);
        $stmt_memoires->execute($params);
        $memoires = $stmt_memoires->fetchAll(PDO::FETCH_ASSOC);

        return compact('stats', 'filieres', 'annees', 'recent_professeurs', 'memoires');
    }
}