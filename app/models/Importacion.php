<?php
// Motor genérico del módulo de Importación Masiva. Es "genérico" a propósito: una sola
// implementación sirve a todas las entidades declaradas en app/config/importaciones.php,
// en vez de duplicar el mismo flujo (leer archivo, validar, insertar/actualizar) por cada tabla.
//
// Nota sobre seguridad: varios métodos interpolan nombres de tabla/columna directamente en el
// SQL (p.ej. "SELECT id FROM `$tabla` ..."). Esto es seguro porque $tabla/$columna SIEMPRE vienen
// de app/config/importaciones.php (definido por el desarrollador), nunca del archivo subido por
// el usuario; el valor de búsqueda en sí siempre se parametriza con bindParam/bindValue.
class Importacion {
    private $conn;
    private $cacheFk = [];
    private $cacheExistente = [];

    const MAX_FILAS = 5000;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Lee un CSV subido por el usuario (con o sin BOM UTF-8 de Excel, separador , o ;
    // autodetectado) y lo convierte en un array de filas asociativas usando la primera
    // línea como encabezados (normalizados a minúsculas).
    public function leerCsv($rutaArchivo) {
        $contenido = @file_get_contents($rutaArchivo);
        if ($contenido === false || trim($contenido) === '') {
            throw new Exception("El archivo está vacío o no se pudo leer.");
        }
        if (substr($contenido, 0, 3) === "\xEF\xBB\xBF") {
            $contenido = substr($contenido, 3);
        }

        $lineas = preg_split("/\r\n|\n|\r/", $contenido);
        $lineas = array_values(array_filter($lineas, function ($l) { return trim($l) !== ''; }));

        if (count($lineas) < 1) {
            throw new Exception("El archivo no contiene encabezados.");
        }
        if (count($lineas) - 1 > self::MAX_FILAS) {
            throw new Exception("El archivo supera el máximo de " . self::MAX_FILAS . " filas por carga. Divídelo en partes más pequeñas.");
        }

        $primeraLinea = $lineas[0];
        $separador = (substr_count($primeraLinea, ';') > substr_count($primeraLinea, ',')) ? ';' : ',';

        $encabezados = array_map(function ($c) { return strtolower(trim($c)); }, str_getcsv($primeraLinea, $separador));

        $filas = [];
        for ($i = 1; $i < count($lineas); $i++) {
            $campos = str_getcsv($lineas[$i], $separador);
            $fila = [];
            foreach ($encabezados as $idx => $nombreCol) {
                $fila[$nombreCol] = isset($campos[$idx]) ? trim((string)$campos[$idx]) : '';
            }
            $filas[] = $fila;
        }

        return $filas;
    }

    // Genera el contenido CSV (con BOM UTF-8 para Excel) de la plantilla de una entidad:
    // encabezados exactos + una fila de ejemplo orientativa que el usuario debe reemplazar.
    // Se usa ';' como delimitador (no ',') porque es el separador de listas que Excel en
    // configuración regional español espera para repartir el CSV en columnas al abrirlo con
    // doble clic, sin pasar por el asistente de "Texto en columnas".
    public function generarPlantillaCsv($config) {
        $columnas = array_keys($config['columnas']);

        $out = fopen('php://temp', 'w+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $columnas, ';');

        $ejemplo = array_map(function ($col) use ($config) {
            $r = $config['columnas'][$col];
            if (isset($r['enum'])) return $r['enum'][0];
            if (isset($r['fk'])) return 'Nombre exacto existente';
            if (($r['tipo'] ?? null) === 'decimal') return '10.50';
            if (($r['tipo'] ?? null) === 'entero') return '10';
            if (($r['tipo'] ?? null) === 'fecha') return date('Y-m-d');
            return 'Ejemplo';
        }, $columnas);
        fputcsv($out, $ejemplo, ';');

        rewind($out);
        $contenido = stream_get_contents($out);
        fclose($out);
        return $contenido;
    }

    // Valida cada fila leída contra las reglas de columnas de la entidad. No escribe nada en
    // la base de datos (solo hace SELECTs para resolver llaves foráneas y detectar si una fila
    // ya existe). Devuelve, por fila: si es válida, sus errores, los datos ya normalizados
    // (listos para INSERT/UPDATE) y si se creará o actualizará un registro.
    public function validar($entidadKey, $config, $filas) {
        $clavesVistas = [];
        $resultado = [];

        foreach ($filas as $numFila => $fila) {
            $errores = [];
            $datos = [];

            foreach ($config['columnas'] as $columna => $reglas) {
                $valor = $fila[$columna] ?? '';

                if ($valor === '' && isset($reglas['default'])) {
                    $valor = $reglas['default'];
                }

                if (!empty($reglas['requerido']) && $valor === '') {
                    $errores[] = "'$columna' es obligatorio";
                }

                if ($valor !== '' && isset($reglas['enum']) && !in_array($valor, $reglas['enum'], true)) {
                    $errores[] = "'$columna' debe ser uno de: " . implode(', ', $reglas['enum']);
                }

                $tipo = $reglas['tipo'] ?? null;
                if ($valor !== '' && $tipo === 'decimal' && !is_numeric($valor)) {
                    $errores[] = "'$columna' debe ser un número (ej. 12.50)";
                }
                if ($valor !== '' && $tipo === 'entero' && !ctype_digit((string)$valor)) {
                    $errores[] = "'$columna' debe ser un número entero";
                }
                if ($valor !== '' && $tipo === 'fecha' && !$this->esFechaValida($valor)) {
                    $errores[] = "'$columna' debe tener formato de fecha AAAA-MM-DD";
                }

                if (isset($reglas['fk'])) {
                    if ($valor !== '') {
                        $idResuelto = $this->resolverFk($reglas['fk'], $valor);
                        if ($idResuelto === null) {
                            $errores[] = "'$columna': no existe \"$valor\" en {$reglas['fk']['tabla']}";
                        }
                        $datos[$reglas['fk']['columna_id']] = $idResuelto;
                    } else {
                        $datos[$reglas['fk']['columna_id']] = null;
                    }
                    continue; // no se guarda el nombre crudo, solo el id resuelto
                }

                $datos[$columna] = $valor;
            }

            // Duplicado dentro del propio archivo (dos filas con la misma clave natural)
            $accion = 'crear';
            if (!empty($config['clave_natural'])) {
                $clave = $fila[$config['clave_natural']] ?? '';
                if ($clave !== '') {
                    if (isset($clavesVistas[$clave])) {
                        $errores[] = "Valor duplicado dentro del archivo para '{$config['clave_natural']}' (ya aparece en la fila {$clavesVistas[$clave]})";
                    } else {
                        $clavesVistas[$clave] = $numFila + 2; // +2: encabezado (fila 1) + índice base 1
                    }
                    if ($this->buscarExistente($config['tabla'], $config['clave_natural'], $clave) !== null) {
                        $accion = 'actualizar';
                    }
                }
            }

            $this->completarCamposEspeciales($entidadKey, $datos, $accion);

            $resultado[] = [
                'fila' => $numFila + 2,
                'original' => $fila,
                'datos' => $datos,
                'errores' => $errores,
                'valida' => empty($errores),
                'accion' => $accion,
            ];
        }

        return $resultado;
    }

    // Ejecuta la carga real dentro de una única transacción: si cualquier fila falla, se
    // revierte todo (no se guarda nada a medias). Recibe solo las filas ya marcadas como
    // válidas por validar().
    public function procesarLote($entidadKey, $config, $filasValidas, $idUsuario) {
        $insertados = 0;
        $actualizados = 0;

        try {
            $this->conn->beginTransaction();

            if ($entidadKey === 'inventario') {
                require_once 'Inventario.php';
                $inventarioModel = new Inventario($this->conn);
                foreach ($filasValidas as $fila) {
                    $d = $fila['datos'];
                    $vencimiento = $d['fecha_vencimiento'] !== '' ? $d['fecha_vencimiento'] : date('Y-m-d', strtotime('+365 days'));
                    $inventarioModel->registrarEntrada(
                        $d['id_producto'],
                        $idUsuario,
                        (int)$d['cantidad'],
                        $d['motivo'],
                        $d['lote'],
                        $vencimiento
                    );
                    $insertados++;
                }
            } else {
                foreach ($filasValidas as $fila) {
                    $d = $fila['datos'];
                    $idExistente = null;

                    if (!empty($config['clave_natural'])) {
                        $clave = $d[$config['clave_natural']] ?? '';
                        if ($clave !== '') {
                            $idExistente = $this->buscarExistente($config['tabla'], $config['clave_natural'], $clave, true);
                        }
                    }

                    if ($idExistente) {
                        // En una actualización no se tocan campos que no vienen en la plantilla
                        // (p.ej. fraccionamiento de un producto), para no perder configuración
                        // existente que el usuario nunca quiso cambiar.
                        $this->actualizarFila($config['tabla'], $d, $idExistente);
                        $actualizados++;
                    } else {
                        $this->insertarFila($config['tabla'], $d);
                        $insertados++;
                    }
                }
            }

            $this->conn->commit();
            return ['insertados' => $insertados, 'actualizados' => $actualizados];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('Error en Importacion::procesarLote: ' . $e->getMessage());
            return false;
        }
    }

    private function esFechaValida($valor) {
        $d = DateTime::createFromFormat('Y-m-d', $valor);
        return $d && $d->format('Y-m-d') === $valor;
    }

    private function resolverFk($fk, $valor) {
        $cacheKey = $fk['tabla'] . ':' . $fk['buscar_por'] . ':' . mb_strtolower($valor);
        if (array_key_exists($cacheKey, $this->cacheFk)) {
            return $this->cacheFk[$cacheKey];
        }
        $stmt = $this->conn->prepare("SELECT id FROM `{$fk['tabla']}` WHERE `{$fk['buscar_por']}` = :valor LIMIT 1");
        $stmt->bindParam(':valor', $valor);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row ? (int)$row['id'] : null;
        $this->cacheFk[$cacheKey] = $id;
        return $id;
    }

    // $forzarConsultaFresca: en procesarLote() no se usa el cache (se vuelve a preguntar a la
    // BD), para no dar por buena una decisión insertar/actualizar tomada varios segundos antes
    // en validar() si algo cambió mientras el usuario miraba la previsualización.
    private function buscarExistente($tabla, $columna, $valor, $forzarConsultaFresca = false) {
        $cacheKey = "$tabla:$columna:" . mb_strtolower($valor);
        if (!$forzarConsultaFresca && array_key_exists($cacheKey, $this->cacheExistente)) {
            return $this->cacheExistente[$cacheKey];
        }
        $stmt = $this->conn->prepare("SELECT id FROM `$tabla` WHERE `$columna` = :valor LIMIT 1");
        $stmt->bindParam(':valor', $valor);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row ? (int)$row['id'] : null;
        $this->cacheExistente[$cacheKey] = $id;
        return $id;
    }

    // Completa, solo para 'productos', columnas que el modelo Producto necesita pero que la
    // plantilla de importación no pide (mantenerla simple para el usuario). El margen se
    // recalcula siempre que hay precio; el resto (fraccionamiento) solo se fija en filas
    // nuevas, nunca al actualizar un producto ya existente.
    private function completarCamposEspeciales($entidadKey, &$datos, $accion) {
        if ($entidadKey !== 'productos') {
            return;
        }
        if (isset($datos['precio_compra']) && isset($datos['precio_venta']) && $datos['precio_compra'] !== '') {
            $pc = (float)$datos['precio_compra'];
            $pv = (float)$datos['precio_venta'];
            $datos['margen_ganancia'] = $pc > 0 ? round((($pv - $pc) / $pc) * 100, 2) : 0;
        }
        if ($accion === 'crear') {
            $datos['fraccionable'] = 0;
            $datos['unidades_por_caja'] = 1;
            $datos['unidad_fraccion'] = null;
            $datos['precio_fraccion'] = 0.00;
        }
    }

    private function insertarFila($tabla, $datos) {
        $columnas = array_keys($datos);
        $marcadores = array_map(function ($c) { return ':' . $c; }, $columnas);
        $sql = "INSERT INTO `$tabla` (`" . implode('`, `', $columnas) . "`) VALUES (" . implode(', ', $marcadores) . ")";
        $stmt = $this->conn->prepare($sql);
        foreach ($datos as $col => $val) {
            $stmt->bindValue(':' . $col, $val === '' ? null : $val);
        }
        $stmt->execute();
    }

    private function actualizarFila($tabla, $datos, $id) {
        $sets = array_map(function ($c) { return "`$c` = :$c"; }, array_keys($datos));
        $sql = "UPDATE `$tabla` SET " . implode(', ', $sets) . " WHERE id = :__id";
        $stmt = $this->conn->prepare($sql);
        foreach ($datos as $col => $val) {
            $stmt->bindValue(':' . $col, $val === '' ? null : $val);
        }
        $stmt->bindValue(':__id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
