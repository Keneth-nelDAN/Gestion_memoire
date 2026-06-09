<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';
require_once __DIR__ . '/de_helpers.php';

// Vérifier que l'utilisateur est un directeur
if (empty($_SESSION['idde']) && (empty($_SESSION['user']) || $_SESSION['user']['type'] !== 'directeur')) {
    header('Location: ../auth/connexion.php');
    exit;
}

function upload_pdf_file($file, $upload_dir, &$error) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

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
$upload_dir = __DIR__ . 'Gestion_memoire/public/assets/uploads/memoires';
$success = '';
$error = '';

$filieres = mysqli_query($conn, "SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere ASC");
$centres = get_de_centres($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';

    if ($mode === 'single') {
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
            $fichier = upload_pdf_file($_FILES['fichier'] ?? null, $upload_dir, $error);
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

    if ($mode === 'batch') {
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
                $fichier = upload_pdf_file($file, $upload_dir, $local_error);
                if ($fichier === false) {
                    $error = $local_error;
                    continue;
                }

                $theme = trim($titres[$index] ?? '');
                if ($theme === '') {
                    $theme = pathinfo($name, PATHINFO_FILENAME);
                    $theme = str_replace(['_', '-'], ' ', $theme);
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
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Publications DE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="workspace">
        <header class="workspace-header">
            <div>
                <span class="overline">Direction des Études</span>
                <h1>Publier des mémoires</h1>
                <p>Publication unitaire ou import groupé de plusieurs mémoires.</p>
            </div>
            <a class="btn-blue" href="dashboard_de.php"><i class="fa-solid fa-arrow-left"></i> Retour au dashboard</a>
        </header>

        <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

        <section class="form-grid-two">
            <form class="publication-card" id="single" method="post" enctype="multipart/form-data">
                <input type="hidden" name="mode" value="single">
                <div class="form-title">
                    <i class="fa-solid fa-file-circle-plus"></i>
                    <div>
                        <h2>Publier un mémoire</h2>
                        <p>Pour un seul étudiant ou un groupe d'auteurs.</p>
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
                            <?php mysqli_data_seek($filieres, 0); while ($filiere = mysqli_fetch_assoc($filieres)): ?>
                                <option value="<?= (int) $filiere['idfiliere'] ?>"><?= e($filiere['nom_filiere']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label>Centre</label>
                        <select name="idCentre">
                            <option value="">Non défini</option>
                            <?php if ($centres) { mysqli_data_seek($centres, 0); while ($centre = mysqli_fetch_assoc($centres)): ?>
                                <option value="<?= (int) $centre['idCentre'] ?>"><?= e($centre['nomCentre']) ?></option>
                            <?php endwhile; } ?>
                        </select>
                    </div>
                </div>

                <div class="field-row">
                    <div><label>Année académique</label><input name="annee_academique" placeholder="2025-2026"></div>
                    <div>
                        <label>Statut</label>
                        <select name="statut">
                            <option value="publie">Publié</option>
                            <option value="en_attente">Validé non publié</option>
                            <option value="brouillon">Brouillon</option>
                        </select>
                    </div>
                </div>

                <label>Maître mémoire</label><input name="maitre_memoire">
                <label>Examinateur</label><input name="examinateur">
                <label>Président du jury</label><input name="president_jury">
                <label>Fichier</label><input type="file" name="fichier" accept="application/pdf" required>

                <button class="btn-gold full" type="submit"><i class="fa-solid fa-paper-plane"></i> Publier ce mémoire</button>
            </form>

            <form class="publication-card" id="batch" method="post" enctype="multipart/form-data">
                <input type="hidden" name="mode" value="batch">
                <div class="form-title">
                    <i class="fa-solid fa-layer-group"></i>
                    <div>
                        <h2>Uploader plusieurs mémoires</h2>
                        <p>Ajout en lot : plusieurs fichiers, une ligne créée par fichier.</p>
                    </div>
                </div>

                <label>Fichiers</label>
                <input type="file" name="fichiers[]" accept="application/pdf" multiple required>
                <small class="hint">Les titres peuvent être déduits des noms de fichiers si la liste ci-dessous est vide.</small>

                <label>Titres, un par ligne</label>
                <textarea name="batch_titres" rows="5" placeholder="Titre du fichier 1&#10;Titre du fichier 2"></textarea>

                <label>Auteurs, un par ligne</label>
                <textarea name="batch_auteurs" rows="4" placeholder="Prénom Nom&#10;Prénom Nom"></textarea>

                <div class="field-row">
                    <div>
                        <label>Filière commune</label>
                        <select name="batch_idfiliere" required>
                            <option value="">Sélectionner</option>
                            <?php mysqli_data_seek($filieres, 0); while ($filiere = mysqli_fetch_assoc($filieres)): ?>
                                <option value="<?= (int) $filiere['idfiliere'] ?>"><?= e($filiere['nom_filiere']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label>Centre commun</label>
                        <select name="batch_idCentre">
                            <option value="">Non défini</option>
                            <?php if ($centres) { mysqli_data_seek($centres, 0); while ($centre = mysqli_fetch_assoc($centres)): ?>
                                <option value="<?= (int) $centre['idCentre'] ?>"><?= e($centre['nomCentre']) ?></option>
                            <?php endwhile; } ?>
                        </select>
                    </div>
                </div>

                <div class="field-row">
                    <div><label>Année académique</label><input name="batch_annee" placeholder="2025-2026"></div>
                    <div><label>Maître mémoire commun</label><input name="batch_maitre"></div>
                </div>
                <label>Examinateur commun</label><input name="batch_examinateur">
                <label>Président du jury commun</label><input name="batch_president">

                <button class="btn-blue full" type="submit"><i class="fa-solid fa-cloud-arrow-up"></i> Publier le lot</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>




