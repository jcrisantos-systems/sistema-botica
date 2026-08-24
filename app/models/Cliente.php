<?php
class Cliente {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE estado = 1 ORDER BY nombres");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE id = :id AND estado = 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO clientes (tipo_documento, num_documento, nombres, telefono, direccion) VALUES (:tipo, :num, :nom, :tel, :dir)");
        $stmt->bindParam(':tipo', $data['tipo_documento']);
        $stmt->bindParam(':num', $data['num_documento']);
        $stmt->bindParam(':nom', $data['nombres']);
        $stmt->bindParam(':tel', $data['telefono']);
        $stmt->bindParam(':dir', $data['direccion']);
        return $stmt->execute();
    }

    public function update($data) {
        $stmt = $this->conn->prepare("UPDATE clientes SET tipo_documento = :tipo, num_documento = :num, nombres = :nom, telefono = :tel, direccion = :dir WHERE id = :id");
        $stmt->bindParam(':tipo', $data['tipo_documento']);
        $stmt->bindParam(':num', $data['num_documento']);
        $stmt->bindParam(':nom', $data['nombres']);
        $stmt->bindParam(':tel', $data['telefono']);
        $stmt->bindParam(':dir', $data['direccion']);
        $stmt->bindParam(':id', $data['id']);
        return $stmt->execute();
    }

    public function delete($id) {
        if($id == 1) return false; // El 1 es el público en general protegido
        $stmt = $this->conn->prepare("UPDATE clientes SET estado = 0 WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function actualizarPuntos($id, $puntos_delta) {
        if($id == 1) return true; // Público general no acumula puntos
        // $puntos_delta puede ser negativo si se descuentan por canje, o por anulación
        $stmt = $this->conn->prepare("UPDATE clientes SET puntos_acumulados = puntos_acumulados + :puntos WHERE id = :id");
        $stmt->bindParam(':puntos', $puntos_delta);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
