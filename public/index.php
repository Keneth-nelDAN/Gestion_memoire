<?php
require_once "../config/database.php";
require_once "../app/controllers/dashboardController.php";

$controller = new dashboardController();

$controller->index();
?>