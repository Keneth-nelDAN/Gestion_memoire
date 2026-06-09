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

$active_page = 'etudiants';
$de_profile = get_de_profile($conn);
$nom_de = $de_profile['nom_de'];
$initiales_de = $de_profile['initiales_de'];
ensure_etudiant_account_schema($conn);

$success = '';
$error = '';
$generated_password = '';
$mail_sent = false;
$mail_error = '';

$filieres = mysqli_query($conn, "SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere ASC");
$centres = get_de_centres($conn);
$niveaux = get_de_niveaux($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $idfiliere = (int) ($_POST['idfiliere'] ?? 0);
    $idCentre = (int) ($_POST['idCentre'] ?? 0);
    $idNiveau = (int) ($_POST['idNiveau'] ?? 0);
    $type_compte = $_POST['type_compte'] ?? 'consultant';

    if (!in_array($type_compte, ['consultant', 'diplome'], true)) {
        $type_compte = 'consultant';
    }

    if ($nom === '' || $prenom === '' || $idfiliere <= 0 || $idCentre <= 0 || $idNiveau <= 0) {
        $error = 'Veuillez renseigner le nom, le prénom, la filière et le niveau.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || substr($email, -10) !== '@gmail.com') {
        $error = 'Le compte étudiant doit être créé avec une adresse Gmail valide.';
    } else {
        $check = mysqli_prepare($conn, 'SELECT idetudiant FROM etudiant WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        $exists = mysqli_stmt_get_result($check);

        if ($exists && mysqli_num_rows($exists) > 0) {
            $error = 'Un étudiant utilise déjà cette adresse Gmail.';
        } else {
            $password = generate_student_password();
            $niveau_result = mysqli_query($conn, 'SELECT nomNiveau FROM niveau WHERE idNiveau = ' . $idNiveau . ' LIMIT 1');
            $niveau_row = $niveau_result ? mysqli_fetch_assoc($niveau_result) : null;
            $niveau = $niveau_row['nomNiveau'] ?? '';
            $stmt = mysqli_prepare($conn, 'INSERT INTO etudiant (nom, prenom, idfiliere, idCentre, idNiveau, niveau, email, motdepasse, type_compte) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssiiissss', $nom, $prenom, $idfiliere, $idCentre, $idNiveau, $niveau, $email, $password, $type_compte);

            if (mysqli_stmt_execute($stmt)) {
                $generated_password = $password;
                $mail_sent = send_student_credentials_email($email, $prenom, $password, $type_compte, $mail_error);
                $success = 'Compte étudiant créé avec succès.';
            } else {
                $error = 'Impossible de créer le compte étudiant.';
            }
        }
    }
}

$students = mysqli_query($conn, "
    SELECT e.idetudiant, e.nom, e.prenom, e.email, e.niveau, e.type_compte, e.date_creation,
           f.nom_filiere, c.nomCentre, n.nomNiveau
    FROM etudiant e
    LEFT JOIN filiere f ON f.idfiliere = e.idfiliere
    LEFT JOIN centre c ON c.idCentre = e.idCentre
    LEFT JOIN niveau n ON n.idNiveau = e.idNiveau
    ORDER BY e.idetudiant DESC
");
$total_students = $students ? mysqli_num_rows($students) : 0;
$consultants = mysqli_query($conn, "SELECT COUNT(*) FROM etudiant WHERE type_compte = 'consultant'");
$diplomes = mysqli_query($conn, "SELECT COUNT(*) FROM etudiant WHERE type_compte = 'diplome'");
$nb_consultants = (int) (mysqli_fetch_row($consultants)[0] ?? 0);
$nb_diplomes = (int) (mysqli_fetch_row($diplomes)[0] ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Comptes étudiants</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <?php include __DIR__ . '/../partials/sidebar_DE.php'; ?>

    <main class="workspace">
        <header class="workspace-header">
            <div>
                <span class="overline">Gestion des accès</span>
                <h1>Comptes étudiants</h1>
                <p>Créez les accès consultaires et diplômés, avec envoi automatique du mot de passe par Gmail.</p>
            </div>
            <a class="btn-blue" href="dashboard_de.php"><i class="fa-solid fa-arrow-left"></i> Tableau de bord</a>
        </header>

        <?php if ($success): ?>
            <div class="alert success">
                <?= e($success) ?>
                <?php if ($mail_sent): ?>
                    <br>Le mot de passe a été envoyé à l'adresse Gmail de l'étudiant.
                <?php else: ?>
                    <br>L'envoi email n'a pas abouti sur ce serveur. Mot de passe initial : <strong><?= e($generated_password) ?></strong>
                    <?php if ($mail_error !== ''): ?><br>Détail : <?= e($mail_error) ?><?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

        <section class="professor-layout">
            <form class="publication-card professor-form" method="post" autocomplete="off">
                <div class="form-title">
                    <i class="fa-solid fa-user-graduate"></i>
                    <div>
                        <h2>Nouveau compte étudiant</h2>
                        <p>Le type de compte détermine les droits de consultation et de dépôt.</p>
                    </div>
                </div>

                <div class="field-row">
                    <div><label for="nom">Nom</label><input id="nom" name="nom" required></div>
                    <div><label for="prenom">Prénom</label><input id="prenom" name="prenom" required></div>
                </div>

                <label for="email">Adresse Gmail</label>
                <input id="email" type="email" name="email" required placeholder="etudiant@gmail.com">

                <div class="field-row">
                    <div>
                        <label for="idfiliere">Filière</label>
                        <select id="idfiliere" name="idfiliere" required>
                            <option value="">Sélectionner</option>
                            <?php if ($filieres) { while ($filiere = mysqli_fetch_assoc($filieres)): ?>
                                <option value="<?= (int) $filiere['idfiliere'] ?>"><?= e($filiere['nom_filiere']) ?></option>
                            <?php endwhile; } ?>
                        </select>
                    </div>
                    <div>
                        <label for="idCentre">Centre</label>
                        <select id="idCentre" name="idCentre" required>
                            <option value="">Sélectionner</option>
                            <?php if ($centres) { while ($centre = mysqli_fetch_assoc($centres)): ?>
                                <option value="<?= (int) $centre['idCentre'] ?>"><?= e($centre['nomCentre']) ?></option>
                            <?php endwhile; } ?>
                        </select>
                    </div>
                </div>

                <label for="idNiveau">Niveau</label>
                <select id="idNiveau" name="idNiveau" required>
                    <option value="">Sélectionner</option>
                    <?php if ($niveaux) { while ($niveau = mysqli_fetch_assoc($niveaux)): ?>
                        <option value="<?= (int) $niveau['idNiveau'] ?>"><?= e($niveau['nomNiveau']) ?></option>
                    <?php endwhile; } ?>
                </select>

                <label for="type_compte">Type de compte</label>
                <select id="type_compte" name="type_compte" required>
                    <option value="consultant">Étudiant consultaire - consultation sans téléchargement</option>
                    <option value="diplome">Étudiant diplômé - dépôt de mémoire autorisé</option>
                </select>

                <button class="btn-gold full" type="submit"><i class="fa-solid fa-paper-plane"></i> Créer et envoyer le mot de passe</button>
            </form>

            <aside class="access-summary">
                <div class="summary-card">
                    <span class="summary-icon"><i class="fa-solid fa-users"></i></span>
                    <p>Comptes étudiants</p>
                    <strong><?= (int) $total_students ?></strong>
                    <small><?= $nb_consultants ?> consultaires · <?= $nb_diplomes ?> diplômés</small>
                </div>
                <div class="summary-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>Les consultaires consultent les mémoires sans téléchargement. Les diplômés peuvent déposer leurs mémoires depuis leur espace.</p>
                </div>
            </aside>
        </section>

        <section class="table-container professor-table">
            <div class="table-header">
                <div>
                    <h2>Étudiants enregistrés</h2>
                    <p>Liste dynamique des comptes créés et de leurs droits.</p>
                </div>
            </div>
            <div class="teacher-list student-list">
                <?php if ($students && mysqli_num_rows($students) > 0): ?>
                    <?php mysqli_data_seek($students, 0); while ($student = mysqli_fetch_assoc($students)): ?>
                        <article class="teacher-item student-item">
                            <div class="teacher-avatar"><?= e(strtoupper(substr($student['prenom'], 0, 1) . substr($student['nom'], 0, 1))) ?></div>
                            <div>
                                <strong><?= e($student['prenom'] . ' ' . $student['nom']) ?></strong>
                                <span><?= e($student['email']) ?></span>
                                <small><?= e($student['nom_filiere'] ?: 'Filière non définie') ?> · <?= e($student['niveau']) ?></small>
                            </div>
                            <span class="badge"><?= $student['type_compte'] === 'diplome' ? 'Diplômé' : 'Consultaire' ?></span>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">Aucun compte étudiant créé pour le moment.</div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>
