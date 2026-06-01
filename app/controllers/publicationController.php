<?php
require_once "../config/database.php";
require_once "../app/models/Publication.php";
require_once "../app/models/Dashboard.php";
require_once "../app/models/Like.php";
require_once "../app/models/Commentaire.php";

class PublicationController {

    public function index()
    {
        if(session_status() == PHP_SESSION_NONE){
            session_start();
        }
        $_SESSION['idetudiant'] = 1;

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

        $niveau = $_GET['niveau'] ?? 'Tous';

        // ======================
        // PUBLICATIONS
        // ======================

        $publicationModel = new Publication($pdo);

        if($niveau == 'Tous')
        {
            $publications = $publicationModel->getAll();
        }
        else
        {
            $publications = $publicationModel->getByNiveau($niveau);
        }

        $memoireModel = new Memoire($pdo);
        $memoires = $memoireModel->getMemoires($niveau);
        //likes
        $likeModel = new Like($pdo);

        // commentaires
        $commentaireModel = new Commentaire($pdo);

         require "../app/views/memoire/index.php";
    }
}