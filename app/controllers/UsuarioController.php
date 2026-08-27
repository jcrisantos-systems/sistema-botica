<?php
class UsuarioController extends Controller {

    public function __construct() {
        // Todas las acciones requieren sesión iniciada; la gestión de otros usuarios
        // (index/save/toggle) además exige rol de Administrador (ver cada método).
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function index() {
        $this->requireAdmin();

        $userModel = $this->model('User');
        $roleModel = $this->model('Role');
        
        $data = [
            'title' => 'Gestión de Personal',
            'usuarios' => $userModel->getAll(),
            'roles' => $roleModel->getAll()
        ];
        
        $this->view('usuarios/index', $data);
    }

    public function save() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $userModel = $this->model('User');

            $data = [
                'nombres' => trim($_POST['nombres']),
                'apellidos' => trim($_POST['apellidos']),
                'usuario' => trim($_POST['usuario']),
                'email' => trim($_POST['email']),
                'rol_id' => (int)$_POST['rol_id'],
                'estado' => 1
            ];

            // Validación backend de correo duplicado, normalizando mayúsculas/minúsculas y
            // espacios. No existe todavía un UNIQUE de BD para email (pendiente de Fase F,
            // tras decidir qué hacer con las 3 cuentas duplicadas ya detectadas) — este
            // chequeo solo previene NUEVOS duplicados, sin tocar cuentas existentes. Se
            // omite si el correo queda vacío (el campo no es obligatorio a nivel de BD).
            if ($data['email'] !== '') {
                $excludeIdEmail = empty($_POST['id']) ? null : $_POST['id'];
                if ($userModel->existeEmail($data['email'], $excludeIdEmail)) {
                    $this->flash('error', "Ya existe otro usuario registrado con el correo '{$data['email']}'.");
                    header('Location: ' . BASE_URL . 'usuario/index');
                    exit;
                }
            }

            if (empty($_POST['id'])) {
                // Nuevo usuario
                if (strlen($_POST['password'] ?? '') < 8) {
                    $this->flash('error', "La contraseña debe tener al menos 8 caracteres.");
                    header('Location: ' . BASE_URL . 'usuario/index');
                    exit;
                }
                // Verificación previa (feedback inmediato y con el mensaje exacto solicitado);
                // el try/catch de abajo queda como red de seguridad ante una condición de carrera
                // (dos altas simultáneas con el mismo usuario) u otra restricción de la BD.
                if ($userModel->existeUsuario($data['usuario'])) {
                    $this->flash('error', "El nombre de usuario '{$data['usuario']}' ya está en uso. Por favor, elige otro.");
                    header('Location: ' . BASE_URL . 'usuario/index');
                    exit;
                }
                $data['password'] = $_POST['password'];
                try {
                    $userModel->create($data);
                    $this->flash('success', "Usuario creado exitosamente.");
                } catch (PDOException $e) {
                    $this->flash('error', $this->handleDbException($e, ['usuario' => 'nombre de usuario', 'email' => 'correo electrónico']));
                }
            } else {
                // Actualizar usuario
                if (!empty($_POST['password']) && strlen($_POST['password']) < 8) {
                    $this->flash('error', "La contraseña debe tener al menos 8 caracteres.");
                    header('Location: ' . BASE_URL . 'usuario/index');
                    exit;
                }
                if ($userModel->existeUsuario($data['usuario'], $_POST['id'])) {
                    $this->flash('error', "El nombre de usuario '{$data['usuario']}' ya está en uso. Por favor, elige otro.");
                    header('Location: ' . BASE_URL . 'usuario/index');
                    exit;
                }
                try {
                    $userModel->update($_POST['id'], $data);
                    // Actualizar constraseña si se proporcionó
                    if (!empty($_POST['password'])) {
                        $userModel->updatePassword($_POST['id'], $_POST['password']);
                    }
                    $this->flash('success', "Usuario actualizado exitosamente.");
                } catch (PDOException $e) {
                    $this->flash('error', $this->handleDbException($e, ['usuario' => 'nombre de usuario', 'email' => 'correo electrónico']));
                }
            }
        }
        header('Location: ' . BASE_URL . 'usuario/index');
        exit;
    }

    public function toggle($id) {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . 'usuario/index'); exit; }
        $this->verifyCsrf();

        $userModel = $this->model('User');
        if($id == 1) {
            $_SESSION['error'] = "No se puede desactivar al Administrador principal.";
        } else {
            if ($userModel->toggleEstado($id)) {
                $_SESSION['mensaje'] = "Estado de usuario cambiado.";
            } else {
                $_SESSION['error'] = "Error al cambiar estado.";
            }
        }
        header('Location: ' . BASE_URL . 'usuario/index');
    }

    // Perfil propio: cualquier usuario autenticado puede ver/editar sus datos y su contraseña,
    // sin necesidad de permisos de Administrador.
    public function perfil() {
        $userModel = $this->model('User');
        $usuario = $userModel->getById($_SESSION['user_id']);

        if (!$usuario) {
            header('Location: ' . BASE_URL . 'auth/logout');
            exit;
        }

        $roles = [1 => 'Administrador', 2 => 'Farmacéutico', 3 => 'Cajero', 4 => 'Almacenero'];

        $this->view('usuarios/perfil', [
            'title' => 'Mi Perfil',
            'usuario' => $usuario,
            'rol_nombre' => $roles[$usuario['rol_id']] ?? 'Usuario'
        ]);
    }

    public function actualizarPerfil() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . 'usuario/perfil'); exit; }
        $this->verifyCsrf();

        $userModel = $this->model('User');
        $id = $_SESSION['user_id'];
        $actual = $userModel->getById($id);

        if (!$actual) {
            header('Location: ' . BASE_URL . 'auth/logout');
            exit;
        }

        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($nombres === '' || $apellidos === '') {
            $_SESSION['error'] = "Nombres y apellidos son obligatorios.";
            header('Location: ' . BASE_URL . 'usuario/perfil');
            exit;
        }

        // Mismo control de correo duplicado que en la gestión de personal (Fase C), para
        // que un usuario no pueda auto-asignarse por perfil el correo de otra cuenta.
        if ($email !== '' && $userModel->existeEmail($email, $id)) {
            $_SESSION['error'] = "Ya existe otro usuario registrado con el correo '{$email}'.";
            header('Location: ' . BASE_URL . 'usuario/perfil');
            exit;
        }

        // El usuario/rol_id se conservan intactos: el propio usuario no puede
        // cambiarse el nombre de acceso ni auto-otorgarse otro rol desde este formulario.
        $data = [
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'usuario' => $actual['usuario'],
            'email' => $email,
            'rol_id' => $actual['rol_id']
        ];

        $passwordNueva = $_POST['password_nueva'] ?? '';
        if ($passwordNueva !== '') {
            $passwordActual = $_POST['password_actual'] ?? '';
            if (!password_verify($passwordActual, $actual['password'])) {
                $_SESSION['error'] = "La contraseña actual ingresada es incorrecta.";
                header('Location: ' . BASE_URL . 'usuario/perfil');
                exit;
            }
            if (strlen($passwordNueva) < 8) {
                $_SESSION['error'] = "La nueva contraseña debe tener al menos 8 caracteres.";
                header('Location: ' . BASE_URL . 'usuario/perfil');
                exit;
            }
            $userModel->updatePassword($id, $passwordNueva);
        }

        if ($userModel->update($id, $data)) {
            $_SESSION['nombre'] = $nombres . ' ' . $apellidos;
            $_SESSION['mensaje'] = "Perfil actualizado correctamente.";
        } else {
            $_SESSION['error'] = "Error al actualizar el perfil.";
        }

        header('Location: ' . BASE_URL . 'usuario/perfil');
        exit;
    }
}
