<?php
session_start();

define('SECURE_ACCESS', true);

require_once __DIR__ . '/../../controllers/juryController.php';
require_once __DIR__ . '/../../controllers/professeurController.php';

// Vérifier si l'utilisateur est connecté et est un professeur
if (empty($_SESSION['user']) || $_SESSION['user']['type'] !== 'professeur') {
    header('Location: ../auth/connexion.php');
    exit;
}

$idprof = $_SESSION['idprof'] ?? $_SESSION['user']['id'] ?? null;
if (!$idprof) {
    header('Location: ../auth/connexion.php');
    exit;
}

$juryController = new JuryController();
$professeurController = new ProfesseurController();

// Récupérer les informations du professeur
$professor = $professeurController->getProfile($idprof);

if (!$professor) {
    header('Location: ../auth/connexion.php');
    exit;
}

$prenom = htmlspecialchars($professor['prenom'] ?? '', ENT_QUOTES, 'UTF-8');
$nom = htmlspecialchars($professor['nom'] ?? '', ENT_QUOTES, 'UTF-8');

$success_message = '';
$error_message = '';

// Gérer l'enregistrement d'un nouveau commentaire/observation (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['memoire_id'])) {
    $mem_id = intval($_POST['memoire_id']);
    $content = trim($_POST['observation_content'] ?? '');

    if (empty($content)) {
        $error_message = "Veuillez rédiger le contenu de votre observation avant d'envoyer.";
    } else {
        if ($juryController->addObservation($mem_id, $idprof, $content, $nom . " " . $prenom)) {
            $success_message = "Votre observation pédagogique a été enregistrée avec succès et diffusée de manière sécurisée.";
        } else {
            $error_message = "Impossible d'insérer l'observation.";
        }
    }
}

// Récupérer la liste des mémoires assignés pour alimenter la sélection
$memos = $juryController->getAssignedMemoires($idprof);

// Récupérer les observations réelles
$observations_to_display = $juryController->getRecentObservations($idprof);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Observations Académiques - GénieMémoire UATM</title>
    <!-- Bootstrap 5.3 + FontAwesome 6.4.0 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter & Space Grotesk -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-primary: #f8fafc;
            --sidebar-width: 290px;
            --color-gasa-blue: #1e3a8a;
            --color-gasa-dark: #0f172a;
            --color-gasa-gold: #d97706;
            --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
            --font-display: 'Space Grotesk', sans-serif;
            --border-radius-lg: 16px;
            --border-radius-xl: 24px;
            --box-shadow-soft: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 8px -1px rgba(15, 23, 42, 0.03);
            --transition-smooth: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background-color: var(--bg-primary);
            font-family: var(--font-sans);
            color: #334155;
            min-height: 100vh;
        }

        .page-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR COHESIVE STYLE */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--color-gasa-dark) 0%, #1e1b4b 100%);
            color: rgba(255, 255, 255, 0.85);
            padding: 32px 20px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            transition: var(--transition-smooth);
        }

        .logo-block {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 20px;
            border-b: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);
        }

        .logo-title {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 18px;
            color: #ffffff;
            letter-spacing: -0.025em;
            margin: 0;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius-lg);
            padding: 16px;
        }

        .profile-card h3 {
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 4px 0;
        }

        /* Nav Menu */
        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nav-menu li a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            border-radius: 12px;
            transition: var(--transition-smooth);
        }

        .nav-menu li a:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .nav-menu li a.active {
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            color: #0f172a;
            font-weight: 600;
        }

        .logout-button {
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            border-radius: 12px;
            font-size: 14px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid rgba(239, 68, 68, 0.15);
            transition: var(--transition-smooth);
        }

        .logout-button:hover {
            background: #ef4444;
            color: #ffffff;
        }

        /* MAIN AREA */
        .main {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 40px;
            transition: var(--transition-smooth);
        }

        .main-header h1 {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 30px;
            color: var(--color-gasa-dark);
            margin: 0;
        }

        .main-header p {
            color: #64748b;
            margin: 4px 0 0;
            font-size: 14px;
        }

        .section-card {
            background: #ffffff;
            border-radius: var(--border-radius-xl);
            padding: 28px;
            border: 1px solid #e2e8f0;
            box-shadow: var(--box-shadow-soft);
            margin-bottom: 28px;
        }

        .section-card h3 {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 18px;
            color: var(--color-gasa-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Controls */
        .form-label-custom {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            margin-bottom: 6px;
        }

        .form-control-custom, .form-textarea-custom, .form-select-custom {
            border-radius: 12px;
            border-color: #cbd5e1;
            padding: 12px 14px;
            font-size: 13.5px;
            transition: var(--transition-smooth);
        }

        .form-control-custom:focus, .form-textarea-custom:focus, .form-select-custom:focus {
            box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.1);
            border-color: var(--color-gasa-blue);
        }

        /* Obs item card */
        .obs-item {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: var(--border-radius-lg);
            padding: 20px;
            margin-bottom: 16px;
        }

        .obs-item .obs-header {
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .obs-item h4 {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.4;
        }

        .obs-item .obs-meta {
            font-size: 11.5px;
            color: #64748b;
            margin-top: 4px;
        }

        .obs-comment-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            position: relative;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            font-size: 12.5px;
        }

        .comment-role {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2.5px 6px;
            background-color: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border-radius: 4px;
        }

        .comment-text {
            font-size: 13px;
            color: #475569;
            font-style: italic;
            line-height: 1.5;
            margin: 0;
        }

        .mobile-header {
            display: none;
            background-color: var(--color-gasa-dark);
            color: white;
            padding: 16px 20px;
            align-items: center;
            justify-content: space-between;
        }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main { margin-left: 0; padding: 24px; }
            .mobile-header { display: flex; }
        }
    </style>
