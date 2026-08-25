<style>
/* Estilos extra para la experiencia POS full screen */
body { overflow-x: hidden; }
.pos-layout { display: flex; height: calc(100vh - 80px); gap: 20px; }
.pos-left { flex: 0 0 65%; display: flex; flex-direction: column; }
.pos-right { flex: 0 0 calc(35% - 20px); display: flex; flex-direction: column; }
.pos-cart { flex-grow: 1; overflow-y: auto; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-color); padding: 15px;}
.pos-totals { background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-color); padding: 20px; margin-top: 15px; }

/* Carrito Table */
.tr-cart { border-bottom: 1px solid var(--border-color); }
.tr-cart td { padding: 12px 5px; vertical-align: middle; }
.qty-btn { background: var(--bg-dark); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 5px; width: 30px; height: 30px; display: inline-flex; justify-content: center; align-items: center; cursor: pointer; font-weight: bold; font-size: 18px; }
.qty-btn:hover { background: var(--accent-primary); color: #fff; border-color: var(--accent-primary); }
.qty-input { width: 50px; text-align: center; background: transparent; border: none; color: var(--text-primary); font-weight: bold;}

/* Pay Button */
.btn-pay { background: linear-gradient(135deg, var(--accent-primary) 0%, var(--success) 100%); width: 100%; color: #fff; font-weight: 800; font-size: 22px; padding: 20px; border-radius: 12px; border: none; cursor: pointer; transition: transform 0.2s; box-shadow: 0 4px 15px var(--accent-light); }
.btn-pay:hover { transform: translateY(-2px); }

/* Catalogo Buscar */
.pos-catalog { flex-grow: 1; overflow-y: auto; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-color); padding: 15px; }
.item-card { background: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; margin-bottom: 10px; cursor: pointer; transition: all 0.2s; display: flex; justify-content: space-between; align-items: center; }
.item-card:hover { border-color: var(--accent-primary); background: var(--accent-light); }
.item-card.disabled { opacity: 0.5; pointer-events: none; }
</style>

<div class="pos-layout">
    <!-- LADO IZQUIERDO: CARRITO DE COMPRA -->
    <div class="pos-left">
        <!-- Notificaciones del POS: ahora renderizadas por JS a partir de la respuesta AJAX de venta/save -->
        <div id="posAlertBox"></div>

        <form action="<?php echo BASE_URL; ?>venta/save" method="POST" id="formVenta" style="display:flex; flex-direction:column; height: 100%;">
            <input type="hidden" name="csrf_token" id="csrfToken" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <!-- Header Carrito: Seleccion de Cliente -->
            <div class="d-flex gap-3 mb-3">
                <div class="flex-grow-1">
                    <select class="form-control-custom" name="id_cliente" required style="font-size: 16px; font-weight: 600;">
                        <?php foreach($data['clientes'] as $cli): ?>
                            <option value="<?php echo $cli['id']; ?>" data-puntos="<?php echo $cli['puntos_acumulados'] ?? 0; ?>"><?php echo htmlspecialchars($cli['num_documento'] . ' - ' . $cli['nombres']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="puntosBlock" class="mt-1" style="display:none; font-size:12px; font-weight:600; color: var(--accent-primary);">
                        <i class="bi bi-star-fill text-warning"></i> Puntos Disponibles: <span id="lblPuntos">0</span> pts.
                    </div>
                </div>
                <div style="width: 200px;">
                    <select class="form-control-custom" name="tipo_comprobante" required>
                        <option value="Ticket">Ticket de Venta</option>
                        <option value="Boleta">Boleta Eléctronica</option>
                        <option value="Factura">Factura Eléctronica</option>
                    </select>
                </div>
            </div>

            <!-- Area de lista de productos (Carrito) -->
            <div class="pos-cart" id="cartContainer">
                <table style="width: 100%; color: var(--text-primary);">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); color:var(--text-secondary); font-size:12px; text-transform:uppercase;">
                            <th class="pb-2">Producto</th>
                            <th class="pb-2 text-center" width="120">Cantidad</th>
                            <th class="pb-2 text-end" width="100">P. Unit</th>
                            <th class="pb-2 text-end" width="100">Subtotal</th>
                            <th class="pb-2 text-center" width="50">X</th>
                        </tr>
                    </thead>
                    <tbody id="cartItems">
                        <!-- JS Inyecta Filas -->
                    </tbody>
                </table>
                <div id="cartEmpty" class="text-center text-muted mt-5 mt-md-5 pt-5">
                    <i class="bi bi-cart" style="font-size: 48px; opacity:0.3;"></i>
                    <p class="mt-2">El carrito está vacío.<br>Busca un producto a la derecha para iniciar la venta.</p>
                </div>
            </div>

            <!-- Area de Totales y Pago -->
            <div class="pos-totals">
                <div class="row">
                    <div class="col-md-7">
                        <div class="d-flex flex-column gap-3 mb-3">
                            <label class="form-label mb-0" style="color:var(--text-secondary);">Método de Pago</label>
                            <div class="btn-group" role="group" aria-label="Metodo de pago">
                                <input type="radio" class="btn-check" name="metodo_pago" id="btnEfecti" autocomplete="off" value="Efectivo" checked>
                                <label class="btn btn-outline-success" for="btnEfecti">Efectivo</label>

                                <input type="radio" class="btn-check" name="metodo_pago" id="btnYape" autocomplete="off" value="Yape/Plin">
                                <label class="btn btn-outline-info" for="btnYape">Yape/Plin</label>

                                <input type="radio" class="btn-check" name="metodo_pago" id="btnTarj" autocomplete="off" value="Tarjeta">
                                <label class="btn btn-outline-primary" for="btnTarj">Tarjeta</label>
                            </div>
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label mb-0" style="color:var(--text-secondary); font-size:12px;">Efectivo Recibido</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: var(--bg-dark); border-color: var(--border-color); color: var(--text-primary);">S/</span>
                                    <input type="number" step="0.01" class="form-control fw-bold" style="background-color: var(--bg-dark); border-color: var(--border-color); color: var(--text-primary);" name="pago_recibido" id="inPago" placeholder="0.00" oninput="calcularVuelto()">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label mb-0" style="color:var(--text-secondary); font-size:12px;">Vuelto</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: var(--bg-dark); border-color: var(--border-color); color: var(--text-primary);">S/</span>
                                    <input type="number" class="form-control fw-bold text-warning" style="background-color: var(--bg-dark); border-color: var(--border-color);" name="vuelto_venta" id="inVuelto" value="0.00" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bloque CMP Oculto -->
                        <div id="cmpBlock" class="mt-3" style="display:none; background: rgba(220, 53, 69, 0.1); padding: 10px; border-radius: 8px; border: 1px solid rgba(220, 53, 69, 0.5);">
                            <label class="form-label mb-0" style="color:var(--danger); font-size:13px; font-weight:bold;"><i class="bi bi-file-medical"></i> CMP Médico (Requerido para controlados)</label>
                            <input type="text" class="form-control border-danger mt-1" style="background-color: var(--bg-dark); color: var(--text-primary);" name="medico_cmp" id="inCmp" placeholder="Ej. 12345">
                        </div>
                    </div>
                    
                    <div class="col-md-5 d-flex flex-column justify-content-between text-end">
                        <div>
                            <div class="d-flex justify-content-between mb-1" style="font-size:14px; color:var(--text-secondary);">
                                <span>Subtotal:</span>
                                <span id="txtSub">S/ 0.00</span>
                                <input type="hidden" id="fiSub" name="subtotal_venta" value="0">
                            </div>
                            <div class="d-flex justify-content-between mb-1" style="font-size:14px; color:var(--text-secondary);">
                                <div>
                                    <span>Descuento:</span>
                                    <button type="button" id="btnCanjear" class="btn btn-sm btn-outline-warning ms-1 py-0 px-1" style="font-size:10px; display:none;" onclick="canjearPuntos()"><i class="bi bi-star"></i> Usar Pts</button>
                                </div>
                                <div>
                                    <span>S/</span>
                                    <input type="number" step="0.01" min="0" id="inDesc" style="width: 70px; text-align:right; border: none; border-bottom: 1px solid var(--text-secondary); background: transparent; color: var(--danger); font-weight:bold; outline:none;" value="0.00" oninput="renderCarrito()">
                                    <input type="hidden" id="fiDesc" name="descuento_venta" value="0">
                                    <input type="hidden" id="fiPuso" name="puntos_usados" value="0">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-secondary" style="font-size:14px; color:var(--text-secondary);">
                                <span>IGV (<?php echo htmlspecialchars($data['igv']); ?>% ref):</span>
                                <span id="txtIgv">S/ 0.00</span>
                                <input type="hidden" id="fiIgv" name="igv_venta" value="0">
                            </div>
                            <div class="d-flex justify-content-between">
                                <span style="font-size: 20px; font-weight:700; color:var(--text-primary);">Total:</span>
                                <span style="font-size: 28px; font-weight:800; color:var(--accent-primary);" id="txtTot">S/ 0.00</span>
                                <input type="hidden" id="fiTot" name="total_venta" value="0">
                            </div>
                        </div>
                        <button type="button" class="btn-pay mt-2" id="btnCobrar" onclick="confirmarVenta()">
                            <i class="bi bi-wallet2"></i> COBRAR
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- LADO DERECHO: BUSCADOR CATALOGO -->
    <div class="pos-right">
        <div class="search-box mb-3">
            <i class="bi bi-upc-scan"></i>
            <input type="text" id="buscadorPOS" placeholder="Código de barras o Nombre..." onkeyup="filtrarCatalogo()" autofocus>
        </div>
        
        <div class="pos-catalog" id="catList">
            <!-- Renderizado de catálogo disponible -->
            <?php foreach($data['productos'] as $prod): 
                $stock = $prod['stock_actual'];
                $disabledClass = $stock <= 0 ? 'disabled' : '';
                // Escapar para JS data
                $jsonP = json_encode([
                    'id' => $prod['id'],
                    'codigo_barras' => $prod['codigo_barras'],
                    'nombre' => $prod['nombre_comercial'],
                    'precio' => $prod['precio_venta'],
                    'precio_fraccion' => isset($prod['precio_fraccion']) ? $prod['precio_fraccion'] : 0,
                    'fraccionable' => isset($prod['fraccionable']) ? $prod['fraccionable'] : 0,
                    'unidad_fraccion' => isset($prod['unidad_fraccion']) ? $prod['unidad_fraccion'] : 'Fracción',
                    'unidad_medida' => $prod['unidad_medida'] ? $prod['unidad_medida'] : 'Caja',
                    'unidades_por_caja' => isset($prod['unidades_por_caja']) ? $prod['unidades_por_caja'] : 1,
                    'stock' => $stock,
                    'requiere_receta' => $prod['requiere_receta']
                ]);
            ?>
            <div class="item-card <?php echo $disabledClass; ?>" data-id="<?php echo $prod['id']; ?>" data-busqueda="<?php echo strtolower($prod['codigo_barras'] . ' ' . $prod['nombre_comercial'] . ' ' . $prod['nombre_generico']); ?>" onclick='agregarAlCarrito(<?php echo htmlspecialchars($jsonP, ENT_QUOTES); ?>)'>
                <div style="flex-grow:1;">
                    <strong style="color:var(--text-primary); display:block; font-size: 14px;"><?php echo htmlspecialchars($prod['nombre_comercial']); ?></strong>
                    <span style="font-size: 11px; color:var(--text-secondary);"><?php echo htmlspecialchars($prod['unidad_medida'] . ' ' . $prod['concentracion']); ?> | <span data-stock-label><?php echo $stock > 0 ? "Stock U.Mín: $stock" : "<span class='text-danger'>Agotado</span>"; ?></span></span>
                </div>
                <div style="font-weight: 700; color: var(--accent-primary); font-size: 16px;">
                    S/ <?php echo number_format($prod['precio_venta'], 2); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
let carrito = {};

// Catálogo Global en JS
const catalogoGlobal = [
<?php foreach($data['productos'] as $p): ?>
    <?php echo json_encode([
        'id' => $p['id'],
        'codigo_barras' => $p['codigo_barras'],
        'nombre' => $p['nombre_comercial'],
        'precio' => $p['precio_venta'],
        'precio_fraccion' => isset($p['precio_fraccion']) ? $p['precio_fraccion'] : 0,
        'fraccionable' => isset($p['fraccionable']) ? $p['fraccionable'] : 0,
        'unidad_fraccion' => isset($p['unidad_fraccion']) ? $p['unidad_fraccion'] : 'Fracción',
        'unidad_medida' => $p['unidad_medida'] ? $p['unidad_medida'] : 'Caja',
        'unidades_por_caja' => isset($p['unidades_por_caja']) ? $p['unidades_por_caja'] : 1,
        'stock' => $p['stock_actual'],
        'requiere_receta' => $p['requiere_receta']
    ]); ?>,
<?php endforeach; ?>
];

// Fidelidad
let ratioCanje = 10; // 10 puntos = 1 Sol

document.querySelector('select[name="id_cliente"]').addEventListener('change', function() {
    evaluarClientePuntos();
});

function evaluarClientePuntos() {
    let sel = document.querySelector('select[name="id_cliente"]');
    let opt = sel.options[sel.selectedIndex];
    let pts = parseInt(opt.getAttribute('data-puntos')) || 0;
    
    if(sel.value == 1) { // Publico General
        document.getElementById('puntosBlock').style.display = 'none';
        document.getElementById('btnCanjear').style.display = 'none';
        // Reset 
        document.getElementById('fiPuso').value = 0;
    } else {
        document.getElementById('puntosBlock').style.display = 'block';
        document.getElementById('lblPuntos').innerText = pts;
        
        if(pts >= ratioCanje) {
            document.getElementById('btnCanjear').style.display = 'inline-block';
        } else {
            document.getElementById('btnCanjear').style.display = 'none';
        }
    }
    renderCarrito(); // Por si habíamos aplicado descuento por puntos, validar si cambia
}

function canjearPuntos() {
    let sel = document.querySelector('select[name="id_cliente"]');
    let pts = parseInt(sel.options[sel.selectedIndex].getAttribute('data-puntos')) || 0;
    let maxSoles = pts / ratioCanje; // EJ: 15 / 10 = 1.5 soles
    
    // Obtenemos el total sin descuento actual
    let arrCart = Object.values(carrito);
    let sum = 0;
    arrCart.forEach(i => { sum += (i.tipo_unidad == 'CAJA' ? i.precio_caja : i.precio_fraccion) * i.cantidad; });
    
    if(sum <= 0) { alert("Primero agrega productos al carrito."); return; }
    
    // Queremos usar la máxima cantidad de puntos para el total, pero no pasarnos del total
    let dsctoSoles = maxSoles;
    if(dsctoSoles > sum) dsctoSoles = sum;
    
    let puntosAUsar = Math.floor(dsctoSoles * ratioCanje);
    dsctoSoles = puntosAUsar / ratioCanje;
    
    document.getElementById('inDesc').value = dsctoSoles.toFixed(2);
    document.getElementById('fiPuso').value = puntosAUsar;
    
    renderCarrito();
}

// Inicializar select
evaluarClientePuntos();

document.getElementById("buscadorPOS").addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        let codigo = this.value.trim();
        if (codigo === '') return;
        
        let productoList = catalogoGlobal.filter(p => p.codigo_barras === codigo);
        if(productoList.length > 0) {
            let prod = productoList[0];
            if (prod.stock <= 0) {
                alert("Producto sin stock o agotado: " + prod.nombre);
                this.value = '';
                return;
            }
            agregarAlCarrito(prod);
            this.value = '';
        } else {
            // alert('Código de barras no encontrado'); // Opcional
        }
    }
});

