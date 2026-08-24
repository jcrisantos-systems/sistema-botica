<div class="page-content">
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="page-title mb-1">Kardex General</h1>
            <div class="page-subtitle">Registro físico de entradas y salidas del catálogo.</div>
        </div>
        <button type="button" class="btn btn-success fw-bold p-2 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEntrada">
            <i class="bi bi-box-arrow-in-down-right"></i> Añadir Lote/Stock Manual
        </button>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Filtro Específico -->
    <div class="card-metric mb-3 p-3">
        <form action="<?php echo BASE_URL; ?>inventario/kardex" method="GET" class="row align-items-center g-3">
            <div class="col-md-6">
                <label style="font-size: 12px; color: var(--text-secondary);">Filtrar Historial por Producto</label>
                <select class="form-control-custom" name="producto" onchange="this.form.submit()">
                    <option value="">TODOS LOS PRODUCTOS</option>
                    <?php foreach($data['productos'] as $pd): ?>
                    <?php $sel = ($data['filtro_producto'] == $pd['id']) ? 'selected' : ''; ?>
                    <option value="<?php echo $pd['id']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($pd['nombre_comercial'] . ' (' . $pd['unidad_medida'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <?php if($data['filtro_producto']): ?>
                    <a href="<?php echo BASE_URL; ?>inventario/kardex" class="btn btn-outline-danger w-100">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="width: 100%; font-size: 13px;">
                <thead>
                    <tr>
                        <th width="140">Fecha/Hora</th>
                        <th>Producto Afectado</th>
                        <th>Motivo Operación</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Saldo General</th>
                        <th class="text-center">Resp.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['movimientos'])): ?>
                    <tr><td colspan="7" class="text-center text-muted">No existen movimientos registrados para este filtro.</td></tr>
                    <?php else: ?>
                    <?php foreach($data['movimientos'] as $mov): 
                        $tColor = $mov['tipo_movimiento'] == 'ENTRADA' ? 'var(--accent-primary)' : 'var(--danger)'; 
                        $tSigno = $mov['tipo_movimiento'] == 'ENTRADA' ? '+' : '-';
                    ?>
                    <tr>
                        <td style="color:var(--text-secondary); font-family:monospace;">
                            <?php echo date('d/m/Y H:i', strtotime($mov['fecha'])); ?>
                        </td>
                        <td style="font-weight:700; color:var(--text-primary);">
                            <?php echo htmlspecialchars($mov['nombre_comercial']); ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($mov['motivo']); ?>
                        </td>
                        <td class="text-center">
                            <span style="border: 1px solid <?php echo $tColor; ?>; color: <?php echo $tColor; ?>; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                <?php echo $mov['tipo_movimiento']; ?>
                            </span>
                        </td>
                        <td class="text-center" style="font-weight:700; color: <?php echo $tColor; ?>;">
                            <?php echo $tSigno . $mov['cantidad']; ?>
                        </td>
                        <td class="text-end" style="font-weight:700; font-size: 15px;">
                            <?php echo $mov['saldo_actual']; ?>
                        </td>
                        <td class="text-center text-secondary">
                           <?php echo explode(' ', $mov['usuario'])[0]; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Entrada Manual -->
<div class="modal fade" id="modalEntrada" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-box-arrow-in-down-right"></i> Ajuste: Ingreso de Stock / Nuevo Lote</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>inventario/entrada_manual" method="POST">
                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary" style="font-size:13px;">Producto a afectar</label>
                        <select class="form-select form-control-custom" name="id_producto" required>
                            <option value="">Seleccione el producto...</option>
                            <?php foreach($data['productos'] as $pd): ?>
                            <option value="<?php echo $pd['id']; ?>"><?php echo htmlspecialchars($pd['nombre_comercial'] . ' (U.M: ' . $pd['unidad_medida'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size:13px;">Cant. (Unds Mínimas)</label>
                            <input type="number" class="form-control" name="cantidad" min="1" required placeholder="Ej. 100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size:13px;">Código Lote Físico</label>
                            <input type="text" class="form-control" name="lote" placeholder="Dejar en blanco si no aplica">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size:13px;">Fecha Vencimiento (Lote)</label>
                            <input type="date" class="form-control" name="fecha_vencimiento">
                            <small class="text-muted" style="font-size:11px;">Opcional, requiere lote.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary" style="font-size:13px;">Motivo Operación</label>
                            <input type="text" class="form-control" name="motivo" required placeholder="Inventario Inicial, Sobrante...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer pb-3 pt-3 border-top-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">Procesar Ingreso al Kardex</button>
                </div>
            </form>
        </div>
    </div>
</div>
