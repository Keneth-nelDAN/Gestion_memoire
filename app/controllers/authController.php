<?php

require_once __DIR__ . '/../../config/database.php';

class AuthController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

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

    public function register($userType, $nom, $prenom, $email, $password, $confirmPassword, $niveau = null, $idfiliere = null) {
        if (empty($userType) || empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirmPassword)) {
            return [
                'success' => false,
                'message' => 'Veuillez remplir tous les champs du formulaire d\'inscription'
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Le format de l\'email est invalide'
            ];
        }

        $allowedRoles = ['etudiant', 'professeur', 'directeur'];
        if (!in_array($userType, $allowedRoles, true)) {
            return [
                'success' => false,
                'message' => 'Veuillez sélectionner un rôle valide'
            ];
        }

        if (strlen($password) < 6) {
            return [
                'success' => false,
                'message' => 'Le mot de passe doit contenir au moins 6 caractères'
            ];
        }

        if ($password !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Les mots de passe ne correspondent pas'
            ];
        }

        switch ($userType) {
            case 'etudiant':
                return $this->registerEtudiant($nom, $prenom, $email, $password, $niveau, $idfiliere);
            case 'professeur':
                return $this->registerProfesseur($nom, $prenom, $email, $password);
            case 'directeur':
                return $this->registerDirecteur($nom, $prenom, $email, $password);
        }

        return [
            'success' => false,
            'message' => 'Impossible de traiter l\'inscription pour ce rôle'
        ];
    }

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

    private function loginEtudiant($email, $password) {
        try {
            $query = 'SELECT * FROM etudiant WHERE email = :email LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

            $password = trim($password);
            $storedPassword = trim((string) $etudiant['motdepasse']);
            $passwordMatches = $etudiant && (password_verify($password, $storedPassword) || hash_equals($storedPassword, $password));
            if ($passwordMatches) {
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

    private function loginProfesseur($email, $password) {
        try {
            $query = 'SELECT * FROM professeur WHERE email = :email LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            $professeur = $stmt->fetch(PDO::FETCH_ASSOC);

            $password = trim($password);
            $storedPassword = trim((string) $professeur['motdepasse']);
            $passwordMatches = $professeur && (password_verify($password, $storedPassword) || hash_equals($storedPassword, $password));
            if ($passwordMatches) {
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

    private function loginDirecteur($email, $password) {
        try {
            $query = 'SELECT * FROM direction_etude WHERE email = :email LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            $directeur = $stmt->fetch(PDO::FETCH_ASSOC);

            $password = trim($password);
            $storedPassword = trim((string) $directeur['motdepasse']);
            $passwordMatches = $directeur && (password_verify($password, $storedPassword) || hash_equals($storedPassword, $password));
            if ($passwordMatches) {
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

    private function registerEtudiant($nom, $prenom, $email, $password, $niveau = null, $idfiliere = null) {
        try {
            if (empty($niveau) || empty($idfiliere)) {
                return [
                    'success' => false,
                    'message' => 'Veuillez renseigner votre niveau et votre filière.'
                ];
            }

            // Vérifier si l'email existe déjà
            $query = 'SELECT email FROM etudiant WHERE email = :email LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                return [
                    'success' => false,
                    'message' => 'Cet email est déjà utilisé.'
                ];
            }

            $query = 'INSERT INTO etudiant (nom, prenom, idfiliere, niveau, email, motdepasse) VALUES (:nom, :prenom, :idfiliere, :niveau, :email, :motdepasse)';
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':idfiliere' => $idfiliere,
                ':niveau' => $niveau,
                ':email' => $email,
                ':motdepasse' => $password
            ]);

            return [
                'success' => true,
                'message' => 'Inscription réussie. Vous pouvez maintenant vous connecter.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'inscription : ' . $e->getMessage()
            ];
        }
    }

    private function registerProfesseur($nom, $prenom, $email, $password) {
        try {
            $query = 'SELECT email FROM professeur WHERE email = :email LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                return [
                    'success' => false,
                    'message' => 'Cet email est déjà utilisé.'
                ];
            }

            $query = 'INSERT INTO professeur (nom, prenom, email, motdepasse) VALUES (:nom, :prenom, :email, :motdepasse)';
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':motdepasse' => $password
            ]);

            return [
                'success' => true,
                'message' => 'Inscription réussie. Vous pouvez maintenant vous connecter.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'inscription : ' . $e->getMessage()
            ];
        }
    }

    private function registerDirecteur($nom, $prenom, $email, $password) {
        try {
            $query = 'SELECT email FROM direction_etude WHERE email = :email LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                return [
                    'success' => false,
                    'message' => 'Cet email est déjà utilisé.'
                ];
            }

            $query = 'INSERT INTO direction_etude (nom, prenom, email, motdepasse) VALUES (:nom, :prenom, :email, :motdepasse)';
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':motdepasse' => $password
            ]);

            return [
                'success' => true,
                'message' => 'Inscription réussie. Vous pouvez maintenant vous connecter.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'inscription : ' . $e->getMessage()
            ];
        }
    }
}
