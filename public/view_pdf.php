<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';

// 1. Validation de session ouverte et inclusive
if (empty($_SESSION['user_id']) && empty($_SESSION['idetudiant'])) {
    die("<div style='font-family: sans-serif; text-align: center; padding: 50px; background: #0f172a; color: #f8fafc; height: 100vh; box-sizing: border-box;'>
        <h2 style='color: #ef4444;'>🚫 Accès refusé</h2>
        <p>Veuillez vous connecter sur le portail universitaire de l'UATM GASA pour consulter ce mémoire.</p>
        <a href='../auth/connexion.php' style='display: inline-block; margin-top: 20px; background: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;'>Se connecter</a>
    </div>");
}

// 2. Récupération & sécurisation de l'ID du document
$memoire_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$filePath = "";
$themeMemo = "Rapport de recherche académique";

if ($memoire_id > 0 && isset($conn)) {
    // Requête pour récupérer les données du mémoire
    $query = "SELECT theme, fichier, chemin_pdf, document, chemin FROM ancien_memoire WHERE idAM = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $memoire_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $themeMemo = $row['theme'] ?? $themeMemo;
            // Détection dynamique de la colonne contenant le lien ou nom du fichier
            $filePath = $row['fichier'] ?? $row['chemin_pdf'] ?? $row['document'] ?? $row['chemin'] ?? '';
        }
        mysqli_stmt_close($stmt);
    }
}

// 3. Fallback de test à défaut de fichier dans la base de données
if (empty($filePath)) {
    $filePath = "uploads/exemples/sample_thesis.pdf"; 
}

// IP & Informations d'Audit pour le Filigrane Dynamique
$student_name = $_SESSION['user_name'] ?? $_SESSION['nom'] ?? 'Étudiant Consultateur';
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'IP-VPN';
$current_time = date('d/m/Y H:i');
// Ce texte s'affichera directement sur le document
$watermark_text = "UATM GASA - " . htmlspecialchars($student_name) . " (" . $client_ip . ") - Lu le " . $current_time . " - TOUTE COPIE INTERDITE";

// 4. Flux local : Recherche intelligente sur plusieurs répertoires alternatifs
$resolvedPath = "";
$fileData = "";

$searchPaths = [
    $filePath,
    __DIR__ . '/' . $filePath,
    __DIR__ . '/../../../' . $filePath, // Si le fichier est stocké relativement à la racine du projet
    __DIR__ . '/../../' . $filePath,
    $_SERVER['DOCUMENT_ROOT'] . '/' . $filePath,
    $_SERVER['DOCUMENT_ROOT'] . '/Gestion_memoire/' . $filePath,
    "uploads/exemples/sample_thesis.pdf", // Dernier fallback de secours
];

foreach ($searchPaths as $path) {
    if (!empty($path) && file_exists($path) && is_file($path)) {
        $resolvedPath = $path;
        $fileData = base64_encode(file_get_contents($path));
        break;
    }
}

