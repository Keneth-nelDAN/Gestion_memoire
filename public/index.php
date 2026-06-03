<?php
require_once "../config/database.php";
require_once "../app/controllers/dashboardController.php";
require_once "../app/controllers/PublicationController.php";

// initialiser PDO via Database
$database = new Database();
$pdo = $database->connect();

// niveau choisi
$niveau = $_GET['niveau'] ?? "Tous";

// model mémoire
$memoireModel = new Memoire($pdo);

// récupérer mémoires
$memoires = $memoireModel->getMemoires($niveau);

$controller = new PublicationController();
$controller->index();
?>