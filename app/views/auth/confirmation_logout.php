<?php
// Initialisation de la session pour récupérer les informations de l'utilisateur
session_start();

// Simulation de données de session si elles n'existent pas (pour le test visuel)
if (!isset($_SESSION['nom'])) {
    $_SESSION['nom'] = "Kokou";
    $_SESSION['prenom'] = "Jean";
    $_SESSION['email'] = "jean.kokou@universite.bj";
}

// Extraction des données de session
$nom_complet = htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']);
$email = htmlspecialchars($_SESSION['email']);

// Génération des initiales (Ex: Jean Kokou -> JK)
$initiales = strtoupper(substr($_SESSION['prenom'], 0, 1) . substr($_SESSION['nom'], 0, 1));

// Traitement de l'action de déconnexion effective
if (isset($_POST['action']) && $_POST['action'] === 'confirm_logout') {
    // Nettoyage complet de la session
    $_SESSION = [];

    // Destruction du cookie de session si activé
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destruction de la session
    session_destroy();

    // Redirection vers la page de connexion
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion - MémoiresUniv</title>
    <style>
        /* --- Variables globales et Reset --- */
        :root {
            --bg-main: #f5f7fa;
            --white: #ffffff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --primary-red: #ef4444;
            --primary-red-hover: #dc2626;
            --bg-user-block: #f8fafc;
            --border-color: #e2e8f0;
            --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-main);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- Barre supérieure (Header) --- */
        .navbar {
            background-color: var(--white);
            height: 70px;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .logo {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
        }

        .logo span {
            color: #2563eb; /* Couleur d'accent optionnelle pour "Univ" */
        }

        .user-nav-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-nav-name {
            font-size: 15px;
            font-weight: 500;
            color: var(--text-muted);
        }

        /* --- Conteneur Principal --- */
        .main-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 100px 20px 40px 20px; /* Top padding pour éviter la navbar fixe */
        }

        /* --- Carte Centrale --- */
        .logout-card {
            background-color: var(--white);
            width: 100%;
            max-width: 520px;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        /* Cercle de l'icône */
        .icon-container {
            width: 64px;
            height: 64px;
            background-color: #fee2e2; /* Rose / rouge très clair */
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 24px auto;
        }

        /* Icône Porte minimaliste en SVG */
        .icon-door {
            width: 28px;
            height: 28px;
            fill: #ef4444;
        }

        .card-title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }

        .card-description {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 28px;
            padding: 0 10px;
        }

        /* --- Bloc Utilisateur (Détails) --- */
        .user-details-box {
            background-color: var(--bg-user-block);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            text-align: left;
            margin-bottom: 28px;
        }

        /* Avatar Global (Header et Carte) */
        .avatar {
            width: 44px;
            height: 44px;
            background-color: #e0f2fe; /* Bleu clair */
            color: #0284c7; /* Bleu foncé */
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .user-name {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
        }

        .user-email {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* --- Boutons d'Action --- */
        .actions-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn {
            width: 100%;
            padding: 14px 20px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }

        .btn-danger {
            background-color: var(--primary-red);
            color: var(--white);
            border: none;
        }

        .btn-danger:hover {
            background-color: var(--primary-red-hover);
        }

        .btn-secondary {
            background-color: var(--white);
            color: #475569;
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: #f8fafc;
            color: #1e293b;
        }

        /* --- Responsive Design --- */
        @media (max-width: 600px) {
            .navbar {
                padding: 0 20px;
            }
            .user-nav-name {
                display: none; /* Masque le nom dans la navbar sur mobile pour gagner de la place */
            }
            .logout-card {
                padding: 30px 20px;
                box-shadow: none;
                background-color: transparent;
            }
            .main-container {
                padding-top: 80px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="logo">Mémoires<span>Univ</span></div>
        <div class="user-nav-profile">
            <span class="user-nav-name"><?php echo $nom_complet; ?></span>
            <div class="avatar"><?php echo $initiales; ?></div>
        </div>
    </header>

    <main class="main-container">
        <div class="logout-card">
            
            <div class="icon-container">
                <svg class="icon-door" viewBox="0 0 24 24">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v3l-5-4 5-4v3h4v2z"/>
                </svg>
            </div>

            <h1 class="card-title">Se déconnecter ?</h1>
            <p class="card-description">
                Vous êtes sur le point de vous déconnecter de votre compte. Vous devrez vous reconnecter pour accéder à la plateforme.
            </p>

            <div class="user-details-box">
                <div class="avatar"><?php echo $initiales; ?></div>
                <div class="user-info">
                    <span class="user-name"><?php echo $nom_complet; ?></span>
                    <span class="user-email"><?php echo $email; ?></span>
                </div>
            </div>

            <form method="POST" action="" class="actions-form">
                <button type="submit" name="action" value="confirm_logout" class="btn btn-danger">
                    Oui, me déconnecter
                </button>
                
                <a href="dashboard.php" class="btn btn-secondary">
                    Annuler — Rester connecté
                </a>
            </form>

        </div>
    </main>

</body>
</html>