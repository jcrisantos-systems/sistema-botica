<?php
class Compra {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $query = "SELECT c.*, p.razon_social as proveedor, u.nombres as cajero 
                  FROM compras c 
                  INNER JOIN proveedores p ON c.id_proveedor = p.id 
                  INNER JOIN usuarios u ON c.id_usuario = u.id 
                  ORDER BY c.fecha_compra DESC, c.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getDetallesConLotes($id_compra) {
        $query = "SELECT cd.*, p.nombre_comercial, l.id as id_lote, l.codigo_lote, l.fecha_vencimiento, l.cantidad_disponible as stock_lote_actual
                  FROM compra_detalles cd 
                  INNER JOIN productos p ON cd.id_producto = p.id 
                  LEFT JOIN inventario_lotes l ON l.id_compra_detalle = cd.id
                  WHERE cd.id_compra = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_compra);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCompraPorId($id) {
        $query = "SELECT c.*, p.razon_social as proveedor 
                  FROM compras c 
                  INNER JOIN proveedores p ON c.id_proveedor = p.id 
                  WHERE c.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método principal para registrar transaccionalmente una compra entera
    public function registrarCompra($cabecera, $detalles, $id_usuario) {
        try {
            $this->conn->beginTransaction();

            // 1. Insertar la Compra
            $query = "INSERT INTO compras (id_proveedor, id_usuario, tipo_comprobante, serie_comprobante, num_comprobante, fecha_compra, impuesto, total, estado) 
                      VALUES (:prov, :usr, :tip, :ser, :num, :fec, :imp, :tot, :est)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':prov', $cabecera['id_proveedor']);
            $stmt->bindParam(':usr', $id_usuario);
            $stmt->bindParam(':tip', $cabecera['tipo_comprobante']);
            $stmt->bindParam(':ser', $cabecera['serie_comprobante']);
            $stmt->bindParam(':num', $cabecera['num_comprobante']);
            $stmt->bindParam(':fec', $cabecera['fecha_compra']);
            $stmt->bindParam(':imp', $cabecera['impuesto']);
            $stmt->bindParam(':tot', $cabecera['total']);
            $stmt->bindParam(':est', $cabecera['estado']); // 'Completada' o 'Pendiente'
            $stmt->execute();
            
            $id_compra = $this->conn->lastInsertId();

            // Instanciar el de inventario pasándole la misma conexión para la transacción
            require_once 'Inventario.php';
            $inventario = new Inventario($this->conn);
            $motivo = "Compra " . $cabecera['tipo_comprobante'] . " " . $cabecera['serie_comprobante'] . "-" . $cabecera['num_comprobante'];

            // 2. Insertar Detalles e Inventario (Solo si está Completada)
            foreach ($detalles as $det) {
                // Insertar detalle siempre
                $sDet = $this->conn->prepare("INSERT INTO compra_detalles (id_compra, id_producto, cantidad, precio_unitario, subtotal) 
                                              VALUES (:ic, :ip, :cant, :pre, :sub)");
                $sDet->bindParam(':ic', $id_compra);
                $sDet->bindParam(':ip', $det['id_producto']);
                $sDet->bindParam(':cant', $det['cantidad']);
                $sDet->bindParam(':pre', $det['precio_unitario']);
                $sDet->bindParam(':sub', $det['subtotal']);
                $sDet->execute();
                
                $id_detalle = $this->conn->lastInsertId();

                if ($cabecera['estado'] == 'Completada') {
                    // Actualizar precio de compra del producto (opcional, solicitado por UX)
                    if (isset($det['actualizar_precio']) && $det['actualizar_precio'] == 1) {
                        $upd = $this->conn->prepare("UPDATE productos SET precio_compra = :precio WHERE id = :id");
                        $upd->bindParam(':precio', $det['precio_unitario']);
                        $upd->bindParam(':id', $det['id_producto']);
                        $upd->execute();
                    }

                    // Obtener factor fraccionario
                    $prodQuery = $this->conn->prepare("SELECT unidades_por_caja, fraccionable FROM productos WHERE id = :id");
                    $prodQuery->bindParam(':id', $det['id_producto']);
                    $prodQuery->execute();
                    $prodData = $prodQuery->fetch(PDO::FETCH_ASSOC);
                    $factor = ($prodData['fraccionable'] == 1 && $prodData['unidades_por_caja'] > 0) ? $prodData['unidades_por_caja'] : 1;
                    $cantidad_real = $det['cantidad'] * $factor;

                    // Registrar la entrada en Inventario
                    $inventario->registrarEntrada(
                        $det['id_producto'],
                        $id_usuario,
                        $cantidad_real,
                        $motivo,
                        $det['lote'],
                        $det['vencimiento'],
                        $id_detalle
                    );
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function registrarDevolucion($cabecera, $detalles, $id_usuario) {
        try {
            $this->conn->beginTransaction();

            // 1. Insertar Cabecera de Devolución
            $query = "INSERT INTO compras_devoluciones (id_compra, id_usuario, num_documento_prov, motivo, total_devuelto, fecha_devolucion) 
                      VALUES (:idc, :usr, :num, :mot, :tot, :fec)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':idc', $cabecera['id_compra']);
            $stmt->bindParam(':usr', $id_usuario);
            $stmt->bindParam(':num', $cabecera['num_documento_prov']);
            $stmt->bindParam(':mot', $cabecera['motivo']);
            $stmt->bindParam(':tot', $cabecera['total_devuelto']);
            $stmt->bindParam(':fec', $cabecera['fecha_devolucion']);
            $stmt->execute();

            $id_devolucion = $this->conn->lastInsertId();
            $motivo_kardex = "Devolución NC " . $cabecera['num_documento_prov'] . " de Compra ID " . $cabecera['id_compra'];

            // 2. Procesar Detalles
            foreach ($detalles as $det) {
                // Verificar stock disponible en el lote proactivamente (integridad)
                $stmtL = $this->conn->prepare("SELECT cantidad_disponible FROM inventario_lotes WHERE id = :idl FOR UPDATE");
                $stmtL->bindParam(':idl', $det['id_lote']);
                $stmtL->execute();
                $lote = $stmtL->fetch(PDO::FETCH_ASSOC);

                if (!$lote || $lote['cantidad_disponible'] < $det['cantidad']) {
                    throw new Exception("Stock insuficiente en el lote para devolver " . $det['cantidad'] . " unidades.");
                }

                // Insertar detalle de devolución
                $stmtD = $this->conn->prepare("INSERT INTO compras_devolucion_detalles (id_devolucion, id_producto, id_lote, cantidad, precio_costo, subtotal) 
                                               VALUES (:idv, :idp, :idl, :can, :pre, :sub)");
                $stmtD->bindParam(':idv', $id_devolucion);
                $stmtD->bindParam(':idp', $det['id_producto']);
                $stmtD->bindParam(':idl', $det['id_lote']);
                $stmtD->bindParam(':can', $det['cantidad']);
                $stmtD->bindParam(':pre', $det['precio_costo']);
                $stmtD->bindParam(':sub', $det['subtotal']);
                $stmtD->execute();

                // Actualizar lote
                $updLote = $this->conn->prepare("UPDATE inventario_lotes SET cantidad_disponible = cantidad_disponible - :can WHERE id = :idl");
                $updLote->bindParam(':can', $det['cantidad']);
                $updLote->bindParam(':idl', $det['id_lote']);
                $updLote->execute();

                // Obtener stock general del producto
                $stmtP = $this->conn->prepare("SELECT stock_actual FROM productos WHERE id = :idp FOR UPDATE");
                $stmtP->bindParam(':idp', $det['id_producto']);
                $stmtP->execute();
                $nuevo_stock = $stmtP->fetch(PDO::FETCH_ASSOC)['stock_actual'] - $det['cantidad'];

                // Actualizar stock general
                $updProd = $this->conn->prepare("UPDATE productos SET stock_actual = :nst WHERE id = :idp");
                $updProd->bindParam(':nst', $nuevo_stock);
                $updProd->bindParam(':idp', $det['id_producto']);
                $updProd->execute();

                // Registrar salida en Kardex
                $stmtK = $this->conn->prepare("INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, saldo_actual) 
                                               VALUES (:idp, :usr, 'SALIDA', :mot, :can, :sld)");
                $stmtK->bindParam(':idp', $det['id_producto']);
                $stmtK->bindParam(':usr', $id_usuario);
                $stmtK->bindParam(':mot', $motivo_kardex);
                $stmtK->bindParam(':can', $det['cantidad']);
                $stmtK->bindParam(':sld', $nuevo_stock);
                $stmtK->execute();
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log('DB Error en Compra::registrarDevolucion: ' . $e->getMessage());
            return "Ocurrió un error de base de datos al registrar la devolución. Por favor, intenta nuevamente.";
        } catch (Exception $e) {
            $this->conn->rollBack();
            // Mensaje de negocio lanzado deliberadamente más arriba (p.ej. stock insuficiente): ya es amigable.
            return $e->getMessage();
        }
    }

    public function procesarRecepcion($id_compra, $id_usuario, $lotes_data) {
        try {
            $this->conn->beginTransaction();

            // 1. Verificar estado y obtener compra
            $compra = $this->getCompraPorId($id_compra);
            if (!$compra || $compra['estado'] !== 'Pendiente') {
                throw new Exception("La compra no existe o ya fue procesada.");
            }

            // 2. Obtener detalles de la compra
            $detalles = $this->getDetallesConLotes($id_compra);
            
            require_once 'Inventario.php';
            $inventario = new Inventario($this->conn);
            $motivo = "Recepción de Orden " . $compra['tipo_comprobante'] . " " . $compra['serie_comprobante'] . "-" . $compra['num_comprobante'];

            // 3. Procesar cada detalle para inventario
            foreach ($detalles as $det) {
                $id_detalle = $det['id'];
                
                // Buscar datos de lote enviados desde el form de recepción
                $lote_info = null;
                foreach($lotes_data as $ld) {
                    if($ld['id_detalle'] == $id_detalle) {
                        $lote_info = $ld;
                        break;
                    }
                }

                if (!$lote_info) {
                    throw new Exception("Faltan datos de lote para el producto: " . $det['nombre_comercial']);
                }

                // Obtener factor fraccionario
                $prodQuery = $this->conn->prepare("SELECT unidades_por_caja, fraccionable FROM productos WHERE id = :id");
                $prodQuery->bindParam(':id', $det['id_producto']);
                $prodQuery->execute();
                $prodData = $prodQuery->fetch(PDO::FETCH_ASSOC);
                $factor = ($prodData['fraccionable'] == 1 && $prodData['unidades_por_caja'] > 0) ? $prodData['unidades_por_caja'] : 1;
                $cantidad_real = $det['cantidad'] * $factor;

                // Registrar entrada
                $inventario->registrarEntrada(
                    $det['id_producto'],
                    $id_usuario,
                    $cantidad_real,
                    $motivo,
                    $lote_info['lote'],
                    $lote_info['vencimiento'],
                    $id_detalle
                );
            }

            // 4. Cambiar estado de la compra
            $stmt = $this->conn->prepare("UPDATE compras SET estado = 'Completada' WHERE id = :id");
            $stmt->bindParam(':id', $id_compra);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log('DB Error en Compra::procesarRecepcion: ' . $e->getMessage());
            return "Ocurrió un error de base de datos al procesar la recepción. Por favor, intenta nuevamente.";
        } catch (Exception $e) {
            $this->conn->rollBack();
            // Mensaje de negocio lanzado deliberadamente más arriba: ya es amigable.
            return $e->getMessage();
        }
    }
}
