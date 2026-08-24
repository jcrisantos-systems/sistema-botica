<?php
class Proveedor {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM proveedores WHERE estado = 1 ORDER BY razon_social");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO proveedores (ruc, razon_social, representante, telefono, direccion) VALUES (:ruc, :razon_social, :representante, :telefono, :direccion)");
        $stmt->bindParam(':ruc', $data['ruc']);
        $stmt->bindParam(':razon_social', $data['razon_social']);
        $stmt->bindParam(':representante', $data['representante']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':direccion', $data['direccion']);
        return $stmt->execute();
    }

    public function update($data) {
        $stmt = $this->conn->prepare("UPDATE proveedores SET ruc = :ruc, razon_social = :razon_social, representante = :representante, telefono = :telefono, direccion = :direccion WHERE id = :id");
        $stmt->bindParam(':ruc', $data['ruc']);
        $stmt->bindParam(':razon_social', $data['razon_social']);
        $stmt->bindParam(':representante', $data['representante']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->bindParam(':direccion', $data['direccion']);
        $stmt->bindParam(':id', $data['id']);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("UPDATE proveedores SET estado = 0 WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
