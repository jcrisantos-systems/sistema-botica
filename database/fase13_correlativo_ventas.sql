-- Fase 13: correlativo transaccional de num_comprobante para ventas (reemplaza rand()).
--
-- ALGORITMO FINAL REALMENTE IMPLEMENTADO en Venta::registrarVenta() (app/models/Venta.php):
--
--   1. INSERT INTO venta_correlativos (serie_comprobante, tipo_comprobante, ultimo_numero)
--      VALUES (:ser, :tip, 1)
--      ON DUPLICATE KEY UPDATE ultimo_numero = ultimo_numero + 1;
--
--      Upsert atómico nativo de MySQL, en una única sentencia: si la fila de esa
--      serie+tipo no existe, la crea con ultimo_numero=1; si ya existe, la incrementa
--      en 1. No hay una fase de lectura-y-bloqueo separada de la fase de escritura, por
--      lo que no puede quedar un lock compartido (S) a medio camino de escalar a
--      exclusivo (X) — la causa raíz de los deadlocks reales que se observaron con el
--      patrón alternativo "SELECT ... FOR UPDATE" + "UPDATE" bajo concurrencia real
--      (confirmado con SHOW ENGINE INNODB STATUS y una prueba de 8 procesos PHP
--      concurrentes; ese patrón queda descartado, no es el algoritmo vigente).
--
--   2. SELECT ultimo_numero FROM venta_correlativos WHERE serie_comprobante = :ser
--      AND tipo_comprobante = :tip;
--
--      Lectura simple, SIN "FOR UPDATE", ejecutada dentro de la MISMA transacción que el
--      upsert anterior: no hace falta bloquear nada de nuevo porque esa transacción ya
--      tiene el lock exclusivo de la fila desde el paso 1, y una transacción siempre ve
--      sus propios cambios aún no confirmados (comportamiento estándar de InnoDB).
--
--   3. El valor leído se formatea con str_pad(..., 6, '0', STR_PAD_LEFT) y se usa tal
--      cual como num_comprobante al insertar la venta, DENTRO de la misma transacción.
--
--   4. Si cualquier paso posterior de la venta falla (stock insuficiente, puntos
--      insuficientes, etc.), la excepción provoca rollBack() de TODA la transacción,
--      incluido el incremento del paso 1 — el contador nunca queda "quemado" por una
--      venta que no se concretó.
--
-- Probado con 8 procesos PHP reales concurrentes contra la misma serie/tipo (incluido el
-- flujo completo de venta, no solo el correlativo aislado): 8/8 éxitos, 8 números
-- consecutivos sin colisión, 0 deadlocks.
--
-- uq_venta_comprobante (aplicado al final de este archivo) es la DEFENSA FINAL de base
-- de datos: aunque el algoritmo de arriba ya impide colisiones por diseño, el índice
-- UNIQUE garantiza a nivel de esquema que ninguna fila de ventas pueda duplicar
-- (tipo_comprobante, serie_comprobante, num_comprobante) pase lo que pase en la capa de
-- aplicación (bug futuro, acceso directo a la BD, etc.).
--
-- ADVERTENCIA SOBRE REVERSIÓN — NO usar "DROP TABLE venta_correlativos;" como reversión
-- normal:
--   - No borrar venta_correlativos en una base con ventas reales: la tabla conserva el
--     estado de numeración de comprobantes (ultimo_numero por serie+tipo); borrarla hace
--     que la próxima venta reinicie el correlativo desde 0/1, generando num_comprobante
--     que YA existen en `ventas` (choque directo contra uq_venta_comprobante, o peor,
--     duplicados reales si ese índice no estuviera aplicado).
--   - Una devolución o anulación de venta NUNCA debe borrar, reducir ni reutilizar
--     correlativos: Venta::anularVenta() marca la venta como 'Anulada' y revierte stock/
--     lotes/kardex/puntos, pero el número de comprobante ya emitido permanece consumido
--     para siempre (es lo correcto: un comprobante anulado sigue siendo un documento
--     emitido, no un hueco para reutilizar).
--   - Para revertir SOLO el código (volver a la versión anterior de Venta.php/
--     VentaController.php sin el correlativo transaccional): conservar la tabla
--     venta_correlativos tal cual; el código antiguo simplemente no la consulta.
--   - Para una reversión COMPLETA de datos y esquema (deshacer esta migración por
--     completo, incluyendo el estado de numeración): usar EXCLUSIVAMENTE un respaldo
--     completo y verificado tomado antes de aplicar esta migración (ver
--     app/backups/backup_pre_fase_integridad_*.sql de la Fase A), restaurado con el
--     procedimiento ya documentado — nunca un DROP TABLE suelto.
--   - Esta nota es documental: no ejecuta ninguna operación sobre botica_db ni sobre
--     botica_db_test_restore.

CREATE TABLE venta_correlativos (
  serie_comprobante VARCHAR(20) NOT NULL,
  tipo_comprobante  VARCHAR(50) NOT NULL,
  ultimo_numero     INT NOT NULL DEFAULT 0,
  PRIMARY KEY (serie_comprobante, tipo_comprobante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Semilla desde los máximos reales ya emitidos por tipo+serie (verificado, solo lectura,
-- 0 num_comprobante no numéricos en las ventas existentes).
INSERT INTO venta_correlativos (serie_comprobante, tipo_comprobante, ultimo_numero) VALUES
  ('T001', 'Ticket',  80076),
  ('B001', 'Boleta',     28),
  ('F001', 'Factura',    35);

-- Defensa final de BD (aplicada solo después de confirmar que Venta::registrarVenta() ya
-- genera num_comprobante con el correlativo transaccional, no con rand()). Verificado
-- justo antes de aplicar: 0 duplicados y 0 NULL/vacíos/espacios en tipo/serie/num de las
-- 50 ventas reales existentes.
-- Reversión (documentada, NO ejecutada): ALTER TABLE ventas DROP INDEX uq_venta_comprobante;
ALTER TABLE ventas
  ADD UNIQUE KEY uq_venta_comprobante (tipo_comprobante, serie_comprobante, num_comprobante);
