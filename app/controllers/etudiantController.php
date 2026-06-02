<?php
// app/controllers/etudiantController.php
session_start();
require_once "../../config/database.php";

$action = $_GET['action'] ?? '';

if ($action === 'deposer') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../views/etudiant/depot_memoire.php");
        exit;
    }

    $database = new Database();
    $pdo = $database->connect();

    // Récupération des données du formulaire
    $theme = trim($_POST['theme']);
    $centre_id = (int)$_POST['centre'];
    $filiere_id = (int)$_POST['filiere'];
    $annee_id = (int)$_POST['annee'];
    $date_soutenance = $_POST['date_soutenance'];
    
    // Identifiant de l'étudiant en session
    $idetudiant = $_SESSION['idetudiant'] ?? 1; // Exemple temporaire si non défini

    // Validation du fichier
    if (!isset($_FILES['memoire_file']) || $_FILES['memoire_file']['error'] !== UPLOAD_ERR_OK) {
        $error_code = $_FILES['memoire_file']['error'] ?? 'Inconnu';
        header("Location: ../views/etudiant/depot_memoire.php?erreur=Fichier non reçu ou corrompu (Code Erreur : " . $error_code . ")");
        exit;
    }

    $file = $_FILES['memoire_file'];
    $filename = $file['name'];
    $filetype = $file['type'];
    $tmp_name = $file['tmp_name'];
    $filesize = $file['size'];

    // Vérifier l'extension
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if ($ext !== 'pdf' || $filetype !== 'application/pdf') {
        header("Location: ../views/etudiant/depot_memoire.php?erreur=Seuls les fichiers de type PDF sont autorisés.");
        exit;
    }

    // Définir le répertoire de destination et s'assurer qu'il existe !
    $target_dir = "../../uploads/";
    if (!is_dir($target_dir)) {
        // Crée le dossier automatiquement avec des droits d'écriture complets
        mkdir($target_dir, 0777, true);
    }

    // Renommer le fichier pour éviter les écrasements d'homonymes et supprimer les espaces
    $safe_title = preg_replace('/[^A-Za-z0-9]/', '_', substr($theme, 0, 50));
    $new_filename = "memoire_" . time() . "_" . $safe_title . ".pdf";
    $target_file = $target_dir . $new_filename;

    // Déplacement effectif du fichier temporaire vers le répertoire uploads
    if (move_uploaded_file($tmp_name, $target_file)) {
        
        try {
            // Insertion en base de données de l'enregistrement de mémoire
            $sql = "INSERT INTO ancien_memoire (theme, idCentre, idfiliere, idAnnee, dateSoutenance, fichier, idetudiant, statut, date_creation) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'attente', NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $theme,
                $centre_id,
                $filiere_id,
                $annee_id,
                $date_soutenance,
                $new_filename, // On conserve uniquement le nom du fichier en BDD
                $idetudiant
            ]);

            // Redirection vers le tableau de bord étudiant en cas de succès
            header("Location: ../views/etudiant/dashboard_etudiant.php?success=Memoire déposé avec succès.");
            exit;

        } catch (PDOException $e) {
            // Supprimer le fichier uploaded si l'insertion MYSQL échoue
            if (file_exists($target_file)) {
                unlink($target_file);
            }
            header("Location: ../views/etudiant/depot_memoire.php?erreur=Erreur de base de données : " . $e->getMessage());
            exit;
        }

    } else {
        header("Location: ../views/etudiant/depot_memoire.php?erreur=Impossible de déplacer le fichier temporaire de l'upload. Veuillez vérifier les permissions d'écriture du dossier uploads/.");
        exit;
    }
}