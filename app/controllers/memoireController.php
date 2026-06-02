<?php
require_once "../app/models/Notification.php";

$notifModel = new Notification();
$notificationsNonLues = $notifModel->countNonLues($pdo, $_SESSION['idetudiant']);
?>