<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';

if (empty($_SESSION['idetudiant'])) {
    header('Location: ../auth/connexion.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
$where = "WHERE am.statut IN ('publie','publié','publiee','publiée')";
$types = '';
$params = [];

if ($search !== '') {
    $where .= " AND (am.theme LIKE ? OR am.nomAut LIKE ? OR am.prenomAut LIKE ? OR f.nom_filiere LIKE ?)";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like, $like];
    $types = 'ssss';
}

$sql = "SELECT am.*, f.nom_filiere, CONCAT(am.prenomAut, ' ', am.nomAut) AS auteur,
        (SELECT COUNT(*) FROM like_memoire lm WHERE lm.idAM = am.idAM OR lm.idmemoire = am.idAM) AS likes
    FROM ancien_memoire am
    LEFT JOIN filiere f ON f.idfiliere = am.idfiliere
    $where
    ORDER BY am.idAM DESC";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt && $types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$memoires = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Consulter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../direction_etude/style.css">
</head>
<body>
<main class="student-shell">
    <header class="workspace-header">
        <div>
            <span class="overline">Bibliothèque</span>
            <h1>Consulter les mémoires</h1>
            <p>Lecture disponible sur le site. Le téléchargement n'est pas proposé aux étudiants consultaires.</p>
        </div>
        <a class="btn-blue" href="dashboard_etudiant.php"><i class="fa-solid fa-arrow-left"></i> Tableau de bord</a>
    </header>

    <section class="table-container">
        <div class="table-header">
            <div>
                <h2>Mémoires publiés</h2>
                <p>Recherchez par thème, auteur ou filière.</p>
            </div>
        </div>
        <form class="search-panel student-search" method="get">
            <input type="search" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher un mémoire">
            <button class="btn-blue" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Rechercher</button>
            <a class="btn-muted" href="consulter_memoire.php">Réinitialiser</a>
        </form>
        <div class="memoires-grid">
            <?php if ($memoires && mysqli_num_rows($memoires) > 0): ?>
                <?php while ($memoire = mysqli_fetch_assoc($memoires)): ?>
                    <article class="memoire-card">
                        <div class="memoire-card-top">
                            <span class="badge"><?= htmlspecialchars($memoire['nom_filiere'] ?: 'Non définie', ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="memoire-date"><?= htmlspecialchars(date('d/m/Y', strtotime($memoire['date_depot'])), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <h3><?= htmlspecialchars($memoire['theme'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="memoire-meta">
                            <p><i class="fa-solid fa-user-graduate"></i> <strong>Auteur :</strong> <?= htmlspecialchars($memoire['auteur'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p><i class="fa-solid fa-heart"></i> <?= (int) $memoire['likes'] ?> likes</p>
                            <p><i class="fa-solid fa-lock"></i> Consultation uniquement sur la plateforme</p>
                        </div>
                        <!-- Ouvre le lecteur sécurisé contenant PDF.js et la protection par canvas dans un nouvel onglet sans exposer le PDF -->
                        <a style="background-color: #101d29; color: white; padding: 10px 20px; text-decoration: none; border-radius: 10px; width: 400px; text-align: center;" href="view_pdf.php?id=<?= $memoire['id'] ?>" target="_blank" class="btn-lecture-protegee">
                            Consulter <!--en Lecture Sécurisée (Anti-tél.) 🛡️-->
                        </a>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">Aucun mémoire disponible.</div>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
