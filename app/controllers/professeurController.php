<?php

require_once __DIR__ . '/../../config/database.php';

class ProfesseurController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function getDashboardData($idprof)
    {
        if (!$idprof) return null;

        try {
            // 1. Profil du professeur
            $stmt_prof = $this->db->prepare("SELECT * FROM professeur WHERE idprof = :idprof");
            $stmt_prof->execute([':idprof' => $idprof]);
            $professor = $stmt_prof->fetch(PDO::FETCH_ASSOC);
            if (!$professor) return null;

            // 2. Statistiques
            $stmt_stats = $this->db->prepare("
                SELECT 
                    COUNT(*) as nb_evaluations,
                    SUM(CASE WHEN m.statut = 'en_attente' THEN 1 ELSE 0 END) as nb_a_valider,
                    SUM(CASE WHEN m.statut = 'valide' THEN 1 ELSE 0 END) as nb_valides
                FROM jury j
                JOIN memoire m ON j.idmemoire = m.idmemoire
                WHERE j.idprof = :idprof
            ");
            $stmt_stats->execute([':idprof' => $idprof]);
            $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

            // 3. Mémoires récents
            $stmt_recent = $this->db->prepare("SELECT m.theme as titre, CONCAT(e.prenom, ' ', e.nom) as etudiant, f.nom_filiere as filiere, m.statut FROM jury j JOIN memoire m ON j.idmemoire = m.idmemoire JOIN etudiant e ON m.idetudiant = e.idetudiant LEFT JOIN filiere f ON m.idfiliere = f.idfiliere WHERE j.idprof = :idprof ORDER BY m.datesoumission DESC LIMIT 5");
            $stmt_recent->execute([':idprof' => $idprof]);
            $recent_memos = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

            return ['professor' => $professor, 'stats' => $stats, 'recent_memos' => $recent_memos];
        } catch (PDOException $e) {
            // En cas d'erreur, retourner null pour éviter de planter la page
            return null;
        }
    }

    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("SELECT idprof, nom, prenom, email FROM professeur ORDER BY nom, prenom");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Gestion_memoire/app/views/direction_etude/professeurs_de.php');
            exit;
        }

        if (empty($_SESSION['idde'])) {
            $_SESSION['flash']['error'] = "Accès non autorisé.";
            header('Location: /Gestion_memoire/public/login.php');
            exit;
        }

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['motdepasse'] ?? '');

        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            $_SESSION['flash']['error'] = 'Veuillez remplir tous les champs.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash']['error'] = 'Le format de l\'email est invalide.';
        } else {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("INSERT INTO professeur (nom, prenom, email, motdepasse) VALUES (:nom, :prenom, :email, :motdepasse)");
                $stmt->execute([':nom' => $nom, ':prenom' => $prenom, ':email' => $email, ':motdepasse' => $hashedPassword]);

                $_SESSION['flash']['success'] = 'Compte professeur créé avec succès.';
                $_SESSION['flash']['password'] = $password;
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // Duplicate entry
                    $_SESSION['flash']['error'] = 'Cet email est déjà utilisé par un autre professeur.';
                } else {
                    $_SESSION['flash']['error'] = 'Une erreur est survenue lors de la création du compte.';
                }
            }
        }

        header('Location: /Gestion_memoire/app/views/direction_etude/professeurs_de.php');
        exit;
    }

    public function getDEProfile()
    {
        if (empty($_SESSION['idde'])) {
            return ['nom_de' => 'Direction', 'initiales_de' => 'DE'];
        }
        try {
            $stmt = $this->db->prepare("SELECT nom, prenom FROM direction_etude WHERE idde = :idde");
            $stmt->execute([':idde' => $_SESSION['idde']]);
            $de = $stmt->fetch(PDO::FETCH_ASSOC);
            $nom_de = trim(($de['prenom'] ?? '') . ' ' . ($de['nom'] ?? ''));
            $initiales_de = strtoupper(substr($de['prenom'] ?? 'D', 0, 1) . substr($de['nom'] ?? 'E', 0, 1));
            return ['nom_de' => $nom_de, 'initiales_de' => $initiales_de];
        } catch (PDOException $e) {
            return ['nom_de' => 'Direction', 'initiales_de' => 'DE'];
        }
    }
}