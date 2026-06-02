<?php
require_once "../config/database.php";
require_once "../app/controllers/MesMemoiresController.php";

$controller = new MesMemoiresController();
$controller->index();
?>