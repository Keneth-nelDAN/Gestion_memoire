<?php

class Etudiant {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function countTotal() {
        $sql = "SELECT COUNT(*) as total FROM etudiant";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function countByType($type) {
        $sql = "SELECT COUNT(*) as total FROM etudiant WHERE type_compte = :type";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':type' => $type]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
