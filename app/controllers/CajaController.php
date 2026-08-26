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

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
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

    // Historial propio: cualquier rol ve solo los arqueos de su propio usuario_id
    // (a diferencia de index(), que es el listado completo solo para Administrador).
    public function historial_propio() {
        $cajaModel = $this->model('Caja');

        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        $historial = $cajaModel->getHistorial($fecha_inicio, $fecha_fin, $_SESSION['user_id']);

        $data = [
            'title' => 'Mi Historial de Arqueos',
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
                $_SESSION['mensaje'] = "Caja cerrada correctamente.";
                // No se redirige directamente al ticket (eso "atrapaba" al usuario en una
                // pestaña sin forma de volver). En su lugar, caja/apertura muestra el mensaje
                // de éxito junto con un botón para ver el ticket en una pestaña nueva.
                $_SESSION['ultimo_cierre_id'] = $cajaAbierta['id'];
                header('Location: ' . BASE_URL . 'caja/apertura');
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

        $usuarioNormalizado = $this->normalizarNombreArchivo(($caja['nombres'] ?? '') . ' ' . ($caja['apellidos'] ?? ''));
        // Usamos la fecha de cierre (no "ahora") para que el nombre de archivo sea estable:
        // volver a ver el mismo ticket sobrescribe la misma copia en vez de duplicarla.
        $fechaReferencia = !empty($caja['fecha_cierre']) ? strtotime($caja['fecha_cierre']) : time();
        $tituloTicket = 'Arqueo_' . $usuarioNormalizado . '_' . date('Ymd_His', $fechaReferencia);

        ob_start();
        require '../app/views/cajas/ticket_arqueo.php';
        $html = ob_get_clean();

        $this->guardarCopiaArqueo($caja['id'], $usuarioNormalizado, $fechaReferencia, $html);

        echo $html;
    }

    // Guarda una copia server-side del ticket de arqueo en storage/arqueos/ (fuera de
    // public/, protegido además por su propio .htaccess). Es un respaldo "best effort":
    // si falla, no debe impedir que el usuario vea/imprima su ticket.
    private function guardarCopiaArqueo($cajaId, $usuarioNormalizado, $fechaReferencia, $html) {
        $dir = '../storage/arqueos';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombreArchivo = sprintf('arqueo_%d_%s_%s.html', $cajaId, $usuarioNormalizado, date('Ymd_His', $fechaReferencia));

        if (@file_put_contents($dir . '/' . $nombreArchivo, $html) === false) {
            error_log('No se pudo guardar copia del arqueo: ' . $nombreArchivo);
        }
    }

    // Convierte nombre/apellido (u otro texto libre) en un fragmento seguro para nombre
    // de archivo: sin tildes/ñ, sin espacios, solo minúsculas/números/guion bajo.
    private function normalizarNombreArchivo($texto) {
        $texto = trim($texto);
        $transliterado = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        $texto = $transliterado !== false ? $transliterado : $texto;
        $texto = strtolower($texto);
        $texto = preg_replace('/[^a-z0-9]+/', '_', $texto);
        return trim($texto, '_');
    }
}
