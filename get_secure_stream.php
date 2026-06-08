<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';

if (empty($_SESSION['idetudiant']) && empty($_SESSION['user_id'])) {
    die("Authentification requise.");
}

$idAM = intval($_GET['id'] ?? 0);
if ($idAM <= 0) {
    die("Requête invalide.");
}

$sql = "SELECT * FROM ancien_memoire WHERE idAM = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $idAM);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) {
    die("Mémoire non instancié.");
}

// Auto-détection de la colonne fichier
$fileDBName = '';
foreach (['fichier', 'fichier_pdf', 'chemin', 'pdf', 'url_pdf', 'document'] as $col) {
    if (!empty($row[$col])) {
        $fileDBName = $row[$col];
        break;
    }
}

// Détection de l'emplacement réel sur votre serveur
$filePath = "";
if (!empty($fileDBName)) {
    if (strpos($fileDBName, '/') !== false) {
        $filePath = $fileDBName;
    } else {
        $filePath = "uploads/exemples/" . $fileDBName;
        if (!file_exists(__DIR__ . '/../../../' . $filePath)) {
            $filePath = "uploads/" . $fileDBName;
        }
    }
}

// Si absent, utiliser le PDF exemple par défaut pour l'expérience étudiant
$realPath = __DIR__ . '/../../../' . $filePath;
if (empty($filePath) || !file_exists($realPath)) {
    $realPath = __DIR__ . '/../../../uploads/exemples/sample_thesis.pdf';
}

if (!file_exists($realPath)) {
    die("Le document physique n'est pas ou n'est plus disponible sur le serveur.");
}

// Envoyer les en-têtes de flux binaire pour PDF.js en empêchant l'invitation au téléchargement
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="document_protect.pdf"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
header('Cache-Control: private, no-transform, no-store, must-revalidate');

readfile($realPath);
exit;