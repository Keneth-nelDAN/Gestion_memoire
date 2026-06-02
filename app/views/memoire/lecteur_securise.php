<?php
// app/views/memoire/lecteur_securise.php
session_start();
require_once "../../config/database.php";
require_once "../../app/models/publication.php";

$database = new Database();
$pdo = $database->connect();

$idAM = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pubModel = new Publication($pdo);
$memoire = $pubModel->getById($idAM);

if (!$memoire) {
    die("Ce mémoire n'existe pas ou n'est plus public.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Visualiseur Sécurisé - <?= htmlspecialchars($memoire['theme']) ?></title>
    <!-- Tailwind CSS pour un design ultra moderne -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Librairie Mozilla PDF.js officielle chargée via CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <style>
        /* Anti-sélection de texte pour contrer la copie de paragraphes complets */
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        canvas {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            margin-bottom: 2rem;
            max-width: 100%;
        }
        /* Cache la frame d'impression de page habituelle du navigateur */
        @media print {
            body { display: none; }
        }
    </style>
</head>
<body class="bg-gray-900 min-h-screen flex flex-col antialiased select-none" oncontextmenu="return false;">

    <!-- Barre d'outils de visualisation sécurisée -->
    <header class="bg-gray-800 text-white px-6 py-4 flex items-center justify-between border-b border-gray-700 shadow-lg sticky top-0 z-50">
        <div class="flex items-center space-x-3">
            <span class="text-blue-400 font-extrabold text-lg">GASA Archive Secured</span>
            <div class="h-4 w-px bg-gray-600"></div>
            <span class="text-xs text-gray-400 truncate max-w-sm md:max-w-md font-mono"><?= htmlspecialchars($memoire['theme']) ?></span>
        </div>

        <div class="flex items-center space-x-4">
            <span class="text-xs text-red-400 bg-red-950 px-2 py-1 rounded border border-red-800 font-bold">MODE CONSULTATION SEULE (Téléchargement désactivé)</span>
            <a href="index.php" class="bg-gray-700 hover:bg-gray-600 font-semibold text-xs px-3.5 py-1.5 rounded transition">Retour</a>
        </div>
    </header>

    <!-- Zone principale contenant le visualiseur -->
    <main class="flex-grow flex flex-col items-center justify-start p-4 md:p-8 overflow-y-auto">
        <div id="loading" class="text-gray-400 text-center py-20">
            <svg class="animate-spin h-10 w-10 text-blue-500 mx-auto mb-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="font-medium">Chargement sécurisé du document en cours de traitement...</p>
        </div>

        <!-- Conteneur global recevant les Canvas injectés par PDF.js -->
        <div id="pdf-container" class="w-full max-w-4xl hidden flex flex-col items-center"></div>
    </main>

    <script>
        // Charger la ressource PDF.js globale
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        
        // Spécification de l'URL du Worker PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        // L'URL masque pointant vers le proxy stream qui diffuse le document
        const documentUrl = '../../../public/visualiser_pdf.php?id=<?= $memoire['idAM'] ?>';
        const pdfContainer = document.getElementById('pdf-container');
        const loadingIndicator = document.getElementById('loading');

        // Charger asynchroniquement le PDF
        pdfjsLib.getDocument(documentUrl).promise.then(pdf => {
            // Effacer l'indicateur de chargement
            loadingIndicator.classList.add('hidden');
            pdfContainer.classList.remove('hidden');

            // Rendu en cascade de toutes les pages sur des Canvas séparés
            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                renderPdfPage(pdf, pageNum);
            }
        }).catch(err => {
            console.error(err);
            loadingIndicator.innerHTML = `
                <div class="text-red-500">
                    <svg class="h-12 w-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="font-bold">Impossible d'afficher le document.</p>
                    <p class="text-sm text-gray-500 mt-1">${err.message}</p>
                </div>
            `;
        });

        // Fonction maîtresse de génération de Canvas par page
        function renderPdfPage(pdf, pageNumber) {
            pdf.getPage(pageNumber).then(page => {
                const scale = 1.5; // Qualité de zoom HD
                const viewport = page.getViewport({ scale: scale });

                // Création dynamique d'un Canvas HTML5 dédié à la page
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                pdfContainer.appendChild(canvas);

                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                page.render(renderContext);
            });
        }

        // --- SCRIPTS DE PROTECTION SUPPLÉMENTAIRES ---

        // 1. Bloquer le Clic-Droit pour stopper l'option "Enregistrer l'image sous" du canvas
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
        });

        // 2. Bloquer les touches clavier critiques (Impression, Sauvegarde, Capture, inspecteur)
        document.addEventListener('keydown', (e) => {
            // Empêche Ctrl + S (Sauvegarder)
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                alert("Sauvegarde locale désactivée pour la protection des droits du mémoire.");
            }
            // Empêche Ctrl + P (Imprimer)
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                alert("Option d'impression désactivée.");
            }
            // Empêche l'ouverture des Outils de Développement (F12 ou Ctrl+Maj+I)
            if (e.key === 'F12' || ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'I')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>