$is_student = true; // Activer le mode sécurité maximale
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lecteur Sécurisé GASA-Shield | UATM GASA Formation</title>
    <!-- Chargement sécurisé de PDF.js stable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        /* Désactive complètement l'impression des éléments en cas de tentative (Bouton d'impression Ctrl+P) */
        @media print {
            body, html, canvas, #pdf-pages-container, .relative {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col justify-between">

    <!-- En-tête sécurisé -->
    <header class="bg-gray-800 border-b border-gray-700 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="bg-red-600 text-white font-black text-xs px-2.5 py-1 rounded tracking-widest uppercase">
                GASA-SHIELD SECURE VIEW
            </span>
            <div>
                <h1 class="text-sm font-bold text-gray-100 italic" style="max-width: 450px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <?= htmlspecialchars($themeMemo, ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <p class="text-xs text-indigo-300">Archivage Institutionnel &bull; UATM GASA Formation</p>
            </div>
        </div>
        <div class="flex items-center space-x-3 text-xs">
            <span class="bg-gray-700 px-3 py-1.5 rounded text-indigo-200 font-semibold select-none">
                Auditeur : <?= htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ?> (<?= $client_ip ?>)
            </span>
            <button onclick="window.close();" class="bg-red-700 hover:bg-red-600 text-white font-bold px-4 py-1.5 rounded transition">
                Fermer l'accès
            </button>
        </div>
    </header>

    <!-- Zone d'alerte sécurité -->
    <div id="security-warning" class="hidden bg-yellow-500 text-gray-950 px-6 py-3 text-center text-xs font-bold animate-pulse">
        ⚠️ [GASA-SHIELD] Téléchargement, impression et extraction de texte interdits pour préserver les droits d'auteur des étudiants de l'UATM GASA.
    </div>

    <!-- Zone de lecture principale -->
    <main class="flex-1 overflow-y-auto p-4 md:p-8 flex flex-col space-y-8 bg-gray-950 relative" id="pdf-scrollable-container">
        
        <div class="text-center text-xs text-gray-400 max-w-lg mx-auto bg-gray-900 p-3 rounded border border-gray-800">
            🔒 Rendu vectoriel sécurisé actif. Aucun lien brut de fichier d'origine n'est transféré au navigateur.
        </div>

        <div id="pdf-pages-container" class="flex flex-col items-center space-y-6">
            <div id="loading" class="text-indigo-400 text-sm py-12 flex flex-col items-center space-y-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
                <p class="font-bold">Déchiffrement et rendu sécurisé du mémoire...</p>
            </div>
        </div>
    </main>

    <!-- Filigrane de pied de page -->
    <footer class="bg-gray-800 border-t border-gray-700 text-center py-3 text-xs text-gray-400">
        <p>&copy; <?= date('Y') ?> UATM GASA Formation. Ce document confidentiel est marqué d'une signature d'audit à votre adresse IP.</p>
    </footer>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        // Chargement hyper-sécurisé du PDF depuis la chaîne Base64 générée par le serveur
        const base64Data = '<?= $fileData ?>';

        if (base64Data.trim() === '') {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('pdf-pages-container').innerHTML = `
                <p class="text-rose-400 py-12 text-center font-bold">
                    ⚠️ Erreur : Le document PDF d'origine n'a pas pu être localisé sur le serveur.<br>
                    Veuillez contacter le secrétariat ou votre Directeur des Études.
                </p>`;
        } else {
            // Conversion Base64 vers tableau binaire exploitable par PDF.js
            const raw = window.atob(base64Data);
            const rawLength = raw.length;
            const array = new Uint8Array(new ArrayBuffer(rawLength));

            for(let i = 0; i < rawLength; i++) {
                array[i] = raw.charCodeAt(i);
            }

            const container = document.getElementById('pdf-pages-container');

            pdfjsLib.getDocument({ data: array }).promise.then(function(pdf) {
                document.getElementById('loading').style.display = 'none';

                for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                    pdf.getPage(pageNum).then(function(page) {
                        
                        // Conteneur de page (incluant le filigrane d'audit)
                        const pageWrapper = document.createElement('div');
                        pageWrapper.className = 'relative bg-white shadow-2xl rounded border border-gray-200 overflow-hidden my-4';
                        pageWrapper.style.width = '780px'; 
                        
                        // Création du Canvas de dessin
                        const canvas = document.createElement('canvas');
                        canvas.className = 'block mx-auto';
                        pageWrapper.appendChild(canvas);

                        // Filigrane d'interdiction superposé en diagonale (Introuvable par script)
                        const watermark = document.createElement('div');
                        watermark.className = 'absolute inset-0 pointer-events-none flex items-center justify-center overflow-hidden';
                        watermark.style.zIndex = '50';
                        
                        // Signature de traçabilité dynamique
                        watermark.innerHTML = `
                            <div style="
                                transform: rotate(-25deg);
                                font-size: 20px;
                                font-weight: 900;
                                color: rgba(220, 38, 38, 0.08);
                                text-align: center;
                                white-space: nowrap;
                                user-select: none;
                                line-height: 2;
                            ">
                                CONSULTE UNIQUEMENT SUR PORTAIL UATM<br>
                                <?= $watermark_text ?><br>
                                COPIE INTERDITE / PROPRIÉTÉ INTELLECTUELLE
                            </div>
                        `;
                        pageWrapper.appendChild(watermark);
                        container.appendChild(pageWrapper);

                        const context = canvas.getContext('2d');
                        const viewport = page.getViewport({ scale: 1.3 }); // Net d'écriture ajusté
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
                console.error("Erreur de décryptage PDF :", err);
                document.getElementById('loading').style.display = 'none';
                container.innerHTML = `<p class="text-rose-400 py-12 text-center font-bold">Impossible de générer le rendu vectoriel du document. Type de PDF incompatible.</p>`;
            });
        }

        // 🛡️ SÉCURITÉS SUPPLÉMENTAIRES (ANTI-COPIE & PRINT)
        
        // Clic droit interdit
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            triggerSecurityAlert();
        });

        // Désactiver copier/coller et raccourcis d'impression/sauvegarde
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