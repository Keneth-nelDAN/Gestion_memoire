<?php
class Memoire {
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getMemoires($niveau = 'Tous')
    {

        if($niveau == 'Tous')
        {
            $sql = "SELECT ancien_memoire.*,
                    filiere.nom_filiere,
                    niveau.nomNiveau AS niveau,
                    centre.nomCentre AS centre,
                    annee_scolaire.annee AS annee_academique

                    FROM ancien_memoire
                    LEFT JOIN filiere
                    ON ancien_memoire.idfiliere = filiere.idfiliere
                    LEFT JOIN niveau
                    ON ancien_memoire.idNiveau = niveau.idNiveau
                    LEFT JOIN centre
                    ON ancien_memoire.idCentre = centre.idCentre
                    LEFT JOIN annee_scolaire
                    ON ancien_memoire.idAnnee = annee_scolaire.idAnnee
                    WHERE statut = 'publie'
                    ORDER BY idAM DESC";
        }
        else
        {
            $sql = "SELECT ancien_memoire.*,
                    filiere.nom_filiere,
                    niveau.nomNiveau AS niveau,
                    centre.nomCentre AS centre,
                    annee_scolaire.annee AS annee_academique

                    FROM ancien_memoire
                    LEFT JOIN filiere
                    ON ancien_memoire.idfiliere = filiere.idfiliere
                    LEFT JOIN niveau
                    ON ancien_memoire.idNiveau = niveau.idNiveau
                    LEFT JOIN centre
                    ON ancien_memoire.idCentre = centre.idCentre
                    LEFT JOIN annee_scolaire
                    ON ancien_memoire.idAnnee = annee_scolaire.idAnnee
                    WHERE niveau.nomNiveau = ?
                    AND statut = 'publie'
                    ORDER BY idAM DESC";
        }

        $stmt = $this->pdo->prepare($sql);

        if($niveau == 'Tous')
        {
            $stmt->execute();
        }
        else
        {
            $stmt->execute([$niveau]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>