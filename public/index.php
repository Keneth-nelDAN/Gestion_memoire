<?php
require_once "../config/database.php";
require_once "../app/controllers/dashboardController.php";
require_once "../app/controllers/PublicationController.php";

require_once "../config/database.php";

$database = new Database();
$pdo = $database->connect(); // Initialise $pdo pour les modèles et requêtes

// niveau choisi
$niveau = $_GET['niveau'] ?? "Tous";

// model mémoire
$memoireModel = new Memoire($pdo);

// récupérer mémoires
$memoires = $memoireModel->getMemoires($niveau);

$controller = new dashboardController();

$memoireModel = new Memoire($pdo);
$memoires = $memoireModel->getMemoires($niveau);
$controller = new PublicationController();

$controller->index();
?>
