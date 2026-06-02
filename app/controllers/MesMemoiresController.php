<?php
require_once "../config/database.php";
require_once "../app/models/MemoireEtudiant.php";
require_once "../app/models/Etudiant.php";

class MesMemoiresController
{
    public function index()
    {
        global $pdo;

        session_start();

        $idetudiant = $_SESSION['idetudiant'];

        $memoireModel = new MemoireEtudiant($pdo);

        $memoires = $memoireModel->getByEtudiant($idetudiant);

        $etudiantModel = new Etudiant($pdo);
        $etudiant = $etudiantModel->getById($idetudiant);

        require "../app/views/etudiant/mes_memoires.php";
    }
}
?>