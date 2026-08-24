-- Corrige proveedores cuyo texto quedó mal codificado (bytes UTF-8 correctos que fueron
-- leídos con una codepage CP437/CP850 y vueltos a guardar como UTF-8), p.ej.
-- 'Qu├¡mica' -> 'Química'. Solo toca las filas/columnas que realmente contienen el
-- artefacto '├' (marca inequívoca de esta corrupción); las filas ya correctas no se tocan.

UPDATE proveedores
SET razon_social = CONVERT(CAST(CONVERT(razon_social USING cp850) AS BINARY) USING utf8mb4)
WHERE razon_social LIKE '%├%';

UPDATE proveedores
SET representante = CONVERT(CAST(CONVERT(representante USING cp850) AS BINARY) USING utf8mb4)
WHERE representante LIKE '%├%';

UPDATE proveedores
SET direccion = CONVERT(CAST(CONVERT(direccion USING cp850) AS BINARY) USING utf8mb4)
WHERE direccion LIKE '%├%';

UPDATE proveedores
SET ruc = CONVERT(CAST(CONVERT(ruc USING cp850) AS BINARY) USING utf8mb4)
WHERE ruc LIKE '%├%';

UPDATE proveedores
SET telefono = CONVERT(CAST(CONVERT(telefono USING cp850) AS BINARY) USING utf8mb4)
WHERE telefono LIKE '%├%';
