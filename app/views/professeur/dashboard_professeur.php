<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['type'] ?? '') !== 'professeur') {
    header('Location: ../auth/connexion.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Professeur</title>
    <link rel="stylesheet" href="../direction_etude/style.css">
    <style>
        body { background: #f4f6f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .dashboard-container { max-width: 1024px; margin: 40px auto; padding: 24px; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .dashboard-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 24px; }
        .dashboard-header h1 { font-size: 28px; margin: 0; }
        .dashboard-header a { text-decoration: none; color: #fff; background: #007bff; padding: 10px 16px; border-radius: 8px; }
        .dashboard-welcome { margin-bottom: 20px; }
        .dashboard-card { background: #f8fbff; border: 1px solid #dce7f2; border-radius: 14px; padding: 18px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div>
                <h1>Bienvenue, Professeur</h1>
                <p class="dashboard-welcome">Vous êtes connecté avec l’adresse <strong><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></strong>.</p>
            </div>
            <a href="../../public/logout.php">Déconnexion</a>
        </div>

        <div class="dashboard-card">
            <h2>Page provisoire</h2>
            <p>Cette page sert de tableau de bord professeur. Vous pouvez la compléter ultérieurement avec vos fonctions de validation, notifications et observations.</p>
        </div>

        <div class="dashboard-card">
            <p>Si vous souhaitez accéder à votre espace professeur, utilisez les menus ou demandez la mise à jour du dashboard.</p>
        </div>
    </div>
</body>
</html>
