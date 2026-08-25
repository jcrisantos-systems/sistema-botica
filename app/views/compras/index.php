<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Historial de Compras</h1>
            <div class="page-subtitle">Facturas y recepciones de mercadería al almacén.</div>
        </div>
        <a href="<?php echo BASE_URL; ?>compra/create" class="btn-primary-custom" style="width: auto; padding: 10px 20px; text-decoration: none; display: inline-block;">
            <i class="bi bi-cart-plus"></i> Ingresar Mercadería
        </a>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th width="100">ID / Info</th>
                        <th>Proveedor</th>
                        <th>Documento</th>
                        <th>Fecha Compra</th>
                        <th class="text-end">Total Compra (S/)</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['compras'])): ?>
                    <tr><td colspan="5" class="text-center text-muted">No hay compras registradas en el sistema.</td></tr>
                    <?php else: ?>
                    <?php foreach($data['compras'] as $compra): ?>
                    <tr>
                        <td style="color:var(--text-secondary); font-family:monospace;">
                            #C-<?php echo str_pad($compra['id'], 5, '0', STR_PAD_LEFT); ?><br>
                            <small class="text-muted"><i class="bi bi-person"></i> <?php echo explode(' ', $compra['cajero'])[0]; ?></small>
                        </td>
                        <td style="font-weight:600; color:var(--text-primary);">
                            <?php echo htmlspecialchars($compra['proveedor']); ?>
                        </td>
                        <td>
                            <strong style="color:var(--accent-primary);"><?php echo htmlspecialchars($compra['tipo_comprobante']); ?></strong><br>
                            <span style="font-size:12px;"><?php echo htmlspecialchars($compra['serie_comprobante'] . '-' . $compra['num_comprobante']); ?></span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($compra['fecha_compra'])); ?></td>
                        <td class="text-end" style="font-size: 16px; font-weight:700;">
                            <?php echo number_format($compra['total'], 2); ?>
                        </td>
                        <td class="text-center">
                            <?php if($compra['estado'] == 'Pendiente'): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock-history"></i> Pendiente</span>
                            <?php elseif($compra['estado'] == 'Completada'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-all"></i> Recibida</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo $compra['estado']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <?php if($compra['estado'] == 'Pendiente'): ?>
                                    <a href="<?php echo BASE_URL; ?>compra/recepcion/<?php echo $compra['id']; ?>" class="btn btn-sm btn-success" title="Registrar Ingreso Físico">
                                        <i class="bi bi-box-seam"></i> Recibir
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?php echo BASE_URL; ?>compra/devolver/<?php echo $compra['id']; ?>" class="btn btn-sm btn-outline-warning" title="Devolver a Proveedor">
                                    <i class="bi bi-arrow-return-left"></i> Devolver
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
