<?php
class Database {
    private $host = "localhost";
    private $db_name = "botica_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            $this->conn->exec("SET NAMES utf8mb4");
        } catch(PDOException $exception) {
            error_log("Error de conexión a la base de datos: " . $exception->getMessage());
            die("No se pudo conectar con la base de datos. Intente nuevamente más tarde.");
        }
        return $this->conn;
    }
}
?>
