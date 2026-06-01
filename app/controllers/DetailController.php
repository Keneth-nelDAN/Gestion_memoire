<?php
require_once "../config/database.php";
require_once "../app/models/Publication.php";
require_once "../app/models/Like.php";
require_once "../app/models/Commentaire.php";

class DetailController
{
    public function index()
    {
        global $pdo;

        if(session_status() == PHP_SESSION_NONE){
            session_start();
        }

        $_SESSION['idetudiant'] = 1;

        if(!isset($_GET['id']))
        {
            die("Mémoire introuvable");
        }

        $idAM = $_GET['id'];

        $publicationModel = new Publication($pdo);
        $memoire = $publicationModel->getById($idAM);

        $likeModel = new Like($pdo);
        $commentaireModel = new Commentaire($pdo);

        $totalLikes =
        $likeModel->countLikes($memoire['idAM']);

        $totalCommentaires =
        $commentaireModel->countCommentaires($memoire['idAM']);

        $commentaires =
        $commentaireModel->getCommentaires($memoire['idAM']);

        require "../app/views/memoire/detail.php";
    }
}