<?php
class Database {
    private $host = 'localhost';
    private $port = 3307;
    private $db_name = 'visas_db';
    private $username = 'devuser';
    private $password = 'webcoder01@2905';
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=$this->host;port=$this->port;dbname=$this->db_name",
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            die("Connection error: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>
