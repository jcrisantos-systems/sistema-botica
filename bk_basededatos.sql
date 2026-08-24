-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para botica_db
CREATE DATABASE IF NOT EXISTS `botica_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `botica_db`;

-- Volcando estructura para tabla botica_db.audit_accesos
CREATE TABLE IF NOT EXISTS `audit_accesos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `accion` enum('LOGIN','LOGOUT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_audit_acc_usr` (`id_usuario`),
  CONSTRAINT `fk_audit_acc_usr` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.audit_accesos: ~14 rows (aproximadamente)
DELETE FROM `audit_accesos`;
INSERT INTO `audit_accesos` (`id`, `id_usuario`, `accion`, `ip_address`, `user_agent`, `fecha`) VALUES
	(1, 1, 'LOGOUT', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 06:46:29'),
	(2, 1, 'LOGIN', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 06:46:31'),
	(3, 1, 'LOGIN', '127.0.0.1', 'Mozilla/5.0 Seeding', '2026-04-18 06:51:21'),
	(4, 1, 'LOGOUT', '127.0.0.1', 'Mozilla/5.0 Seeding', '2026-04-18 06:51:21'),
	(5, 1, 'LOGIN', '192.168.1.10', 'Chrome/120', '2026-04-18 06:51:21'),
	(6, 1, 'LOGIN', '10.0.0.1', 'Firefox/121', '2026-04-18 06:51:21'),
	(7, 1, 'LOGIN', '127.0.0.1', 'Seed Agent', '2026-04-18 06:51:21'),
	(8, 1, 'LOGOUT', '127.0.0.1', 'Seed Agent', '2026-04-18 06:51:21'),
	(9, 1, 'LOGIN', '172.16.0.4', 'Safari/17', '2026-04-18 06:51:21'),
	(10, 1, 'LOGIN', '192.168.0.25', 'Edge/119', '2026-04-18 06:51:21'),
	(11, 1, 'LOGOUT', '192.168.0.25', 'Edge/119', '2026-04-18 06:51:21'),
	(12, 1, 'LOGIN', '10.10.10.10', 'Mobile Android', '2026-04-18 06:51:21'),
	(13, 1, 'LOGOUT', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 06:57:48'),
	(14, 1, 'LOGIN', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 06:57:54');

-- Volcando estructura para tabla botica_db.audit_acciones
CREATE TABLE IF NOT EXISTS `audit_acciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto_afectado` decimal(12,2) DEFAULT '0.00',
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_audit_act_usr` (`id_usuario`),
  CONSTRAINT `fk_audit_act_usr` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.audit_acciones: ~10 rows (aproximadamente)
DELETE FROM `audit_acciones`;
INSERT INTO `audit_acciones` (`id`, `id_usuario`, `modulo`, `accion`, `descripcion`, `monto_afectado`, `fecha`) VALUES
	(1, 1, 'Seguridad', 'SEED', 'Poblamiento masivo de datos de prueba', 0.00, '2026-04-18 06:51:21'),
	(2, 1, 'Productos', 'EDITAR', 'Prueba de auditor├¡a en productos', 0.00, '2026-04-18 06:51:21'),
	(3, 1, 'Ventas', 'ANULAR', 'Prueba de anulaci├│n auditada', 40.00, '2026-04-18 06:51:21'),
	(4, 1, 'Inventario', 'AJUSTE', 'Ajuste de stock por vencimiento', 0.00, '2026-04-18 06:51:21'),
	(5, 1, 'Caja', 'EGRESO', 'Pago de servicios b├ísicos', 120.00, '2026-04-18 06:51:21'),
	(6, 1, 'Compras', 'CREAR', 'Registro de compra Bayer', 500.00, '2026-04-18 06:51:21'),
	(7, 1, 'Clientes', 'PUNTOS', 'Canje de puntos fidelidad exitoso', 0.00, '2026-04-18 06:51:21'),
	(8, 1, 'Configuracion', 'LOGO', 'Actualizaci├│n de imagen corporativa', 0.00, '2026-04-18 06:51:21'),
	(9, 1, 'Auditoria', 'LIMPIEZA', 'Optimizaci├│n de base de datos', 0.00, '2026-04-18 06:51:21'),
	(10, 1, 'Inventario', 'FISICO', 'Toma de inventario trimestral', 0.00, '2026-04-18 06:51:21');

-- Volcando estructura para tabla botica_db.cajas
CREATE TABLE IF NOT EXISTS `cajas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `fecha_apertura` datetime NOT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `monto_inicial` decimal(10,2) NOT NULL,
  `ingresos_efectivo` decimal(10,2) DEFAULT '0.00',
  `ingresos_transferencia` decimal(10,2) DEFAULT '0.00',
  `monto_final_esperado` decimal(10,2) DEFAULT NULL,
  `monto_final_real` decimal(10,2) DEFAULT NULL,
  `diferencia` decimal(10,2) DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `estado` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_caja_usuario` (`usuario_id`),
  CONSTRAINT `fk_caja_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.cajas: ~2 rows (aproximadamente)
DELETE FROM `cajas`;
INSERT INTO `cajas` (`id`, `usuario_id`, `fecha_apertura`, `fecha_cierre`, `monto_inicial`, `ingresos_efectivo`, `ingresos_transferencia`, `monto_final_esperado`, `monto_final_real`, `diferencia`, `observacion`, `estado`) VALUES
	(1, 1, '2026-04-07 00:00:00', NULL, 100.00, 0.00, 0.00, NULL, NULL, NULL, NULL, 0),
	(2, 1, '2026-04-09 00:00:00', NULL, 150.00, 0.00, 0.00, NULL, NULL, NULL, NULL, 1);

-- Volcando estructura para tabla botica_db.caja_movimientos
CREATE TABLE IF NOT EXISTS `caja_movimientos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `caja_id` int NOT NULL,
  `tipo` enum('INGRESO','EGRESO') COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_movimiento` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_mov_caja` (`caja_id`),
  CONSTRAINT `fk_mov_caja` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.caja_movimientos: ~0 rows (aproximadamente)
DELETE FROM `caja_movimientos`;

-- Volcando estructura para tabla botica_db.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.categorias: ~5 rows (aproximadamente)
DELETE FROM `categorias`;
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `estado`) VALUES
	(1, 'Analgésicos y Antipiréticos', NULL, 1),
	(2, 'Antibióticos', NULL, 1),
	(3, 'Antialérgicos', NULL, 1),
	(4, 'Vitaminas', NULL, 1),
	(5, 'Cuidado Personal', NULL, 1);

-- Volcando estructura para tabla botica_db.clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DNI',
  `num_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `puntos_acumulados` int DEFAULT '0',
  `estado` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.clientes: ~26 rows (aproximadamente)
DELETE FROM `clientes`;
INSERT INTO `clientes` (`id`, `tipo_documento`, `num_documento`, `nombres`, `telefono`, `direccion`, `puntos_acumulados`, `estado`) VALUES
	(1, 'Sin Documento', '00000000', 'Cliente P├║blico en General', '', NULL, 0, 1),
	(2, 'DNI', '71234567', 'Juan Perez Lopez', '987123456', 'Av Las Palmeras', 0, 1),
	(3, 'DNI', '42567812', 'Maria Gomez Silva', '912345678', 'Los Pinos 102', 0, 1),
	(4, 'DNI', '41238945', 'Carlos Ruiz Martinez', '998123456', 'El Rosal 304', 0, 1),
	(5, 'DNI', '73456712', 'Ana Torres Velasquez', '945678123', 'Urb Centro', 0, 1),
	(6, 'RUC', '10432345678', 'Minimarket Los Andes', '989012345', 'Urb Canto Grande', 0, 1),
	(7, 'DNI', '10203040', 'Juan P├®rez Garc├¡a', '988123456', 'Av. Larco 123, Trujillo', 50, 1),
	(8, 'DNI', '20304050', 'Mar├¡a Rodr├¡guez Paz', '977234567', 'Calle Pizarro 456', 120, 1),
	(9, 'DNI', '30405060', 'Carlos S├ínchez Ruiz', '966345678', 'Urb. El Recreo C-12', 0, 1),
	(10, 'DNI', '40506070', 'Ana L├│pez Villacorta', '955456789', 'Av. Espa├▒a 789', 258, 1),
	(11, 'DNI', '50607080', 'Roberto G├│mez Castro', '944567890', 'Jr. Jun├¡n 321', 15, 1),
	(12, 'DNI', '60708090', 'Luis Torres Mendoza', '933678901', 'Calle San Mart├¡n 555', 85, 1),
	(13, 'DNI', '70809001', 'Elena Vargas Sol├¡s', '922789012', 'Av. Am├®rica Sur 101', 300, 1),
	(14, 'DNI', '80900112', 'Pedro Castillo Luna', '911890123', 'Calle Las Gemas 202', 45, 1),
	(15, 'DNI', '90011223', 'Sof├¡a Ram├¡rez Vega', '900901234', 'Urb. California G-5', 60, 1),
	(16, 'DNI', '01122334', 'Miguel Huam├ín Jara', '988012345', 'Av. Mansiche 900', 10, 1),
	(17, 'DNI', '10203040', 'Juan P├®rez Garc├¡a', '988123456', 'Av. Larco 123, Trujillo', 50, 1),
	(18, 'DNI', '20304050', 'Mar├¡a Rodr├¡guez Paz', '977234567', 'Calle Pizarro 456', 120, 1),
	(19, 'DNI', '30405060', 'Carlos S├ínchez Ruiz', '966345678', 'Urb. El Recreo C-12', 0, 1),
	(20, 'DNI', '40506070', 'Ana L├│pez Villacorta', '955456789', 'Av. Espa├▒a 789', 240, 1),
	(21, 'DNI', '50607080', 'Roberto G├│mez Castro', '944567890', 'Jr. Jun├¡n 321', 15, 1),
	(22, 'DNI', '60708090', 'Luis Torres Mendoza', '933678901', 'Calle San Mart├¡n 555', 85, 1),
	(23, 'DNI', '70809001', 'Elena Vargas Sol├¡s', '922789012', 'Av. Am├®rica Sur 101', 300, 1),
	(24, 'DNI', '80900112', 'Pedro Castillo Luna', '911890123', 'Calle Las Gemas 202', 45, 1),
	(25, 'DNI', '90011223', 'Sof├¡a Ram├¡rez Vega', '900901234', 'Urb. California G-5', 60, 1),
	(26, 'DNI', '01122334', 'Miguel Huam├ín Jara', '988012345', 'Av. Mansiche 900', 10, 1);

-- Volcando estructura para tabla botica_db.compras
CREATE TABLE IF NOT EXISTS `compras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_proveedor` int NOT NULL,
  `id_usuario` int NOT NULL,
  `tipo_comprobante` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `serie_comprobante` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_comprobante` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_compra` date NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `impuesto` decimal(10,2) DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Completada',
  PRIMARY KEY (`id`),
  KEY `id_proveedor` (`id_proveedor`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.compras: ~10 rows (aproximadamente)
DELETE FROM `compras`;
INSERT INTO `compras` (`id`, `id_proveedor`, `id_usuario`, `tipo_comprobante`, `serie_comprobante`, `num_comprobante`, `fecha_compra`, `fecha_registro`, `impuesto`, `total`, `estado`) VALUES
	(1, 1, 1, 'Factura', 'F001', '5001', '2026-04-10', '2026-04-18 06:51:21', 0.00, 500.00, 'Completada'),
	(2, 2, 1, 'Factura', 'F001', '5002', '2026-04-11', '2026-04-18 06:51:21', 0.00, 400.00, 'Completada'),
	(3, 3, 1, 'Factura', 'F002', '5003', '2026-04-12', '2026-04-18 06:51:21', 0.00, 300.00, 'Completada'),
	(4, 4, 1, 'Factura', 'F002', '5004', '2026-04-13', '2026-04-18 06:51:21', 0.00, 600.00, 'Completada'),
	(5, 5, 1, 'Factura', 'F003', '5005', '2026-04-14', '2026-04-18 06:51:21', 0.00, 250.00, 'Completada'),
	(6, 6, 1, 'Factura', 'F003', '5006', '2026-04-15', '2026-04-18 06:51:21', 0.00, 900.00, 'Completada'),
	(7, 7, 1, 'Factura', 'F004', '5007', '2026-04-16', '2026-04-18 06:51:21', 0.00, 100.00, 'Completada'),
	(8, 8, 1, 'Factura', 'F004', '5008', '2026-04-17', '2026-04-18 06:51:21', 0.00, 200.00, 'Completada'),
	(9, 9, 1, 'Factura', 'F005', '5009', '2026-04-18', '2026-04-18 06:51:21', 0.00, 750.00, 'Completada'),
	(10, 10, 1, 'Factura', 'F005', '5010', '2026-04-18', '2026-04-18 06:51:21', 0.00, 1000.00, 'Pendiente');

-- Volcando estructura para tabla botica_db.compras_devoluciones
CREATE TABLE IF NOT EXISTS `compras_devoluciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_compra` int NOT NULL,
  `id_usuario` int NOT NULL,
  `num_documento_prov` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci,
  `total_devuelto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `fecha_devolucion` date NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_dev_compra` (`id_compra`),
  KEY `fk_dev_usuario` (`id_usuario`),
  CONSTRAINT `fk_dev_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dev_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.compras_devoluciones: ~0 rows (aproximadamente)
DELETE FROM `compras_devoluciones`;

-- Volcando estructura para tabla botica_db.compras_devolucion_detalles
CREATE TABLE IF NOT EXISTS `compras_devolucion_detalles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_devolucion` int NOT NULL,
  `id_producto` int NOT NULL,
  `id_lote` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_costo` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_det_dev` (`id_devolucion`),
  KEY `fk_det_prod_dev` (`id_producto`),
  KEY `fk_det_lote_dev` (`id_lote`),
  CONSTRAINT `fk_det_dev` FOREIGN KEY (`id_devolucion`) REFERENCES `compras_devoluciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_det_lote_dev` FOREIGN KEY (`id_lote`) REFERENCES `inventario_lotes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_det_prod_dev` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.compras_devolucion_detalles: ~0 rows (aproximadamente)
DELETE FROM `compras_devolucion_detalles`;

-- Volcando estructura para tabla botica_db.compra_detalles
CREATE TABLE IF NOT EXISTS `compra_detalles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_compra` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_compra` (`id_compra`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `compra_detalles_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `compra_detalles_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.compra_detalles: ~0 rows (aproximadamente)
DELETE FROM `compra_detalles`;

-- Volcando estructura para tabla botica_db.configuracion
CREATE TABLE IF NOT EXISTS `configuracion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.configuracion: ~6 rows (aproximadamente)
DELETE FROM `configuracion`;
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `descripcion`) VALUES
	(1, 'nombre_botica', 'Mi Botica', 'Nombre comercial de la farmacia/botica'),
	(2, 'ruc', '20123456789', 'RUC de la empresa'),
	(3, 'direccion', 'Av. Principal 123', 'Direcci├│n del establecimiento'),
	(4, 'telefono', '999888777', 'Tel├®fono principal'),
	(5, 'moneda', 'S/', 'S├¡mbolo de moneda'),
	(6, 'igv', '18', 'Porcentaje de IGV'),
	(8, 'logo', '', 'Ruta del logo institucional');

-- Volcando estructura para tabla botica_db.inventario_auditorias
CREATE TABLE IF NOT EXISTS `inventario_auditorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `fecha_inicio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_fin` datetime DEFAULT NULL,
  `estado` enum('Abierta','Finalizada','Cancelada') COLLATE utf8mb4_unicode_ci DEFAULT 'Abierta',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_inv_aud_usr` (`id_usuario`),
  CONSTRAINT `fk_inv_aud_usr` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.inventario_auditorias: ~0 rows (aproximadamente)
DELETE FROM `inventario_auditorias`;

-- Volcando estructura para tabla botica_db.inventario_auditoria_detalles
CREATE TABLE IF NOT EXISTS `inventario_auditoria_detalles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_auditoria` int NOT NULL,
  `id_lote` int NOT NULL,
  `stock_sistema` int NOT NULL,
  `stock_fisico` int NOT NULL DEFAULT '0',
  `diferencia` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_inv_aud_det` (`id_auditoria`),
  KEY `fk_inv_aud_lote` (`id_lote`),
  CONSTRAINT `fk_inv_aud_det` FOREIGN KEY (`id_auditoria`) REFERENCES `inventario_auditorias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inv_aud_lote` FOREIGN KEY (`id_lote`) REFERENCES `inventario_lotes` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.inventario_auditoria_detalles: ~0 rows (aproximadamente)
DELETE FROM `inventario_auditoria_detalles`;

-- Volcando estructura para tabla botica_db.inventario_lotes
CREATE TABLE IF NOT EXISTS `inventario_lotes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `id_compra_detalle` int DEFAULT NULL,
  `codigo_lote` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `cantidad_inicial` int NOT NULL,
  `cantidad_disponible` int NOT NULL,
  `estado` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id_producto` (`id_producto`),
  KEY `id_compra_detalle` (`id_compra_detalle`),
  CONSTRAINT `inventario_lotes_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `inventario_lotes_ibfk_2` FOREIGN KEY (`id_compra_detalle`) REFERENCES `compra_detalles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.inventario_lotes: ~22 rows (aproximadamente)
DELETE FROM `inventario_lotes`;
INSERT INTO `inventario_lotes` (`id`, `id_producto`, `id_compra_detalle`, `codigo_lote`, `fecha_vencimiento`, `cantidad_inicial`, `cantidad_disponible`, `estado`) VALUES
	(1, 1, NULL, 'L-PAN-2027', '2027-12-31', 500, 480, 1),
	(2, 2, NULL, 'L-AMX-2028', '2028-05-15', 150, 120, 1),
	(3, 3, NULL, 'L-ALR-RED', '2026-04-21', 300, 150, 1),
	(4, 4, NULL, 'L-SUP-2027', '2027-08-10', 50, 45, 1),
	(5, 5, NULL, 'L-COL-2030', '2030-01-01', 100, 60, 1),
	(6, 6, NULL, 'L-IBU-YELLOW', '2026-05-24', 400, 310, 1),
	(7, 7, NULL, 'L-LOS-2028', '2028-09-22', 200, 180, 1),
	(8, 8, NULL, 'L-GRI-2027', '2027-03-30', 300, 225, 1),
	(9, 9, NULL, 'L-SAL-2029', '2029-07-28', 200, 140, 1),
	(10, 10, NULL, 'L-SHA-2028', '2028-11-11', 50, 30, 1),
	(11, 11, NULL, 'L-VIT-2027', '2027-04-05', 20, 10, 1),
	(12, 12, NULL, 'L-AZI-2028', '2028-02-14', 15, 12, 1),
	(13, 22, NULL, 'XP-22', '2027-04-18', 300, 300, 1),
	(14, 21, NULL, 'XP-21', '2027-04-18', 300, 300, 1),
	(15, 20, NULL, 'XP-20', '2027-04-18', 300, 290, 1),
	(16, 19, NULL, 'XP-19', '2027-04-18', 300, 300, 1),
	(17, 18, NULL, 'XP-18', '2027-04-18', 300, 300, 1),
	(18, 17, NULL, 'XP-17', '2027-04-18', 300, 300, 1),
	(19, 16, NULL, 'XP-16', '2027-04-18', 300, 300, 1),
	(20, 15, NULL, 'XP-15', '2027-04-18', 300, 300, 1),
	(21, 14, NULL, 'XP-14', '2027-04-18', 300, 300, 1),
	(22, 13, NULL, 'XP-13', '2027-04-18', 300, 300, 1);

-- Volcando estructura para tabla botica_db.kardex
CREATE TABLE IF NOT EXISTS `kardex` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `id_usuario` int NOT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA','AJUSTE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` int NOT NULL,
  `saldo_actual` int NOT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_producto` (`id_producto`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `kardex_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kardex_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.kardex: ~14 rows (aproximadamente)
DELETE FROM `kardex`;
INSERT INTO `kardex` (`id`, `id_producto`, `id_usuario`, `tipo_movimiento`, `motivo`, `cantidad`, `saldo_actual`, `fecha`) VALUES
	(1, 1, 1, 'ENTRADA', 'Inventario Inicial Simulado', 480, 480, '2026-04-09 07:20:09'),
	(2, 2, 1, 'ENTRADA', 'Inventario Inicial Simulado', 120, 120, '2026-04-09 07:20:09'),
	(3, 3, 1, 'ENTRADA', 'Inventario Inicial Simulado', 250, 250, '2026-04-09 07:20:09'),
	(4, 4, 1, 'ENTRADA', 'Inventario Inicial Simulado', 45, 45, '2026-04-09 07:20:09'),
	(5, 5, 1, 'ENTRADA', 'Inventario Inicial Simulado', 60, 60, '2026-04-09 07:20:09'),
	(6, 6, 1, 'ENTRADA', 'Inventario Inicial Simulado', 310, 310, '2026-04-09 07:20:09'),
	(7, 7, 1, 'ENTRADA', 'Inventario Inicial Simulado', 180, 180, '2026-04-09 07:20:09'),
	(8, 8, 1, 'ENTRADA', 'Inventario Inicial Simulado', 225, 225, '2026-04-09 07:20:09'),
	(9, 9, 1, 'ENTRADA', 'Inventario Inicial Simulado', 140, 140, '2026-04-09 07:20:09'),
	(10, 10, 1, 'ENTRADA', 'Inventario Inicial Simulado', 30, 30, '2026-04-09 07:20:09'),
	(11, 11, 1, 'ENTRADA', 'Inventario Inicial Simulado', 10, 10, '2026-04-09 07:20:09'),
	(12, 12, 1, 'ENTRADA', 'Inventario Inicial Simulado', 12, 12, '2026-04-09 07:20:09'),
	(13, 3, 1, 'SALIDA', 'Venta Ticket T001-041848', 100, 150, '2026-04-18 06:57:24'),
	(14, 20, 1, 'SALIDA', 'Venta Ticket T001-041848', 10, 290, '2026-04-18 06:57:24');

-- Volcando estructura para tabla botica_db.laboratorios
CREATE TABLE IF NOT EXISTS `laboratorios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.laboratorios: ~5 rows (aproximadamente)
DELETE FROM `laboratorios`;
INSERT INTO `laboratorios` (`id`, `nombre`, `descripcion`, `estado`) VALUES
	(1, 'Bayer S.A.', NULL, 1),
	(2, 'Pfizer', NULL, 1),
	(3, 'Genfar', NULL, 1),
	(4, 'Farmindustria', NULL, 1),
	(5, 'Teva Perú', NULL, 1);

-- Volcando estructura para tabla botica_db.productos
CREATE TABLE IF NOT EXISTS `productos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo_barras` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_generico` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_comercial` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `concentracion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forma_farmaceutica` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_laboratorio` int DEFAULT NULL,
  `id_categoria` int DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio_venta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `margen_ganancia` decimal(5,2) NOT NULL DEFAULT '0.00',
  `unidad_medida` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requiere_receta` tinyint(1) DEFAULT '0',
  `stock_actual` int NOT NULL DEFAULT '0',
  `stock_minimo` int NOT NULL DEFAULT '10',
  `estado` tinyint(1) DEFAULT '1',
  `fraccionable` tinyint(1) DEFAULT '0',
  `unidades_por_caja` int DEFAULT '1',
  `unidad_fraccion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_fraccion` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_barras` (`codigo_barras`),
  KEY `id_laboratorio` (`id_laboratorio`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_laboratorio`) REFERENCES `laboratorios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.productos: ~22 rows (aproximadamente)
DELETE FROM `productos`;
INSERT INTO `productos` (`id`, `codigo_barras`, `nombre_generico`, `nombre_comercial`, `concentracion`, `forma_farmaceutica`, `id_laboratorio`, `id_categoria`, `precio_compra`, `precio_venta`, `margen_ganancia`, `unidad_medida`, `requiere_receta`, `stock_actual`, `stock_minimo`, `estado`, `fraccionable`, `unidades_por_caja`, `unidad_fraccion`, `precio_fraccion`) VALUES
	(1, '77512345001', 'Paracetamol', 'Panadol Fuerte', '500mg', 'Tableta', 1, 1, 15.00, 25.00, 40.00, 'Caja', 0, 480, 50, 1, 1, 100, 'Pastilla', 0.50),
	(2, '77512345002', 'Amoxicilina', 'Amoxil', '500mg', 'Cápsula', 2, 2, 20.00, 35.00, 42.00, 'Caja', 1, 120, 20, 1, 1, 50, 'Cápsula', 1.00),
	(3, '77512345003', 'Cetirizina', 'Alercet', '10mg', 'Tableta', 3, 3, 10.00, 18.00, 44.00, 'Caja', 0, 150, 50, 1, 1, 100, 'Pastilla', 0.30),
	(4, '77512345004', 'Multivitamínico', 'Supradyn', '1g', 'Tableta Ef', 1, 4, 12.00, 20.00, 40.00, 'Tubo', 0, 45, 10, 1, 0, 1, 'Unidad', 0.00),
	(5, '77512345005', 'Crema Dental', 'Colgate Total 12', '150g', 'Crema', 4, 5, 8.00, 12.00, 33.00, 'Unidad', 0, 60, 15, 1, 0, 1, 'Unidad', 0.00),
	(6, '77512345006', 'Ibuprofeno', 'Ibuprofeno Genfar', '400mg', 'Tableta', 3, 1, 5.00, 10.00, 50.00, 'Caja', 0, 310, 50, 1, 1, 100, 'Pastilla', 0.20),
	(7, '77512345007', 'Losartán', 'Losartán Potásico', '50mg', 'Tableta', 4, 1, 18.00, 28.00, 35.00, 'Caja', 1, 180, 50, 1, 1, 50, 'Pastilla', 0.80),
	(8, '77512345008', 'Paracetamol + Clorf', 'Gripeal', '500mg/2mg', 'Cápsula', 5, 1, 14.00, 22.00, 36.00, 'Caja', 0, 225, 40, 1, 1, 100, 'Cápsula', 0.40),
	(9, '77512345009', 'Bicarbonato', 'Sal de Andrews', '5g', 'Polvo', 2, 1, 25.00, 35.00, 28.00, 'Caja', 0, 140, 25, 1, 1, 50, 'Sobre', 1.00),
	(10, '77512345010', 'Piritiona Zinc', 'Shampoo H&S', '375ml', 'Líquido', 4, 5, 15.00, 22.00, 31.00, 'Unidad', 0, 30, 10, 1, 0, 1, 'Unidad', 0.00),
	(11, '77512345011', 'Ácido Ascórbico', 'Vitamina C', '500mg', 'Masticable', 5, 4, 10.00, 15.00, 33.00, 'Frasco', 0, 10, 20, 1, 0, 1, 'Unidad', 0.00),
	(12, '77512345012', 'Azitromicina', 'Azitromicina', '500mg', 'Tableta', 3, 2, 20.00, 30.00, 33.00, 'Caja', 1, 12, 15, 1, 1, 3, 'Tableta', 12.00),
	(13, '7751234567901', 'Amoxicilina', 'Moxilin 500', '500mg', 'Tableta', 1, 2, 0.50, 1.20, 140.00, 'UNIDAD', 0, 300, 100, 1, 1, 10, 'Tableta', 1.50),
	(14, '7751234567902', 'Azitromicina', 'Zitromax', '500mg', 'Tableta', 2, 2, 1.20, 4.50, 275.00, 'UNIDAD', 0, 300, 50, 1, 0, 1, NULL, 0.00),
	(15, '7751234567903', 'Paracetamol', 'Panadol Ni├▒os', '120mg/5ml', 'Jarabe', 1, 1, 4.50, 8.50, 88.00, 'FRASCO', 0, 300, 20, 1, 0, 1, NULL, 0.00),
	(16, '7751234567904', 'Clorfenamina', 'Alergistat', '4mg', 'Tableta', 3, 3, 0.10, 0.50, 400.00, 'UNIDAD', 0, 300, 200, 1, 1, 20, 'Tableta', 0.60),
	(17, '7751234567905', 'Vitamina C', 'Redoxon', '1g', 'Efervescente', 1, 4, 12.00, 18.00, 50.00, 'TUBO', 0, 300, 30, 1, 0, 1, NULL, 0.00),
	(18, '7751234567906', 'Naproxeno', 'Apronax', '550mg', 'Tableta', 1, 1, 0.80, 2.50, 212.00, 'UNIDAD', 0, 300, 100, 1, 1, 10, 'Tableta', 2.80),
	(19, '7751234567907', 'Omeprazol', 'Gastrolen', '20mg', 'C├ípsula', 4, 1, 0.30, 1.00, 233.00, 'UNIDAD', 0, 300, 150, 1, 1, 15, 'C├ípsula', 1.20),
	(20, '7751234567908', 'Cetirizina', 'Alerfast', '10mg', 'Tableta', 5, 3, 0.20, 0.80, 300.00, 'UNIDAD', 0, 290, 100, 1, 1, 10, 'Tableta', 1.00),
	(21, '7751234567909', 'Complejo B', 'Neurobi├│n', 'Inyectable', 'Ampolla', 1, 4, 5.00, 12.00, 140.00, 'AMPOLLA', 0, 300, 40, 1, 0, 1, NULL, 0.00),
	(22, '7751234567910', 'Loratadina', 'Claritin', '10mg', 'Tableta', 2, 3, 0.40, 1.50, 275.00, 'UNIDAD', 0, 300, 100, 1, 1, 10, 'Tableta', 1.80);

-- Volcando estructura para tabla botica_db.proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ruc` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razon_social` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `representante` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruc` (`ruc`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.proveedores: ~13 rows (aproximadamente)
DELETE FROM `proveedores`;
INSERT INTO `proveedores` (`id`, `ruc`, `razon_social`, `representante`, `telefono`, `direccion`, `estado`) VALUES
	(1, '20546781234', 'Droguería Del Sur S.A.C.', NULL, '987654321', 'Av. Sur 123, Lima', 1),
	(2, '20123456789', 'Distribuidora Continental S.A.', NULL, '912345678', 'Industrial 45, Lima', 1),
	(3, '20456123789', 'Medifarma Distribuidores', NULL, '998877665', 'Km 23 Panamericana', 1),
	(4, '20100200301', 'Distribuidora FarmaOriente S.A.C.', 'Ing. Ricardo Arana', '044-203040', 'Lima, Santa Anita', 1),
	(5, '20506070802', 'Global Medicine Per├║', 'Lic. Carmen Rosa', '01-4556677', 'Av. Iquitos 455, La Victoria', 1),
	(6, '20443322114', 'Laboratorios Unidos S.A.', 'Sr. Jorge Valdivia', '01-2223344', 'Chorrillos, Lima', 1),
	(7, '20998877665', 'Qu├¡mica Suiza S.A.', 'Central de Pedidos', '01-2114000', 'Av. Paseo de la Rep├║blica', 1),
	(8, '20112233446', 'Droguer├¡a Los Olivos', 'Sra. Martha Vilchez', '044-506070', 'Trujillo, El Porvenir', 1),
	(9, '10456789011', 'Representaciones M├®dicas P&G', 'Pablo Gonzales', '999888777', 'Calle Real 123, Huancayo', 1),
	(10, '20556677889', 'Importaciones San Jos├®', 'Jos├® Santos', '01-3334455', 'Jr. Az├íngaro, Lima', 1),
	(11, '20667788990', 'Corporaci├│n M├®dica del Norte', 'Lilian Ruiz', '044-445566', 'Trujillo, Centro', 1),
	(12, '20778899001', 'Per├║ Farma Log├¡stica', 'Mario Vargas', '01-6667788', 'Lur├¡n, Almacenes', 1),
	(13, '20889900112', 'BioTech Soluciones', 'Dra. Sandra Sol├¡s', '977665544', 'Miraflores, Lima', 1);

-- Volcando estructura para tabla botica_db.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.roles: ~4 rows (aproximadamente)
DELETE FROM `roles`;
INSERT INTO `roles` (`id`, `nombre`, `descripcion`) VALUES
	(1, 'Administrador', 'Acceso total al sistema'),
	(2, 'Farmac├®utico', 'Gesti├│n de cat├ílogo m├®dico y almac├®n'),
	(3, 'Cajero', 'Acceso al Punto de Venta (POS) ├║nicamente'),
	(4, 'Almacenero', 'Gesti├│n de ingresos y kardex');

-- Volcando estructura para tabla botica_db.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rol_id` int NOT NULL,
  `estado` tinyint(1) DEFAULT '1',
  `ultimo_login` datetime DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  KEY `rol_id` (`rol_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.usuarios: ~1 rows (aproximadamente)
DELETE FROM `usuarios`;
INSERT INTO `usuarios` (`id`, `nombres`, `apellidos`, `usuario`, `password`, `email`, `rol_id`, `estado`, `ultimo_login`, `creado_en`) VALUES
	(1, 'Admin', 'Sistema', 'admin', '$2y$10$cAdM2fx.HNnhXycqInJ4IedrL19q3aWWq7CO.eeCGPc8ZaAJ6PieG', 'admin@botica.com', 1, 1, '2026-04-18 01:57:54', '2026-03-28 05:32:58');

-- Volcando estructura para tabla botica_db.ventas
CREATE TABLE IF NOT EXISTS `ventas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `caja_id` int DEFAULT NULL,
  `id_usuario` int NOT NULL,
  `tipo_comprobante` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Ticket',
  `serie_comprobante` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_comprobante` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_venta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(10,2) DEFAULT '0.00',
  `igv` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `metodo_pago` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Efectivo',
  `pago_recibido` decimal(12,2) DEFAULT NULL,
  `vuelto` decimal(12,2) DEFAULT NULL,
  `puntos_ganados` int DEFAULT '0',
  `puntos_usados` int DEFAULT '0',
  `medico_cmp` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Completada',
  PRIMARY KEY (`id`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.ventas: ~46 rows (aproximadamente)
DELETE FROM `ventas`;
INSERT INTO `ventas` (`id`, `id_cliente`, `caja_id`, `id_usuario`, `tipo_comprobante`, `serie_comprobante`, `num_comprobante`, `fecha_venta`, `subtotal`, `descuento`, `igv`, `total`, `metodo_pago`, `pago_recibido`, `vuelto`, `puntos_ganados`, `puntos_usados`, `medico_cmp`, `estado`) VALUES
	(1, 1, 1, 1, 'Boleta', 'B001', '000001', '2026-03-31 15:14:52', 100.85, 0.00, 18.15, 119.00, 'Efectivo', 120.00, 1.00, 0, 0, NULL, 'Completada'),
	(2, 1, 1, 1, 'Factura', 'F001', '000002', '2026-04-08 16:20:44', 1.69, 0.00, 0.31, 2.00, 'Efectivo', 10.00, 8.00, 0, 0, NULL, 'Completada'),
	(3, 5, 1, 1, 'Ticket', 'T001', '000003', '2026-04-06 18:24:11', 44.58, 0.00, 8.02, 52.60, 'Efectivo', 60.00, 7.40, 0, 0, NULL, 'Completada'),
	(4, 5, 1, 1, 'Ticket', 'T001', '000004', '2026-04-04 20:15:22', 3.39, 0.00, 0.61, 4.00, 'Yape/Plin', 4.00, 0.00, 0, 0, NULL, 'Completada'),
	(5, 5, 1, 1, 'Factura', 'F001', '000005', '2026-04-05 17:28:39', 15.25, 0.00, 2.75, 18.00, 'Tarjeta', 18.00, 0.00, 0, 0, NULL, 'Completada'),
	(6, 3, 1, 1, 'Ticket', 'T001', '000006', '2026-04-04 01:30:11', 0.42, 0.00, 0.08, 0.50, 'Yape/Plin', 0.50, 0.00, 0, 0, NULL, 'Completada'),
	(7, 3, 1, 1, 'Boleta', 'B001', '000007', '2026-04-10 00:49:11', 71.19, 0.00, 12.81, 84.00, 'Efectivo', 90.00, 6.00, 0, 0, NULL, 'Completada'),
	(8, 1, 1, 1, 'Ticket', 'T001', '000008', '2026-03-31 15:52:47', 134.75, 0.00, 24.25, 159.00, 'Efectivo', 160.00, 1.00, 0, 0, NULL, 'Completada'),
	(9, 5, 1, 1, 'Ticket', 'T001', '000009', '2026-04-04 23:45:35', 34.75, 0.00, 6.25, 41.00, 'Tarjeta', 41.00, 0.00, 0, 0, NULL, 'Completada'),
	(10, 5, 1, 1, 'Boleta', 'B001', '000010', '2026-04-05 16:33:43', 45.76, 0.00, 8.24, 54.00, 'Yape/Plin', 54.00, 0.00, 0, 0, NULL, 'Completada'),
	(11, 4, 1, 1, 'Ticket', 'T001', '000011', '2026-04-04 16:51:35', 17.29, 0.00, 3.11, 20.40, 'Efectivo', 30.00, 9.60, 0, 0, NULL, 'Completada'),
	(12, 3, 1, 1, 'Factura', 'F001', '000012', '2026-04-09 23:23:22', 155.93, 0.00, 28.07, 184.00, 'Tarjeta', 184.00, 0.00, 0, 0, NULL, 'Completada'),
	(13, 1, 1, 1, 'Boleta', 'B001', '000013', '2026-04-09 00:05:44', 189.83, 0.00, 34.17, 224.00, 'Efectivo', 230.00, 6.00, 0, 0, NULL, 'Completada'),
	(14, 1, 1, 1, 'Boleta', 'B001', '000014', '2026-04-09 22:36:03', 29.66, 0.00, 5.34, 35.00, 'Yape/Plin', 35.00, 0.00, 0, 0, NULL, 'Completada'),
	(15, 4, 1, 1, 'Boleta', 'B001', '000015', '2026-04-02 23:35:25', 105.93, 0.00, 19.07, 125.00, 'Efectivo', 130.00, 5.00, 0, 0, NULL, 'Completada'),
	(16, 3, 1, 1, 'Ticket', 'T001', '000016', '2026-04-04 22:08:13', 133.90, 0.00, 24.10, 158.00, 'Efectivo', 160.00, 2.00, 0, 0, NULL, 'Completada'),
	(17, 4, 1, 1, 'Boleta', 'B001', '000017', '2026-04-07 20:48:19', 45.76, 0.00, 8.24, 54.00, 'Efectivo', 60.00, 6.00, 0, 0, NULL, 'Completada'),
	(18, 4, 1, 1, 'Ticket', 'T001', '000018', '2026-04-10 00:47:29', 14.24, 0.00, 2.56, 16.80, 'Efectivo', 20.00, 3.20, 0, 0, NULL, 'Completada'),
	(19, 1, 1, 1, 'Boleta', 'B001', '000019', '2026-04-04 00:35:06', 74.58, 0.00, 13.42, 88.00, 'Yape/Plin', 88.00, 0.00, 0, 0, NULL, 'Completada'),
	(20, 2, 1, 1, 'Boleta', 'B001', '000020', '2026-04-09 17:20:09', 0.51, 0.00, 0.09, 0.60, 'Efectivo', 10.00, 9.40, 0, 0, NULL, 'Completada'),
	(21, 3, 1, 1, 'Factura', 'F001', '000021', '2026-04-01 16:17:31', 46.78, 0.00, 8.42, 55.20, 'Efectivo', 60.00, 4.80, 0, 0, NULL, 'Completada'),
	(22, 5, 1, 1, 'Ticket', 'T001', '000022', '2026-04-01 00:00:13', 0.51, 0.00, 0.09, 0.60, 'Efectivo', 10.00, 9.40, 0, 0, NULL, 'Completada'),
	(23, 4, 1, 1, 'Boleta', 'B001', '000023', '2026-04-02 14:45:17', 155.51, 0.00, 27.99, 183.50, 'Efectivo', 190.00, 6.50, 0, 0, NULL, 'Completada'),
	(24, 5, 1, 1, 'Ticket', 'T001', '000024', '2026-04-09 21:44:33', 37.12, 0.00, 6.68, 43.80, 'Tarjeta', 43.80, 0.00, 0, 0, NULL, 'Completada'),
	(25, 3, 1, 1, 'Ticket', 'T001', '000025', '2026-03-31 14:32:32', 88.98, 0.00, 16.02, 105.00, 'Efectivo', 110.00, 5.00, 0, 0, NULL, 'Completada'),
	(26, 3, 1, 1, 'Boleta', 'B001', '000026', '2026-04-03 15:32:57', 62.71, 0.00, 11.29, 74.00, 'Tarjeta', 74.00, 0.00, 0, 0, NULL, 'Completada'),
	(27, 1, 1, 1, 'Boleta', 'B001', '000027', '2026-04-08 18:55:10', 91.69, 0.00, 16.51, 108.20, 'Tarjeta', 108.20, 0.00, 0, 0, NULL, 'Completada'),
	(28, 4, 1, 1, 'Boleta', 'B001', '000028', '2026-04-03 18:51:52', 44.58, 0.00, 8.02, 52.60, 'Efectivo', 60.00, 7.40, 0, 0, NULL, 'Completada'),
	(29, 3, 1, 1, 'Factura', 'F001', '000029', '2026-03-31 21:50:20', 38.31, 0.00, 6.89, 45.20, 'Efectivo', 50.00, 4.80, 0, 0, NULL, 'Completada'),
	(30, 3, 1, 1, 'Ticket', 'T001', '000030', '2026-04-04 21:23:00', 169.49, 0.00, 30.51, 200.00, 'Tarjeta', 200.00, 0.00, 0, 0, NULL, 'Completada'),
	(31, 5, 1, 1, 'Ticket', 'T001', '000031', '2026-04-05 19:20:06', 86.44, 0.00, 15.56, 102.00, 'Efectivo', 110.00, 8.00, 0, 0, NULL, 'Completada'),
	(32, 1, 1, 1, 'Factura', 'F001', '000032', '2026-04-06 17:23:02', 36.61, 0.00, 6.59, 43.20, 'Tarjeta', 43.20, 0.00, 0, 0, NULL, 'Completada'),
	(33, 1, 1, 1, 'Factura', 'F001', '000033', '2026-04-07 15:52:54', 115.25, 0.00, 20.75, 136.00, 'Efectivo', 140.00, 4.00, 0, 0, NULL, 'Completada'),
	(34, 1, 1, 1, 'Ticket', 'T001', '000034', '2026-04-03 23:46:10', 20.34, 0.00, 3.66, 24.00, 'Efectivo', 30.00, 6.00, 0, 0, NULL, 'Completada'),
	(35, 3, 1, 1, 'Factura', 'F001', '000035', '2026-04-06 15:54:57', 18.90, 0.00, 3.40, 22.30, 'Efectivo', 30.00, 7.70, 0, 0, NULL, 'Completada'),
	(36, 1, 2, 1, 'Ticket', 'T001', '9001', '2026-04-12 06:51:21', 10.00, 0.00, 1.80, 11.80, 'Efectivo', NULL, NULL, 0, 0, NULL, 'Completada'),
	(37, 2, 2, 1, 'Ticket', 'T001', '9002', '2026-04-13 06:51:21', 20.00, 0.00, 3.60, 23.60, 'Efectivo', NULL, NULL, 0, 0, NULL, 'Completada'),
	(38, 3, 2, 1, 'Ticket', 'T001', '9003', '2026-04-14 06:51:21', 30.00, 0.00, 5.40, 35.40, 'Tarjeta', NULL, NULL, 0, 0, NULL, 'Completada'),
	(39, 4, 2, 1, 'Ticket', 'T001', '9004', '2026-04-15 06:51:21', 40.00, 0.00, 7.20, 47.20, 'Yape/Plin', NULL, NULL, 0, 0, NULL, 'Completada'),
	(40, 1, 2, 1, 'Ticket', 'T001', '9005', '2026-04-16 06:51:21', 50.00, 0.00, 9.00, 59.00, 'Efectivo', NULL, NULL, 0, 0, NULL, 'Completada'),
	(41, 2, 2, 1, 'Ticket', 'T001', '9006', '2026-04-17 06:51:21', 60.00, 0.00, 10.80, 70.80, 'Tarjeta', NULL, NULL, 0, 0, NULL, 'Completada'),
	(42, 3, 2, 1, 'Ticket', 'T001', '9007', '2026-04-18 06:51:21', 70.00, 0.00, 12.60, 82.60, 'Efectivo', NULL, NULL, 0, 0, NULL, 'Completada'),
	(43, 4, 2, 1, 'Ticket', 'T001', '9008', '2026-04-18 06:51:21', 80.00, 0.00, 14.40, 94.40, 'Efectivo', NULL, NULL, 0, 0, NULL, 'Completada'),
	(44, 1, 2, 1, 'Ticket', 'T001', '9009', '2026-04-18 00:51:21', 90.00, 0.00, 16.20, 106.20, 'Tarjeta', NULL, NULL, 0, 0, NULL, 'Completada'),
	(45, 2, 2, 1, 'Ticket', 'T001', '9010', '2026-04-18 05:51:21', 100.00, 0.00, 18.00, 118.00, 'Yape/Plin', NULL, NULL, 0, 0, NULL, 'Completada'),
	(46, 10, 2, 1, 'Ticket', 'T001', '041848', '2026-04-18 06:57:24', 15.93, 0.00, 2.87, 18.80, 'Efectivo', 20.00, 1.20, 18, 0, '', 'Completada');

-- Volcando estructura para tabla botica_db.venta_detalles
CREATE TABLE IF NOT EXISTS `venta_detalles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_venta` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `id_lote` int DEFAULT NULL,
  `tipo_unidad` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Caja',
  PRIMARY KEY (`id`),
  KEY `id_venta` (`id_venta`),
  KEY `id_producto` (`id_producto`),
  KEY `id_lote` (`id_lote`),
  CONSTRAINT `venta_detalles_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `venta_detalles_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `venta_detalles_ibfk_3` FOREIGN KEY (`id_lote`) REFERENCES `inventario_lotes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla botica_db.venta_detalles: ~70 rows (aproximadamente)
DELETE FROM `venta_detalles`;
INSERT INTO `venta_detalles` (`id`, `id_venta`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`, `id_lote`, `tipo_unidad`) VALUES
	(1, 1, 9, 1, 35.00, 35.00, NULL, 'CAJA'),
	(2, 1, 7, 3, 28.00, 84.00, NULL, 'CAJA'),
	(3, 2, 2, 2, 1.00, 2.00, NULL, 'FRACCION'),
	(4, 3, 3, 2, 0.30, 0.60, NULL, 'FRACCION'),
	(5, 3, 4, 2, 20.00, 40.00, NULL, 'CAJA'),
	(6, 3, 5, 1, 12.00, 12.00, NULL, 'CAJA'),
	(7, 4, 1, 4, 0.50, 2.00, NULL, 'FRACCION'),
	(8, 4, 2, 2, 1.00, 2.00, NULL, 'FRACCION'),
	(9, 5, 3, 1, 18.00, 18.00, NULL, 'CAJA'),
	(10, 6, 1, 1, 0.50, 0.50, NULL, 'FRACCION'),
	(11, 7, 7, 3, 28.00, 84.00, NULL, 'CAJA'),
	(12, 8, 3, 3, 18.00, 54.00, NULL, 'CAJA'),
	(13, 8, 9, 3, 35.00, 105.00, NULL, 'CAJA'),
	(14, 9, 1, 2, 0.50, 1.00, NULL, 'FRACCION'),
	(15, 9, 6, 4, 10.00, 40.00, NULL, 'CAJA'),
	(16, 10, 3, 3, 18.00, 54.00, NULL, 'CAJA'),
	(17, 11, 6, 2, 10.00, 20.00, NULL, 'CAJA'),
	(18, 11, 8, 1, 0.40, 0.40, NULL, 'FRACCION'),
	(19, 12, 5, 2, 12.00, 24.00, NULL, 'CAJA'),
	(20, 12, 6, 2, 10.00, 20.00, NULL, 'CAJA'),
	(21, 12, 2, 4, 35.00, 140.00, NULL, 'CAJA'),
	(22, 13, 7, 3, 28.00, 84.00, NULL, 'CAJA'),
	(23, 13, 2, 4, 35.00, 140.00, NULL, 'CAJA'),
	(24, 14, 2, 1, 35.00, 35.00, NULL, 'CAJA'),
	(25, 15, 9, 2, 35.00, 70.00, NULL, 'CAJA'),
	(26, 15, 2, 1, 35.00, 35.00, NULL, 'CAJA'),
	(27, 15, 6, 2, 10.00, 20.00, NULL, 'CAJA'),
	(28, 16, 9, 2, 35.00, 70.00, NULL, 'CAJA'),
	(29, 16, 8, 4, 22.00, 88.00, NULL, 'CAJA'),
	(30, 17, 3, 1, 18.00, 18.00, NULL, 'CAJA'),
	(31, 17, 5, 3, 12.00, 36.00, NULL, 'CAJA'),
	(32, 18, 7, 4, 0.80, 3.20, NULL, 'FRACCION'),
	(33, 18, 5, 1, 12.00, 12.00, NULL, 'CAJA'),
	(34, 18, 8, 4, 0.40, 1.60, NULL, 'FRACCION'),
	(35, 19, 10, 4, 22.00, 88.00, NULL, 'CAJA'),
	(36, 20, 6, 3, 0.20, 0.60, NULL, 'FRACCION'),
	(37, 21, 3, 3, 18.00, 54.00, NULL, 'CAJA'),
	(38, 21, 3, 4, 0.30, 1.20, NULL, 'FRACCION'),
	(39, 22, 3, 2, 0.30, 0.60, NULL, 'FRACCION'),
	(40, 23, 7, 4, 28.00, 112.00, NULL, 'CAJA'),
	(41, 23, 1, 3, 0.50, 1.50, NULL, 'FRACCION'),
	(42, 23, 2, 2, 35.00, 70.00, NULL, 'CAJA'),
	(43, 24, 8, 2, 0.40, 0.80, NULL, 'FRACCION'),
	(44, 24, 9, 3, 1.00, 3.00, NULL, 'FRACCION'),
	(45, 24, 4, 2, 20.00, 40.00, NULL, 'CAJA'),
	(46, 25, 9, 3, 35.00, 105.00, NULL, 'CAJA'),
	(47, 26, 9, 2, 1.00, 2.00, NULL, 'FRACCION'),
	(48, 26, 3, 4, 18.00, 72.00, NULL, 'CAJA'),
	(49, 27, 9, 3, 35.00, 105.00, NULL, 'CAJA'),
	(50, 27, 7, 4, 0.80, 3.20, NULL, 'FRACCION'),
	(51, 28, 2, 4, 1.00, 4.00, NULL, 'FRACCION'),
	(52, 28, 5, 4, 12.00, 48.00, NULL, 'CAJA'),
	(53, 28, 6, 3, 0.20, 0.60, NULL, 'FRACCION'),
	(54, 29, 8, 3, 0.40, 1.20, NULL, 'FRACCION'),
	(55, 29, 10, 2, 22.00, 44.00, NULL, 'CAJA'),
	(56, 30, 7, 2, 28.00, 56.00, NULL, 'CAJA'),
	(57, 30, 9, 4, 1.00, 4.00, NULL, 'FRACCION'),
	(58, 30, 2, 4, 35.00, 140.00, NULL, 'CAJA'),
	(59, 31, 4, 4, 20.00, 80.00, NULL, 'CAJA'),
	(60, 31, 10, 1, 22.00, 22.00, NULL, 'CAJA'),
	(61, 32, 7, 4, 0.80, 3.20, NULL, 'FRACCION'),
	(62, 32, 6, 4, 10.00, 40.00, NULL, 'CAJA'),
	(63, 33, 10, 3, 22.00, 66.00, NULL, 'CAJA'),
	(64, 33, 2, 2, 35.00, 70.00, NULL, 'CAJA'),
	(65, 34, 10, 1, 22.00, 22.00, NULL, 'CAJA'),
	(66, 34, 2, 2, 1.00, 2.00, NULL, 'FRACCION'),
	(67, 35, 10, 1, 22.00, 22.00, NULL, 'CAJA'),
	(68, 35, 3, 1, 0.30, 0.30, NULL, 'FRACCION'),
	(69, 46, 3, 100, 0.18, 18.00, 3, 'Pastilla'),
	(70, 46, 20, 10, 0.08, 0.80, 15, 'Tableta');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
