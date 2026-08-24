<?php
// Definir BASE_URL globalmente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Endurecer la cookie de sesión antes de iniciarla (mitiga robo de sesión vía XSS/CSRF)
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => $protocol === 'https'
]);
session_start();

// Token CSRF único por sesión, usado por Controller::verifyCsrf() y los formularios
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$host = $_SERVER['HTTP_HOST'];
$script = dirname($_SERVER['SCRIPT_NAME']);
$script = str_replace('\\', '/', $script); // fix para windows
define('BASE_URL', $protocol . '://' . $host . $script . '/');

require_once '../app/config/database.php';
require_once '../app/core/App.php';
require_once '../app/core/Controller.php';

// Inicializar la aplicación (Router)
$app = new App();
