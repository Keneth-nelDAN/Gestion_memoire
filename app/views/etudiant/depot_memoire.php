<?php
session_start();
require_once __DIR__ . '/../../../config/legacy_db.php';

if (empty($_SESSION['idetudiant'])) {
    header('Location: ../auth/connexion.php');
    exit;
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function column_exists($conn, $table, $column) {
    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    mysqli_stmt_bind_param($stmt, 'ss', $table, $column);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return !empty($row['total']);
}

function bind_dynamic($stmt, $types, &$params) {
    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    return mysqli_stmt_bind_param($stmt, $types, ...$refs);
}

function professor_name_by_id($conn, $idprof) {
    $stmt = mysqli_prepare($conn, 'SELECT nom, prenom FROM professeur WHERE idprof = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $idprof);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $row ? trim($row['prenom'] . ' ' . $row['nom']) : '';
}

function notify_professor($conn, $idprof, $message) {
    $stmt = mysqli_prepare($conn, 'INSERT INTO notification (message, statut_lecture, date_notification, idprof) VALUES (?, 0, CURRENT_TIMESTAMP, ?)');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'si', $message, $idprof);
        mysqli_stmt_execute($stmt);
    }
}

$idetudiant = (int) $_SESSION['idetudiant'];
$student_stmt = mysqli_prepare($conn, 'SELECT * FROM etudiant WHERE idetudiant = ? LIMIT 1');
mysqli_stmt_bind_param($student_stmt, 'i', $idetudiant);
mysqli_stmt_execute($student_stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($student_stmt));

if (!$student || (($student['type_compte'] ?? 'consultant') !== 'diplome')) {
    header('Location: dashboard_etudiant.php');
    exit;
}

