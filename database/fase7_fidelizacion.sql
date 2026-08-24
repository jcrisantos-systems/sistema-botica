USE `botica_db`;

-- Agregar puntos a clientes
SET @dbname = 'botica_db';
SET @tablename = 'clientes';
SET @columnname = 'puntos_acumulados';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE clientes ADD COLUMN puntos_acumulados INT DEFAULT 0 AFTER direccion;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Agregar columnas a ventas
SET @tablenameV = 'ventas';

SET @columnDesc = 'descuento';
SET @prepDesc = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablenameV) AND (table_schema = @dbname) AND (column_name = @columnDesc)
  ) > 0, "SELECT 1", "ALTER TABLE ventas ADD COLUMN descuento DECIMAL(10,2) DEFAULT 0.00 AFTER subtotal;"
));
PREPARE alt1 FROM @prepDesc; EXECUTE alt1; DEALLOCATE PREPARE alt1;

SET @columnGan = 'puntos_ganados';
SET @prepGan = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablenameV) AND (table_schema = @dbname) AND (column_name = @columnGan)
  ) > 0, "SELECT 1", "ALTER TABLE ventas ADD COLUMN puntos_ganados INT DEFAULT 0 AFTER vuelto;"
));
PREPARE alt2 FROM @prepGan; EXECUTE alt2; DEALLOCATE PREPARE alt2;

SET @columnUs = 'puntos_usados';
SET @prepUs = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablenameV) AND (table_schema = @dbname) AND (column_name = @columnUs)
  ) > 0, "SELECT 1", "ALTER TABLE ventas ADD COLUMN puntos_usados INT DEFAULT 0 AFTER puntos_ganados;"
));
PREPARE alt3 FROM @prepUs; EXECUTE alt3; DEALLOCATE PREPARE alt3;
