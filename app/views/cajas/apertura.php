<div class="container-fluid px-4 pt-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="h3 text-gray-800 mb-0"><i class="bi bi-box-arrow-in-right text-success"></i> Apertura de Caja</h2>
            <p class="text-muted mt-2">Ingrese el monto base de sencillo con el que iniciará su turno.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-success">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-cash-stack text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 font-weight-bold text-success">Registrar Saldo Inicial</h5>
                    <form action="<?php echo BASE_URL; ?>caja/apertura" method="POST" class="mt-4">
                        <div class="mb-3">
                            <label class="form-label text-start d-block font-weight-bold">Monto Base (S/)</label>
                            <input type="number" step="0.01" name="monto_inicial" class="form-control form-control-lg text-center" style="font-size: 1.5rem; font-weight: bold;" placeholder="0.00" required autofocus>
                            <small class="text-muted text-start d-block mt-2">Monto físico (monedas/billetes) disponible en gaveta al iniciar.</small>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100 font-weight-bold">
                            <i class="bi bi-check-circle"></i> ABRIR CAJA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
