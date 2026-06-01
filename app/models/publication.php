<?php

class Publication {
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll()
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
                WHERE ancien_memoire.statut = 'publie'

                ORDER BY ancien_memoire.idAM DESC";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByNiveau($niveauChoisi)
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
                AND ancien_memoire.statut = 'publie'
                ORDER BY ancien_memoire.idAM DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$niveauChoisi]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        public function getById($idAM)
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

                WHERE ancien_memoire.idAM = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idAM]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}