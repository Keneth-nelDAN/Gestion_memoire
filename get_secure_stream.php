<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mysqli_config.php';

// Désactiver l'affichage des erreurs textuelles pour éviter de corrompre le PDF
ini_set('display_errors', 0);
error_reporting(0);

// Barrière de sécurité
if (empty($_SESSION['idetudiant']) && empty($_SESSION['idprof']) && empty($_SESSION['idde'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Accès refusé.");
}

$idAM = intval($_GET['id'] ?? 0);
if ($idAM <= 0) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

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
    mysqli_stmt_close($stmt);
}

if (empty($fileDBName)) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

// CORRECTION DES CHEMINS : Vu que nous sommes à la racine du projet
$possible_paths = [
    __DIR__ . '/app/views/direction_etude/uploads/memoires/' . $fileDBName,
    __DIR__ . '/app/views/memoire/uploads/memoires/' . $fileDBName,
    $_SERVER['DOCUMENT_ROOT'] . '/Gestion_memoire/app/views/direction_etude/uploads/memoires/' . $fileDBName,
    $_SERVER['DOCUMENT_ROOT'] . '/Gestion_memoire/app/views/memoire/uploads/memoires/' . $fileDBName
];

$filePath = "";
foreach ($possible_paths as $path) {
    if (!empty($path) && file_exists($path) && is_file($path)) {
        $filePath = $path;
        break;
    }
}

if (empty($filePath)) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

if (ob_get_length()) {
    ob_end_clean();
}

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"" . basename($filePath) . "\"");
header("Content-Length: " . filesize($filePath));
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

readfile($filePath);
exit;