<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';

if (empty($_SESSION['idetudiant']) && empty($_SESSION['idprof']) && empty($_SESSION['idde'])) {
    die("Accès refusé. Veuillez vous connecter pour consulter les mémoires de l'UATM GASA.");
}

$idAM = intval($_GET['id'] ?? 0);
if ($idAM <= 0) {
    die("Identifiant de mémoire non spécifié ou invalide.");
}

$id_user_actuel = $_SESSION['idetudiant'] ?? 0;

$theme = "Mémoire Académique";
$auteur = "UATM GASA student";

// Récupération des informations du mémoire
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
    }
    mysqli_stmt_close($stmt);
}

// Vérifier si l'étudiant connecté a déjà liké ce mémoire
$deja_like = false;
if ($id_user_actuel > 0) {
    $like_chk = mysqli_query($conn, "SELECT idlike FROM like_memoire WHERE idAM = $idAM AND idetudiant = $id_user_actuel");
    if (mysqli_num_rows($like_chk) > 0) {
        $deja_like = true;
    }
}

// Compter le nombre total de likes
$likes_count_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM like_memoire WHERE idAM = $idAM");
$total_likes = mysqli_fetch_assoc($likes_count_res)['total'] ?? 0;

// Récupérer les commentaires existants liés à ce mémoire avec le nom/prenom de l'étudiant auteur
$comments_query = mysqli_query($conn, "
    SELECT c.*, e.nom, e.prenom 
    FROM commentaire c 
    JOIN etudiant e ON c.idetudiant = e.idetudiant 
    WHERE c.idmemoire = $idAM 
    ORDER BY c.date_commentaire ASC
");

$pdfUrl = "../../../get_secure_stream.php?id=" . $idAM;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GASA-Shield Reader - Protection Intégrale</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';</script>
</head>
<body class="bg-slate-900 min-h-screen text-white select-none relative overflow-x-hidden flex flex-col md:flex-row">

    <div class="flex-1 min-h-screen relative overflow-y-auto pb-12">
        <header class="bg-slate-950/80 backdrop-blur-md border-b border-indigo-500/10 px-6 py-4 fixed top-0 left-0 w-full md:w-[calc(100%-24rem)] GiantScreenWidth z-20 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="p-2 bg-indigo-600 rounded-xl text-white">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <div>
                    <h1 class="text-sm font-bold truncate max-w-sm text-slate-100"><?= htmlspecialchars($theme, ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase">Auteur: <?= htmlspecialchars($auteur, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
            <div>
                <span class="text-xs bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-lg text-slate-300 font-bold">
                    GASA-Shield
                </span>
            </div>
        </header>

        <main class="pt-24 flex flex-col items-center justify-center space-y-6">
            <div id="loading" class="text-center py-20 space-y-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-indigo-500"></div>
                <p class="text-sm text-slate-400">Chargement sécurisé du mémoire...</p>
            </div>
            <div id="pdf-container" class="flex flex-col items-center gap-6"></div>
        </main>
    </div>

    <aside class="w-full md:w-96 bg-slate-950 border-t md:border-t-0 md:border-l border-slate-800 flex flex-col h-screen md:sticky md:top-0 z-30">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950">
            <div class="flex items-center gap-2">
                <button id="likeBtn" onclick="toggleLike()" class="p-3 rounded-xl bg-slate-900 border border-slate-800 hover:border-rose-500/50 transition-all text-xl flex items-center justify-center">
                    <i id="likeIcon" class="<?= $deja_like ? 'fa-solid text-rose-500' : 'fa-regular text-slate-400' ?> fa-heart"></i>
                </button>
                <div>
                    <span id="likeCount" class="text-sm font-bold text-slate-100"><?= $total_likes ?></span>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Appréciations</p>
                </div>
            </div>
            <span class="text-xs font-bold text-indigo-400 bg-indigo-500/10 px-2.5 py-1 rounded-md border border-indigo-500/20">
                Espace d'échange
            </span>
        </div>

        <div id="commentsBox" class="flex-1 p-4 overflow-y-auto space-y-4 max-h-[400px] md:max-h-none select-text">
            <?php if (mysqli_num_rows($comments_query) > 0): ?>
                <?php while ($comm = mysqli_fetch_assoc($comments_query)): ?>
                    <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800/80 space-y-1">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-indigo-400"><?= htmlspecialchars($comm['prenom'] . ' ' . $comm['nom'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="text-slate-500"><?= date('d/m/Y à H:i', strtotime($comm['date_commentaire'])) ?></span>
                        </div>
                        <p class="text-xs text-slate-300 font-normal leading-relaxed"><?= nl2br(htmlspecialchars($comm['contenu'], ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div id="noCommentText" class="text-center py-8 text-slate-500 text-xs">
                    <i class="fa-regular fa-comments text-xl mb-1.5 block"></i> Aucun commentaire pour le moment. Soyez le premier à donner votre avis !
                </div>
            <?php endif; ?>
        </div>

        <div class="p-4 border-t border-slate-800 bg-slate-950">
            <?php if ($id_user_actuel > 0): ?>
                <form id="commentForm" onsubmit="sendComment(event)" class="relative flex items-center">
                    <input 
                        type="text" 
                        id="commentInput" 
                        placeholder="Écrire un commentaire académique..." 
                        class="w-full bg-slate-900 border border-slate-800 rounded-xl pl-4 pr-12 py-3 text-xs focus:outline-none focus:border-indigo-500 text-slate-100 placeholder-slate-500 select-text"
                        required
                    >
                    <button type="submit" class="absolute right-2 p-2 text-indigo-400 hover:text-indigo-300 transition-colors">
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                    </button>
                </form>
            <?php else: ?>
                <p class="text-[11px] text-center text-rose-400">Seuls les étudiants peuvent interagir.</p>
            <?php endif; ?>
        </div>
    </aside>

    <script>
        const pdfUrl = '<?= $pdfUrl ?>';
        const container = document.getElementById('pdf-container');
        const idAM = <?= $idAM ?>;

        // Code d'intégration PDF.js (Inchangé)
        pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
            document.getElementById('loading').style.display = 'none';
            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const pageWrapper = document.createElement('div');
                pageWrapper.className = 'relative bg-white shadow-2xl rounded-xl border border-slate-700 overflow-hidden my-4';
                pageWrapper.style.width = '750px'; pageWrapper.style.maxWidth = '90vw';

                const canvas = document.createElement('canvas');
                canvas.className = 'block mx-auto w-full';
                pageWrapper.appendChild(canvas);

                const watermark = document.createElement('div');
                watermark.className = 'absolute inset-0 pointer-events-none flex items-center justify-center overflow-hidden z-10';
                watermark.innerHTML = `<div style="transform: rotate(-30deg); font-size: 26px; font-weight: 800; color: rgba(99, 102, 241, 0.09); text-align: center; white-space: nowrap;">UATM GASA FORMATION<br>COPIE INTERDITE</div>`;
                pageWrapper.appendChild(watermark);
                container.appendChild(pageWrapper);

                pdf.getPage(pageNum).then(function(page) {
                    const canvasContext = canvas.getContext('2d');
                    const viewport = page.getViewport({ scale: 1.3 });
                    canvas.height = viewport.height; canvas.width = viewport.width;
                    page.render({ canvasContext: canvasContext, viewport: viewport });
                });
            }
        }).catch(function(error) {
            document.getElementById('loading').innerHTML = `<div class="p-6 bg-rose-500/10 rounded-2xl border border-rose-500/30 max-w-md text-center"><p class="text-sm font-bold text-rose-400">Erreur lors de la lecture sécurisée.</p></div>`;
        });

        // FONCTION AJAX POUR LIKER
        function toggleLike() {
            const formData = new FormData();
            formData.append('action', 'toggle_like');
            formData.append('idAM', idAM);

            fetch('../../../action_memoire.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    const icon = document.getElementById('likeIcon');
                    if(data.liked) {
                        icon.className = 'fa-solid text-rose-500 fa-heart';
                    } else {
                        icon.className = 'fa-regular text-slate-400 fa-heart';
                    }
                    document.getElementById('likeCount').innerText = data.total_likes;
                }
            });
        }

        // FONCTION AJAX POUR COMMENTER
        function sendComment(e) {
            e.preventDefault();
            const input = document.getElementById('commentInput');
            const contenu = input.value.trim();
            if(!contenu) return;

            const formData = new FormData();
            formData.append('action', 'add_comment');
            formData.append('idAM', idAM);
            formData.append('contenu', contenu);

            fetch('../../../action_memoire.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    input.value = '';
                    const noCommText = document.getElementById('noCommentText');
                    if(noCommText) noCommText.remove();

                    const box = document.getElementById('commentsBox');
                    const newComment = document.createElement('div');
                    newComment.className = 'bg-slate-900/60 p-3 rounded-xl border border-slate-800/80 space-y-1';
                    newComment.innerHTML = `
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-indigo-400">Moi</span>
                            <span class="text-slate-500">${data.date}</span>
                        </div>
                        <p class="text-xs text-slate-300 font-normal leading-relaxed">${data.contenu}</p>
                    `;
                    box.appendChild(newComment);
                    box.scrollTop = box.scrollHeight; // Auto scroll vers le bas
                } else {
                    alert(data.message);
                }
            });
        }

        // Protections d'écran inchangées
        document.addEventListener('contextmenu', e => e.preventDefault());
    </script>
</body>
</html>