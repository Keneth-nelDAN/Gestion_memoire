<?php
session_start();

// 1. Inclusions des configurations de base et mysqli
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';

// Sécurité : Vérifiez que l'utilisateur est bien connecté en tant que DE
if (!isset($_SESSION['idde'])) {
    header('Location: login.php'); // Rediriger s'il n'est pas connecté
    exit;
}

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    // 1. Récupération des informations sur le fichier lié pour suppression physique
    $stmt = mysqli_prepare($conn, "SELECT fichier FROM ancien_memoire WHERE idAM = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $memoire = mysqli_fetch_assoc($result);

    if ($memoire) {
        $nom_fichier = $memoire['fichier'];
        
        // Dossier exact de stockage des publications
        $upload_dir = __DIR__ . '/uploads/memoires/';
        $chemin_complet = rtrim($upload_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nom_fichier;

        // Suppression du fichier PDF sur le disque si existant
        if (!empty($nom_fichier) && file_exists($chemin_complet)) {
            unlink($chemin_complet);
        }

        // 2. Suppression de la ligne associée dans la base de données
        $delete_stmt = mysqli_prepare($conn, "DELETE FROM ancien_memoire WHERE idAM = ?");
        mysqli_stmt_bind_param($delete_stmt, 'i', $id);

        if (mysqli_stmt_execute($delete_stmt)) {
            // Suppression réussie
            header('Location: dashboard_de.php?status=deleted');
            exit;
        } else {
            // Erreur lors de l'exécution de la suppression SQL
            header('Location: dashboard_de.php?status=error_delete');
            exit;
        }
    } else {
        // Mémoire introuvable dans la base
        header('Location: dashboard_de.php?status=not_found');
        exit;
    }
} else {
    // ID fourni invalide ou manquant
    header('Location: dashboard_de.php?status=invalid_id');
    exit;
}