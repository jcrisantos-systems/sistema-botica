<?php
class Producto {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $query = "SELECT p.*, c.nombre as categoria, l.nombre as laboratorio 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.id_categoria = c.id 
                  LEFT JOIN laboratorios l ON p.id_laboratorio = l.id 
                  WHERE p.estado = 1 ORDER BY p.nombre_comercial";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllAdmin() {
        $query = "SELECT p.*, c.nombre as categoria, l.nombre as laboratorio 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.id_categoria = c.id 
                  LEFT JOIN laboratorios l ON p.id_laboratorio = l.id 
                  ORDER BY p.nombre_comercial";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO productos (codigo_barras, nombre_generico, nombre_comercial, concentracion, forma_farmaceutica, id_laboratorio, id_categoria, precio_compra, precio_venta, margen_ganancia, unidad_medida, requiere_receta, stock_minimo, fraccionable, unidades_por_caja, unidad_fraccion, precio_fraccion) 
                  VALUES (:cb, :ng, :nc, :conc, :ff, :idl, :idc, :pc, :pv, :mg, :um, :rr, :sm, :frac, :upc, :ufrac, :pfrac)";
        $stmt = $this->conn->prepare($query);
        $this->bindParams($stmt, $data);
        return $stmt->execute();
    }

    public function update($data) {
        $query = "UPDATE productos SET codigo_barras=:cb, nombre_generico=:ng, nombre_comercial=:nc, concentracion=:conc, forma_farmaceutica=:ff, id_laboratorio=:idl, id_categoria=:idc, precio_compra=:pc, precio_venta=:pv, margen_ganancia=:mg, unidad_medida=:um, requiere_receta=:rr, stock_minimo=:sm, fraccionable=:frac, unidades_por_caja=:upc, unidad_fraccion=:ufrac, precio_fraccion=:pfrac WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $this->bindParams($stmt, $data);
        $stmt->bindParam(':id', $data['id']);
        return $stmt->execute();
    }
    
    private function bindParams($stmt, $data) {
        $stmt->bindParam(':cb', $data['codigo_barras']);
        $stmt->bindParam(':ng', $data['nombre_generico']);
        $stmt->bindParam(':nc', $data['nombre_comercial']);
        $stmt->bindParam(':conc', $data['concentracion']);
        $stmt->bindParam(':ff', $data['forma_farmaceutica']);
        $stmt->bindParam(':idl', $data['id_laboratorio']);
        $stmt->bindParam(':idc', $data['id_categoria']);
        $stmt->bindParam(':pc', $data['precio_compra']);
        $stmt->bindParam(':pv', $data['precio_venta']);
        $stmt->bindParam(':mg', $data['margen_ganancia']);
        $stmt->bindParam(':um', $data['unidad_medida']);
        $stmt->bindParam(':rr', $data['requiere_receta']);
        $stmt->bindParam(':sm', $data['stock_minimo']);
        $stmt->bindParam(':frac', $data['fraccionable']);
        $stmt->bindParam(':upc', $data['unidades_por_caja']);
        $stmt->bindParam(':ufrac', $data['unidad_fraccion']);
        $stmt->bindParam(':pfrac', $data['precio_fraccion']);
    }

    public function toggleEstado($id) {
        $stmt = $this->conn->prepare("UPDATE productos SET estado = CASE WHEN estado = 1 THEN 0 ELSE 1 END WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
