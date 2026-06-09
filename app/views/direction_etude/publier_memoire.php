<?php
session_start();
// 1. INCLUSIONS DES CONFIGURATIONS DE BASE ET MYSQLI (Résout le problème de $conn non définie)
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';
require_once __DIR__ . '/de_helpers.php';

// Sécurité : Définition de la fonction d'échappement 'e' si elle n'existe pas
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

function upload_file_for_memoire($file, $upload_dir, &$error) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        $error = "Veuillez sélectionner un fichier.";
        return false;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Erreur pendant l'envoi du fichier.";
        return false;
    }
    if ($file['size'] > 12 * 1024 * 1024) {
        $error = "Le fichier doit peser 12 Mo maximum.";
        return false;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if ($mime !== 'application/pdf') {
        $error = "Seuls les fichiers de type PDF sont autorisés.";
        return false;
    }
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0775, true);
    }
    $name = 'memoire_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.pdf';
    $destination = rtrim($upload_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $error = "Impossible d'enregistrer le fichier.";
        return false;
    }
    return $name;
}

$active_page = 'publication';
$de_profile = get_de_profile($conn);
$nom_de = $de_profile['nom_de'] ?? 'Direction';
$initiales_de = $de_profile['initiales_de'] ?? 'DE';
$idde = isset($_SESSION['idde']) ? (int) $_SESSION['idde'] : null;

// Dossier exact défini par vos soins
$upload_dir = __DIR__ . '/uploads/memoires/';
$success = '';
$error = '';

$filieres = mysqli_query($conn, "SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere ASC");
$centres = get_de_centres($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomAut = trim($_POST['nomAut'] ?? '');
    $prenomAut = trim($_POST['prenomAut'] ?? '');
    $theme = trim($_POST['theme'] ?? '');
    $idfiliere = (int) ($_POST['idfiliere'] ?? 0);
    $idCentre = ($_POST['idCentre'] ?? '') !== '' ? (int) $_POST['idCentre'] : null;
    $annee = trim($_POST['annee_academique'] ?? '');
    $maitre = trim($_POST['maitre_memoire'] ?? '');
    $examinateur = trim($_POST['examinateur'] ?? '');
    $president = trim($_POST['president_jury'] ?? '');
    $statut = trim($_POST['statut'] ?? 'publie');
    $source = 'de_unitaire';

    if ($theme === '' || $nomAut === '' || $idfiliere <= 0) {
        $error = "Veuillez renseigner au minimum le thème, l'auteur et la filière.";
    } else {
        $fichier = upload_file_for_memoire($_FILES['fichier'] ?? null, $upload_dir, $error);
        if ($fichier !== false) {
            $stmt = mysqli_prepare($conn, "INSERT INTO ancien_memoire (nomAut, prenomAut, theme, idfiliere, idCentre, annee_academique, maitre_memoire, examinateur, president_jury, fichier, statut, source, publie_par) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sssiisssssssi', $nomAut, $prenomAut, $theme, $idfiliere, $idCentre, $annee, $maitre, $examinateur, $president, $fichier, $statut, $source, $idde);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Le mémoire a été publié avec succès.";
            } else {
                $error = "Insertion impossible : " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Publier un mémoire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <?php include __DIR__ . '/../partials/sidebar_DE.php'; ?>
    <main class="workspace">
        <header class="workspace-header">
            <div>
                <span class="overline">Publication</span>
                <h1>Publier un mémoire</h1>
                <p>Ajoutez un mémoire unique dans la table des anciens mémoires.</p>
            </div>
            <a class="btn-blue" href="publier_lots.php"><i class="fa-solid fa-layer-group"></i> Publier plusieurs lots</a>
        </header>

        <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

        <form class="publication-card large" method="post" enctype="multipart/form-data">
            <div class="form-title">
                <i class="fa-solid fa-file-circle-plus"></i>
                <div>
                    <h2>Informations du mémoire</h2>
                    <p>Renseignez les métadonnées avant publication.</p>
                </div>
            </div>
            <label>Thème du mémoire</label>
            <textarea name="theme" rows="3" required></textarea>
            <div class="field-row">
                <div><label>Prénom auteur(s)</label><input name="prenomAut"></div>
                <div><label>Nom auteur(s)</label><input name="nomAut" required></div>
            </div>
            <div class="field-row">
                <div>
                    <label>Filière</label>
                    <select name="idfiliere" required>
                        <option value="">Sélectionner</option>
                        <?php while ($filiere = mysqli_fetch_assoc($filieres)): ?>
                            <option value="<?= (int) $filiere['idfiliere'] ?>"><?= e($filiere['nom_filiere']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>Centre</label>
                    <select name="idCentre">
                        <option value="">Non défini</option>
                        <?php if ($centres) { while ($centre = mysqli_fetch_assoc($centres)): ?>
                            <option value="<?= (int) $centre['idCentre'] ?>"><?= e($centre['nomCentre']) ?></option>
                        <?php endwhile; } ?>
                    </select>
                </div>
            </div>
            <div class="field-row">
                <div><label>Année académique</label><input name="annee_academique" placeholder="2025-2026"></div>
                <div><label>Statut</label><select name="statut"><option value="publie">Publié</option><option value="en_attente">Validé non publié</option><option value="brouillon">Brouillon</option></select></div>
            </div>
            <label>Maître mémoire</label><input name="maitre_memoire">
            <label>Examinateur</label><input name="examinateur">
            <label>Président du jury</label><input name="president_jury">
            <label>Fichier</label><input type="file" name="fichier" accept="application/pdf" required>
            <button class="btn-gold full" type="submit"><i class="fa-solid fa-paper-plane"></i> Publier ce mémoire</button>
        </form>
    </main>
</div>
</body>
</html>