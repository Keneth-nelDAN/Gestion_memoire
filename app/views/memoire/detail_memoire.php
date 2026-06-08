<?php

// Utilise directement la structure de detail.php pour une uniformité de conception
require __DIR__ . '/detail.php';

require_once __DIR__ . '/detail.php';
// detail_memoire.php (Chemin d'accès : app/views/memoire/detail_memoire.php)
session_start();

if (!isset($_SESSION['idetudiant'])) {
    header("Location: ../auth/connexion.php");
    exit;
}

$idAM = $_GET['id'] ?? null;
if (!$idAM) {
    die("Identifiant manquant.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lecteur de mémoire sécurisé</title>
    <!-- Google Fonts & Tailwind CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chargement officiel de la bibliothèque Mozilla PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Empêche la sélection de texte */
        .no-select {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col no-select" oncontextmenu="return false;">

    <!-- Barre d'outils supérieure du lecteur -->
    <header class="bg-slate-950 px-6 py-4 flex justify-between items-center border-b border-slate-800 shadow-lg shrink-0">
        <div class="flex items-center space-x-3">
            <span class="bg-indigo-600 text-white rounded px-2.5 py-1 text-xs font-semibold tracking-wider uppercase">LECTEUR SÉCURISÉ</span>
            <h1 class="text-md font-medium text-slate-200">Consultation en ligne</h1>
        </div>
        
        <!-- Contrôleurs et indicateurs -->
        <div class="flex items-center space-x-6">
            <div class="flex items-center space-x-2">
                <button id="prev-page" class="bg-slate-800 hover:bg-slate-700 text-white font-medium rounded-lg px-3 py-1.5 transition text-xs">Précédent</button>
                <span class="text-sm text-slate-400">
                    Page <span id="page-num" class="text-white font-bold">0</span> / <span id="page-count" class="font-bold text-slate-400">0</span>
                </span>
                <button id="next-page" class="bg-slate-800 hover:bg-slate-700 text-white font-medium rounded-lg px-3 py-1.5 transition text-xs">Suivant</button>
            </div>
            
            <div class="h-6 w-px bg-slate-800"></div>
            
            <div class="text-xs text-red-400 flex items-center bg-red-950/40 px-3 py-1.5 rounded-lg border border-red-900/50">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0-6v.01M12 2a10 10 0 110 20 10 10 0 010-20z"></path></svg>
                Téléchargement désactivé pour la protection intellectuelle
            </div>
        </div>
    </header>

    <!-- Zone d'affichage du PDF -->
    <main class="flex-1 overflow-auto p-8 flex justify-center items-start bg-slate-900">
        <div class="relative bg-slate-950 p-4 rounded-xl shadow-2xl border border-slate-800">
            <!-- Balise Canvas dans laquelle s'affiche la page du PDF -->
            <canvas id="pdf-render" class="max-w-full rounded shadow-md border border-slate-800"></canvas>
            
            <!-- Message de chargement transparent sur le document -->
            <div id="loader" class="absolute inset-0 flex items-center justify-center bg-slate-955 bg-opacity-95 text-sm text-slate-400">
                <span>Chargement du mémoire en cours...</span>
            </div>
        </div>
    </main>

    <!-- Code Javascript gérant l'appel masqué et la désactivation des commandes claviers -->
    <script>
        // Le lien pointe vers le flux PHP et non le document (.pdf) physique
        const url = '../../get_pdf.php?id=<?php echo $idAM; ?>';

        let pdfDoc = null,
            pageNum = 1,
            pageIsRendering = false,
            pageNumPending = null;

        const scale = 1.3, // Augmenter cette valeur pour une meilleure résolution
              canvas = document.querySelector('#pdf-render'),
              ctx = canvas.getContext('2d');

        // Rendu de la page
        const renderPage = num => {
            pageIsRendering = true;
            document.querySelector('#loader').classList.remove('hidden');

            pdfDoc.getPage(num).then(page => {
                const viewport = page.getViewport({ scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderCtx = {
                    canvasContext: ctx,
                    viewport: viewport
                };

                page.render(renderCtx).promise.then(() => {
                    pageIsRendering = false;
                    document.querySelector('#loader').classList.add('hidden');

                    if (pageNumPending !== null) {
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                });

                document.querySelector('#page-num').textContent = num;
            });
        };

        const queueRenderPage = num => {
            if (pageIsRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        };

        // Page précédente
        const showPrevPage = () => {
            if (pageNum <= 1) return;
            pageNum--;
            queueRenderPage(pageNum);
        };

        // Page suivante
        const showNextPage = () => {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            queueRenderPage(pageNum);
        };

        // Initier le chargement de PDF.js
        pdfjsLib.getDocument(url).promise.then(pdfDoc_ => {
            pdfDoc = pdfDoc_;
            document.querySelector('#page-count').textContent = pdfDoc.numPages;
            renderPage(pageNum);
        }).catch(err => {
            document.querySelector('#loader').innerHTML = `<p class="text-red-500 font-bold p-6">Le document n'a pas pu être chargé ou vous n'avez pas l'autorisation d'accès.</p>`;
        });

        // Liaison des boutons
        document.querySelector('#prev-page').addEventListener('click', showPrevPage);
        document.querySelector('#next-page').addEventListener('click', showNextPage);

        // --- DISPOSITIF DE SURVEILLANCE ET SÉPARATION DES TOUCHES ---
        
        // Bloquer l'impression (Ctrl+P) et la copie logique (Ctrl+S / Ctrl+C)
        window.addEventListener('keydown', e => {
            if (e.ctrlKey && (e.key === 'p' || e.key === 'P' || e.key === 's' || e.key === 'S' || e.key === 'c' || e.key === 'C')) {
                e.preventDefault();
                alert("Action non autorisée.");
            }
        });
    </script>
</body>
</html>