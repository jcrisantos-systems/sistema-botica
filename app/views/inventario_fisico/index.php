<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Inventario Físico (Cuadre)</h1>
            <div class="page-subtitle">Gestión de auditorías de almacén y ajustes automáticos de stock.</div>
        </div>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalIniciar">
            <i class="bi bi-plus-circle"></i> Iniciar Nueva Auditoría
        </button>
    </div>

    <div class="card-metric p-4">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data['auditorias'] as $aud): ?>
                    <tr>
                        <td style="font-weight: bold; color: var(--accent-primary);">#<?php echo $aud['id']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($aud['fecha_inicio'])); ?></td>
                        <td><?php echo $aud['fecha_fin'] ? date('d/m/Y H:i', strtotime($aud['fecha_fin'])) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($aud['usuario']); ?></td>
                        <td>
                            <?php if($aud['estado'] == 'Abierta'): ?>
                                <span class="badge bg-warning text-dark">Abierta</span>
                            <?php elseif($aud['estado'] == 'Finalizada'): ?>
                                <span class="badge bg-success">Finalizada</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo $aud['estado']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 13px; color: var(--text-secondary);">
                            <?php echo htmlspecialchars($aud['observaciones']); ?>
                        </td>
                        <td class="text-center">
                            <?php if($aud['estado'] == 'Abierta'): ?>
                                <a href="<?php echo BASE_URL; ?>inventariofisico/conteo/<?php echo $aud['id']; ?>" class="btn btn-sm btn-success">
                                    <i class="bi bi-pencil-square"></i> Continuar Conteo
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                    <i class="bi bi-lock"></i> Cerrada
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Iniciar -->
<div class="modal fade" id="modalIniciar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border-color);">
            <div class="modal-header">
                <h5 class="modal-title">Iniciar Auditoría de Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>inventariofisico/iniciar" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-body">
                    <p style="font-size: 14px; color: var(--text-secondary);">
                        Al iniciar, el sistema capturará el stock actual de todos los lotes.
                        Podrás ingresar las cantidades físicas encontradas para que el sistema realice los ajustes.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Observaciones / Motivo</label>
                        <textarea name="observaciones" class="form-control-custom" rows="3" placeholder="Ej: Auditoría Trimestral Marzo 2024"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-primary-custom" style="width: auto;">Empezar Auditoría</button>
                </div>
            </form>
        </div>
    </div>
</div>
