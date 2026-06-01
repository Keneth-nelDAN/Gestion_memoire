<?php
require_once "../config/database.php";
require_once "../app/models/like.php";

session_start();

class LikeController {
    public function toggle()
    {
        global $pdo;

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

        header("Location: ../public/index.php");
    }
}