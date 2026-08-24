USE `botica_db`;

-- 1. Tabla de Cabecera para Devoluciones a Proveedores (Notas de Crédito)
CREATE TABLE IF NOT EXISTS `compras_devoluciones` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_compra` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `num_documento_prov` VARCHAR(50) NOT NULL, -- El nro de Nota de Credito que da el proveedor
  `motivo` TEXT NULL,
  `total_devuelto` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `fecha_devolucion` DATE NOT NULL,
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_dev_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dev_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Detalle para Devoluciones a Proveedores
CREATE TABLE IF NOT EXISTS `compras_devolucion_detalles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_devolucion` INT NOT NULL,
  `id_producto` INT NOT NULL,
  `id_lote` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `precio_costo` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_det_dev` FOREIGN KEY (`id_devolucion`) REFERENCES `compras_devoluciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_det_prod_dev` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_det_lote_dev` FOREIGN KEY (`id_lote`) REFERENCES `inventario_lotes` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
