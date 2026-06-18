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
        // Logique simplifiée utilisant un modèle Like si possible
        // Pour l'instant on garde une version compatible avec ce qui existait
        $sql_check = "SELECT idlike FROM like_memoire WHERE idAM = :idAM AND idetudiant = :idetudiant";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->execute([':idAM' => $idAM, ':idetudiant' => $idetudiant]);
        $row = $stmt_check->fetch();

        if ($row) {
            $sql_del = "DELETE FROM like_memoire WHERE idlike = :idlike";
            $stmt_del = $this->db->prepare($sql_del);
            $stmt_del->execute([':idlike' => $row['idlike']]);
            $liked = false;
        } else {
            $sql_ins = "INSERT INTO like_memoire (idAM, idmemoire, idetudiant) VALUES (:idAM, :idAM, :idetudiant)";
            $stmt_ins = $this->db->prepare($sql_ins);
            $stmt_ins->execute([':idAM' => $idAM, ':idetudiant' => $idetudiant]);
            $liked = true;
        }

        $sql_count = "SELECT COUNT(*) FROM like_memoire WHERE idAM = :idAM";
        $stmt_count = $this->db->prepare($sql_count);
        $stmt_count->execute([':idAM' => $idAM]);
        $total_likes = $stmt_count->fetchColumn();

        return ['liked' => $liked, 'total_likes' => $total_likes];
    }

    public function addComment($idAM, $idetudiant, $contenu) {
        return $this->commentaireModel->ajouter($contenu, $idAM, $idetudiant);
    }
}
