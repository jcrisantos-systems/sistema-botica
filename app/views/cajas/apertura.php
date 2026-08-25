<div class="page-content">
    <div class="mb-4">
        <h1 class="page-title"><i class="bi bi-box-arrow-in-right" style="color:var(--accent-primary);"></i> Apertura de Caja</h1>
        <div class="page-subtitle">Ingrese el monto base de sencillo con el que iniciará su turno.</div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card-metric text-center" style="border: 2px solid var(--accent-primary);">
                <i class="bi bi-cash-stack" style="font-size: 3rem; color:var(--accent-primary);"></i>
                <h5 style="color: var(--text-primary); font-weight:700; margin-top: 12px;">Registrar Saldo Inicial</h5>
                <form action="<?php echo BASE_URL; ?>caja/apertura" method="POST" class="mt-4">
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
