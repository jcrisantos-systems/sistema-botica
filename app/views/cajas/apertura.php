<div class="page-content">
    <div class="mb-4">
        <h1 class="page-title"><i class="bi bi-box-arrow-in-right" style="color:var(--accent-primary);"></i> Apertura de Caja</h1>
        <div class="page-subtitle">Ingrese el monto base de sencillo con el que iniciará su turno.</div>
    </div>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8'); ?></span>
            <?php if (isset($_SESSION['ultimo_cierre_id'])): ?>
                <a href="<?php echo BASE_URL; ?>caja/ticket_arqueo/<?php echo (int)$_SESSION['ultimo_cierre_id']; ?>" target="_blank" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-printer"></i> Ver ticket de cierre
                </a>
            <?php endif; ?>
        </div>
        <?php unset($_SESSION['mensaje']); unset($_SESSION['ultimo_cierre_id']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="text-center mb-3">
        <a href="<?php echo BASE_URL; ?>caja/historial_propio" style="font-size: 13px; color: var(--text-secondary);">
            <i class="bi bi-clock-history"></i> Ver mi historial de arqueos
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card-metric text-center" style="border: 2px solid var(--accent-primary);">
                <i class="bi bi-cash-stack" style="font-size: 3rem; color:var(--accent-primary);"></i>
                <h5 style="color: var(--text-primary); font-weight:700; margin-top: 12px;">Registrar Saldo Inicial</h5>
                <form action="<?php echo BASE_URL; ?>caja/apertura" method="POST" class="mt-4">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label class="form-label text-start d-block">Monto Base (S/)</label>
                        <input type="number" step="0.01" name="monto_inicial" class="form-control-custom text-center" style="font-size: 1.5rem; font-weight: bold;" placeholder="0.00" required autofocus>
                        <small style="color:var(--text-secondary); text-align:left; display:block; margin-top:8px;">Monto físico (monedas/billetes) disponible en gaveta al iniciar.</small>
                    </div>
                    <button type="submit" class="btn-primary-custom">
                        <i class="bi bi-check-circle"></i> ABRIR CAJA
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
