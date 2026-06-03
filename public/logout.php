<?php
session_start();
session_unset();
session_destroy();
header('Location: ../app/views/auth/connexion.php');
exit;