<?php
require_once "../config/database.php";
require_once "../app/controllers/DetailController.php";

$database = new Database();
$pdo = $database->connect();

$controller = new DetailController();
$controller->index();
?>