<div class="page-content">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="page-title"><i class="bi bi-bar-chart-line-fill text-primary"></i> Reportes Gerenciales</h2>
            <p class="page-subtitle mt-2">Centro de descargas y auditoría para fines contables y de supervisión.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Tarjeta: Reporte de Ventas Financiero -->
        <div class="col-md-6">
            <div class="card card-metric h-100">
                <div class="card-body d-flex flex-column" style="padding: 5px;">
                    <h5 class="text-primary font-weight-bold mb-3"><i class="bi bi-file-earmark-excel-fill text-success"></i> Extracto de Ventas</h5>
                    <p class="text-muted" style="font-size: 14px;">Genera un archivo Excel (.csv compatibilidad universal) con el desglose de ventas, impuestos asimilados y métodos de pago.</p>
                    
                    <form action="<?php echo BASE_URL; ?>reporte/exportar_ventas" method="GET" class="mt-auto">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" style="font-size: 12px; font-weight:600;">Fecha Desde</label>
                                <input type="date" name="fecha_inicio" class="form-control" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size: 12px; font-weight:600;">Fecha Hasta</label>
                                <input type="date" name="fecha_fin" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="submit" class="btn btn-outline-success w-100 font-weight-bold" style="font-size:12px;">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> CSV
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" onclick="this.form.action='<?php echo BASE_URL; ?>reporte/ventas_pdf'; this.form.target='_blank'; this.form.submit(); this.form.action='<?php echo BASE_URL; ?>reporte/exportar_ventas'; this.form.target='_self';" class="btn btn-warning w-100 font-weight-bold text-dark" style="font-size:12px;">
                                    <i class="bi bi-file-earmark-pdf"></i> Visual PDF
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Lotes por Vencer -->
        <div class="col-md-6">
            <div class="card card-metric h-100">
                <div class="card-body d-flex flex-column" style="padding: 5px;">
                    <h5 class="text-primary font-weight-bold mb-3"><i class="bi bi-box-seam-fill text-warning"></i> Medicamentos por Vencer</h5>
                    <p class="text-muted" style="font-size: 14px;">Descarga la relación de lotes farmacológicos cuyo vencimiento esté marcado dentro de los próximos 90 días o ya estén mermados.</p>
                    
                    <div class="row g-2 mt-auto">
                        <div class="col-6">
                            <a href="<?php echo BASE_URL; ?>reporte/vencimientos_excel" class="btn btn-outline-success w-100 font-weight-bold" style="font-size:12px;">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Bajar CSV
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?php echo BASE_URL; ?>reporte/vencimientos_pdf" target="_blank" class="btn btn-warning w-100 font-weight-bold text-dark" style="font-size:12px;">
                                <i class="bi bi-file-earmark-pdf"></i> Imprimir a PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Futuros Reportes -->
        <div class="col-md-12">
            <div class="card card-metric" style="border: 2px dashed var(--border-color); background-color: var(--bg-dark); box-shadow:none;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-clock-history text-muted mb-2" style="font-size: 32px;"></i>
                    <h6 class="text-muted font-weight-bold">Integración con SUNAT / Facturación Electrónica</h6>
                    <p class="text-secondary" style="font-size: 13px; margin-bottom: 0;">Los reportes directos en XML/CDR para SUNAT requerirán el siguiente módulo de firma digital una vez provisto el Certificado Digital.</p>
                </div>
            </div>
        </div>
    </div>
</div>
