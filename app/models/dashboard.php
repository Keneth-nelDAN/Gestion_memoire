<?php
class Dashboard {
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // nombre mémoires publiés
    public function countMemoires()
    {
        $sql = "SELECT COUNT(*) as total
                FROM ancien_memoire
                WHERE statut = 'publie'";

        return $this->pdo
                    ->query($sql)
                    ->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // nombre filières
    public function countFilieres()
    {
        $sql = "SELECT COUNT(*) as total
                FROM filiere";

        return $this->pdo
                    ->query($sql)
                    ->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // nombre centres
    public function countCentres()
    {
        $sql = "SELECT COUNT(*) as total
                FROM centre";

        return $this->pdo
                    ->query($sql)
                    ->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>