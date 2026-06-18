<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/memoire.php';
require_once __DIR__ . '/../models/etudiant.php';
require_once __DIR__ . '/../models/professeur.php';

class DashboardController {
    private $db;
    private $memoireModel;
    private $etudiantModel;
    private $professeurModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->memoireModel = new Memoire($this->db);
        $this->etudiantModel = new Etudiant($this->db);
        $this->professeurModel = new Professeur($this->db);
    }

    public function getDEData($filters = []) {
        return [
            'stats' => [
                'nb_memoires' => $this->memoireModel->countTotal(),
                'nb_publies' => $this->memoireModel->countByStatut(['publie','publié','publiee','publiée']),
                'nb_attente' => $this->memoireModel->countByStatut(['en_attente','valide_non_publie']),
                'nb_mois' => $this->memoireModel->countThisMonth(),
                'nb_etudiants' => $this->etudiantModel->countTotal(),
                'nb_consultants' => $this->etudiantModel->countByType('consultant'),
                'nb_diplomes' => $this->etudiantModel->countByType('diplome'),
                'nb_professeurs' => $this->professeurModel->countTotal(),
            ],
            'filieres' => $this->memoireModel->getFilieres(),
            'annees' => $this->memoireModel->getAnneesDisponibles(),
            'recent_professeurs' => $this->professeurModel->getRecent(),
            'memoires' => $this->memoireModel->search($filters)
        ];
    }

    public function addProfesseur($data) {
        $nom = trim($data['prof_nom'] ?? '');
        $prenom = trim($data['prof_prenom'] ?? '');
        $email = strtolower(trim($data['prof_email'] ?? ''));
        $password = trim($data['prof_password'] ?? '');

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
