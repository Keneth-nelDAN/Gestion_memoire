<?php

require_once "../config/database.php";
require_once "../app/models/Publication.php";

$database = new Database();
$pdo = $database->connect();

if(!isset($_GET['id']))
{
    die("Mémoire introuvable");
}

$idAM = $_GET['id'];

$publicationModel = new Publication($pdo);

$memoire = $publicationModel->getById($idAM);

$fichier = __DIR__ .
"/assets/uploads/memoires/" .
$memoire['fichier'];

header("Content-Type: application/pdf");
header("Content-Disposition: inline");

readfile($fichier);
exit;
