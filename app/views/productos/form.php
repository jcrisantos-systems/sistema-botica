<div class="page-content">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>producto/index" style="color: var(--text-secondary); text-decoration: none; font-size: 14px;">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
        <h1 class="page-title mt-2"><?php echo htmlspecialchars($data['title']); ?></h1>
    </div>

    <?php $p = $data['producto']; ?>

    <form action="<?php echo BASE_URL; ?>producto/save" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="id" value="<?php echo $p ? $p['id'] : ''; ?>">
        
        <div class="row g-4">
            <!-- Izquierda: Datos Principales -->
            <div class="col-md-8">
                <div class="card-metric">
                    <h5 style="color: #fff; font-size: 16px; margin-bottom: 20px;">Información Principal</h5>
                    <div class="row g-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Código de Barras</label>
                            <input type="text" class="form-control-custom" name="codigo_barras" value="<?php echo $p ? htmlspecialchars($p['codigo_barras']) : ''; ?>" placeholder="Escanear o digitar...">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Nombre Comercial <span class="text-danger">*</span></label>
                            <input type="text" class="form-control-custom" name="nombre_comercial" value="<?php echo $p ? htmlspecialchars($p['nombre_comercial']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Nombre Genérico <span class="text-danger">*</span></label>
                            <input type="text" class="form-control-custom" name="nombre_generico" value="<?php echo $p ? htmlspecialchars($p['nombre_generico']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Concentración</label>
                            <input type="text" class="form-control-custom" name="concentracion" value="<?php echo $p ? htmlspecialchars($p['concentracion']) : ''; ?>" placeholder="Ej: 500mg">
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label class="form-label">Forma Farmacéutica</label>
                            <select class="form-control-custom" name="forma_farmaceutica">
                                <option value="">Seleccionar...</option>
                                <?php 
                                $formas = ['Tableta', 'Cápsula', 'Jarabe', 'Suspensión', 'Ampolla', 'Crema', 'Gel', 'Inyectable', 'Gotas'];
                                foreach($formas as $f): 
                                    $sel = ($p && $p['forma_farmaceutica'] == $f) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $f; ?>" <?php echo $sel; ?>><?php echo $f; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Unidad de Medida</label>
                            <select class="form-control-custom" name="unidad_medida">
                                <?php 
                                $ums = ['Unidad', 'Caja', 'Blister', 'Frasco', 'Tubo'];
                                foreach($ums as $u): 
                                    $sel = ($p && $p['unidad_medida'] == $u) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $u; ?>" <?php echo $sel; ?>><?php echo $u; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label class="form-label">Laboratorio</label>
                            <select class="form-control-custom" name="id_laboratorio">
                                <option value="">Seleccionar...</option>
                                <?php foreach($data['laboratorios'] as $lab): 
                                    $sel = ($p && $p['id_laboratorio'] == $lab['id']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $lab['id']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($lab['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Categoría</label>
                            <select class="form-control-custom" name="id_categoria">
                                <option value="">Seleccionar...</option>
                                <?php foreach($data['categorias'] as $cat): 
                                    $sel = ($p && $p['id_categoria'] == $cat['id']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($cat['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-12 form-group form-check mt-2 ms-1">
                            <input type="checkbox" class="form-check-input" id="req_receta" name="requiere_receta" value="1" <?php echo ($p && $p['requiere_receta'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="req_receta" style="color: var(--danger); font-size: 13px;">Requiere Receta Médica Obligatoria (Controlado)</label>
                        </div>
                    </div>
                </div>

                <!-- Fraccionamiento Inteligente -->
                <div class="card-metric mt-4">
                    <h5 style="color: #fff; font-size: 16px; margin-bottom: 20px;">
                        <i class="bi bi-box-seam"></i> Venta Fraccionada
                    </h5>
                    <div class="form-group form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="fraccionable" name="fraccionable" value="1" <?php echo ($p && isset($p['fraccionable']) && $p['fraccionable'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" style="color:var(--text-primary);" for="fraccionable">Este producto se vende por fracciones (Ej. por Blíster, por Pastilla)</label>
                    </div>
                    
                    <div class="row g-3" id="fraccion_config" style="display: none; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid var(--border-color);">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Unidades por Caja <span class="text-danger">*</span></label>
                            <input type="number" class="form-control-custom" name="unidades_por_caja" id="uCaja" value="<?php echo ($p && isset($p['unidades_por_caja'])) ? $p['unidades_por_caja'] : '1'; ?>" min="1">
                            <small style="color:var(--text-secondary); font-size: 11px;">Ej: 100 pastillas en la caja.</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Nombre Fracción <span class="text-danger">*</span></label>
                            <select class="form-control-custom" name="unidad_fraccion">
                                <option value="">Seleccionar...</option>
                                <?php 
                                $ufracs = ['Pastilla', 'Sobre', 'Ampolla', 'Blister'];
                                foreach($ufracs as $uf): 
                                    $sel = ($p && isset($p['unidad_fraccion']) && $p['unidad_fraccion'] == $uf) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $uf; ?>" <?php echo $sel; ?>><?php echo $uf; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Precio x Fracción (S/)</label>
                            <input type="number" step="0.01" class="form-control-custom text-warning font-weight-bold" name="precio_fraccion" id="pFraccion" value="<?php echo ($p && isset($p['precio_fraccion'])) ? $p['precio_fraccion'] : '0.00'; ?>">
                            <small style="color:var(--text-secondary); font-size: 11px;">Suele ser más caro que el proporcional.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Derecha: Finanzas e Inventario -->
            <div class="col-md-4">
                <div class="card-metric mb-4">
                    <h5 style="color: #fff; font-size: 16px; margin-bottom: 20px;">Precios y Márgenes</h5>
                    
                    <div class="form-group">
                        <label class="form-label">Precio Compra (S/)</label>
                        <input type="number" step="0.01" class="form-control-custom calc-in" name="precio_compra" id="pCompra" value="<?php echo $p ? $p['precio_compra'] : '0.00'; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Margen de Ganancia (%)</label>
                        <div class="input-group" style="border-radius:10px; overflow:hidden;">
                            <input type="number" step="0.01" class="form-control-custom calc-in" name="margen_ganancia" id="pMargen" value="<?php echo $p ? $p['margen_ganancia'] : '40.00'; ?>" required style="border-radius: 10px 0 0 10px;">
                            <span class="input-group-text" style="background-color: var(--border-color); border:none; color:#fff;">%</span>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label" style="color: var(--accent-primary);">Precio Venta Sugerido (S/)</label>
                        <input type="number" step="0.01" class="form-control-custom" name="precio_venta" id="pVenta" value="<?php echo $p ? $p['precio_venta'] : '0.00'; ?>" style="border-color: var(--accent-primary); font-size: 18px; font-weight: 700;" required>
                    </div>
                </div>

                <div class="card-metric">
                    <h5 style="color: #fff; font-size: 16px; margin-bottom: 20px;">Configuración de Stock</h5>
                    <div class="form-group">
                        <label class="form-label">Alerta de Stock Mínimo</label>
                        <input type="number" class="form-control-custom" name="stock_minimo" value="<?php echo $p ? $p['stock_minimo'] : '10'; ?>" required>
                        <small style="color:var(--text-secondary); font-size: 11px;">El sistema avisará cuando llegue a esta cantidad.</small>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary-custom mt-4 d-flex justify-content-center align-items-center gap-2">
                    <i class="bi bi-save"></i> Guardar Producto
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Algoritmo de cálculo de margen (como solicitado por el usuario)
const cCompra = document.getElementById('pCompra');
const cMargen = document.getElementById('pMargen');
const cVenta = document.getElementById('pVenta');

function calcVenta() {
    let compra = parseFloat(cCompra.value) || 0;
    let margen = parseFloat(cMargen.value) || 0;
    let venta = compra + (compra * (margen / 100));
    cVenta.value = venta.toFixed(2);
}

function calcMargen() {
    let compra = parseFloat(cCompra.value) || 0;
    let venta = parseFloat(cVenta.value) || 0;
    if (compra > 0) {
        let margen = ((venta / compra) - 1) * 100;
        cMargen.value = margen.toFixed(2);
    }
}

// Eventos
cCompra.addEventListener('input', calcVenta);
cMargen.addEventListener('input', calcVenta);
cVenta.addEventListener('input', calcMargen);

// Fraccionamiento toggle
const chkFraccion = document.getElementById('fraccionable');
const panelFraccion = document.getElementById('fraccion_config');
function toggleFraccion() {
    if(chkFraccion.checked) {
        panelFraccion.style.display = 'flex';
    } else {
        panelFraccion.style.display = 'none';
        document.getElementById('uCaja').value = '1';
    }
}
chkFraccion.addEventListener('change', toggleFraccion);
toggleFraccion(); // init
</script>
