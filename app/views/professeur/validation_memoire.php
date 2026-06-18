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

// Gérer la soumission du formulaire d'évaluation (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $memo_id = intval($_GET['id']);
    $note = floatval($_POST['note'] ?? 0);
    $juryMembres = trim($_POST['membres_jury'] ?? '');
    $comments = trim($_POST['appreciations'] ?? '');

    if ($note < 0 || $note > 20) {
        $error_message = "La note saisie doit être comprise entre 0 et 20.";
    } else {
        if ($juryController->validateThesis($memo_id, $idprof, $note, $juryMembres, $comments)) {
            $success_message = "L'évaluation finale de soutenance a été enregistrée avec succès ! Le mémoire de l'étudiant est validé et archivé.";
        } else {
            $error_message = "Erreur lors de la notation.";
        }
    }
}

// Récupérer la liste des mémoires assignés
$pending_memos = $juryController->getPendingMemoires($idprof);

// Récupérer le mémoire actif sélectionné pour évaluation
$active_memo = null;
if (isset($_GET['id'])) {
    $active_id = intval($_GET['id']);
    $active_memo = $juryController->getMemoireDetails($active_id, $idprof);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notations & Validations - GénieMémoire UATM</title>
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
            height: 100%;
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

        /* Sidebar items list */
        .memo-select-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 580px;
            overflow-y: auto;
        }

        .memo-select-item {
            display: block;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            text-decoration: none !important;
            color: inherit !important;
            transition: var(--transition-smooth);
        }

        .memo-select-item:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateX(3px);
        }

        .memo-select-item.active {
            background-color: rgba(30, 58, 138, 0.04);
            border-color: var(--color-gasa-blue);
            box-shadow: 0 0 0 1px var(--color-gasa-blue);
        }

        .memo-select-item h4 {
            font-size: 13.5px;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #0f172a;
            line-height: 1.4;
        }

        /* Form Controls */
        .form-label-custom {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            margin-bottom: 6px;
        }

        .form-control-custom, .form-textarea-custom {
            border-radius: 12px;
            border-color: #cbd5e1;
            padding: 12px 14px;
            font-size: 13.5px;
            transition: var(--transition-smooth);
        }

        .form-control-custom:focus, .form-textarea-custom:focus {
            box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.1);
            border-color: var(--color-gasa-blue);
        }

        .notation-alert-banner {
            background-color: #fef5e7;
            border: 1px solid #fde0b5;
            color: #a75c00;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 13px;
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
                <h1>Fiches d'Évaluation & Delibération</h1>
                <p>Enregistrez de manière permanente les notes officielles de soutenance de Licence et Master.</p>
            </div>

            <!-- SUCCESS/ERROR BANNERS -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs mb-4 p-3 rounded-4 d-flex align-items-center gap-3" role="alert" style="background-color: #d1fae5; color: #065f46;">
                    <i class="fas fa-check-circle fs-4 shrink-0"></i>
                    <div><?php echo $success_message; ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-xs mb-4 p-3 rounded-4 d-flex align-items-center gap-3" role="alert" style="background-color: #fee2e2; color: #991b1b;">
                    <i class="fas fa-exclamation-circle fs-4 shrink-0"></i>
                    <div><?php echo $error_message; ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                
                <!-- LEFT COLUMN: Files list pending evaluation -->
                <div class="col-xl-4 col-lg-12">
                    <div class="section-card">
                        <h3><i class="fas fa-clipboard-list text-muted"></i> À Évaluer (<?php echo count($pending_memos); ?>)</h3>
                        
                        <?php if (empty($pending_memos)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-check-double text-success mb-3" style="font-size: 56px;"></i>
                                <h5 class="text-xs font-semibold text-slate-800">Aucun mémoire en attente</h5>
                                <p class="text-muted text-[11px] mb-0 mt-1 leading-relaxed">Toutes vos fiches de notation ont été transmises à la Direction des Épreuves.</p>
                            </div>
                        <?php else: ?>
                            <div class="memo-select-list">
                                <?php foreach ($pending_memos as $memo): ?>
                                    <a class="memo-select-item <?php echo (isset($_GET['id']) && intval($_GET['id']) === intval($memo['id'])) ? 'active' : ''; ?>" href="validation_memoire.php?id=<?php echo $memo['id']; ?>">
                                        <h4><?php echo htmlspecialchars($memo['titre']); ?></h4>
                                        <div class="d-flex flex-wrap align-items-center gap-2 justify-content-between text-xs text-muted" style="font-size: 11.5px;">
                                            <span><i class="fas fa-user-graduate me-1"></i> <?php echo htmlspecialchars($memo['etudiant']); ?></span>
                                            <span class="badge bg-indigo-subtle text-indigo px-2 py-0.5 rounded"><?php echo htmlspecialchars($memo['niveau'] ?? 'N/A'); ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Selected Notation Card Form -->
                <div class="col-xl-8 col-lg-12">
                    <div class="section-card">
                        <?php if (!$active_memo): ?>
                            <div class="d-flex flex-column align-items-center justify-content-center text-center h-100 py-5">
                                <i class="fas fa-file-invoice text-light d-block mb-3" style="font-size: 80px;"></i>
                                <h4 class="h5 font-semibold text-slate-800">Fiche de Délibération inactive</h4>
                                <p class="text-muted text-xs max-w-sm">Veuillez sélectionner un examen de mémoire universitaire dans la colonne de gauche pour débuter l'évaluation du jury.</p>
                                <a href="memoire_jury.php" class="btn btn-primary btn-sm px-4 py-2 mt-2" style="border-radius: 8px;">
                                    <i class="fas fa-arrow-left me-2"></i> Consulter l'ensemble du jury
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-wrap align-items-center justify-content-between pb-3 mb-4 border-b border-light gap-2">
                                <h3 class="m-0"><i class="fas fa-scroll text-warning"></i> Formulaire d'Évaluation Officielle</h3>
                                <a href="validation_memoire.php" class="btn btn-xs btn-outline-secondary" style="font-size: 12px; border-radius: 8px;"><i class="fas fa-times me-1"></i> Fermer</a>
                            </div>

                            <!-- Header info about thesis -->
                            <div class="p-4 rounded-4 bg-light border border-slate-200 mb-4 text-xs space-y-2">
                                <div class="badge bg-amber-500 text-white font-bold uppercase tracking-wider text-[9px] mb-2">
                                    Licence / Master d'Ingénierie
                                </div>
                                <h4 class="font-bold text-sm text-slate-900 leading-snug mb-2"><?php echo htmlspecialchars($active_memo['theme']); ?></h4>
                                <div class="row g-2 text-muted">
                                    <div class="col-md-6"><strong>Étudiant :</strong> <?php echo htmlspecialchars($active_memo['prenom_etudiant'] . " " . $active_memo['nom_etudiant']); ?> (<?php echo htmlspecialchars($active_memo['matricule'] ?? 'GASA-' . $active_memo['idetudiant']); ?>)</div>
                                    <div class="col-md-6"><strong>Directeur de thèse :</strong> <?php echo htmlspecialchars($active_memo['maitre_memoire'] ?? 'N/A'); ?></div>
                                    <div class="col-md-6 col-12"><strong>Filière & Niveau :</strong> <?php echo htmlspecialchars($active_memo['nom_filiere'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($active_memo['niveau'] ?? 'N/A'); ?>)</div>
                                    <div class="col-md-6 col-12"><strong>Année Académique :</strong> <?php echo htmlspecialchars($active_memo['annee_academique'] ?? 'N/A'); ?></div>
                                </div>
                            </div>

                            <!-- Notation Form -->
                            <form method="POST" action="validation_memoire.php?id=<?php echo $active_memo['idmemoire']; ?>" class="row g-3">
                                
                                <!-- Mark field -->
                                <div class="col-md-4">
                                    <label class="form-label form-label-custom">Note Finale (0-20) *</label>
                                    <div class="input-group">
                                        <input type="number" required min="0" max="20" step="0.25" name="note" class="form-control form-control-custom font-bold text-slate-900" style="font-family: var(--font-display); font-size: 16px;" value="<?php echo isset($active_memo['note']) ? floatval(str_replace(['Confirmé (', '/20)'], '', $active_memo['note'])) : ''; ?>" placeholder="Ex: 16.5">
                                        <span class="input-group-text bg-white font-semibold text-muted">/ 20</span>
                                    </div>
                                    <p class="text-[10px] text-muted mt-1 leading-snug">Note de soutenance attribuée par délibération collective.</p>
                                </div>

                                <!-- Jury composition -->
                                <div class="col-md-8">
                                    <label class="form-label form-label-custom">Membres du Jury d'Évaluation *</label>
                                    <input type="text" name="membres_jury" class="form-control form-control-custom font-semibold text-slate-800" placeholder="Ex: Dr. Sévérin Kpovié (Président), Dr. Marc GANDONOU (Rapporteur)" value="<?php echo htmlspecialchars($active_memo['jury_membres'] ?? $nom . " " . $prenom); ?>">
                                    <p class="text-[10px] text-muted mt-1 leading-snug">Entrez les noms des examinateurs, séparés par des virgules.</p>
                                </div>

                                <!-- Observations/Critique -->
                                <div class="col-12 mt-2">
                                    <label class="form-label form-label-custom">Observations Générales et Recommandations de Correction *</label>
                                    <textarea required name="appreciations" class="form-control form-textarea-custom font-light" rows="4" placeholder="Saisissez les conclusions formelles... Ex: Excellent travail d'analyse. Méthodologie rigoureuse. Corriger la notation des diagrammes UML en annexe..."><?php echo htmlspecialchars($active_memo['appreciations'] ?? ''); ?></textarea>
                                    <p class="text-[10px] text-muted mt-1 leading-snug">Ces commentaires sont consultables par l'étudiant et archivés de façon permanente par la Direction des Études.</p>
                                </div>

                                <div class="col-12">
                                    <hr class="my-3 text-slate-200">
                                    <div class="notation-alert-banner d-flex gap-2.5 mb-3">
                                        <i class="fas fa-shield-halved fs-5 mt-0.5"></i>
                                        <div>
                                            <strong>Rappel Déontologique GASA-Archive :</strong> En enregistrant cette fiche, vous certifiez sur l'honneur l’évaluation légitime et définitive de ce travail académique. Le statut passera à l'état "Validé", ouvrant la voie à sa publication finale.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 d-flex justify-content-end gap-2.5">
                                    <a href="memoire_jury.php" class="btn btn-light px-4 py-2.5" style="border-radius: 12px; font-weight: 500;">Annuler</a>
                                    <button type="submit" class="btn btn-amber px-5 py-2.5 bg-amber-600 hover:bg-slate-900 text-white font-extrabold text-xs uppercase tracking-wide border-0" style="border-radius: 12px;">
                                        Archiver la note & Valider le mémoire
                                    </button>
                                </div>

                            </form>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <!-- BS Bundle JS CDN to handle alerts close -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('appSidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>
