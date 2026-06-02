<?php
require_once "../config/database.php";
require_once "../app/models/Etudiant.php";

class ProfilController
{
    public function index()
    {
        global $pdo;

        session_start();

        $idetudiant = $_SESSION['idetudiant'];

        $etudiantModel = new Etudiant($pdo);

        $etudiant = $etudiantModel->getById($idetudiant);

        $etudiant = $etudiantModel->getById($_SESSION['idetudiant']);

        require "../app/views/etudiant/profil.php";
    }
}
?>