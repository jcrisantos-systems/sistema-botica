-- Seleccionar la base de datos
USE botica_db;

-- -----------------------------------------------------
-- Table `compras`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `compras` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_proveedor` INT NOT NULL,
  `id_usuario` INT NOT NULL, -- Quién registró la compra
  `tipo_comprobante` VARCHAR(50) NOT NULL, -- Factura, Boleta, Guia
  `serie_comprobante` VARCHAR(20) NULL,
  `num_comprobante` VARCHAR(50) NOT NULL,
  `fecha_compra` DATE NOT NULL,
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `impuesto` DECIMAL(10,2) DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `estado` VARCHAR(20) DEFAULT 'Completada', -- Completada, Anulada
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table `compra_detalles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `compra_detalles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_compra` INT NOT NULL,
  `id_producto` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_compra`) REFERENCES `compras`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table `inventario_lotes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `inventario_lotes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_producto` INT NOT NULL,
  `id_compra_detalle` INT NULL, -- Puede ser Null si es ingreso por ajuste
  `codigo_lote` VARCHAR(50) NOT NULL,
  `fecha_vencimiento` DATE NOT NULL,
  `cantidad_inicial` INT NOT NULL,
  `cantidad_disponible` INT NOT NULL,
  `estado` TINYINT(1) DEFAULT 1, -- 1: Disponible, 0: Agotado/Anulado
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_compra_detalle`) REFERENCES `compra_detalles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table `kardex`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `kardex` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_producto` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `tipo_movimiento` ENUM('ENTRADA', 'SALIDA', 'AJUSTE') NOT NULL,
  `motivo` VARCHAR(150) NOT NULL, -- ej. "Compra Factura F001-231", "Venta Ticket T001", "Vencimiento"
  `cantidad` INT NOT NULL, -- Positivo o negativo dependiendo
  `saldo_actual` INT NOT NULL, -- Saldo temporal despues de la operacion
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;
