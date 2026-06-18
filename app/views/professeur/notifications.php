<?php
session_start();

define('SECURE_ACCESS', true);

require_once __DIR__ . '/../../controllers/notificationController.php';
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

$notificationController = new NotificationController();
$professeurController = new ProfesseurController();

// Récupérer les informations du professeur
$professor = $professeurController->getProfile($idprof);

if (!$professor) {
    header('Location: ../auth/connexion.php');
    exit;
}

$prenom = htmlspecialchars($professor['prenom'] ?? '', ENT_QUOTES, 'UTF-8');
$nom = htmlspecialchars($professor['nom'] ?? '', ENT_QUOTES, 'UTF-8');

// Gérer le marquage comme lu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_read') {
    $notificationController->markAllAsReadForProfesseur($idprof);
}

// Charger les alertes via le contrôleur
$notifications = $notificationController->getProfesseurNotifications($idprof);

$unread_count = 0;
foreach ($notifications as $n) {
    if (!$n['read']) {
        $unread_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertes Académiques - GénieMémoire UATM</title>
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
        }

        /* Notification list feed */
        .notif-feed {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .notif-item {
            display: flex;
            gap: 18px;
            padding: 20px;
            border-radius: var(--border-radius-lg);
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            transition: var(--transition-smooth);
            position: relative;
        }

        .notif-item:hover {
            border-color: #cbd5e1;
            transform: translateX(2px);
        }

        .notif-item.notif-unread {
            background-color: rgba(217, 119, 6, 0.015);
            border-left: 4px solid var(--color-gasa-gold);
        }

        .notif-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            shrink: 0;
        }

        .icon-deposit { background-color: rgba(30, 58, 138, 0.08); color: var(--color-gasa-blue); }
        .icon-jury { background-color: rgba(217, 119, 6, 0.08); color: var(--color-gasa-gold); }
        .icon-publication { background-color: rgba(16, 185, 129, 0.08); color: #10b981; }
        .icon-system { background-color: rgba(100, 116, 139, 0.08); color: #64748b; }

        .notif-item h4 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 6px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notif-item p {
            font-size: 13.5px;
            color: #475569;
            line-height: 1.5;
            margin: 0 0 10px 0;
        }

        .notif-item .notif-meta {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }

        .notif-badge-unread {
            background-color: #fbbf24;
            color: #78350f;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 3px 6px;
            border-radius: 6px;
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
            
            <div class="main-header mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h1>Registre des Alertes Académiques</h1>
                    <p>Liste des dépôts d'étudiants, publications et communiqués administratifs récents.</p>
                </div>
                <?php if ($unread_count > 0): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="mark_read">
                        <button class="btn btn-sm btn-outline-secondary" type="submit" style="border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-check-double me-1"></i> Tout marquer comme lu
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="section-card">
                <div class="notif-feed">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $notif): ?>
                            <div class="notif-item <?php echo !$notif['read'] ? 'notif-unread' : ''; ?>">
                                <div class="notif-icon-box icon-<?php echo $notif['type']; ?>">
                                    <?php if ($notif['type'] === 'deposit'): ?>
                                        <i class="fas fa-file-upload"></i>
                                    <?php elseif ($notif['type'] === 'jury'): ?>
                                        <i class="fas fa-award"></i>
                                    <?php elseif ($notif['type'] === 'publication'): ?>
                                        <i class="fas fa-globe"></i>
                                    <?php else: ?>
                                        <i class="fas fa-info-circle"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex-1">
                                    <h4>
                                        <span><?php echo htmlspecialchars($notif['title']); ?></span>
                                        <?php if (!$notif['read']): ?>
                                            <span class="notif-badge-unread">Nouveau</span>
                                        <?php endif; ?>
                                    </h4>
                                    <p><?php echo $notif['message']; ?></p>
                                    <div class="d-flex align-items-center justify-content-between text-xs text-muted">
                                        <span class="notif-meta"><i class="far fa-clock me-1"></i> <?php echo htmlspecialchars($notif['time']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune notification pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
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
