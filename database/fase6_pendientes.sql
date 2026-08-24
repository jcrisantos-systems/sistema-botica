USE `botica_db`;

-- 1. Agregar columna medico_cmp a la tabla ventas
SET @dbname = 'botica_db';
SET @tablename = 'ventas';
SET @columnname = 'medico_cmp';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE ventas ADD COLUMN medico_cmp VARCHAR(50) NULL AFTER vuelto;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. Crear tabla caja_movimientos para retiros/ingresos extra no-venta
CREATE TABLE IF NOT EXISTS `caja_movimientos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `caja_id` INT NOT NULL,
  `tipo` ENUM('INGRESO', 'EGRESO') NOT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `motivo` VARCHAR(255) NOT NULL,
  `fecha_movimiento` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_mov_caja` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
