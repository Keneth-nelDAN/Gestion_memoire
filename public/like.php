<?php
require_once "../config/database.php";
require_once "../app/models/Like.php";

$database = new Database();
$pdo = $database->connect();

if(session_status() == PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['idetudiant'])) {
    $_SESSION['idetudiant'] = 1;
}

// vérifier id
if(!isset($_GET['id']))
{
    if(isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID introuvable']);
        exit;
    }

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

$liked = $likeModel->isLiked($idAM, $idetudiant);
$totalLikes = $likeModel->countLikes($idAM);

if(isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'idAM' => $idAM,
        'liked' => $liked,
        'totalLikes' => (int)$totalLikes,
    ]);
    exit;
}

// retour accueil
header("Location: /Mon%20document/Gestion_memoire/public/");
exit;
?>