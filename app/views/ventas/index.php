<div class="page-content">
    <div class="mb-4">
        <h1 class="page-title">Boletas y Facturas Emitidas</h1>
        <div class="page-subtitle">Historial de tickets y ventas procesadas en caja.</div>
    </div>
    
    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th width="120">Fecha/Hora</th>
                        <th>Cliente</th>
                        <th>Cajero</th>
                        <th class="text-center">Comprobante</th>
                        <th class="text-center">Método Pago</th>
                        <th class="text-end">Monto Cobrado</th>
                        <th width="80" class="text-center">Ver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['ventas'])): ?>
                    <tr><td colspan="7" class="text-center text-muted">Aún no hay ventas efectuadas.</td></tr>
                    <?php else: ?>
                    <?php foreach($data['ventas'] as $v): ?>
                    <tr>
                        <td style="color:var(--text-secondary); font-family:monospace;">
                            <?php echo date('d/m/Y', strtotime($v['fecha_venta'])) . "<br><small>" . date('H:i', strtotime($v['fecha_venta'])) . "</small>"; ?>
                        </td>
                        <td style="font-weight:700; color:var(--text-primary); font-size: 13px;">
                            <?php echo htmlspecialchars($v['cliente']); ?>
                        </td>
                        <td style="color:var(--text-secondary); font-size: 13px;">
                            <?php echo explode(' ', $v['cajero'])[0]; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($v['estado'] == 'Anulada'): ?>
                                <span class="badge bg-danger shadow-sm"><i class="bi bi-x-circle"></i> Anulada</span><br>
                                <strike style="font-size:10px; color:var(--text-secondary);"><?php echo htmlspecialchars($v['num_comprobante']); ?></strike>
                            <?php else: ?>
                                <span style="border: 1px solid var(--accent-primary); color: var(--accent-primary); padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                                    <?php echo htmlspecialchars($v['num_comprobante']); ?>
                                </span><br>
                                <span style="font-size:10px;"><?php echo htmlspecialchars($v['tipo_comprobante']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php 
                                $bColor = 'bg-secondary';
                                if($v['metodo_pago'] == 'Yape' || $v['metodo_pago'] == 'Plin') $bColor = 'bg-info text-dark';
                                else if($v['metodo_pago'] == 'Tarjeta') $bColor = 'bg-primary';
                                else $bColor = 'bg-success';
                            ?>
                            <span class="badge <?php echo $bColor; ?>"><?php echo $v['metodo_pago']; ?></span>
                        </td>
                        <td class="text-end" style="font-size: 16px; font-weight:700; color: var(--accent-primary);">
                            S/ <?php echo number_format($v['total'], 2); ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="<?php echo BASE_URL; ?>venta/ticket/<?php echo $v['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver Ticket Impresión">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <?php if ($v['estado'] != 'Anulada'): ?>
                                <form action="<?php echo BASE_URL; ?>venta/anular/<?php echo $v['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de ANULAR esta venta? El stock se devolverá al almacén de forma íntegra.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Anular Venta">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
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
