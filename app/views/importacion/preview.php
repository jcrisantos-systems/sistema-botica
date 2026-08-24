<div class="page-content">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>importacion/index" style="color:var(--text-secondary); text-decoration:none; font-size:14px;"><i class="bi bi-arrow-left"></i> Importación Masiva</a>
        <h1 class="page-title mt-2">Previsualización — <?php echo htmlspecialchars($data['entidadLabel'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <div class="page-subtitle">
            <?php echo $data['totalFilas']; ?> filas leídas ·
            <span style="color: var(--accent-primary); font-weight:600;"><?php echo $data['totalValidas']; ?> válidas</span> ·
            <span style="color: var(--danger); font-weight:600;"><?php echo $data['totalFilas'] - $data['totalValidas']; ?> con errores</span>
        </div>
    </div>

    <?php if ($data['totalValidas'] === 0): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-octagon-fill"></i> Ninguna fila pasó la validación. Corrige el archivo y vuelve a subirlo.</div>
    <?php else: ?>
        <div class="alert" style="background: rgba(255,193,7,0.12); border:1px solid #ffc107; color:#856404;">
            <i class="bi bi-info-circle-fill"></i> Al confirmar, solo se procesarán las <strong><?php echo $data['totalValidas']; ?> filas válidas</strong> (en verde). Las filas en rojo se omitirán; corrígelas en el archivo original y súbelo de nuevo si quieres incluirlas.
        </div>
    <?php endif; ?>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-sm table-hover" style="font-size:12px;">
                <thead>
                    <tr>
                        <th>Fila</th>
                        <th>Estado</th>
                        <?php foreach($data['columnas'] as $col): ?>
                            <th><?php echo htmlspecialchars($col, ENT_QUOTES, 'UTF-8'); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data['filas'] as $f): ?>
                    <tr style="<?php echo $f['valida'] ? 'background: rgba(2,195,154,0.08);' : 'background: rgba(230,57,70,0.10);'; ?>">
                        <td><?php echo (int)$f['fila']; ?></td>
                        <td>
                            <?php if ($f['valida']): ?>
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> <?php echo $f['accion'] === 'actualizar' ? 'Actualizará' : 'Nuevo'; ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-lg"></i> <?php echo count($f['errores']); ?> error(es)</span>
                            <?php endif; ?>
                        </td>
                        <?php foreach($data['columnas'] as $col): ?>
                            <td><?php echo htmlspecialchars($f['original'][$col] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php if (!$f['valida']): ?>
                    <tr style="background: rgba(230,57,70,0.04);">
                        <td></td>
                        <td colspan="<?php echo count($data['columnas']) + 1; ?>" style="color: var(--danger); font-size:11px;">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars(implode(' · ', $f['errores']), ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="<?php echo BASE_URL; ?>importacion/cancelar" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle"></i> Cancelar
        </a>
        <?php if ($data['totalValidas'] > 0): ?>
        <form action="<?php echo BASE_URL; ?>importacion/confirmar" method="POST" onsubmit="return confirm('Se procesarán <?php echo $data['totalValidas']; ?> filas y se escribirán en la base de datos.\n\n¿Confirmas?');">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="btn-primary-custom" style="width:auto; padding:10px 24px;">
                <i class="bi bi-check2-circle"></i> Confirmar e Importar <?php echo $data['totalValidas']; ?> Fila(s)
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>
