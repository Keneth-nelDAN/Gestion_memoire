<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: dashboard_de.php');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT fichier FROM ancien_memoire WHERE idAM = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$memoire = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$del = mysqli_prepare($conn, "DELETE FROM ancien_memoire WHERE idAM = ?");
mysqli_stmt_bind_param($del, 'i', $id);
mysqli_stmt_execute($del);

if ($memoire && !empty($memoire['fichier'])) {
    $path = __DIR__ . '/uploads/memoires/' . basename($memoire['fichier']);
    if (is_file($path)) {
        unlink($path);
    }
}

header('Location: dashboard_de.php?status=deleted');
exit;
?>
