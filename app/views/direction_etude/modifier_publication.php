<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';
require_once __DIR__ . '/de_helpers.php';

function upload_pdf_file($file, $upload_dir, &$error) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
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
        $error = "Seuls les fichiers sont acceptés.";
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

$active_page = 'publications';
$de_profile = get_de_profile($conn);
$nom_de = $de_profile['nom_de'];
$initiales_de = $de_profile['initiales_de'];
$id = (int) ($_GET['id'] ?? 0);
$error = '';
$success = '';
$upload_dir = __DIR__ . 'Gestion_memoire/public/assets/uploads/memoires';

$stmt = mysqli_prepare($conn, "SELECT am.*, f.nom_filiere, c.nomCentre FROM ancien_memoire am LEFT JOIN filiere f ON f.idfiliere = am.idfiliere LEFT JOIN centre c ON c.idCentre = am.idCentre WHERE am.idAM = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$memoire = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$memoire) {
    header('Location: dashboard_de.php');
    exit;
}

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
    $statut = trim($_POST['statut'] ?? 'en_attente');
    $fichier = $memoire['fichier'];

    if ($theme === '' || $nomAut === '' || $idfiliere <= 0) {
        $error = "Veuillez renseigner le thème, le nom de l'auteur et la filière.";
    } else {
        $new_file = upload_pdf_file($_FILES['fichier'] ?? null, $upload_dir, $error);
        if ($new_file !== false) {
            if ($new_file !== null) {
                $fichier = $new_file;
            }

            $up = mysqli_prepare($conn, "UPDATE ancien_memoire SET nomAut = ?, prenomAut = ?, theme = ?, idfiliere = ?, idCentre = ?, annee_academique = ?, maitre_memoire = ?, examinateur = ?, president_jury = ?, fichier = ?, statut = ? WHERE idAM = ?");
            mysqli_stmt_bind_param($up, 'sssiissssssi', $nomAut, $prenomAut, $theme, $idfiliere, $idCentre, $annee, $maitre, $examinateur, $president, $fichier, $statut, $id);

            if (mysqli_stmt_execute($up)) {
                header('Location: dashboard_de.php?status=updated');
                exit;
            }
            $error = "Mise à jour impossible : " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Modifier publication</title>
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
                <h1>Modifier le mémoire</h1>
                <p>Mise à jour des métadonnées, du jury et du fichier.</p>
            </div>
            <a class="btn-blue" href="dashboard_de.php"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        </header>

        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>

        <form class="publication-card large" method="post" enctype="multipart/form-data">
            <div class="form-title">
                <i class="fa-solid fa-pen-to-square"></i>
                <div>
                    <h2><?= e($memoire['theme']) ?></h2>
                    <p><?= e(trim($memoire['prenomAut'] . ' ' . $memoire['nomAut'])) ?></p>
                </div>
            </div>

            <label>Thème du mémoire</label>
            <textarea name="theme" rows="3" required><?= e($memoire['theme']) ?></textarea>

            <div class="field-row">
                <div><label>Prénom auteur(s)</label><input name="prenomAut" value="<?= e($memoire['prenomAut']) ?>"></div>
                <div><label>Nom auteur(s)</label><input name="nomAut" value="<?= e($memoire['nomAut']) ?>" required></div>
            </div>

            <div class="field-row">
                <div>
                    <label>Filière</label>
                    <select name="idfiliere" required>
                        <?php while ($filiere = mysqli_fetch_assoc($filieres)): ?>
                            <option value="<?= (int) $filiere['idfiliere'] ?>" <?= (int) $memoire['idfiliere'] === (int) $filiere['idfiliere'] ? 'selected' : '' ?>><?= e($filiere['nom_filiere']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>Centre</label>
                    <select name="idCentre">
                        <option value="">Non défini</option>
                        <?php if ($centres) { while ($centre = mysqli_fetch_assoc($centres)): ?>
                            <option value="<?= (int) $centre['idCentre'] ?>" <?= (int) $memoire['idCentre'] === (int) $centre['idCentre'] ? 'selected' : '' ?>><?= e($centre['nomCentre']) ?></option>
                        <?php endwhile; } ?>
                    </select>
                </div>
            </div>

            <div class="field-row">
                <div><label>Année académique</label><input name="annee_academique" value="<?= e($memoire['annee_academique']) ?>"></div>
                <div>
                    <label>Statut</label>
                    <select name="statut">
                        <option value="publie" <?= $memoire['statut'] === 'publie' ? 'selected' : '' ?>>Publié</option>
                        <option value="en_attente" <?= $memoire['statut'] === 'en_attente' ? 'selected' : '' ?>>Validé non publié</option>
                        <option value="brouillon" <?= $memoire['statut'] === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                    </select>
                </div>
            </div>

            <label>Maître mémoire</label><input name="maitre_memoire" value="<?= e($memoire['maitre_memoire']) ?>">
            <label>Examinateur</label><input name="examinateur" value="<?= e($memoire['examinateur']) ?>">
            <label>Président du jury</label><input name="president_jury" value="<?= e($memoire['president_jury']) ?>">
            <label>Remplacer le fichier</label><input type="file" name="fichier" accept="application/pdf">
            <small class="hint">Fichier actuel : <?= e($memoire['fichier'] ?: 'aucun fichier') ?></small>

            <div class="form-actions">
                <a class="btn-muted" href="dashboard_de.php">Annuler</a>
                <button class="btn-gold" type="submit"><i class="fa-solid fa-floppy-disk"></i> Enregistrer</button>
            </div>
        </form>
    </main>
</div>
</body>
</html>