function filtrarCatalogo() {
    let input = document.getElementById("buscadorPOS").value.toLowerCase();
    let items = document.getElementsByClassName("item-card");
    for (let i = 0; i < items.length; i++) {
        let keyword = items[i].getAttribute("data-busqueda");
        if (keyword.indexOf(input) > -1) {
            items[i].style.display = "flex";
        } else {
            items[i].style.display = "none";
        }
    }
}

function agregarAlCarrito(producto) {
    let id = producto.id;
    if(carrito[id]) {
        let stockRequerido = carrito[id].cantidad;
        if (carrito[id].tipo_unidad == 'CAJA' && carrito[id].fraccionable == 1) {
            stockRequerido = carrito[id].cantidad * carrito[id].unidades_por_caja;
        }
        let stockFuturo = stockRequerido + (carrito[id].tipo_unidad == 'CAJA' && carrito[id].fraccionable == 1 ? carrito[id].unidades_por_caja : 1);

        if(stockFuturo <= producto.stock) {
            carrito[id].cantidad++;
        } else {
            alert('¡Límite de stock original alcanzado para este producto (Stock en unidades: '+producto.stock+')!');
        }
    } else {
        carrito[id] = {
            id: producto.id,
            nombre: producto.nombre,
            precio_caja: parseFloat(producto.precio),
            precio_fraccion: parseFloat(producto.precio_fraccion),
            fraccionable: producto.fraccionable,
            unidad_medida: producto.unidad_medida,
            unidad_fraccion: producto.unidad_fraccion,
            unidades_por_caja: parseInt(producto.unidades_por_caja) || 1,
            tipo_unidad: 'CAJA', // Por defecto compra como se vende normalmente
            cantidad: 1,
            stock: parseInt(producto.stock),
            requiere_receta: producto.requiere_receta
        };
    }
    renderCarrito();
    document.getElementById("buscadorPOS").value = ''; // limpiar
    document.getElementById("buscadorPOS").focus();
}

