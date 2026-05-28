<?php
require_once "../config/database.php";
require_once "../app/controllers/dashboardController.php";

// niveau choisi
$niveau = $_GET['niveau'] ?? "Tous";

// model mémoire
$memoireModel = new Memoire($pdo);

// récupérer mémoires
$memoires = $memoireModel->getMemoires($niveau);

$controller = new dashboardController();

$controller->index();
?>