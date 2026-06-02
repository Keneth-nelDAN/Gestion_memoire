<?php
<<<<<<< HEAD
session_start();
session_unset();
session_destroy();
header('Location: ../app/views/auth/connexion.php');
exit;
=======

session_start();

session_destroy();

header("Location: index.php");
exit;
?>
>>>>>>> 36b3c28 (Modification de la page accueil étudiants et la page détails. Création des pages : profil, mes mémoires.)
