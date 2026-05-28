<?php   
require_once "../config/database.php";
require_once "../app/models/dashboard.php";
require_once "../app/models/Memoire.php";

class dashboardController {
    public function index()
    {
        global $pdo;

        // MODEL
        $dashboardModel = new dashboard($pdo);

        // Données
        $totalMemoires = $dashboardModel->countMemoires();

        $totalFilieres = $dashboardModel->countFilieres();

        // FILTRE NIVEAU
        $niveau = $_GET['niveau'] ?? 'Tous';

        // model mémoire
        $memoireModel = new Memoire($pdo);

        // mémoires filtrés
        $memoires = $memoireModel->getMemoires($niveau);
        
        // VIEW
        require_once "../app/views/memoire/index.php";
    }
}