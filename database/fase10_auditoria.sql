USE `botica_db`;

-- 1. Tabla de Auditoría de Accesos (Login/Logout)
CREATE TABLE IF NOT EXISTS `audit_accesos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `accion` ENUM('LOGIN', 'LOGOUT') NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_audit_acc_usr` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Auditoría de Acciones Críticas
CREATE TABLE IF NOT EXISTS `audit_acciones` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `modulo` VARCHAR(50) NOT NULL, -- Ej: Ventas, Compras, Productos, Inventario
  `accion` VARCHAR(50) NOT NULL, -- Ej: ANULAR, EDITAR_PRECIO, AJUSTE_STOCK, DEVOLUCION
  `descripcion` TEXT NOT NULL,
  `monto_afectado` DECIMAL(12,2) DEFAULT 0.00, -- Opcional, para ventas/compras anuladas
  `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_audit_act_usr` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
