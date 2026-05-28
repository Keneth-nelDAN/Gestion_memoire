<?php

class dashboard {
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Nombre de mémoires publiés
    public function countMemoires()
    {
        $sql = "SELECT COUNT(*) as total FROM memoire";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Nombre de filières
    public function countFilieres()
    {
        $sql = "SELECT COUNT(*) as total FROM filiere";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>