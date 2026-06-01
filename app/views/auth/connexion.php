<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = trim($_POST['motdepasse'] ?? '');
    $role = $_POST['role'] ?? 'etudiant';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Veuillez saisir un email valide et un mot de passe.';
    } elseif ($role === 'etudiant') {
        $column = mysqli_query($conn, "SHOW COLUMNS FROM etudiant LIKE 'type_compte'");
        if (!$column || mysqli_num_rows($column) === 0) {
            mysqli_query($conn, "ALTER TABLE etudiant ADD type_compte varchar(20) NOT NULL DEFAULT 'consultant' AFTER motdepasse");
        }
        $stmt = mysqli_prepare($conn, 'SELECT idetudiant, nom, prenom, email, motdepasse, type_compte FROM etudiant WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($user && hash_equals((string) $user['motdepasse'], $password)) {
            $_SESSION['idetudiant'] = (int) $user['idetudiant'];
            $_SESSION['nom_etudiant'] = trim($user['prenom'] . ' ' . $user['nom']);
            $_SESSION['type_compte_etudiant'] = $user['type_compte'] ?: 'consultant';
            header('Location: ../etudiant/dashboard_etudiant.php');
            exit;
        }
        $error = 'Identifiants étudiant incorrects.';
    } elseif ($role === 'professeur') {
        $stmt = mysqli_prepare($conn, 'SELECT idprof, nom, prenom, motdepasse FROM professeur WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($user && hash_equals((string) $user['motdepasse'], $password)) {
            $_SESSION['idprof'] = (int) $user['idprof'];
            $_SESSION['nom_professeur'] = trim($user['prenom'] . ' ' . $user['nom']);
            header('Location: ../professeur/dashboard_professeur.php');
            exit;
        }
        $error = 'Identifiants professeur incorrects.';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT idde, nom, prenom, motdepasse FROM direction_etude WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($user && hash_equals((string) $user['motdepasse'], $password)) {
            $_SESSION['idde'] = (int) $user['idde'];
            $_SESSION['nom_de'] = trim($user['prenom'] . ' ' . $user['nom']);
            header('Location: ../direction_etude/dashboard_de.php');
            exit;
        }
        $error = 'Identifiants Direction des Études incorrects.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GénieMémoire - Connexion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../direction_etude/style.css">
</head>
<body>
<main class="auth-shell">
    <section class="auth-card">
        <div class="form-title">
            <i class="fa-solid fa-lock"></i>
            <div>
                <h2>Connexion</h2>
                <p>Accédez à votre espace GénieMémoire.</p>
            </div>
        </div>
        <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post">
            <label for="role">Profil</label>
            <select id="role" name="role">
                <option value="etudiant">Étudiant</option>
                <option value="professeur">Professeur</option>
                <option value="direction">Direction des Études</option>
            </select>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" required>
            <label for="motdepasse">Mot de passe</label>
            <input id="motdepasse" type="password" name="motdepasse" required>
            <button class="btn-gold full" type="submit"><i class="fa-solid fa-right-to-bracket"></i> Se connecter</button>
        </form>
    </section>
</main>
</body>
</html>
