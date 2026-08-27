<?php
class User {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login($username, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE usuario = :usuario AND estado = 1 LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    public function updateLastLogin($id) {
        $query = "UPDATE " . $this->table_name . " SET ultimo_login = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT u.*, r.nombre as rol_nombre FROM " . $this->table_name . " u LEFT JOIN roles r ON u.rol_id = r.id ORDER BY u.id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Verifica si ya existe otro usuario con ese nombre de acceso (columna UNIQUE en BD).
    // $excludeId permite ignorar el propio registro al editar.
    public function existeUsuario($usuario, $excludeId = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE usuario = :usuario";
        if ($excludeId !== null) {
            $query .= " AND id != :id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario", $usuario);
        if ($excludeId !== null) {
            $stmt->bindParam(":id", $excludeId);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Verifica si ya existe otro usuario con ese correo, normalizando mayúsculas/minúsculas
    // y espacios (columna sin UNIQUE en BD todavía; único control hasta la Fase F).
    // $excludeId permite ignorar el propio registro al editar.
    public function existeEmail($email, $excludeId = null) {
        $query = "SELECT 1 FROM " . $this->table_name . " WHERE LOWER(TRIM(email)) = LOWER(TRIM(:email))";
        if ($excludeId !== null) {
            $query .= " AND id != :id";
        }
        $query .= " LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        if ($excludeId !== null) {
            $stmt->bindParam(':id', $excludeId);
        }
        $stmt->execute();
        return $stmt->fetchColumn() !== false;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " SET nombres=:nombres, apellidos=:apellidos, usuario=:usuario, password=:password, email=:email, rol_id=:rol_id, estado=:estado";
        $stmt = $this->conn->prepare($query);
        
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $stmt->bindParam(":nombres", $data['nombres']);
        $stmt->bindParam(":apellidos", $data['apellidos']);
        $stmt->bindParam(":usuario", $data['usuario']);
        $stmt->bindParam(":password", $hash);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":rol_id", $data['rol_id']);
        $stmt->bindParam(":estado", $data['estado']);
        
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET nombres=:nombres, apellidos=:apellidos, usuario=:usuario, email=:email, rol_id=:rol_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":nombres", $data['nombres']);
        $stmt->bindParam(":apellidos", $data['apellidos']);
        $stmt->bindParam(":usuario", $data['usuario']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":rol_id", $data['rol_id']);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function updatePassword($id, $password) {
        $query = "UPDATE " . $this->table_name . " SET password=:password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $hash);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function toggleEstado($id) {
        $query = "UPDATE " . $this->table_name . " SET estado = IF(estado=1, 0, 1) WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
