<?php
session_start();

define('SECURE_ACCESS', true);

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

$professeurController = new ProfesseurController();
$data = $professeurController->getDashboardData($idprof);

if (!$data) {
    header('Location: ../auth/connexion.php');
    exit;
}

$professor = $data['professor'];
$stats = $data['stats'];
$recent_memos = $data['recent_memos'];

$prenom = htmlspecialchars($professor['prenom'] ?? '', ENT_QUOTES, 'UTF-8');
$nom = htmlspecialchars($professor['nom'] ?? '', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($professor['email'] ?? '', ENT_QUOTES, 'UTF-8');
$specialty = 'Enseignant-Chercheur';
$department = 'DST (Sciences et Technologies)';

$nb_evaluations = $stats['nb_evaluations'];
$nb_a_valider = $stats['nb_a_valider'];
$nb_valides = $stats['nb_valides'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Enseignant - GénieMémoire UATM</title>
    <!-- Bootstrap 5.3 + FontAwesome 6.4.0 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter -->
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
            overflow-x: hidden;
        }

        .page-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR PREMIUM STYLE */
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

        .logo-subtitle {
            font-size: 11px;
            color: #93c5fd;
            margin: 2px 0 0;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 600;
        }

        /* Glassmorphism Profile Sidebar */
        .profile-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius-lg);
            padding: 16px;
            margin-bottom: 8px;
        }

        .profile-card h3 {
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 4px 0;
            line-height: 1.3;
        }

        .profile-card p {
            color: #b4c6fc;
            font-size: 12px;
            margin: 0;
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

        .nav-menu li a i {
            font-size: 18px;
            width: 24px;
            display: flex;
            justify-content: center;
        }

        .nav-menu li a:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .nav-menu li a.active {
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            color: #0f172a;
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(217, 119, 6, 0.2);
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
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        /* MAIN CONTENT AREA */
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
            letter-spacing: -0.02em;
            margin: 0;
        }

        .main-header p {
            color: #64748b;
            margin: 4px 0 0;
            font-size: 14px;
        }

        /* BENTO STATS GRID */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 36px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: var(--border-radius-lg);
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: var(--box-shadow-soft);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08); /* Modern, soft elevation */
            border-color: #cbd5e1;
        }

        .stat-card .stat-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-card small {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.07em;
            color: #64748b;
        }

        .stat-card h2 {
            margin: 4px 0 0 0;
            font-size: 36px;
            font-weight: 800;
            color: var(--color-gasa-dark);
            font-family: var(--font-display);
        }

        .stat-card .stat-label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            color: #64748b;
            font-size: 13px;
        }

        .stat-card .stat-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .icon-total {
            background: rgba(30, 58, 138, 0.08);
            color: var(--color-gasa-blue);
        }

        .icon-pending {
            background: rgba(217, 119, 6, 0.08);
            color: var(--color-gasa-gold);
        }

        .icon-validated {
            background: rgba(16, 185, 129, 0.08);
            color: #10b981;
        }

        /* SECTION CARD BLOCK */
        .section-card {
            background: #ffffff;
            border-radius: var(--border-radius-xl);
            padding: 30px;
            border: 1px solid #e2e8f0;
            box-shadow: var(--box-shadow-soft);
            margin-bottom: 30px;
        }

        .section-card h3 {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 20px;
            color: var(--color-gasa-dark);
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Table design */
        .table-responsive {
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .table-custom {
            margin-bottom: 0;
            font-size: 13px;
        }

        .table-custom th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            padding: 14px 16px;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-custom td {
            padding: 14px 16px;
            vertical-align: middle;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-custom tbody tr:last-child td {
            border-bottom: none;
        }

        .table-custom tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Badges */
        .badge-status {
            font-size: 11px;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-status-en-attente {
            background-color: #fef3c7;
            color: #d97706;
        }

        .badge-status-valide {
            background-color: #d1fae5;
            color: #059669;
        }

        .badge-status-publie, .badge-status-publié {
            background-color: #dbeafe;
            color: #2563eb;
        }

        /* Mobile Hamburger Hamburger (Responsive) */
        .mobile-header {
            display: none;
            background-color: var(--color-gasa-dark);
            color: white;
            padding: 16px 20px;
            align-items: center;
            justify-content: space-between;
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main {
                margin-left: 0;
                padding: 24px;
            }
            .mobile-header {
                display: flex;
            }
            .cards-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }
    </style>
</head>
<body>

    <!-- Mobile Header for responsive views -->
    <div class="mobile-header d-lg-none" id="mobileHeader">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="logo-icon">M</span>
            <span class="logo-title">GénieMémoire</span>
        </div>
        <button class="btn text-white fs-4 p-0 border-0" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    </div>

    <div class="page-wrapper">
        
        <?php include __DIR__ . '/../partials/sidebar_PROF.php'; ?>

        <!-- MAIN AREA -->
        <main class="main">
            
            <!-- Main Title Header -->
            <div class="main-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h1>Tableau de bord</h1>
                    <p>Vue d'ensemble et pilotage pédagogique d'évaluation · <?php echo date('F Y'); ?></p>
                </div>
                <div class="bg-white px-3 py-2 border border-slate-200 rounded-pill shadow-xs text-xs font-semibold d-flex align-items-center gap-2">
                    <span class="bg-success rounded-circle" style="width: 8px; height: 8px; display: inline-block;"></span>
                    <span>Direct académique connecté</span>
                </div>
            </div>

            <!-- BENTO STATS CARDS -->
            <div class="cards-grid">
                <!-- Card 1: Total -->
                <div class="stat-card">
                    <div class="stat-info">
                        <small>Évaluations affectées</small>
                        <h2><?php echo $nb_evaluations; ?></h2>
                        <div class="stat-label">
                            <i class="fas fa-book-open text-primary"></i>
                            <span>Mémoires au total</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper icon-total">
                        <i class="fas fa-file-signature"></i>
                    </div>
                </div>

                <!-- Card 2: Pending -->
                <div class="stat-card">
                    <div class="stat-info">
                        <small>Attente d'évaluation</small>
                        <h2><?php echo $nb_a_valider; ?></h2>
                        <div class="stat-label">
                            <i class="fas fa-clock text-warning animate-pulse"></i>
                            <span>Avis de jury requis</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper icon-pending">
                        <i class="fas fa-spinner fa-spin-hover"></i>
                    </div>
                </div>

                <!-- Card 3: Approved -->
                <div class="stat-card">
                    <div class="stat-info">
                        <small>Approuvés par vous</small>
                        <h2><?php echo $nb_valides; ?></h2>
                        <div class="stat-label">
                            <i class="fas fa-check-double text-success"></i>
                            <span>Soutenances archivées</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper icon-validated">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- LEFT COLUMN: Profile Info DetailsCard -->
                <div class="col-xl-5 col-lg-12 mb-4">
                    <div class="section-card h-100">
                        <h3><i class="fas fa-id-card text-muted"></i> Profil Enseignant</h3>
                        
                        <div class="text-center py-3 mb-4 border-bottom border-light">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 72px; height: 72px; font-size: 28px; font-weight: 700; background: linear-gradient(135deg, var(--color-gasa-blue) 0%, #3b82f6 100%) !important;">
                                <?php echo substr($prenom, 0, 1) . substr($nom, 0, 1); ?>
                            </div>
                            <h4 class="h5 mb-1 text-slate-900"><?php echo $prenom . ' ' . $nom; ?></h4>
                            <span class="badge bg-secondary-subtle text-secondary px-3 py-1.5 rounded-pill text-xs font-semibold uppercase">
                                <?php echo $specialty; ?>
                            </span>
                        </div>

                        <ul class="list-group list-group-flush" style="font-size: 13.5px;">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent">
                                <span class="text-muted">Prénom</span>
                                <strong class="text-slate-900"><?php echo $prenom; ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent">
                                <span class="text-muted">Nom</span>
                                <strong class="text-slate-900"><?php echo $nom; ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent">
                                <span class="text-muted">Messagerie</span>
                                <strong class="text-slate-800 font-mono" style="font-size: 12px;"><?php echo $email; ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 bg-transparent border-0">
                                <span class="text-muted">Département académique</span>
                                <strong class="text-slate-700 text-end text-[12px]"><?php echo $department; ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Active / assigned memoirs list -->
                <div class="col-xl-7 col-lg-12 mb-4">
                    <div class="section-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h3><i class="fas fa-folder-open text-muted"></i> Travaux Récents</h3>
                            <a href="memoire_jury.php" class="btn btn-sm btn-outline-primary" style="font-size: 12px; font-weight: 600; border-radius: 8px;">
                                Voir tout <i class="fas fa-arrow-right list-arrow ms-1"></i>
                            </a>
                        </div>

                        <?php if (empty($recent_memos)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-archive text-light d-block mb-3" style="font-size: 64px;"></i>
                                <p class="text-muted mb-0">Aucun mémoire de jury assigné à ce jour.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-custom table-hover">
                                    <thead>
                                        <tr>
                                            <th>Sujet / Titre de Thèse</th>
                                            <th>Étudiant</th>
                                            <th>Filière</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_memos as $memo): ?>
                                            <tr>
                                                <td class="font-semibold text-slate-800" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <a href="memoire_jury.php" title="<?php echo htmlspecialchars($memo['titre']); ?>" class="text-decoration-none text-dark hover:text-primary">
                                                        <?php echo htmlspecialchars($memo['titre']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="font-medium text-slate-700"><?php echo htmlspecialchars($memo['etudiant'] ?? 'Non renseigné'); ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-xs font-semibold badge bg-light text-slate-650 rounded"><?php echo htmlspecialchars($memo['filiere']); ?></span>
                                                </td>
                                                <td>
                                                    <?php $st = strtolower($memo['statut'] ?? ''); ?>
                                                    <span class="badge-status badge-status-<?php echo $st == 'publié' ? 'publie' : $st; ?>">
                                                        <span class="rounded-circle" style="width: 6px; height: 6px; background-color: currentColor; display: inline-block;"></span>
                                                        <?php echo htmlspecialchars(ucfirst($memo['statut'])); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- JS for Mobile layout interaction -->
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('appSidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>
