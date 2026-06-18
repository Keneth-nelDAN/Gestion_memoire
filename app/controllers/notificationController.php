<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/notification.php';

class NotificationController {
    private $db;
    private $notificationModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->notificationModel = new Notification($this->db);
    }

    public function getEtudiantNotifications($idetudiant) {
        return $this->notificationModel->getByEtudiant($idetudiant);
    }

    public function getProfesseurNotifications($idprof) {
        $notifs = $this->notificationModel->getByProfesseur($idprof);
        $formattedNotifs = [];
        foreach ($notifs as $row) {
            $formattedNotifs[] = [
                'id' => intval($row['id']),
                'type' => 'system',
                'title' => 'Notification Académique',
                'message' => htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8'),
                'time' => date('d/m/Y H:i', strtotime($row['time'])),
                'read' => intval($row['statut_lecture']) === 1
            ];
        }
        return $formattedNotifs;
    }

    public function markAllAsReadForEtudiant($idetudiant) {
        return $this->notificationModel->markAllAsReadByEtudiant($idetudiant);
    }

    public function getAll() {
        $sql = "SELECT n.idnotification, n.message, n.statut_lecture, n.date_notification,
                       CONCAT(e.prenom, ' ', e.nom) AS etudiant,
                       CONCAT(p.prenom, ' ', p.nom) AS professeur
                FROM notification n
                LEFT JOIN etudiant e ON e.idetudiant = n.idetudiant
                LEFT JOIN professeur p ON p.idprof = n.idprof
                ORDER BY n.date_notification DESC, n.idnotification DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAllAsRead() {
        $sql = "UPDATE notification SET statut_lecture = 1 WHERE statut_lecture = 0";
        return $this->db->query($sql);
    }
}
