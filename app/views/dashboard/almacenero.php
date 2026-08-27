<div class="page-content">
    <div class="page-header">
        <h1 class="page-title">Hola, <?php echo htmlspecialchars(isset($_SESSION['nombre']) ? explode(' ', $_SESSION['nombre'])[0] : 'Almacenero', ENT_QUOTES, 'UTF-8'); ?>!</h1>
        <div class="page-subtitle">Este es tu resumen del día.</div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Alertas de Vencimiento -->
        <div class="col-md-6">
            <div class="card-metric" style="background: linear-gradient(135deg, #E63946 0%, #C1121F 100%); color: white; border: none;">
                <div class="metric-header">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9;">Alertas de Vencimiento</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 8px; letter-spacing: -1px;">
                    <?php echo count($data['lotesVencer']); ?>
                </div>
                <a href="<?php echo BASE_URL; ?>inventario/lotes" style="font-size: 13px; font-weight: 500; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; color: white; text-decoration: none;">
                    <i class="bi bi-clock-history"></i> Ver Fechas de Vencimiento
                </a>
            </div>
        </div>

        <!-- Stock Bajo Mínimo -->
        <div class="col-md-6">
            <div class="card-metric" style="background: linear-gradient(135deg, #F4A261 0%, #E67E22 100%); color: white; border: none;">
                <div class="metric-header">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9;">Stock Bajo Mínimo</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="bi bi-capsule-pill"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 8px; letter-spacing: -1px;">
                    <?php echo count($data['stockBajo']); ?>
                </div>
                <a href="<?php echo BASE_URL; ?>inventario/kardex" style="font-size: 13px; font-weight: 500; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; color: white; text-decoration: none;">
                    <i class="bi bi-clipboard-data"></i> Ver Kardex General
                </a>
            </div>
        </div>
    </div>
</div>
