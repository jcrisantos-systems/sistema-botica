<?php
class AuthController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
        // Cajero (rol_id = 3): "Acceso al Punto de Venta (POS) únicamente" según la
        // descripción real del rol en la tabla `roles` de la BD.
        if ($_SESSION['rol_id'] == 3) {
            header('Location: ' . BASE_URL . 'venta/pos');
        } else {
            header('Location: ' . BASE_URL . 'dashboard/index');
        }
        exit;
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();

            // Mitigación de fuerza bruta: bloqueo temporal creciente tras 5 intentos fallidos
            $intentos = $_SESSION['login_intentos'] ?? 0;
            $bloqueadoHasta = $_SESSION['login_bloqueado_hasta'] ?? 0;

            if ($intentos >= 5 && time() < $bloqueadoHasta) {
                $espera = $bloqueadoHasta - time();
                $error = "Demasiados intentos fallidos. Intente nuevamente en {$espera} segundos.";
                $this->view('auth/login', ['error' => $error]);
                return;
            }

            $userModel = $this->model('User');
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $userModel->login($username, $password);

            if ($user) {
                // Regenerar el ID de sesión tras autenticar (previene fijación de sesión)
                session_regenerate_id(true);

                unset($_SESSION['login_intentos'], $_SESSION['login_bloqueado_hasta']);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombres'] . ' ' . $user['apellidos'];
                $_SESSION['rol_id'] = $user['rol_id'];

                $userModel->updateLastLogin($user['id']);

                // Registro de Auditoría
                $auditModel = $this->model('Auditoria');
                $auditModel->registrarAcceso($user['id'], 'LOGIN');

                header('Location: ' . BASE_URL . 'auth/index');
                exit;
            } else {
                $intentos++;
                $_SESSION['login_intentos'] = $intentos;
                if ($intentos >= 5) {
                    $_SESSION['login_bloqueado_hasta'] = time() + min(300, 10 * ($intentos - 4));
                }
                $error = 'Usuario o contraseña incorrectos';
            }
        }

        $this->view('auth/login', ['error' => $error]);
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $auditModel = $this->model('Auditoria');
            $auditModel->registrarAcceso($_SESSION['user_id'], 'LOGOUT');
        }
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }
}
