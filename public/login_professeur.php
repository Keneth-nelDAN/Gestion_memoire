<?php
session_start();

// Correction du chemin d’inclusion (utilisation de __DIR__)
require_once __DIR__ . '/../config/database.php';

// Si déjà connecté, rediriger vers l'espace professeur (route MVC)
if (isset($_SESSION['idprof']) && isset($_SESSION['role']) && $_SESSION['role'] === 'professeur') {
    header('Location: index.php?controller=professeur&action=index');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['motdepasse'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Requête préparée sécurisée
        $stmt = $pdo->prepare("SELECT idprof, nom, prenom, email, motdepasse FROM professeur WHERE email = ?");
        $stmt->execute([$email]);
        $prof = $stmt->fetch(PDO::FETCH_ASSOC);

        // Comparaison (mot de passe en clair – à hasher plus tard)
        if ($prof && $password === $prof['motdepasse']) {
            // Régénérer l’ID de session pour éviter la fixation
            session_regenerate_id(true);

            $_SESSION['idprof'] = $prof['idprof'];
            $_SESSION['role'] = 'professeur';
            $_SESSION['nom'] = $prof['nom'];
            $_SESSION['prenom'] = $prof['prenom'];
            $_SESSION['email'] = $prof['email'];

            // Redirection vers le tableau de bord professeur (route MVC)
            header('Location: index.php?controller=professeur&action=index');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Professeur - Gestion des Mémoires</title>
    <style>
        /* Vos styles (inchangés) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-header {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
        }
        .login-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .login-body {
            padding: 35px 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: border 0.2s;
        }
        input:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42,82,152,0.1);
        }
        button {
            width: 100%;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border: none;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .footer-links {
            margin-top: 20px;
            text-align: center;
            font-size: 0.85rem;
        }
        .footer-links a {
            color: #2a5298;
            text-decoration: none;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <h1>📚 Espace Professeur</h1>
        <p>Gestion et validation des mémoires</p>
    </div>
    <div class="login-body">
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>📧 Email professionnel</label>
                <input type="email" name="email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>🔒 Mot de passe</label>
                <input type="password" name="motdepasse" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>
        <div class="footer-links">
            <a href="#">Mot de passe oublié ?</a> | 
            <a href="index.php">Accueil</a>
        </div>
    </div>
</div>
</body>
</html>