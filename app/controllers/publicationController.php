<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../views/direction_etude/de_helpers.php';

class PublicationController
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

    public function create()
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Gestion_memoire/app/views/direction_etude/publier_memoire.php');
            exit;
        }

        $idde = isset($_SESSION['idde']) ? (int)$_SESSION['idde'] : null;
        $upload_dir = realpath(__DIR__ . '/../memoire/uploads/memoires/') . '/';
        $error = '';

        $nomAut = trim($_POST['nomAut'] ?? '');
        $prenomAut = trim($_POST['prenomAut'] ?? '');
        $theme = trim($_POST['theme'] ?? '');
        $idfiliere = (int)($_POST['idfiliere'] ?? 0);
        $idCentre = ($_POST['idCentre'] ?? '') !== '' ? (int)$_POST['idCentre'] : null;
        $annee = trim($_POST['annee_academique'] ?? '');
        $maitre = trim($_POST['maitre_memoire'] ?? '');
        $examinateur = trim($_POST['examinateur'] ?? '');
        $president = trim($_POST['president_jury'] ?? '');
        $statut = trim($_POST['statut'] ?? 'publie');
        $source = 'de_unitaire';

        if ($theme === '' || $nomAut === '' || $idfiliere <= 0) {
            $_SESSION['flash']['error'] = "Veuillez renseigner au minimum le thème, l'auteur et la filière.";
        } else {
            $fichier = $this->upload_file_for_memoire($_FILES['fichier'] ?? null, $upload_dir, $error);
            if ($fichier !== false) {
                $stmt = $this->db->prepare("INSERT INTO ancien_memoire (nomAut, prenomAut, theme, idfiliere, idCentre, annee_academique, maitre_memoire, examinateur, president_jury, fichier, statut, source, publie_par) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$nomAut, $prenomAut, $theme, $idfiliere, $idCentre, $annee, $maitre, $examinateur, $president, $fichier, $statut, $source, $idde])) {
                    $_SESSION['flash']['success'] = "Le mémoire a été publié avec succès.";
                } else {
                    $_SESSION['flash']['error'] = "Insertion impossible : " . $stmt->errorInfo()[2];
                }
            } else {
                $_SESSION['flash']['error'] = $error;
            }
        }

        header('Location: /Gestion_memoire/app/views/direction_etude/publier_memoire.php');
        exit;
    }

    public function createBatch()
    {
        // La logique de `publier_lots.php` serait déplacée ici de la même manière.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Gestion_memoire/app/views/direction_etude/publier_lots.php');
            exit;
        }

        $idde = isset($_SESSION['idde']) ? (int)$_SESSION['idde'] : null;
        if (!$idde) {
            header('Location: /Gestion_memoire/public/login.php');
            exit;
        }

        $upload_dir = realpath(__DIR__ . '/../memoire/uploads/memoires/') . '/';

        $idfiliere = (int)($_POST['batch_idfiliere'] ?? 0);
        $idCentre = ($_POST['batch_idCentre'] ?? '') !== '' ? (int)$_POST['batch_idCentre'] : null;
        $annee = trim($_POST['batch_annee'] ?? '');
        $maitre = trim($_POST['batch_maitre'] ?? '');
        $examinateur = trim($_POST['batch_examinateur'] ?? '');
        $president = trim($_POST['batch_president'] ?? '');
        $titres = preg_split('/\R/', trim($_POST['batch_titres'] ?? '')) ?: [];
        $auteurs = preg_split('/\R/', trim($_POST['batch_auteurs'] ?? '')) ?: [];
        $inserted = 0;

        if ($idfiliere <= 0 || empty($_FILES['fichiers']['name'][0])) {
            $_SESSION['flash']['error'] = "Sélectionnez une filière et au moins un fichier.";
        } else {
            foreach ($_FILES['fichiers']['name'] as $index => $name) {
                $file = [
                    'name' => $_FILES['fichiers']['name'][$index],
                    'type' => $_FILES['fichiers']['type'][$index],
                    'tmp_name' => $_FILES['fichiers']['tmp_name'][$index],
                    'error' => $_FILES['fichiers']['error'][$index],
                    'size' => $_FILES['fichiers']['size'][$index],
                ];
                $local_error = '';
                $fichier = $this->upload_file_for_memoire($file, $upload_dir, $local_error);
                if ($fichier === false) {
                    $_SESSION['flash']['error'] = $local_error; // On garde la dernière erreur
                    continue;
                }
                $theme = trim($titres[$index] ?? '');
                if ($theme === '') {
                    $theme = str_replace(['_', '-'], ' ', pathinfo($name, PATHINFO_FILENAME));
                }
                $author = $this->split_author($auteurs[$index] ?? '', $index + 1);
                $statut = 'publie';
                $source = 'de_lot';
                $stmt = $this->db->prepare("INSERT INTO ancien_memoire (nomAut, prenomAut, theme, idfiliere, idCentre, annee_academique, maitre_memoire, examinateur, president_jury, fichier, statut, source, publie_par) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$author['nom'], $author['prenom'], $theme, $idfiliere, $idCentre, $annee, $maitre, $examinateur, $president, $fichier, $statut, $source, $idde])) {
                    $inserted++;
                }
            }
            if ($inserted > 0) {
                $_SESSION['flash']['success'] = $inserted . " mémoire(s) publié(s) avec succès.";
            } elseif (empty($_SESSION['flash']['error'])) {
                $_SESSION['flash']['error'] = "Aucun mémoire n'a pu être publié.";
            }
        }

        header('Location: /Gestion_memoire/app/views/direction_etude/publier_lots.php');
        exit;
    }

    public function delete($id)
    {
        if (!isset($_SESSION['idde'])) {
            header('Location: /Gestion_memoire/public/login.php');
            exit;
        }

        $id = (int)$id;
        if ($id <= 0) {
            header('Location: /Gestion_memoire/app/views/direction_etude/dashboard_de.php?status=invalid_id');
            exit;
        }

        // 1. Récupération du nom du fichier
        $stmt = $this->db->prepare("SELECT fichier FROM `ancien_memoire` WHERE idAM = ?");
        $stmt->execute([$id]);
        $memoire = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($memoire) {
            $nom_fichier = $memoire['fichier'];
            $upload_dir = realpath(__DIR__ . '/../memoire/uploads/memoires/') . '/';
            $chemin_complet = $upload_dir . $nom_fichier;

            // Suppression du fichier physique
            if (!empty($nom_fichier) && file_exists($chemin_complet)) {
                unlink($chemin_complet);
            }

            // 2. Suppression dans la base de données
            $delete_stmt = $this->db->prepare("DELETE FROM `ancien_memoire` WHERE idAM = ?");

            if ($delete_stmt->execute([$id])) {
                header('Location: /Gestion_memoire/app/views/direction_etude/dashboard_de.php?status=deleted');
            } else {
                header('Location: /Gestion_memoire/app/views/direction_etude/dashboard_de.php?status=error_delete');
            }
        } else {
            header('Location: /Gestion_memoire/app/views/direction_etude/dashboard_de.php?status=not_found');
        }
        exit;
    }

    private function split_author($line, $fallback_index) {
        $line = trim($line);
        if ($line === '') {
            return ['nom' => 'Auteur ' . $fallback_index, 'prenom' => ''];
        }
        $parts = preg_split('/\s+/', $line);
        if (count($parts) === 1) {
            return ['nom' => $parts[0], 'prenom' => ''];
        }
        $nom = array_pop($parts);
        return ['nom' => $nom, 'prenom' => implode(' ', $parts)];
    }

        private function upload_file_for_memoire($file, $upload_dir, &$error)
        {
            if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
                $error = "Veuillez sélectionner un fichier.";
                return false;
            }
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = "Erreur pendant l'envoi du fichier. Code: " . $file['error'];
                return false;
            }
            if ($file['size'] > 12 * 1024 * 1024) {
                $error = "Le fichier doit peser 12 Mo maximum.";
                return false;
            }
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0775, true);
            }
    
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            if ($mime !== 'application/pdf') {
                $error = "Seuls les fichiers de type PDF sont autorisés (type détecté: $mime).";
                return false;
            }
    
            $name = 'memoire_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.pdf';
            $destination = $upload_dir . $name;
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $error = "Impossible d'enregistrer le fichier sur le serveur.";
                return false;
            }
            return $name;
        }

    public function getSharedData()
    {
        $filieres_stmt = $this->db->query("SELECT idfiliere, nom_filiere FROM filiere ORDER BY nom_filiere ASC");
        $filieres = $filieres_stmt->fetchAll(PDO::FETCH_ASSOC);

        $centres_stmt = $this->db->query("SELECT idCentre, nomCentre FROM centre ORDER BY FIELD(nomCentre, 'Agla', 'Akpakpa', 'Gbegamey', 'Calavi', 'Porto-novo'), nomCentre");
        $centres = $centres_stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['filieres' => $filieres, 'centres' => $centres];
    }
}
?>