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
$dashboard_success = '';
$dashboard_error = '';
$generated_prof_password = '';
$default_prof_password = 'Prof@' . random_int(1000, 9999);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['dashboard_action'] ?? '') === 'add_professeur') {
    $prof_nom = trim($_POST['prof_nom'] ?? '');
    $prof_prenom = trim($_POST['prof_prenom'] ?? '');
    $prof_email = strtolower(trim($_POST['prof_email'] ?? ''));
    $prof_password = trim($_POST['prof_password'] ?? '');

    if ($prof_password === '') {
        $prof_password = $default_prof_password;
    }

    if ($prof_nom === '' || $prof_prenom === '' || !filter_var($prof_email, FILTER_VALIDATE_EMAIL)) {
        $dashboard_error = 'Veuillez renseigner le nom, le prénom et un email valide pour le professeur.';
    } else {
        $check_prof = mysqli_prepare($conn, 'SELECT idprof FROM professeur WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($check_prof, 's', $prof_email);
        mysqli_stmt_execute($check_prof);
        $prof_exists = mysqli_stmt_get_result($check_prof);

        if ($prof_exists && mysqli_num_rows($prof_exists) > 0) {
            $dashboard_error = 'Un professeur utilise déjà cet email.';
        } else {
            $insert_prof = mysqli_prepare($conn, 'INSERT INTO professeur (nom, prenom, email, motdepasse) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($insert_prof, 'ssss', $prof_nom, $prof_prenom, $prof_email, $prof_password);

            if (mysqli_stmt_execute($insert_prof)) {
                $generated_prof_password = $prof_password;
                $dashboard_success = 'Compte professeur créé avec succès depuis le tableau de bord.';
            } else {
                $dashboard_error = 'Impossible de créer le compte professeur.';
            }
        }
    }
}

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
$nb_professeurs = scalar_count($conn, "SELECT COUNT(*) FROM professeur");

$filieres = mysqli_query($conn, "SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere ASC");
$annees = mysqli_query($conn, "SELECT DISTINCT YEAR(date_depot) as annee_academique FROM ancien_memoire WHERE date_depot IS NOT NULL ORDER BY annee_academique DESC");
$recent_professeurs = mysqli_query($conn, "SELECT idprof, nom, prenom, email FROM professeur ORDER BY idprof DESC LIMIT 4");

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
    $where[] = "YEAR(am.date_depot) = ?";
    $params[] = (int) $annee_filter;
    $types .= 'i';
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
    <?php include __DIR__ . '/../partials/sidebar_DE.php'; ?>

    <main class="workspace">
        <header class="workspace-header">
            <div>
                <span class="overline">Vue d'ensemble de la plateforme</span>
                <h1>Tableau de bord</h1>
                <p>Bienvenue <?= e($nom_de) ?>, vous gérez les mémoires publiés.</p>
            </div>
            <div class="header-actions">
                <a class="btn-gold" href="publier_memoire.php"><i class="fa-solid fa-plus"></i> Publier un mémoire</a>
                <a class="btn-blue" href="publier_lots.php"><i class="fa-solid fa-layer-group"></i> Publier plusieurs mémoires</a>
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
        
        <?php if ($dashboard_success !== ''): ?>
            <div class="alert success">
                <?= e($dashboard_success) ?>
                <?php if ($generated_prof_password !== ''): ?>
                    Mot de passe provisoire : <strong><?= e($generated_prof_password) ?></strong>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($dashboard_error !== ''): ?>
            <div class="alert error"><?= e($dashboard_error) ?></div>
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
                <small><?= $nb_consultants ?> consultants · <?= $nb_diplomes ?> diplômés</small>
            </article>
            <article class="dashboard-card accent-gold">
                <div class="stat-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
                <span class="card-label">Professeurs actifs</span>
                <strong><?= $nb_professeurs ?></strong>
                <small>Comptes enseignants créés</small>
            </article>
        </section>
            <hr style="border: none; border-top: 3px solid #333; width: 50%;">
            <br/>
        <!-- <section class="quick-actions">
            <a href="publier_memoire.php"><i class="fa-solid fa-file-circle-plus"></i><span>Publier un mémoire</span></a>
            <a href="publier_lots.php"><i class="fa-solid fa-cloud-arrow-up"></i><span>Uploader plusieurs fichiers</span></a>
            <a href="etudiants_de.php"><i class="fa-solid fa-users"></i><span>Créer un compte étudiant</span></a>
            <a href="#ajouter-professeur"><i class="fa-solid fa-user-tie"></i><span>Ajouter professeur</span></a>
        </section>
            <hr style="border: none; border-top: 3px solid #333; width: 50%;">
            <br/>
        <section class="dashboard-professor-panel" id="ajouter-professeur">
            <article class="form-card dashboard-professor-form">
                <div class="section-heading">
                    <div>
                        <span class="overline">Comptes enseignants</span>
                        <h2>Ajouter professeur</h2>
                        <p>Le DE crée le compte du professeur afin qu'il puisse se connecter ensuite.</p>
                    </div>
                    <a class="btn-muted" href="professeurs_de.php"><i class="fa-solid fa-arrow-up-right-from-square"></i> Page professeurs</a>
                </div>

                <form method="post" class="professional-form">
                    <input type="hidden" name="dashboard_action" value="add_professeur">
                    <div class="form-grid">
                        <label>
                            <span>Nom</span>
                            <input type="text" name="prof_nom" placeholder="Ex : Dansou" required>
                        </label>
                        <label>
                            <span>Prénom</span>
                            <input type="text" name="prof_prenom" placeholder="Ex : Arnaud" required>
                        </label>
                        <label>
                            <span>Email de connexion</span>
                            <input type="email" name="prof_email" placeholder="professeur@geniememoire.edu" required>
                        </label>
                        <label>
                            <span>Mot de passe provisoire</span>
                            <input type="text" name="prof_password" value="<?= e($default_prof_password) ?>" required>
                        </label>
                    </div>
                    <div class="form-actions">
                        <button class="btn-gold" type="submit"><i class="fa-solid fa-user-plus"></i> Créer le compte professeur</button>
                    </div>
                </form>
            </article>

            <article class="form-card dashboard-professor-list">
                <div class="section-heading">
                    <div>
                        <span class="overline">Dernières créations</span>
                        <h2>Professeurs récents</h2>
                    </div>
                </div>
                <div class="teacher-list compact-teacher-list">
                    <?php if ($recent_professeurs && mysqli_num_rows($recent_professeurs) > 0): ?>
                        <?php while ($professeur = mysqli_fetch_assoc($recent_professeurs)): ?>
                            <div class="teacher-item">
                                <div class="teacher-avatar"><?= e(strtoupper(substr($professeur['prenom'], 0, 1) . substr($professeur['nom'], 0, 1))) ?></div>
                                <div>
                                    <strong><?= e(trim($professeur['prenom'] . ' ' . $professeur['nom'])) ?></strong>
                                    <small><?= e($professeur['email']) ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">Aucun professeur créé pour le moment.</div>
                    <?php endif; ?>
                </div>
            </article>
        </section> -->

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
                                <div class="pt-5 mt-5 border-t border-slate-100">
                                    <a
                                        href="view_pdf.php?id=<?= $memoire['idAM'] ?>"
                                        target="_blank"
                                        class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-slate-950 hover:bg-slate-900 group-hover:bg-indigo-600 text-white font-bold text-xs rounded-xl transition-all shadow-sm gap-2"
                                        style="background-color: #2563eb;"
                                    >
                                        <i class="fa-solid fa-eye-slash text-xs"></i> Lire en lecture sécurisée
                                    </a>
                                </div>
                                <a class="btn-blue" href="modifier_publication.php?id=<?= (int) $memoire['idAM'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i> Modifier
                                </a>
                                <!-- Bouton intelligent à deux étapes (Copiez ceci dans vos colonnes d'action pour chaque mémoire) -->
                                <button 
                                    type="button"
                                    class="btn-delete-inline"
                                    data-url="supprimer_pubication.php?id=<?= $memoire['idAM'] ?>"
                                    onclick="handleInlineDelete(this)"
                                    style="background-color: #dc2626; color: white; border: none; padding: 8px 12px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: all 0.2s;"
                                >
                                    Supprimer
                                </button>

                                <script>
                                function handleInlineDelete(btn) {
                                    if (!btn.dataset.confirmed) {
                                        // Premier clic : on passe en mode d'attente de confirmation
                                        btn.dataset.confirmed = "true";
                                        btn.style.backgroundColor = "#f59e0b"; // Orange ambré
                                        btn.innerHTML = "⚠️ Confirmer ?";
                                        
                                        // Réinitialise le bouton après 4 secondes sans interaction
                                        setTimeout(() => {
                                            btn.removeAttribute('data-confirmed');
                                            btn.style.backgroundColor = "#dc2626"; // Retour au rouge
                                            btn.innerHTML = "Supprimer";
                                        }, 4000);
                                    } else {
                                        // Deuxième clic validé : redirection directe vers l'action de suppression
                                        window.location.href = btn.dataset.url;
                                    }
                                }
                                </script>
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







