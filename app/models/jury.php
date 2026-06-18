<?php

class Jury {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAssignedMemoires($idprof) {
        $sql = "SELECT m.idmemoire AS id, m.theme AS titre, 
                       CONCAT(e.prenom, ' ', e.nom) AS etudiant
                FROM jury j
                JOIN memoire m ON j.idmemoire = m.idmemoire
                JOIN etudiant e ON m.idetudiant = e.idetudiant
                WHERE j.idprof = :idprof
                ORDER BY m.idmemoire DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idprof' => $idprof]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getObservations($idprof, $limit = 10) {
        $sql = "SELECT j.observation AS contenu, j.date_decision AS date, m.theme AS title,
                        CONCAT(e.prenom, ' ', e.nom) AS etudiant,
                        CONCAT(p.prenom, ' ', p.nom) AS auteur,
                        j.role_jury AS role
                 FROM jury j
                 JOIN memoire m ON j.idmemoire = m.idmemoire
                 JOIN etudiant e ON m.idetudiant = e.idetudiant
                 JOIN professeur p ON j.idprof = p.idprof
                 WHERE j.idprof = :idprof AND j.observation IS NOT NULL AND j.observation != ''
                 ORDER BY j.date_decision DESC
                 LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':idprof', $idprof, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateObservation($idmemoire, $idprof, $observation) {
        $sql = "UPDATE jury 
                SET observation = :observation, 
                    date_decision = NOW() 
                WHERE idmemoire = :idmemoire AND idprof = :idprof";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':observation' => $observation,
            ':idmemoire' => $idmemoire,
            ':idprof' => $idprof
        ]);
    }
}
