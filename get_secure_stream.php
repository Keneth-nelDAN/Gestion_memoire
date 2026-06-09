<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';

// Barrière de sécurité : Seuls les utilisateurs connectés ont le droit de lire le flux binaire
if (empty($_SESSION['idetudiant']) && empty($_SESSION['user_id']) && empty($_SESSION['id_user'])) {
    header("HTTP/1.1 403 Forbidden");
    die("Accès refusé. Veuillez vous authentifier.");
}

$idAM = intval($_GET['id'] ?? 0);
if ($idAM <= 0) {
    header("HTTP/1.1 404 Not Found");
    die("Identifiant d'archive invalide.");
}

// Récupération de la référence binaire du fichier en base de données
$fileDBName = "";
$sql = "SELECT fichier FROM ancien_memoire WHERE idAM = ?";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $idAM);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $fileDBName = $row['fichier'];
    }
}

if (empty($fileDBName)) {
    header("HTTP/1.1 404 Not Found");
    die("Fichier introuvable en bdd.");
}

// Résolution intelligente du chemin réel du PDF (Direction des études)
$possible_paths = [
    __DIR__ . '/../direction_etude/uploads/memoires/' . $fileDBName,
    __DIR__ . '/direction_etude/uploads/memoires/' . $fileDBName,
    $_SERVER['DOCUMENT_ROOT'] . '/Gestion_memoire/app/views/direction_etude/uploads/memoires/' . $fileDBName
];

$filePath = "";
foreach ($possible_paths as $path) {
    if (file_exists($path) && is_file($path)) {
        $filePath = $path;
        break;
    }
}

if (empty($filePath)) {
    header("HTTP/1.1 404 Not Found");
    die("Le document physique n'existe pas sur le serveur.");
}

// Configuration des headers HTTP pour diffuser uniquement le flux brut compressé sans possibilité de mise en cache
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"" . basename($filePath) . "\"");
header("Content-Length: " . filesize($filePath));
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Lecture directe et silencieuse du flux binaire
readfile($filePath);
exit;