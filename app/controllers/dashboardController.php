<?php   
require_once "../config/database.php";
require_once "../app/models/dashboard.php";

class dashboardController {
    public function index()
    {
        global $pdo;

        // MODEL
        $dashboardModel = new dashboard($pdo);

        // Données
        $totalMemoires = $dashboardModel->countMemoires();

        $totalFilieres = $dashboardModel->countFilieres();

        // VIEW
        require_once "../app/views/memoire/index.php";
    }
}