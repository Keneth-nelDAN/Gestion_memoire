<?php
session_start();
// Ajouter cette clause au début du fichier, après session_start()
$controller = $_GET['controller'] ?? '';

if ($controller === 'professeur') {
    require_once __DIR__ . '/../app/controllers/ProfesseurController.php';
    $action = $_GET['action'] ?? 'index';
    $ctrl = new ProfesseurController();
    if ($action === 'index') {
        $ctrl->index();
    } elseif ($action === 'traiterDecision') {
        $ctrl->traiterDecision();
    } elseif ($action === 'annulerDecision') {
        $ctrl->annulerDecision();
    } else {
        $ctrl->index();
    }
    exit; // ne pas exécuter le reste (partie étudiant)
}

// ... reste de votre code pour les étudiants (comme avant)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/memoire.php';
require_once __DIR__ . '/../app/models/like.php';
require_once __DIR__ . '/../app/controllers/likeController.php';

// Traitement du like
if (isset($_GET['action']) && $_GET['action'] === 'like' && isset($_GET['id'])) {
    $likeController = new LikeController();
    $likeController->toggle();
    exit;
}

// Filtre niveau
$niveau = $_GET['niveau'] ?? 'Tous';

$memoireModel = new Memoire($pdo);
$publications = $memoireModel->getMemoires($niveau);
$totalMemoires = count($memoireModel->getMemoires()); // total sans filtre

// Total filières
$stmt = $pdo->query("SELECT COUNT(*) FROM filiere");
$totalFilieres = $stmt->fetchColumn();

$likeModel = new Like();

include __DIR__ . '/../app/views/memoire/index.php';
?>