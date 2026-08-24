-- Seleccionar la base de datos
USE botica_db;

-- -----------------------------------------------------
-- Table `clientes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `tipo_documento` VARCHAR(20) NOT NULL DEFAULT 'DNI', -- DNI, RUC, Pasaporte, Sin Documento
  `num_documento` VARCHAR(20) NOT NULL,
  `nombres` VARCHAR(150) NOT NULL,
  `telefono` VARCHAR(50) NULL,
  `direccion` VARCHAR(255) NULL,
  `estado` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Siempre inyectamos un Cliente Genérico para agilidad de ventas rápidas
INSERT IGNORE INTO `clientes` (`id`, `tipo_documento`, `num_documento`, `nombres`, `telefono`) VALUES
(1, 'Sin Documento', '00000000', 'Cliente Público en General', '');

-- -----------------------------------------------------
-- Table `ventas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ventas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_cliente` INT NOT NULL,
  `id_usuario` INT NOT NULL, -- Cajero/Farmacéutico
  `tipo_comprobante` VARCHAR(50) NOT NULL DEFAULT 'Ticket', -- Boleta, Factura, Ticket
  `serie_comprobante` VARCHAR(20) NULL,
  `num_comprobante` VARCHAR(50) NOT NULL,
  `fecha_venta` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `igv` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` VARCHAR(50) NOT NULL DEFAULT 'Efectivo', -- Efectivo, Yape, Plin, Tarjeta
  `pago_recibido` DECIMAL(12,2) NULL, -- Con cuánto pagó
  `vuelto` DECIMAL(12,2) NULL,
  `estado` VARCHAR(20) DEFAULT 'Completada', -- Completada, Anulada
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_cliente`) REFERENCES `clientes`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table `venta_detalles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `venta_detalles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_venta` INT NOT NULL,
  `id_producto` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `id_lote` INT NULL, -- Para trazabilidad exacta de qué lote salió
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_venta`) REFERENCES `ventas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_producto`) REFERENCES `productos`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_lote`) REFERENCES `inventario_lotes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;
