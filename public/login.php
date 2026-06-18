<?php
require_once __DIR__ . '/../app/controllers/authController.php';

/**
 * Point d'entrée unique pour la gestion de l'authentification.
 * Ce fichier agit comme un routeur simple pour les actions d'authentification.
 */

// Initialise le contrôleur d'authentification
$authController = new AuthController();

// Récupère l'action demandée (par exemple, 'login', 'logout')
$action = $_GET['action'] ?? 'login';

switch ($action) {
    case 'login':
        // Si la requête est POST, on traite la tentative de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->processLogin();
        }
        break;
    // D'autres cas comme 'logout' pourraient être ajoutés ici.
}

// Si aucune action n'est traitée, on ne fait rien.
