<?php $u = $data['usuario']; ?>
<style>
.perfil-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 25px;
    height: 100%;
}
</style>
<div class="page-content">
    <div class="mb-4">
        <h1 class="page-title"><i class="bi bi-person-circle" style="color:var(--accent-primary);"></i> Mi Perfil</h1>
        <div class="page-subtitle">Consulta y edita tus datos personales, y cambia tu contraseña de acceso.</div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success mb-4" style="background-color: var(--success-bg); color: var(--accent-primary); border: 1px solid var(--accent-primary); font-weight:600;">
            <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['mensaje']); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>usuario/actualizarPerfil" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

        <div class="row g-4">
            <!-- Izquierda: Resumen de cuenta -->
            <div class="col-md-4">
                <div class="perfil-card text-center">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($u['nombres'] . ' ' . $u['apellidos']); ?>&background=00A896&color=fff&bold=true&size=110"
                         alt="Avatar" style="width:110px; height:110px; border-radius:50%; box-shadow:0 2px 8px rgba(0,0,0,0.15); margin-bottom:18px;">
                    <h5 style="color: var(--text-primary); font-weight:700;"><?php echo htmlspecialchars($u['nombres'] . ' ' . $u['apellidos'], ENT_QUOTES, 'UTF-8'); ?></h5>
                    <span class="badge bg-secondary mb-2">@<?php echo htmlspecialchars($u['usuario'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <p style="color: var(--text-secondary); font-size: 13px; margin-top: 8px;">
                        <i class="bi bi-shield-check"></i> Rol: <strong><?php echo htmlspecialchars($data['rol_nombre'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                        <?php if (!empty($u['ultimo_login'])): ?>
                            <i class="bi bi-clock-history"></i> Último acceso: <?php echo date('d/m/Y H:i', strtotime($u['ultimo_login'])); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Derecha: Datos editables -->
            <div class="col-md-8">
                <div class="perfil-card">
                    <h5 style="color: var(--text-primary); font-size: 16px; margin-bottom: 25px;"><i class="bi bi-person-lines-fill"></i> Datos Personales</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="nombres" class="form-control" value="<?php echo htmlspecialchars($u['nombres'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" value="<?php echo htmlspecialchars($u['apellidos'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($u['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <h5 style="color: var(--text-primary); font-size: 16px; margin-top: 30px; margin-bottom: 20px;"><i class="bi bi-key-fill"></i> Cambiar Contraseña</h5>
                    <p style="color: var(--text-secondary); font-size: 12px; margin-top: -12px;">Deja estos campos en blanco si no deseas cambiar tu contraseña.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Contraseña Actual</label>
                            <input type="password" name="password_actual" class="form-control" autocomplete="current-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contraseña Nueva</label>
                            <input type="password" name="password_nueva" class="form-control" minlength="8" autocomplete="new-password">
                            <small class="text-muted">Mínimo 8 caracteres.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary" style="padding: 10px 28px;">
                <i class="bi bi-save2-fill"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>
