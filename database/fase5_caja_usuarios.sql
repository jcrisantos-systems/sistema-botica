USE `botica_db`;

-- Tabla de Cajas (Turnos de Apertura y Cierre)
CREATE TABLE IF NOT EXISTS `cajas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `fecha_apertura` DATETIME NOT NULL,
  `fecha_cierre` DATETIME NULL,
  `monto_inicial` DECIMAL(10,2) NOT NULL,
  `ingresos_efectivo` DECIMAL(10,2) DEFAULT '0.00',
  `ingresos_transferencia` DECIMAL(10,2) DEFAULT '0.00',
  `monto_final_esperado` DECIMAL(10,2) NULL, -- Inicial + Efectivo
  `monto_final_real` DECIMAL(10,2) NULL, -- Lo que cuenta el cajero físicamente
  `diferencia` DECIMAL(10,2) NULL, -- Diferencia (sobrante o faltante)
  `observacion` TEXT NULL,
  `estado` TINYINT(1) DEFAULT 1, -- 1: Abierta, 0: Cerrada
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_caja_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modificar tabla ventas para enlazar con la caja
-- Verificamos si existe la columna caja_id, si no existe la agregamos
SET @dbname = 'botica_db';
SET @tablename = 'ventas';
SET @columnname = 'caja_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE ventas ADD COLUMN caja_id INT NULL AFTER id_cliente;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Establecer FK de venta a caja (opcional, pero buena práctica si sabemos que existen)
-- Asumimos que existirá el FK de ventas.caja_id referenciando cajas.id
-- No lo añadimos estrictamente como restricción dura para evitar conflictos con ventas antiguas que tendrán caja_id = NULL
