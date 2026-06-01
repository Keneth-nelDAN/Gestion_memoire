<?php
session_start();

require_once __DIR__ . '/../app/controllers/authController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = $_POST['userType'] ?? null;
    $nom = $_POST['nom'] ?? null;
    $prenom = $_POST['prenom'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $confirmPassword = $_POST['confirmPassword'] ?? null;
    $niveau = $_POST['niveau'] ?? null;
    $idfiliere = $_POST['idfiliere'] ?? null;

    $authController = new AuthController();
    $result = $authController->register($userType, $nom, $prenom, $email, $password, $confirmPassword, $niveau, $idfiliere);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
?>