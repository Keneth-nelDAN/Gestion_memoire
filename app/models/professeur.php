<?php
class Professeur {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupère les informations d’un professeur par son id
     */
    public function getProfById($idprof) {
        $stmt = $this->pdo->prepare("SELECT * FROM professeur WHERE idprof = ?");
        $stmt->execute([$idprof]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Normalise un nom (enlève les titres Pr, Dr, etc.)
     */
    public static function normaliserNom($nomComplet) {
        $nomComplet = trim($nomComplet);
        $titres = ['Pr ', 'Dr ', 'M. ', 'Mme ', 'Mlle ', 'Prof '];
        foreach ($titres as $titre) {
            if (strpos($nomComplet, $titre) === 0) {
                $nomComplet = substr($nomComplet, strlen($titre));
            }
        }
        return trim($nomComplet);
    }

    /**
     * Récupère tous les mémoires où le professeur est président du jury
     * (basé sur le champ `president_jury` de la table `memoire`)
     */
    public function getMemoiresByPresident($prenom, $nom) {
    $nomPrenomNormalise = self::normaliserNom($prenom . ' ' . $nom);
    $prenomNomNormalise = self::normaliserNom($nom . ' ' . $prenom);

    $sql = "SELECT m.*, 
                   e.nom AS etudiant_nom, 
                   e.prenom AS etudiant_prenom,
                   f.nom_filiere AS filiere_nom
            FROM memoire m
            LEFT JOIN etudiant e ON m.idetudiant = e.idetudiant
            LEFT JOIN filiere f ON m.idfiliere = f.idfiliere
            WHERE REPLACE(REPLACE(REPLACE(m.president_jury, 'Pr ', ''), 'Dr ', ''), 'Prof ', '') LIKE ?
               OR REPLACE(REPLACE(REPLACE(m.president_jury, 'Pr ', ''), 'Dr ', ''), 'Prof ', '') LIKE ?";
    $stmt = $this->pdo->prepare($sql);
    $like1 = '%' . $nomPrenomNormalise . '%';
    $like2 = '%' . $prenomNomNormalise . '%';
    $stmt->execute([$like1, $like2]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Récupère la décision existante du président pour un mémoire
     */
    public function getDecision($idmemoire, $idprof) {
        $stmt = $this->pdo->prepare("SELECT * FROM jury WHERE idmemoire = ? AND idprof = ? AND role_jury = 'president'");
        $stmt->execute([$idmemoire, $idprof]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Enregistre ou met à jour une décision (acceptation/refus)
     * Met également à jour le statut global du mémoire
     */
    public function saveDecision($idmemoire, $idprof, $decision, $observation) {
        // Vérifier si une décision existe déjà
        $existing = $this->getDecision($idmemoire, $idprof);
        if ($existing) {
            // Mise à jour
            $stmt = $this->pdo->prepare("UPDATE jury SET decision = ?, observation = ?, date_decision = NOW() WHERE idjury = ?");
            $stmt->execute([$decision, $observation, $existing['idjury']]);
        } else {
            // Insertion
            $stmt = $this->pdo->prepare("INSERT INTO jury (idmemoire, idprof, role_jury, decision, observation, date_decision) VALUES (?, ?, 'president', ?, ?, NOW())");
            $stmt->execute([$idmemoire, $idprof, $decision, $observation]);
        }

        // Mise à jour du statut du mémoire (table `memoire`)
        $statut = ($decision === 'accepte') ? 'valide' : 'refuse';
        $stmt2 = $this->pdo->prepare("UPDATE memoire SET statut = ? WHERE idmemoire = ?");
        $stmt2->execute([$statut, $idmemoire]);
    }

    /**
     * Supprime une décision (annule la validation)
     */
    public function deleteDecision($idmemoire, $idprof) {
        $stmt = $this->pdo->prepare("DELETE FROM jury WHERE idmemoire = ? AND idprof = ? AND role_jury = 'president'");
        $stmt->execute([$idmemoire, $idprof]);
        // Remet le statut du mémoire en "en_attente"
        $stmt2 = $this->pdo->prepare("UPDATE memoire SET statut = 'en_attente' WHERE idmemoire = ?");
        $stmt2->execute([$idmemoire]);
    }
}
?>