function cambiarUnidad(id, tipo) {
    if(carrito[id]) {
        carrito[id].tipo_unidad = tipo;
        let stockRequerido = carrito[id].cantidad;
        if (tipo == 'CAJA' && carrito[id].fraccionable == 1) {
            stockRequerido = carrito[id].cantidad * carrito[id].unidades_por_caja;
        }
        if (stockRequerido > carrito[id].stock) {
            alert("No hay suficiente stock para vender en este formato (Stock disponible: " + carrito[id].stock + " unidades mínimas).");
            carrito[id].tipo_unidad = 'FRACCION';
        }
    }
    renderCarrito();
}

function modQty(id, delta) {
    if(carrito[id]) {
        let nueva = carrito[id].cantidad + delta;
        if(nueva <= 0) {
            delete carrito[id];
        } else {
            let stock_req = nueva;
            if (carrito[id].tipo_unidad == 'CAJA' && carrito[id].fraccionable == 1) {
                stock_req = nueva * carrito[id].unidades_por_caja;
            }
            if (stock_req > carrito[id].stock) {
                alert('Stock insuficiente (Disponibles: ' + carrito[id].stock + ' unidades mínimas).');
            } else {
                carrito[id].cantidad = nueva;
            }
        }
    }
    renderCarrito();
}

