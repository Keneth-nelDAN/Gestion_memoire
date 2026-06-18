<?php
session_start();
require_once __DIR__ . '/../app/controllers/publicationController.php';
// Simple routeur pour diriger les requêtes
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/Gestion_memoire/public';

$route = str_replace($base_path, '', $request_uri);
$route = strtok($route, '?'); // Enlever les query params

// Parsing pour les routes avec des IDs (ex: /publication/delete/12)
$parts = explode('/', trim($route, '/'));

switch ($route) {
    case '/':
    case '/dashboard':
        require_once __DIR__ . '/../app/controllers/dashboardController.php';
        $controller = new dashboardController();
        $controller->index();
        break;

    case '/login':
        require_once __DIR__ . '/../app/views/auth/connexion.php';
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

    // Ajouter d'autres routes ici
    default:
        http_response_code(404);
        echo "<h1>404 - Page non trouvée</h1>";
        break;
}
