<?php
session_start();
require_once __DIR__ . '/../../controllers/notificationController.php';
require_once __DIR__ . '/de_helpers.php';

// Vérifier que l'utilisateur est un directeur
if (empty($_SESSION['idde']) && (empty($_SESSION['user']) || $_SESSION['user']['type'] !== 'directeur')) {
    header('Location: ../auth/connexion.php');
    exit;
}

$notificationController = new NotificationController();

$active_page = 'notifications';
require_once __DIR__ . '/../../../config/mysqli_config.php';
$de_profile = get_de_profile($conn);
$nom_de = $de_profile['nom_de'];
$initiales_de = $de_profile['initiales_de'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_read') {
    if ($notificationController->markAllAsRead()) {
        $success = 'Toutes les notifications ont été marquées comme lues.';
    } else {
        $error = 'Impossible de mettre à jour les notifications.';
    }
}

$notifications = $notificationController->getAll();
$total_notifications = count($notifications);
$unread_count = 0;
foreach ($notifications as $n) {
    if ((int)$n['statut_lecture'] === 0) $unread_count++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <?php include __DIR__ . '/../partials/sidebar_DE.php'; ?>

    <main class="workspace">
        <header class="workspace-header">
            <div>
                <span class="overline">Messages reçus</span>
                <h1>Notifications</h1>
                <p>Consultez les alertes envoyées par les étudiants et les professeurs.</p>
            </div>
            <?php if ($unread_count > 0): ?>
                <form method="post" class="header-actions">
                    <input type="hidden" name="action" value="mark_read">
                    <button class="btn-blue" type="submit"><i class="fa-solid fa-check-double"></i> Tout marquer comme lu</button>
                </form>
            <?php endif; ?>
        </header>

        <?php if ($success !== ''): ?>
            <div class="alert success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <section class="cards-container notification-stats">
            <article class="dashboard-card accent-blue">
                <div class="stat-icon"><i class="fa-solid fa-bell"></i></div>
                <span class="card-label">Notifications reçues</span>
                <strong><?= (int) $total_notifications ?></strong>
                <small>Total des messages</small>
            </article>
            <article class="dashboard-card accent-gold">
                <div class="stat-icon"><i class="fa-solid fa-envelope"></i></div>
                <span class="card-label">Non lues</span>
                <strong><?= (int) $unread_count ?></strong>
                <small>À consulter</small>
            </article>
        </section>

        <section class="table-container">
            <div class="table-header">
                <div>
                    <h2>Boîte de notifications</h2>
                    <p>Les dernières notifications apparaissent en premier.</p>
                </div>
                <span class="result-count"><?= (int) $total_notifications ?> notification(s)</span>
            </div>

            <div class="notification-list">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <?php
                            $sender = trim($notification['etudiant'] ?? '');
                            $sender_type = 'Étudiant';
                            if ($sender === '') {
                                $sender = trim($notification['professeur'] ?? '');
                                $sender_type = 'Professeur';
                            }
                            if ($sender === '') {
                                $sender = 'Système';
                                $sender_type = 'Notification';
                            }
                            $is_unread = (int) $notification['statut_lecture'] === 0;
                            $date = $notification['date_notification'] ? date('d/m/Y à H:i', strtotime($notification['date_notification'])) : '';
                        ?>
                        <article class="notification-item <?= $is_unread ? 'unread' : '' ?>">
                            <div class="notification-icon">
                                <i class="fa-solid <?= $is_unread ? 'fa-envelope' : 'fa-envelope-open' ?>"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-meta">
                                    <strong><?= e($sender) ?></strong>
                                    <span><?= e($sender_type) ?></span>
                                    <?php if ($date !== ''): ?>
                                        <time datetime="<?= e($notification['date_notification']) ?>"><?= e($date) ?></time>
                                    <?php endif; ?>
                                </div>
                                <p><?= e($notification['message']) ?></p>
                            </div>
                            <span class="badge"><?= $is_unread ? 'Non lue' : 'Lue' ?></span>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">Aucune notification reçue pour le moment.</div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>
