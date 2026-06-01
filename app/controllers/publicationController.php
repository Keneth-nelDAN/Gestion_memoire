<?php

require_once "../config/database.php";
require_once "../app/models/Publication.php";
require_once "../app/models/Dashboard.php";

class PublicationController {

    public function index()
    {
        global $pdo;

        // ======================
        // DASHBOARD
        // ======================

        $dashboardModel = new Dashboard($pdo);
        $totalMemoires = $dashboardModel->countMemoires();
        $totalFilieres = $dashboardModel->countFilieres();

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

        require "../app/views/memoire/index.php";
    }
}