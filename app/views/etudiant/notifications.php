<?php
session_start();
require_once __DIR__ . '/../../controllers/notificationController.php';
require_once __DIR__ . '/../../controllers/etudiantController.php';

if (empty($_SESSION['idetudiant'])) {
    header('Location: ../auth/connexion.php');
    exit;
}

$idetudiant = (int) $_SESSION['idetudiant'];

$notificationController = new NotificationController();
$etudiantController = new EtudiantController();

// Récupérer les informations de l'étudiant
$student = $etudiantController->getProfile($idetudiant);

// Gérer le marquage comme lu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_read') {
    $notificationController->markAllAsReadForEtudiant($idetudiant);
}

// Récupérer les notifications via le contrôleur
$notifications = $notificationController->getEtudiantNotifications($idetudiant);

$unread_count = 0;
foreach ($notifications as $n) {
    if ((int)$n['statut_lecture'] === 0) {
        $unread_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Notifications Étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../direction_etude/style.css">
</head>
<body>
<main class="student-shell">
    <header class="workspace-header">
        <div>
            <span class="overline">Espace étudiant</span>
            <h1>Mes Notifications</h1>
            <p>Retrouvez ici les messages concernant l'état de vos dépôts et les annonces académiques.</p>
        </div>
        <div class="header-actions">
            <?php if ($unread_count > 0): ?>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="mark_read">
                    <button class="btn-blue" type="submit"><i class="fa-solid fa-check-double"></i> Tout marquer comme lu</button>
                </form>
            <?php endif; ?>
            <a class="btn-muted" href="dashboard_etudiant.php"><i class="fa-solid fa-arrow-left"></i> Retour au tableau de bord</a>
        </div>
    </header>

    <section class="table-container" style="margin-top: 20px;">
        <div class="table-header">
            <div>
                <h2>Liste des notifications</h2>
                <p>Vos alertes les plus récentes</p>
            </div>
            <span class="result-count"><?= count($notifications) ?> notification(s)</span>
        </div>

        <div class="notification-list" style="padding: 20px; display: flex; flex-direction: column; gap: 15px;">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notif): ?>
                    <?php 
                        $is_unread = (int)$notif['statut_lecture'] === 0;
                        $date = date('d/m/Y à H:i', strtotime($notif['date_notification']));
                    ?>
                    <article class="notification-item <?= $is_unread ? 'unread' : '' ?>" style="display: flex; align-items: flex-start; gap: 15px; padding: 15px; border: 1px solid #eee; border-radius: 8px; background: #fff; <?= $is_unread ? 'border-left: 4px solid #d97706;' : '' ?>">
                        <div class="notification-icon" style="font-size: 20px; color: <?= $is_unread ? '#d97706' : '#94a3b8' ?>;">
                            <i class="fa-solid <?= $is_unread ? 'fa-envelope' : 'fa-envelope-open' ?>"></i>
                        </div>
                        <div class="notification-content" style="flex: 1;">
                            <div class="notification-meta" style="margin-bottom: 5px; font-size: 12px; color: #64748b;">
                                <time><?= $date ?></time>
                            </div>
                            <p style="margin: 0; font-size: 14px; color: #334155;"><?= htmlspecialchars($notif['message']) ?></p>
                        </div>
                        <?php if ($is_unread): ?>
                            <span class="badge" style="background: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Nouveau</span>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state" style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fa-solid fa-bell-slash" style="font-size: 40px; margin-bottom: 10px;"></i>
                    <p>Aucune notification pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
