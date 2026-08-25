<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Auditoría e Integridad del Sistema</h1>
            <div class="page-subtitle">Rastreo completo de seguridad, accesos y acciones críticas de los usuarios.</div>
        </div>
        <button class="btn btn-outline-secondary" onclick="window.location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Actualizar Logs
        </button>
    </div>

    <!-- TABS NAVIGATION -->
    <ul class="nav nav-tabs border-secondary mb-4" id="auditTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="acciones-tab" data-bs-toggle="tab" data-bs-target="#acciones" type="button" role="tab">
                <i class="bi bi-activity"></i> Registro de Actividad
            </button>
        </li>
        <li class="nav-item" role="presentation" style="margin-left: 10px;">
            <button class="nav-link" id="accesos-tab" data-bs-toggle="tab" data-bs-target="#accesos" type="button" role="tab">
                <i class="bi bi-shield-lock"></i> Control de Accesos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="auditTabsContent">
        <!-- TAB 1: ACCIONES CRÍTICAS -->
        <div class="tab-pane fade show active" id="acciones" role="tabpanel">
            <div class="card-metric p-4">
                <div class="table-responsive">
                    <table class="table-custom" id="tableAcciones">
                        <thead>
                            <tr>
                                <th width="150">Fecha / Hora</th>
                                <th width="150">Usuario</th>
                                <th width="120">Módulo</th>
                                <th width="120">Acción</th>
                                <th>Descripción detallada del evento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['acciones'] as $acc): ?>
                            <tr>
                                <td style="font-family: monospace; font-size:12px; color: var(--text-secondary);">
                                    <?php echo date('d/m/Y H:i:s', strtotime($acc['fecha'])); ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" style="width:24px; height:24px; font-size:10px;">
                                            <?php echo strtoupper(substr($acc['username'], 0, 1)); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($acc['nombres']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-secondary"><?php echo htmlspecialchars($acc['modulo'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td>
                                    <?php
                                        $badgeClass = 'bg-secondary';
                                        if(strpos($acc['accion'], 'ANULAR') !== false) $badgeClass = 'bg-danger';
                                        if(strpos($acc['accion'], 'AJUSTE') !== false) $badgeClass = 'bg-warning text-dark';
                                        if(strpos($acc['accion'], 'CREAR') !== false) $badgeClass = 'bg-success';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($acc['accion'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td style="font-size: 13px; color: var(--text-primary);">
                                    <?php echo htmlspecialchars($acc['descripcion']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: ACCESOS (LOGIN/LOGOUT) -->
        <div class="tab-pane fade" id="accesos" role="tabpanel">
            <div class="card-metric p-4">
                <div class="table-responsive">
                    <table class="table-custom" id="tableAccesos">
                        <thead>
                            <tr>
                                <th width="150">Fecha / Hora</th>
                                <th width="180">Usuario</th>
                                <th width="120">Acción</th>
                                <th width="150">Dirección IP</th>
                                <th>Navegador / Sistema</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['accesos'] as $log): ?>
                            <tr>
                                <td style="font-family: monospace; color: var(--text-secondary);">
                                    <?php echo date('d/m/Y H:i:s', strtotime($log['fecha'])); ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($log['nombres']); ?></strong> <br> <small class="text-muted">@<?php echo htmlspecialchars($log['username'], ENT_QUOTES, 'UTF-8'); ?></small></td>
                                <td>
                                    <?php if($log['accion'] == 'LOGIN'): ?>
                                        <span class="text-success fw-bold"><i class="bi bi-box-arrow-in-right"></i> INGRESÓ</span>
                                    <?php else: ?>
                                        <span class="text-danger fw-bold"><i class="bi bi-box-arrow-right"></i> SALIÓ</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-family: monospace;"><?php echo htmlspecialchars($log['ip_address'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="font-size: 11px; color: var(--text-secondary);">
                                    <?php echo htmlspecialchars(substr($log['user_agent'], 0, 80)); ?>...
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Manejo simple de tabs
document.addEventListener('DOMContentLoaded', function() {
    var triggerTabList = [].slice.call(document.querySelectorAll('#auditTabs button'))
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl)
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault()
            tabTrigger.show()
            
            // Estilo visual manual
            document.querySelectorAll('#auditTabs button').forEach(b => {
                b.style.borderBottom = 'none';
            });
            triggerEl.style.borderBottom = '2px solid var(--accent-primary)';
        })
    })
});
</script>
