<?php
require_once "../config/database.php";
require_once "../app/controllers/MesMemoiresController.php";

$database = new Database();
$pdo = $database->connect();

$controller = new MesMemoiresController();
$controller->index();
?>