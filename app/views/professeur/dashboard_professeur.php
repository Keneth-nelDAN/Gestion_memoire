<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';

// Vérifier si l'utilisateur est connecté et est un professeur
if (empty($_SESSION['idprof']) && (empty($_SESSION['user']) || $_SESSION['user']['type'] !== 'professeur')) {
    header('Location: ../auth/connexion.php');
    exit;
}

$idprof = $_SESSION['idprof'] ?? $_SESSION['user']['id'] ?? null;
if (!$idprof) {
    header('Location: ../auth/connexion.php');
    exit;
}

// Récupérer les informations du professeur
$stmt = mysqli_prepare($conn, "SELECT idprof, nom, prenom, email FROM professeur WHERE idprof = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $idprof);
mysqli_stmt_execute($stmt);
$professor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$professor) {
    header('Location: ../auth/connexion.php');
    exit;
}

$prenom = htmlspecialchars($professor['prenom'] ?? '', ENT_QUOTES, 'UTF-8');
$nom = htmlspecialchars($professor['nom'] ?? '', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($professor['email'] ?? '', ENT_QUOTES, 'UTF-8');

// Récupérer les statistiques
$nb_evaluations = (int) (mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ancien_memoire WHERE examinateur = '$email' OR maitre_memoire = '$email' OR president_jury = '$email'"))[0] ?? 0);
$nb_a_valider = (int) (mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ancien_memoire WHERE statut = 'en_attente' AND (examinateur = '$email' OR maitre_memoire = '$email' OR president_jury = '$email')"))[0] ?? 0);
$nb_valides = (int) (mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM ancien_memoire WHERE statut IN ('valide','publié','publié') AND (examinateur = '$email' OR maitre_memoire = '$email' OR president_jury = '$email')"))[0] ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Professeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .page-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #2c5aa0 0%, #1a3a52 100%);
            color: white;
            padding: 32px 24px;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .logo-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background-color: #d4af37;
            color: #1a3a52;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
        }
        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            color: #d7e5f7;
            text-decoration: none;
            border-radius: 16px;
            transition: all 0.2s;
        }
        .nav-menu a.active,
        .nav-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .main {
            flex: 1;
            padding: 32px;
        }
        .main-header h1 {
            font-size: 32px;
            margin: 0 0 8px 0;
            color: #1a2d40;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(26, 58, 82, 0.08);
            border: 1px solid rgba(30, 72, 124, 0.06);
        }
        .stat-card small {
            display: block;
            color: #758299;
            margin-bottom: 14px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 12px;
        }
        .stat-card h2 {
            margin: 0;
            font-size: 36px;
            color: #10273f;
        }
        .stat-label {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            color: #5b7a9d;
            font-size: 14px;
            align-items: center;
        }
        .logout-button {
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.1);
            color: #f8f9fb;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s;
        }
        .logout-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <aside class="sidebar">
            <div style="display: flex; align-items: center; gap: 14px;">
                <span class="logo-icon">M</span>
                <div>
                    <p style="margin: 0; font-size: 16px; font-weight: 700;">GénieMémoire</p>
                    <p style="margin: 4px 0 0; font-size: 13px; color: #b8d3ff;">Espace Professeur</p>
                </div>
            </div>

            <div style="padding: 18px; background: rgba(255, 255, 255, 0.08); border-radius: 20px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 700;"><?php echo htmlspecialchars($prenom . ' ' . $nom); ?></h3>
                <p style="margin: 6px 0 0; color: #b2c7dc; font-size: 14px;">Professeur</p>
            </div>

            <ul class="nav-menu">
                <li><a class="active" href="/Gestion_memoire/app/views/professeur/dashboard_professeur.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                <li><a href="/Gestion_memoire/app/views/professeur/memoire_jury.php"><i class="fas fa-book-open"></i> Mémoires en jury</a></li>
                <li><a href="/Gestion_memoire/app/views/professeur/validation_memoire.php"><i class="fas fa-check-circle"></i> Validations</a></li>
                <li><a href="/Gestion_memoire/app/views/professeur/notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
            </ul>

            <a href="/Gestion_memoire/public/logout.php" class="logout-button"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </aside>

        <main class="main">
            <div class="main-header" style="margin-bottom: 28px;">
                <div>
                    <h1>Tableau de bord</h1>
                    <p style="color: #66758d; margin: 6px 0 0;">Vue d'ensemble · <?php echo date('F Y'); ?></p>
                </div>
            </div>

            <div class="cards-grid">
                <div class="stat-card">
                    <small>Évaluations totales</small>
                    <h2><?php echo $nb_evaluations; ?></h2>
                    <div class="stat-label"><i class="fas fa-book-open"></i> Mémoires assignés</div>
                </div>
                <div class="stat-card">
                    <small>En attente de validation</small>
                    <h2><?php echo $nb_a_valider; ?></h2>
                    <div class="stat-label"><i class="fas fa-clock"></i> À examiner</div>
                </div>
                <div class="stat-card">
                    <small>Validés par vous</small>
                    <h2><?php echo $nb_valides; ?></h2>
                    <div class="stat-label"><i class="fas fa-check"></i> Approuvés</div>
                </div>
            </div>

            <div style="background: white; border-radius: 24px; padding: 28px; box-shadow: 0 4px 16px rgba(26, 58, 82, 0.08);">
                <h3 style="margin-top: 0; color: #10273f; font-size: 24px;">Informations de profil</h3>
                <table style="width: 100%; margin-top: 20px;">
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px solid #e9edf3; color: #6c7c9a;">Prénom</td>
                        <td style="padding: 12px 0; border-bottom: 1px solid #e9edf3; color: #1a2d40;"><?php echo $prenom; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px solid #e9edf3; color: #6c7c9a;">Nom</td>
                        <td style="padding: 12px 0; border-bottom: 1px solid #e9edf3; color: #1a2d40;"><?php echo $nom; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; color: #6c7c9a;">Email</td>
                        <td style="padding: 12px 0; color: #1a2d40;"><?php echo $email; ?></td>
                    </tr>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
