<?php
session_start();

require_once __DIR__ . '/../app/controllers/authController.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = $_POST['userType'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    // Créer une instance du contrôleur et valider la connexion
    $authController = new AuthController();
    $result = $authController->login($userType, $email, $password);

    // Répondre en JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    
    // Si la connexion est réussie, créer la session utilisateur
    if ($result['success']) {
        $_SESSION['user'] = $result['user'];
        $_SESSION['logged_in'] = true;
    }
    
    exit;
}
?>
