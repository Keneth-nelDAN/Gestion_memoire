<?php

class Notification {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getByEtudiant($idetudiant) {
        $sql = "SELECT idnotification, message, statut_lecture, date_notification 
                FROM notification 
                WHERE idetudiant = :idetudiant 
                ORDER BY date_notification DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idetudiant' => $idetudiant]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByProfesseur($idprof, $limit = 50) {
        $sql = "SELECT idnotification AS id, message, statut_lecture AS `read`, date_notification AS `time`
                FROM notification 
                WHERE idprof = :idprof 
                ORDER BY idnotification DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':idprof', $idprof, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAllAsReadByEtudiant($idetudiant) {
        $sql = "UPDATE notification SET statut_lecture = 1 
                WHERE idetudiant = :idetudiant AND statut_lecture = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':idetudiant' => $idetudiant]);
    }

    public function markAllAsReadByProfesseur($idprof) {
        $sql = "UPDATE notification SET statut_lecture = 1 
                WHERE idprof = :idprof AND statut_lecture = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':idprof' => $idprof]);
    }

    public function countNonLues($idetudiant) {
        $sql = "SELECT COUNT(*) as total
                FROM notification
                WHERE idetudiant = :idetudiant AND statut_lecture = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idetudiant' => $idetudiant]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['total'] : 0;
    }

    public function create($message, $idetudiant = null, $idprof = null) {
        $sql = "INSERT INTO notification (message, idetudiant, idprof) 
                VALUES (:message, :idetudiant, :idprof)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':message' => $message,
            ':idetudiant' => $idetudiant,
            ':idprof' => $idprof
        ]);
    }
}
