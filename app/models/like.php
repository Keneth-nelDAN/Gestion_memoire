<?php

class Like {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function countByAM($idAM) {
        $sql = "SELECT COUNT(*) FROM like_memoire WHERE idAM = :idAM";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idAM' => $idAM]);
        return $stmt->fetchColumn();
    }
}
