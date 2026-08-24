-- Corrige en TODAS las tablas el texto cuyos bytes UTF-8 fueron mal interpretados con una
-- codepage CP437/CP850 y vueltos a guardar como UTF-8 (p.ej. 'Qu├¡mica' -> 'Química').
-- Cada UPDATE solo toca las filas que contienen el artefacto '├' (marca inequívoca de la
-- corrupción); las columnas/filas ya correctas quedan intactas porque el WHERE no las alcanza.

UPDATE audit_accesos SET ip_address = CONVERT(CAST(CONVERT(ip_address USING cp850) AS BINARY) USING utf8mb4) WHERE ip_address LIKE '%├%';
UPDATE audit_accesos SET user_agent = CONVERT(CAST(CONVERT(user_agent USING cp850) AS BINARY) USING utf8mb4) WHERE user_agent LIKE '%├%';

UPDATE audit_acciones SET modulo = CONVERT(CAST(CONVERT(modulo USING cp850) AS BINARY) USING utf8mb4) WHERE modulo LIKE '%├%';
UPDATE audit_acciones SET accion = CONVERT(CAST(CONVERT(accion USING cp850) AS BINARY) USING utf8mb4) WHERE accion LIKE '%├%';
UPDATE audit_acciones SET descripcion = CONVERT(CAST(CONVERT(descripcion USING cp850) AS BINARY) USING utf8mb4) WHERE descripcion LIKE '%├%';

UPDATE caja_movimientos SET motivo = CONVERT(CAST(CONVERT(motivo USING cp850) AS BINARY) USING utf8mb4) WHERE motivo LIKE '%├%';

UPDATE cajas SET observacion = CONVERT(CAST(CONVERT(observacion USING cp850) AS BINARY) USING utf8mb4) WHERE observacion LIKE '%├%';

UPDATE categorias SET nombre = CONVERT(CAST(CONVERT(nombre USING cp850) AS BINARY) USING utf8mb4) WHERE nombre LIKE '%├%';
UPDATE categorias SET descripcion = CONVERT(CAST(CONVERT(descripcion USING cp850) AS BINARY) USING utf8mb4) WHERE descripcion LIKE '%├%';

UPDATE clientes SET tipo_documento = CONVERT(CAST(CONVERT(tipo_documento USING cp850) AS BINARY) USING utf8mb4) WHERE tipo_documento LIKE '%├%';
UPDATE clientes SET num_documento = CONVERT(CAST(CONVERT(num_documento USING cp850) AS BINARY) USING utf8mb4) WHERE num_documento LIKE '%├%';
UPDATE clientes SET nombres = CONVERT(CAST(CONVERT(nombres USING cp850) AS BINARY) USING utf8mb4) WHERE nombres LIKE '%├%';
UPDATE clientes SET telefono = CONVERT(CAST(CONVERT(telefono USING cp850) AS BINARY) USING utf8mb4) WHERE telefono LIKE '%├%';
UPDATE clientes SET direccion = CONVERT(CAST(CONVERT(direccion USING cp850) AS BINARY) USING utf8mb4) WHERE direccion LIKE '%├%';

UPDATE compras SET tipo_comprobante = CONVERT(CAST(CONVERT(tipo_comprobante USING cp850) AS BINARY) USING utf8mb4) WHERE tipo_comprobante LIKE '%├%';
UPDATE compras SET serie_comprobante = CONVERT(CAST(CONVERT(serie_comprobante USING cp850) AS BINARY) USING utf8mb4) WHERE serie_comprobante LIKE '%├%';
UPDATE compras SET num_comprobante = CONVERT(CAST(CONVERT(num_comprobante USING cp850) AS BINARY) USING utf8mb4) WHERE num_comprobante LIKE '%├%';
UPDATE compras SET estado = CONVERT(CAST(CONVERT(estado USING cp850) AS BINARY) USING utf8mb4) WHERE estado LIKE '%├%';

UPDATE compras_devoluciones SET num_documento_prov = CONVERT(CAST(CONVERT(num_documento_prov USING cp850) AS BINARY) USING utf8mb4) WHERE num_documento_prov LIKE '%├%';
UPDATE compras_devoluciones SET motivo = CONVERT(CAST(CONVERT(motivo USING cp850) AS BINARY) USING utf8mb4) WHERE motivo LIKE '%├%';

UPDATE configuracion SET clave = CONVERT(CAST(CONVERT(clave USING cp850) AS BINARY) USING utf8mb4) WHERE clave LIKE '%├%';
UPDATE configuracion SET valor = CONVERT(CAST(CONVERT(valor USING cp850) AS BINARY) USING utf8mb4) WHERE valor LIKE '%├%';
UPDATE configuracion SET descripcion = CONVERT(CAST(CONVERT(descripcion USING cp850) AS BINARY) USING utf8mb4) WHERE descripcion LIKE '%├%';

