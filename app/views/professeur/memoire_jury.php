<?php
session_start();

define('SECURE_ACCESS', true);

require_once __DIR__ . '/../../controllers/juryController.php';
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

$prenom = htmlspecialchars($_SESSION['user']['prenom'] ?? '', ENT_QUOTES, 'UTF-8');
$nom = htmlspecialchars($_SESSION['user']['nom'] ?? '', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($_SESSION['user']['email'] ?? '', ENT_QUOTES, 'UTF-8');

// Récupérer les filtres HTTP GET
$filter_niveau = $_GET['niveau'] ?? 'tous';
$filter_statut = $_GET['statut'] ?? 'tous';
$search_query = $_GET['search'] ?? '';

$juryController = new JuryController();
$filters = [
    'niveau' => $filter_niveau,
    'statut' => $filter_statut,
    'search' => $search_query,
];
$memos = $juryController->getFilteredMemoiresForJury($idprof, $email, $filters);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mémoires en Jury - GénieMémoire UATM</title>
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
        }

        /* Filter Controls */
        .search-input-group {
            position: relative;
        }

        .search-input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-input-group .form-control {
            padding-left: 40px;
            border-radius: 12px;
            border-color: #cbd5e1;
        }

        .form-select-custom {
            border-radius: 12px;
            border-color: #cbd5e1;
            font-size: 13.5px;
            height: 42px;
        }

        /* Table */
        .table-responsive {
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .table-custom th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 16px;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-custom td {
            padding: 16px;
            vertical-align: middle;
            font-size: 13px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-custom tbody tr:hover td {
            background-color: #f8fafc;
        }

        .memo-title-link {
            font-weight: 700;
            color: #0f172a;
            text-decoration: none;
            transition: var(--transition-smooth);
        }

        .memo-title-link:hover {
            color: var(--color-gasa-blue);
        }

        /* Grade Badges */
        .grade-badge {
            background-color: rgba(16, 185, 129, 0.1);
            color: #059669;
            font-family: var(--font-display);
            font-weight: 800;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-block;
            border: 1px solid rgba(16, 185, 129, 0.15);
        }

        .grade-missing {
            background-color: #fef3c7;
            color: #b45309;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #fde68a;
        }

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
                <h1>Mémoires en jury assignés</h1>
                <p>Consultez la liste complète des rapports et mémoires dont vous supervisez l'examen final.</p>
            </div>

            <!-- FILTER CONTROLS BAR -->
            <div class="section-card mb-4">
                <form method="GET" class="row g-3 align-items-center">
                    <!-- Search input -->
                    <div class="col-lg-4 col-md-12">
                        <div class="search-input-group">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" class="form-control" placeholder="Rechercher par sujet, étudiant, matricule..." value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                    </div>
                    
                    <!-- Dropdown Niveau -->
                    <div class="col-lg-3 col-md-6 col-6">
                        <select name="niveau" class="form-select form-select-custom" onchange="this.form.submit()">
                            <option value="tous" <?php echo $filter_niveau === 'tous' ? 'selected' : ''; ?>>Tous diplômes (L3 & M2)</option>
                            <option value="L3" <?php echo $filter_niveau === 'L3' ? 'selected' : ''; ?>>Licence 3 (L3)</option>
                            <option value="M2" <?php echo $filter_niveau === 'M2' ? 'selected' : ''; ?>>Master 2 (M2)</option>
                        </select>
                    </div>

                    <!-- Dropdown Statut -->
                    <div class="col-lg-3 col-md-6 col-6">
                        <select name="statut" class="form-select form-select-custom" onchange="this.form.submit()">
                            <option value="tous" <?php echo $filter_statut === 'tous' ? 'selected' : ''; ?>>Tous les statuts</option>
                            <option value="en_attente" <?php echo $filter_statut === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="valide" <?php echo $filter_statut === 'valide' ? 'selected' : ''; ?>>Validé / Noté</option>
                            <option value="publie" <?php echo $filter_statut === 'publie' ? 'selected' : ''; ?>>Publié</option>
                        </select>
                    </div>

                    <!-- Submit / Reset button -->
                    <div class="col-lg-2 col-md-12 d-grid">
                        <a href="memoire_jury.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center" style="height: 42px; border-radius: 12px;">
                            <i class="fas fa-sync-alt me-2"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            <!-- RESULTS LIST -->
            <div class="section-card">
                <?php if (empty($memos)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open text-light d-block mb-3" style="font-size: 72px;"></i>
                        <h4 class="h5 font-semibold text-slate-800">Aucun projet trouvé</h4>
                        <p class="text-muted text-xs max-w-sm mx-auto">Il n'y a aucun dossier universitaire correspondant à vos paramètres de recherche actuels.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom table-hover">
                            <thead>
                                <tr>
                                    <th>Réf / Diplôme</th>
                                    <th>Validation / Titre</th>
                                    <th>Étudiant(e)</th>
                                    <th>Direction / Major</th>
                                    <th>Note académique</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($memos as $memo): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-3 text-xs w-max px-2.5 py-1 text-center font-bold" style="max-width: max-content;">
                                                    <?php echo htmlspecialchars($memo['niveau']); ?>
                                                </span>
                                                <small class="text-muted font-mono" style="font-size: 11px;">#MEM-<?php echo $memo['id']; ?></small>
                                            </div>
                                        </td>
                                        <td style="max-width: 320px;">
                                            <a href="validation_memoire.php?id=<?php echo $memo['id']; ?>" class="memo-title-link line-clamp-2">
                                                <?php echo htmlspecialchars($memo['titre']); ?>
                                            </a>
                                            <p class="text-xs text-muted mb-0 mt-1 truncate" title="<?php echo htmlspecialchars($memo['theme']); ?>">
                                                Thème: <?php echo htmlspecialchars($memo['theme']); ?>
                                            </p>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($memo['etudiant']); ?></span>
                                                <span class="text-xs text-muted font-mono"><?php echo htmlspecialchars($memo['matricule'] ?? 'N/A'); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="font-medium text-slate-700"><?php echo htmlspecialchars($memo['superviseur'] ?? 'Directeur Non Désigné'); ?></span>
                                                <span class="text-xs text-muted"><?php echo htmlspecialchars($memo['filiere']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (isset($memo['note']) && $memo['note'] !== null && $memo['note'] !== ''): ?>
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="grade-badge"><?php echo $memo['note']; ?> / 20</span>
                                                    <small class="text-xs text-muted italic max-w-xs truncate" title="<?php echo htmlspecialchars($memo['appreciations'] ?? ''); ?>">
                                                        "<?php echo htmlspecialchars($memo['appreciations'] ?? 'Très satisfaisant'); ?>"
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                                <span class="grade-missing">Non noté</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex flex-column gap-2" style="max-width: 140px; margin-left: auto;">
                                                <a href="validation_memoire.php?id=<?php echo $memo['id']; ?>" class="btn btn-sm btn-amber text-xs font-semibold d-flex align-items-center justify-content-center gap-1 bg-amber-500 hover:bg-amber-600 text-white rounded-3 shadow-sm py-2">
                                                    <i class="fas fa-edit"></i> Noter / Évaluer
                                                </a>
                                                <button class="btn btn-sm btn-outline-secondary text-[11px] font-semibold py-1.5 rounded-3 d-flex align-items-center justify-content-center gap-1" onclick="alert('Accès sécurisé activé. Vous pouvez consulter le fichier PDF original dans le panneau principal GASA-Shield de l\'application.')">
                                                    <i class="fas fa-lock"></i> Lire PDF
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('appSidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>