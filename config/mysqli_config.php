<?php
// Connexion MySQLi globale pour compatibilité avec les anciens fichiers
$mysqli_host = '127.0.0.1';
$mysqli_port = 3306;
$mysqli_user = 'root';
$mysqli_password = '';
$mysqli_db = 'gestion_memoires';
$mysqli_socket = '';

$conn = mysqli_init();
if ($mysqli_socket !== '') {
    mysqli_options($conn, MYSQLI_OPT_LOCAL_INFILE, true);
}
if (!mysqli_real_connect($conn, $mysqli_host, $mysqli_user, $mysqli_password, $mysqli_db, $mysqli_port, $mysqli_socket)) {
    die('Erreur de connexion MySQL : ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
