<?php

require_once __DIR__ . '/../../config/database.php';

class AuthController {
    private $db;

    // La méthode qui sera appelée par public/login.php
    // Elle orchestre la connexion, la création de session et la réponse JSON.
    public function processLogin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Récupérer les données du formulaire envoyées en POST
        $userType = $_POST['userType'] ?? null;
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        // 2. Appeler la logique de validation de la connexion
        $result = $this->login($userType, $email, $password);

        // 3. Si la connexion est réussie, créer la session utilisateur
        if ($result['success']) {
            // Régénérer l'ID de session pour des raisons de sécurité
            session_regenerate_id(true);

            // Stocker les informations de l'utilisateur dans la session
            $_SESSION['user'] = $result['user'];
            $_SESSION['logged_in'] = true;
            $_SESSION['userType'] = $result['user']['type'];
            $_SESSION['nom'] = $result['user']['nom'];
            $_SESSION['prenom'] = $result['user']['prenom'];
            $_SESSION['email'] = $result['user']['email'];

            // Stocker l'ID spécifique au rôle pour les vérifications d'accès
            $user_id = $result['user']['id'];
            if ($result['user']['type'] === 'etudiant') $_SESSION['idetudiant'] = $user_id;
            if ($result['user']['type'] === 'professeur') $_SESSION['idprof'] = $user_id;
            if ($result['user']['type'] === 'directeur') $_SESSION['idde'] = $user_id;

            // Ajouter l'URL de redirection vers le contrôleur du tableau de bord
            $result['redirectUrl'] = '/Gestion_memoire/public/dashboard';
        }

        // 4. Renvoyer la réponse au format JSON au script JavaScript du client
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Nettoyage complet du tableau de session
        $_SESSION = [];

        // Destruction du cookie de session si activé
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        header("Location: /Gestion_memoire/public/login.php");
        exit;
    }
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

            if ($etudiant) {
                $password = trim($password);
                $storedPassword = trim((string) $etudiant['motdepasse']);
                $passwordMatches = false;
                $needsRehash = false;

                if (password_verify($password, $storedPassword)) {
                    $passwordMatches = true;
                    if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                        $needsRehash = true;
                    }
                } elseif (hash_equals($storedPassword, $password)) { // Plain text check
                    $passwordMatches = true;
                    $needsRehash = true;
                }

                if ($passwordMatches) {
                    if ($needsRehash) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $rehash_stmt = $this->db->prepare('UPDATE etudiant SET motdepasse = :motdepasse WHERE idetudiant = :idetudiant');
                        $rehash_stmt->execute([':motdepasse' => $newHash, ':idetudiant' => $etudiant['idetudiant']]);
                    }
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
                }
            }
            
            return [
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion au serveur.'
            ];
        }
    }

    private function loginProfesseur($email, $password) {
        try {
            // Trim de sécurité pour éviter les espaces invisibles accidentels
            $email = trim($email);
            $password = trim($password);

            $query = 'SELECT * FROM professeur WHERE email = :email LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            $professeur = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérifier d'abord si l'utilisateur existe
            if ($professeur) {
                $storedPassword = trim((string) $professeur['motdepasse']);
                $passwordMatches = false;
                $needsRehash = false;

                if (password_verify($password, $storedPassword)) {
                    $passwordMatches = true;
                    // Si le hachage n'est pas à jour avec les derniers algos/options, on le met à jour
                    if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                        $needsRehash = true;
                    }
                } elseif (hash_equals($storedPassword, $password) || hash_equals($storedPassword, md5($password)) || hash_equals($storedPassword, sha1($password))) {
                    // Ancien mot de passe (texte brut, md5, sha1) - Connexion réussie, mais mise à jour nécessaire
                    $passwordMatches = true;
                    $needsRehash = true;
                }

                if ($passwordMatches) {
                    // Si le mot de passe a besoin d'être mis à jour, on le fait maintenant.
                    if ($needsRehash) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $rehash_stmt = $this->db->prepare('UPDATE professeur SET motdepasse = :motdepasse WHERE idprof = :idprof');
                        $rehash_stmt->execute([':motdepasse' => $newHash, ':idprof' => $professeur['idprof']]);
                    }
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
                }
            }

            // Retourner l'erreur de connexion standard
            return [
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion au serveur.' // Ne pas exposer les messages d'erreur détaillés
            ];
        }
    }
    private function loginDirecteur($email, $password) {
        try {
            $query = 'SELECT * FROM direction_etude WHERE email = :email LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            $directeur = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($directeur) {
                $password = trim($password);
                $storedPassword = trim((string) $directeur['motdepasse']);
                $passwordMatches = false;
                $needsRehash = false;

                if (password_verify($password, $storedPassword)) {
                    $passwordMatches = true;
                    if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                        $needsRehash = true;
                    }
                } elseif (hash_equals($storedPassword, $password)) { // Plain text check
                    $passwordMatches = true;
                    $needsRehash = true;
                }
                if ($passwordMatches) {
                    if ($needsRehash) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $rehash_stmt = $this->db->prepare('UPDATE direction_etude SET motdepasse = :motdepasse WHERE idde = :idde');
                        $rehash_stmt->execute([':motdepasse' => $newHash, ':idde' => $directeur['idde']]);
                    }
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
                }
            }
            return [
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion au serveur.'
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

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = 'INSERT INTO etudiant (nom, prenom, idfiliere, niveau, email, motdepasse) VALUES (:nom, :prenom, :idfiliere, :niveau, :email, :motdepasse)';
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':idfiliere' => $idfiliere,
                ':niveau' => $niveau,
                ':email' => $email,
                ':motdepasse' => $hashedPassword
            ]);

            return [
                'success' => true,
                'message' => 'Inscription réussie. Vous pouvez maintenant vous connecter.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur serveur lors de l\'inscription.'
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

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = 'INSERT INTO professeur (nom, prenom, email, motdepasse) VALUES (:nom, :prenom, :email, :motdepasse)';
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':motdepasse' => $hashedPassword
            ]);

            return [
                'success' => true,
                'message' => 'Inscription réussie. Vous pouvez maintenant vous connecter.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur serveur lors de l\'inscription.'
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

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = 'INSERT INTO direction_etude (nom, prenom, email, motdepasse) VALUES (:nom, :prenom, :email, :motdepasse)';
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':motdepasse' => $hashedPassword
            ]);

            return [
                'success' => true,
                'message' => 'Inscription réussie. Vous pouvez maintenant vous connecter.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur serveur lors de l\'inscription.'
            ];
        }
    }
}
