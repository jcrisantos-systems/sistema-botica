<div class="page-content">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="page-title"><i class="bi bi-bell-fill text-danger"></i> Centro de Alertas Sanitarias</h2>
            <p class="page-subtitle mt-2">Atención prioritaria para mantener la botica abastecida y sin productos vencidos.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Vencimientos -->
        <div class="col-md-6">
            <div class="card card-metric h-100" style="border-top: 4px solid var(--danger);">
                <div class="card-body">
                    <h5 class="text-danger font-weight-bold mb-3"><i class="bi bi-calendar-x-fill"></i> Lotes Críticos (Vencimientos)</h5>
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead style="font-size: 12px; opacity: 0.7;">
                                <tr>
                                    <th>Producto</th>
                                    <th>Lote</th>
                                    <th>Vence</th>
                                    <th class="text-end">Stock U.M.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($data['lotes'])): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">No hay lotes próximos a vencer.</td></tr>
                                <?php else: ?>
                                    <?php 
                                    $hoy = new DateTime();
                                    foreach($data['lotes'] as $l): 
                                        $fv = new DateTime($l['fecha_vencimiento']);
                                        $diff = $hoy->diff($fv)->days;
                                        $isVencido = $fv < $hoy;
                                        $color = $isVencido || $diff <= 30 ? 'color: var(--danger);' : 'color: var(--warning);';
                                    ?>
                                    <tr>
                                        <td style="color:var(--text-primary); font-size:13px;"><?php echo htmlspecialchars($l['producto']); ?></td>
                                        <td style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($l['lote']); ?></td>
                                        <td style="<?php echo $color; ?> font-weight:bold; font-size:12px;">
                                            <?php echo date('d/m/Y', strtotime($l['fecha_vencimiento'])); ?><br>
                                            <small>(<?php echo $isVencido ? 'VENCIDO' : "En $diff días"; ?>)</small>
                                        </td>
                                        <td class="text-end font-weight-bold" style="color:var(--accent-primary);"><?php echo $l['stock']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quiebre Stock -->
        <div class="col-md-6">
            <div class="card card-metric h-100" style="border-top: 4px solid var(--warning);">
                <div class="card-body">
                    <h5 class="text-warning font-weight-bold mb-3"><i class="bi bi-box-arrow-down"></i> Bajo Nivel de Inventario</h5>
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead style="font-size: 12px; opacity: 0.7;">
                                <tr>
                                    <th>Producto</th>
                                    <th>Und Medida</th>
                                    <th class="text-end">Stock Min. Actual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($data['bajos'])): ?>
                                    <tr><td colspan="3" class="text-center text-muted py-4">No hay productos con bajo stock.</td></tr>
                                <?php else: ?>
                                    <?php foreach($data['bajos'] as $b): ?>
                                    <tr>
                                        <td style="color:var(--text-primary); font-size:13px;"><?php echo htmlspecialchars($b['producto']); ?></td>
                                        <td style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($b['unidad_medida']); ?></td>
                                        <td class="text-end font-weight-bold" style="<?php echo $b['stock'] <= 5 ? 'color:var(--danger);' : 'color:var(--warning);'; ?>">
                                            <?php echo $b['stock']; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
