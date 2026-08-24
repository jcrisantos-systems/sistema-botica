USE `botica_db`;

-- 1. Clientes (10)
INSERT INTO `clientes` (`tipo_documento`, `num_documento`, `nombres`, `telefono`, `direccion`, `puntos_acumulados`) VALUES
('DNI', '10203040', 'Juan Pérez García', '988123456', 'Av. Larco 123, Trujillo', 50),
('DNI', '20304050', 'María Rodríguez Paz', '977234567', 'Calle Pizarro 456', 120),
('DNI', '30405060', 'Carlos Sánchez Ruiz', '966345678', 'Urb. El Recreo C-12', 0),
('DNI', '40506070', 'Ana López Villacorta', '955456789', 'Av. España 789', 240),
('DNI', '50607080', 'Roberto Gómez Castro', '944567890', 'Jr. Junín 321', 15),
('DNI', '60708090', 'Luis Torres Mendoza', '933678901', 'Calle San Martín 555', 85),
('DNI', '70809001', 'Elena Vargas Solís', '922789012', 'Av. América Sur 101', 300),
('DNI', '80900112', 'Pedro Castillo Luna', '911890123', 'Calle Las Gemas 202', 45),
('DNI', '90011223', 'Sofía Ramírez Vega', '900901234', 'Urb. California G-5', 60),
('DNI', '01122334', 'Miguel Huamán Jara', '988012345', 'Av. Mansiche 900', 10);

-- 2. Proveedores (10)
INSERT INTO `proveedores` (`ruc`, `razon_social`, `representante`, `telefono`, `direccion`) VALUES
('20100200301', 'Distribuidora FarmaOriente S.A.C.', 'Ing. Ricardo Arana', '044-203040', 'Lima, Santa Anita'),
('20506070802', 'Global Medicine Perú', 'Lic. Carmen Rosa', '01-4556677', 'Av. Iquitos 455, La Victoria'),
('20443322114', 'Laboratorios Unidos S.A.', 'Sr. Jorge Valdivia', '01-2223344', 'Chorrillos, Lima'),
('20998877665', 'Química Suiza S.A.', 'Central de Pedidos', '01-2114000', 'Av. Paseo de la República'),
('20112233446', 'Droguería Los Olivos', 'Sra. Martha Vilchez', '044-506070', 'Trujillo, El Porvenir'),
('10456789011', 'Representaciones Médicas P&G', 'Pablo Gonzales', '999888777', 'Calle Real 123, Huancayo'),
('20556677889', 'Importaciones San José', 'José Santos', '01-3334455', 'Jr. Azángaro, Lima'),
('20667788990', 'Corporación Médica del Norte', 'Lilian Ruiz', '044-445566', 'Trujillo, Centro'),
('20778899001', 'Perú Farma Logística', 'Mario Vargas', '01-6667788', 'Lurín, Almacenes'),
('20889900112', 'BioTech Soluciones', 'Dra. Sandra Solís', '977665544', 'Miraflores, Lima');

