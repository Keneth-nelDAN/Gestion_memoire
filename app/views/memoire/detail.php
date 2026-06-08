<?php
session_start();
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../config/mysqli_config.php';

if (empty($_SESSION['idetudiant'])) {
    header('Location: ../../auth/connexion.php');
    exit;
}

$idAM = intval($_GET['id'] ?? 0);
if ($idAM <= 0) {
    die("Soutenance non spécifiée.");
}

$sql = "SELECT am.*, f.nom_filiere, CONCAT(am.prenomAut, ' ', am.nomAut) AS auteur 
        FROM ancien_memoire am
        LEFT JOIN filiere f ON f.idfiliere = am.idfiliere
        WHERE am.idAM = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $idAM);
mysqli_stmt_execute($stmt);
$memoire = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$memoire) {
    die("Mémoire non trouvé ou archivé.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dossier Académique - <?= htmlspecialchars($memoire['theme'], ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen text-slate-800">

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-200/60 space-y-8">
        <!-- Badge + Retours -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-6">
            <span class="bg-indigo-50 border border-indigo-150 text-indigo-700 text-xs font-bold px-3 py-1 rounded-lg uppercase">
                <?= htmlspecialchars($memoire['nom_filiere'] ?: 'Non spécifiée', ENT_QUOTES, 'UTF-8') ?>
            </span>
            <a href="index.php" class="text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors uppercase">
                <i class="fa-solid fa-chevron-left mr-1"></i> Retour à mes suivis
            </a>
        </div>

        <!-- Méta -->
        <div class="space-y-4">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-950 leading-tight">
                <?= htmlspecialchars($memoire['theme'], ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="text-sm font-semibold text-slate-400 italic">Déposé le <?= date('d/m/Y', strtotime($memoire['date_depot'])) ?></p>
        </div>

        <!-- Deux colonnes d'infos de la thèse -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 rounded-2xl p-6 border border-slate-100 text-sm">
            <p><strong><i class="fa-solid fa-user-graduate text-slate-400 mr-2"></i>Auteur de l'étude :</strong> <?= htmlspecialchars($memoire['auteur'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong><i class="fa-solid fa-chalkboard-user text-slate-400 mr-2"></i>Maître / Tuteur :</strong> (UATM GASA Faculty)</p>
            <p><strong><i class="fa-solid fa-shield-halved text-emerald-500 mr-2"></i>Statut de validation :</strong> <?= htmlspecialchars($memoire['statut'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong><i class="fa-solid fa-scale-balanced text-slate-400 mr-2"></i>Réf d'archivage :</strong> REF-<?= $memoire['idAM'] ?></p>
        </div>

        <!-- Résumé facultatif -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest">Résumé / Abstract</h3>
            <p class="text-slate-600 text-sm leading-relaxed italic bg-indigo-50/25 p-4 rounded-xl border border-indigo-500/5">
                "Ce projet de recherche explore de manière appliquée, dans le contexte des directives de formation de l'UATM, des solutions avancées. Veuillez consulter le texte intégral pour passer en revue le cadre conceptuel, méthodologique et les conclusions."
            </p>
        </div>

        <!-- CTA Vers le lecteur sécurisé -->
        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row gap-4">
            <a href="lecteur_securise.php?id=<?= $memoire['idAM'] ?>" target="_blank" class="flex-1 inline-flex items-center justify-center px-6 py-3.5 bg-slate-950 hover:bg-indigo-600 text-white font-extrabold text-sm rounded-xl transition-all shadow-md gap-2">
                <i class="fa-solid fa-eye-slash"></i> Ouvrir en Lecture Sécurisée (Séc-Reader)
            </a>
            <a href="index.php" class="inline-flex items-center justify-center px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-all">
                Retour
            </a>
        </div>
    </div>
</div>

</body>
</html>