function removeRow(id) {
    delete carrito[id];
    renderCarrito();
}

function renderCarrito() {
    let tbody = document.getElementById('cartItems');
    let emptyMsg = document.getElementById('cartEmpty');
    tbody.innerHTML = '';
    
    let sum = 0;
    let formsHtml = ''; // Inputs ocultos
    let arrCart = Object.values(carrito);
    let requiereCmp = false;
    
    if(arrCart.length === 0) {
        emptyMsg.style.display = 'block';
    } else {
        emptyMsg.style.display = 'none';
        
        arrCart.forEach(item => {
            if(item.requiere_receta == 1) requiereCmp = true;
            
            let precioUnit = item.tipo_unidad == 'CAJA' ? item.precio_caja : item.precio_fraccion;
            let subtotal = precioUnit * item.cantidad;
            sum += subtotal;

            let comboUnidad = item.fraccionable == 1 
                ? `<select class="border-0 py-1 rounded" style="font-size:11px; background-color: var(--bg-dark); color: var(--text-primary);" onchange="cambiarUnidad(${item.id}, this.value)">
                    <option value="CAJA" ${item.tipo_unidad == 'CAJA' ? 'selected' : ''}>${item.unidad_medida}</option>
                    <option value="FRACCION" ${item.tipo_unidad == 'FRACCION' ? 'selected' : ''}>${item.unidad_fraccion}</option>
                   </select>` 
                : `<span style="font-size:11px; color:var(--text-secondary);">${item.unidad_medida}</span>`;
            
            // Fila Visual
            tbody.innerHTML += `
            <tr class="tr-cart">
                <td>
                    <strong style="color:var(--text-primary);font-size:14px;display:block;">${item.nombre}</strong>
                    ${comboUnidad}
                </td>
                <td class="text-center" style="vertical-align:top; pt-2;">
                    <div style="display:flex; justify-content:center; align-items:center; gap:5px;">
                        <span class="qty-btn" onclick="modQty(${item.id}, -1)">-</span>
                        <input type="text" readonly class="qty-input" value="${item.cantidad}">
                        <span class="qty-btn" onclick="modQty(${item.id}, 1)">+</span>
                    </div>
                </td>
                <td class="text-end" style="color:var(--text-secondary); vertical-align:top; pt-2;">${precioUnit.toFixed(2)}</td>
                <td class="text-end" style="font-weight:700; vertical-align:top; pt-2;">${subtotal.toFixed(2)}</td>
                <td class="text-center" style="vertical-align:top; pt-2;"><button type="button" class="btn btn-sm text-danger border-0 p-0" onclick="removeRow(${item.id})"><i class="bi bi-x-circle-fill fs-5"></i></button></td>
            </tr>
            `;
            
            // Inputs invisibles para el POST PHP
            formsHtml += `
                <input type="hidden" name="producto_id[]" value="${item.id}">
                <input type="hidden" name="cantidad[]" value="${item.cantidad}">
                <input type="hidden" name="precio_d[]" value="${precioUnit}">
                <input type="hidden" name="subtotal_d[]" value="${subtotal}">
                <input type="hidden" name="tipo_unidad[]" value="${item.tipo_unidad}">
            `;
        });
    }
    
    // Anexar inputs form ocultos (podemos meterlos al final de la tabla)
    let tmpDiv = document.createElement('div');
    tmpDiv.innerHTML = formsHtml;
    tbody.appendChild(tmpDiv);
    
    // Toggle CMP validation field
    if(requiereCmp) {
        document.getElementById('cmpBlock').style.display = 'block';
        document.getElementById('inCmp').setAttribute('required', 'required');
    } else {
        document.getElementById('cmpBlock').style.display = 'none';
        document.getElementById('inCmp').removeAttribute('required');
        document.getElementById('inCmp').value = '';
    }
    
    // Validar descuento
    let inDescObj = document.getElementById('inDesc');
    let descuento = parseFloat(inDescObj.value) || 0;
    if(descuento < 0) { descuento = 0; inDescObj.value = '0.00'; }
    if(descuento > sum) { descuento = sum; inDescObj.value = descuento.toFixed(2); }
    
    // Si se modifica manualmente el descuento y difiere de lo usado en puntos, reseteamos puntos usados
    let sel = document.querySelector('select[name="id_cliente"]');
    if(sel.value != 1) {
        let ptsObj = document.getElementById('fiPuso');
        let dEsperado = (parseFloat(ptsObj.value) || 0) / ratioCanje;
        if(Math.abs(dEsperado - descuento) > 0.01) {
             ptsObj.value = 0; // Se anuló el canje automático o se sobreescribió a mano
        }
    } else {
        document.getElementById('fiPuso').value = 0;
    }
    
    let totalCobrar = sum - descuento;
    
    // Totales global dinámico por config
    let igvLocal = <?php echo floatval($data['igv']); ?>;
    let factorIgv = (igvLocal / 100) + 1;
    let mIgv = totalCobrar - (totalCobrar / factorIgv);
    let mSubSec = totalCobrar - mIgv;
    
    document.getElementById('txtSub').innerText = 'S/ ' + mSubSec.toFixed(2);
    document.getElementById('txtIgv').innerText = 'S/ ' + mIgv.toFixed(2);
    document.getElementById('txtTot').innerText = 'S/ ' + totalCobrar.toFixed(2);
    
    document.getElementById('fiSub').value = mSubSec.toFixed(2);
    document.getElementById('fiIgv').value = mIgv.toFixed(2);
    document.getElementById('fiTot').value = totalCobrar.toFixed(2);
    document.getElementById('fiDesc').value = descuento.toFixed(2);
    
    calcularVuelto();
}