-- 3. Productos (10)
INSERT INTO `productos` (`codigo_barras`, `nombre_generico`, `nombre_comercial`, `concentracion`, `forma_farmaceutica`, `id_laboratorio`, `id_categoria`, `precio_compra`, `precio_venta`, `margen_ganancia`, `unidad_medida`, `stock_actual`, `stock_minimo`, `fraccionable`, `unidades_por_caja`, `unidad_fraccion`, `precio_fraccion`) VALUES
('7751234567901', 'Amoxicilina', 'Moxilin 500', '500mg', 'Tableta', 1, 2, 0.50, 1.20, 140, 'UNIDAD', 0, 100, 1, 10, 'Tableta', 1.50),
('7751234567902', 'Azitromicina', 'Zitromax', '500mg', 'Tableta', 2, 2, 1.20, 4.50, 275, 'UNIDAD', 0, 50, 0, 1, NULL, 0.00),
('7751234567903', 'Paracetamol', 'Panadol Niños', '120mg/5ml', 'Jarabe', 1, 1, 4.50, 8.50, 88, 'FRASCO', 0, 20, 0, 1, NULL, 0.00),
('7751234567904', 'Clorfenamina', 'Alergistat', '4mg', 'Tableta', 3, 3, 0.10, 0.50, 400, 'UNIDAD', 0, 200, 1, 20, 'Tableta', 0.60),
('7751234567905', 'Vitamina C', 'Redoxon', '1g', 'Efervescente', 1, 4, 12.00, 18.00, 50, 'TUBO', 0, 30, 0, 1, NULL, 0.00),
('7751234567906', 'Naproxeno', 'Apronax', '550mg', 'Tableta', 1, 1, 0.80, 2.50, 212, 'UNIDAD', 0, 100, 1, 10, 'Tableta', 2.80),
('7751234567907', 'Omeprazol', 'Gastrolen', '20mg', 'Cápsula', 4, 1, 0.30, 1.00, 233, 'UNIDAD', 0, 150, 1, 15, 'Cápsula', 1.20),
('7751234567908', 'Cetirizina', 'Alerfast', '10mg', 'Tableta', 5, 3, 0.20, 0.80, 300, 'UNIDAD', 0, 100, 1, 10, 'Tableta', 1.00),
('7751234567909', 'Complejo B', 'Neurobión', 'Inyectable', 'Ampolla', 1, 4, 5.00, 12.00, 140, 'AMPOLLA', 0, 40, 0, 1, NULL, 0.00),
('7751234567910', 'Loratadina', 'Claritin', '10mg', 'Tableta', 2, 3, 0.40, 1.50, 275, 'UNIDAD', 0, 100, 1, 10, 'Tableta', 1.80);

-- 4. Apertura de Caja (verificar estructura real)
INSERT INTO `cajas` (`usuario_id`, `fecha_apertura`, `monto_inicial`, `estado`) 
SELECT 1, NOW(), 500.00, 1
WHERE NOT EXISTS (SELECT 1 FROM cajas WHERE estado = 1);

SET @last_caja_id = (SELECT id FROM cajas WHERE estado = 1 LIMIT 1);

-- 5. Compras (10)
INSERT INTO `compras` (`id_proveedor`, `id_usuario`, `tipo_comprobante`, `serie_comprobante`, `num_comprobante`, `fecha_compra`, `total`, `estado`) VALUES
(1, 1, 'Factura', 'F001', '5001', DATE_SUB(CURDATE(), INTERVAL 8 DAY), 500.00, 'Completada'),
(2, 1, 'Factura', 'F001', '5002', DATE_SUB(CURDATE(), INTERVAL 7 DAY), 400.00, 'Completada'),
(3, 1, 'Factura', 'F002', '5003', DATE_SUB(CURDATE(), INTERVAL 6 DAY), 300.00, 'Completada'),
(4, 1, 'Factura', 'F002', '5004', DATE_SUB(CURDATE(), INTERVAL 5 DAY), 600.00, 'Completada'),
(5, 1, 'Factura', 'F003', '5005', DATE_SUB(CURDATE(), INTERVAL 4 DAY), 250.00, 'Completada'),
(6, 1, 'Factura', 'F003', '5006', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 900.00, 'Completada'),
(7, 1, 'Factura', 'F004', '5007', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 100.00, 'Completada'),
(8, 1, 'Factura', 'F004', '5008', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 200.00, 'Completada'),
(9, 1, 'Factura', 'F005', '5009', CURDATE(), 750.00, 'Completada'),
(10, 1, 'Factura', 'F005', '5010', CURDATE(), 1000.00, 'Pendiente');

-- 6. Lotes (Alimentar stock)
INSERT INTO `inventario_lotes` (`id_producto`, `codigo_lote`, `fecha_vencimiento`, `cantidad_inicial`, `cantidad_disponible`)
SELECT id, CONCAT('XP-', id), DATE_ADD(CURDATE(), INTERVAL 12 MONTH), 300, 300 
FROM productos ORDER BY id DESC LIMIT 10;

-- Actualizamos el stock_actual
UPDATE productos p 
JOIN (SELECT id_producto, SUM(cantidad_disponible) as total FROM inventario_lotes WHERE estado = 1 GROUP BY id_producto) l 
ON p.id = l.id_producto 
SET p.stock_actual = l.total;

