-- =============================================================================
-- Corrección de dato histórico: cajas.id = 1
-- Fecha de ejecución: 2026-08-26
-- Ejecutado por: auditoría de integridad del módulo de Caja (sesión de Claude Code,
--                autorizada explícitamente por el propietario del sistema)
-- =============================================================================
--
-- HALLAZGO
-- --------
-- Durante una auditoría de solo lectura del módulo de Caja se detectó que la fila
-- `cajas` id=1 tenía estado = 0 (cerrada) pero fecha_cierre, monto_final_esperado,
-- monto_final_real y diferencia en NULL (y, además, ingresos_efectivo /
-- ingresos_transferencia en 0.00 pese a tener ventas reales asociadas). Se
-- investigó el origen (ver historial de la conversación / revisión de código):
--
--   - Caja::cerrarCaja() (app/models/Caja.php) actualiza estado y fecha_cierre en
--     UN SOLO UPDATE atómico junto con el resto de columnas del cierre; no existe
--     ningún camino de código donde estado pueda quedar en 0 sin fecha_cierre.
--     Se descartó por completo que esta fuera una falla del flujo real de cierre
--     de caja de la aplicación.
--   - Se localizó el mismo estado incompleto ya presente en el dump más antiguo
--     disponible del proyecto (bk_basededatos.sql, con fecha de modificación en
--     disco del 18/04/2026, ~8 minutos después de las filas de audit_acciones
--     insertadas por database/seed_data.sql). La fila fue insertada manualmente
--     (fecha_apertura a medianoche exacta, monto_inicial que no coincide con
--     ningún valor generado por código o por el seed) durante la configuración
--     inicial del entorno de desarrollo/demo, no por uso real de la aplicación.
--   - Se verificó que la fila SÍ tiene 35 ventas reales asociadas (ventas.caja_id
--     = 1, todas 'Completada', sin anulaciones), por lo que no es una fila vacía
--     descartable: representa un cierre de caja real que nunca se completó
--     correctamente en el momento de crear los datos de prueba.
--   - Se confirmó (búsqueda en todo el código) que ningún reporte, dashboard o
--     cálculo del sistema usa "fecha_cierre IS NULL" ni "monto_final_esperado IS
--     NULL" como condición; el estado abierto/cerrado se determina siempre por la
--     columna `estado`. Completar estos campos no altera ningún comportamiento
--     existente, solo corrige la visualización en cajas/index.php y
--     ticket_arqueo.php (que antes mostraban "-", una fecha inválida, o un
--     "Ventas Efectivo: S/ 0.00" inconsistente con el efectivo esperado).
--
-- CÁLCULO (mismas fórmulas que Caja::getResumenActual() / cerrarCaja())
-- -----------------------------------------------------------------------------
-- Calculado con SELECT de solo lectura sobre los datos reales de esta caja:
--   ingresos_efectivo      (ventas 'Efectivo', no anuladas, caja_id=1)     = 1741.80
--   ingresos_transferencia (ventas != 'Efectivo', no anuladas, caja_id=1) =  893.70
--   ingresos_extras / egresos (caja_movimientos, caja_id=1)               = 0 / 0 (sin filas)
--   monto_inicial                                                         = 100.00
--   => monto_final_esperado = monto_inicial + ingresos_efectivo + ingresos_extras - egresos
--                            = 100.00 + 1741.80 + 0 - 0 = 1841.80
--
-- fecha_cierre se fijó en la fecha/hora de la ÚLTIMA venta real asociada a esta
-- caja (MAX(fecha_venta) WHERE caja_id = 1 = '2026-04-09 19:49:11'), por ser el
-- único punto en el tiempo verificable con datos reales: la caja no pudo haberse
-- cerrado antes de su última venta.
--
-- NO SE TOCARON (autorización explícita del propietario):
--   - estado (permanece en 0, ya cerrada)
--   - monto_final_real (permanece NULL: no hay conteo físico real que reconstruir)
--   - diferencia (permanece NULL, depende de monto_final_real)
--
-- Este fix se aplicó en dos UPDATE ejecutados en momentos distintos de la misma
-- sesión de auditoría (el segundo, sobre ingresos_efectivo/ingresos_transferencia,
-- fue una corrección de seguimiento pedida al notar la inconsistencia visual que
-- dejaba el primero). Se documentan juntos aquí para que quede un solo registro
-- completo del fix, en vez de dos entradas separadas.
--
-- RESPALDO PREVIO
-- ----------------
-- app/backups/backup_pre_fix_caja_id1_2026-08-26_05-43-05.sql
-- Generado con mysqldump (--single-transaction --routines --triggers
-- --default-character-set=utf8mb4), mismo método que BackupService.php.
-- Verificado: 68499 bytes, contiene CREATE TABLE y el INSERT INTO `cajas` con el
-- estado exacto previo a AMBOS updates (id=1 con estado=0, fecha_cierre=NULL,
-- ingresos_efectivo=0.00, ingresos_transferencia=0.00).
--
-- AUTORIZACIÓN
-- ------------
-- Ambos UPDATE fueron mostrados al propietario del sistema (cálculo, sentencia
-- exacta y confirmación del backup) y ejecutados solo tras su autorización
-- explícita en la conversación. No se ejecutó ningún DELETE ni se tocó la
-- estructura de la tabla.
-- =============================================================================

-- Paso 1: reconstruir fecha_cierre y monto_final_esperado.
UPDATE cajas SET
  fecha_cierre = '2026-04-09 19:49:11',
  monto_final_esperado = 1841.80,
  observacion = 'Cierre reconstruido en auditoría de integridad (2026-08-26). Sin conteo físico verificado; dato de configuración inicial del sistema. Efectivo esperado calculado a partir de las 35 ventas asociadas.'
WHERE id = 1;

-- Paso 2: completar ingresos_efectivo / ingresos_transferencia con los mismos
-- valores ya calculados y verificados en el Paso 1 (no son datos nuevos), para
-- que monto_inicial + ingresos_efectivo = monto_final_esperado cuadre en la UI.
UPDATE cajas SET
  ingresos_efectivo = 1741.80,
  ingresos_transferencia = 893.70
WHERE id = 1;
