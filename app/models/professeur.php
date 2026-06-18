<?php

class Professeur {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function countTotal() {
        $sql = "SELECT COUNT(*) as total FROM professeur";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getRecent($limit = 4) {
        $sql = "SELECT idprof, nom, prenom, email FROM professeur ORDER BY idprof DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exists($email) {
        $sql = "SELECT idprof FROM professeur WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() !== false;
    }

    public function create($nom, $prenom, $email, $password) {
        $sql = "INSERT INTO professeur (nom, prenom, email, motdepasse) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nom, $prenom, $email, $password]);
    }
}
