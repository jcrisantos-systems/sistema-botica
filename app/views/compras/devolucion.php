<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Generar Devolución (Nota de Crédito)</h1>
            <div class="page-subtitle">Compra #C-<?php echo str_pad($data['compra']['id'], 5, '0', STR_PAD_LEFT); ?> - Proveedor: <?php echo htmlspecialchars($data['compra']['proveedor']); ?></div>
        </div>
        <a href="<?php echo BASE_URL; ?>compra/index" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Historial
        </a>
    </div>

    <form action="<?php echo BASE_URL; ?>compra/save_devolucion" method="POST" id="formDevolucion">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="id_compra" value="<?php echo $data['compra']['id']; ?>">

        <div class="row g-4">
            <!-- Datos de la Nota de Crédito -->
            <div class="col-md-4">
                <div class="card-metric h-100 p-4">
                    <h5 class="mb-3 text-accent"><i class="bi bi-file-earmark-text"></i> Datos del Documento</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Nro. de Nota de Crédito (Proveedor)</label>
                        <input type="text" name="num_documento_prov" class="form-control-custom" placeholder="Ej. NC001-000123" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha de Devolución</label>
                        <input type="date" name="fecha_devolucion" class="form-control-custom" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Motivo de Devolución</label>
                        <textarea name="motivo" class="form-control-custom" rows="3" placeholder="Estado de los productos, vencimiento, etc..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Listado de Productos a devolver -->
            <div class="col-md-8">
                <div class="card-metric p-4">
                    <h5 class="mb-3 text-warning"><i class="bi bi-box-seam"></i> Seleccionar Productos</h5>
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Producto / Lote</th>
                                    <th class="text-center" width="100">Compreado</th>
                                    <th class="text-center" width="100">En Stock</th>
                                    <th class="text-center" width="120">Devolver</th>
                                    <th class="text-end" width="100">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['detalles'] as $i => $det): ?>
                                <tr>
                                    <td>
                                        <strong style="color:#fff;"><?php echo htmlspecialchars($det['nombre_comercial']); ?></strong><br>
                                        <small class="text-muted">Lote: <?php echo htmlspecialchars($det['codigo_lote']); ?> | Vence: <?php echo date('d/m/Y', strtotime($det['fecha_vencimiento'])); ?></small>
                                        <input type="hidden" name="producto_id[]" value="<?php echo $det['id_producto']; ?>">
                                        <input type="hidden" name="lote_id[]" value="<?php echo $det['id_lote']; ?>">
                                        <input type="hidden" name="precio_costo[]" value="<?php echo $det['precio_unitario']; ?>" class="row-costo" data-index="<?php echo $i; ?>">
                                    </td>
                                    <td class="text-center"><?php echo $det['cantidad']; ?></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $det['stock_lote_actual'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $det['stock_lote_actual']; ?> unidades
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" name="cantidad_dev[]" class="form-control-custom text-center row-qty" 
                                               value="0" min="0" max="<?php echo min($det['cantidad'], $det['stock_lote_actual']); ?>" 
                                               oninput="recalcularFila(this)" data-index="<?php echo $i; ?>"
                                               style="height:35px; border-color: var(--warning);">
                                    </td>
                                    <td class="text-end">
                                        <input type="text" name="subtotal_dev[]" class="form-control-custom border-0 bg-transparent text-end p-0 row-subtotal" 
                                               value="0.00" readonly style="color:var(--text-secondary); font-family:monospace;">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 pt-4 border-top border-secondary d-flex justify-content-between align-items-center">
                        <div class="text-start">
                            <span class="text-muted d-block">Monto Total Nota de Crédito</span>
                            <h2 class="text-accent fw-bold mb-0" id="totalNC">S/ 0.00</h2>
                            <input type="hidden" name="total_devolucion" id="fiTotal" value="0">
                        </div>
                        <button type="button" class="btn-primary-custom" style="width:auto; padding: 15px 40px;" onclick="confirmarDevolucion()">
                            <i class="bi bi-check2-circle"></i> Procesar Devolución
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function recalcularFila(input) {
    let index = input.getAttribute('data-index');
    let qty = parseInt(input.value) || 0;
    let max = parseInt(input.getAttribute('max')) || 0;

    // Validación extra por si acaso el usuario escribe algo mayor al max manual
    if(qty > max) {
        qty = max;
        input.value = max;
    }

    let costo = parseFloat(document.querySelectorAll('.row-costo')[index].value) || 0;
    let subtotal = qty * costo;
    
    document.querySelectorAll('.row-subtotal')[index].value = subtotal.toFixed(2);
    
    recalcularTotal();
}

function recalcularTotal() {
    let total = 0;
    document.querySelectorAll('.row-subtotal').forEach(el => {
        total += parseFloat(el.value) || 0;
    });
    
    document.getElementById('totalNC').innerText = 'S/ ' + total.toFixed(2);
    document.getElementById('fiTotal').value = total.toFixed(2);
}

function confirmarDevolucion() {
    let total = parseFloat(document.getElementById('fiTotal').value) || 0;
    if(total <= 0) {
        alert("Atención: Debe ingresar al menos una cantidad mayor a cero para devolver.");
        return;
    }

    if(confirm('¿Está seguro de procesar esta devolución por S/ ' + total.toFixed(2) + '? Se descontará el stock de los productos seleccionados.')) {
        document.getElementById('formDevolucion').submit();
    }
}
</script>
