<?php
// Importación Masiva: sube un CSV, lo valida y previsualiza, y solo al confirmar explícitamente
// se escribe en la base de datos. Sirve a todas las entidades declaradas en
// app/config/importaciones.php mediante el motor genérico de app/models/Importacion.php.
class ImportacionController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    private function configuraciones() {
        return require '../app/config/importaciones.php';
    }

    private function config($entidadKey) {
        $configs = $this->configuraciones();
        return $configs[$entidadKey] ?? null;
    }

    // Corta la ejecución si el rol actual no puede importar esta entidad. Cada entidad
    // declara su propia lista de roles permitidos en 'roles_permitidos' (ver
    // app/config/importaciones.php); p.ej. Productos/Proveedores son solo Administrador,
    // mientras que Clientes admite Admin/Farmacéutico/Cajero (igual que el alta 1 a 1).
    private function verificarPermiso($config) {
        if (!in_array($_SESSION['rol_id'] ?? null, $config['roles_permitidos'] ?? [1])) {
            $this->flash('error', "No tienes permiso para importar esta información.");
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }
    }

    public function index() {
        $configs = $this->configuraciones();
        $disponibles = array_filter($configs, function ($c) {
            return in_array($_SESSION['rol_id'] ?? null, $c['roles_permitidos'] ?? [1]);
        });

        $this->view('importacion/index', [
            'title' => 'Importación Masiva',
            'entidades' => $disponibles,
        ]);
    }

    public function plantilla($entidadKey = '') {
        $config = $this->config($entidadKey);
        if (!$config) { header('Location: ' . BASE_URL . 'importacion/index'); exit; }
        $this->verificarPermiso($config);

        $modelo = $this->model('Importacion');
        $csv = $modelo->generarPlantillaCsv($config);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=plantilla_' . $entidadKey . '.csv');
        echo $csv;
        exit;
    }

    public function subir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . 'importacion/index'); exit; }
        $this->verifyCsrf();

        $entidadKey = $_POST['entidad'] ?? '';
        $config = $this->config($entidadKey);
        if (!$config) {
            $this->flash('error', "Selecciona una entidad válida para importar.");
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }
        $this->verificarPermiso($config);

        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', "Debes seleccionar un archivo CSV válido.");
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }

        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $this->flash('error', "Solo se aceptan archivos .csv. Si tienes un Excel, usa \"Archivo > Guardar como > CSV UTF-8 (delimitado por comas)\".");
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }

        $dirTemp = '../storage/imports';
        if (!is_dir($dirTemp)) {
            mkdir($dirTemp, 0755, true);
        }
        $nombreTemp = uniqid('import_', true) . '.csv';
        $rutaTemp = $dirTemp . '/' . $nombreTemp;

        if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaTemp)) {
            $this->flash('error', "No se pudo procesar el archivo subido. Intenta nuevamente.");
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }

        // Solo se guarda la ruta del archivo temporal en sesión (no las filas en sí), para no
        // inflar la sesión con archivos grandes; se vuelve a leer y validar en cada paso.
        $_SESSION['importacion_pendiente'] = [
            'entidad' => $entidadKey,
            'archivo' => $nombreTemp,
        ];

        header('Location: ' . BASE_URL . 'importacion/previsualizar');
        exit;
    }

    public function previsualizar() {
        $pendiente = $_SESSION['importacion_pendiente'] ?? null;
        if (!$pendiente) {
            $this->flash('error', "No hay ningún archivo pendiente de revisar. Sube uno primero.");
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }

        $config = $this->config($pendiente['entidad']);
        $this->verificarPermiso($config);

        $rutaTemp = '../storage/imports/' . $pendiente['archivo'];
        if (!is_file($rutaTemp)) {
            unset($_SESSION['importacion_pendiente']);
            $this->flash('error', "El archivo temporal ya no existe. Sube el archivo nuevamente.");
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }

        $modelo = $this->model('Importacion');
        try {
            $filas = $modelo->leerCsv($rutaTemp);
            if (empty($filas)) {
                throw new Exception("El archivo no tiene filas de datos (solo encabezados).");
            }
            $filasValidadas = $modelo->validar($pendiente['entidad'], $config, $filas);
        } catch (Exception $e) {
            @unlink($rutaTemp);
            unset($_SESSION['importacion_pendiente']);
            $this->flash('error', "No se pudo procesar el archivo: " . $e->getMessage());
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }

        $totalValidas = count(array_filter($filasValidadas, function ($f) { return $f['valida']; }));

        $this->view('importacion/preview', [
            'title' => 'Previsualización de Importación',
            'entidadLabel' => $config['label'],
            'columnas' => array_keys($config['columnas']),
            'filas' => $filasValidadas,
            'totalFilas' => count($filasValidadas),
            'totalValidas' => $totalValidas,
        ]);
    }

    public function confirmar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . 'importacion/index'); exit; }
        $this->verifyCsrf();

        $pendiente = $_SESSION['importacion_pendiente'] ?? null;
        if (!$pendiente) {
            $this->flash('error', "No hay ningún archivo pendiente de confirmar.");
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }

        $config = $this->config($pendiente['entidad']);
        $this->verificarPermiso($config);

        $rutaTemp = '../storage/imports/' . $pendiente['archivo'];
        if (!is_file($rutaTemp)) {
            unset($_SESSION['importacion_pendiente']);
            $this->flash('error', "El archivo temporal ya no existe. Sube el archivo nuevamente.");
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }

        $modelo = $this->model('Importacion');

        // Se vuelve a leer y validar contra el estado ACTUAL de la base de datos justo antes de
        // escribir: no se confía en lo que el navegador mostró en la previsualización, por si
        // algo cambió mientras tanto (p.ej. otro usuario borró una categoría referenciada).
        try {
            $filas = $modelo->leerCsv($rutaTemp);
            $filasValidadas = $modelo->validar($pendiente['entidad'], $config, $filas);
        } catch (Exception $e) {
            $this->flash('error', "No se pudo leer el archivo: " . $e->getMessage());
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }

        $filasValidas = array_values(array_filter($filasValidadas, function ($f) { return $f['valida']; }));
        $totalConError = count($filas) - count($filasValidas);

        if (empty($filasValidas)) {
            $this->flash('error', "Ninguna fila es válida. No se importó nada.");
            header('Location: ' . BASE_URL . 'importacion/previsualizar');
            exit;
        }

        $resultado = $modelo->procesarLote($pendiente['entidad'], $config, $filasValidas, $_SESSION['user_id']);

        @unlink($rutaTemp);
        unset($_SESSION['importacion_pendiente']);

        if ($resultado === false) {
            $this->flash('error', "Ocurrió un error durante la importación. No se guardó ningún cambio (operación revertida por completo).");
            header('Location: ' . BASE_URL . 'importacion/index');
            exit;
        }

        $descripcion = "Importación masiva de {$config['label']}: {$resultado['insertados']} creados, {$resultado['actualizados']} actualizados"
                     . ($totalConError > 0 ? ", $totalConError filas con errores omitidas" : "") . ".";
        $this->logAccion('Importacion', 'CARGA_MASIVA', $descripcion);

        $mensaje = "Importación completada: {$resultado['insertados']} registros creados, {$resultado['actualizados']} actualizados.";
        if ($totalConError > 0) {
            $mensaje .= " Se omitieron $totalConError filas con errores.";
        }
        $this->flash('success', $mensaje);

        header('Location: ' . BASE_URL . 'importacion/index');
        exit;
    }

    public function cancelar() {
        $pendiente = $_SESSION['importacion_pendiente'] ?? null;
        if ($pendiente) {
            @unlink('../storage/imports/' . $pendiente['archivo']);
            unset($_SESSION['importacion_pendiente']);
        }
        header('Location: ' . BASE_URL . 'importacion/index');
        exit;
    }
}
