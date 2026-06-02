<?php
// public/visualiser_pdf.php
session_start();

// Protection anti-invités : vous pouvez restreindre l'accès à vos utilisateurs connectés
if (!isset($_SESSION['idetudiant']) && !isset($_SESSION['idde']) && !isset($_SESSION['idprofesseur'])) {
    http_response_code(403);
    die("Accès non autorisé ou session expirée. Veuillez vous connecter.");
}

require_once "../config/database.php";
require_once "../app/models/publication.php"; // Assurez-vous d'utiliser la bonne casse de fichier

$database = new Database();
$pdo = $database->connect();

$idAM = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idAM <= 0) {
    http_response_code(400);
    die("Requête invalide.");
}

// Charger l'enregistrement
$publicationModel = new Publication($pdo);
$memoire = $publicationModel->getById($idAM);

if (!$memoire) {
    http_response_code(404);
    die("Document de mémoire introuvable.");
}

$file_path = "../uploads/" . $memoire['fichier'];

if (!file_exists($file_path)) {
    http_response_code(404);
    die("Fichier PDF physique absent du serveur.");
}

// Envoyer les en-têtes HTTP requis pour diffuser le flux PDF
header('Content-Type: application/pdf');

// 'inline' force le navigateur à ouvrir le PDF à l'écran plutôt que de proposer l'enregistrement immédiat.
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Stream binaire direct
readfile($file_path);
exit;