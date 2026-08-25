<div class="page-content">
    <div class="mb-4">
        <h1 class="page-title">Historial de Cajas (Arqueos)</h1>
        <div class="page-subtitle">Consulta los turnos de caja abiertos y cerrados por fecha.</div>
    </div>

    <!-- Filtros -->
    <div class="card-metric mb-4 p-3">
        <form method="GET" action="<?php echo BASE_URL; ?>caja/index" class="row align-items-end g-3">
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control-custom" value="<?php echo $data['fecha_inicio']; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:12px;">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control-custom" value="<?php echo $data['fecha_fin']; ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-primary-custom" style="width:100%;">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Nº Arqueo</th>
                        <th>Cajero / Usuario</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Inicial (S/)</th>
                        <th>Ingresos Efectivo</th>
                        <th>Tran/Tarj</th>
                        <th>Dif.</th>
                        <th>Estado</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                    <tbody>
                        <?php foreach ($data['historial'] as $c): ?>
                        <tr>
                            <td><strong><?php echo $c['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']); ?></td>
                            <td><?php echo date('d/m H:i', strtotime($c['fecha_apertura'])); ?></td>
                            <td><?php echo $c['fecha_cierre'] ? date('d/m H:i', strtotime($c['fecha_cierre'])) : '-'; ?></td>
                            <td style="color:var(--accent-primary); font-weight:700;"><?php echo $c['monto_inicial']; ?></td>
                            <td><?php echo $c['ingresos_efectivo']; ?></td>
                            <td><?php echo $c['ingresos_transferencia']; ?></td>
                            <td>
                                <?php if($c['estado'] == 0): ?>
                                    <?php if($c['diferencia'] > 0): ?>
                                        <span class="text-success">+<?php echo $c['diferencia']; ?></span>
                                    <?php elseif($c['diferencia'] < 0): ?>
                                        <span class="text-danger"><?php echo $c['diferencia']; ?></span>
                                    <?php else: ?>
                                        <span class="text-secondary">0.00</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($c['estado'] == 1): ?>
                                    <span class="badge bg-success"><i class="bi bi-circle-fill" style="font-size: 8px;"></i> ABIERTA</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">CERRADA</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($c['estado'] == 0): ?>
                                <a href="<?php echo BASE_URL; ?>caja/ticket_arqueo/<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-dark" target="_blank" title="Imprimir Ticket">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data['historial'])): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No hay registros de caja para las fechas seleccionadas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
    </div>
</div>
