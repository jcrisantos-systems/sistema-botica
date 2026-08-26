<?php
class CompraController extends Controller {

    public function __construct() {
        $this->requireAdmin();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Compra');
        $compras = $modelo->getAll();
        
        $this->view('compras/index', ['title' => 'Historial de Compras', 'compras' => $compras]);
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $provModel = $this->model('Proveedor');
        $prodModel = $this->model('Producto');

        // Token de un solo uso por carga de formulario (distinto del CSRF general): protege
        // contra doble envío (doble clic / reenvío) a nivel de backend, aprovechando que PHP
        // serializa las peticiones de una misma sesión (lock del archivo de sesión), así que
        // la segunda petición casi simultánea espera a que la primera consuma y borre este
        // token antes de poder leerlo.
        $_SESSION['compra_form_token'] = bin2hex(random_bytes(16));

        $data = [
            'title' => 'Registrar Nueva Compra',
            'proveedores' => $provModel->getAll(),
            // Productos se cargarán en JS, o los pasamos todos para un array rápido
            'productos' => $prodModel->getAll(),
            'form_token' => $_SESSION['compra_form_token']
        ];

        $this->view('compras/create', $data);
    }
    
    public function detalle($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Compra');
        $detalles = $modelo->getDetallesConLotes($id);
        echo json_encode($detalles);
        exit;
    }

    public function devolver($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Compra');
        $compra = $modelo->getCompraPorId($id);
        if (!$compra) {
            $this->flash('error', "Compra no encontrada.");
            header('Location: ' . BASE_URL . 'compra/index');
            exit;
        }

        $detalles = $modelo->getDetallesConLotes($id);

        $this->view('compras/devolucion', [
            'title' => 'Devolver Productos al Proveedor',
            'compra' => $compra,
            'detalles' => $detalles
        ]);
    }

    public function save() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_proveedor'])) {
            $this->verifyCsrf();
            $modelo = $this->model('Compra');

            // Paso 1: el form_token debe existir y coincidir, pero NO se consume todavía.
            // Verificarlo temprano sigue bloqueando un reenvío casi simultáneo (doble clic):
            // PHP mantiene bloqueada la sesión (session_start en public/index.php) durante
            // toda la petición, así que una segunda petición de la misma sesión queda
            // esperando ese lock hasta que ésta termine — para entonces el token ya habrá
            // sido consumido (paso 6) o seguirá intacto si esta petición fue rechazada.
            $formToken = $_POST['form_token'] ?? '';
            if (empty($_SESSION['compra_form_token']) || !hash_equals($_SESSION['compra_form_token'], $formToken)) {
                $this->flash('error', "Esta compra ya fue enviada anteriormente o el formulario expiró. Si no aparece en el Historial de Compras, vuelve a completarla desde \"Nueva Compra\".");
                header('Location: ' . BASE_URL . 'compra/index');
                exit;
            }

            // Paso 2: validar TODOS los datos de entrada antes de tocar la BD o el token.
            $tiposValidos = ['Factura', 'Boleta', 'Guia Remision'];
            $tipoComprobante = trim($_POST['tipo_comprobante'] ?? '');
            $serieComprobante = trim($_POST['serie_comprobante'] ?? '');
            $numComprobante = trim($_POST['num_comprobante'] ?? '');
            $idProveedor = (int)($_POST['id_proveedor'] ?? 0);
            $fechaCompra = trim($_POST['fecha_compra'] ?? '');

