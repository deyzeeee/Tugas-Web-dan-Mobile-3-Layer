<?php
class Database {
    private $host = "localhost";
    private $db_name = "db_customer_management";
    private $username = "root";
    private $password = ""; // Default XAMPP kosong
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Koneksi Database Gagal: " . $e->getMessage()
            ]);
            exit;
        }
        return $this->conn;
    }
}
