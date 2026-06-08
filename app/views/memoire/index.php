<?php
session_start();
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../config/mysqli_config.php';

if (empty($_SESSION['idetudiant'])) {
    header('Location: ../../auth/connexion.php');
    exit;
}

$etudiant_id = $_SESSION['idetudiant'];

// Récupérer les mémoires déposés par l'étudiant connecté
$sql = "SELECT am.*, f.nom_filiere 
        FROM ancien_memoire am
        LEFT JOIN filiere f ON f.idfiliere = am.idfiliere
        WHERE am.idetudiant = ? OR am.nomAut = (SELECT nom FROM etudiant WHERE idetudiant = ?)
        ORDER BY am.idAM DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $etudiant_id, $etudiant_id);
    mysqli_stmt_execute($stmt);
    $mes_memoires = mysqli_stmt_get_result($stmt);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Dépôts Académiques - GASA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen font-sans text-slate-800">

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <header class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">Espace Personnel</span>
            <h1 class="text-2xl font-black mt-1 text-slate-900">Suivi de mes travaux de mémoire</h1>
            <p class="text-xs text-slate-500 mt-1">Gérez vos fichiers soumis pour évaluation par les directeurs d'études.</p>
        </div>
        <div class="flex gap-3">
            <a href="deposer.php" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm gap-2">
                <i class="fa-solid fa-plus"></i> Déposer un nouveau mémoire
            </a>
            <a href="../../consulter_memoire.php" class="inline-flex items-center px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold rounded-xl transition-all gap-2">
                <i class="fa-solid fa-book-open"></i> Bibliothèque Publique
            </a>
        </div>
    </header>

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/60">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        <th class="py-4 px-4">Thème</th>
                        <th class="py-4 px-4">Filière</th>
                        <th class="py-4 px-4">Date dépôt</th>
                        <th class="py-4 px-4">Statut</th>
                        <th class="py-4 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <?php if ($mes_memoires && mysqli_num_rows($mes_memoires) > 0): ?>
                        <?php while ($memo = mysqli_fetch_assoc($mes_memoires)): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-900 max-w-sm truncate">
                                    <?= htmlspecialchars($memo['theme'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    <?= htmlspecialchars($memo['nom_filiere'] ?: 'Non définie', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="py-4 px-4 text-slate-400 font-semibold text-xs">
                                    <?= date('d/m/Y', strtotime($memo['date_depot'])) ?>
                                </td>
                                <td class="py-4 px-4">
                                    <?php 
                                    $statut = strtolower($memo['statut']);
                                    if ($statut === 'publie' || $statut === 'publié' || $statut === 'publiée') {
                                        echo '<span class="inline-flex items-center px-2 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-100">Publié</span>';
                                    } elseif ($statut === 'attente' || $statut === 'en attente') {
                                        echo '<span class="inline-flex items-center px-2 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-lg border border-amber-100">En Revue</span>';
                                    } else {
                                        echo '<span class="inline-flex items-center px-2 py-1 bg-slate-50 text-slate-500 text-xs font-bold rounded-lg border border-slate-100">Brouillon</span>';
                                    }
                                    ?>
                                </td>
                                <td class="py-4 px-4 text-center flex items-center justify-center gap-2">
                                    <a href="detail.php?id=<?= $memo['idAM'] ?>" class="p-1 px-3 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg text-xs font-bold transition-all flex items-center gap-1">
                                        Fiche détaillée
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 font-medium">
                                Aucun mémoire soumis pour le moment.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>