-- 7. Ventas (10)
INSERT INTO `ventas` (`id_cliente`, `caja_id`, `id_usuario`, `tipo_comprobante`, `serie_comprobante`, `num_comprobante`, `fecha_venta`, `subtotal`, `igv`, `total`, `metodo_pago`) VALUES
(1, @last_caja_id, 1, 'Ticket', 'T001', '9001', DATE_SUB(NOW(), INTERVAL 6 DAY), 10.00, 1.80, 11.80, 'Efectivo'),
(2, @last_caja_id, 1, 'Ticket', 'T001', '9002', DATE_SUB(NOW(), INTERVAL 5 DAY), 20.00, 3.60, 23.60, 'Efectivo'),
(3, @last_caja_id, 1, 'Ticket', 'T001', '9003', DATE_SUB(NOW(), INTERVAL 4 DAY), 30.00, 5.40, 35.40, 'Tarjeta'),
(4, @last_caja_id, 1, 'Ticket', 'T001', '9004', DATE_SUB(NOW(), INTERVAL 3 DAY), 40.00, 7.20, 47.20, 'Yape/Plin'),
(1, @last_caja_id, 1, 'Ticket', 'T001', '9005', DATE_SUB(NOW(), INTERVAL 2 DAY), 50.00, 9.00, 59.00, 'Efectivo'),
(2, @last_caja_id, 1, 'Ticket', 'T001', '9006', DATE_SUB(NOW(), INTERVAL 1 DAY), 60.00, 10.80, 70.80, 'Tarjeta'),
(3, @last_caja_id, 1, 'Ticket', 'T001', '9007', NOW(), 70.00, 12.60, 82.60, 'Efectivo'),
(4, @last_caja_id, 1, 'Ticket', 'T001', '9008', NOW(), 80.00, 14.40, 94.40, 'Efectivo'),
(1, @last_caja_id, 1, 'Ticket', 'T001', '9009', DATE_SUB(NOW(), INTERVAL 6 HOUR), 90.00, 16.20, 106.20, 'Tarjeta'),
(2, @last_caja_id, 1, 'Ticket', 'T001', '9010', DATE_SUB(NOW(), INTERVAL 1 HOUR), 100.00, 18.00, 118.00, 'Yape/Plin');

-- 8. Audit Logs
INSERT INTO `audit_accesos` (`id_usuario`, `accion`, `ip_address`, `user_agent`) VALUES
(1, 'LOGIN', '127.0.0.1', 'Mozilla/5.0 Seeding'),
(1, 'LOGOUT', '127.0.0.1', 'Mozilla/5.0 Seeding'),
(1, 'LOGIN', '192.168.1.10', 'Chrome/120'),
(1, 'LOGIN', '10.0.0.1', 'Firefox/121'),
(1, 'LOGIN', '127.0.0.1', 'Seed Agent'),
(1, 'LOGOUT', '127.0.0.1', 'Seed Agent'),
(1, 'LOGIN', '172.16.0.4', 'Safari/17'),
(1, 'LOGIN', '192.168.0.25', 'Edge/119'),
(1, 'LOGOUT', '192.168.0.25', 'Edge/119'),
(1, 'LOGIN', '10.10.10.10', 'Mobile Android');

INSERT INTO `audit_acciones` (`id_usuario`, `modulo`, `accion`, `descripcion`, `monto_afectado`) VALUES
(1, 'Seguridad', 'SEED', 'Poblamiento masivo de datos de prueba', 0),
(1, 'Productos', 'EDITAR', 'Prueba de auditoría en productos', 0),
(1, 'Ventas', 'ANULAR', 'Prueba de anulación auditada', 40.00),
(1, 'Inventario', 'AJUSTE', 'Ajuste de stock por vencimiento', 0),
(1, 'Caja', 'EGRESO', 'Pago de servicios básicos', 120.00),
(1, 'Compras', 'CREAR', 'Registro de compra Bayer', 500.00),
(1, 'Clientes', 'PUNTOS', 'Canje de puntos fidelidad exitoso', 0),
(1, 'Configuracion', 'LOGO', 'Actualización de imagen corporativa', 0),
(1, 'Auditoria', 'LIMPIEZA', 'Optimización de base de datos', 0),
(1, 'Inventario', 'FISICO', 'Toma de inventario trimestral', 0);
