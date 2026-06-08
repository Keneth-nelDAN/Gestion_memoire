<?php

// Permet de lire nativement le PDF en respectant le routing du dossier memoire/
require __DIR__ . '/../../../views/memoire/view_pdf.php';

// Vue lecteur_securise.php - à appeler par le contrôleur de votre MVC
// L'ID est disponible via $_GET['id'] ou passé par le contrôleur
$memoire_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$filePath = "";
$themeMemo = "Thèse d'Étude GASA";

if ($memoire_id > 0 && isset($conn)) {
    $query = "SELECT theme, fichier, chemin_pdf, document, chemin FROM l_ancien_memoire WHERE idAM = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $memoire_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $themeMemo = $row['theme'] ?? $themeMemo;
            $filePath = $row['fichier'] ?? $row['chemin_pdf'] ?? $row['document'] ?? $row['chemin'] ?? '';
        }
        mysqli_stmt_close($stmt);
    }
}

if (empty($filePath)) {
    $filePath = "uploads/exemples/sample_thesis.pdf"; 
}

// Données d'audit filigrane
$student_name = $_SESSION['user_name'] ?? $_SESSION['nom'] ?? 'Candidat';
$client_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$current_time = date('d/m/Y H:i');
$watermark_text = "UATM GASA • $student_name ($client_ip) • $current_time • NON REPRODUCIBLE";

$fileData = "";
$searchPaths = [
    $filePath,
    __DIR__ . '/' . $filePath,
    __DIR__ . '/../../../../' . $filePath,
    __DIR__ . '/../../../' . $filePath,
    "uploads/exemples/sample_thesis.pdf",
];

foreach ($searchPaths as $path) {
    if (!empty($path) && file_exists($path) && is_file($path)) {
        $fileData = base64_encode(file_get_contents($path));
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lecteur Universitaire Sécurisé GASA-Shield</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }
        @media print {
            body, html, canvas, #pdf-pages-container {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col justify-between">
    <header class="bg-gray-800 border-b border-gray-700 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="bg-indigo-600 text-white font-bold text-xs px-2.5 py-1 rounded">GASA-SHIELD ACTIVE</span>
            <p class="text-xs text-indigo-300 font-extrabold truncate" style="max-width: 400px;"><?= htmlspecialchars($themeMemo, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <button onclick="window.close();" class="bg-gray-700 hover:bg-red-600 text-slate-100 font-bold px-4 py-1 rounded transition text-xs">Fermer</button>
    </header>

    <div id="security-warning" class="hidden bg-red-600 text-white px-6 py-2.5 text-center text-xs font-bold">
        ⚠️ Action sécurisée bloquée : Captures de texte, impressions et téléchargements du document original sont contrôlés et bloqués.
    </div>

    <main class="flex-1 overflow-y-auto p-4 flex flex-col items-center bg-gray-950">
        <div id="pdf-pages-container" class="flex flex-col items-center space-y-4">
            <div id="loading" class="text-indigo-400 py-12 flex flex-col items-center space-y-2">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
                <p class="text-xs font-bold">Moteur vectoriel sécurisé en cours de rendu...</p>
            </div>
        </div>
    </main>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
        const base64Data = '<?= $fileData ?>';

        if (base64Data.trim() === '') {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('pdf-pages-container').innerHTML = `<p class="text-rose-400 font-bold py-10">Fichier de thèse introuvable sur le serveur.</p>`;
        } else {
            const raw = window.atob(base64Data);
            const array = new Uint8Array(new ArrayBuffer(raw.length));
            for(let i = 0; i < raw.length; i++) {
                array[i] = raw.charCodeAt(i);
            }

            pdfjsLib.getDocument({ data: array }).promise.then(function(pdf) {
                document.getElementById('loading').style.display = 'none';
                for (let num = 1; num <= pdf.numPages; num++) {
                    pdf.getPage(num).then(function(page) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'relative bg-white shadow-xl rounded overflow-hidden my-3';
                        wrapper.style.width = '750px';

                        const canvas = document.createElement('canvas');
                        wrapper.appendChild(canvas);

                        // Filigrane de traçabilité contre les captures photo externes
                        const label = document.createElement('div');
                        label.className = 'absolute inset-0 pointer-events-none flex items-center justify-center overflow-hidden';
                        label.innerHTML = `
                            <div style="transform: rotate(-30deg); font-size: 16px; font-weight: 800; color: rgba(99, 102, 241, 0.08); text-align: center;">
                                <?= $watermark_text ?><br>COPIE INTERDITE / UATM GASA
                            </div>`;
                        wrapper.appendChild(label);
                        document.getElementById('pdf-pages-container').appendChild(wrapper);

                        const context = canvas.getContext('2d');
                        const viewport = page.getViewport({ scale: 1.2 });
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        page.render({ canvasContext: context, viewport: viewport });
                    });
                }
            });
        }

        // Bloquer clic droit et F12
        document.addEventListener('contextmenu', e => { e.preventDefault(); triggerAlert(); });
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey && ['p','s','c'].includes(e.key.toLowerCase())) || (e.metaKey && ['p','s','c'].includes(e.key.toLowerCase())) || e.key === 'F12') {
                e.preventDefault(); triggerAlert();
            }
        });
        function triggerAlert() {
            document.getElementById('security-warning').classList.remove('hidden');
            setTimeout(() => document.getElementById('security-warning').classList.add('hidden'), 5000);
        }
    </script>
</body>
</html>