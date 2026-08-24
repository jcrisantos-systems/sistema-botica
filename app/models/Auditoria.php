<?php
class Auditoria {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function registrarAcceso($id_usuario, $accion) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $query = "INSERT INTO audit_accesos (id_usuario, accion, ip_address, user_agent) VALUES (:uid, :acc, :ip, :agent)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $id_usuario);
        $stmt->bindParam(':acc', $accion);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':agent', $agent);
        return $stmt->execute();
    }

    public function registrarAccion($id_usuario, $modulo, $accion, $descripcion, $monto = 0) {
        $query = "INSERT INTO audit_acciones (id_usuario, modulo, accion, descripcion, monto_afectado) 
                  VALUES (:uid, :mod, :acc, :des, :mon)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $id_usuario);
        $stmt->bindParam(':mod', $modulo);
        $stmt->bindParam(':acc', $accion);
        $stmt->bindParam(':des', $descripcion);
        $stmt->bindParam(':mon', $monto);
        return $stmt->execute();
    }

    public function getAccesos($limit = 100) {
        $query = "SELECT a.*, u.usuario as username, u.nombres 
                  FROM audit_accesos a 
                  INNER JOIN usuarios u ON a.id_usuario = u.id 
                  ORDER BY a.fecha DESC LIMIT :lim";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAcciones($limit = 100) {
        $query = "SELECT a.*, u.usuario as username, u.nombres 
                  FROM audit_acciones a 
                  INNER JOIN usuarios u ON a.id_usuario = u.id 
                  ORDER BY a.fecha DESC LIMIT :lim";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
