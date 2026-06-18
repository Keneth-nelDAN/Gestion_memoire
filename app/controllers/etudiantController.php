<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/etudiant.php';
require_once __DIR__ . '/../models/memoire.php';
require_once __DIR__ . '/../models/notification.php';

class EtudiantController {
    private $db;
    private $etudiantModel;
    private $memoireModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->etudiantModel = new Etudiant($this->db);
        $this->memoireModel = new Memoire($this->db);
    }

    public function getProfile($idetudiant) {
        return $this->etudiantModel->getById($idetudiant);
    }

    public function getDashboardData($idetudiant) {
        $student = $this->getProfile($idetudiant);
        $nb_memoires = $this->memoireModel->countByStatut(['publie','publié','publiee','publiée']);
        
        $sql_deposes = "SELECT COUNT(*) FROM ancien_memoire WHERE idetudiant = :idetudiant";
        $stmt_deposes = $this->db->prepare($sql_deposes);
        $stmt_deposes->execute([':idetudiant' => $idetudiant]);
        $nb_deposes = $stmt_deposes->fetchColumn();

        return [
            'student' => $student,
            'nb_memoires' => $nb_memoires,
            'nb_deposes' => $nb_deposes
        ];
    }

    public function getDepositData() {
        $filieres = $this->memoireModel->getFilieres();
        
        $sql_centres = "SELECT idCentre, nomCentre FROM centre ORDER BY FIELD(nomCentre, 'Agla', 'Akpakpa', 'Gbegamey', 'Calavi', 'Porto-novo'), nomCentre";
        $centres = $this->db->query($sql_centres)->fetchAll(PDO::FETCH_ASSOC);

        $sql_profs = "SELECT idprof, nom, prenom, email FROM professeur ORDER BY prenom ASC, nom ASC";
        $professeurs = $this->db->query($sql_profs)->fetchAll(PDO::FETCH_ASSOC);

        return [
            'filieres' => $filieres,
            'centres' => $centres,
            'professeurs' => $professeurs
        ];
    }

    private function getProfessorName($idprof) {
        $sql = "SELECT nom, prenom FROM professeur WHERE idprof = :idprof LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idprof' => $idprof]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? trim($row['prenom'] . ' ' . $row['nom']) : '';
    }

    public function handleDeposer() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $idetudiant = $_SESSION['idetudiant'] ?? null;
        if (!$idetudiant) return ['error' => 'Non authentifié'];

        $student = $this->getProfile($idetudiant);

        // Récupération des données du formulaire
        $theme = trim($_POST['theme'] ?? '');
        $idfiliere = (int) ($_POST['idfiliere'] ?? $student['idfiliere']);
        $idCentre = ($_POST['idCentre'] ?? '') !== '' ? (int) $_POST['idCentre'] : null;
        $annee = trim($_POST['annee_academique'] ?? '');
        $date_soutenance = trim($_POST['date_soutenance'] ?? '');
        $mots_cles = trim($_POST['mots_cles'] ?? '');
        $id_maitre = (int) ($_POST['maitre_memoire'] ?? 0);
        $id_examinateur = (int) ($_POST['examinateur'] ?? 0);
        $id_president = (int) ($_POST['president_jury'] ?? 0);

        $maitre = $this->getProfessorName($id_maitre);
        $examinateur = $this->getProfessorName($id_examinateur);
        $president = $this->getProfessorName($id_president);

        if ($theme === '' || $idfiliere <= 0 || $idCentre === null || $annee === '' || $id_maitre <= 0 || $id_examinateur <= 0 || $id_president <= 0) {
            return ['error' => 'Veuillez renseigner toutes les informations obligatoires.'];
        }

        // Validation du fichier
        if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Veuillez sélectionner un fichier valide.'];
        }

        $file = $_FILES['fichier'];
        if ($file['size'] > 50 * 1024 * 1024) {
            return ['error' => 'Le fichier doit peser 50 Mo maximum.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        if (!isset($allowed[$mime])) {
            return ['error' => 'Format non autorisé. Formats acceptés : PDF ou Word.'];
        }

        $target_dir = __DIR__ . "/../views/memoire/uploads/memoires/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0775, true);
        }

        $extension = $allowed[$mime];
        $new_filename = 'memoire_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destination = $target_dir . DIRECTORY_SEPARATOR . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            try {
                $sql = "INSERT INTO ancien_memoire (nomAut, prenomAut, theme, idfiliere, idCentre, annee_academique, maitre_memoire, examinateur, president_jury, fichier, statut, source, idetudiant, date_depot, date_soutenance, mots_cles) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', 'etudiant_diplome', ?, NOW(), ?, ?)";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $student['nom'], $student['prenom'], $theme, $idfiliere, $idCentre, $annee, $maitre, $examinateur, $president, $new_filename, $idetudiant, $date_soutenance, $mots_cles
                ]);

                // Notifications
                $notificationModel = new Notification($this->db);
                $notif_msg = "Nouveau mémoire déposé par " . trim($student['prenom'] . ' ' . $student['nom']) . " : " . $theme;
                foreach (array_unique([$id_maitre, $id_examinateur, $id_president]) as $idprof) {
                    $notificationModel->create($notif_msg, null, (int)$idprof);
                }

                return ['success' => 'Votre mémoire a été déposé avec succès. Il est en attente de validation.'];

            } catch (PDOException $e) {
                if (file_exists($destination)) unlink($destination);
                return ['error' => 'Erreur de base de données : ' . $e->getMessage()];
            }
        } else {
            return ['error' => 'Impossible d\'enregistrer le fichier.'];
        }
    }

    public function getAll() {
        $sql = "SELECT e.*, f.nom_filiere, c.nomCentre, n.nomNiveau
                FROM etudiant e
                LEFT JOIN filiere f ON f.idfiliere = e.idfiliere
                LEFT JOIN centre c ON c.idCentre = e.idCentre
                LEFT JOIN niveau n ON n.idNiveau = e.idNiveau
                ORDER BY e.idetudiant DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exists($email) {
        $sql = "SELECT idetudiant FROM etudiant WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() !== false;
    }

    public function create($data) {
        $sql = "INSERT INTO etudiant (nom, prenom, idfiliere, idCentre, idNiveau, niveau, email, motdepasse, type_compte) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nom'], $data['prenom'], $data['idfiliere'], $data['idCentre'], $data['idNiveau'], $data['niveau'], $data['email'], $data['password'], $data['type_compte']
        ]);
    }
}
