-- =============================================================================
-- Depuración de clientes duplicados + restricción UNIQUE(tipo_documento, num_documento)
-- Fecha de ejecución: 2026-08-26
-- Ejecutado por: auditoría de integridad y duplicados del módulo de Clientes
--                (sesión de Claude Code, autorizada explícitamente por el
--                propietario del sistema, tras revisión línea por línea del script)
-- =============================================================================
--
-- HALLAZGO
-- --------
-- Una auditoría de solo lectura del estado de protección contra duplicados
-- (iniciada retomando el tema desde cero, verificando el código y la BD real,
-- no conversaciones previas) encontró que `clientes` no tenía nunca una
-- restricción UNIQUE sobre (tipo_documento, num_documento) — solo un chequeo de
-- aplicación en ClienteController::save() / Cliente::existeCliente() (agregado en
-- el commit 48c24c3, "feat(security): reforzar integridad..."), que previene
-- duplicados NUEVOS pero no tocaba los ya existentes.
--
-- Se detectaron 21 registros repartidos en 10 grupos duplicados por el mismo
-- (tipo_documento, num_documento), todos con datos idénticos entre sí (mismo
-- nombre, teléfono y dirección en cada par/trío):
--
--   DNI 01122334: ids 16, 26                (Miguel Huamán Jara)
--   DNI 10203040: ids 7, 17                 (Juan Pérez García)
--   DNI 20304050: ids 8, 18                 (María Rodríguez Paz)
--   DNI 30405060: ids 9, 19                 (Carlos Sánchez Ruiz)
--   DNI 40506070: ids 10, 20, 28            (Ana López Villacorta)
--   DNI 50607080: ids 11, 21                (Roberto Gómez Castro)
--   DNI 60708090: ids 12, 22                (Luis Torres Mendoza)
--   DNI 70809001: ids 13, 23                (Elena Vargas Solís)
--   DNI 80900112: ids 14, 24                (Pedro Castillo Luna)
--   DNI 90011223: ids 15, 25                (Sofía Ramírez Vega)
--
-- Única relación real hacia clientes.id en todo el esquema (verificado con
-- information_schema.KEY_COLUMN_USAGE y con búsqueda de toda columna %cliente%):
-- ventas.id_cliente (FK ventas_ibfk_1, ON DELETE RESTRICT). No existe tabla de
-- fidelización aparte; puntos_acumulados vive directamente en `clientes`.
--
-- CRITERIO DE SELECCIÓN DEL "PRINCIPAL" POR GRUPO (decidido por el propietario)
-- -----------------------------------------------------------------------------
-- 1) Si un solo id del grupo tenía ventas asociadas, ese es el principal.
--    Único caso: DNI 40506070 — id=10 tenía 6 ventas (2 Completadas por S/38.80,
--    4 Anuladas; ids 20 y 28 sin ninguna venta) -> principal = 10.
-- 2) En los 9 grupos restantes ningún id tenía ventas (empate). Se comparó
--    completitud de teléfono/dirección entre ambos ids de cada grupo: los 9
--    grupos resultaron en empate exacto (mismo teléfono y misma dirección,
--    ambos campos llenos en los dos ids). Por regla de desempate del
--    propietario, principal = id menor del grupo en los 9 casos.
--
-- Principal final por grupo y puntos_acumulados a sumar (suma de todos los ids
-- del grupo, ya que ningún duplicado tenía relaciones que perder aparte de sus
-- propios puntos):
--   id=16 (DNI 01122334): 10+10  = 20   | duplicado: 26
--   id=7  (DNI 10203040): 50+50  = 100  | duplicado: 17
--   id=8  (DNI 20304050): 120+120= 240  | duplicado: 18
--   id=9  (DNI 30405060): 0+0    = 0    | duplicado: 19
--   id=10 (DNI 40506070): 278+240+0=518 | duplicados: 20, 28
--   id=11 (DNI 50607080): 15+15  = 30   | duplicado: 21
--   id=12 (DNI 60708090): 85+85  = 170  | duplicado: 22
--   id=13 (DNI 70809001): 300+300= 600  | duplicado: 23
--   id=14 (DNI 80900112): 45+45  = 90   | duplicado: 24
--   id=15 (DNI 90011223): 60+60  = 120  | duplicado: 25
--
-- DECISIÓN: eliminar físicamente (DELETE) los 11 duplicados, no desactivarlos
-- con estado=0. Motivo: un UNIQUE KEY (tipo_documento, num_documento) aplica
-- sobre TODAS las filas sin importar `estado`; dejar el duplicado inactivo con
-- el mismo num_documento habría bloqueado la creación del propio UNIQUE que se
-- quería aplicar a continuación. Al reasignar antes sus ventas, ningún
-- duplicado perdía historial transaccional real; solo se eliminó el registro
-- maestro redundante (nombre/teléfono/dirección repetidos).
--
-- NOTA TÉCNICA (por qué se usó un procedimiento almacenado temporal):
-- MySQL/InnoDB ejecuta un COMMIT implícito antes de cualquier DDL (igual que
-- TRUNCATE en SistemaReset.php), por lo que ALTER TABLE no puede formar parte
-- de la misma transacción atómica que los UPDATE/DELETE anteriores. El
-- procedimiento envuelve la lógica condicional real: si tras el DELETE aún
-- quedan grupos duplicados, hace ROLLBACK y aborta con SIGNAL sin tocar la
-- estructura; solo si la verificación da 0 duplicados hace COMMIT de los datos
-- y luego el ALTER TABLE. El procedimiento se creó, se ejecutó una única vez y
-- se eliminó al terminar (no queda como objeto permanente de la BD).
--
-- RESPALDO PREVIO
-- ----------------
-- app/backups/backup_pre_depuracion_clientes_duplicados_2026-08-26_11-51-14.sql
-- Generado con mysqldump (--single-transaction --routines --triggers
-- --default-character-set=utf8mb4), mismo método que BackupService.php.
-- Verificado: 69201 bytes, contiene CREATE TABLE y el estado completo de los 21
-- registros duplicados antes de esta depuración.
--
-- AUTORIZACIÓN
-- ------------
-- El script completo (tabla de comparación de completitud, cálculo de puntos,
-- sentencia exacta de reasignación/depuración/ALTER, y confirmación del
-- backup) fue mostrado al propietario del sistema, revisado por él línea por
-- línea (incluyendo una ronda de verificación de un posible residuo de
-- copiado que resultó no existir en el script real), y ejecutado solo tras su
-- autorización explícita en la conversación.
--
-- RESULTADO DE LA EJECUCIÓN
-- --------------------------
-- v_grupos_duplicados verificado = 0 tras el DELETE -> rama COMMIT + ALTER TABLE.
-- clientes: 28 filas -> 17 filas (21 duplicados -> 10 principales, id=1 Cliente
-- Público sin cambios, resto de clientes no duplicados sin cambios).
-- UNIQUE KEY uq_cliente_doc (tipo_documento, num_documento) creado y verificado
-- con SHOW CREATE TABLE clientes.
-- Ventas del grupo DNI 40506070 (antes en ids 10/20/28) verificadas: las 6
-- ventas (ids 46,47,48,49,50,53) ahora apuntan a id_cliente=10, sin pérdidas.
-- =============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS sp_depurar_clientes_duplicados$$

