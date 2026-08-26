-- Fase 12: restricciones de integridad de base de datos (duplicados y huérfanos)
--
-- Contexto: auditoría de seguridad e integridad de datos, Fase D (autorizada por el
-- propietario del sistema). Aplica únicamente las restricciones que NO requieren
-- depurar datos históricos (0 duplicados/huérfanos confirmados justo antes de aplicar
-- cada una, ver reporte de la sesión).
--
-- Explícitamente NO incluidas en esta fase (pendientes de autorización futura):
--   - uq_cliente_doc (clientes.tipo_documento+num_documento): requiere depurar 21
--     registros duplicados en 10 grupos (Fase E, no autorizada).
--   - uq_usuario_email (usuarios.email): requiere depurar 3 cuentas duplicadas
--     (Fase E, no autorizada).
--   - uq_venta_comprobante: pendiente de sustituir rand() por un correlativo
--     transaccional real (Regla especial 5); se aplicará en una migración separada
--     tras confirmación explícita del diseño.
--
-- Reversión de cada restricción (documentada también en el reporte de auditoría):
--   ALTER TABLE categorias  DROP INDEX uq_categoria_nombre;
--   ALTER TABLE laboratorios DROP INDEX uq_laboratorio_nombre;
--   ALTER TABLE compras     DROP INDEX uq_compra_comprobante;
--   ALTER TABLE ventas      DROP FOREIGN KEY fk_venta_caja;

-- a) Categorías: nombre único. La colación utf8mb4_unicode_ci ya existente en la
--    columna pliega mayúsculas/minúsculas, tildes y espacios finales (verificado
--    empíricamente); no cubre espacios iniciales, por eso ya se normaliza con trim()
--    en CategoriaController::save() (ya existía antes de esta fase).
ALTER TABLE categorias
  ADD UNIQUE KEY uq_categoria_nombre (nombre);

-- b) Laboratorios: mismo criterio que categorías.
ALTER TABLE laboratorios
  ADD UNIQUE KEY uq_laboratorio_nombre (nombre);

-- c) Compras: un mismo proveedor no puede tener dos compras con el mismo tipo+serie+
--    número de comprobante. Se incluye id_proveedor porque cada proveedor numera sus
--    propios comprobantes de forma independiente. Requiere que tipo/serie/num nunca
--    queden vacíos ni NULL: reforzado en CompraController::save() (Fase C) exigiendo
--    los 3 campos no vacíos para los 3 tipos de comprobante soportados hoy (Factura,
--    Boleta, Guía de Remisión) -- el modelo de datos actual no distingue reglas
--    obligatorias distintas por tipo de comprobante.
ALTER TABLE compras
  ADD UNIQUE KEY uq_compra_comprobante (id_proveedor, tipo_comprobante, serie_comprobante, num_comprobante);

-- d) Integridad referencial ventas -> cajas (columna sin ningún índice ni FK hasta
--    ahora). Sin CASCADE: una caja histórica nunca debe poder desaparecer mientras
--    tenga ventas asociadas; "desactivar" (estado=0) no se ve afectado por esta FK.
ALTER TABLE ventas
  ADD CONSTRAINT fk_venta_caja
  FOREIGN KEY (caja_id) REFERENCES cajas(id)
  ON DELETE RESTRICT
  ON UPDATE RESTRICT;
