<?php
class ConfiguracionController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
        // Solo el administrador debe poder cambiar configuración general de la botica
        if ($_SESSION['rol_id'] != 1) {
            header('Location: ' . BASE_URL . 'dashboard/index');
            exit;
        }
    }

    public function index() {
        $configModel = $this->model('Configuracion');
        $configs = $configModel->getAll();
        
        $data = [
            'title' => 'Configuración de Empresa',
            'configs' => $configs
        ];
        
        $this->view('configuracion/index', $data);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $configModel = $this->model('Configuracion');

            $updates = [
                'nombre_botica' => trim($_POST['nombre_botica']),
                'ruc' => trim($_POST['ruc']),
                'direccion' => trim($_POST['direccion']),
                'telefono' => trim($_POST['telefono']),
                'moneda' => trim($_POST['moneda']),
                'igv' => trim($_POST['igv'])
            ];
            
            // Upload Logo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                // Verificar que el contenido sea realmente una imagen válida, no solo la extensión
                $infoImagen = @getimagesize($_FILES['logo']['tmp_name']);
                $mimesPermitidos = ['image/jpeg', 'image/png', 'image/gif'];

                if (in_array($ext, $allowed) && $infoImagen !== false && in_array($infoImagen['mime'], $mimesPermitidos)) {
                    $newFileName = 'logo_botica_' . time() . '.' . $ext;
                    $destPath = 'img/' . $newFileName;
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $destPath)) {
                        $updates['logo'] = BASE_URL . $destPath;
                    }
                } else {
                    $this->flash('error', "El archivo subido no es una imagen válida (jpg, png o gif).");
                }
            }

            if ($configModel->updateMultiples($updates)) {
                $this->flash('success', "Parámetros actualizados correctamente.");
            } else {
                $this->flash('error', "Hubo un error al actualizar los datos de la botica en BD.");
            }
        }
        header('Location: ' . BASE_URL . 'configuracion/index');
        exit;
    }

    // "Restablecer a Estado de Fábrica". Acceso ya restringido a Administrador por el
    // constructor de este controlador. Requiere reverificar la contraseña del propio
    // usuario en sesión y escribir literalmente "RESTABLECER" antes de tocar la BD.
    public function reset() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'configuracion/index');
            exit;
        }
        $this->verifyCsrf();

        $password = $_POST['password_actual'] ?? '';
        $confirmacion = trim($_POST['confirmacion'] ?? '');
        $opcion = $_POST['opcion_reset'] ?? '';

        $userModel = $this->model('User');
        $usuarioActual = $userModel->getById($_SESSION['user_id']);

        if (!$usuarioActual || !password_verify($password, $usuarioActual['password'])) {
            $this->flash('error', "La contraseña ingresada no es correcta. No se realizó ningún cambio.");
            header('Location: ' . BASE_URL . 'configuracion/index');
            exit;
        }

        if ($confirmacion !== 'RESTABLECER') {
            $this->flash('error', "Debes escribir exactamente la palabra 'RESTABLECER' para confirmar. No se realizó ningún cambio.");
            header('Location: ' . BASE_URL . 'configuracion/index');
            exit;
        }

        if (!in_array($opcion, ['total', 'transacciones'], true)) {
            $this->flash('error', "Debes seleccionar una opción de limpieza válida.");
            header('Location: ' . BASE_URL . 'configuracion/index');
            exit;
        }

        $resetModel = $this->model('SistemaReset');
        $exito = $resetModel->ejecutar($opcion);

        if (!$exito) {
            $this->flash('error', "Ocurrió un error al restablecer el sistema. La operación fue revertida por completo; no se perdió ningún dato.");
            header('Location: ' . BASE_URL . 'configuracion/index');
            exit;
        }

        $descripcion = $opcion === 'total'
            ? "Restablecimiento TOTAL del sistema a estado de fábrica (catálogos y transacciones eliminados)."
            : "Limpieza de transacciones del sistema (catálogos preservados).";
        $this->logAccion('Configuracion', 'RESET_SISTEMA', $descripcion);

        // Cerramos la sesión actual de forma segura y abrimos una nueva, vacía, solo
        // para poder entregar el mensaje de éxito vía el sistema global de flash() en
        // la pantalla de login (auth/login.php no usa el layout principal, así que
        // incluye su propio partials/flash.php).
        $_SESSION = [];
        session_destroy();
        session_start();
        $this->flash('success', "El sistema fue restablecido correctamente. Por favor, inicia sesión nuevamente.");

        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }
}
