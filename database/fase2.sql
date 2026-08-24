-- Seleccionar la base de datos
USE botica_db;

-- -----------------------------------------------------
-- Table `categorias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `estado` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table `laboratorios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laboratorios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `estado` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table `proveedores`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ruc` VARCHAR(20) NOT NULL UNIQUE,
  `razon_social` VARCHAR(150) NOT NULL,
  `representante` VARCHAR(150) NULL,
  `telefono` VARCHAR(50) NULL,
  `direccion` VARCHAR(255) NULL,
  `estado` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table `productos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `productos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo_barras` VARCHAR(50) NULL UNIQUE,
  `nombre_generico` VARCHAR(150) NOT NULL,
  `nombre_comercial` VARCHAR(150) NOT NULL,
  `concentracion` VARCHAR(100) NULL,
  `forma_farmaceutica` VARCHAR(100) NULL, -- Tableta, Jarabe, etc.
  `id_laboratorio` INT NULL,
  `id_categoria` INT NULL,
  `precio_compra` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `precio_venta` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `margen_ganancia` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `unidad_medida` VARCHAR(50) NULL, -- Unidad, Caja, Blister
  `requiere_receta` TINYINT(1) DEFAULT 0,
  `stock_actual` INT NOT NULL DEFAULT 0,
  `stock_minimo` INT NOT NULL DEFAULT 10,
  `estado` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_laboratorio`) REFERENCES `laboratorios`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Insertar datos de prueba base
INSERT IGNORE INTO `categorias` (`nombre`, `descripcion`) VALUES
('Analgésicos', 'Medicamentos para el dolor'),
('Antibióticos', 'Combaten infecciones bacterianas'),
('Antiinflamatorios', 'Reducen la inflamación');

INSERT IGNORE INTO `laboratorios` (`nombre`, `descripcion`) VALUES
('FarmaIndustria', 'Laboratorio nacional líder'),
('Genfar', 'Genéricos de calidad'),
('Bayer', 'Laboratorio multinacional');

INSERT IGNORE INTO `proveedores` (`ruc`, `razon_social`, `telefono`) VALUES
('20100200300', 'Droguería INTI S.A.', '01-555-1234'),
('20400500600', 'Distribuidora Médica SAC', '01-444-9876');

INSERT IGNORE INTO `productos` (`codigo_barras`, `nombre_generico`, `nombre_comercial`, `concentracion`, `forma_farmaceutica`, `id_laboratorio`, `id_categoria`, `precio_compra`, `precio_venta`, `margen_ganancia`, `unidad_medida`, `requiere_receta`, `stock_actual`) VALUES
('775123456780', 'Paracetamol', 'Panadol', '500mg', 'Tableta', 1, 1, 0.50, 1.00, 50.00, 'Unidad', 0, 450),
('775123456781', 'Amoxicilina', 'Amoxil', '500mg', 'Cápsula', 2, 2, 1.20, 2.00, 40.00, 'Unidad', 1, 120),
('775123456782', 'Ibuprofeno', 'Advil', '400mg', 'Tableta', 3, 3, 0.80, 1.50, 46.67, 'Blister', 0, 85);