UPDATE inventario_auditorias SET observaciones = CONVERT(CAST(CONVERT(observaciones USING cp850) AS BINARY) USING utf8mb4) WHERE observaciones LIKE '%├%';

UPDATE inventario_lotes SET codigo_lote = CONVERT(CAST(CONVERT(codigo_lote USING cp850) AS BINARY) USING utf8mb4) WHERE codigo_lote LIKE '%├%';

UPDATE kardex SET motivo = CONVERT(CAST(CONVERT(motivo USING cp850) AS BINARY) USING utf8mb4) WHERE motivo LIKE '%├%';

UPDATE laboratorios SET nombre = CONVERT(CAST(CONVERT(nombre USING cp850) AS BINARY) USING utf8mb4) WHERE nombre LIKE '%├%';
UPDATE laboratorios SET descripcion = CONVERT(CAST(CONVERT(descripcion USING cp850) AS BINARY) USING utf8mb4) WHERE descripcion LIKE '%├%';

UPDATE productos SET codigo_barras = CONVERT(CAST(CONVERT(codigo_barras USING cp850) AS BINARY) USING utf8mb4) WHERE codigo_barras LIKE '%├%';
UPDATE productos SET nombre_generico = CONVERT(CAST(CONVERT(nombre_generico USING cp850) AS BINARY) USING utf8mb4) WHERE nombre_generico LIKE '%├%';
UPDATE productos SET nombre_comercial = CONVERT(CAST(CONVERT(nombre_comercial USING cp850) AS BINARY) USING utf8mb4) WHERE nombre_comercial LIKE '%├%';
UPDATE productos SET concentracion = CONVERT(CAST(CONVERT(concentracion USING cp850) AS BINARY) USING utf8mb4) WHERE concentracion LIKE '%├%';
UPDATE productos SET forma_farmaceutica = CONVERT(CAST(CONVERT(forma_farmaceutica USING cp850) AS BINARY) USING utf8mb4) WHERE forma_farmaceutica LIKE '%├%';
UPDATE productos SET unidad_medida = CONVERT(CAST(CONVERT(unidad_medida USING cp850) AS BINARY) USING utf8mb4) WHERE unidad_medida LIKE '%├%';
UPDATE productos SET unidad_fraccion = CONVERT(CAST(CONVERT(unidad_fraccion USING cp850) AS BINARY) USING utf8mb4) WHERE unidad_fraccion LIKE '%├%';

UPDATE roles SET nombre = CONVERT(CAST(CONVERT(nombre USING cp850) AS BINARY) USING utf8mb4) WHERE nombre LIKE '%├%';
UPDATE roles SET descripcion = CONVERT(CAST(CONVERT(descripcion USING cp850) AS BINARY) USING utf8mb4) WHERE descripcion LIKE '%├%';

UPDATE usuarios SET nombres = CONVERT(CAST(CONVERT(nombres USING cp850) AS BINARY) USING utf8mb4) WHERE nombres LIKE '%├%';
UPDATE usuarios SET apellidos = CONVERT(CAST(CONVERT(apellidos USING cp850) AS BINARY) USING utf8mb4) WHERE apellidos LIKE '%├%';
UPDATE usuarios SET usuario = CONVERT(CAST(CONVERT(usuario USING cp850) AS BINARY) USING utf8mb4) WHERE usuario LIKE '%├%';
UPDATE usuarios SET email = CONVERT(CAST(CONVERT(email USING cp850) AS BINARY) USING utf8mb4) WHERE email LIKE '%├%';
-- (usuarios.password es un hash bcrypt en ASCII; se omite intencionalmente)

UPDATE venta_detalles SET tipo_unidad = CONVERT(CAST(CONVERT(tipo_unidad USING cp850) AS BINARY) USING utf8mb4) WHERE tipo_unidad LIKE '%├%';

UPDATE ventas SET tipo_comprobante = CONVERT(CAST(CONVERT(tipo_comprobante USING cp850) AS BINARY) USING utf8mb4) WHERE tipo_comprobante LIKE '%├%';
UPDATE ventas SET serie_comprobante = CONVERT(CAST(CONVERT(serie_comprobante USING cp850) AS BINARY) USING utf8mb4) WHERE serie_comprobante LIKE '%├%';
UPDATE ventas SET num_comprobante = CONVERT(CAST(CONVERT(num_comprobante USING cp850) AS BINARY) USING utf8mb4) WHERE num_comprobante LIKE '%├%';
UPDATE ventas SET metodo_pago = CONVERT(CAST(CONVERT(metodo_pago USING cp850) AS BINARY) USING utf8mb4) WHERE metodo_pago LIKE '%├%';
UPDATE ventas SET medico_cmp = CONVERT(CAST(CONVERT(medico_cmp USING cp850) AS BINARY) USING utf8mb4) WHERE medico_cmp LIKE '%├%';
UPDATE ventas SET estado = CONVERT(CAST(CONVERT(estado USING cp850) AS BINARY) USING utf8mb4) WHERE estado LIKE '%├%';
