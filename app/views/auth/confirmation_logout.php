<?php
// Initialisation de la session pour récupérer les informations réelles de l'utilisateur
session_start();

// 1. Validation de l'authentification : si aucun utilisateur n'est connecté, redirection immédiate
if (
    empty($_SESSION['idetudiant']) && 
    empty($_SESSION['idde']) && 
    empty($_SESSION['id_user']) && 
    empty($_SESSION['idprofesseur'])
) {
    header("Location: /Gestion_memoire/public/login.php");
    exit;
}

// 2. Extraction dynamique et ultra-robuste des données de l'utilisateur connecté
$nom = $_SESSION['nom'] ?? '';
$prenom = $_SESSION['prenom'] ?? '';
$email = $_SESSION['email'] ?? '';

// Fallback pour le Directeur des Études au cas où les clés de session diffèrent
if (empty($nom) && !empty($_SESSION['nom_de'])) {
    $nom = $_SESSION['nom_de'];
}
if (empty($prenom) && !empty($_SESSION['prenom_de'])) {
    $prenom = $_SESSION['prenom_de'];
}

// S'il n'y a pas d'adresse e-mail dans la session, utiliser un fallback neutre
if (empty($email)) {
    $email = "utilisateur@uatm-gasa.bj";
}

$nom_complet = trim($prenom . ' ' . $nom);
if (empty($nom_complet)) {
    $nom_complet = "$prenom$nom";
}

$nom_complet_html = htmlspecialchars($nom_complet, ENT_QUOTES, 'UTF-8');
$email_html = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

// Génération intelligente des initiales (Ex: Jean Kokou -> JK)
$initiales = "";
if (!empty($prenom)) {
    $initiales .= strtoupper(substr($prenom, 0, 1));
}
if (!empty($nom)) {
    $initiales .= strtoupper(substr($nom, 0, 1));
}
if (empty($initiales)) {
    $initiales = "U";
}

// 3. Détermination du tableau de bord de retour pour le bouton "Annuler"
$cancelUrl = "/Gestion_memoire/public/login.php"; // Valeur par défaut de secours

if (isset($_SESSION['idetudiant']) || (isset($_SESSION['userType']) && $_SESSION['userType'] === 'etudiant')) {
    $cancelUrl = '/Gestion_memoire/app/views/etudiant/dashboard_etudiant.php';
} elseif (isset($_SESSION['idde']) || (isset($_SESSION['userType']) && $_SESSION['userType'] === 'directeur')) {
    $cancelUrl = '/Gestion_memoire/app/views/direction_etude/dashboard_de.php';
} elseif (isset($_SESSION['id_user']) || isset($_SESSION['idprofesseur']) || (isset($_SESSION['userType']) && $_SESSION['userType'] === 'professeur')) {
    $cancelUrl = '/Gestion_memoire/app/views/professeur/dashboard_professeur.php';
} elseif (!empty($_SERVER['HTTP_REFERER'])) {
    // Si on a l'URL d'origine dans le Referer, on l'utilise en fallback
    $cancelUrl = $_SERVER['HTTP_REFERER'];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion - GénieMémoire</title>
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
            height: 90px;
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
            color: #2563eb;
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
            padding: 100px 20px 40px 20px;
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

        .icon-container {
            width: 64px;
            height: 64px;
            background-color: #fee2e2;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 24px auto;
        }

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

        /* --- Bloc Utilisateur --- */
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

        .avatar {
            width: 44px;
            height: 44px;
            background-color: #e0f2fe;
            color: #0284c7;
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

        .brand-icon {
            width: 50px;
            height: 50px;
            background-color: #d4af37;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 50px auto auto auto;
            padding: 4px 0 0 8px;
            font-weight: bold;
            color: #1a3a52;
        }

        .brand {
            text-align: center;
            margin-top: 8px;
        }

        .brand-icon,.brand {
            color: #1a3a52;
            display: inline-block;
            margin: 0;
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

        @media (max-width: 600px) {
            .navbar {
                padding: 0 20px;
            }
            .user-nav-name {
                display: none;
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
        <div class="brand_icon">
                <div class="brand-icon">M</div>
                <div class="brand">
                    <h1>GénieMémoire</h1>
                    <p>Plateforme universitaire</p>
                </div>
            </div>
        <div class="user-nav-profile">
            <span class="user-nav-name"><?= $nom_complet_html; ?></span>
            <div class="avatar"><?= htmlspecialchars($initiales, ENT_QUOTES, 'UTF-8'); ?></div>
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
                Vous êtes sur le point de vous déconnecter de votre compte. Vous devrez à nouveau vous authentifier pour accéder à GénieMémoire.
            </p>

            <div class="user-details-box">
                <div class="avatar"><?= htmlspecialchars($initiales, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="user-info">
                    <span class="user-name"><?= $nom_complet_html; ?></span>
                    <span class="user-email"><?= $email_html; ?></span>
                </div>
            </div>

            <form method="POST" action="/Gestion_memoire/public/logout" class="actions-form">
                <button type="submit" class="btn btn-danger">
                    Oui, me déconnecter
                    <i class="fa-solid fa-sign-out-alt"></i>
                </button>
                
                <a href="<?= htmlspecialchars($cancelUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">
                    Annuler — Rester connecté
                </a>
            </form>

        </div>
    </main>

</body>
</html>