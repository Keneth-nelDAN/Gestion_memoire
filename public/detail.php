<?php
require_once "../config/database.php";
require_once "../app/controllers/DetailController.php";

$controller = new DetailController();
$controller->index();
?>