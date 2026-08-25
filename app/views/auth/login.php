<?php
// Incluir el DOCTYPE e imports de Bootstrap / Iconos en una página limpia (sin el layout general)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Sistema de Botica</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body class="login-body">

<div class="login-shell">
    <!-- Panel de marca (visible en pantallas grandes; comunica confianza y valor del producto) -->
    <div class="login-visual">
        <div class="login-visual-brand">
            <i class="bi bi-heart-pulse-fill"></i> Mi Botica
        </div>
        <div class="login-visual-main">
            <h1>La gestión integral de tu botica, en un solo lugar.</h1>
            <p>Ventas, inventario, compras y reportes conectados en tiempo real, con el respaldo y la seguridad que tu negocio merece.</p>
            <ul class="login-visual-features">
                <li><i class="bi bi-check-lg"></i> Punto de venta rápido y sin fricciones</li>
                <li><i class="bi bi-check-lg"></i> Control de lotes y vencimientos FEFO</li>
                <li><i class="bi bi-check-lg"></i> Auditoría y trazabilidad de cada acción</li>
            </ul>
        </div>
        <div class="login-visual-footer">&copy; <?php echo date('Y'); ?> Mi Botica. Todos los derechos reservados.</div>
    </div>

    <!-- Panel de acceso -->
    <div class="login-form-panel">
        <div class="login-card">
            <div class="login-logo">
                <i class="bi bi-heart-pulse-fill" style="background: linear-gradient(135deg, var(--accent-primary) 0%, var(--success) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i> Mi Botica
            </div>
            <div class="login-title">Bienvenido de nuevo</div>
            <div class="login-subtitle">Inicia sesión con tus credenciales para continuar.</div>

            <?php require_once '../app/views/partials/flash.php'; ?>

            <?php if (!empty($data['error'])): ?>
                <div class="alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($data['error'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo BASE_URL; ?>auth/login" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="username" class="form-control-custom" placeholder="Ej: admin" required autofocus>
                </div>
                <div class="form-group">
                    <div class="d-flex justify-content-between">
                        <label class="form-label">Contraseña</label>
                        <a href="#" style="font-size: 13px; color: var(--accent-primary); text-decoration: none;">¿Olvidaste la clave?</a>
                    </div>
                    <input type="password" name="password" class="form-control-custom" placeholder="••••••••" required>
                </div>
                <div class="form-group form-check mb-4">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember" style="color: var(--text-secondary); font-size: 13px;">Recordarme en este equipo</label>
                </div>

                <button type="submit" class="btn-primary-custom">Iniciar Sesión</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
