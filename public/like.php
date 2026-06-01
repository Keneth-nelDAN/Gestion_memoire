<?php
require_once "../config/database.php";
require_once "../app/models/Like.php";

if(session_status() == PHP_SESSION_NONE){
    session_start();
}

// étudiant temporaire
$_SESSION['idetudiant'] = 1;

// vérifier id
if(!isset($_GET['id']))
{
    die("ID introuvable");
}

$idAM = $_GET['id'];
$idetudiant = $_SESSION['idetudiant'];
$likeModel = new Like($pdo);

// déjà liké ?
if($likeModel->isLiked($idAM, $idetudiant))
{
    $likeModel->removeLike($idAM, $idetudiant);
}
else
{
    $likeModel->addLike($idAM, $idetudiant);
}

// retour accueil
header("Location: /Mon%20document/Gestion_memoire/public/");
exit;
?>