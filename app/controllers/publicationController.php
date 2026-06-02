<?php
require_once "../config/database.php";
require_once "../app/models/Publication.php";
require_once "../app/models/Dashboard.php";
require_once "../app/models/Like.php";
require_once "../app/models/Commentaire.php";
require_once "../app/models/Etudiant.php";

class PublicationController {

    public function index()
    {
        if(session_status() == PHP_SESSION_NONE){
            session_start();
        }

        if(!isset($_SESSION['idetudiant'])) {
            $_SESSION['idetudiant'] = 1;
        }

        global $pdo;

        // ======================
        // DASHBOARD
        // ======================

        $dashboardModel = new Dashboard($pdo);
        $totalMemoires = $dashboardModel->countMemoires();
        $totalFilieres = $dashboardModel->countFilieres();
        $totalCentres = $dashboardModel->countCentres();

        // ======================
        // FILTRE
        // ======================
        $niveauParam = $_GET['niveau'] ?? 'Tous';
        $search = trim($_GET['search'] ?? '');

        $niveau = $niveauParam;
        if ($niveauParam === 'L3') {
            $niveau = 'Licence 3';
        } elseif ($niveauParam === 'M2') {
            $niveau = 'Master 2';
        }

        $publicationModel = new Publication($pdo);
        $likeModel = new Like($pdo);
        $commentaireModel = new Commentaire($pdo);
        $etudiantModel = new Etudiant($pdo);

        $etudiant = $etudiantModel->getById($_SESSION['idetudiant']);
        $etudiantNiveau = $etudiant['nomNiveau'] ?? '';

        $limite = 6;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $debut = ($page - 1) * $limite;

        $totalResultats = $publicationModel->countFiltered($niveau, $search);
        $nombrePages = $totalResultats > 0 ? ceil($totalResultats / $limite) : 1;

        $publications = $publicationModel->rechercherEtFiltrerPaginated(
            $niveau,
            $search,
            $debut,
            $limite
        );

        require "../app/views/memoire/index.php";
    }
}