<?php

require_once "../config/database.php";
require_once "../app/models/Commentaire.php";

session_start();

$idAM = $_POST['idAM'];
$contenu = $_POST['contenu'];

$idetudiant = $_SESSION['idetudiant'];

$commentaireModel =
new Commentaire($pdo);

$commentaireModel->ajouter(
    $contenu,
    $idAM,
    $idetudiant
);

header(
"Location: /Mon%20document/Gestion_memoire/public/detail.php?id=".$idAM
);

exit;
?>