<?php

class Notification {

    public function countNonLues($pdo, $idetudiant) {

        $sql = "SELECT COUNT(*) as total
                FROM notification
                WHERE idetudiant = ? AND statut_lecture = 0";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idetudiant]);

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>