<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/de_helpers.php';

function scalar_count($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return 0;
    }
    $row = mysqli_fetch_row($result);
    return (int) ($row[0] ?? 0);
}

$active_page = 'dashboard';
$de_profile = get_de_profile($conn);
$nom_de = $de_profile['nom_de'];
$initiales_de = $de_profile['initiales_de'];
ensure_etudiant_account_schema($conn);

$search = trim($_GET['q'] ?? '');
$filiere_filter = (int) ($_GET['filiere'] ?? 0);
$annee_filter = trim($_GET['annee'] ?? '');

$nb_memoires = scalar_count($conn, "SELECT COUNT(*) FROM ancien_memoire");
$nb_publies = scalar_count($conn, "SELECT COUNT(*) FROM ancien_memoire WHERE statut IN ('publie','publié','publiee','publiée')");
$nb_attente = scalar_count($conn, "SELECT COUNT(*) FROM ancien_memoire WHERE statut IN ('en_attente','valide_non_publie')");
$nb_mois = scalar_count($conn, "SELECT COUNT(*) FROM ancien_memoire WHERE MONTH(date_depot) = MONTH(CURRENT_DATE()) AND YEAR(date_depot) = YEAR(CURRENT_DATE())");
$nb_etudiants = scalar_count($conn, "SELECT COUNT(*) FROM etudiant");
$nb_consultants = scalar_count($conn, "SELECT COUNT(*) FROM etudiant WHERE type_compte = 'consultant'");
$nb_diplomes = scalar_count($conn, "SELECT COUNT(*) FROM etudiant WHERE type_compte = 'diplome'");

$filieres = mysqli_query($conn, "SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere ASC");
$annees = mysqli_query($conn, "SELECT DISTINCT annee_academique FROM ancien_memoire WHERE annee_academique IS NOT NULL AND annee_academique <> '' ORDER BY annee_academique DESC");

$where = [];
$types = '';
$params = [];

if ($search !== '') {
    $where[] = "(am.theme LIKE ? OR am.nomAut LIKE ? OR am.prenomAut LIKE ? OR am.maitre_memoire LIKE ? OR am.examinateur LIKE ? OR am.president_jury LIKE ? OR f.nom_filiere LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like, $like, $like, $like);
    $types .= 'sssssss';
}

if ($filiere_filter > 0) {
    $where[] = "am.idfiliere = ?";
    $params[] = $filiere_filter;
    $types .= 'i';
}

if ($annee_filter !== '') {
    $where[] = "am.annee_academique = ?";
    $params[] = $annee_filter;
    $types .= 's';
}