$success = '';
$error = '';
$upload_dir = __DIR__ . '/../direction_etude/uploads/memoires';
$filieres = mysqli_query($conn, "SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere ASC");
$centres = mysqli_query($conn, "SELECT idCentre, nomCentre FROM centre ORDER BY FIELD(nomCentre, 'Agla', 'Akpakpa', 'Gbegamey', 'Calavi', 'Porto-novo'), nomCentre");
$professeurs_result = mysqli_query($conn, "SELECT idprof, nom, prenom, email FROM professeur ORDER BY prenom ASC, nom ASC");
$professeurs = $professeurs_result ? mysqli_fetch_all($professeurs_result, MYSQLI_ASSOC) : [];
$annee_default = date('Y') . '-' . (date('Y') + 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = trim($_POST['theme'] ?? '');
    $idfiliere = (int) ($_POST['idfiliere'] ?? $student['idfiliere']);
    $idCentre = ($_POST['idCentre'] ?? '') !== '' ? (int) $_POST['idCentre'] : null;
    $annee = trim($_POST['annee_academique'] ?? '');
    $date_soutenance = trim($_POST['date_soutenance'] ?? '');
    $mots_cles = trim($_POST['mots_cles'] ?? '');
    $id_maitre = (int) ($_POST['maitre_memoire'] ?? 0);
    $id_examinateur = (int) ($_POST['examinateur'] ?? 0);
    $id_president = (int) ($_POST['president_jury'] ?? 0);
    $maitre = professor_name_by_id($conn, $id_maitre);
    $examinateur = professor_name_by_id($conn, $id_examinateur);
    $president = professor_name_by_id($conn, $id_president);

    if ($theme === '' || $idfiliere <= 0 || $idCentre === null || $annee === '' || $id_maitre <= 0 || $id_examinateur <= 0 || $id_president <= 0 || $maitre === '' || $examinateur === '' || $president === '') {
        $error = 'Veuillez renseigner toutes les informations obligatoires.';
    } elseif (empty($_FILES['fichier']['name']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Veuillez sélectionner un fichier.';
    } elseif ($_FILES['fichier']['size'] > 50 * 1024 * 1024) {
        $error = 'Le fichier doit peser 50 Mo maximum.';
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['fichier']['tmp_name']);
        finfo_close($finfo);

        $allowed = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        if (!isset($allowed[$mime])) {
            $error = 'Format non autorisé. Formats acceptés : PDF ou Word.';
        } else {
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0775, true);
            }

            $extension = $allowed[$mime];
            $fichier = 'memoire_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $destination = $upload_dir . DIRECTORY_SEPARATOR . $fichier;

            if (move_uploaded_file($_FILES['fichier']['tmp_name'], $destination)) {
                $source = 'etudiant_diplome';
                $statut = 'en_attente';
                $columns = ['nomAut', 'prenomAut', 'theme', 'idfiliere', 'idCentre', 'annee_academique', 'maitre_memoire', 'examinateur', 'president_jury', 'fichier', 'statut', 'source', 'idetudiant'];
                $placeholders = array_fill(0, count($columns), '?');
                $types = 'sssiisssssssi';
                $params = [$student['nom'], $student['prenom'], $theme, $idfiliere, $idCentre, $annee, $maitre, $examinateur, $president, $fichier, $statut, $source, $idetudiant];

                if (column_exists($conn, 'ancien_memoire', 'mots_cles')) {
                    $columns[] = 'mots_cles';
                    $placeholders[] = '?';
                    $types .= 's';
                    $params[] = $mots_cles;
                }
                if (column_exists($conn, 'ancien_memoire', 'date_soutenance')) {
                    $columns[] = 'date_soutenance';
                    $placeholders[] = '?';
                    $types .= 's';
                    $params[] = $date_soutenance;
                }

                $sql = 'INSERT INTO ancien_memoire (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
                $stmt = mysqli_prepare($conn, $sql);
                bind_dynamic($stmt, $types, $params);

                if (mysqli_stmt_execute($stmt)) {
                    $message = 'Nouveau mémoire déposé par ' . trim($student['prenom'] . ' ' . $student['nom']) . ' : ' . $theme;
                    foreach (array_unique([$id_maitre, $id_examinateur, $id_president]) as $idprof) {
                        notify_professor($conn, (int) $idprof, $message);
                    }
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
    <style>
        :root{--navy:#173763;--navy-deep:#183864;--gold:#D4AF37;--cream:#F7F5F2;--paper:#fff;--ink:#1d2d44;--muted:#6b7280;--line:#e8e2d8;--soft:#fbfaf7;--success:#087f5b;--danger:#b91c1c;--shadow:0 14px 35px rgba(29,45,68,.08)}
        *{box-sizing:border-box} body{margin:0;background:var(--cream);color:var(--ink);font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px} a{text-decoration:none;color:inherit}
        .topbar{height:58px;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:space-between;padding:0 28px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
        .brand{display:flex;align-items:center;gap:10px}.brand-mark{width:32px;height:32px;border-radius:8px;background:var(--gold);color:var(--navy);display:grid;place-items:center;font-family:Georgia,serif;font-weight:800}.brand strong{display:block;font-size:15px}.brand span{display:block;font-size:11px;color:#d9e1ef}.top-actions{display:flex;align-items:center;gap:12px}.top-icon{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:rgba(255,255,255,.08);position:relative}.dot{position:absolute;right:6px;top:5px;width:8px;height:8px;background:var(--gold);border-radius:50%}.student-avatar{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:var(--gold);color:var(--navy);font-weight:800}
        .page{max-width:1120px;margin:28px auto 60px;padding:0 18px}.page-title{margin-bottom:28px}.page-title h1{margin:0;color:var(--navy-deep);font-family:Georgia,serif;font-size:31px}.page-title p{margin:8px 0 0;color:#5c6470;font-size:13px}.alert{border-radius:12px;padding:14px 16px;margin-bottom:18px;font-weight:700}.alert.success{background:#e8f7ef;color:var(--success)}.alert.error{background:#fff0f0;color:var(--danger)}
        .deposit-form{display:grid;gap:24px}.section-card{background:var(--paper);border:1px solid var(--line);border-radius:14px;box-shadow:var(--shadow);padding:26px}.section-head{display:flex;align-items:center;gap:12px;border-bottom:1px solid var(--line);padding-bottom:18px;margin-bottom:22px}.step{width:28px;height:28px;border-radius:50%;background:var(--navy);color:#fff;display:grid;place-items:center;font-weight:800;font-size:13px}.section-head h2{margin:0;color:var(--navy-deep);font-family:Georgia,serif;font-size:22px}.section-text{margin:0 0 22px;color:#5f6875;font-size:13px}.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:18px}.field{margin-bottom:18px}.field.full{grid-column:1/-1}.field label{display:block;margin-bottom:8px;color:#243247;text-transform:uppercase;letter-spacing:.08em;font-size:11px;font-weight:800}.field input,.field select,.keyword-box{width:100%;min-height:44px;border:1px solid #d9d4ca;border-radius:8px;background:#fffdf9;padding:11px 13px;font:inherit;outline:none;transition:.18s}.field input:focus,.field select:focus,.keyword-box:focus-within{border-color:var(--gold);box-shadow:0 0 0 4px rgba(212,175,55,.14)}.hint{display:block;margin-top:7px;color:#8a9099;font-size:12px}.keyword-box{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:8px 10px}.tag{display:inline-flex;align-items:center;gap:6px;background:#eaf2fb;color:#173763;border-radius:999px;padding:6px 9px;font-size:12px;font-weight:700}.tag button{border:0;background:none;color:#173763;cursor:pointer}.keyword-box input{border:0;background:transparent;min-height:28px;box-shadow:none;flex:1;min-width:160px;padding:0}.keyword-box input:focus{box-shadow:none}
        .jury-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.jury-card{border:1px solid #e3ded5;border-radius:13px;background:#fffdf9;padding:20px}.jury-top{display:flex;align-items:center;gap:13px;margin-bottom:18px}.jury-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center}.jury-card:nth-child(1) .jury-icon{background:#edf6ff;color:#173763}.jury-card:nth-child(2) .jury-icon{background:#effbf7;color:#087f5b}.jury-card:nth-child(3) .jury-icon{background:#faf5df;color:#8a6408}.jury-top strong{display:block;color:var(--navy);font-size:15px}.jury-top span{display:block;color:#8a9099;font-size:12px;margin-top:3px}.jury-card input,.jury-card select{width:100%;min-height:44px;border:1px solid #d9d4ca;border-radius:9px;padding:11px 13px;font:inherit;background:#fff;outline:none}.jury-card input:focus,.jury-card select:focus{border-color:var(--gold);box-shadow:0 0 0 4px rgba(212,175,55,.14)}
        .drop-zone{position:relative;border:1.5px dashed #d9d4ca;border-radius:13px;min-height:150px;display:grid;place-items:center;text-align:center;background:#fffdf9;padding:26px;transition:.2s;cursor:pointer}.drop-zone:hover,.drop-zone.dragover{border-color:var(--gold);background:#fffaf0}.drop-zone input{position:absolute;inset:0;opacity:0;cursor:pointer}.drop-zone i{font-size:30px;color:var(--navy);margin-bottom:10px}.drop-zone strong{color:var(--navy);font-size:16px}.drop-zone span{color:#8a9099;font-size:13px}.file-name{margin-top:10px;color:var(--success);font-weight:700}.form-actions{display:flex;justify-content:flex-end;gap:12px}.btn{border:0;border-radius:10px;min-height:46px;padding:0 20px;display:inline-flex;align-items:center;justify-content:center;gap:9px;font-weight:800;cursor:pointer}.btn-primary{background:var(--gold);color:var(--navy);box-shadow:0 12px 28px rgba(212,175,55,.22)}.btn-secondary{background:var(--navy);color:#fff}
        @media(max-width:900px){.grid-2,.jury-grid{grid-template-columns:1fr}.page{margin-top:22px}.section-card{padding:20px}.form-actions{flex-direction:column}.btn{width:100%}}
    </style>
</head>
<body>
<header class="topbar">
    <a class="brand" href="dashboard_etudiant.php">
        <span class="brand-mark">M</span>
        <span><strong>GénieMémoire</strong><span>Plateforme universitaire</span></span>
    </a>
    <div class="top-actions">
        <a class="top-icon" href="notifications.php" aria-label="Notifications"><i class="fa-solid fa-bell"></i><span class="dot"></span></a>
        <a class="student-avatar" href="profil.php"><?= e(strtoupper(substr($student['prenom'], 0, 1) . substr($student['nom'], 0, 1))) ?></a>
    </div>
</header>

<main class="page">
    <div class="page-title">
        <h1>Dépôt de mémoire</h1>
        <p>Remplissez soigneusement les informations. Votre mémoire sera envoyé au jury dès soumission.</p>
    </div>

    <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

    <form class="deposit-form" method="post" enctype="multipart/form-data" id="depositForm">
        <section class="section-card">
            <div class="section-head"><span class="step">1</span><h2>Informations du mémoire</h2></div>
            <div class="grid-2">
                <div class="field full">
                    <label for="theme">Thème / titre du mémoire</label>
                    <input id="theme" name="theme" required placeholder="Saisissez le titre complet de votre mémoire...">
                </div>
                <div class="field">
                    <label for="idCentre">Centre</label>
                    <select id="idCentre" name="idCentre" required>
                        <option value="">Sélectionnez un centre</option>
                        <?php if ($centres) { mysqli_data_seek($centres, 0); while ($centre = mysqli_fetch_assoc($centres)): ?>
                            <option value="<?= (int) $centre['idCentre'] ?>"><?= e($centre['nomCentre']) ?></option>
                        <?php endwhile; } ?>
                    </select>
                </div>
                <div class="field">
                    <label for="idfiliere">Filière</label>
                    <select id="idfiliere" name="idfiliere" required>
                        <?php if ($filieres) { while ($filiere = mysqli_fetch_assoc($filieres)): ?>
                            <option value="<?= (int) $filiere['idfiliere'] ?>" <?= (int) $student['idfiliere'] === (int) $filiere['idfiliere'] ? 'selected' : '' ?>><?= e($filiere['nom_filiere']) ?></option>
                        <?php endwhile; } ?>
                    </select>
                </div>
                <div class="field">
                    <label for="annee_academique">Année académique</label>
                    <select id="annee_academique" name="annee_academique" required>
                        <option value="<?= e($annee_default) ?>"><?= e($annee_default) ?></option>
                        <option value="<?= e(((int) date('Y') - 1) . '-' . date('Y')) ?>"><?= e(((int) date('Y') - 1) . '-' . date('Y')) ?></option>
                    </select>
                </div>
                <div class="field">
                    <label for="date_soutenance">Date de soutenance</label>
                    <input id="date_soutenance" name="date_soutenance" type="date">
                </div>
                <div class="field full">
                    <label for="keywordInput">Mots-clés</label>
                    <div class="keyword-box" id="keywordBox">
                        <input id="keywordInput" type="text" placeholder="Ajouter un mot-clé...">
                    </div>
                    <input type="hidden" name="mots_cles" id="motsCles">
                    <small class="hint">Appuyez sur Entrée pour ajouter un mot-clé. Séparez les termes pertinents de votre sujet.</small>
                </div>
            </div>
        </section>

        <section class="section-card">
            <div class="section-head"><span class="step">2</span><h2>Composition du jury</h2></div>
            <p class="section-text">Saisissez les noms complets des membres de votre jury. Ils recevront une notification automatique et accéderont à votre mémoire.</p>
            <div class="jury-grid">
                <div class="jury-card">
                    <div class="jury-top"><span class="jury-icon"><i class="fa-solid fa-user-tie"></i></span><div><strong>Maître Mémoire</strong><span>Directeur de recherche</span></div></div>
                    <label for="maitre_memoire">Professeur</label>
                    <select id="maitre_memoire" name="maitre_memoire" required>
                        <option value="">Sélectionner un professeur</option>
                        <?php foreach ($professeurs as $professeur): ?>
                            <option value="<?= (int) $professeur['idprof'] ?>"><?= e($professeur['prenom'] . ' ' . $professeur['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="hint">Premier évaluateur</small>
                </div>
                <div class="jury-card">
                    <div class="jury-top"><span class="jury-icon"><i class="fa-solid fa-user-check"></i></span><div><strong>Examinateur</strong><span>Évaluateur secondaire</span></div></div>
                    <label for="examinateur">Professeur</label>
                    <select id="examinateur" name="examinateur" required>
                        <option value="">Sélectionner un professeur</option>
                        <?php foreach ($professeurs as $professeur): ?>
                            <option value="<?= (int) $professeur['idprof'] ?>"><?= e($professeur['prenom'] . ' ' . $professeur['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="hint">Évalue le fond et la forme</small>
                </div>
                <div class="jury-card">
                    <div class="jury-top"><span class="jury-icon"><i class="fa-solid fa-gavel"></i></span><div><strong>Président du Jury</strong><span>Validation finale</span></div></div>
                    <label for="president_jury">Professeur</label>
                    <select id="president_jury" name="president_jury" required>
                        <option value="">Sélectionner un professeur</option>
                        <?php foreach ($professeurs as $professeur): ?>
                            <option value="<?= (int) $professeur['idprof'] ?>"><?= e($professeur['prenom'] . ' ' . $professeur['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="hint">Donne la décision finale</small>
                </div>
            </div>
        </section>

        <section class="section-card">
            <div class="section-head"><span class="step">3</span><h2>Fichier du mémoire</h2></div>
            <label class="drop-zone" id="dropZone">
                <input type="file" name="fichier" id="fichier" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                <span>
                    <i class="fa-solid fa-cloud-arrow-up"></i><br>
                    <strong>Cliquez pour choisir un fichier</strong> ou glissez-déposez ici<br>
                    <span>Formats acceptés : PDF ou Word (.docx) — Taille maximale : 50 Mo</span>
                    <div class="file-name" id="fileName"></div>
                </span>
            </label>
        </section>

        <div class="form-actions">
            <a class="btn btn-secondary" href="dashboard_etudiant.php"><i class="fa-solid fa-arrow-left"></i> Retour</a>
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Soumettre le mémoire</button>
        </div>
    </form>
</main>

<script>
    const keywordInput = document.getElementById('keywordInput');
    const keywordBox = document.getElementById('keywordBox');
    const hiddenKeywords = document.getElementById('motsCles');
    const keywords = [];

    function renderKeywords() {
        keywordBox.querySelectorAll('.tag').forEach(tag => tag.remove());
        keywords.forEach((keyword, index) => {
            const tag = document.createElement('span');
            tag.className = 'tag';
            tag.innerHTML = `${keyword} <button type="button" aria-label="Retirer">×</button>`;
            tag.querySelector('button').addEventListener('click', () => {
                keywords.splice(index, 1);
                renderKeywords();
            });
            keywordBox.insertBefore(tag, keywordInput);
        });
        hiddenKeywords.value = keywords.join(', ');
    }

    keywordInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            const value = keywordInput.value.trim();
            if (value && !keywords.includes(value)) {
                keywords.push(value);
                keywordInput.value = '';
                renderKeywords();
            }
        }
    });

    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fichier');
    const fileName = document.getElementById('fileName');
    ['dragenter','dragover'].forEach(eventName => dropZone.addEventListener(eventName, event => {
        event.preventDefault();
        dropZone.classList.add('dragover');
    }));
    ['dragleave','drop'].forEach(eventName => dropZone.addEventListener(eventName, event => {
        event.preventDefault();
        dropZone.classList.remove('dragover');
    }));
    dropZone.addEventListener('drop', event => {
        if (event.dataTransfer.files.length) {
            fileInput.files = event.dataTransfer.files;
            fileName.textContent = event.dataTransfer.files[0].name;
        }
    });
    fileInput.addEventListener('change', () => {
        fileName.textContent = fileInput.files.length ? fileInput.files[0].name : '';
    });
</script>
</body>
</html>