function calcularVuelto() {
    let total = parseFloat(document.getElementById('fiTot').value) || 0;
    let pago = parseFloat(document.getElementById('inPago').value) || 0;
    let vuelto = 0;
    if(pago >= total && pago > 0) {
        vuelto = pago - total;
    }
    document.getElementById('inVuelto').value = vuelto.toFixed(2);
}

// Yape/Plin y Tarjeta no manejan vuelto físico: el pago recibido es siempre el total exacto.
// Solo Efectivo permite ingresar un monto distinto para calcular el vuelto en caja.
document.querySelectorAll('input[name="metodo_pago"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        let inPago = document.getElementById('inPago');
        if (this.value === 'Efectivo') {
            inPago.readOnly = false;
            inPago.value = '';
            inPago.placeholder = '0.00';
        } else {
            inPago.readOnly = true;
            let total = parseFloat(document.getElementById('fiTot').value) || 0;
            inPago.value = total.toFixed(2);
        }
        calcularVuelto();
    });
});

function mostrarAlerta(tipo, mensaje, idVentaParaTicket) {
    let box = document.getElementById('posAlertBox');
    if (tipo === 'success') {
        box.innerHTML = `
            <div class="alert alert-success mt-2 mb-2 p-2 px-3" style="background-color: var(--success-bg); color: var(--accent-primary); border: 1px solid var(--accent-primary); font-weight:600; display:flex; justify-content:space-between;">
                <span><i class="bi bi-check-circle-fill"></i> ${mensaje}</span>
                ${idVentaParaTicket ? `<button class="btn btn-sm btn-outline-success border-0" onclick="window.open('<?php echo BASE_URL; ?>venta/ticket/${idVentaParaTicket}', 'Ticket', 'width=400,height=600')"><i class="bi bi-printer"></i> Imprimir Ticket</button>` : ''}
            </div>`;
    } else {
        box.innerHTML = `
            <div class="alert alert-danger mt-2 mb-2 p-2 px-3" style="background-color: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger);">
                <i class="bi bi-exclamation-triangle-fill"></i> ${mensaje}
            </div>`;
    }
}