CREATE PROCEDURE sp_depurar_clientes_duplicados()
BEGIN
    DECLARE v_grupos_duplicados INT DEFAULT 0;

    START TRANSACTION;

    -- -----------------------------------------------------------------
    -- 1) Reasignar ventas.id_cliente de cada duplicado hacia su principal
    -- -----------------------------------------------------------------
    UPDATE ventas SET id_cliente = 16 WHERE id_cliente IN (26);              -- DNI 01122334
    UPDATE ventas SET id_cliente = 7  WHERE id_cliente IN (17);              -- DNI 10203040
    UPDATE ventas SET id_cliente = 8  WHERE id_cliente IN (18);              -- DNI 20304050
    UPDATE ventas SET id_cliente = 9  WHERE id_cliente IN (19);              -- DNI 30405060
    UPDATE ventas SET id_cliente = 10 WHERE id_cliente IN (20, 28);          -- DNI 40506070
    UPDATE ventas SET id_cliente = 11 WHERE id_cliente IN (21);              -- DNI 50607080
    UPDATE ventas SET id_cliente = 12 WHERE id_cliente IN (22);              -- DNI 60708090
    UPDATE ventas SET id_cliente = 13 WHERE id_cliente IN (23);              -- DNI 70809001
    UPDATE ventas SET id_cliente = 14 WHERE id_cliente IN (24);              -- DNI 80900112
    UPDATE ventas SET id_cliente = 15 WHERE id_cliente IN (25);              -- DNI 90011223

    -- -----------------------------------------------------------------
    -- 2) Sumar puntos_acumulados de los duplicados en el principal
    -- -----------------------------------------------------------------
    UPDATE clientes SET puntos_acumulados = 20  WHERE id = 16;   -- 10+10
    UPDATE clientes SET puntos_acumulados = 100 WHERE id = 7;    -- 50+50
    UPDATE clientes SET puntos_acumulados = 240 WHERE id = 8;    -- 120+120
    UPDATE clientes SET puntos_acumulados = 0   WHERE id = 9;    -- 0+0
    UPDATE clientes SET puntos_acumulados = 518 WHERE id = 10;   -- 278+240+0
    UPDATE clientes SET puntos_acumulados = 30  WHERE id = 11;   -- 15+15
    UPDATE clientes SET puntos_acumulados = 170 WHERE id = 12;   -- 85+85
    UPDATE clientes SET puntos_acumulados = 600 WHERE id = 13;   -- 300+300
    UPDATE clientes SET puntos_acumulados = 90  WHERE id = 14;   -- 45+45
    UPDATE clientes SET puntos_acumulados = 120 WHERE id = 15;   -- 60+60

    -- -----------------------------------------------------------------
    -- 3) Eliminar los 11 registros duplicados (ya sin relaciones pendientes)
    -- -----------------------------------------------------------------
    DELETE FROM clientes WHERE id IN (17,18,19,20,21,22,23,24,25,26,28);

    -- -----------------------------------------------------------------
    -- 4) Verificación: no debe quedar ningún grupo duplicado
    -- -----------------------------------------------------------------
    SELECT COUNT(*) INTO v_grupos_duplicados FROM (
        SELECT tipo_documento, num_documento
        FROM clientes
        GROUP BY tipo_documento, num_documento
        HAVING COUNT(*) > 1
    ) AS dup;

    SELECT v_grupos_duplicados AS v_grupos_duplicados;

    IF v_grupos_duplicados > 0 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Abortado: aun quedan grupos de clientes duplicados tras la depuracion. No se aplico el UNIQUE. Ningun cambio fue guardado.';
    ELSE
        COMMIT;

        -- ---------------------------------------------------------
        -- 5) UNIQUE final, solo si el paso 4 confirmo 0 duplicados
        -- ---------------------------------------------------------
        ALTER TABLE clientes ADD UNIQUE KEY uq_cliente_doc (tipo_documento, num_documento);
    END IF;
END$$

DELIMITER ;

-- Ejecuta todo lo anterior de una sola vez:
CALL sp_depurar_clientes_duplicados();

-- Limpieza: el procedimiento era solo un contenedor temporal para la lógica condicional
DROP PROCEDURE sp_depurar_clientes_duplicados;
