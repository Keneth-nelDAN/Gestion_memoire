<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';

// Force l'affichage des erreurs pour voir immédiatement ce qui bloque si besoin
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (empty($_SESSION['idetudiant'])) {
    header('Location: ../auth/connexion.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
$filiere_filter = trim($_GET['filiere'] ?? '');

// Gestion simplifiée et robuste du statut pour éviter les conflits d'accents
$where = "WHERE (am.statut LIKE 'publi%' OR am.statut = 'publie')";
$types = '';
$params = [];

if ($search !== '') {
    $where .= " AND (am.theme LIKE ? OR am.nomAut LIKE ? OR am.prenomAut LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

if ($filiere_filter !== '') {
    // Utilisation de nom_filiere
    $where .= " AND f.nom_filiere = ?";
    $params[] = $filiere_filter;
    $types .= 's';
}

// Requête principale nettoyée avec nom_filiere
$sql = "SELECT am.*, f.nom_filiere, CONCAT(am.prenomAut, ' ', am.nomAut) AS auteur,
        (SELECT COUNT(*) FROM like_memoire lm WHERE lm.idAM = am.idAM) AS likes
    FROM ancien_memoire am
    LEFT JOIN filiere f ON f.idfiliere = am.idfiliere
    $where
    ORDER BY am.idAM DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Erreur de préparation SQL (Requête principale) : " . mysqli_error($conn));
}

if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$memoires = mysqli_stmt_get_result($stmt);

// Liste des filières pour le menu déroulant avec nom_filiere
$filieres_query = mysqli_query($conn, "SELECT DISTINCT nom_filiere FROM filiere WHERE nom_filiere IS NOT NULL ORDER BY nom_filiere ASC");

if (!$filieres_query) {
    die("Erreur de requête (Liste filières) : " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Consultation des travaux</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen font-sans antialiased text-slate-800">

<main class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <header class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/60 mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="space-y-2">
            <span class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full uppercase tracking-wider">
                <i class="fa-solid fa-graduation-cap mr-1.5"></i> Bibliothèque Académique
            </span>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-950">Consulter les mémoires</h1>
            <p class="text-sm text-slate-500 leading-relaxed max-w-2xl">
                Accès autorisé uniquement en lecture sécurisée sur écran. Toute capture, tentative de téléchargement ou copie papier est proscrite par la réglementation de l'UATM.
            </p>
        </div>
        <a class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-slate-900 text-white font-semibold text-sm rounded-xl transition-all shadow-sm gap-2 shrink-0 self-start md:self-center" href="dashboard_etudiant.php">
            <i class="fa-solid fa-arrow-left"></i> Retour Tableau de bord
        </a>
    </header>

    <section class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/60 space-y-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Mémoires validés & publiés</h2>
                <p class="text-xs text-slate-400">Faites vos filtres pour affiner votre recherche</p>
            </div>
        </div>

        <form class="bg-slate-50 rounded-2xl p-4 border border-slate-100 grid grid-cols-1 md:grid-cols-4 gap-4" method="get">
            <div class="md:col-span-2 relative">
                <input 
                    type="search" 
                    name="q" 
                    value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" 
                    placeholder="Par thème, nom d'auteur..."
                    class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-medium text-slate-900"
                >
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400"></i>
            </div>
            
            <div>
                <select 
                    name="filiere" 
                    class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-700"
                >
                    <option value="">Toutes les filières</option>
                    <?php if ($filieres_query): ?>
                        <?php while($f = mysqli_fetch_assoc($filieres_query)): ?>
                            <option value="<?= htmlspecialchars($f['nom_filiere'], ENT_QUOTES, 'UTF-8') ?>" <?= $filiere_filter === $f['nom_filiere'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nom_filiere'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="flex gap-2">
                <button class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-indigo-600 hover:bg-slate-900 text-white font-semibold text-sm rounded-xl transition-all shadow-sm gap-2" type="submit">
                    Filtrer
                </button>
                <a class="inline-flex items-center justify-center px-4 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-sm rounded-xl transition-all" href="consulter_memoire.php">
                    <i class="fa-solid fa-rotate"></i>
                </a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($memoires && mysqli_num_rows($memoires) > 0): ?>
                <?php while ($memoire = mysqli_fetch_assoc($memoires)): ?>
                    <article class="bg-white rounded-2xl p-6 border border-slate-200 hover:border-indigo-500/30 hover:shadow-lg transition-all flex flex-col justify-between group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="bg-indigo-50 border border-indigo-100 text-indigo-700 text-[10px] font-extrabold px-2.5 py-1 rounded-md uppercase">
                                    <?= htmlspecialchars($memoire['nom_filiere'] ?: 'Générique', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider flex items-center gap-1">
                                    <i class="fa-regular fa-calendar-check"></i>
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($memoire['date_depot'])), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-2 leading-snug">
                                <?= htmlspecialchars($memoire['theme'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <div class="space-y-2 pt-3 border-t border-slate-100 text-xs text-slate-600">
                                <p class="flex items-center gap-2">
                                    <i class="fa-solid fa-user-graduate text-slate-400 w-4"></i>
                                    <span><strong>Auteur :</strong> <?= htmlspecialchars($memoire['auteur'], ENT_QUOTES, 'UTF-8') ?></span>
                                </p>
                                <p class="flex items-center gap-2">
                                    <i class="fa-solid fa-heart text-rose-500 w-4"></i>
                                    <span><strong>Likes :</strong> <?= (int) $memoire['likes'] ?></span>
                                </p>
                                <p class="flex items-center gap-2 text-indigo-600 font-medium">
                                    <i class="fa-solid fa-shield-halved w-4"></i>
                                    <span>Lecture Sécurisée</span>
                                </p>
                            </div>
                        </div>

                        <div class="pt-5 mt-5 border-t border-slate-100">
                            <a 
                                href="../memoire/view_pdf.php?id=<?= $memoire['idAM'] ?>" 
                                target="_blank" 
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-slate-950 hover:bg-slate-900 group-hover:bg-indigo-600 text-white font-bold text-xs rounded-xl transition-all shadow-sm gap-2"
                            >
                                <i class="fa-solid fa-eye text-xs"></i> Lire en lecture sécurisée
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-slate-50/50 rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center max-w-md mx-auto">
                    <div class="p-3 bg-white rounded-full shadow-sm max-w-max mx-auto mb-4 text-slate-400">
                        <i class="fa-solid fa-folder-open text-2xl"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">Aucun projet trouvé</h4>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                        Vérifiez l'orthographe ou changez de filière pour parcourir d'autres contributions académiques.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

</body>
</html>