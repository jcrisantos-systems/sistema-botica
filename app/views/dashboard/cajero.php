<div class="page-content">
    <div class="page-header">
        <h1 class="page-title">Hola, <?php echo htmlspecialchars(isset($_SESSION['nombre']) ? explode(' ', $_SESSION['nombre'])[0] : 'Cajero', ENT_QUOTES, 'UTF-8'); ?>!</h1>
        <div class="page-subtitle">Este es tu resumen del día.</div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Mis Ventas de Hoy -->
        <div class="col-md-6">
            <div class="card-metric" style="background: linear-gradient(135deg, var(--accent-primary) 0%, var(--success) 100%); color: white; border: none;">
                <div class="metric-header">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9;">Mis Ventas de Hoy</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 8px; letter-spacing: -1px;">
                    S/ <?php echo number_format($data['ventasHoy']['ingresos'], 2); ?>
                </div>
                <div style="font-size: 13px; font-weight: 500; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px;">
                    <i class="bi bi-receipt"></i> <?php echo (int)$data['ventasHoy']['transacciones']; ?> transacciones hoy
                </div>
            </div>
        </div>

        <!-- Estado de mi Caja -->
        <div class="col-md-6">
            <?php if ($data['cajaAbierta']): ?>
            <div class="card-metric" style="background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%); color: white; border: none;">
                <div class="metric-header">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9;">Estado de mi Caja</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 8px; letter-spacing: -1px;">
                    Abierta
                </div>
                <div style="font-size: 13px; font-weight: 500; margin-bottom: 12px;">
                    Monto inicial: S/ <?php echo number_format($data['cajaAbierta']['monto_inicial'], 2); ?>
                </div>
                <a href="<?php echo BASE_URL; ?>caja/cierre" style="font-size: 13px; font-weight: 500; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; color: white; text-decoration: none;">
                    <i class="bi bi-box-arrow-right"></i> Ir a Cerrar / Arqueo
                </a>
            </div>
            <?php else: ?>
            <div class="card-metric" style="background: linear-gradient(135deg, #F4A261 0%, #E67E22 100%); color: white; border: none;">
                <div class="metric-header">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9;">Estado de mi Caja</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 8px; letter-spacing: -1px;">
                    Cerrada
                </div>
                <a href="<?php echo BASE_URL; ?>caja/apertura" style="font-size: 13px; font-weight: 500; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; color: white; text-decoration: none;">
                    <i class="bi bi-box-arrow-in-right"></i> Ir a Abrir Turno
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
