<?php
// config/database.php
class Database {
    private $host = "localhost";
    private $port = "3307";
    private $db_name = "barangay_online_services";
    private $username = "root";
    private $password = "";  //
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $this->conn;
        } catch(PDOException $e) {
            die("❌ Connection failed: " . $e->getMessage());
        }
    }
}
?>