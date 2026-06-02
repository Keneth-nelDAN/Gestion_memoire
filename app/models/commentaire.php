<?php

class Commentaire {

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function countCommentaires($idAM)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM commentaire
                WHERE idAM = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idAM]);

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getCommentaires($idAM)
    {
        $sql = "SELECT commentaire.*,
                       etudiant.nom,
                       etudiant.prenom
                FROM commentaire
                LEFT JOIN etudiant
                ON commentaire.idetudiant = etudiant.idetudiant
                WHERE idAM = ?
                ORDER BY date_commentaire DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idAM]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommentairesPaginated($idAM, $debut, $limite)
    {
        $sql = "SELECT commentaire.*,
                       etudiant.nom,
                       etudiant.prenom
                FROM commentaire
                LEFT JOIN etudiant
                ON commentaire.idetudiant = etudiant.idetudiant
                WHERE idAM = ?
                ORDER BY date_commentaire DESC
                LIMIT ?, ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $idAM, PDO::PARAM_INT);
        $stmt->bindValue(2, $debut, PDO::PARAM_INT);
        $stmt->bindValue(3, $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ajouter($contenu,$idAM,$idetudiant)
    {
        $sql = "INSERT INTO commentaire
                (contenu,date_commentaire,idAM,idetudiant)
                VALUES(?,NOW(),?,?)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $contenu,
            $idAM,
            $idetudiant
        ]);
    }
}
?>