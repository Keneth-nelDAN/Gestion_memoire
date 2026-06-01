<?php
require_once __DIR__ . '/../../../config/database.php';

$filieres = [];
$niveauOptions = [];

try {
    $database = new Database();
    $db = $database->connect();

    $stmt = $db->query('SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere');
    $filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->query('SELECT DISTINCT niveau FROM etudiant ORDER BY niveau');
    $niveauOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $filieres = [];
    $niveauOptions = [];
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .auth-card {
            width: 100%;
            max-width: 1000px;
            min-height: 760px;
            background: white;
            border-radius: 18px;
            box-shadow: 0 20px 50px rgba(26,58,82,0.12);
            overflow: hidden;
            display: flex;
            align-items: stretch;
            transform: translateY(0);
            transition: transform 200ms ease, box-shadow 200ms ease;
        }

        

        .login-container {
            display: flex;
            width: 100%;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #1a3a52 0%, #2c5aa0 100%);
            color: white;
            padding: 40px 30px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
        }

        .brand {
            text-align: center;
            margin-bottom: 50px;
            padding: 20px;
        }

        .brand-icon {
            width: 80px;
            height: 80px;
            background-color: #d4af37;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
            font-weight: bold;
            color: #1a3a52;
        }

        .brand h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .brand p {
            font-size: 14px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .brand-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
            max-width: 400px;
        }

        .login-left.register-active .brand-content {
            justify-content: flex-end;
        }

        .brand-title {
            font-size: 48px;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 30px;
            font-style: italic;
            letter-spacing: 1px;
        }

        .brand-title .highlight {
            color: #d4af37;
        }

        .brand-description {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        .brand-features {
            list-style: none;
        }

        .brand-features li {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 15px;
            line-height: 1.5;
        }

        .brand-features i {
            width: 30px;
            height: 30px;
            background-color: rgba(212, 175, 55, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #d4af37;
            font-size: 14px;
        }

        .brand_icon{
            display: inline-flex;
        }

        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: auto;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }

        .dot.active {
            background-color: #d4af37;
            width: 30px;
            border-radius: 5px;
        }

        .login-right {
            flex: 1;
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: #fbfbfd;
        }

        .tab-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 20px;
        }

        .tab-btn {
            background: none;
            border: none;
            font-size: 16px;
            color: #999;
            font-weight: 600;
            cursor: pointer;
            padding: 10px 5px;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            position: relative;
            bottom: -20px;
            text-decoration: none;
        }

        .tab-btn:hover,
        .tab-btn:focus {
            text-decoration: none;
        }

        .tab-btn.active {
            color: #1a3a52;
            border-bottom-color: #1a3a52;
        }

        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a3a52;
            margin-bottom: 15px;
        }

        .welcome-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 40px;
        }

        .user-type-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 40px;
        }

        .user-type-btn {
            border: 2px solid #e0e0e0;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .user-type-btn:hover,
        .user-type-btn.active {
            border-color: #1a3a52;
            background-color: #f0f5ff;
            color: #1a3a52;
        }

        .user-type-btn i {
            display: block;
            font-size: 28px;
            margin-bottom: 10px;
            color: #1a3a52;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: white;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1a3a52;
            box-shadow: 0 0 0 3px rgba(26, 58, 82, 0.08);
            background-color: white;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-grid.two-columns .form-group {
            margin-bottom: 18px;
        }

        .form-title {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .subtext {
            color: #6b7280;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .styled-button {
            width: 100%;
            padding: 16px;
            background-color: #1f3568;
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .styled-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(31, 53, 104, 0.22);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .forgot-password {
            color: #d4af37;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            color: #c99c2e;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1a3a52 0%, #2c5aa0 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(26, 58, 82, 0.3);
        }

        .login-btn i {
            font-size: 16px;
        }

        .signup-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .hidden {
            display: none;
        }

        .signup-link a {
            color: #d4af37;
            text-decoration: none;
            font-weight: 700;
            margin-left: 5px;
            transition: all 0.3s ease;
        }

        .signup-link a:hover {
            color: #c99c2e;
        }

        @media (max-width: 992px) {
            .auth-card {
                max-width: 760px;
                height: auto;
                min-height: 720px;
            }

            .login-container {
                flex-direction: column;
            }

            .login-left {
                padding: 30px 24px;
            }

            .login-right {
                padding: 30px 24px;
            }

            .brand-title {
                font-size: 36px;
            }

            .user-type-buttons {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .auth-card {
                border-radius: 12px;
            }

            .login-left {
                padding: 24px 18px;
            }

            .login-right {
                padding: 24px 18px;
            }

            .brand-title {
                font-size: 28px;
            }

            .user-type-buttons {
                grid-template-columns: 1fr;
            }

            .tab-buttons {
                gap: 10px;
            }

            .tab-btn {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="login-container">
        <div class="login-left">
            <div class="brand_icon">
                <div class="brand-icon">M</div>
                <div class="brand">
                    <h1>GénieMémoire</h1>
                    <p>Plateforme universitaire</p>
                </div>
            </div>

            <div class="brand-content">
                <div class="brand-title">
                    Vos travaux, <span class="highlight">archivés</span> et valorisés.
                </div>
                <p class="brand-description">
                    Un espace numérique pour déposer, valider et partager les mémoires universitaires en toute simplicité.
                </p>
                <ul class="brand-features">
                    <li>
                        <i class="fas fa-lock"></i>
                        Dépôt en ligne sécurisé
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        Validation numérique par le Jury
                    </li>
                    <li>
                        <i class="fas fa-search"></i>
                        Recherche avancée par filière, auteur, année
                    </li>
                    <li>
                        <i class="fas fa-shield-alt"></i>
                        Protection contre la copie et le téléchargement
                    </li>
                </ul>
            </div>

            <div class="carousel-dots">
                <div class="dot active"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>

        <div class="login-right">
            <div class="tab-buttons">
                <button class="tab-btn active">Se connecter</button>
                <!-- <a class="tab-btn" href="/Gestion_memoire/app/views/auth/inscription.php">Créer un compte</a> -->
            </div>

            <h2 class="welcome-title">Bienvenue</h2>
            <p class="welcome-subtitle">Connectez-vous à votre espace personnel.</p>

             <p style="font-weight: bold;">Je suis...</p>
            <div class="user-type-buttons">
                <button class="user-type-btn" onclick="selectUserType(this, 'etudiant')">
                    <i class="fas fa-user-graduate"></i>
                    Étudiant
                </button>
                <button class="user-type-btn" onclick="selectUserType(this, 'professeur')">
                    <i class="fas fa-chalkboard-user"></i>
                    Professeur
                </button>
                <button class="user-type-btn" onclick="selectUserType(this, 'directeur')">
                    <i class="fas fa-landmark"></i>
                    Directeur des Études
                </button>
            </div>

            <div id="alertMessage" style="display: none; margin-bottom: 20px; padding: 12px 15px; border-radius: 8px; font-size: 14px;" role="alert"></div>

            <input type="hidden" id="userTypeInput" name="userType" value="etudiant">

            <div id="loginPanel">
                <div class="form-title">Bienvenue</div>
                <p class="subtext">Connectez-vous à votre espace personnel.</p>
                <form id="loginForm">
                <div class="form-group">
                    <label for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" placeholder="votre@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de Passe</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="form-options">
                    <label style="margin: 0; display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" id="rememberMe" name="rememberMe" style="width: auto;">
                        Se souvenir de moi
                    </label>
                    <a href="#" class="forgot-password">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="styled-button" id="submitBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>

                <div class="signup-link">
                    Pas encore inscrit ? <a href="mailto:admin@gmail.fr">Écrire au gestionnaire</a>
                </div>
            </form>
            </div>

        </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const userTypeInput = document.getElementById('userTypeInput');
        const alertDiv = document.getElementById('alertMessage');
        const loginPanel = document.getElementById('loginPanel');
        const registerPanel = document.getElementById('registerPanel');
        const tabButtons = document.querySelectorAll('.tab-btn');

        function selectUserType(btn, type) {
            document.querySelectorAll('.user-type-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            userTypeInput.value = type;
        }

        function activateTab(tab) {
            tabButtons.forEach(button => button.classList.toggle('active', button.textContent.trim() === (tab === 'login' ? 'Se connecter' : 'Créer un compte')));
            const loginLeft = document.querySelector('.login-left');
            if (tab === 'login') {
                loginPanel.classList.remove('hidden');
                registerPanel.classList.add('hidden');
                alertDiv.style.display = 'none';
                document.querySelector('.welcome-subtitle').textContent = 'Connectez-vous à votre espace personnel.';
                // Par défaut pour l'onglet connexion, conserver le rôle étudiant
                userTypeInput.value = 'etudiant';
                loginLeft.classList.remove('register-active');
            } else {
                loginPanel.classList.add('hidden');
                registerPanel.classList.remove('hidden');
                alertDiv.style.display = 'none';
                document.querySelector('.welcome-subtitle').textContent = 'Créez un compte pour accéder à la plateforme.';
                userTypeInput.value = 'etudiant';
                loginLeft.classList.add('register-active');
            }
        }

        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', () => {
                const tab = button.textContent.trim() === 'Créer un compte' ? 'register' : 'login';
                activateTab(tab);
            });
        });

        // Certains éléments (showRegisterTab / showLoginTab) peuvent ne pas exister
        const showRegisterTabBtn = document.getElementById('showRegisterTab');
        if (showRegisterTabBtn) {
            showRegisterTabBtn.addEventListener('click', function(e) {
                e.preventDefault();
                activateTab('register');
            });
        }

        const showLoginTabBtn = document.getElementById('showLoginTab');
        if (showLoginTabBtn) {
            showLoginTabBtn.addEventListener('click', function(e) {
                e.preventDefault();
                activateTab('login');
            });
        }

        function showAlert(type, message) {
            alertDiv.className = '';
            if (type === 'success') {
                alertDiv.style.backgroundColor = '#d4edda';
                alertDiv.style.color = '#155724';
                alertDiv.style.borderLeft = '4px solid #28a745';
            } else if (type === 'warning') {
                alertDiv.style.backgroundColor = '#fff3cd';
                alertDiv.style.color = '#856404';
                alertDiv.style.borderLeft = '4px solid #ffeeba';
            } else {
                alertDiv.style.backgroundColor = '#f8d7da';
                alertDiv.style.color = '#721c24';
                alertDiv.style.borderLeft = '4px solid #f5c6cb';
            }
            alertDiv.innerHTML = message;
            alertDiv.style.display = 'block';
        }

        function buildFormBody(values) {
            return Object.keys(values).map(key => `${encodeURIComponent(key)}=${encodeURIComponent(values[key])}`).join('&');
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const userType = userTypeInput.value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('submitBtn');

            if (!userType) {
                showAlert('warning', '<i class="fas fa-exclamation-triangle"></i> Veuillez sélectionner votre rôle avant de vous connecter.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion en cours...';
            alertDiv.style.display = 'none';

            fetch('/Gestion_memoire/public/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                credentials: 'include',
                body: buildFormBody({ userType, email, password })
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
                if (data.success) {
                    showAlert('success', `<i class="fas fa-check-circle"></i> ${data.message}`);
                    setTimeout(() => {
                        switch(data.user.type) {
                            case 'etudiant':
                                window.location.href = '/Gestion_memoire/app/views/etudiant/dashboard_etudiant.php';
                                break;
                            case 'professeur':
                                window.location.href = '/Gestion_memoire/app/views/professeur/dashboard_professeur.php';
                                break;
                            case 'directeur':
                                window.location.href = '/Gestion_memoire/app/views/direction_etude/dashboard_de.php';
                                break;
                        }
                    }, 1500);
                } else {
                    showAlert('danger', `<i class="fas fa-exclamation-circle"></i> ${data.message}`);
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
                showAlert('danger', `<i class="fas fa-exclamation-circle"></i> Erreur de connexion au serveur`);
                console.error('Erreur:', error);
            });
        });

    </script>
</body>
</html>
