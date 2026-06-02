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

function ensure_etudiant_account_schema($conn) {
    $type_column = mysqli_query($conn, "SHOW COLUMNS FROM etudiant LIKE 'type_compte'");
    if (!$type_column || mysqli_num_rows($type_column) === 0) {
        mysqli_query($conn, "ALTER TABLE etudiant ADD type_compte varchar(20) NOT NULL DEFAULT 'consultant' AFTER motdepasse");
    }

    $centre_column = mysqli_query($conn, "SHOW COLUMNS FROM etudiant LIKE 'idCentre'");
    if (!$centre_column || mysqli_num_rows($centre_column) === 0) {
        mysqli_query($conn, "ALTER TABLE etudiant ADD idCentre int DEFAULT NULL AFTER idfiliere");
    }

    $niveau_column = mysqli_query($conn, "SHOW COLUMNS FROM etudiant LIKE 'idNiveau'");
    if (!$niveau_column || mysqli_num_rows($niveau_column) === 0) {
        mysqli_query($conn, "ALTER TABLE etudiant ADD idNiveau int DEFAULT NULL AFTER idCentre");
    }
}

function get_de_niveaux($conn) {
    $niveaux = ['L1', 'L2', 'L3', 'M1', 'M2'];

    foreach ($niveaux as $niveau) {
        $check = mysqli_prepare($conn, 'SELECT idNiveau FROM niveau WHERE nomNiveau = ? LIMIT 1');
        if (!$check) {
            continue;
        }
        mysqli_stmt_bind_param($check, 's', $niveau);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        if (!$result || mysqli_num_rows($result) === 0) {
            $insert = mysqli_prepare($conn, 'INSERT INTO niveau (nomNiveau) VALUES (?)');
            if ($insert) {
                mysqli_stmt_bind_param($insert, 's', $niveau);
                mysqli_stmt_execute($insert);
            }
        }
    }

    return mysqli_query($conn, "
        SELECT idNiveau, nomNiveau
        FROM niveau
        ORDER BY FIELD(nomNiveau, 'L1', 'L2', 'L3', 'M1', 'M2'), nomNiveau
    ");
}

function generate_student_password() {
    return 'Etud@' . random_int(100000, 999999);
}

function smtp_read_response($socket) {
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    return $response;
}

function smtp_command($socket, $command, $expected_codes, &$error) {
    if ($command !== null) {
        fwrite($socket, $command . "\r\n");
    }
    $response = smtp_read_response($socket);
    $code = (int) substr($response, 0, 3);
    if (!in_array($code, (array) $expected_codes, true)) {
        $error = trim($response);
        return false;
    }
    return true;
}

function smtp_escape_message($message) {
    $message = str_replace(["\r\n", "\r"], "\n", $message);
    $lines = explode("\n", $message);
    foreach ($lines as &$line) {
        if (isset($line[0]) && $line[0] === '.') {
            $line = '.' . $line;
        }
    }
    return implode("\r\n", $lines);
}

function send_smtp_email($to, $subject, $body, &$error) {
    $config_path = __DIR__ . '/../../../config/email.php';
    if (!file_exists($config_path)) {
        $error = 'Le fichier config/email.php est introuvable.';
        return false;
    }

    $config = require $config_path;
    $username = trim($config['smtp_username'] ?? '');
    $password = trim($config['smtp_password'] ?? '');
    $from_email = trim($config['from_email'] ?? $username);
    $from_name = trim($config['from_name'] ?? 'GenieMemoire');

    if ($username === '' || $password === '' || str_contains($username, 'votre.') || str_contains($password, 'votre_')) {
        $error = 'Configurez votre adresse Gmail et votre mot de passe d application dans config/email.php.';
        return false;
    }

    $host = $config['smtp_host'] ?? 'smtp.gmail.com';
    $port = (int) ($config['smtp_port'] ?? 465);
    $secure = $config['smtp_secure'] ?? 'ssl';
    $target = ($secure === 'ssl' ? 'ssl://' : '') . $host;

    $socket = @fsockopen($target, $port, $errno, $errstr, 20);
    if (!$socket) {
        $error = 'Connexion SMTP impossible : ' . $errstr;
        return false;
    }
    stream_set_timeout($socket, 20);

    if (!smtp_command($socket, null, 220, $error)
        || !smtp_command($socket, 'EHLO localhost', 250, $error)
        || !smtp_command($socket, 'AUTH LOGIN', 334, $error)
        || !smtp_command($socket, base64_encode($username), 334, $error)
        || !smtp_command($socket, base64_encode($password), 235, $error)
        || !smtp_command($socket, 'MAIL FROM:<' . $from_email . '>', 250, $error)
        || !smtp_command($socket, 'RCPT TO:<' . $to . '>', [250, 251], $error)
        || !smtp_command($socket, 'DATA', 354, $error)) {
        fclose($socket);
        return false;
    }

    $headers = [
        'From: ' . $from_name . ' <' . $from_email . '>',
        'To: <' . $to . '>',
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];
    fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . smtp_escape_message($body) . "\r\n.\r\n");

    if (!smtp_command($socket, null, 250, $error)) {
        fclose($socket);
        return false;
    }

    smtp_command($socket, 'QUIT', 221, $error);
    fclose($socket);
    return true;
}

function send_student_credentials_email($email, $prenom, $password, $type_compte, &$error = '') {
    $role = $type_compte === 'diplome' ? 'étudiant diplômé' : 'étudiant consultaire';
    $subject = 'Vos identifiants GenieMemoire';
    $message = "Bonjour " . $prenom . ",\n\n"
        . "Votre compte " . $role . " a été créé sur GenieMemoire.\n\n"
        . "Email de connexion : " . $email . "\n"
        . "Mot de passe : " . $password . "\n\n"
        . "Connectez-vous puis conservez ces informations en sécurité.\n\n"
        . "Direction des Études";

    return send_smtp_email($email, $subject, $message, $error);
}
function get_de_professeurs($conn) {
    $result = mysqli_query($conn, "SELECT idprof, nom, prenom, email FROM professeur ORDER BY prenom ASC, nom ASC");
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function get_professeur_name_by_id($conn, $idprof) {
    $idprof = (int) $idprof;
    if ($idprof <= 0) {
        return '';
    }
    $stmt = mysqli_prepare($conn, 'SELECT nom, prenom FROM professeur WHERE idprof = ? LIMIT 1');
    if (!$stmt) {
        return '';
    }
    mysqli_stmt_bind_param($stmt, 'i', $idprof);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    return $row ? trim($row['prenom'] . ' ' . $row['nom']) : '';
}

function professeur_option_selected($current_name, $prenom, $nom) {
    return trim((string) $current_name) === trim($prenom . ' ' . $nom) ? 'selected' : '';
}
?>

