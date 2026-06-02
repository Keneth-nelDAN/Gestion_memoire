<?php
// Charger les fichiers requis de manière robuste à l'aide de chemins absolus
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/models/dashboard.php";
require_once __DIR__ . "/../app/models/memoire.php"; // respect de la minuscule

// Obtenir la connexion PDO
$database = new Database();
$pdo = $database->connect();

if (!$pdo) {
    die("Échec d'initialisation de la base de données.");
}

// Récupérer le filtre de niveau
$niveau = $_GET['niveau'] ?? "Tous";

// Charger et exécuter le contrôleur principal
require_once __DIR__ . "/../app/controllers/dashboardController.php";
$controller = new dashboardController();
$controller->index();
?>
