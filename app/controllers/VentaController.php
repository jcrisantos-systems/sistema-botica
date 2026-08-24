<?php
class VentaController extends Controller {

    public function pos() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $cajaModel = $this->model('Caja');
        $cajaAbierta = $cajaModel->getCajaAbiertaPorUsuario($_SESSION['user_id']);
        if (!$cajaAbierta) {
            $_SESSION['error'] = "Debe aperturar su caja antes de poder realizar ventas.";
            header('Location: ' . BASE_URL . 'caja/apertura');
            exit;
        }
        
        $cliModel = $this->model('Cliente');
        $prodModel = $this->model('Producto');
        
        $configModel = $this->model('Configuracion');
        
        $data = [
            'title' => 'Punto de Venta',
            'clientes' => $cliModel->getAll(),
            'productos' => $prodModel->getAll(),
            'igv' => $configModel->get('igv')
        ];
        
        // Vista directa para el POS (usa layout de main)
        $this->view('ventas/pos', $data);
    }
    
    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        $modelo = $this->model('Venta');
        $this->view('ventas/index', ['title' => 'Historial de Ventas', 'ventas' => $modelo->getAll()]);
    }

    public function ticket($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Venta');
        $ventas = $modelo->getAll();
        
        $venta_actual = null;
        foreach($ventas as $v) {
            if($v['id'] == $id) {
                $venta_actual = $v; break;
            }
        }
        
        if(!$venta_actual) die("Ticket no encontrado.");
        
        $detalles = $modelo->getDetalles($id);
        $configModel = $this->model('Configuracion');
        
        $data = [
            'venta' => $venta_actual,
            'detalles' => $detalles,
            'config' =>  $configModel->getAll()
        ];
        
        // Cargar vista HTML plana (sin layout)
        require_once '../app/views/ventas/ticket.php';
    }

    // Endpoint AJAX/JSON consumido por fetch() desde el POS (app/views/ventas/pos.php).
    // Responde siempre en JSON: el frontend nunca depende de una redirección aquí.
    public function save() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError(405, 'method_not_allowed', 'Método no permitido.');
        }

        // Sesión y CSRF primero: si la sesión expiró, el frontend debe pedir reautenticación
        // en vez de mostrar un error genérico de token.
        $this->verifyCsrfJson();

        if (!isset($_POST['id_cliente'])) {
            $this->jsonError(400, 'bad_request', 'Datos de venta incompletos.');
        }

        $cajaModel = $this->model('Caja');
        $cajaAbierta = $cajaModel->getCajaAbiertaPorUsuario($_SESSION['user_id']);
        if (!$cajaAbierta) {
            $this->jsonError(409, 'caja_cerrada', 'Tu caja no está abierta. Debes aperturarla antes de registrar ventas.');
        }

        $metodosValidos = ['Efectivo', 'Yape/Plin', 'Tarjeta'];
        $metodo_pago = $_POST['metodo_pago'] ?? '';
        if (!in_array($metodo_pago, $metodosValidos, true)) {
            $this->jsonError(400, 'metodo_pago_invalido', 'Método de pago no válido.');
        }

        $total = (float)($_POST['total_venta'] ?? 0);
        if ($total <= 0) {
            $this->jsonError(400, 'total_invalido', 'El total de la venta debe ser mayor a cero.');
        }

        $pago_recibido = (float)($_POST['pago_recibido'] ?: $total);
        if ($metodo_pago === 'Efectivo' && $pago_recibido < $total) {
            $this->jsonError(400, 'pago_insuficiente', 'El efectivo recibido es menor al total de la venta.');
        }

        $id_cliente = (int)$_POST['id_cliente'];

        $puntos_ganados = 0;
        if ($id_cliente != 1) {
            // 1 Sol = 1 Punto (basado en el total final)
            $puntos_ganados = floor($total);
        }
        $puntos_usados = isset($_POST['puntos_usados']) ? (int)$_POST['puntos_usados'] : 0;
        $descuento = isset($_POST['descuento_venta']) ? (float)$_POST['descuento_venta'] : 0.00;

        // Generar un número ficticio correlativo de ticket
        $numero_t = str_pad(rand(1000, 99999), 6, '0', STR_PAD_LEFT);

        $cabecera = [
            'caja_id' => $cajaAbierta['id'],
            'id_cliente' => $id_cliente,
            'tipo_comprobante' => $_POST['tipo_comprobante'],
            'serie_comprobante' => 'T001',
            'num_comprobante' => $numero_t,
            'subtotal' => (float)$_POST['subtotal_venta'],
            'descuento' => $descuento,
            'igv' => (float)$_POST['igv_venta'],
            'total' => $total,
            'metodo_pago' => $metodo_pago,
            'pago_recibido' => $pago_recibido,
            'vuelto' => (float)($_POST['vuelto_venta'] ?: 0.00),
            'puntos_ganados' => $puntos_ganados,
            'puntos_usados' => $puntos_usados,
            'medico_cmp' => $_POST['medico_cmp'] ?? null
        ];

        // Detalles paralelos por arrays
        $detalles = [];
        $productos = $_POST['producto_id'] ?? [];
        foreach ($productos as $i => $id_prod) {
            if (empty($id_prod) || empty($_POST['cantidad'][$i])) continue;
            $detalles[] = [
                'id_producto' => (int)$id_prod,
                'cantidad' => (int)$_POST['cantidad'][$i],
                'precio_unitario' => (float)$_POST['precio_d'][$i],
                'subtotal' => (float)$_POST['subtotal_d'][$i],
                'tipo_unidad' => $_POST['tipo_unidad'][$i] ?? 'CAJA'
            ];
        }

        if (count($detalles) === 0) {
            $this->jsonError(400, 'carrito_vacio', 'El carrito de compras está vacío.');
        }

        // registrarVenta() ya envuelve la inserción de la cabecera, el descuento FEFO por
        // lote, la actualización de stock y el registro en Kardex en una única transacción
        // (beginTransaction/commit/rollBack en app/models/Venta.php) para que nada quede a
        // medias si algo falla a mitad de camino.
        $modelo = $this->model('Venta');
        $id_venta = $modelo->registrarVenta($cabecera, $detalles, $_SESSION['user_id']);

        if (!$id_venta) {
            $this->jsonError(409, 'stock_insuficiente', 'Stock insuficiente o conflicto de lote FEFO. La venta no fue registrada.');
        }

        if ($id_cliente != 1) {
            $cliModel = $this->model('Cliente');
            $delta = $puntos_ganados - $puntos_usados;
            $cliModel->actualizarPuntos($id_cliente, $delta);
        }

        $this->logAccion('Ventas', 'CREAR', "Venta {$cabecera['tipo_comprobante']} T001-{$numero_t} registrada.", $total);

        // Rotar el token CSRF tras la operación exitosa; el frontend lo toma de la respuesta
        // y lo reutiliza en la siguiente venta sin necesidad de recargar la página.
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        echo json_encode([
            'success' => true,
            'id_venta' => $id_venta,
            'ticket' => "T001-{$numero_t}",
            'mensaje' => "Venta procesada exitosamente. Ticket #T001-{$numero_t}",
            'csrf_token' => $_SESSION['csrf_token']
        ]);
        exit;
    }

    public function anular($id) {
        // Anular una venta es una operación financiera sensible: solo Administrador
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . 'venta/index'); exit; }
        $this->verifyCsrf();

        $modelo = $this->model('Venta');
        
        if($modelo->anularVenta($id, $_SESSION['user_id'])) {
            $this->logAccion('Ventas', 'ANULAR', "Anulación de venta ID #$id por el usuario.");
            $this->flash('success', "Venta anulada correctamente. El stock ha sido devuelto al inventario.");
        } else {
            $this->flash('error', "No se pudo anular la venta. Verifique que no esté ya anulada.");
        }
        header('Location: ' . BASE_URL . 'venta/index');
        exit;
    }
}
