<?php
class Inventario {
    private $conn;

    public function __construct($dbConn = null) {
        if ($dbConn) {
            $this->conn = $dbConn; // permitir inyectar conexion para transacciones
        } else {
            $db = new Database();
            $this->conn = $db->getConnection();
        }
    }

    public function getLotesActivos() {
        // Trae los lotes que aún tienen stock disponible o están por vencer
        $query = "SELECT l.*, p.nombre_comercial, p.forma_farmaceutica, p.concentracion, c.nombre as categoria
                  FROM inventario_lotes l
                  INNER JOIN productos p ON l.id_producto = p.id
                  LEFT JOIN categorias c ON p.id_categoria = c.id
                  WHERE l.cantidad_disponible > 0 AND l.estado = 1
                  ORDER BY l.fecha_vencimiento ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getLotesProximosVencer($dias = 90) {
        $query = "SELECT p.nombre_comercial as producto, l.codigo_lote as lote, l.fecha_vencimiento, l.cantidad_disponible as stock
                  FROM inventario_lotes l
                  INNER JOIN productos p ON l.id_producto = p.id
                  WHERE l.cantidad_disponible > 0 
                  AND l.estado = 1 
                  AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                  ORDER BY l.fecha_vencimiento ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProductosBajoStock($limite = 20) {
        $query = "SELECT id, nombre_comercial as producto, stock_actual as stock, unidad_medida 
                  FROM productos 
                  WHERE stock_actual <= :limite AND estado = 1
                  ORDER BY stock_actual ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductosBajoStockMinimo() {
        $query = "SELECT id, nombre_comercial as producto, stock_actual as stock, stock_minimo, unidad_medida
                  FROM productos
                  WHERE stock_actual <= stock_minimo AND estado = 1
                  ORDER BY stock_actual ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKardex($id_producto = null) {
        $query = "SELECT k.*, p.nombre_comercial, u.nombres as usuario 
                  FROM kardex k
                  INNER JOIN productos p ON k.id_producto = p.id
                  INNER JOIN usuarios u ON k.id_usuario = u.id ";
        if ($id_producto) {
            $query .= "WHERE k.id_producto = :id_producto ";
        }
        $query .= "ORDER BY k.id DESC LIMIT 500";
        
        $stmt = $this->conn->prepare($query);
        if ($id_producto) $stmt->bindParam(':id_producto', $id_producto);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registra la entrada (Kardex, Lote y actualización de Stock) - ASUME ESTAR EN TRANSACCION
    public function registrarEntrada($id_producto, $id_usuario, $cantidad, $motivo, $lote, $vencimiento, $id_compra_detalle = null) {
        // 1. Obtener stock actual
        $stmt = $this->conn->prepare("SELECT stock_actual FROM productos WHERE id = :id FOR UPDATE");
        $stmt->bindParam(':id', $id_producto);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $saldo_anterior = $row ? $row['stock_actual'] : 0;
        $nuevo_saldo = $saldo_anterior + $cantidad;

        // 2. Insertar Lote
        $stmt2 = $this->conn->prepare("INSERT INTO inventario_lotes (id_producto, id_compra_detalle, codigo_lote, fecha_vencimiento, cantidad_inicial, cantidad_disponible) 
                                       VALUES (:prod, :det, :lote, :venc, :cant, :cant)");
        $stmt2->bindParam(':prod', $id_producto);
        $stmt2->bindParam(':det', $id_compra_detalle);
        $stmt2->bindParam(':lote', $lote);
        $stmt2->bindParam(':venc', $vencimiento);
        $stmt2->bindParam(':cant', $cantidad);
        $stmt2->execute();

        // 3. Insertar Kardex
        $stmt3 = $this->conn->prepare("INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, saldo_actual) 
                                       VALUES (:prod, :usr, 'ENTRADA', :motivo, :cant, :saldo)");
        $stmt3->bindParam(':prod', $id_producto);
        $stmt3->bindParam(':usr', $id_usuario);
        $stmt3->bindParam(':motivo', $motivo);
        $stmt3->bindParam(':cant', $cantidad);
        $stmt3->bindParam(':saldo', $nuevo_saldo);
        $stmt3->execute();

        // 4. Actualizar Stock en Producto
        $stmt4 = $this->conn->prepare("UPDATE productos SET stock_actual = :saldo WHERE id = :prod");
        $stmt4->bindParam(':saldo', $nuevo_saldo);
        $stmt4->bindParam(':prod', $id_producto);
        $stmt4->execute();
        
        return true;
    }

    // --- MÓDULO DE INVENTARIO FÍSICO (FASE 11) ---

    public function iniciarAuditoria($id_usuario, $observaciones = '') {
        try {
            $this->conn->beginTransaction();
            
            // 1. Crear Cabecera
            $stmt = $this->conn->prepare("INSERT INTO inventario_auditorias (id_usuario, observaciones) VALUES (:uid, :obs)");
            $stmt->bindParam(':uid', $id_usuario);
            $stmt->bindParam(':obs', $observaciones);
            $stmt->execute();
            $id_audit = $this->conn->lastInsertId();

            // 2. Capturar "Foto" de lotes activos (con stock > 0)
            $stmt2 = $this->conn->prepare("INSERT INTO inventario_auditoria_detalles (id_auditoria, id_lote, stock_sistema)
                                           SELECT :id, id, cantidad_disponible FROM inventario_lotes WHERE cantidad_disponible > 0 AND estado = 1");
            $stmt2->bindParam(':id', $id_audit);
            $stmt2->execute();

            $this->conn->commit();
            return $id_audit;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getDetallesAuditoria($id_audit) {
        $query = "SELECT d.*, p.nombre_comercial, l.codigo_lote, l.fecha_vencimiento
                  FROM inventario_auditoria_detalles d
                  INNER JOIN inventario_lotes l ON d.id_lote = l.id
                  INNER JOIN productos p ON l.id_producto = p.id
                  WHERE d.id_auditoria = :id
                  ORDER BY p.nombre_comercial ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_audit);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function finalizarAuditoria($id_audit, $conteos, $id_usuario) {
        try {
            $this->conn->beginTransaction();

            foreach ($conteos as $lote_id => $fisico) {
                // 1. Obtener datos actuales del detalle y del producto
                $stmt = $this->conn->prepare("SELECT d.*, l.id_producto FROM inventario_auditoria_detalles d 
                                              INNER JOIN inventario_lotes l ON d.id_lote = l.id 
                                              WHERE d.id_auditoria = :ida AND d.id_lote = :idl");
                $stmt->bindParam(':ida', $id_audit);
                $stmt->bindParam(':idl', $lote_id);
                $stmt->execute();
                $det = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$det) continue;

                $fisico = (int)$fisico;
                $sistema = (int)$det['stock_sistema'];
                $dif = $fisico - $sistema;

                // 2. Actualizar detalle de auditoría
                $updDet = $this->conn->prepare("UPDATE inventario_auditoria_detalles SET stock_fisico = :fis, diferencia = :dif WHERE id = :id");
                $updDet->bindParam(':fis', $fisico);
                $updDet->bindParam(':dif', $dif);
                $updDet->bindParam(':id', $det['id']);
                $updDet->execute();

                if ($dif != 0) {
                    // 3. Actualizar Lote
                    $updLote = $this->conn->prepare("UPDATE inventario_lotes SET cantidad_disponible = :fis WHERE id = :idl");
                    $updLote->bindParam(':fis', $fisico);
                    $updLote->bindParam(':idl', $lote_id);
                    $updLote->execute();

                    // 4. Registrar en Kardex el AJUSTE
                    $motivo = "Ajuste por Inventario Físico #" . $id_audit;
                    $tipo = ($dif > 0) ? 'AJUSTE' : 'SALIDA'; // Podría ser ENTRADA/SALIDA, usamos AJUSTE como comodín o ENUM según DB

                    // Recalcular saldo parcial para el Kardex
                    $stmtS = $this->conn->prepare("SELECT stock_actual FROM productos WHERE id = :idp FOR UPDATE");
                    $stmtS->bindParam(':idp', $det['id_producto']);
                    $stmtS->execute();
                    $stock_anterior = $stmtS->fetch(PDO::FETCH_ASSOC)['stock_actual'];
                    $nuevo_saldo = $stock_anterior + $dif;

                    $stmtK = $this->conn->prepare("INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, saldo_actual) 
                                                   VALUES (:idp, :usr, 'AJUSTE', :mot, :cant, :sld)");
                    $stmtK->bindParam(':idp', $det['id_producto']);
                    $stmtK->bindParam(':usr', $id_usuario);
                    $stmtK->bindParam(':mot', $motivo);
                    $stmtK->bindParam(':cant', $dif);
                    $stmtK->bindParam(':sld', $nuevo_saldo);
                    $stmtK->execute();

                    // 5. Actualizar Stock del Producto
                    $updP = $this->conn->prepare("UPDATE productos SET stock_actual = :sld WHERE id = :idp");
                    $updP->bindParam(':sld', $nuevo_saldo);
                    $updP->bindParam(':idp', $det['id_producto']);
                    $updP->execute();
                }
            }

            // 6. Marcar auditoría como finalizada
            $stmtF = $this->conn->prepare("UPDATE inventario_auditorias SET estado = 'Finalizada', fecha_fin = NOW() WHERE id = :id");
            $stmtF->bindParam(':id', $id_audit);
            $stmtF->execute();

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log('DB Error en Inventario::finalizarAuditoria: ' . $e->getMessage());
            return "Ocurrió un error de base de datos al finalizar la auditoría. Por favor, intenta nuevamente.";
        } catch (Exception $e) {
            $this->conn->rollBack();
            return $e->getMessage();
        }
    }
}
