<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/memoire.php';
require_once __DIR__ . '/../models/commentaire.php';
require_once __DIR__ . '/../models/like.php';

class MemoireController {
    private $db;
    private $memoireModel;
    private $commentaireModel;
    private $likeModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->memoireModel = new Memoire($this->db);
        $this->commentaireModel = new Commentaire($this->db);
        $this->likeModel = new Like($this->db);
    }

    public function index($filters = []) {
        $memoires = $this->memoireModel->search($filters);
        $filieres = $this->memoireModel->getFilieres();
        return [
            'memoires' => $memoires,
            'filieres' => $filieres
        ];
    }

    public function toggleLike($idAM, $idetudiant) {
        if ($this->likeModel->exists($idAM, $idetudiant)) {
            $this->likeModel->remove($idAM, $idetudiant);
            $liked = false;
        } else {
            // La table like_memoire n'a que idAM et idetudiant comme clés étrangères pertinentes
            $this->likeModel->add($idAM, $idetudiant); 
            $liked = true;
        }
        $total_likes = $this->likeModel->countForMemoire($idAM);

        return ['liked' => $liked, 'total_likes' => $total_likes];
    }

    public function addComment($idAM, $idetudiant, $contenu) {
        return $this->commentaireModel->ajouter($contenu, $idAM, $idetudiant);
    }
}
