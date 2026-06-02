<?php
require_once "../config/database.php";
require_once "../app/controllers/dashboardController.php";
require_once "../app/controllers/PublicationController.php";

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