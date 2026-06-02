<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/mysqli_config.php';

// Vérifier si l'utilisateur est connecté et est un professeur
if (empty($_SESSION['idprof']) && (empty($_SESSION['user']) || $_SESSION['user']['type'] !== 'professeur')) {
    header('Location: ../auth/connexion.php');
    exit;
}

$idprof = $_SESSION['idprof'] ?? $_SESSION['user']['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mémoires en jury - Professeur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Cette page affichera les mémoires en jury assignés à ce professeur.
        </div>
        <a href="dashboard_professeur.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>
</body>
</html>
