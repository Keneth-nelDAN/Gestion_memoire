<?php
session_start();
require_once __DIR__ . '/../../controllers/etudiantController.php';

if (empty($_SESSION['idetudiant'])) {
    header('Location: ../auth/connexion.php');
    exit;
}

$idetudiant = (int) $_SESSION['idetudiant'];
$etudiantController = new EtudiantController();

$data = $etudiantController->getDashboardData($idetudiant);
$student = $data['student'];
$nb_memoires = $data['nb_memoires'];
$nb_deposes = $data['nb_deposes'];

$type_compte = $student['type_compte'] ?? 'consultant';
$can_deposit = $type_compte === 'diplome';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Espace étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../direction_etude/style.css">
</head>
<body>
<main class="student-shell">
    <header class="workspace-header">
        <div>
            <span class="overline">Espace étudiant</span>
            <h1>Bienvenue <?= htmlspecialchars($student['prenom'] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars($student['nom_filiere'] ?? 'Filière non définie', ENT_QUOTES, 'UTF-8') ?> · <?= $can_deposit ? 'Compte diplômé' : 'Compte consultaire' ?></p>
        </div>
        <a class="btn-muted" href="../../../app/views/auth/confirmation_logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </header>

    <section class="cards-container">
        <article class="dashboard-card accent-gold">
            <div class="stat-icon"><i class="fa-solid fa-book-open"></i></div>
            <span class="card-label">Mémoires consultables</span>
            <strong><?= $nb_memoires ?></strong>
            <small>Lecture sur la plateforme</small>
        </article>
        <article class="dashboard-card accent-blue">
            <div class="stat-icon"><i class="fa-solid <?= $can_deposit ? 'fa-cloud-arrow-up' : 'fa-eye' ?>"></i></div>
            <span class="card-label"><?= $can_deposit ? 'Dépôts effectués' : 'Accès consultaire' ?></span>
            <strong><?= $can_deposit ? $nb_deposes : '0' ?></strong>
            <small><?= $can_deposit ? 'Mémoires soumis' : 'Téléchargement désactivé' ?></small>
        </article>
    </section>

    <section class="quick-actions">
        <a href="consulter_memoire.php"><i class="fa-solid fa-book-open-reader"></i><span>Consulter les mémoires</span></a>
        <a href="notifications.php"><i class="fa-solid fa-bell"></i><span>Mes Notifications</span></a>
        <?php if ($can_deposit): ?>
            <a href="depot_memoire.php"><i class="fa-solid fa-file-circle-plus"></i><span>Déposer mon mémoire</span></a>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
