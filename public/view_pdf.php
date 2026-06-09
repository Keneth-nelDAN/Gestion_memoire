<?php
session_start();
// Inclusion des configurations de base de données
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';

// Ajustement de la session pour accepter tous les types d'utilisateurs connectés
if (empty($_SESSION['idetudiant']) && empty($_SESSION['user_id']) && empty($_SESSION['id_user'])) {
    die("Accès refusé. Veuillez vous connecter pour consulter les mémoires de l'UATM GASA.");
}

$idAM = intval($_GET['id'] ?? 0);
if ($idAM <= 0) {
    die("Identifiant de mémoire non spécifié ou invalide.");
}

// Initialisation des variables de métadonnées
$theme = "Mémoire Académique";
$auteur = "Étudiant UATM GASA";
$fileDBName = "";

$sql = "SELECT am.*, f.nom_filiere, CONCAT(am.prenomAut, ' ', am.nomAut) AS auteur 
        FROM ancien_memoire am 
        LEFT JOIN filiere f ON f.idfiliere = am.idfiliere 
        WHERE am.idAM = ?";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $idAM);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $theme = $row['theme'];
        $auteur = $row['auteur'];
        
        // Auto-détection de la colonne contenant le fichier
        foreach (['fichier', 'fichier_pdf', 'chemin', 'pdf', 'url_pdf', 'document'] as $col) {
            if (!empty($row[$col])) {
                $fileDBName = $row[$col];
                break;
            }
        }
    }
}

// Traitement du chemin du PDF basé sur l'arborescence corrigée
$baseDir = __DIR__ . '/../direction_etude/uploads/memoires/';
$filePath = "";

if (!empty($fileDBName)) {
    // Extraction sécurisée du nom du fichier pour éviter les traversées de répertoires
    $fileName = basename($fileDBName);
    $filePath = $baseDir . $fileName;

    // Fallback si jamais la base stocke un chemin relatif complexe
    if (!file_exists($filePath)) {
        $filePath = $baseDir . $fileDBName;
    }
}

// Fallback vers un fichier template de démonstration si le fichier n'existe pas encore
if (empty($filePath) || !file_exists($filePath)) {
    $filePath = $baseDir . "sample_thesis.pdf";
}

// URL relative du point de sortie brute sécurisée (ne révèle jamais le chemin réel du fichier)
$pdfUrl = "get_secure_stream.php?id=" . $idAM;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GASA-Shield Reader - Protection Intégrale</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- PDF.js - Chargement stable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    </script>
</head>
<body class="bg-slate-900 min-h-screen text-white select-none relative overflow-x-hidden">

    <!-- Header du Lecteur Sécurisé -->
    <header class="bg-slate-950/80 backdrop-blur-md border-b border-indigo-500/10 px-6 py-4 fixed top-0 left-0 w-full z-20 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <span class="p-2 bg-indigo-600 rounded-xl text-white">
                <i class="fa-solid fa-lock"></i>
            </span>
            <div>
                <h1 class="text-sm font-bold truncate max-w-sm sm:max-w-md text-slate-100"><?= htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="text-[10px] text-slate-400 font-semibold uppercase">Consulté par : <?= htmlspecialchars($auteur, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
        <div>
            <span id="page-indicator" class="text-xs bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-lg text-slate-300 font-bold">
                Lecteur Sécurisé GASA-Shield
            </span>
        </div>
    </header>

    <!-- Zone de rendu des pages protégées -->
    <main class="pt-24 pb-12 flex flex-col items-center justify-center space-y-6">
        <!-- Loader animé -->
        <div id="loading" class="text-center py-20 space-y-4">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-indigo-500"></div>
            <p class="text-sm text-slate-400">Chargement sécurisé du mémoire en cours...</p>
        </div>

        <!-- Conteneur global ordonné des pages -->
        <div id="pdf-container" class="flex flex-col items-center gap-6"></div>
    </main>

    <!-- Script de rendering ordonné et blindage contre le piratage -->
    <script>
        const pdfUrl = '<?= $pdfUrl ?>';
        const container = document.getElementById('pdf-container');

        pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
            document.getElementById('loading').style.display = 'none';

            // Pré-allocation ordonnée des balises de pages pour garantir l'ordre de lecture
            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const pageWrapper = document.createElement('div');
                pageWrapper.className = 'relative bg-white shadow-2xl rounded-xl border border-slate-700 overflow-hidden my-4';
                pageWrapper.style.width = '750px';
                pageWrapper.style.maxWidth = '90vw';
                pageWrapper.id = 'page-wrapper-' + pageNum;

                const canvas = document.createElement('canvas');
                canvas.className = 'block mx-auto w-full';
                canvas.id = 'canvas-page-' + pageNum;
                pageWrapper.appendChild(canvas);

                // Filigrane de sécurité diagonal
                const watermark = document.createElement('div');
                watermark.className = 'absolute inset-0 pointer-events-none flex items-center justify-center overflow-hidden z-10';
                watermark.innerHTML = `
                    <div style="
                        transform: rotate(-30deg);
                        font-size: 26px;
                        font-weight: 800;
                        color: rgba(99, 102, 241, 0.09);
                        text-align: center;
                        white-space: nowrap;
                        user-select: none;
                        line-height: 1.6;
                        pointer-events: none;
                    ">
                        UATM GASA FORMATION<br>
                        COPIE & REPRODUCTION INTERDITES<br>
                        SÛRETÉ DES CONTENUS
                    </div>
                `;
                pageWrapper.appendChild(watermark);
                container.appendChild(pageWrapper);

                // Rendu asynchrone sécurisé de la page correspondante
                pdf.getPage(pageNum).then(function(page) {
                    const canvasContext = canvas.getContext('2d');
                    const viewport = page.getViewport({ scale: 1.3 });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: canvasContext,
                        viewport: viewport
                    };
                    page.render(renderContext);
                });
            }
        }).catch(function(error) {
            console.error(error);
            document.getElementById('loading').innerHTML = `
                <div class="p-6 bg-rose-500/10 rounded-2xl border border-rose-500/30 max-w-md mx-auto">
                    <i class="fa-solid fa-cloud-bolt text-rose-500 text-2xl mb-2"></i>
                    <p class="text-sm font-bold text-rose-400">Le mémoire physique est introuvable ou en cours d'évaluation.</p>
                </div>
            `;
        });

        // 🛡️ ENTRAVES TECHNIQUES ANTIVOL / ANTI-DOWNLOAD 🛡️

        // 1. Désactiver le clic droit
        document.addEventListener('contextmenu', e => e.preventDefault());

        // 2. Bloquer les tentatives d'impression et de sauvegarde (Ctrl+S, Ctrl+P, F12)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.key === 'p' || e.key === 'P')) {
                e.preventDefault();
                alert("L'impression de ce mémoire est verrouillée pour des raisons de protection de la propriété intellectuelle.");
            }
            if (e.ctrlKey && (e.key === 's' || e.key === 'S')) {
                e.preventDefault();
            }
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>