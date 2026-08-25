<div class="page-content">
    <div class="mb-4">
        <h1 class="page-title"><i class="bi bi-cloud-upload-fill" style="color:var(--accent-primary);"></i> Importación Masiva</h1>
        <div class="page-subtitle">Carga o actualiza muchos registros a la vez desde un archivo CSV, sin tener que ingresarlos uno por uno.</div>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card-metric h-100">
                <h5 style="color: var(--text-primary); font-size:16px; margin-bottom:15px;"><i class="bi bi-1-circle-fill" style="color:var(--accent-primary);"></i> Descarga la plantilla</h5>
                <p style="color:var(--text-secondary); font-size:13px;">Elige qué vas a importar y descarga el CSV con las columnas exactas que el sistema espera.</p>
                <div class="list-group">
                    <?php foreach($data['entidades'] as $key => $ent): ?>
                    <a href="<?php echo BASE_URL; ?>importacion/plantilla/<?php echo $key; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($ent['label'], ENT_QUOTES, 'UTF-8'); ?>
                        <i class="bi bi-download"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card-metric h-100">
                <h5 style="color: var(--text-primary); font-size:16px; margin-bottom:15px;"><i class="bi bi-2-circle-fill" style="color:var(--accent-primary);"></i> Sube el archivo completado</h5>
                <form action="<?php echo BASE_URL; ?>importacion/subir" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="mb-3">
                        <label class="form-label">¿Qué vas a importar?</label>
                        <select name="entidad" class="form-control-custom" required>
                            <option value="">Selecciona...</option>
                            <?php foreach($data['entidades'] as $key => $ent): ?>
                            <option value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($ent['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Archivo CSV completado</label>
                        <input type="file" name="archivo" class="form-control-custom" accept=".csv" required>
                        <small class="text-muted">Solo .csv (UTF-8). Si lo editaste en Excel: <em>Archivo &gt; Guardar como &gt; CSV UTF-8 (delimitado por comas)</em>.</small>
                    </div>

                    <button type="submit" class="btn-primary-custom" style="width:auto; padding:10px 24px;">
                        <i class="bi bi-eye-fill"></i> Previsualizar
                    </button>
                </form>

                <hr class="my-4">
                <p style="font-size:12px; color:var(--text-secondary); margin:0;">
                    <i class="bi bi-info-circle"></i> El sistema <strong>no guarda nada automáticamente</strong>: primero verás una vista previa con los errores resaltados fila por fila, y tú confirmas antes de que se escriba algo en la base de datos.
                    Un registro cuya clave (código de barras, RUC, N.º de documento o nombre, según la entidad) ya exista <strong>se actualiza</strong>; si no existe, <strong>se crea</strong>.
                </p>
            </div>
        </div>
    </div>
</div>
