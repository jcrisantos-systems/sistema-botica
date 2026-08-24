<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Recepción de Mercadería</h1>
            <div class="page-subtitle">Orden #C-<?php echo str_pad($data['compra']['id'], 5, '0', STR_PAD_LEFT); ?> - Proveedor: <?php echo htmlspecialchars($data['compra']['proveedor']); ?></div>
        </div>
        <a href="<?php echo BASE_URL; ?>compra/index" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Historial
        </a>
    </div>

    <div class="alert alert-warning border-warning" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Importante:</strong> Ingrese los datos de los lotes y fechas de vencimiento tal como figuran en el empaque físico. Al confirmar, los productos se cargarán al stock inmediatamente.
    </div>

    <form action="<?php echo BASE_URL; ?>compra/procesar_recepcion" method="POST" id="formRecepcion">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="id_compra" value="<?php echo $data['compra']['id']; ?>">

        <div class="card-metric p-4">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center" width="120">Cant. Pedida</th>
                            <th width="200">Código de Lote <span class="text-danger">*</span></th>
                            <th width="200">Fecha Vencimiento <span class="text-danger">*</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['detalles'] as $det): ?>
                        <tr>
                            <td>
                                <strong style="color: #fff;"><?php echo htmlspecialchars($det['nombre_comercial']); ?></strong>
                                <input type="hidden" name="detalle_id[]" value="<?php echo $det['id']; ?>">
                            </td>
                            <td class="text-center" style="font-size: 16px; font-weight: bold;">
                                <?php echo $det['cantidad']; ?>
                            </td>
                            <td>
                                <input type="text" name="lote[]" class="form-control-custom" placeholder="Lote / Batch" required>
                            </td>
                            <td>
                                <input type="date" name="vencimiento[]" class="form-control-custom" required>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4 pt-4 border-top border-secondary">
                <button type="submit" class="btn-primary-custom" style="width: 300px; font-size: 16px; padding: 15px;">
                    <i class="bi bi-box-seam"></i> Confirmar Recepción Física
                </button>
            </div>
        </div>
    </form>
</div>