</head>
<body>

    <div class="mobile-header d-lg-none">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="logo-icon">M</span>
            <span class="logo-title">GénieMémoire</span>
        </div>
        <button class="btn text-white fs-4 p-0 border-0" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    </div>

    <div class="page-wrapper">
        
        <?php include __DIR__ . '/../partials/sidebar_PROF.php'; ?>

        <!-- MAIN -->
        <main class="main">
            
            <div class="main-header mb-4">
                <h1>Observations & Recommandations</h1>
                <p>Diffusez instantanément des rapports informels, corrections de code ou annotations de thèse.</p>
            </div>

            <!-- TOAST MESSAGES -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 mb-4 p-3 rounded-4 d-flex align-items-center gap-2" role="alert" style="background-color: #d1fae5; color: #065f46; font-size: 13.5px;">
                    <i class="fas fa-check-circle fs-4 me-2"></i>
                    <div><?php echo $success_message; ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 mb-4 p-3 rounded-4 d-flex align-items-center gap-2" role="alert" style="background-color: #fee2e2; color: #991b1b; font-size: 13.5px;">
                    <i class="fas fa-exclamation-triangle fs-4 me-2"></i>
                    <div><?php echo $error_message; ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                
                <!-- PUBLICATION CARD FORM -->
                <div class="col-xl-5 col-lg-12">
                    <div class="section-card">
                        <h3><i class="fas fa-paper-plane text-indigo"></i> Nouvelle Note Académique</h3>
                        
                        <form method="POST" action="observations.php">
                            <!-- Select Mémoire -->
                            <div class="mb-3">
                                <label class="form-label form-label-custom">Mémoire concerné *</label>
                                <select name="memoire_id" required class="form-select form-select-custom w-full">
                                    <option value="" disabled selected>-- Choisissez un dossier --</option>
                                    <?php foreach ($memos as $m): ?>
                                        <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['titre']); ?> (<?php echo htmlspecialchars($m['etudiant']); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Comment Textarea -->
                            <div class="mb-3">
                                <label class="form-label form-label-custom">Texte de l'Observation *</label>
                                <textarea name="observation_content" required class="form-control form-textarea-custom font-light" rows="5" placeholder="Expliquez ici vos conclusions de relecture... Ex: Le diagramme d'architecture sous la page 43 comporte des contradictions avec l'implémentation, veuillez le modifier..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-full py-2.5 font-extrabold text-xs uppercase tracking-wide bg-indigo-600 hover:bg-slate-900 text-white border-0" style="border-radius: 12px; font-family: var(--font-sans);">
                                Diffuser l'observation pédagogique
                            </button>
                        </form>
                    </div>
                </div>

                <!-- HISTORY TIMELINE -->
                <div class="col-xl-7 col-lg-12">
                    <div class="section-card">
                        <h3><i class="fas fa-history text-muted"></i> Flux récent d'Échanges</h3>
                        
                        <div class="obs-timeline">
                            <?php if (!empty($observations_to_display)): ?>
                                <?php foreach ($observations_to_display as $obs): ?>
                                    <div class="obs-item">
                                        <div class="obs-header">
                                            <h4><?php echo htmlspecialchars($obs['title']); ?></h4>
                                            <div class="obs-meta d-flex justify-content-between">
                                                <span><i class="fas fa-graduation-cap me-1"></i> Étudiant(e) : <?php echo htmlspecialchars($obs['etudiant']); ?></span>
                                                <span><i class="far fa-calendar-alt me-1"></i> <?php echo date('d/m/Y H:i', strtotime($obs['date'])); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="obs-comment-card">
                                            <div class="comment-header">
                                                <strong class="text-slate-800"><?php echo htmlspecialchars($obs['auteur']); ?></strong>
                                                <span class="comment-role"><?php echo htmlspecialchars($obs['role']); ?></span>
                                            </div>
                                            <p class="comment-text">"<?php echo htmlspecialchars($obs['contenu']); ?>"</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucune observation enregistrée pour le moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('appSidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>
