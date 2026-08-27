<div class="page-content">
    <div class="mb-4">
        <h1 class="page-title"><i class="bi bi-box-arrow-right" style="color:var(--danger);"></i> Arqueo y Cierre de Caja</h1>
        <div class="page-subtitle">Cierre del turno y cuadre de efectivo físico vs sistema.</div>
    </div>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success mb-4"><i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><i class="bi bi-x-circle"></i> <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Resumen del Sistema -->
        <div class="col-lg-6 mb-4">
            <div class="card-metric h-100" style="border-top: 3px solid var(--accent-primary);">
                    <h5 class="section-title"><i class="bi bi-display"></i> Resumen del Sistema</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Fecha Apertura
                            <span class="badge bg-secondary"><?php echo date('d/m/Y H:i', strtotime($data['caja']['fecha_apertura'])); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Monto Base Inicial
                            <strong>S/ <?php echo number_format($data['caja']['monto_inicial'], 2); ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center text-success">
                            + Ventas en Efectivo
                            <strong>S/ <?php echo number_format($data['resumen']['ingresos_efectivo'], 2); ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center text-success bg-light">
                            + Ingresos Extraordinarios Totales
                            <strong>S/ <?php echo number_format($data['resumen']['ingresos_extras'], 2); ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center text-danger bg-light">
                            - Retiros / Egresos Totales
                            <strong>S/ <?php echo number_format($data['resumen']['egresos'], 2); ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-light" style="color: var(--text-primary);">
                            Ventas por Transferencia/Tarjeta (No Efectivo)
                            <strong>S/ <?php echo number_format($data['resumen']['ingresos_transferencia'], 2); ?></strong>
                        </li>
                    </ul>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center bg-dark text-white p-3 rounded">
                        <h5 class="mb-0">EFECTIVO ESPERADO</h5>
                        <?php
                            $esperado = $data['caja']['monto_inicial'] + $data['resumen']['ingresos_efectivo'] + $data['resumen']['ingresos_extras'] - $data['resumen']['egresos'];
                        ?>
                        <h4 class="mb-0">S/ <?php echo number_format($esperado, 2); ?></h4>
                    </div>
                    <small class="d-block mt-2" style="color:var(--text-secondary);">* Este es el monto físico (billetes y monedas) que debería contarse en la gaveta en este momento.</small>
            </div>
        </div>

        <!-- Declaración Física (Arqueo) -->
        <div class="col-lg-6 mb-4">
            <div class="card-metric h-100" style="padding:0; overflow:hidden;">
                <div class="card-header bg-danger text-white font-weight-bold">
                    <i class="bi bi-person-check"></i> Arqueo Físico (Gaveta)
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>caja/cierre" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="mb-4 text-center">
                            <label class="form-label font-weight-bold" style="color:var(--danger);">Ingresar Monto Físico Contado (S/)</label>
                            <input type="number" step="0.01" name="monto_final_real" id="monto_final_real" class="form-control-custom text-center" style="font-size: 2rem; font-weight: bold; background-color: #fff3f3;" placeholder="0.00" oninput="calcularDiferencia()" required>

                            <div class="mt-3 p-3 rounded" id="diferencia_box" style="display:none;">
                                <h5>Diferencia Calculada:</h5>
                                <h3 id="diferencia_texto">S/ 0.00</h3>
                                <div id="diferencia_badge"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Observaciones (Opcional)</label>
                            <textarea name="observacion" class="form-control-custom" rows="2" placeholder="Motivo en caso de sobrante o faltante..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-danger btn-lg w-100 font-weight-bold" onclick="return confirm('¿Está seguro de cerrar la caja con estos montos? No podrá modificarlos luego.')">
                            <i class="bi bi-lock-fill"></i> CONFIRMAR CIERRE DE CAJA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Panel de Movimientos Extra de Caja -->
        <div class="col-lg-12 mb-4">
            <div class="card-metric">
                <h5 class="section-title"><i class="bi bi-wallet2"></i> Movimientos Extraordinarios Manuales</h5>
                <div class="row">
                    <div class="col-md-5 border-end">
                        <form action="<?php echo BASE_URL; ?>caja/movimiento" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                            <h6 class="mb-3" style="color:var(--text-secondary);">Registrar Nuevo Movimiento</h6>
                            <div class="mb-2">
                                <label class="form-label" style="font-size:12px;">Tipo de Movimiento</label>
                                <select class="form-control-custom" name="tipo" required>
                                    <option value="EGRESO">Retiro de Efectivo (Egreso)</option>
                                    <option value="INGRESO">Ingreso Adicional a Gaveta</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label" style="font-size:12px;">Monto S/</label>
                                <input type="number" step="0.01" min="0.1" name="monto" class="form-control-custom" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:12px;">Motivo o Descripción</label>
                                <input type="text" name="motivo" class="form-control-custom" placeholder="Ej: Pago servicio de luz, o Retiro de seguridad" required>
                            </div>
                            <button type="submit" class="btn-primary-custom"><i class="bi bi-plus-circle"></i> GUARDAR MOVIMIENTO</button>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h6 class="mb-3" style="color:var(--text-secondary);">Historial de Turno Actual</h6>
                        <table class="table-custom" style="font-size:13px;">
                            <thead>
                                <tr><th>Hora</th><th>Tipo</th><th>Motivo</th><th>Monto</th></tr>
                            </thead>
                            <tbody>
                                <?php if(empty($data['movimientos'])): ?>
                                    <tr><td colspan="4" class="text-center text-muted">Sin movimientos extra.</td></tr>
                                <?php else: ?>
                                    <?php foreach($data['movimientos'] as $m): ?>
                                    <tr>
                                        <td><?php echo date('H:i:s', strtotime($m['fecha_movimiento'])); ?></td>
                                        <td>
                                            <?php if($m['tipo']=='INGRESO'): ?>
                                                <span class="badge bg-success"><i class="bi bi-arrow-down-short"></i> INGRESO</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><i class="bi bi-arrow-up-short"></i> EGRESO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($m['motivo']); ?></td>
                                        <td class="fw-bold">S/ <?php echo number_format($m['monto'],2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const esperado = <?php echo $esperado; ?>;

    function calcularDiferencia() {
        const cajaRealBox = document.getElementById('monto_final_real').value;
        const diferenciaBox = document.getElementById('diferencia_box');
        const diferenciaTexto = document.getElementById('diferencia_texto');
        const diferenciaBadge = document.getElementById('diferencia_badge');

        if(cajaRealBox === '') {
            diferenciaBox.style.display = 'none';
            return;
        }

        const real = parseFloat(cajaRealBox);
        const diferencia = real - esperado;
        
        diferenciaBox.style.display = 'block';
        diferenciaTexto.innerText = "S/ " + diferencia.toFixed(2);

        if (diferencia > 0) {
            diferenciaBox.className = "mt-3 p-3 rounded bg-success bg-opacity-10 text-success";
            diferenciaBadge.innerHTML = "<span class='badge bg-success'>SOBRANTE</span>";
        } else if (diferencia < 0) {
            diferenciaBox.className = "mt-3 p-3 rounded bg-danger bg-opacity-10 text-danger";
            diferenciaBadge.innerHTML = "<span class='badge bg-danger'>FALTANTE</span>";
        } else {
            diferenciaBox.className = "mt-3 p-3 rounded bg-primary bg-opacity-10 text-primary";
            diferenciaBadge.innerHTML = "<span class='badge bg-primary'>CUADRE PERFECTO</span>";
        }
    }
</script>