// Refleja en el catálogo en memoria (y en las tarjetas visibles) el stock recién descontado,
// para que la siguiente venta valide contra cifras correctas sin necesidad de recargar la página.
function sincronizarStockLocal(itemsVendidos) {
    itemsVendidos.forEach(item => {
        let unidadesVendidas = (item.tipo_unidad === 'CAJA' && item.fraccionable == 1)
            ? item.cantidad * item.unidades_por_caja
            : item.cantidad;

        let prodGlobal = catalogoGlobal.find(p => p.id === item.id);
        if (prodGlobal) {
            prodGlobal.stock = Math.max(0, prodGlobal.stock - unidadesVendidas);

            let card = document.querySelector('.item-card[data-id="' + item.id + '"]');
            if (card) {
                let label = card.querySelector('[data-stock-label]');
                if (label) {
                    label.innerHTML = prodGlobal.stock > 0 ? ('Stock U.Mín: ' + prodGlobal.stock) : "<span class='text-danger'>Agotado</span>";
                }
                card.classList.toggle('disabled', prodGlobal.stock <= 0);
            }
        }
    });
}

function resetVentaTrasCobro() {
    carrito = {};
    document.getElementById('inPago').value = '';
    document.getElementById('inPago').readOnly = false;
    document.getElementById('inVuelto').value = '0.00';
    document.getElementById('inDesc').value = '0.00';
    document.getElementById('fiPuso').value = 0;
    document.getElementById('inCmp').value = '';
    document.getElementById('btnEfecti').checked = true;
    // El cliente por defecto es id=1 ("Cliente Público en General"); el <select> está
    // ordenado alfabéticamente, así que no podemos asumir que sea la primera opción.
    document.querySelector('select[name="id_cliente"]').value = '1';
    evaluarClientePuntos();
    renderCarrito();
    document.getElementById('buscadorPOS').focus();
}

