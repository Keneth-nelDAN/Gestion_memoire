<?php
// 1. Charger la configuration bdd et démarrer la connexion
require_once "../config/database.php";

$database = new Database();
$pdo = $database->connect();

if (!$pdo) {
    die("Erreur de connexion à la base de données. Veuillez vérifier vos paramètres.");
}

// 2. Charger les modèles de données (Attention aux majuscules/minuscules !)
require_once "../app/models/memoire.php";
require_once "../app/models/dashboard.php";

// 3. Charger les contrôleurs
require_once "../app/controllers/dashboardController.php";

// 4. Détecter le niveau choisi
$niveau = $_GET['niveau'] ?? "Tous";

// 5. Exécuter le contrôleur du Tableau de Bord (Dashboard)
$controller = new dashboardController();
$controller->index();
?>
