<?php
// Connexion mysqli légère pour les parties legacy du code utilisant mysqli
$mysqli_host = '127.0.0.1';
$mysqli_user = 'root';
$mysqli_pass = '';
$mysqli_db   = 'gestion_memoires';

$conn = mysqli_connect($mysqli_host, $mysqli_user, $mysqli_pass, $mysqli_db);
if (!$conn) {
    error_log('legacy_db.php: erreur de connexion mysqli: ' . mysqli_connect_error());
    die('Erreur de connexion à la base de données (mysqli).');
}
mysqli_set_charset($conn, 'utf8mb4');