            $errorValidacion = null;
            if ($idProveedor <= 0) {
                $errorValidacion = "Debes seleccionar un proveedor válido.";
            } elseif (!in_array($tipoComprobante, $tiposValidos, true)) {
                $errorValidacion = "Tipo de comprobante no válido.";
            } elseif ($serieComprobante === '' || $numComprobante === '') {
                $errorValidacion = "Debes indicar la serie y el número del comprobante de la compra.";
            } elseif ($fechaCompra === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCompra)) {
                $errorValidacion = "Debes indicar una fecha de emisión válida.";
            }

            // Detalles de los productos (llegan en arrays paralelos); se validan cantidades
            // y precios como parte del mismo paso, antes de decidir si el token se consume.
            $detalles = [];
            if ($errorValidacion === null) {
                $productos = $_POST['producto_id'] ?? [];
                foreach ($productos as $i => $id_prod) {
                    if (empty($id_prod) || empty($_POST['cantidad'][$i])) continue;

                    $cantidad = (int)$_POST['cantidad'][$i];
                    $precioUnitario = (float)($_POST['precio_c_unitario'][$i] ?? 0);
                    if ($cantidad <= 0 || $precioUnitario < 0) {
                        $errorValidacion = "Cada producto debe tener una cantidad mayor a cero y un precio válido.";
                        break;
                    }

                    $detalles[] = [
                        'id_producto' => (int)$id_prod,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'subtotal' => (float)($_POST['subtotal'][$i] ?? 0),
                        'lote' => $_POST['lote'][$i] ?? '',
                        'vencimiento' => $_POST['vencimiento'][$i] ?? '',
                        'actualizar_precio' => isset($_POST['actualizar_precio']) ? 1 : 0
                    ];
                }
                if ($errorValidacion === null && count($detalles) === 0) {
                    $errorValidacion = "Debe agregar al menos un producto.";
                }
            }

            // Paso 3: duplicado de comprobante (proveedor+tipo+serie+número). No existe
            // todavía un UNIQUE de BD para esto en este entorno de prueba (pendiente de que
            // termine de validarse en botica_db_test_restore antes de aplicarlo en real).
            if ($errorValidacion === null && $modelo->existeComprobante($idProveedor, $tipoComprobante, $serieComprobante, $numComprobante)) {
                $errorValidacion = "Ya existe una compra registrada con el comprobante {$tipoComprobante} {$serieComprobante}-{$numComprobante} para este proveedor.";
            }

            if ($errorValidacion !== null) {
                // El formulario no procesó nada: se conserva el form_token vigente en sesión
                // (no se toca) para que el usuario pueda corregir el dato y reenviar sin
                // necesidad de recargar "Nueva Compra". El botón ya deshabilitado por JS se
                // reactiva solo al volver a cargar la página con el mensaje de error.
                $this->flash('error', $errorValidacion);
                header('Location: ' . BASE_URL . 'compra/index');
                exit;
            }

            // Paso 4: intentar registrar la compra (transacción propia del modelo).
            $cabecera = [
                'id_proveedor' => $idProveedor,
                'tipo_comprobante' => $tipoComprobante,
                'serie_comprobante' => $serieComprobante,
                'num_comprobante' => $numComprobante,
                'fecha_compra' => $fechaCompra,
                'impuesto' => (float)($_POST['impuesto'] ?? 0.00),
                'total' => (float)($_POST['total_compra'] ?? 0),
                'estado' => $_POST['estado'] ?? 'Completada'
            ];

            $resultado = $modelo->registrarCompra($cabecera, $detalles, $_SESSION['user_id']);

            if ($resultado) {
                // Paso 5: SOLO ahora, con la compra ya confirmada en BD, se consume el
                // form_token y se rota el CSRF general, para que un reenvío posterior del
                // mismo formulario (o una petición concurrente que estuviera esperando el
                // lock de sesión) sea rechazado.
                unset($_SESSION['compra_form_token']);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                // Se deja lista una nueva llave por si el frontend reutiliza esta misma
                // página sin recargar; "Nueva Compra" (create()) siempre genera la suya.
                $_SESSION['compra_form_token'] = bin2hex(random_bytes(16));

                $logMsg = ($cabecera['estado'] == 'Pendiente') ? "Registro de Orden de Compra Pendiente" : "Registro de Compra con Ingreso Directo";
                $this->logAccion('Compras', 'CREAR', "$logMsg. Prov: " . $idProveedor . ", Total: " . $cabecera['total'], $cabecera['total']);
                $this->flash('success', "Compra y Lotes generados correctamente.");
            } else {
                // La compra falló dentro de su propia transacción (rollback ya aplicado por
                // el modelo): se conserva el form_token vigente para permitir un reintento
                // legítimo sin duplicar el registro exitosamente creado (porque no se creó).
                $this->flash('error', "No se pudo registrar la compra. Verifica los datos ingresados e intenta nuevamente.");
            }
        }
        header('Location: ' . BASE_URL . 'compra/index');
        exit;
    }

    public function save_devolucion() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_compra'])) {
            $this->verifyCsrf();
            $modelo = $this->model('Compra');

            $cabecera = [
                'id_compra' => (int)$_POST['id_compra'],
                'num_documento_prov' => $_POST['num_documento_prov'],
                'motivo' => $_POST['motivo'],
                'total_devuelto' => (float)$_POST['total_devolucion'],
                'fecha_devolucion' => $_POST['fecha_devolucion']
            ];
            
            $detalles = [];
            $productos = $_POST['producto_id'] ?? [];
            foreach ($productos as $i => $id_prod) {
                $cant = (int)$_POST['cantidad_dev'][$i];
                if ($cant <= 0) continue;
                
                $detalles[] = [
                    'id_producto' => (int)$id_prod,
                    'id_lote' => (int)$_POST['lote_id'][$i],
                    'cantidad' => $cant,
                    'precio_costo' => (float)$_POST['precio_costo'][$i],
                    'subtotal' => (float)$_POST['subtotal_dev'][$i]
                ];
            }
            
            if (count($detalles) > 0) {
                $resultado = $modelo->registrarDevolucion($cabecera, $detalles, $_SESSION['user_id']);
                if ($resultado === true) {
                    $this->logAccion('Compras', 'DEVOLUCION', "Nota de Crédito/Devolución de Compra ID #" . $cabecera['id_compra'] . ", NC: " . $cabecera['num_documento_prov'], $cabecera['total_devuelto']);
                    $this->flash('success', "Nota de Crédito y devolución de stock registradas.");
                } else {
                    $this->flash('error', $resultado);
                }
            } else {
                $this->flash('error', "No se marcó ningún producto para devolver.");
            }
        }
        header('Location: ' . BASE_URL . 'compra/index');
        exit;
    }

    public function recepcion($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }

        $modelo = $this->model('Compra');
        $compra = $modelo->getCompraPorId($id);
        if (!$compra || $compra['estado'] !== 'Pendiente') {
            $this->flash('error', "La compra no está pendiente de recepción.");
            header('Location: ' . BASE_URL . 'compra/index');
            exit;
        }

        $detalles = $modelo->getDetallesConLotes($id);

        $this->view('compras/recepcion', [
            'title' => 'Recibir Mercadería',
            'compra' => $compra,
            'detalles' => $detalles
        ]);
    }

    public function procesar_recepcion() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_compra'])) {
            $this->verifyCsrf();
            $modelo = $this->model('Compra');
            $id_compra = (int)$_POST['id_compra'];

            $lotes_data = [];
            foreach (($_POST['detalle_id'] ?? []) as $i => $id_det) {
                $lotes_data[] = [
                    'id_detalle' => (int)$id_det,
                    'lote' => $_POST['lote'][$i] ?? '',
                    'vencimiento' => $_POST['vencimiento'][$i] ?? ''
                ];
            }

            if (empty($lotes_data)) {
                $this->flash('error', "Debe ingresar los datos de lote de al menos un producto.");
                header('Location: ' . BASE_URL . 'compra/index');
                exit;
            }

            $resultado = $modelo->procesarRecepcion($id_compra, $_SESSION['user_id'], $lotes_data);
            if ($resultado === true) {
                $this->logAccion('Compras', 'RECEPCION', "Recepción física de productos de la Orden ID #" . $id_compra);
                $this->flash('success', "Mercadería recibida y stock actualizado.");
            } else {
                $this->flash('error', $resultado);
            }
        }
        header('Location: ' . BASE_URL . 'compra/index');
        exit;
    }
}
