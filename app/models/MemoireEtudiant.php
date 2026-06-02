<?php
class MemoireEtudiant
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByEtudiant($idetudiant)
    {
        $sql = "SELECT *
                FROM ancien_memoire
                WHERE idetudiant = ?
                ORDER BY idAM DESC";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([$idetudiant]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}