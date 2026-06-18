<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mysqli_config.php';

// Sécurité : Seul un étudiant connecté peut liker ou commenter
if (empty($_SESSION['idetudiant'])) {
    echo json_encode(['status' => 'error', 'message' => 'Non authentifié']);
    exit;
}

$id_etudiant = $_SESSION['idetudiant'];
$action = $_POST['action'] ?? '';
$idAM = intval($_POST['idAM'] ?? 0);

if ($idAM <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID mémoire invalide']);
    exit;
}

// 1. GESTION DES LIKES
if ($action === 'toggle_like') {
    // Vérifier si l'étudiant a déjà liké ce mémoire
    $check_sql = "SELECT idlike FROM like_memoire WHERE idAM = ? AND idetudiant = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "ii", $idAM, $id_etudiant);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Déjà liké -> On supprime le like
        $idlike = $row['idlike'];
        $del_sql = "DELETE FROM like_memoire WHERE idlike = ?";
        $del_stmt = mysqli_prepare($conn, $del_sql);
        mysqli_stmt_bind_param($del_stmt, "i", $idlike);
        mysqli_stmt_execute($del_stmt);
        $liked = false;
    } else {
        // Pas encore liké -> On ajoute le like (idmemoire prend la valeur de idAM par cohérence)
        $ins_sql = "INSERT INTO like_memoire (idAM, idmemoire, idetudiant) VALUES (?, ?, ?)";
        $ins_stmt = mysqli_prepare($conn, $ins_sql);
        mysqli_stmt_bind_param($ins_stmt, "iii", $idAM, $idAM, $id_etudiant);
        mysqli_stmt_execute($ins_stmt);
        $liked = true;
    }
    
    // Compter le nombre total de likes mis à jour
    $count_sql = "SELECT COUNT(*) AS total FROM like_memoire WHERE idAM = ?";
    $count_stmt = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($count_stmt, "i", $idAM);
    mysqli_stmt_execute($count_stmt);
    $count_res = mysqli_stmt_get_result($count_stmt);
    $total_likes = mysqli_fetch_assoc($count_res)['total'];
    
    echo json_encode(['status' => 'success', 'liked' => $liked, 'total_likes' => $total_likes]);
    exit;
}

// 2. GESTION DES COMMENTAIRES
if ($action === 'add_comment') {
    $contenu = trim($_POST['contenu'] ?? '');
    if ($contenu === '') {
        echo json_encode(['status' => 'error', 'message' => 'Le commentaire ne peut pas être vide']);
        exit;
    }
    
    // Insertion stricte selon les colonnes de la table 'commentaire'
    // idmemoire reçoit l'identifiant du mémoire consulté (idAM)
    $ins_comm = "INSERT INTO commentaire (contenu, idmemoire, idetudiant) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $ins_comm);
    mysqli_stmt_bind_param($stmt, "sii", $contenu, $idAM, $id_etudiant);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'status' => 'success', 
            'auteur' => $_SESSION['nom'] . ' ' . $_SESSION['prenom'], // À ajuster selon tes variables de session
            'date' => date('d/m/Y à H:i'),
            'contenu' => htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8')
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'enregistrement']);
    }
    exit;
}