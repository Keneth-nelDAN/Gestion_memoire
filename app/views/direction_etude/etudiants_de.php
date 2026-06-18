<?php
session_start();
require_once __DIR__ . '/../../controllers/etudiantController.php';
require_once __DIR__ . '/de_helpers.php';

// Vérifier que l'utilisateur est un directeur
if (empty($_SESSION['idde']) && (empty($_SESSION['user']) || $_SESSION['user']['type'] !== 'directeur')) {
    header('Location: ../auth/connexion.php');
    exit;
}

$etudiantController = new EtudiantController();

$active_page = 'etudiants';
// get_de_profile uses mysqli
require_once __DIR__ . '/../../../config/mysqli_config.php';
$de_profile = get_de_profile($conn);
$nom_de = $de_profile['nom_de'];
$initiales_de = $de_profile['initiales_de'];

$success = '';
$error = '';
$generated_password = '';
$mail_sent = false;
$mail_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || substr($email, -10) !== '@gmail.com') {
        $error = 'Le compte étudiant doit être créé avec une adresse Gmail valide.';
    } elseif ($etudiantController->exists($email)) {
        $error = 'Un étudiant utilise déjà cette adresse Gmail.';
    } else {
        $password = generate_student_password();
        
        // On a besoin du nom du niveau
        $idNiveau = (int)$_POST['idNiveau'];
        $sql_n = "SELECT nomNiveau FROM niveau WHERE idNiveau = :id LIMIT 1";
        $database = new Database(); $pdo = $database->connect();
        $stmt_n = $pdo->prepare($sql_n); $stmt_n->execute([':id' => $idNiveau]);
        $niveau_row = $stmt_n->fetch();
        
        $data = $_POST;
        $data['password'] = $password;
        $data['niveau'] = $niveau_row['nomNiveau'] ?? '';

        if ($etudiantController->create($data)) {
            $generated_password = $password;
            $mail_sent = send_student_credentials_email($email, $_POST['prenom'], $password, $_POST['type_compte'], $mail_error);
            $success = 'Compte étudiant créé avec succès.';
        } else {
            $error = 'Impossible de créer le compte étudiant.';
        }
    }
}

$students = $etudiantController->getAll();
$total_students = count($students);

$nb_consultants = 0;
$nb_diplomes = 0;
foreach ($students as $s) {
    if ($s['type_compte'] === 'consultant') $nb_consultants++;
    else if ($s['type_compte'] === 'diplome') $nb_diplomes++;
}

$depositData = $etudiantController->getDepositData();
$filieres = $depositData['filieres'];
$centres = $depositData['centres'];

// Niveaux (pourrait être dans EtudiantController aussi)
$sql_niv = "SELECT idNiveau, nomNiveau FROM niveau ORDER BY nomNiveau ASC";
$niveaux = $pdo->query($sql_niv)->fetchAll(PDO::FETCH_ASSOC);

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
                            <?php foreach ($filieres as $filiere): ?>
                                <option value="<?= (int) $filiere['idfiliere'] ?>"><?= e($filiere['nom_filiere']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="idCentre">Centre</label>
                        <select id="idCentre" name="idCentre" required>
                            <option value="">Sélectionner</option>
                            <?php foreach ($centres as $centre): ?>
                                <option value="<?= (int) $centre['idCentre'] ?>"><?= e($centre['nomCentre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <label for="idNiveau">Niveau</label>
                <select id="idNiveau" name="idNiveau" required>
                    <option value="">Sélectionner</option>
                    <?php foreach ($niveaux as $niveau): ?>
                        <option value="<?= (int) $niveau['idNiveau'] ?>"><?= e($niveau['nomNiveau']) ?></option>
                    <?php endforeach; ?>
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
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $student): ?>
                        <article class="teacher-item student-item">
                            <div class="teacher-avatar"><?= e(strtoupper(substr($student['prenom'], 0, 1) . substr($student['nom'], 0, 1))) ?></div>
                            <div>
                                <strong><?= e($student['prenom'] . ' ' . $student['nom']) ?></strong>
                                <span><?= e($student['email']) ?></span>
                                <small><?= e($student['nom_filiere'] ?: 'Filière non définie') ?> · <?= e($student['niveau']) ?></small>
                            </div>
                            <span class="badge"><?= $student['type_compte'] === 'diplome' ? 'Diplômé' : 'Consultaire' ?></span>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">Aucun compte étudiant créé pour le moment.</div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>
