<?php
class Like {
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // vérifier si étudiant a liké
    public function isLiked($idAM, $idetudiant)
    {
        $sql = "SELECT * FROM like_memoire
                WHERE idAM = ?
                AND idetudiant = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idAM, $idetudiant]);
        return $stmt->rowCount() > 0;
    }

    // nombre likes
    public function countLikes($idAM)
    {
        $sql = "SELECT COUNT(*) as total
                FROM like_memoire
                WHERE idAM = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idAM]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // ajouter like
    public function addLike($idAM, $idetudiant)
    {
        $sql = "INSERT INTO like_memoire(idAM, idetudiant)
                VALUES(?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$idAM, $idetudiant]);
    }

    // supprimer like
    public function removeLike($idAM, $idetudiant)
    {
        $sql = "DELETE FROM like_memoire
                WHERE idAM = ?
                AND idetudiant = ?";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$idAM, $idetudiant]);
    }
}