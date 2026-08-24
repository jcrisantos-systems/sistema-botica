<?php
class CajaController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function index() {
        // Historial de todos los cajeros: solo administradores
        $this->requireAdmin();

        $cajaModel = $this->model('Caja');

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        
        $historial = $cajaModel->getHistorial($fecha_inicio, $fecha_fin);
        
        $data = [
            'title' => 'Historial de Cajas',
            'historial' => $historial,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ];
        
        $this->view('cajas/index', $data);
    }

    public function apertura() {
        $cajaModel = $this->model('Caja');
        
        // Si ya tiene caja abierta, no puede abrir otra.
        $cajaAbierta = $cajaModel->getCajaAbiertaPorUsuario($_SESSION['user_id']);
        if ($cajaAbierta) {
            header('Location: ' . BASE_URL . 'caja/cierre');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $monto_inicial = (float)($_POST['monto_inicial'] ?? 0);

            if ($cajaModel->abrirCaja($_SESSION['user_id'], $monto_inicial)) {
                $_SESSION['mensaje'] = "Caja aperturada exitosamente. Puede iniciar la venta.";
                header('Location: ' . BASE_URL . 'venta/pos');
                exit;
            } else {
                $_SESSION['error'] = "Ocurrió un error al abrir la caja.";
            }
        }
        
        $this->view('cajas/apertura', ['title' => 'Apertura de Caja']);
    }

    public function cierre() {
        $cajaModel = $this->model('Caja');
        
        // Verificar si tiene caja abierta
        $cajaAbierta = $cajaModel->getCajaAbiertaPorUsuario($_SESSION['user_id']);
        
        if (!$cajaAbierta) {
            $_SESSION['error'] = "No tienes ninguna caja abierta para cerrar.";
            header('Location: ' . BASE_URL . 'caja/apertura');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $monto_final_real = (float)($_POST['monto_final_real'] ?? 0);
            $observacion = trim($_POST['observacion'] ?? '');
            
            if ($cajaModel->cerrarCaja($cajaAbierta['id'], $monto_final_real, $observacion)) {
                $_SESSION['mensaje'] = "Caja cerrada correctamente. Puede imprimir el arqueo.";
                header('Location: ' . BASE_URL . 'caja/ticket_arqueo/' . $cajaAbierta['id']);
                exit;
            } else {
                $_SESSION['error'] = "Ocurrió un error al cerrar la caja.";
            }
        }
        
        $resumen = $cajaModel->getResumenActual($cajaAbierta['id']);
        $movimientos = $cajaModel->getMovimientos($cajaAbierta['id']);
        
        $data = [
            'title' => 'Cierre de Caja',
            'caja' => $cajaAbierta,
            'resumen' => $resumen,
            'movimientos' => $movimientos
        ];
        
        $this->view('cajas/cierre', $data);
    }
    
    public function movimiento() {
        $cajaModel = $this->model('Caja');
        $cajaAbierta = $cajaModel->getCajaAbiertaPorUsuario($_SESSION['user_id']);
        
        if (!$cajaAbierta) {
            $_SESSION['error'] = "No tienes caja abierta para registrar movimientos.";
        } else {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->verifyCsrf();
                $tipo = $_POST['tipo'] ?? '';
                $monto = (float)($_POST['monto'] ?? 0);
                $motivo = trim($_POST['motivo'] ?? '');

                if (!in_array($tipo, ['INGRESO', 'EGRESO'], true)) {
                    $_SESSION['error'] = "Tipo de movimiento inválido.";
                } elseif ($monto > 0 && $cajaModel->registrarMovimiento($cajaAbierta['id'], $tipo, $monto, $motivo)) {
                    $_SESSION['mensaje'] = "Movimiento extra registrado exitosamente.";
                } else {
                    $_SESSION['error'] = "Error al registrar el movimiento.";
                }
            }
        }
        header('Location: ' . BASE_URL . 'caja/cierre');
        exit;
    }
    
    public function ticket_arqueo($id) {
        $cajaModel = $this->model('Caja');
        $caja = $cajaModel->getById($id);

        if(!$caja) {
            die("Caja no encontrada.");
        }

        // Solo el administrador o el cajero dueño de ese arqueo pueden verlo (evita IDOR)
        $esDueno = isset($caja['usuario_id']) && $caja['usuario_id'] == $_SESSION['user_id'];
        if (($_SESSION['rol_id'] ?? null) != 1 && !$esDueno) {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }

        require_once '../app/views/cajas/ticket_arqueo.php';
    }
}
