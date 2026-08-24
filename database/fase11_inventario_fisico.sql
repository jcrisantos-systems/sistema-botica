USE `botica_db`;

-- 1. Cabecera de Auditoría de Inventario
CREATE TABLE IF NOT EXISTS `inventario_auditorias` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `fecha_inicio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_fin` DATETIME NULL,
  `estado` ENUM('Abierta', 'Finalizada', 'Cancelada') DEFAULT 'Abierta',
  `observaciones` TEXT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_inv_aud_usr` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Detalle del Conteo Físico por Lote
CREATE TABLE IF NOT EXISTS `inventario_auditoria_detalles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_auditoria` INT NOT NULL,
  `id_lote` INT NOT NULL,
  `stock_sistema` INT NOT NULL, -- Lo que habia al abrir la sesion
  `stock_fisico` INT NOT NULL DEFAULT 0, -- Lo que el usuario conto
  `diferencia` INT NOT NULL DEFAULT 0, -- Calculado (fisico - sistema)
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_inv_aud_det` FOREIGN KEY (`id_auditoria`) REFERENCES `inventario_auditorias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inv_aud_lote` FOREIGN KEY (`id_lote`) REFERENCES `inventario_lotes` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
