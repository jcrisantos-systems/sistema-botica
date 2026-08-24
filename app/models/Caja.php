<?php
class Caja {
    private $conn;
    private $table_name = "cajas";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getCajaAbiertaPorUsuario($usuario_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE usuario_id = :usuario_id AND estado = 1 LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function abrirCaja($usuario_id, $monto_inicial) {
        // Verificar si ya tiene caja abierta
        if ($this->getCajaAbiertaPorUsuario($usuario_id)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " SET usuario_id = :usuario_id, fecha_apertura = NOW(), monto_inicial = :monto_inicial, estado = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':monto_inicial', $monto_inicial);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getResumenActual($caja_id) {
        // Obtener ingresos desde la tabla ventas para esta caja (solo ventas válidas)
        $query = "SELECT 
                    SUM(CASE WHEN metodo_pago = 'Efectivo' THEN total ELSE 0 END) as efectivo,
                    SUM(CASE WHEN metodo_pago != 'Efectivo' THEN total ELSE 0 END) as transferencias
                  FROM ventas WHERE caja_id = :caja_id AND estado != 'Anulada'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':caja_id', $caja_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Movimientos manuales
        $queryM = "SELECT 
                    SUM(CASE WHEN tipo = 'INGRESO' THEN monto ELSE 0 END) as ingresos_extras,
                    SUM(CASE WHEN tipo = 'EGRESO' THEN monto ELSE 0 END) as egresos
                  FROM caja_movimientos WHERE caja_id = :caja_id";
        $stmtM = $this->conn->prepare($queryM);
        $stmtM->bindParam(':caja_id', $caja_id);
        $stmtM->execute();
        $rowM = $stmtM->fetch(PDO::FETCH_ASSOC);
        
        return [
            'ingresos_efectivo' => $row['efectivo'] ?? 0,
            'ingresos_transferencia' => $row['transferencias'] ?? 0,
            'ingresos_extras' => $rowM['ingresos_extras'] ?? 0,
            'egresos' => $rowM['egresos'] ?? 0
        ];
    }

    public function cerrarCaja($caja_id, $monto_final_real, $observacion) {
        $resumen = $this->getResumenActual($caja_id);
        
        // El cajero debe tener en la caja: Saldo Inicial + Ventas en Efectivo
        // Las ventas por transferencia no cuentan en la gaveta física
        
        $queryCaja = "SELECT monto_inicial FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($queryCaja);
        $stmt->bindParam(':id', $caja_id);
        $stmt->execute();
        $caja = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $monto_inicial = $caja['monto_inicial'];
        // Ajustamos la caja esperada = Inicial + Venta Efectivo + Ingresos Extra - Retiros
        $monto_final_esperado = $monto_inicial + $resumen['ingresos_efectivo'] + $resumen['ingresos_extras'] - $resumen['egresos'];
        $diferencia = $monto_final_real - $monto_final_esperado;
        
        $query = "UPDATE " . $this->table_name . " SET 
                  fecha_cierre = NOW(), 
                  ingresos_efectivo = :ingresos_efectivo,
                  ingresos_transferencia = :ingresos_transferencia,
                  monto_final_esperado = :monto_final_esperado,
                  monto_final_real = :monto_final_real,
                  diferencia = :diferencia,
                  observacion = :observacion,
                  estado = 0 
                  WHERE id = :id";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':ingresos_efectivo', $resumen['ingresos_efectivo']);
        $stmt->bindParam(':ingresos_transferencia', $resumen['ingresos_transferencia']);
        $stmt->bindParam(':monto_final_esperado', $monto_final_esperado);
        $stmt->bindParam(':monto_final_real', $monto_final_real);
        $stmt->bindParam(':diferencia', $diferencia);
        $stmt->bindParam(':observacion', $observacion);
        $stmt->bindParam(':id', $caja_id);
        
        return $stmt->execute();
    }
    
    public function getHistorial($fecha_inicio, $fecha_fin) {
        $query = "SELECT c.*, u.nombres, u.apellidos 
                  FROM " . $this->table_name . " c 
                  JOIN usuarios u ON c.usuario_id = u.id 
                  WHERE DATE(c.fecha_apertura) >= :inicio AND DATE(c.fecha_apertura) <= :fin 
                  ORDER BY c.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':inicio', $fecha_inicio);
        $stmt->bindParam(':fin', $fecha_fin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($caja_id) {
        $query = "SELECT c.*, u.nombres, u.apellidos 
                  FROM " . $this->table_name . " c 
                  JOIN usuarios u ON c.usuario_id = u.id 
                  WHERE c.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $caja_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -- Nuevos Métodos para Movimientos Manuales --
    public function registrarMovimiento($caja_id, $tipo, $monto, $motivo) {
        $query = "INSERT INTO caja_movimientos (caja_id, tipo, monto, motivo) VALUES (:caja, :tipo, :monto, :motivo)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':caja', $caja_id);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':motivo', $motivo);
        return $stmt->execute();
    }

    public function getMovimientos($caja_id) {
        $query = "SELECT * FROM caja_movimientos WHERE caja_id = :id ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $caja_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
