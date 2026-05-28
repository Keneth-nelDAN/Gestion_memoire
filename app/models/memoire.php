<?php
class Memoire {
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // récupérer mémoires
    public function getMemoires($niveau = null)
    {
        // TOUS
        if($niveau == null || $niveau == "Tous")
        {
            $sql = "SELECT * FROM memoire";
            $stmt = $this->pdo->query($sql);
        }

        // FILTRER
        else
        {
            $sql = "SELECT * FROM memoire WHERE niveau = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$niveau]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>