<?php
class Dashboard {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getMetricasHoy() {
        $hoy = date('Y-m-d');
        
        // 1. Ingresos y Cantidad Hoy
        $stmtVentas = $this->conn->prepare("SELECT SUM(total) as ingresos, COUNT(id) as transacciones FROM ventas WHERE DATE(fecha_venta) = :hoy AND estado = 'Completada'");
        $stmtVentas->bindParam(':hoy', $hoy);
        $stmtVentas->execute();
        $ventas_data = $stmtVentas->fetch(PDO::FETCH_ASSOC);

        // 2. Clientes Activos
        $stmt_c = $this->conn->prepare("SELECT COUNT(id) as total FROM clientes WHERE estado = 1 AND id > 1");
        $stmt_c->execute();
        $clientes_total = $stmt_c->fetch(PDO::FETCH_ASSOC)['total'];

        // 3. Medicamentos en Riesgo FEFO (< 90 días)
        $limite = date('Y-m-d', strtotime('+90 days'));
        $stmt_l = $this->conn->prepare("SELECT COUNT(id) as cant FROM inventario_lotes WHERE fecha_vencimiento <= :limite AND cantidad_disponible > 0 AND estado = 1");
        $stmt_l->bindParam(':limite', $limite);
        $stmt_l->execute();
        $riesgo_fefo = $stmt_l->fetch(PDO::FETCH_ASSOC)['cant'];

        // 4. Catálogo Master Count
        $stmt_p = $this->conn->prepare("SELECT COUNT(id) as total FROM productos WHERE estado = 1");
        $stmt_p->execute();
        $productos_total = $stmt_p->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'ingresos_hoy' => $ventas_data['ingresos'] ?: 0.00,
            'ventas_hoy' => $ventas_data['transacciones'] ?: 0,
            'clientes_total' => $clientes_total,
            'lotes_riesgo' => $riesgo_fefo,
            'productos_total' => $productos_total
        ];
    }
    
    public function getGraficoSemanal() {
        // Últimos 7 días
        $query = "SELECT DATE(fecha_venta) as fecha, SUM(total) as suma_dia 
                  FROM ventas 
                  WHERE fecha_venta >= DATE(NOW()) - INTERVAL 6 DAY 
                  AND estado = 'Completada'
                  GROUP BY DATE(fecha_venta)
                  ORDER BY fecha ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $fechas = [];
        $valores = [];
        
        // Formatearemos días (Lun, Mar, Mie...)
        $dias = ['Sun'=>'Dom','Mon'=>'Lun','Tue'=>'Mar','Wed'=>'Mié','Thu'=>'Jue','Fri'=>'Vie','Sat'=>'Sáb'];
        
        foreach ($res as $fila) {
            $dia_str = date('D', strtotime($fila['fecha']));
            $fechas[] = $dias[$dia_str] . ' ' . date('d', strtotime($fila['fecha']));
            $valores[] = (float)$fila['suma_dia'];
        }
        
        return [
            'labels' => $fechas,
            'data' => $valores
        ];
    }
    
    public function getMediosPago() {
        $query = "SELECT metodo_pago as label, SUM(total) as value FROM ventas WHERE estado = 'Completada' GROUP BY metodo_pago";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
