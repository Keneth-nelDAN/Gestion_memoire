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

    public function rechercher($mot)
    {
        $sql = "SELECT ancien_memoire.*,
                    filiere.nom_filiere,
                    niveau.nomNiveau,
                    centre.nomCentre,
                    annee_scolaire.annee

                FROM ancien_memoire

                LEFT JOIN filiere
                ON ancien_memoire.idfiliere = filiere.idfiliere

                LEFT JOIN niveau
                ON ancien_memoire.idNiveau = niveau.idNiveau

                LEFT JOIN centre
                ON ancien_memoire.idCentre = centre.idCentre

                LEFT JOIN annee_scolaire
                ON ancien_memoire.idAnnee = annee_scolaire.idAnnee

                WHERE statut='publie'

                AND (
                    theme LIKE ?
                    OR nom_filiere LIKE ?
                    OR nomCentre LIKE ?
                    OR annee LIKE ?
                    OR maitre_memoire LIKE ?
                )

                ORDER BY idAM DESC";

        $stmt = $this->pdo->prepare($sql);

        $mot = "%".$mot."%";

        $stmt->execute([
            $mot,
            $mot,
            $mot,
            $mot,
            $mot
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPaginated($debut, $limite)
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

                WHERE ancien_memoire.statut='publie'

                ORDER BY idAM DESC

                LIMIT ?, ?";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(1, $debut, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll()
    {
        $sql = "SELECT COUNT(*) total
                FROM ancien_memoire
                WHERE statut='publie'";

        return $this->pdo
            ->query($sql)
            ->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function countFiltered($niveau, $mot)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM ancien_memoire

                LEFT JOIN filiere
                ON ancien_memoire.idfiliere = filiere.idfiliere

                LEFT JOIN niveau
                ON ancien_memoire.idNiveau = niveau.idNiveau

                LEFT JOIN centre
                ON ancien_memoire.idCentre = centre.idCentre

                LEFT JOIN annee_scolaire
                ON ancien_memoire.idAnnee = annee_scolaire.idAnnee

                WHERE ancien_memoire.statut='publie'";

        $params = [];

        if ($niveau != 'Tous') {
            $sql .= " AND niveau.nomNiveau = ?";
            $params[] = $niveau;
        }

        if (!empty($mot)) {
            $sql .= " AND (
                theme LIKE ?
                OR nom_filiere LIKE ?
                OR nomCentre LIKE ?
                OR niveau.nomNiveau LIKE ?
                OR annee_scolaire.annee LIKE ?
                OR nomAut LIKE ?
                OR prenomAut LIKE ?
                OR maitre_memoire LIKE ?
            )";

            $mot = "%" . $mot . "%";
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function rechercherEtFiltrerPaginated($niveau, $mot, $debut, $limite)
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

                WHERE ancien_memoire.statut='publie'";

        $params = [];

        if ($niveau != 'Tous') {
            $sql .= " AND niveau.nomNiveau = ?";
            $params[] = $niveau;
        }

        if (!empty($mot)) {
            $sql .= " AND (
                theme LIKE ?
                OR nom_filiere LIKE ?
                OR nomCentre LIKE ?
                OR niveau.nomNiveau LIKE ?
                OR annee_scolaire.annee LIKE ?
                OR nomAut LIKE ?
                OR prenomAut LIKE ?
                OR maitre_memoire LIKE ?
            )";

            $mot = "%" . $mot . "%";
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
            $params[] = $mot;
        }

        $sql .= " ORDER BY ancien_memoire.idAM DESC
                  LIMIT ?, ?";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $index => $value) {
            $stmt->bindValue($index + 1, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(count($params) + 1, $debut, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, $limite, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}