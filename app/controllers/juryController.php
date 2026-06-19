<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/jury.php';
require_once __DIR__ . '/../models/commentaire.php';
require_once __DIR__ . '/../models/memoire.php';
require_once __DIR__ . '/../models/notification.php';

class JuryController {
    private $db;
    private $juryModel;
    private $commentaireModel;
    private $memoireModel;
    private $notificationModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->juryModel = new Jury($this->db);
        $this->commentaireModel = new Commentaire($this->db);
        $this->memoireModel = new Memoire($this->db);
        $this->notificationModel = new Notification($this->db);
    }

    public function getAssignedMemoires($idprof) {
        return $this->juryModel->getAssignedMemoires($idprof);
    }

    public function getRecentObservations($idprof) {
        $obsJury = $this->juryModel->getObservations($idprof);
        
        // On récupère aussi les commentaires qui sont des observations
        $sql = "SELECT c.contenu, c.date_commentaire AS date, m.theme AS title, 
                       CONCAT(e.prenom, ' ', e.nom) AS etudiant,
                       CONCAT(p.prenom, ' ', p.nom) AS auteur,
                       'Professeur' AS role
                FROM commentaire c
                JOIN memoire m ON c.idmemoire = m.idmemoire
                JOIN etudiant e ON m.idetudiant = e.idetudiant
                JOIN jury j ON j.idmemoire = m.idmemoire
                JOIN professeur p ON j.idprof = p.idprof
                WHERE j.idprof = :idprof
                ORDER BY c.date_commentaire DESC
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idprof' => $idprof]);
        $obsComm = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $merged = array_merge($obsJury, $obsComm);
        usort($merged, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($merged, 0, 10);
    }

    public function handleValidation($idmemoire, $idprof) {
        $success = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $note = floatval($_POST['note'] ?? 0);
            $juryMembres = trim($_POST['membres_jury'] ?? '');
            $comments = trim($_POST['appreciations'] ?? '');

            if ($note < 0 || $note > 20) {
                $error = "La note saisie doit être comprise entre 0 et 20.";
            } else {
                if ($this->validateThesis($idmemoire, $idprof, $note, $juryMembres, $comments)) {
                    $success = "L'évaluation finale de soutenance a été enregistrée avec succès ! Le mémoire de l'étudiant est validé et archivé.";
                } else {
                    $error = "Erreur lors de la notation.";
                }
            }
        }

        return ['success' => $success, 'error' => $error];
    }

    public function validateThesis($idmemoire, $idprof, $note, $juryMembres, $observations) {
        $success = $this->juryModel->updateObservation($idmemoire, $idprof, $observations);
        
        if ($success) {
            // Mettre à jour la décision
            $decision = "Confirmé (" . floatval($note) . "/20)";
            $sql = "UPDATE jury SET decision = :decision WHERE idmemoire = :idmemoire AND idprof = :idprof";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':decision' => $decision, ':idmemoire' => $idmemoire, ':idprof' => $idprof]);

            // Mettre à jour le statut du mémoire
            $sql_memo = "UPDATE memoire SET statut = 'valide' WHERE idmemoire = :idmemoire";
            $stmt_memo = $this->db->prepare($sql_memo);
            $stmt_memo->execute([':idmemoire' => $idmemoire]);

            // Notification
            $memo = $this->memoireModel->getById($idmemoire);
            if ($memo) {
                $idetudiant = $memo['idetudiant'];
                $msg = "Votre mémoire a été validé par le jury avec la note de " . floatval($note) . "/20.";
                $this->notificationModel->create($msg, $idetudiant, $idprof);
            }
        }
        return $success;
    }

    public function getPendingMemoires($idprof) {
        return $this->juryModel->getAssignedMemoires($idprof); // On pourrait filtrer par statut ici si besoin
    }

    public function getMemoireDetails($idmemoire, $idprof) {
        // Cette méthode devrait combiner les infos du mémoire et du jury
        $memo = $this->memoireModel->getById($idmemoire);
        if (!$memo) return null;

        $sql = "SELECT decision AS note, observation AS appreciations, role_jury AS jury_membres 
                FROM jury WHERE idmemoire = :idmemoire AND idprof = :idprof LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idmemoire' => $idmemoire, ':idprof' => $idprof]);
        $juryInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($juryInfo) {
            $memo = array_merge($memo, $juryInfo);
        }
        return $memo;
    }

    public function getFilteredMemoiresForJury($idprof, $email, $filters) {
        $jury_ok = false;
        $memos = [];

        // 1. Essai de requête active avec JURY et MEMOIRE
        $sql = "SELECT m.idmemoire AS id, m.theme AS theme, m.theme AS titre, 
                       CONCAT(e.prenom, ' ', e.nom) AS etudiant, 
                       CONCAT('GASA-', e.idetudiant) AS matricule,
                       f.nom_filiere AS filiere, e.niveau AS niveau, 
                       m.centre AS centre, m.annee_academique AS annee_acad, 
                       '' AS superviseur, m.statut AS statut, 
                       j.decision AS note, j.observation AS appreciations,
                       j.role_jury
                FROM jury j
                JOIN memoire m ON j.idmemoire = m.idmemoire
                JOIN etudiant e ON m.idetudiant = e.idetudiant
                LEFT JOIN filiere f ON m.idfiliere = f.idfiliere
                WHERE j.idprof = :idprof";
        
        $params = [':idprof' => $idprof];

        if (($filters['niveau'] ?? 'tous') !== 'tous') {
            $sql .= " AND e.niveau = :niveau";
            $params[':niveau'] = $filters['niveau'];
        }

        if (($filters['statut'] ?? 'tous') !== 'tous') {
            $sql .= " AND m.statut = :statut";
            $params[':statut'] = $filters['statut'];
        }

        if (!empty($filters['search'] ?? '')) {
            $sql .= " AND (m.theme LIKE :search OR e.nom LIKE :search OR e.prenom LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY m.idmemoire DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $memos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($memos) > 0) {
            $jury_ok = true;
        }

        // 2. Fallback si échec ou aucun résultat actif sur la table `ancien_memoire`
        if (!$jury_ok) {
            $memos = [];
            $sql_fallback = "SELECT idAM AS id, theme AS theme, theme AS titre,
                           CONCAT(prenomAut, ' ', nomAut) AS etudiant,
                           '' AS matricule,
                           (SELECT nom_filiere FROM filiere WHERE idfiliere = am.idfiliere LIMIT 1) AS filiere,
                           '' AS niveau,
                           (SELECT nomCentre FROM centre WHERE idCentre = am.idCentre LIMIT 1) AS centre,
                           annee_academique AS annee_acad,
                           maitre_memoire AS superviseur,
                           statut,
                           '' AS note,
                           '' AS appreciations,
                           'Président/Examinateur' AS role_jury
                    FROM `ancien_memoire` am
                    WHERE (examinateur = :email OR president_jury = :email)";
            
            $stmt_fallback = $this->db->prepare($sql_fallback);
            $stmt_fallback->execute([':email' => $email]);
            $memos = $stmt_fallback->fetchAll(PDO::FETCH_ASSOC);
        }
        return $memos;
    }
}
