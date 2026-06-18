<?php
// get_pdf.php (À placer à la racine de votre dossier public : /public/get_pdf.php)
session_start();

// 1. Protection : Renvoyer une erreur si l'utilisateur n'est pas identifié
if (!isset($_SESSION['idetudiant'])) {
    http_response_code(403);
    die("Accès refusé. Vous devez être connecté.");
}

require_once "../config/database.php";

$idAM = $_GET['id'] ?? null;
if (!$idAM) {
    http_response_code(400);
    die("Mémoire non spécifié.");
}

$database = new Database();
$pdo = $database->connect();

// Récupération sécurisée du fichier dans la base de données
$stmt = $pdo->prepare("SELECT file_name FROM ancien_memoire WHERE idAM = ?");
$stmt->execute([$idAM]);
$memoire = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$memoire) {
    http_response_code(404);
    die("Fichier introuvable.");
}

$filepath = realpath(__DIR__ . "/../app/memoire/uploads/memoires/") . DIRECTORY_SEPARATOR . $memoire['file_name'];

if (!file_exists($filepath)) {
    http_response_code(404);
    die("Le document physique n'existe pas ou a été déplacé.");
}

// Envoyer des Headers interdisant la mise en cache et forçant l'affichage Inline
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"lecteur_memoire.pdf\"");
header("Content-Length: " . filesize($filepath));

// Streamer directement le contenu brute
readfile($filepath);
exit;