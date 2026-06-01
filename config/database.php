<?php

$host = "localhost";
$dbname = "gestion_memoires";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );

    // Activer les erreurs PDO (très important)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "Connexion réussie"; // (optionnel pour test)

} catch (Exception $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
