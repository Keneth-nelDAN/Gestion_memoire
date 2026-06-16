<?php
session_start();
// 1. Inclusion des configurations de base de données (PDO et MySQLi)
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php'; // <-- CORRECTION : Importation essentielle pour $conn
require_once __DIR__ . '/de_helpers.php';

function upload_file_for_memoire($file, $upload_dir, &$error) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Erreur pendant l'envoi du fichier.";
        return false;
    }
    if ($file['size'] > 12 * 1024 * 1024) {
        $error = "Chaque fichier doit peser 12 Mo maximum.";
        return false;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if ($mime !== 'application/pdf') {
        $error = "Seuls les fichiers autorisés sont acceptés.";
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

function split_author($line, $fallback_index) {
    $line = trim($line);
    if ($line === '') {
        return ['nom' => 'Auteur ' . $fallback_index, 'prenom' => ''];
    }
    $parts = preg_split('/\s+/', $line);
    if (count($parts) === 1) {
        return ['nom' => $parts[0], 'prenom' => ''];
    }
    $nom = array_pop($parts);
    return ['nom' => $nom, 'prenom' => implode(' ', $parts)];
}

$active_page = 'publications';
$de_profile = get_de_profile($conn);
$nom_de = $de_profile['nom_de'];
$initiales_de = $de_profile['initiales_de'];
$idde = isset($_SESSION['idde']) ? (int) $_SESSION['idde'] : null;

// 2. CORRECTION : Remonter de 3 niveaux pour atteindre la racine puis cibler le dossier des mémoires
$upload_dir = __DIR__ . '/../memoire/uploads/memoires/';

$success = '';
$error = '';
$filieres = mysqli_query($conn, "SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere ASC");
$centres = get_de_centres($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idfiliere = (int) ($_POST['batch_idfiliere'] ?? 0);
    $idCentre = ($_POST['batch_idCentre'] ?? '') !== '' ? (int) $_POST['batch_idCentre'] : null;
    $annee = trim($_POST['batch_annee'] ?? '');
    $maitre = trim($_POST['batch_maitre'] ?? '');
    $examinateur = trim($_POST['batch_examinateur'] ?? '');
    $president = trim($_POST['batch_president'] ?? '');
    $titres = preg_split('/\R/', trim($_POST['batch_titres'] ?? '')) ?: [];
    $auteurs = preg_split('/\R/', trim($_POST['batch_auteurs'] ?? '')) ?: [];
    $inserted = 0;

    if ($idfiliere <= 0 || empty($_FILES['fichiers']['name'][0])) {
        $error = "Sélectionnez une filière et au moins un fichier.";
    } else {
        foreach ($_FILES['fichiers']['name'] as $index => $name) {
            $file = [
                'name' => $_FILES['fichiers']['name'][$index],
                'type' => $_FILES['fichiers']['type'][$index],
                'tmp_name' => $_FILES['fichiers']['tmp_name'][$index],
                'error' => $_FILES['fichiers']['error'][$index],
                'size' => $_FILES['fichiers']['size'][$index],
            ];
            $local_error = '';
            $fichier = upload_file_for_memoire($file, $upload_dir, $local_error);
            if ($fichier === false) {
                $error = $local_error;
                continue;
            }
            $theme = trim($titres[$index] ?? '');
            if ($theme === '') {
                $theme = str_replace(['_', '-'], ' ', pathinfo($name, PATHINFO_FILENAME));
            }
            $author = split_author($auteurs[$index] ?? '', $index + 1);
            $statut = 'publie';
            $source = 'de_lot';
            $stmt = mysqli_prepare($conn, "INSERT INTO ancien_memoire (nomAut, prenomAut, theme, idfiliere, idCentre, annee_academique, maitre_memoire, examinateur, president_jury, fichier, statut, source, publie_par) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sssiisssssssi', $author['nom'], $author['prenom'], $theme, $idfiliere, $idCentre, $annee, $maitre, $examinateur, $president, $fichier, $statut, $source, $idde);
            if (mysqli_stmt_execute($stmt)) {
                $inserted++;
            }
        }
        if ($inserted > 0) {
            $success = $inserted . " mémoire(s) publié(s) avec succès.";
        } elseif ($error === '') {
            $error = "Aucun mémoire n'a pu être publié.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Publier plusieurs lots</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <?php include __DIR__ . '/../partials/sidebar_DE.php'; ?>
    <main class="workspace">
        <header class="workspace-header">
            <div>
                <span class="overline">Publication par lot</span>
                <h1>Publier plusieurs mémoires</h1>
                <p>Envoyez plusieurs fichiers et créez automatiquement un mémoire par fichier.</p>
            </div>
            <a class="btn-gold" href="publier_memoire.php"><i class="fa-solid fa-file-circle-plus"></i> Publier un mémoire</a>
        </header>
        <?php if ($success): ?><div class="alert success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form class="publication-card large" method="post" enctype="multipart/form-data">
            <div class="form-title"><i class="fa-solid fa-layer-group"></i><div><h2>Informations communes du lot</h2><p>Chaque fichier créera une ligne dans les anciens mémoires.</p></div></div>
            <label>Fichiers</label><input type="file" name="fichiers[]" accept="application/pdf" multiple required>
            <small class="hint">Les titres peuvent être déduits des noms de fichiers si la liste ci-dessous est vide.</small>
            <label>Titres, un par ligne</label><textarea name="batch_titres" rows="5" placeholder="Titre du fichier 1&#10;Titre du fichier 2"></textarea>
            <label>Auteurs, un par ligne</label><textarea name="batch_auteurs" rows="4" placeholder="Prénom Nom&#10;Prénom Nom"></textarea>
            <div class="field-row">
                <div><label>Filière commune</label><select name="batch_idfiliere" required><option value="">Sélectionner</option><?php while ($filiere = mysqli_fetch_assoc($filieres)): ?><option value="<?= (int) $filiere['idfiliere'] ?>"><?= htmlspecialchars($filiere['nom_filiere'], ENT_QUOTES, 'UTF-8') ?></option><?php endwhile; ?></select></div>
                <div><label>Centre commun</label><select name="batch_idCentre"><option value="">Non défini</option><?php if ($centres) { while ($centre = mysqli_fetch_assoc($centres)): ?><option value="<?= (int) $centre['idCentre'] ?>"><?= htmlspecialchars($centre['nomCentre'], ENT_QUOTES, 'UTF-8') ?></option><?php endwhile; } ?></select></div>
            </div>
            <div class="field-row"><div><label>Année académique</label><input name="batch_annee" placeholder="2025-2026"></div><div><label>Maître mémoire commun</label><input name="batch_maitre"></div></div>
            <label>Examinateur commun</label><input name="batch_examinateur">
            <label>Président du jury commun</label><input name="batch_president">
            <button class="btn-blue full" type="submit"><i class="fa-solid fa-cloud-arrow-up"></i> Publier le lot</button>
        </form>
    </main>
</div>
</body>
</html>