<?php
// Connexion MySQLi globale pour compatibilité avec les anciens fichiers
$mysqli_host = 'localhost';
$mysqli_user = 'root';
$mysqli_password = '';
$mysqli_db = 'gestion_memoires';

$conn = mysqli_connect($mysqli_host, $mysqli_user, $mysqli_password, $mysqli_db);

if (!$conn) {
    die('Erreur de connexion MySQL : ' . mysqli_connect_error());
}

// Définir le charset UTF-8
mysqli_set_charset($conn, 'utf8mb4');
