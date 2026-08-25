<div class="page-content">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>compra/index" style="color: var(--text-secondary); text-decoration: none; font-size: 14px;">
            <i class="bi bi-arrow-left"></i> Historial de Compras
        </a>
        <h1 class="page-title mt-2"><?php echo htmlspecialchars($data['title']); ?></h1>
    </div>

    <!-- Creamos array de productos en formato JSON literal de JS para el Autocomplete manual -->
    <script>
        const productosData = <?php echo json_encode($data['productos']); ?>;
    </script>

    <form action="<?php echo BASE_URL; ?>compra/save" method="POST" id="formCompra">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <!-- CABECERA -->
        <div class="card-metric mb-4">
            <h5 class="section-title">Datos del Documento y Proveedor</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                    <select class="form-control-custom" name="id_proveedor" required>
                        <option value="">Selecciona Proveedor...</option>
                        <?php foreach($data['proveedores'] as $prov): ?>
                        <option value="<?php echo $prov['id']; ?>"><?php echo htmlspecialchars($prov['ruc'] . ' - ' . $prov['razon_social']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Comprobante <span class="text-danger">*</span></label>
                    <select class="form-control-custom" name="tipo_comprobante" required>
                        <option value="Factura">Factura</option>
                        <option value="Boleta">Boleta</option>
                        <option value="Guia Remision">Guía de Remisión</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Serie (F001)</label>
                    <input type="text" class="form-control-custom" name="serie_comprobante" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">N° de Documento</label>
                    <input type="text" class="form-control-custom" name="num_comprobante" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Emisión</label>
                    <input type="date" class="form-control-custom" name="fecha_compra" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado de la Compra</label>
                    <select class="form-control-custom" name="estado" id="selEstado" onchange="actualizarInfoEstado()" required>
                        <option value="Completada" selected>Completada (Recibida)</option>
                        <option value="Pendiente">Pendiente (Borrador)</option>
                    </select>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12 form-check ms-1">
                    <input type="checkbox" class="form-check-input" id="act_precio" name="actualizar_precio" value="1" checked>
                    <label class="form-check-label" for="act_precio" style="color: var(--accent-primary); font-size: 13px;">
                        Actualizar automáticamente el "Precio Compra" en el catálogo general si cambió para estos productos.
                    </label>
                </div>
            </div>
        </div>

        <!-- DETALLE MULTILINEA -->
        <div class="card-metric mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="section-title" style="margin: 0; padding-bottom: 0; border-bottom: none;">Detalle de Productos a Ingresar (Lotes)</h5>
                <button type="button" class="btn btn-sm" style="background-color: var(--success-bg); color: var(--accent-primary); border:none;" onclick="agregarFila()">
                    <i class="bi bi-plus-circle"></i> Añadir Ítem
                </button>
            </div>
            
            <div class="table-responsive" style="overflow-x: visible;">
                <table class="table-custom" id="tablaDetalles">
                    <thead>
                        <tr>
                            <th width="28%">Búsqueda de Producto</th>
                            <th width="12%">Lote/Lote</th>
                            <th width="14%">Vencimiento</th>
                            <th width="10%">Cant. (Unid)</th>
                            <th width="12%">Precio Comp. (S/)</th>
                            <th width="14%">Subtotal (S/)</th>
                            <th width="10%" class="text-center">X</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyDetalles">
                        <!-- Filas dinamicas se inyectan aqui -->
                    </tbody>
                </table>
            </div>

            <!-- TOTALES -->
            <div class="row mt-4 align-items-center">
                <div class="col-md-8 text-secondary" style="font-size: 13px;">
                    <i class="bi bi-info-circle"></i> <span id="infoEstado">Atención: Esto generará y activará el stock con el respectivo código Lote FEFO inmediatamente.</span>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color: var(--text-secondary); font-size: 14px;">Subtotal gravado:</span>
                        <span style="font-weight: 600;" id="spSubtotalGlobal">S/ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-bottom border-secondary pb-2">
                        <span style="color: var(--text-secondary); font-size: 14px;">IGV (Reverencial/Neto):</span>
                        <span style="font-weight: 600;" id="spIgvGlobal">S/ 0.00</span>
                        <input type="hidden" name="impuesto" id="fiIgv" value="0.00">
                    </div>
                    <div class="d-flex justify-content-between">
                        <span style="color: var(--text-primary); font-size: 18px; font-weight: 700;">Total Compra:</span>
                        <span style="font-weight: 700; font-size: 18px; color: var(--accent-primary);" id="spTotalGlobal">S/ 0.00</span>
                        <input type="hidden" name="total_compra" id="fiTotal" value="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn-primary-custom" id="btnSubmit" style="width: 280px; font-size: 16px; padding: 15px;">
                <i class="bi bi-check-circle"></i> Confirmar y Generar Lotes
            </button>
        </div>
    </form>
</div>

<script>
// Array options cache for all products (id => name)
let prodOptions = '<option value="">Buscar producto...</option>';
productosData.forEach(p => {
    prodOptions += `<option value="${p.id}" data-precio="${p.precio_compra}">${p.codigo_barras || ''} | ${p.nombre_comercial} (${p.unidad_medida})</option>`;
});

function actualizarInfoEstado() {
    let estado = document.getElementById('selEstado').value;
    let info = document.getElementById('infoEstado');
    let btn = document.getElementById('btnSubmit');
    let chkPrecio = document.getElementById('act_precio');

    if(estado === 'Pendiente') {
        info.innerHTML = '<strong>Modo Borrador:</strong> La compra se guardará pero <u>NO se cargará stock</u> ni se crearán lotes hasta que marque la recepción física.';
        btn.innerHTML = '<i class="bi bi-save"></i> Guardar como Pendiente';
        btn.style.background = 'linear-gradient(135deg, #f39c12 0%, #e67e22 100%)';
        chkPrecio.disabled = true;
        chkPrecio.checked = false;
    } else {
        info.innerHTML = 'Atención: Esto generará y activará el stock con el respectivo código Lote FEFO inmediatamente.';
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Confirmar y Generar Lotes';
        btn.style.background = 'var(--accent-primary)';
        chkPrecio.disabled = false;
        chkPrecio.checked = true;
    }
}

let rowIndex = 0;

function agregarFila() {
    rowIndex++;
    let tr = document.createElement('tr');
    tr.id = 'fila_' + rowIndex;
    tr.innerHTML = `
        <td>
            <select class="form-control-custom prod-select" name="producto_id[]" style="width:100%; font-size:12px;" onchange="seleccionarProducto(this, ${rowIndex})" required>
                ${prodOptions}
            </select>
        </td>
        <td><input type="text" class="form-control-custom" name="lote[]" placeholder="EJ: L-123" required></td>
        <td><input type="date" class="form-control-custom" name="vencimiento[]" required></td>
        <td><input type="number" class="form-control-custom fila-cant" name="cantidad[]" value="1" min="1" oninput="calcularFila(${rowIndex})" required></td>
        <td><input type="number" class="form-control-custom fila-precio" name="precio_c_unitario[]" step="0.01" value="0.00" oninput="calcularFila(${rowIndex})" required></td>
        <td><input type="number" class="form-control-custom fila-subtotal border-0" style="background-color: var(--bg-dark); color: var(--text-primary); font-weight:700;" name="subtotal[]" value="0.00" readonly></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="quitarFila(${rowIndex})"><i class="bi bi-trash"></i></button></td>
    `;
    document.getElementById('tbodyDetalles').appendChild(tr);
}

function seleccionarProducto(selectObj, index) {
    let selectedOption = selectObj.options[selectObj.selectedIndex];
    let precioSugerido = selectedOption.getAttribute('data-precio') || 0;
    let tr = document.getElementById('fila_' + index);
    if(tr) {
        let precioInput = tr.querySelector('.fila-precio');
        precioInput.value = parseFloat(precioSugerido).toFixed(2);
        calcularFila(index);
    }
}

function calcularFila(index) {
    let tr = document.getElementById('fila_' + index);
    if(tr) {
        let cant = parseFloat(tr.querySelector('.fila-cant').value) || 0;
        let precio = parseFloat(tr.querySelector('.fila-precio').value) || 0;
        let subtotal = cant * precio;
        tr.querySelector('.fila-subtotal').value = subtotal.toFixed(2);
        calcularTotales();
    }
}

function quitarFila(index) {
    let tr = document.getElementById('fila_' + index);
    if(tr) {
        tr.remove();
        calcularTotales();
    }
}

function calcularTotales() {
    let sum = 0;
    document.querySelectorAll('.fila-subtotal').forEach(input => {
        sum += parseFloat(input.value) || 0;
    });
    
    // Asumimos un IGV del 18% para el ejemplo o lo leemos.
    // En las boticas peruanas muchas facturas incluyen el IGV en el unitario y solo se desglosa contablemente.
    // Usaremos logic net total = subtotal for simplicity unless user specifies raw total inputs
    let totalC = sum;
    let igvC = sum - (sum / 1.18);
    let subNeto = sum - igvC;

    document.getElementById('spSubtotalGlobal').innerText = 'S/ ' + subNeto.toFixed(2);
    document.getElementById('spIgvGlobal').innerText = 'S/ ' + igvC.toFixed(2);
    document.getElementById('spTotalGlobal').innerText = 'S/ ' + totalC.toFixed(2);
    
    document.getElementById('fiIgv').value = igvC.toFixed(2);
    document.getElementById('fiTotal').value = totalC.toFixed(2);
}

// Iniciar con 1 fila
window.onload = function() {
    agregarFila();
};
</script>