async function confirmarVenta() {
    let total = parseFloat(document.getElementById('fiTot').value) || 0;
    if(total <= 0) {
        alert("El carrito está vacío.");
        return;
    }

    // Optional: Validar el metodo de pago vs el monto recibido en Efectivo
    if(document.getElementById('btnEfecti').checked) {
        let pago = parseFloat(document.getElementById('inPago').value) || 0;
        if (pago < total) {
            alert("El monto de efectivo recibido es menor al total de la venta.");
            document.getElementById('inPago').focus();
            return;
        }
    }

    // Validar CMP visible
    if(document.getElementById('inCmp').hasAttribute('required') && document.getElementById('inCmp').value.trim() === '') {
        alert("Atención: Ha incluido productos controlados. Debe ingresar la colegiatura médica (CMP) del doctor.");
        document.getElementById('inCmp').focus();
        return;
    }

    if(!confirm('¿Procesar venta por S/ ' + total.toFixed(2) + '?')) return;

    let itemsVendidos = Object.values(carrito).map(i => ({
        id: i.id, cantidad: i.cantidad, tipo_unidad: i.tipo_unidad,
        fraccionable: i.fraccionable, unidades_por_caja: i.unidades_por_caja
    }));

    let btn = document.getElementById('btnCobrar');
    btn.disabled = true; // Evita cobros duplicados por doble clic mientras se procesa
    let textoOriginal = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

    try {
        let formData = new FormData(document.getElementById('formVenta'));
        let res = await fetch('<?php echo BASE_URL; ?>venta/save', {
            method: 'POST',
            headers: { 'X-CSRF-Token': document.getElementById('csrfToken').value },
            credentials: 'same-origin',
            body: formData
        });

        let data = null;
        try { data = await res.json(); } catch (e) { /* respuesta no-JSON: se maneja abajo */ }

        if (!data) {
            mostrarAlerta('error', 'Error inesperado del servidor. Intenta nuevamente.');
            return;
        }

        if (data.code === 'session_expired' || data.code === 'csrf_invalid') {
            alert(data.message || 'Tu sesión ha expirado. Vuelve a iniciar sesión.');
            window.location.href = '<?php echo BASE_URL; ?>auth/login';
            return;
        }

        if (!data.success) {
            mostrarAlerta('error', data.message || 'No se pudo procesar la venta.');
            return;
        }

        // Rotamos el token CSRF con el que vino en la respuesta para poder cobrar de nuevo sin recargar.
        document.getElementById('csrfToken').value = data.csrf_token;

        sincronizarStockLocal(itemsVendidos);
        mostrarAlerta('success', data.mensaje, data.id_venta);
        resetVentaTrasCobro();

    } catch (err) {
        mostrarAlerta('error', 'No se pudo conectar con el servidor. Verifica tu conexión e intenta nuevamente.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    }
}
</script>