$sql = "SELECT am.*, f.nom_filiere,
        CONCAT(am.prenomAut, ' ', am.nomAut) AS auteur,
        CONCAT_WS('; ', am.maitre_memoire, am.examinateur, am.president_jury) AS jury,
        (SELECT COUNT(*) FROM like_memoire lm WHERE lm.idAM = am.idAM OR lm.idmemoire = am.idAM) AS likes,
        (SELECT COUNT(*) FROM commentaire c WHERE c.idmemoire = am.idAM) AS commentaires
    FROM ancien_memoire am
    LEFT JOIN filiere f ON f.idfiliere = am.idfiliere";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " ORDER BY am.idAM DESC LIMIT 50";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt && !empty($params)) {
    bind_params_dynamic($stmt, $types, $params);
}
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $memoires = mysqli_stmt_get_result($stmt);
} else {
    $memoires = false;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Dashboard DE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="workspace">
        <header class="workspace-header">
            <div>
                <span class="overline">Vue d'ensemble de la plateforme</span>
                <h1>Tableau de bord</h1>
                <p>Bienvenue <?= e($nom_de) ?>, vous gérez les mémoires publiés.</p>
            </div>
            <div class="header-actions">
                <a class="btn-gold" href="publier_memoire.php"><i class="fa-solid fa-plus"></i> Publier un mémoire</a>
                <a class="btn-blue" href="publier_lots.php"><i class="fa-solid fa-layer-group"></i> Uploader plusieurs fichiers</a>
            </div>
        </header>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
            <div class="alert success">Mémoire modifié avec succès. Les données affichées viennent directement de la base.</div>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
            <div class="alert success">Mémoire supprimé avec succès. La liste est actualisée.</div>
        <?php endif; ?>

        <section class="cards-container">
            <article class="dashboard-card accent-gold">
                <div class="stat-icon"><i class="fa-solid fa-book-open"></i></div>
                <span class="card-label">Mémoires publiés</span>
                <strong><?= $nb_publies ?></strong>
                <small>Total mémoires : <?= $nb_memoires ?></small>
            </article>
            <article class="dashboard-card accent-blue">
                <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                <span class="card-label">Validés non publiés</span>
                <strong><?= $nb_attente ?></strong>
                <small>En attente de publication</small>
            </article>
            <article class="dashboard-card">
                <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <span class="card-label">Soumis ce mois</span>
                <strong><?= $nb_mois ?></strong>
                <small>Activité récente</small>
            </article>
            <article class="dashboard-card">
                <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                <span class="card-label">Étudiants actifs</span>
                <strong><?= $nb_etudiants ?></strong>
                <small><?= $nb_consultants ?> consultaires · <?= $nb_diplomes ?> diplômés</small>
            </article>
        </section>

        <section class="quick-actions">
            <a href="publier_memoire.php"><i class="fa-solid fa-file-circle-plus"></i><span>Publier un mémoire</span></a>
            <a href="publier_lots.php"><i class="fa-solid fa-cloud-arrow-up"></i><span>Uploader plusieurs fichiers</span></a>
            <a href="etudiants_de.php"><i class="fa-solid fa-users"></i><span>Créer un compte étudiant</span></a>
        </section>

        <section class="table-container" id="publications">
            <div class="table-header">
                <div>
                    <h2>Mémoires publiés</h2>
                    <p>Recherchez un mémoire, puis utilisez les actions placées en bas de chaque fiche.</p>
                </div>
                <span class="result-count"><?= $memoires ? mysqli_num_rows($memoires) : 0 ?> résultat(s)</span>
            </div>

            <form class="search-panel" method="get">
                <input type="search" name="q" value="<?= e($search) ?>" placeholder="Rechercher par titre, auteur, professeur, jury ou filière">
                <select name="filiere">
                    <option value="0">Toutes les filières</option>
                    <?php if ($filieres) { mysqli_data_seek($filieres, 0); while ($filiere = mysqli_fetch_assoc($filieres)): ?>
                        <option value="<?= (int) $filiere['idfiliere'] ?>" <?= $filiere_filter === (int) $filiere['idfiliere'] ? 'selected' : '' ?>><?= e($filiere['nom_filiere']) ?></option>
                    <?php endwhile; } ?>
                </select>
                <select name="annee">
                    <option value="">Toutes les années</option>
                    <?php if ($annees) { while ($annee = mysqli_fetch_assoc($annees)): ?>
                        <option value="<?= e($annee['annee_academique']) ?>" <?= $annee_filter === $annee['annee_academique'] ? 'selected' : '' ?>><?= e($annee['annee_academique']) ?></option>
                    <?php endwhile; } ?>
                </select>
                <button class="btn-blue" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Rechercher</button>
                <a class="btn-muted" href="dashboard_de.php">Réinitialiser</a>
            </form>

            <div class="memoires-grid">
                <?php if ($memoires && mysqli_num_rows($memoires) > 0): ?>
                    <?php while ($memoire = mysqli_fetch_assoc($memoires)): ?>
                        <article class="memoire-card">
                            <div class="memoire-card-top">
                                <span class="badge"><?= e($memoire['nom_filiere'] ?: 'Non définie') ?></span>
                                <span class="memoire-date"><?= e(date('d/m/Y', strtotime($memoire['date_depot']))) ?></span>
                            </div>

                            <h3><?= e($memoire['theme']) ?></h3>

                            <div class="memoire-meta">
                                <p><i class="fa-solid fa-user-graduate"></i> <strong>Auteur :</strong> <?= e($memoire['auteur']) ?></p>
                                <p><i class="fa-solid fa-gavel"></i> <strong>Jury :</strong> <?= e($memoire['jury'] ?: 'Non renseigné') ?></p>
                                <p><i class="fa-solid fa-heart"></i> <?= (int) $memoire['likes'] ?> likes · <i class="fa-solid fa-comment"></i> <?= (int) $memoire['commentaires'] ?> commentaires</p>
                            </div>

                            <div class="memoire-card-actions">
                                <a class="btn-blue" href="modifier_publication.php?id=<?= (int) $memoire['idAM'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i> Modifier
                                </a>
                                <a class="btn-danger" href="supprimer_pubication.php?id=<?= (int) $memoire['idAM'] ?>" onclick="return confirm('Supprimer définitivement ce mémoire ?')">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">Aucun mémoire trouvé.</div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>






