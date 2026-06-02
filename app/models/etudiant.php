<?php

class Etudiant
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getById($id)
    {

        $sql = "SELECT * FROM etudiant
                WHERE idetudiant = ?";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([$id]);

        $sql = "SELECT etudiant.*,
                    filiere.nom_filiere,
                    niveau.nomNiveau,
                    centre.nomCentre,
                    annee_scolaire.annee

                FROM etudiant

                LEFT JOIN filiere
                ON etudiant.idfiliere = filiere.idfiliere

                LEFT JOIN niveau
                ON etudiant.idNiveau = niveau.idNiveau

                LEFT JOIN centre
                ON etudiant.idCentre = centre.idCentre

                LEFT JOIN annee_scolaire
                ON etudiant.idAnnee = annee_scolaire.idAnnee

                WHERE etudiant.idetudiant = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>