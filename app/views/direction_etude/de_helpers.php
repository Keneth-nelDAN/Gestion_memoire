<?php
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function get_de_profile($conn) {
    $profile = [
        'nom_de' => 'Directeur des Études',
        'initiales_de' => 'DE',
    ];

    if (empty($_SESSION['idde'])) {
        return $profile;
    }

    $idde = (int) $_SESSION['idde'];
    $stmt = mysqli_prepare($conn, "SELECT nom, prenom FROM direction_etude WHERE idde = ? LIMIT 1");
    if (!$stmt) {
        return $profile;
    }

    mysqli_stmt_bind_param($stmt, 'i', $idde);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    if ($row) {
        $prenom = trim($row['prenom'] ?? '');
        $nom = trim($row['nom'] ?? '');
        $fullName = trim($prenom . ' ' . $nom);
        if ($fullName !== '') {
            $profile['nom_de'] = $fullName;
            $profile['initiales_de'] = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        }
    }

    return $profile;
}

function bind_params_dynamic($stmt, $types, &$params) {
    if ($types === '' || empty($params)) {
        return true;
    }
    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    return mysqli_stmt_bind_param($stmt, $types, ...$refs);
}

function get_de_centres($conn) {
    $centres = ['Agla', 'Akpakpa', 'Gbegamey', 'Calavi', 'Porto-novo'];

    foreach ($centres as $centre) {
        $check = mysqli_prepare($conn, 'SELECT idCentre FROM centre WHERE nomCentre = ? LIMIT 1');
        if (!$check) {
            continue;
        }
        mysqli_stmt_bind_param($check, 's', $centre);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        if (!$result || mysqli_num_rows($result) === 0) {
            $insert = mysqli_prepare($conn, 'INSERT INTO centre (nomCentre) VALUES (?)');
            if ($insert) {
                mysqli_stmt_bind_param($insert, 's', $centre);
                mysqli_stmt_execute($insert);
            }
        }
    }

    return mysqli_query($conn, "
        SELECT idCentre, nomCentre
        FROM centre
        WHERE nomCentre IN ('Agla', 'Akpakpa', 'Gbegamey', 'Calavi', 'Porto-novo')
        ORDER BY FIELD(nomCentre, 'Agla', 'Akpakpa', 'Gbegamey', 'Calavi', 'Porto-novo')
    ");
}
?>
