<?php
session_start();
require_once __DIR__ . '/../../controllers/dashboardController.php';
require_once __DIR__ . '/de_helpers.php';

// Vérifier que l'utilisateur est un directeur
if (empty($_SESSION['idde']) && (empty($_SESSION['user']) || $_SESSION['user']['type'] !== 'directeur')) {
    header('Location: ../auth/connexion.php');
    exit;
}

$dashboardController = new DashboardController();

$active_page = 'dashboard';
// Le nom et les initiales sont maintenant récupérés depuis la session pour plus de cohérence
$nom_de = trim(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? ''));
$initiales_de = strtoupper(substr($_SESSION['user']['prenom'] ?? 'D', 0, 1) . substr($_SESSION['user']['nom'] ?? 'E', 0, 1));
 
$filters = [
    'q' => trim($_GET['q'] ?? ''),
    'filiere' => (int)($_GET['filiere'] ?? 0),
    'annee' => trim($_GET['annee'] ?? '')
];

$data = $dashboardController->getDEData($filters);
$stats = $data['stats'];
$filieres = $data['filieres'];
$annees = $data['annees'];
$recent_professeurs = $data['recent_professeurs'];
$memoires = $data['memoires'];

$search = $filters['q'];
$filiere_filter = $filters['filiere'];
$annee_filter = $filters['annee'];
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
    <?php include __DIR__ . '/../partials/sidebar_DE.php'; ?>

    <main class="workspace">
        <header class="workspace-header">
            <div>
                <span class="overline">Vue d'ensemble de la plateforme</span>
                <h1>Tableau de bord</h1>
                <p>Bienvenue <?= e($nom_de) ?>, vous gérez les mémoires publiés.</p>
            </div>
            <div class="header-actions">
                <a class="btn-gold" href="/Gestion_memoire/public/publications"><i class="fa-solid fa-plus"></i> Publier un mémoire</a>
                <a class="btn-blue" href="/Gestion_memoire/public/publications"><i class="fa-solid fa-layer-group"></i> Publier plusieurs mémoires</a>
            </div>
        </header>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
            <div class="alert success">Mémoire modifié avec succès. Les données affichées viennent directement de la base.</div>
        <?php endif; ?>

        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'deleted'): ?>
                <div class="alert success text-xs">La publication et son fichier physique ont été supprimés de la bibliothèque universitaire.</div>
            <?php elseif ($_GET['status'] === 'error_delete'): ?>
                <div class="alert error text-xs">Une erreur SQL est survenue lors de la suppression du mémoire.</div>
            <?php elseif ($_GET['status'] === 'not_found'): ?>
                <div class="alert error text-xs">Le mémoire demandé n'existe pas ou a déjà été supprimé.</div>
            <?php endif; ?>
        <?php endif; ?>

        <section class="cards-container">
            <article class="dashboard-card accent-gold">
                <div class="stat-icon"><i class="fa-solid fa-book-open"></i></div>
                <span class="card-label">Mémoires publiés</span>
                <strong><?= $stats['nb_publies'] ?></strong>
                <small>Total mémoires : <?= $stats['nb_memoires'] ?></small>
            </article>
            <article class="dashboard-card accent-blue">
                <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                <span class="card-label">Validés non publiés</span>
                <strong><?= $stats['nb_attente'] ?></strong>
                <small>En attente de publication</small>
            </article>
            <article class="dashboard-card">
                <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <span class="card-label">Soumis ce mois</span>
                <strong><?= $stats['nb_mois'] ?></strong>
                <small>Activité récente</small>
            </article>
            <article class="dashboard-card">
                <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                <span class="card-label">Étudiants actifs</span>
                <strong><?= $stats['nb_etudiants'] ?></strong>
                <small><?= $stats['nb_consultants'] ?> consultants · <?= $stats['nb_diplomes'] ?> diplômés</small>
            </article>
            <article class="dashboard-card accent-gold">
                <div class="stat-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
                <span class="card-label">Professeurs actifs</span>
                <strong><?= $stats['nb_professeurs'] ?></strong>
                <small>Comptes enseignants créés</small>
            </article>
        </section>
            <hr style="border: none; border-top: 3px solid #333; width: 50%;">
            <br/>

        <section class="table-container" id="publications">
            <div class="table-header">
                <div>
                    <h2>Mémoires publiés</h2>
                    <p>Recherchez un mémoire, puis utilisez les actions placées en bas de chaque fiche.</p>
                </div>
                <span class="result-count"><?= count($memoires) ?> résultat(s)</span>
            </div>

            <form class="search-panel" action="/Gestion_memoire/public/dashboard" method="get">
                <input type="search" name="q" value="<?= e($search) ?>" placeholder="Rechercher par titre, auteur, professeur, jury ou filière">
                <select name="filiere">
                    <option value="0">Toutes les filières</option>
                    <?php foreach ($filieres as $filiere): ?>
                        <option value="<?= (int) $filiere['idfiliere'] ?>" <?= $filiere_filter === (int) $filiere['idfiliere'] ? 'selected' : '' ?>><?= e($filiere['nom_filiere']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="annee">
                    <option value="">Toutes les années</option>
                    <?php foreach ($annees as $annee): ?>
                        <option value="<?= e($annee['annee_academique']) ?>" <?= $annee_filter === $annee['annee_academique'] ? 'selected' : '' ?>><?= e($annee['annee_academique']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn-blue" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Rechercher</button>
                <a class="btn-muted" href="/Gestion_memoire/public/dashboard">Réinitialiser</a>
            </form>

            <div class="memoires-grid">
                <?php if (!empty($memoires)): ?>
                    <?php foreach ($memoires as $memoire): ?>
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
                                <a
                                    href="/Gestion_memoire/public/pdf/<?= $memoire['idAM'] ?>"
                                    target="_blank"
                                    class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-slate-950 hover:bg-slate-900 group-hover:bg-indigo-600 text-white font-bold text-xs rounded-xl transition-all shadow-sm gap-2"
                                    style="background-color: #2563eb; border-radius: 8px; font-weight: bold; color: white; align-content: center; padding: 0 12px 0 12px;"
                                >
                                    <i class="fa-solid fa-eye-slash text-xs"></i> Lire en lecture sécurisée
                                </a>
                                <a class="btn-blue" href="/Gestion_memoire/public/publication/edit/<?= (int) $memoire['idAM'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i> Modifier
                                </a>
                                <button 
                                    type="button"
                                    class="btn-delete-inline"
                                    data-url="supprimer_pubication.php?id=<?= $memoire['idAM'] ?>"
                                    onclick="handleInlineDelete(this)"
                                    style="background-color: #dc2626; color: white; border: none; padding: 8px 12px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: all 0.2s;"
                                >
                                    Supprimer
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">Aucun mémoire trouvé.</div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
<script>
function handleInlineDelete(btn) {
    if (!btn.dataset.confirmed) {
        btn.dataset.confirmed = "true";
        btn.style.backgroundColor = "#f59e0b";
        btn.innerHTML = "⚠️ Confirmer ?";
        setTimeout(() => {
            btn.removeAttribute('data-confirmed');
            btn.style.backgroundColor = "#dc2626";
            btn.innerHTML = "Supprimer";
        }, 4000);
    } else {
        window.location.href = btn.dataset.url;
    }
}
</script>
</body>
</html>
