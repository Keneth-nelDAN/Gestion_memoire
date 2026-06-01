<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

if (empty($_SESSION['idetudiant'])) {
    header('Location: ../auth/connexion.php');
    exit;
}

$idetudiant = (int) $_SESSION['idetudiant'];
$student_stmt = mysqli_prepare($conn, 'SELECT * FROM etudiant WHERE idetudiant = ? LIMIT 1');
mysqli_stmt_bind_param($student_stmt, 'i', $idetudiant);
mysqli_stmt_execute($student_stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($student_stmt));

if (($student['type_compte'] ?? 'consultant') !== 'diplome') {
    header('Location: dashboard_etudiant.php');
    exit;
}

$success = '';
$error = '';
$upload_dir = __DIR__ . '/../direction_etude/uploads/memoires';
$filieres = mysqli_query($conn, "SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere ASC");
$centres = mysqli_query($conn, "SELECT idCentre, nomCentre FROM centre ORDER BY FIELD(nomCentre, 'Agla', 'Akpakpa', 'Gbegamey', 'Calavi', 'Porto-novo'), nomCentre");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = trim($_POST['theme'] ?? '');
    $idfiliere = (int) ($_POST['idfiliere'] ?? $student['idfiliere']);
    $idCentre = ($_POST['idCentre'] ?? '') !== '' ? (int) $_POST['idCentre'] : null;
    $annee = trim($_POST['annee_academique'] ?? '');
    $maitre = trim($_POST['maitre_memoire'] ?? '');
    $examinateur = trim($_POST['examinateur'] ?? '');
    $president = trim($_POST['president_jury'] ?? '');

    if ($theme === '' || $idfiliere <= 0) {
        $error = 'Veuillez renseigner le thème et la filière.';
    } elseif (empty($_FILES['fichier']['name']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Veuillez sélectionner un fichier PDF.';
    } elseif ($_FILES['fichier']['size'] > 12 * 1024 * 1024) {
        $error = 'Le fichier doit peser 12 Mo maximum.';
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['fichier']['tmp_name']);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            $error = 'Seuls les fichiers PDF sont acceptés.';
        } else {
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0775, true);
            }
            $fichier = 'memoire_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.pdf';
            $destination = $upload_dir . DIRECTORY_SEPARATOR . $fichier;

            if (move_uploaded_file($_FILES['fichier']['tmp_name'], $destination)) {
                $source = 'etudiant_diplome';
                $statut = 'en_attente';
                $stmt = mysqli_prepare($conn, "INSERT INTO ancien_memoire (nomAut, prenomAut, theme, idfiliere, idCentre, annee_academique, maitre_memoire, examinateur, president_jury, fichier, statut, source, idetudiant) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'sssiisssssssi', $student['nom'], $student['prenom'], $theme, $idfiliere, $idCentre, $annee, $maitre, $examinateur, $president, $fichier, $statut, $source, $idetudiant);
                if (mysqli_stmt_execute($stmt)) {
                    $success = 'Votre mémoire a été déposé avec succès. Il est en attente de validation.';
                } else {
                    $error = 'Impossible d enregistrer le mémoire.';
                }
            } else {
                $error = 'Impossible d enregistrer le fichier.';
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
    <title>GénieMémoire - Dépôt mémoire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../direction_etude/style.css">
</head>
<body>
<main class="student-shell">
    <header class="workspace-header">
        <div>
            <span class="overline">Compte diplômé</span>
            <h1>Déposer mon mémoire</h1>
            <p>Soumettez votre fichier PDF et ses informations pour validation.</p>
        </div>
        <a class="btn-blue" href="dashboard_etudiant.php"><i class="fa-solid fa-arrow-left"></i> Tableau de bord</a>
    </header>

    <?php if ($success): ?><div class="alert success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <form class="publication-card large" method="post" enctype="multipart/form-data">
        <div class="form-title">
            <i class="fa-solid fa-file-circle-plus"></i>
            <div>
                <h2>Informations du mémoire</h2>
                <p>Le dépôt sera rattaché à votre compte diplômé.</p>
            </div>
        </div>
        <label>Thème</label>
        <textarea name="theme" rows="3" required></textarea>
        <div class="field-row">
            <div>
                <label>Filière</label>
                <select name="idfiliere" required>
                    <?php if ($filieres) { while ($filiere = mysqli_fetch_assoc($filieres)): ?>
                        <option value="<?= (int) $filiere['idfiliere'] ?>" <?= (int) $student['idfiliere'] === (int) $filiere['idfiliere'] ? 'selected' : '' ?>><?= htmlspecialchars($filiere['nom_filiere'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endwhile; } ?>
                </select>
            </div>
            <div>
                <label>Centre</label>
                <select name="idCentre">
                    <option value="">Non défini</option>
                    <?php if ($centres) { while ($centre = mysqli_fetch_assoc($centres)): ?>
                        <option value="<?= (int) $centre['idCentre'] ?>"><?= htmlspecialchars($centre['nomCentre'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endwhile; } ?>
                </select>
            </div>
        </div>
        <div class="field-row">
            <div><label>Année académique</label><input name="annee_academique" placeholder="2025-2026"></div>
            <div><label>Maître mémoire</label><input name="maitre_memoire"></div>
        </div>
        <label>Examinateur</label><input name="examinateur">
        <label>Président du jury</label><input name="president_jury">
        <label>Fichier PDF</label><input type="file" name="fichier" accept="application/pdf" required>
        <button class="btn-gold full" type="submit"><i class="fa-solid fa-cloud-arrow-up"></i> Déposer le mémoire</button>
    </form>
</main>
</body>
</html>
