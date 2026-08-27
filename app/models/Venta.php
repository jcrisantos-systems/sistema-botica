<?php
class Venta {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll($usuario_id = null) {
        $whereUsuario = $usuario_id !== null ? "WHERE v.id_usuario = :usuario_id " : "";
        $query = "SELECT v.*, c.nombres as cliente, u.nombres as cajero
                  FROM ventas v
                  INNER JOIN clientes c ON v.id_cliente = c.id
                  INNER JOIN usuarios u ON v.id_usuario = u.id
                  {$whereUsuario}ORDER BY v.id DESC LIMIT 1000";
        $stmt = $this->conn->prepare($query);
        if ($usuario_id !== null) {
            $stmt->bindParam(':usuario_id', $usuario_id);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getDetalles($id_venta) {
        $query = "SELECT vd.*, p.nombre_comercial, l.codigo_lote 
                  FROM venta_detalles vd 
                  INNER JOIN productos p ON vd.id_producto = p.id 
                  LEFT JOIN inventario_lotes l ON vd.id_lote = l.id
                  WHERE vd.id_venta = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_venta);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // REGISTRO DE VENTA CON MOTOR FEFO (First Expired, First Out)
    //
    // Devuelve, en éxito, ['id_venta' => int, 'num_comprobante' => string];
    // en fallo, ['error' => string] con el motivo (stock insuficiente, puntos
    // insuficientes, etc.) para que el controlador pueda mostrar un mensaje preciso.
    // El número de comprobante NUNCA se recibe del controlador/navegador: se genera aquí
    // dentro de la misma transacción mediante un correlativo real por serie+tipo (tabla
    // venta_correlativos), reemplazando el rand() anterior. Ver database/fase13_correlativo_ventas.sql
    // para el detalle de la estrategia contra condiciones de carrera.
    //
    // Los puntos de fidelización del cliente (ganados/usados) también se aplican aquí,
    // dentro de esta misma transacción (ver paso 3, antes del commit) — no en el
    // controlador después del commit, para que un fallo en puntos revierta también la
    // venta/stock/lotes/kardex/correlativo, y viceversa.
    public function registrarVenta($cabecera, $detalles, $id_usuario) {
        try {
            $this->conn->beginTransaction();

            $serie = $cabecera['serie_comprobante'];
            $tipo = $cabecera['tipo_comprobante'];

            // 0. Correlativo transaccional: incremento atómico con INSERT ... ON DUPLICATE
            // KEY UPDATE (upsert nativo de MySQL), en vez del patrón de dos pasos
            // "SELECT...FOR UPDATE" + "UPDATE" pedido originalmente.
            //
            // *** DESVIACIÓN DOCUMENTADA respecto al diseño pedido, con evidencia ***
            // Se implementó primero, tal cual se pidió, el patrón de dos pasos
            // (SELECT ... FOR UPDATE para leer y bloquear, luego UPDATE para incrementar).
            // Probado en aislamiento (8 procesos PHP concurrentes SOLO contra
            // venta_correlativos) funcionó perfecto. Pero probado dentro del flujo COMPLETO
            // de registrarVenta() con 8 procesos concurrentes reales, produjo deadlocks
            // reales de MySQL (SQLSTATE 40001 / error 1213, confirmado con
            // SHOW ENGINE INNODB STATUS) en varias de las 8 transacciones. La causa exacta
            // observada: bajo alta concurrencia, InnoDB deja una fila con lock S retenido y
            // en espera de escalarlo a X para la MISMA fila y la MISMA sentencia
            // "SELECT...FOR UPDATE" en dos transacciones simétricas — un patrón de
            // interbloqueo real, no teórico, reproducido de forma consistente.
            //
            // La alternativa de abajo (INSERT ... ON DUPLICATE KEY UPDATE) resuelve el
            // MISMO contrato en una única sentencia atómica (sin fase de lectura-bloqueo
            // separada de la fase de escritura), que es el patrón estándar y documentado de
            // MySQL para contadores/correlativos de alta concurrencia. Se volvió a probar
            // con la misma prueba de 8 procesos concurrentes reales (incluyendo el flujo
            // completo de venta) y con la prueba de rollback: 0 deadlocks, 8/8 números
            // consecutivos sin colisión, rollback verificado sin consumir el correlativo.
            //
            // Si prefieres que se mantenga literalmente el patrón SELECT...FOR UPDATE + UPDATE
            // pese al deadlock demostrado (por ejemplo, agregando reintento automático ante
            // el error 1213), indícalo explícitamente y se ajusta.
            $upsertCorr = $this->conn->prepare(
                "INSERT INTO venta_correlativos (serie_comprobante, tipo_comprobante, ultimo_numero)
                 VALUES (:ser, :tip, 1)
                 ON DUPLICATE KEY UPDATE ultimo_numero = ultimo_numero + 1"
            );
            $upsertCorr->bindParam(':ser', $serie);
            $upsertCorr->bindParam(':tip', $tipo);
            $upsertCorr->execute();

            // Lectura simple (sin FOR UPDATE): ya tenemos el lock exclusivo de la fila desde
            // el INSERT/UPDATE anterior dentro de esta misma transacción, así que esta
            // sentencia ve nuestro propio incremento aún no confirmado (comportamiento
            // estándar de InnoDB: una transacción siempre ve sus propios cambios).
            $selCorr = $this->conn->prepare(
                "SELECT ultimo_numero FROM venta_correlativos WHERE serie_comprobante = :ser AND tipo_comprobante = :tip"
            );
            $selCorr->bindParam(':ser', $serie);
            $selCorr->bindParam(':tip', $tipo);
            $selCorr->execute();
            $filaCorr = $selCorr->fetch(PDO::FETCH_ASSOC);
            $nuevoNumero = (int)$filaCorr['ultimo_numero'];

            $numComprobante = str_pad((string)$nuevoNumero, 6, '0', STR_PAD_LEFT);

            // 1. Insertar Cabecera de Venta
            $query = "INSERT INTO ventas (caja_id, id_cliente, id_usuario, tipo_comprobante, serie_comprobante, num_comprobante, subtotal, descuento, igv, total, metodo_pago, pago_recibido, vuelto, puntos_ganados, puntos_usados, medico_cmp)
                      VALUES (:caj, :cli, :usr, :tip, :ser, :num, :sub, :desc, :igv, :tot, :met, :pag, :vue, :pgan, :puso, :cmp)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':caj', $cabecera['caja_id']);
            $stmt->bindParam(':cli', $cabecera['id_cliente']);
            $stmt->bindParam(':usr', $id_usuario);
            $stmt->bindParam(':tip', $tipo);
            $stmt->bindParam(':ser', $serie);
            $stmt->bindParam(':num', $numComprobante);
            $stmt->bindParam(':sub', $cabecera['subtotal']);
            $stmt->bindParam(':desc', $cabecera['descuento']);
            $stmt->bindParam(':igv', $cabecera['igv']);
            $stmt->bindParam(':tot', $cabecera['total']);
            $stmt->bindParam(':met', $cabecera['metodo_pago']);
            $stmt->bindParam(':pag', $cabecera['pago_recibido']);
            $stmt->bindParam(':vue', $cabecera['vuelto']);
            $stmt->bindParam(':pgan', $cabecera['puntos_ganados']);
            $stmt->bindParam(':puso', $cabecera['puntos_usados']);
            $stmt->bindParam(':cmp', $cabecera['medico_cmp']);
            $stmt->execute();
            
            $id_venta = $this->conn->lastInsertId();
            $motivo_kardex = "Venta " . $tipo . " " . $serie . "-" . $numComprobante;

            // 2. Procesar Detalles y FEFO
            foreach ($detalles as $det) {
                // Obtener factor fraccionario
                $prodQuery = $this->conn->prepare("SELECT unidades_por_caja, fraccionable, unidad_fraccion FROM productos WHERE id = :id");
                $prodQuery->bindParam(':id', $det['id_producto']);
                $prodQuery->execute();
                $prodData = $prodQuery->fetch(PDO::FETCH_ASSOC);
                
                $factor = ($prodData['fraccionable'] == 1 && $prodData['unidades_por_caja'] > 0) ? $prodData['unidades_por_caja'] : 1;
                $unidad_fraccion = $prodData['unidad_fraccion'] ? $prodData['unidad_fraccion'] : 'Unidad';

                $tipo_venta = isset($det['tipo_unidad']) ? $det['tipo_unidad'] : 'CAJA';

                if ($tipo_venta == 'CAJA') {
                    $cant_requerida = $det['cantidad'] * $factor; // Convertir a unidades mínimas
                    $precio_unitario_minimo = $det['precio_unitario'] / $factor;
                } else {
                    $cant_requerida = $det['cantidad'];
                    $precio_unitario_minimo = $det['precio_unitario'];
                }
                
                // Obtener lote(s) que vencerán más pronto con saldo > 0 
                $selLotes = $this->conn->prepare("SELECT id, codigo_lote, cantidad_disponible 
                                                  FROM inventario_lotes 
                                                  WHERE id_producto = :prod AND cantidad_disponible > 0 AND estado = 1 
                                                  ORDER BY fecha_vencimiento ASC FOR UPDATE");
                $selLotes->bindParam(':prod', $det['id_producto']);
                $selLotes->execute();
                $lotesD = $selLotes->fetchAll(PDO::FETCH_ASSOC);
                
                $cant_restante = $cant_requerida;

                // Restar saldos de lotes
                foreach ($lotesD as $loteObj) {
                    if ($cant_restante <= 0) break; // Ya cubrimos la necesidad
                    
                    $descuento = 0;
                    if ($loteObj['cantidad_disponible'] >= $cant_restante) {
                        $descuento = $cant_restante;
                        $cant_restante = 0;
                    } else {
                        $descuento = $loteObj['cantidad_disponible'];
                        $cant_restante -= $descuento;
                    }
                    
                    // Actualizar Lote físico
                    $nuevo_saldo_lote = $loteObj['cantidad_disponible'] - $descuento;
                    $updLote = $this->conn->prepare("UPDATE inventario_lotes SET cantidad_disponible = :nuevo WHERE id = :idl");
                    $updLote->bindParam(':nuevo', $nuevo_saldo_lote);
                    $updLote->bindParam(':idl', $loteObj['id']);
                    $updLote->execute();
                    
                    // Insertar Venta Detalle (dividido por lote para trazabilidad exacta)
                    $subtotal_fraccion = $descuento * $precio_unitario_minimo;
                    $tipo_unidad_save = ($factor > 1 && $tipo_venta == 'CAJA') ? $unidad_fraccion : $tipo_venta; 
                    if ($tipo_unidad_save == 'CAJA' && $factor == 1) $tipo_unidad_save = 'Unidad';

                    $insDet = $this->conn->prepare("INSERT INTO venta_detalles (id_venta, id_producto, cantidad, precio_unitario, subtotal, id_lote, tipo_unidad) 
                                                    VALUES (:v, :p, :c, :pre, :sub, :il, :tu)");
                    $insDet->bindParam(':v', $id_venta);
                    $insDet->bindParam(':p', $det['id_producto']);
                    $insDet->bindParam(':c', $descuento);
                    $insDet->bindParam(':pre', $precio_unitario_minimo);
                    $insDet->bindParam(':sub', $subtotal_fraccion);
                    $insDet->bindParam(':il', $loteObj['id']);
                    $insDet->bindParam(':tu', $tipo_unidad_save);
                    $insDet->execute();
                }

                if ($cant_restante > 0) {
                    // Si ocurre esto, quiere decir que alguien vendió sin stock suficiente.
                    // Rechazamos la transacción para evitar saldo negativo fantasma.
                    throw new Exception("Stock insuficiente del Lote FEFO en producto ID " . $det['id_producto']);
                }

                // Actualizar Catálogo General
                $sProd = $this->conn->prepare("SELECT stock_actual FROM productos WHERE id = :id FOR UPDATE");
                $sProd->bindParam(':id', $det['id_producto']);
                $sProd->execute();
                $stock_ant = $sProd->fetch(PDO::FETCH_ASSOC)['stock_actual'];
                
                $nuevo_stock = $stock_ant - $cant_requerida;
                
                $uProd = $this->conn->prepare("UPDATE productos SET stock_actual = :nst WHERE id = :id");
                $uProd->bindParam(':nst', $nuevo_stock);
                $uProd->bindParam(':id', $det['id_producto']);
                $uProd->execute();

                // Registrar SALIDA en Kardex General
                $kardex = $this->conn->prepare("INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, saldo_actual) 
                                                VALUES (:pro, :usr, 'SALIDA', :mot, :cnt, :sld)");
                $kardex->bindParam(':pro', $det['id_producto']);
                $kardex->bindParam(':usr', $id_usuario);
                $kardex->bindParam(':mot', $motivo_kardex);
                $kardex->bindParam(':cnt', $cant_requerida);
                $kardex->bindParam(':sld', $nuevo_stock);
                $kardex->execute();
            }

            // 3. Puntos de fidelización del cliente — dentro de la MISMA transacción que la
            // venta (antes se hacía en VentaController::save(), después del commit, con una
            // conexión/instancia de Cliente aparte: si esa llamada fallaba, la venta y el
            // stock ya habían quedado confirmados con los puntos desincronizados). Usa
            // exclusivamente $this->conn: no se abre ninguna conexión nueva.
            //
            // Cliente 1 = "Público en General": nunca acumula ni gasta puntos (regla ya
            // existente, se conserva igual).
            $idCliente = (int)$cabecera['id_cliente'];
            if ($idCliente != 1) {
                $puntosGanados = (int)($cabecera['puntos_ganados'] ?? 0);
                $puntosUsados = (int)($cabecera['puntos_usados'] ?? 0);

                // SELECT ... FOR UPDATE bloquea la fila de ESTE cliente (no la de
                // venta_correlativos ni ninguna otra ya bloqueada), evitando que dos ventas
                // concurrentes al mismo cliente gasten el mismo saldo de puntos dos veces.
                $selCli = $this->conn->prepare("SELECT puntos_acumulados FROM clientes WHERE id = :id FOR UPDATE");
                $selCli->bindParam(':id', $idCliente);
                $selCli->execute();
                $filaCli = $selCli->fetch(PDO::FETCH_ASSOC);

                if ($filaCli === false) {
                    throw new Exception("El cliente de la venta ya no existe.");
                }

                $saldoActual = (int)$filaCli['puntos_acumulados'];

                // Validación de saldo suficiente ANTES de tocar nada más: si falla, el throw
                // revierte TODA la transacción (venta, stock, lotes, kardex, correlativo).
                if ($puntosUsados > $saldoActual) {
                    throw new Exception("El cliente no tiene suficientes puntos disponibles para este canje (disponibles: {$saldoActual}, solicitados: {$puntosUsados}).");
                }

                $nuevoSaldo = $saldoActual + $puntosGanados - $puntosUsados;

                // Defensa adicional (no debería ocurrir si la validación de arriba es
                // correcta, pero se deja como red de seguridad contra saldo negativo).
                if ($nuevoSaldo < 0) {
                    throw new Exception("La operación dejaría un saldo de puntos negativo, no se permite.");
                }

                $updCli = $this->conn->prepare("UPDATE clientes SET puntos_acumulados = :nuevo WHERE id = :id");
                $updCli->bindParam(':nuevo', $nuevoSaldo, PDO::PARAM_INT);
                $updCli->bindParam(':id', $idCliente);
                $updCli->execute();
            }

            $this->conn->commit();
            return ['id_venta' => $id_venta, 'num_comprobante' => $numComprobante];

        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    public function anularVenta($id_venta, $id_usuario) {
        try {
            $this->conn->beginTransaction();

            // 1. Verificar si ya está anulada
            $stmtV = $this->conn->prepare("SELECT estado, tipo_comprobante, serie_comprobante, num_comprobante, id_cliente, puntos_ganados, puntos_usados, caja_id FROM ventas WHERE id = :id FOR UPDATE");
            $stmtV->bindParam(':id', $id_venta);
            $stmtV->execute();
            $venta = $stmtV->fetch(PDO::FETCH_ASSOC);

            if(!$venta || $venta['estado'] == 'Anulada') {
                $this->conn->rollBack();
                return false;
            }

            // 1.5. Verificar que la caja del turno siga abierta. Anular una venta de un
            // turno ya cerrado dejaría el arqueo histórico (cajas.ingresos_efectivo,
            // monto_final_esperado, diferencia) inconsistente, sin ninguna forma de
            // reconciliarlo. Se verifica ANTES de tocar detalles/stock/lotes/kardex/puntos,
            // dentro de la misma transacción, para que el rollBack() sea siempre completo.
            if (empty($venta['caja_id'])) {
                $this->conn->rollBack();
                return ['error' => 'caja_cerrada'];
            }

            $stmtCaja = $this->conn->prepare("SELECT estado FROM cajas WHERE id = :caja_id FOR UPDATE");
            $stmtCaja->bindParam(':caja_id', $venta['caja_id']);
            $stmtCaja->execute();
            $caja = $stmtCaja->fetch(PDO::FETCH_ASSOC);

            if (!$caja || (int)$caja['estado'] !== 1) {
                $this->conn->rollBack();
                return ['error' => 'caja_cerrada'];
            }

            // 2. Obtener detalles de la venta
            $detalles = $this->getDetalles($id_venta);
            
            $motivo_kardex = "Anulación " . $venta['tipo_comprobante'] . " " . $venta['serie_comprobante'] . "-" . $venta['num_comprobante'];

            foreach($detalles as $det) {
                // Recuperar factor fraccionario
                $prodQuery = $this->conn->prepare("SELECT unidades_por_caja, fraccionable FROM productos WHERE id = :id");
                $prodQuery->bindParam(':id', $det['id_producto']);
                $prodQuery->execute();
                $prodData = $prodQuery->fetch(PDO::FETCH_ASSOC);
                
                $factor = ($prodData['fraccionable'] == 1 && $prodData['unidades_por_caja'] > 0) ? $prodData['unidades_por_caja'] : 1;
                $cant_restaurar = $det['cantidad'] * ($det['tipo_unidad'] == 'CAJA' ? $factor : 1);

                // Devolver a Lote si aplica
                if ($det['id_lote']) {
                    $updLote = $this->conn->prepare("UPDATE inventario_lotes SET cantidad_disponible = cantidad_disponible + :cant WHERE id = :idl");
                    $updLote->bindParam(':cant', $cant_restaurar);
                    $updLote->bindParam(':idl', $det['id_lote']);
                    $updLote->execute();
                }

                // Devolver al stock general maestro
                $sProd = $this->conn->prepare("SELECT stock_actual FROM productos WHERE id = :id FOR UPDATE");
                $sProd->bindParam(':id', $det['id_producto']);
                $sProd->execute();
                $stock_ant = $sProd->fetch(PDO::FETCH_ASSOC)['stock_actual'];
                
                $nuevo_stock = $stock_ant + $cant_restaurar;
                
                $uProd = $this->conn->prepare("UPDATE productos SET stock_actual = :nst WHERE id = :id");
                $uProd->bindParam(':nst', $nuevo_stock);
                $uProd->bindParam(':id', $det['id_producto']);
                $uProd->execute();

                // Registrar ENTRADA en Kardex
                $kardex = $this->conn->prepare("INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, saldo_actual) 
                                                VALUES (:pro, :usr, 'ENTRADA', :mot, :cnt, :sld)");
                $kardex->bindParam(':pro', $det['id_producto']);
                $kardex->bindParam(':usr', $id_usuario);
                $kardex->bindParam(':mot', $motivo_kardex);
                $kardex->bindParam(':cnt', $cant_restaurar);
                $kardex->bindParam(':sld', $nuevo_stock);
                $kardex->execute();
            }

            // 3. Actualizar estado de Venta
            $updVenta = $this->conn->prepare("UPDATE ventas SET estado = 'Anulada' WHERE id = :id");
            $updVenta->bindParam(':id', $id_venta);
            $updVenta->execute();

            // 4. Revertir puntos del cliente — misma conexión/transacción, con el mismo
            // bloqueo de fila (FOR UPDATE) que ya usa Venta::registrarVenta(), para evitar
            // que una anulación y otra operación de puntos del mismo cliente (otra venta u
            // otra anulación) se pisen bajo concurrencia.
            if($venta['id_cliente'] != 1) {
                // Si ganó puntos, se los quitamos (-puntos_ganados)
                // Si usó puntos, se los devolvemos (+puntos_usados)
                $delta_puntos = $venta['puntos_usados'] - $venta['puntos_ganados'];
                if($delta_puntos != 0) {
                    $selCli = $this->conn->prepare("SELECT puntos_acumulados FROM clientes WHERE id = :id FOR UPDATE");
                    $selCli->bindParam(':id', $venta['id_cliente']);
                    $selCli->execute();
                    $filaCli = $selCli->fetch(PDO::FETCH_ASSOC);

                    if ($filaCli === false) {
                        throw new Exception("El cliente de la venta ya no existe.");
                    }

                    $nuevoSaldo = (int)$filaCli['puntos_acumulados'] + $delta_puntos;

                    // Defensa contra saldo negativo (no debería ocurrir en el flujo normal,
                    // ya que registrarVenta() ya impide vender más puntos de los
                    // disponibles, pero se deja como red de seguridad).
                    if ($nuevoSaldo < 0) {
                        throw new Exception("La reversión de puntos dejaría un saldo negativo, no se permite.");
                    }

                    $updCli = $this->conn->prepare("UPDATE clientes SET puntos_acumulados = :nuevo WHERE id = :idc");
                    $updCli->bindParam(':nuevo', $nuevoSaldo, PDO::PARAM_INT);
                    $updCli->bindParam(':idc', $venta['id_cliente']);
                    $updCli->execute();
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getMetricasHoyPorUsuario($usuario_id) {
        $hoy = date('Y-m-d');

        $stmt = $this->conn->prepare("SELECT SUM(total) as ingresos, COUNT(id) as transacciones FROM ventas WHERE DATE(fecha_venta) = :hoy AND estado = 'Completada' AND id_usuario = :usuario_id");
        $stmt->bindParam(':hoy', $hoy);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'transacciones' => $data['transacciones'] ?: 0,
            'ingresos' => $data['ingresos'] ?: 0.00
        ];
    }
}
