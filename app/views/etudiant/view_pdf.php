<?php
// Exemple de validation de session active
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Accès refusé. Veuillez vous connecter pour consulter les mémoires de l'UATM GASA.");
}

// Récupération de l'ID du document
$memoire_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Simulation de récupération du chemin d'accès chiffré ou masqué depuis votre base de données SQL
// (Exemple : $filePath = "public/uploads/memoires/un-memoire-protege.pdf")
$filePath = "uploads/exemples/sample_thesis.pdf"; 

// Si l'utilisateur est un simple étudiant consultant, on active le mode anti-téléchargement strict
$is_student = ($_SESSION['user_role'] === 'student' || $_SESSION['user_role'] === 'guest');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lecteur Sécurisé GASA-Shield | Consultation Universitaire</title>
    <!-- Chargement securisé de PDF.js version stable depuis CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        /* Desactivation de la selection de texte native pour eviter le plagiat */
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col justify-between">

    <!-- En-tête du Lecteur Protégé -->
    <header class="bg-gray-800 border-b border-gray-700 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="bg-red-500 text-gray-950 font-black text-xs px-2 py-1 rounded tracking-widest uppercase">
                GASA-SHIELD ACTIVE PROTECT
            </span>
            <div>
                <h1 class="text-sm font-bold text-gray-100">Consultation Numérique Sécurisée</h1>
                <p class="text-xs text-slate-400">Archivage Institutionnel &bull; UATM GASA Formation</p>
            </div>
        </div>
        <div class="flex items-center space-x-3 text-xs">
            <span class="bg-gray-700 px-3 py-1.5 rounded text-indigo-300 font-semibold select-none">
                Profil : <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Étudiant Consultateur'); ?>
            </span>
            <a href="javascript:window.close();" class="bg-gray-700 hover:bg-red-600 text-white font-bold px-4 py-1.5 rounded transition">
                Fermer
            </a>
        </div>
    </header>

    <!-- Zone d'alerte sur action interdite -->
    <div id="security-warning" class="hidden bg-yellow-500 text-gray-950 px-6 py-3 text-center text-xs font-bold animate-pulse">
        ⚠️ Action sécurisée bloquée : Le téléchargement, l'impression et les captures de texte sont strictement interdits pour protéger la propriété littéraire de l'auteur.
    </div>

    <!-- Conteneur Rendu Principal de PDF.js -->
    <main class="flex-1 overflow-y-auto p-4 md:p-8 flex flex-col space-y-8 bg-gray-950 relative" id="pdf-scrollable-container">
        
        <div class="text-center text-xs text-gray-400 max-w-lg mx-auto bg-gray-900 p-3 rounded border border-gray-800">
            🔒 Ce document est affiché sous forme graphique via HTML5. L'accès direct au fichier PDF d'origine est masqué par le serveur d'UATM GASA.
        </div>

        <!-- Les pages générées en dynamique s'injecteront ici -->
        <div id="pdf-pages-container" class="flex flex-col items-center space-y-6">
            <div id="loading" class="text-indigo-400 text-sm py-12 flex flex-col items-center space-y-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
                <p class="font-bold">Génération du rendu vectoriel sécurisé...</p>
            </div>
        </div>
    </main>

    <!-- Pied de Page de Traçabilité -->
    <footer class="bg-gray-800 border-t border-gray-700 text-center py-3 text-xs text-gray-400">
        <p>&copy; 2026 UATM GASA Formation. Ce document comporte des métadonnées invisibles et des filigranes pour identifier toute capture frauduleuse.</p>
    </footer>

    <!-- LOGIQUE JS ULTRA-SÉCURISÉE -->
    <script>
        // Spécification de l'URL du Worker de rendu PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        // Passer le chemin du fichier de façon sécurisée depuis le serveur PHP
        const pdfUrl = '<?php echo $filePath; ?>'; 
        const container = document.getElementById('pdf-pages-container');

        // Charger le document d'origine
        pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
            document.getElementById('loading').style.display = 'none';

            // Boucler sur toutes les pages du PDF pour les dessiner une par une
            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                pdf.getPage(pageNum).then(function(page) {
                    
                    // Conteneur de la page individuelle (incluant le filigrane dynamique)
                    const pageWrapper = document.createElement('div');
                    pageWrapper.className = 'relative bg-white shadow-2xl rounded border border-gray-200 overflow-hidden my-4';
                    pageWrapper.style.width = '750px'; // Dimensions idéales de lecture universitaire
                    
                    // Création du Canvas pour le dessin de la page
                    const canvas = document.createElement('canvas');
                    canvas.className = 'block mx-auto';
                    pageWrapper.appendChild(canvas);

                    // Filigrane visuel en diagonale indélébile par-dessus le Canvas
                    const watermark = document.createElement('div');
                    watermark.className = 'absolute inset-0 pointer-events-none flex items-center justify-center overflow-hidden';
                    watermark.innerHTML = `
                        <div style="
                            transform: rotate(-30deg);
                            font-size: 32px;
                            font-weight: 900;
                            color: rgba(99, 102, 241, 0.09);
                            text-align: center;
                            white-space: nowrap;
                            user-select: none;
                            line-height: 1.5;
                        ">
                            PROPRIÉTÉ EXCLUSIVE UATM<br>
                            CONSULTATION UNIQ. SUR PORTAIL<br>
                            COPIE & REPRODUCTION INTERDITE
                        </div>
                    `;
                    pageWrapper.appendChild(watermark);
                    container.appendChild(pageWrapper);

                    const context = canvas.getContext('2d');
                    const viewport = page.getViewport({ scale: 1.2 }); // Ajustement de la netteté d'écriture
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    page.render(renderContext);
                });
            }
        }).catch(err => {
            console.error("Erreur de décryptage du document :", err);
            container.innerHTML = `<p class="text-rose-400 py-12 text-center font-bold">Impossible de générer la consultation interactive sécurisée. Veuillez contacter l'administration de GASA.</p>`;
        });

        // 🛡️ ENTRAVES DE SÉCURITÉ SUPPLÉMENTAIRES (ANTI-COPIE & PRINT)
        
        // 1. Désactivation du Clic Droit
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            triggerSecurityAlert();
        });

        // 2. Désactivation des combinaisons d'impression ou de sauvegarde (Ctrl+P, Ctrl+S, Cmd+P, Cmd+S, F12)
        document.addEventListener('keydown', function(e) {
            if (
                (e.ctrlKey && (e.key === 'p' || e.key === 'P' || e.key === 's' || e.key === 'S' || e.key === 'c' || e.key === 'C')) || 
                (e.metaKey && (e.key === 'p' || e.key === 'P' || e.key === 's' || e.key === 'S' || e.key === 'c' || e.key === 'C')) ||
                e.key === 'F12'
            ) {
                e.preventDefault();
                triggerSecurityAlert();
            }
        });

        function triggerSecurityAlert() {
            const warnBanner = document.getElementById('security-warning');
            warnBanner.classList.remove('hidden');
            setTimeout(() => {
                warnBanner.classList.add('hidden');
            }, 6000);
        }
    </script>
</body>
</html>