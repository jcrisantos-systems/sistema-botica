<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Realizando Conteo Físico</h1>
            <div class="page-subtitle">Auditoría #<?php echo $data['auditoria']['id']; ?> - Iniciada el <?php echo date('d/m/Y H:i', strtotime($data['auditoria']['fecha_inicio'])); ?></div>
        </div>
        <a href="<?php echo BASE_URL; ?>inventariofisico/index" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver sin Guardar
        </a>
    </div>

    <div class="alert alert-info border-info" style="background: rgba(13, 202, 240, 0.1); color: #0dcaf0;">
        <i class="bi bi-info-circle-fill"></i> <strong>Instrucciones:</strong> Ingrese la cantidad real que observa físicamente para cada lote. El sistema calculará la diferencia y ajustará el inventario automáticamente al presionar "Finalizar".
    </div>

    <form action="<?php echo BASE_URL; ?>inventariofisico/finalizar" method="POST" id="formConteo">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="id_auditoria" value="<?php echo $data['auditoria']['id']; ?>">

        <div class="card-metric p-4">
            <div class="table-responsive">
                <table class="table-custom" id="tablaConteo">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Lote</th>
                            <th>Vencimiento</th>
                            <th class="text-center">Stock Sistema</th>
                            <th class="text-center" width="150">Stock Físico (Contado)</th>
                            <th class="text-center">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['detalles'] as $det): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($det['nombre_comercial']); ?></strong></td>
                            <td><span class="badge bg-dark border border-secondary"><?php echo htmlspecialchars($det['codigo_lote']); ?></span></td>
                            <td style="font-size: 13px; color: var(--text-secondary);"><?php echo date('d/m/Y', strtotime($det['fecha_vencimiento'])); ?></td>
                            <td class="text-center" style="font-size: 16px; font-weight: bold;"><?php echo $det['stock_sistema']; ?></td>
                            <td>
                                <input type="number" 
                                       name="conteo[<?php echo $det['id_lote']; ?>]" 
                                       class="form-control-custom text-center stock-input" 
                                       value="<?php echo $det['stock_sistema']; ?>" 
                                       min="0" 
                                       data-sistema="<?php echo $det['stock_sistema']; ?>"
                                       oninput="calcularDiferencia(this)">
                            </td>
                            <td class="text-center diferencia-celda" style="font-weight: bold; font-size: 16px;">
                                0
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4 pt-4 border-top border-secondary">
                <button type="button" class="btn btn-danger me-2" onclick="confirmarCancelacion()">
                    <i class="bi bi-x-circle"></i> Cancelar Auditoría
                </button>
                <button type="submit" class="btn-primary-custom" style="width: 250px; font-size: 16px; padding: 15px;">
                    <i class="bi bi-check-all"></i> Finalizar y Ajustar Stock
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function calcularDiferencia(input) {
    let fisico = parseInt(input.value) || 0;
    let sistema = parseInt(input.getAttribute('data-sistema'));
    let dif = fisico - sistema;
    
    let celda = input.closest('tr').querySelector('.diferencia-celda');
    celda.innerText = (dif > 0 ? '+' : '') + dif;
    
    if(dif > 0) {
        celda.style.color = '#2ecc71'; // Sobrante
    } else if(dif < 0) {
        celda.style.color = '#e74c3c'; // Faltante
    } else {
        celda.style.color = '#fff';
    }
}

function confirmarCancelacion() {
    if(confirm('¿Está seguro de cancelar esta auditoría? Los datos no se guardarán y no se hará ningún ajuste.')) {
        window.location.href = '<?php echo BASE_URL; ?>inventariofisico/index';
    }
}

// Inicializar diferencias
document.querySelectorAll('.stock-input').forEach(i => calcularDiferencia(i));
</script>
