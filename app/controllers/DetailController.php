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

        $_SESSION['idetudiant'] = 2;

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

        $pageComments = max(1, (int)($_GET['page'] ?? 1));
        $commentairesParPage = 3;
        $debutCommentaires = ($pageComments - 1) * $commentairesParPage;
        $nombrePagesCommentaires = $totalCommentaires > 0 ? ceil($totalCommentaires / $commentairesParPage) : 1;

        $commentaires =
        $commentaireModel->getCommentairesPaginated(
            $memoire['idAM'], 
            $debutCommentaires,
            $commentairesParPage
        );

        require "../app/views/memoire/detail.php";
    }
}