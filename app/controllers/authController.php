<?php

require_once __DIR__ . '/../../config/database.php';

class AuthController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * Valide les identifiants de l'utilisateur
     * @param string $userType Type d'utilisateur (etudiant, professeur, directeur)
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @return array Résultat de la validation avec statut et données utilisateur
     */
    public function login($userType, $email, $password) {
        // Validation des champs
        $validation = $this->validateInput($userType, $email, $password);
        if (!$validation['success']) {
            return $validation;
        }

        // Vérifier les informations dans la base de données selon le type d'utilisateur
        switch ($userType) {
            case 'etudiant':
                return $this->loginEtudiant($email, $password);
            case 'professeur':
                return $this->loginProfesseur($email, $password);
            case 'directeur':
                return $this->loginDirecteur($email, $password);
            default:
                return [
                    'success' => false,
                    'message' => 'Type d\'utilisateur invalide'
                ];
        }
    }

    /**
     * Valide les données saisies
     */
    private function validateInput($userType, $email, $password) {
        // Vérifier que tous les champs sont remplis
        if (empty($userType) || empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Veuillez remplir tous les champs'
            ];
        }

        // Valider le format de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Le format de l\'email est invalide'
            ];
        }

        // Vérifier que le type d'utilisateur est valide
        $allowedRoles = ['etudiant', 'professeur', 'directeur'];
        if (!in_array($userType, $allowedRoles, true)) {
            return [
                'success' => false,
                'message' => 'Veuillez sélectionner un rôle valide'
            ];
        }

        // Vérifier la longueur du mot de passe
        if (strlen($password) < 6) {
            return [
                'success' => false,
                'message' => 'Le mot de passe doit contenir au moins 6 caractères'
            ];
        }

        return ['success' => true];
    }

    /**
     * Connexion pour un étudiant
     */
    private function loginEtudiant($email, $password) {
        try {
            $query = 'SELECT * FROM etudiant WHERE email = :email LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($etudiant && $password === $etudiant['motdepasse']) {
                // Connexion réussie
                return [
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'user' => [
                        'id' => $etudiant['idetudiant'],
                        'nom' => $etudiant['nom'],
                        'prenom' => $etudiant['prenom'],
                        'email' => $etudiant['email'],
                        'type' => 'etudiant'
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Email ou mot de passe incorrect'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la connexion : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Connexion pour un professeur
     */
    private function loginProfesseur($email, $password) {
        try {
            $query = 'SELECT * FROM professeur WHERE email = :email LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            $professeur = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($professeur && $password === $professeur['motdepasse']) {
                // Connexion réussie
                return [
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'user' => [
                        'id' => $professeur['idprof'],
                        'nom' => $professeur['nom'],
                        'prenom' => $professeur['prenom'],
                        'email' => $professeur['email'],
                        'type' => 'professeur'
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Email ou mot de passe incorrect'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la connexion : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Connexion pour un directeur des études
     */
    private function loginDirecteur($email, $password) {
        try {
            $query = 'SELECT * FROM direction_etude WHERE email = :email LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            $directeur = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($directeur && $password === $directeur['motdepasse']) {
                // Connexion réussie
                return [
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'user' => [
                        'id' => $directeur['idde'],
                        'nom' => $directeur['nom'],
                        'prenom' => $directeur['prenom'],
                        'email' => $directeur['email'],
                        'type' => 'directeur'
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Email ou mot de passe incorrect'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la connexion : ' . $e->getMessage()
            ];
        }
    }
}
