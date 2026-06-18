<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/professeur.php';

class ProfesseurController {
    private $db;
    private $professeurModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->professeurModel = new Professeur($this->db);
    }

    public function getProfile($idprof) {
        return $this->professeurModel->getById($idprof);
    }

    public function getDashboardData($idprof) {
        $professor = $this->getProfile($idprof);
        if (!$professor) return null;

        $email = $professor['email'];

        // 1. Essai via JURY
        $sql_total = "SELECT COUNT(*) FROM jury WHERE idprof = :idprof";
        $stmt_total = $this->db->prepare($sql_total);
        $stmt_total->execute([':idprof' => $idprof]);
        $nb_evaluations = $stmt_total->fetchColumn();

        $sql_recent = "
            SELECT m.idmemoire AS id, m.theme AS titre, 
                   CONCAT(e.prenom, ' ', e.nom) AS etudiant, 
                   f.nom_filiere AS filiere, e.niveau AS niveau, m.statut AS statut, 
                   m.datesoumission AS date_depot, j.decision AS note
            FROM jury j
            JOIN memoire m ON j.idmemoire = m.idmemoire
            JOIN etudiant e ON m.idetudiant = e.idetudiant
            LEFT JOIN filiere f ON m.idfiliere = f.idfiliere
            WHERE j.idprof = :idprof
            ORDER BY m.idmemoire DESC LIMIT 5
        ";
        $stmt_recent = $this->db->prepare($sql_recent);
        $stmt_recent->execute([':idprof' => $idprof]);
        $recent_memos = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

        $nb_a_valider = 0;
        $nb_valides = 0;

        if ($nb_evaluations > 0) {
            $sql_attente = "SELECT COUNT(*) FROM jury WHERE idprof = :idprof AND (decision IS NULL OR decision = '' OR decision = 'en_attente')";
            $stmt_attente = $this->db->prepare($sql_attente);
            $stmt_attente->execute([':idprof' => $idprof]);
            $nb_a_valider = $stmt_attente->fetchColumn();

            $sql_valide = "SELECT COUNT(*) FROM jury WHERE idprof = :idprof AND decision IS NOT NULL AND decision <> '' AND decision <> 'en_attente' AND decision <> 'refuse'";
            $stmt_valide = $this->db->prepare($sql_valide);
            $stmt_valide->execute([':idprof' => $idprof]);
            $nb_valides = $stmt_valide->fetchColumn();
        } else {
            // Fallback sur ancien_memoire
            $sql_total_alt = "SELECT COUNT(*) FROM ancien_memoire WHERE examinateur = :email OR president_jury = :email";
            $stmt_total_alt = $this->db->prepare($sql_total_alt);
            $stmt_total_alt->execute([':email' => $email]);
            $nb_evaluations = $stmt_total_alt->fetchColumn();

            $sql_attente_alt = "SELECT COUNT(*) FROM ancien_memoire WHERE statut = 'en_attente' AND (examinateur = :email OR president_jury = :email)";
            $stmt_attente_alt = $this->db->prepare($sql_attente_alt);
            $stmt_attente_alt->execute([':email' => $email]);
            $nb_a_valider = $stmt_attente_alt->fetchColumn();

            $sql_valide_alt = "SELECT COUNT(*) FROM ancien_memoire WHERE statut IN ('valide', 'publié', 'publie') AND (examinateur = :email OR president_jury = :email)";
            $stmt_valide_alt = $this->db->prepare($sql_valide_alt);
            $stmt_valide_alt->execute([':email' => $email]);
            $nb_valides = $stmt_valide_alt->fetchColumn();

            $sql_recent_alt = "
                SELECT idAM AS id, theme AS titre, 
                       CONCAT(prenomAut, ' ', nomAut) AS etudiant, 
                       (SELECT nom_filiere FROM filiere WHERE idfiliere = ancien_memoire.idfiliere LIMIT 1) AS filiere, 
                       (SELECT nomNiveau FROM niveau WHERE idNiveau = ancien_memoire.idNiveau LIMIT 1) AS niveau, 
                       statut, date_depot, '' AS note
                FROM ancien_memoire 
                WHERE examinateur = :email OR president_jury = :email 
                ORDER BY idAM DESC LIMIT 5
            ";
            $stmt_recent_alt = $this->db->prepare($sql_recent_alt);
            $stmt_recent_alt->execute([':email' => $email]);
            $recent_memos = $stmt_recent_alt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [
            'professor' => $professor,
            'stats' => [
                'nb_evaluations' => $nb_evaluations,
                'nb_a_valider' => $nb_a_valider,
                'nb_valides' => $nb_valides
            ],
            'recent_memos' => $recent_memos
        ];
    }

    public function getAll() {
        $sql = "SELECT idprof, nom, prenom, email FROM professeur ORDER BY idprof DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addProfesseur($data) {
        $nom = trim($data['nom'] ?? '');
        $prenom = trim($data['prenom'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = trim($data['motdepasse'] ?? '');

        if ($nom === '' || $prenom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Veuillez renseigner le nom, le prénom et un email valide.'];
        }

        if ($this->professeurModel->exists($email)) {
            return ['success' => false, 'error' => 'Un professeur utilise déjà cet email.'];
        }

        if ($this->professeurModel->create($nom, $prenom, $email, $password)) {
            return ['success' => true, 'password' => $password];
        }

        return ['success' => false, 'error' => 'Impossible de créer le compte professeur.'];
    }
}
