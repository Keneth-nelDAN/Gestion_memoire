<?php
// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vider toutes les variables de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion professeur
// Le fichier login_professeur.php se trouve dans le même dossier (public/)
header('Location: login_professeur.php');
exit;
?>
