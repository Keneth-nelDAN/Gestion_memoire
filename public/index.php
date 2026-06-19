<?php
session_start();
require_once __DIR__ . '/../app/controllers/publicationController.php';

// Le routeur lit maintenant le paramètre 'url' fourni par .htaccess
$route = $_GET['url'] ?? '';
$route = '/' . trim($route, '/');

// Parsing pour les routes avec des IDs (ex: /publication/delete/12)
$parts = explode('/', trim($route, '/'));

// Le switch gère les routes principales de l'application
switch ($route) {
    case '/':
    case '/dashboard':
        require_once __DIR__ . '/../app/controllers/dashboardController.php';
        $controller = new dashboardController();
        $controller->index();
        break;

    case '/login':
        // Le fichier login.php gère déjà la distinction GET/POST
        require_once __DIR__ . '/login.php';
        break;

    case '/register':
        require_once __DIR__ . '/register.php';
        break;

    case '/logout':
        require_once __DIR__ . '/../app/controllers/authController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    case '/publications':
        require_once __DIR__ . '/../app/views/direction_etude/publications.php';
        break;

    case '/publication/create':
        $controller = new PublicationController();
        $controller->create();
        break;

    case '/publication/create-batch':
        $controller = new PublicationController();
        $controller->createBatch();
        break;

    // Route pour la suppression: /publication/delete/ID
    case (preg_match('/\/publication\/delete\/(\d+)/', $route, $matches) ? $route : false):
        $id = (int)$matches[1];
        $controller = new PublicationController();
        $controller->delete($id);
        break;

    case '/professeurs':
        require_once __DIR__ . '/../app/views/direction_etude/professeurs_de.php';
        break;

    case '/professeur/create':
        require_once __DIR__ . '/../app/controllers/professeurController.php';
        $controller = new ProfesseurController();
        $controller->create();
        break;

    // Ajouter d'autres routes ici
    default:
        http_response_code(404);
        echo "<h1>404 - Page non trouvée sur le routeur</h1><p>Route demandée: " . htmlspecialchars($route) . "</p>";
        break;
}
