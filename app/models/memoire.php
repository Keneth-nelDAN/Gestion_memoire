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
    public function countTotal() {
        $sql = "SELECT COUNT(*) as total FROM ancien_memoire";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function countByStatut($statuts) {
        $in = implode(',', array_fill(0, count($statuts), '?'));
        $sql = "SELECT COUNT(*) as total FROM ancien_memoire WHERE statut IN ($in)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($statuts);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function countThisMonth() {
        $sql = "SELECT COUNT(*) as total FROM ancien_memoire 
                WHERE MONTH(date_depot) = MONTH(CURRENT_DATE()) 
                AND YEAR(date_depot) = YEAR(CURRENT_DATE())";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getFilieres() {
        $sql = "SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAnneesDisponibles() {
        $sql = "SELECT DISTINCT YEAR(date_depot) as annee_academique 
                FROM ancien_memoire WHERE date_depot IS NOT NULL 
                ORDER BY annee_academique DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($filters = []) {
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = "(theme LIKE :q OR nomAut LIKE :q OR prenomAut LIKE :q OR maitre_memoire LIKE :q OR examinateur LIKE :q OR president_jury LIKE :q)";
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['filiere'])) {
            $where[] = "idfiliere = :filiere";
            $params[':filiere'] = (int)$filters['filiere'];
        }

        if (!empty($filters['annee'])) {
            $where[] = "YEAR(date_depot) = :annee";
            $params[':annee'] = (int)$filters['annee'];
        }

        $sql = "SELECT am.*, f.nom_filiere,
                CONCAT(am.prenomAut, ' ', am.nomAut) AS auteur,
                CONCAT_WS('; ', am.maitre_memoire, am.examinateur, am.president_jury) AS jury,
                (SELECT COUNT(*) FROM like_memoire lm WHERE lm.idAM = am.idAM OR lm.idmemoire = am.idAM) AS likes,
                (SELECT COUNT(*) FROM commentaire c WHERE c.idmemoire = am.idAM) AS commentaires
            FROM ancien_memoire am
            LEFT JOIN filiere f ON f.idfiliere = am.idfiliere";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= " ORDER BY am.idAM DESC LIMIT 50";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($idmemoire) {
        $sql = "SELECT m.*, e.idetudiant, e.nom AS nom_etudiant, e.prenom AS prenom_etudiant 
                FROM memoire m
                JOIN etudiant e ON m.idetudiant = e.idetudiant
                WHERE m.idmemoire = :idmemoire";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idmemoire' => $idmemoire]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}