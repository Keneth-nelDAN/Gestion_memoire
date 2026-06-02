<?php
require_once "../config/database.php";
require_once "../app/controllers/ProfilController.php";

$controller = new ProfilController();
$controller->index();
?>