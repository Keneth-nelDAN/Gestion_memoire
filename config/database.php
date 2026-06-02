<?php
<<<<<<< HEAD

=======
>>>>>>> bf59ca851dd3875f42f95168629b5b5dd69aeb72
class Database {
    private $host = 'localhost';
    private $db_name = 'gestion_memoires';
    private $user = 'root';
    private $password = '';
    private $conn;

    // Établir la connexion à la base de données
    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=127.0.0.1;dbname=' . $this->db_name . ';charset=utf8mb4',
                $this->user,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Erreur de connexion : ' . $e->getMessage();
        }

        return $this->conn;
    }
}
