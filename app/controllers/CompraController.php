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
        
        $data = [
            'title' => 'Registrar Nueva Compra',
            'proveedores' => $provModel->getAll(),
            // Productos se cargarán en JS, o los pasamos todos para un array rápido
            'productos' => $prodModel->getAll()
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

            // 1. Cabecera
            $cabecera = [
                'id_proveedor' => (int)$_POST['id_proveedor'],
                'tipo_comprobante' => $_POST['tipo_comprobante'],
                'serie_comprobante' => $_POST['serie_comprobante'],
                'num_comprobante' => $_POST['num_comprobante'],
                'fecha_compra' => $_POST['fecha_compra'],
                'impuesto' => (float)($_POST['impuesto'] ?? 0.00),
                'total' => (float)$_POST['total_compra'],
                'estado' => $_POST['estado'] ?? 'Completada'
            ];
            
            // 2. Detalles de los productos (llegan en arrays paralelos)
            $detalles = [];
            $productos = $_POST['producto_id'] ?? [];
            foreach ($productos as $i => $id_prod) {
                // si el json o el ajax envió vacíos se filtran
                if (empty($id_prod) || empty($_POST['cantidad'][$i])) continue;
                
                $detalles[] = [
                    'id_producto' => (int)$id_prod,
                    'cantidad' => (int)$_POST['cantidad'][$i],
                    'precio_unitario' => (float)$_POST['precio_c_unitario'][$i],
                    'subtotal' => (float)$_POST['subtotal'][$i],
                    'lote' => $_POST['lote'][$i],
                    'vencimiento' => $_POST['vencimiento'][$i],
                    'actualizar_precio' => isset($_POST['actualizar_precio']) ? 1 : 0
                ];
            }
            
            if (count($detalles) > 0) {
                $resultado = $modelo->registrarCompra($cabecera, $detalles, $_SESSION['user_id']);
                if ($resultado) {
                    $logMsg = ($cabecera['estado'] == 'Pendiente') ? "Registro de Orden de Compra Pendiente" : "Registro de Compra con Ingreso Directo";
                    $this->logAccion('Compras', 'CREAR', "$logMsg. Prov: " . $_POST['id_proveedor'] . ", Total: " . $cabecera['total'], $cabecera['total']);
                    $this->flash('success', "Compra y Lotes generados correctamente.");
                } else {
                    $this->flash('error', "No se pudo registrar la compra. Verifica los datos ingresados e intenta nuevamente.");
                }
            } else {
                $this->flash('error', "Debe agregar al menos un producto.");
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
