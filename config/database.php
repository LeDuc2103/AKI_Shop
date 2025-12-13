<?php
/**
 * Database Connection Class
 * PHP 5.2.6 Compatible
 */
class Database {
    private $host = "localhost:3307";
    private $db_name = "tuc";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * Kết nối đến database
     * @return PDO connection object
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // Kết nối PDO cho MySQL
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            
            // Set UTF-8 encoding
            $this->conn->exec("SET NAMES utf8");
            
            // Set error mode
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
