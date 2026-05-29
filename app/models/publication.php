<?php
class Publication {

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // tous les anciens mémoires
    public function getAll()
    {
        $sql = "SELECT * FROM ancien_memoire
                ORDER BY idAM DESC";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // filtrer par niveau
    public function getByNiveau($niveau)
    {
        $sql = "SELECT * FROM ancien_memoire WHERE niveau = ? ORDER BY idAM DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$niveau]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>