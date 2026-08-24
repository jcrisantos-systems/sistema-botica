<?php
class Controller {
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    public function view($view, $data = []) {
        if (file_exists('../app/views/' . $view . '.php')) {
            if (strpos($view, 'auth/') !== false) {
                require_once '../app/views/' . $view . '.php';
            } else {
                require_once '../app/views/layouts/main.php';
            }
        } else {
            die("La vista $view no existe.");
        }
    }

    // Sistema global de mensajes flash: cualquier controlador puede llamar $this->flash(...)
    // y el mensaje aparecerá automáticamente como alerta Bootstrap en la siguiente página
    // renderizada (ver app/views/partials/flash.php, incluido una sola vez en el layout).
    protected function flash($type, $message) {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
    }

    // Traduce una PDOException (típicamente de una restricción UNIQUE violada) en un mensaje
    // amigable para el usuario, sin exponer detalles internos de la base de datos.
    // $fieldLabels permite mapear el nombre de la columna/índice de MySQL a una etiqueta legible,
    // p.ej. ['usuario' => 'nombre de usuario'].
    protected function handleDbException(PDOException $e, array $fieldLabels = []) {
        error_log('DB Error: ' . $e->getMessage());

        $driverCode = $e->errorInfo[1] ?? null;
        if ($e->getCode() == 23000 && $driverCode == 1062
            && preg_match("/Duplicate entry '(.*)' for key '(?:[^.']+\\.)?([^']+)'/", $e->getMessage(), $m)) {
            $valor = $m[1];
            $columna = $m[2];
            $etiqueta = $fieldLabels[$columna] ?? str_replace('_', ' ', $columna);
            return "Ya existe un registro con ese {$etiqueta} ('{$valor}'). Por favor, utiliza un valor diferente.";
        }

        if ($e->getCode() == 23000) {
            return "No se pudo guardar: los datos ingresados no son válidos o entran en conflicto con otro registro existente.";
        }

        return "Ocurrió un error inesperado al guardar los datos. Por favor, intenta nuevamente.";
    }

    protected function logAccion($modulo, $accion, $descripcion, $monto = 0) {
        if (isset($_SESSION['user_id'])) {
            $audit = $this->model('Auditoria');
            $audit->registrarAccion($_SESSION['user_id'], $modulo, $accion, $descripcion, $monto);
        }
    }

    // Verifica el token CSRF en peticiones POST. Corta la ejecución si es inválido o falta.
    // Acepta el token tanto en el campo oculto csrf_token como en el encabezado X-CSRF-Token
    // (usado por las peticiones fetch/AJAX, p.ej. el POS).
    protected function verifyCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
            if (empty($_SESSION['csrf_token']) || !is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
                http_response_code(403);
                die('Token de seguridad inválido o expirado. Por favor, recargue la página e intente nuevamente.');
            }
        }
    }

    // Igual que verifyCsrf(), pero pensada para endpoints JSON/AJAX: en vez de "morir" con
    // texto plano, corta la ejecución devolviendo un JSON con un código que el frontend puede
    // interpretar (p.ej. para mostrar una alerta y redirigir a login).
    protected function verifyCsrfJson() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonError(401, 'session_expired', 'Tu sesión ha expirado. Por favor, vuelve a iniciar sesión para continuar.');
        }
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (empty($_SESSION['csrf_token']) || !is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $this->jsonError(403, 'csrf_invalid', 'Token de seguridad inválido o expirado. Por favor, vuelve a iniciar sesión.');
        }
    }

    // Responde con un JSON de error estandarizado y corta la ejecución.
    protected function jsonError($httpCode, $code, $message) {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'code' => $code, 'message' => $message]);
        exit;
    }

    // Corta la ejecución si el usuario en sesión no es Administrador (rol_id = 1)
    protected function requireAdmin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
        if (($_SESSION['rol_id'] ?? null) != 1) {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }
    }
}
