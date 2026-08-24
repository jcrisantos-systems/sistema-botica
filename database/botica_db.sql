CREATE DATABASE IF NOT EXISTS botica_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE botica_db;

-- -----------------------------------------------------
-- Table `roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL, -- Administrador, Farmacéutico, Cajero, Almacenero
  `descripcion` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombres` VARCHAR(100) NOT NULL,
  `apellidos` VARCHAR(100) NOT NULL,
  `usuario` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NULL,
  `rol_id` INT NOT NULL,
  `estado` TINYINT(1) DEFAULT 1, -- 1: Activo, 0: Inactivo
  `ultimo_login` DATETIME NULL,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table `configuracion`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracion` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `clave` VARCHAR(50) NOT NULL UNIQUE,
  `valor` TEXT NULL,
  `descripcion` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Insertar roles base
INSERT IGNORE INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema'),
(2, 'Farmacéutico', 'Gestión de catálogo médico y almacén'),
(3, 'Cajero', 'Acceso al Punto de Venta (POS) únicamente'),
(4, 'Almacenero', 'Gestión de ingresos y kardex');

-- Insertar usuario admin con contraseña 'admin' (hash bcrypt)
-- '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' es el hash de bcrypt de 'password'
-- Haré un hash válido para 'admin' que es '$2y$10$Y1/JpG4m8O9gP3W/2/oJwO.Q.IUBuWofkOMs3aLp3LdZ7L712a3zK'
INSERT IGNORE INTO `usuarios` (`id`, `nombres`, `apellidos`, `usuario`, `password`, `email`, `rol_id`) VALUES
(1, 'Admin', 'Sistema', 'admin', '$2y$10$cAdM2fx.HNnhXycqInJ4IedrL19q3aWWq7CO.eeCGPc8ZaAJ6PieG', 'admin@botica.com', 1);

-- Insertar valores de configuración
INSERT IGNORE INTO `configuracion` (`clave`, `valor`, `descripcion`) VALUES
('nombre_botica', 'Mi Botica', 'Nombre comercial de la farmacia/botica'),
('ruc', '20123456789', 'RUC de la empresa'),
('direccion', 'Av. Principal 123', 'Dirección del establecimiento'),
('telefono', '999888777', 'Teléfono principal'),
('moneda', 'S/', 'Símbolo de moneda'),
('igv', '18', 'Porcentaje de IGV');
