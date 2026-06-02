<?php
// 1. Inclure la configuration de la base de données et les modèles indispensables
require_once "../config/database.php";
require_once "../app/models/memoire.php"; 

// 2. Instancier la base de données et créer la variable globale $pdo requise
$database = new Database();
$pdo = $database->connect();

if (!$pdo) {
    die("Erreur critique : Impossible de se connecter à la base de données MySQL. Veuillez vérifier vos accès dans config/database.php.");
}

// 3. Inclure le contrôleur du tableau de bord
require_once "../app/controllers/dashboardController.php";

// 4. Exécuter l'affichage du tableau de bord
$controller = new dashboardController();
$controller->index();
?>
