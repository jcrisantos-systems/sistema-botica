<?php
$c = $data['configs'];
$logo_url = !empty($c['logo']['valor']) ? $c['logo']['valor'] : BASE_URL . 'img/default_logo.png';
?>
<style>
/* Estilos extra para settings */
.settings-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 25px;
    height: 100%;
}
.logo-preview-container {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 3px dashed var(--accent-primary);
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 auto 20px;
    overflow: hidden;
    position: relative;
    background: var(--bg-dark);
    cursor: pointer;
    transition: all 0.3s;
}
.logo-preview-container:hover {
    background: var(--accent-light);
}
.logo-preview-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
.logo-preview-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.6);
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s;
}
.logo-preview-container:hover .logo-preview-overlay {
    opacity: 1;
}
.form-control-custom-icon {
    position: relative;
}
.form-control-custom-icon i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
}
.form-control-custom-icon input {
    padding-left: 45px;
}
</style>
<div class="page-content">
    <div class="mb-4">
        <h1 class="page-title"><i class="bi bi-gear-fill" style="color:var(--accent-primary);"></i> Ajustes de Empresa</h1>
        <div class="page-subtitle">Personaliza la identidad visual y los datos de facturación de la Botica.</div>
    </div>


    <form action="<?php echo BASE_URL; ?>configuracion/save" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="row g-4">
            
            <!-- Izquierda: Branding Logo -->
            <div class="col-md-4">
                <div class="settings-card text-center">
                    <h5 class="section-title" style="justify-content:center; margin-bottom: 30px;">Identidad Visual</h5>
                    
                    <label for="fileLogo" style="width: 100%; cursor: pointer;">
                        <div class="logo-preview-container" id="logoContainer">
                            <img src="<?php echo htmlspecialchars($logo_url); ?>" id="imgPreview" alt="Logo_Preview">
                            <div class="logo-preview-overlay">
                                <i class="bi bi-camera-fill fs-2"></i>
                                <span style="font-size: 12px; font-weight:bold;">Subir Logo</span>
                            </div>
                        </div>
                    </label>
                    <input type="file" name="logo" id="fileLogo" style="display: none;" accept="image/png, image/jpeg, image/jpg">
                    
                    <h6 style="color: var(--accent-primary); font-weight: 700;">Logotipo Institucional</h6>
                    <p style="color: var(--text-secondary); font-size: 12px; line-height: 1.5; margin-top: 10px;">
                        Esta imagen reemplazará automáticamente el logo del sistema en todos los módulos.<br>Formatos: .png, .jpg
                    </p>
                </div>
            </div>

            <!-- Derecha: Datos Legales -->
            <div class="col-md-8">
                <div class="settings-card">
                    <h5 class="section-title"><i class="bi bi-building"></i> Información Fiscal y Comercial</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-12 form-group">
                            <label class="form-label">Nombre o Razón Social</label>
                            <div class="form-control-custom-icon">
                                <i class="bi bi-shop"></i>
                                <input type="text" name="nombre_botica" class="form-control-custom" value="<?php echo htmlspecialchars($c['nombre_botica']['valor'] ?? ''); ?>" required>
                            </div>
                            <small style="color:var(--text-secondary); font-size: 11px;"><?php echo htmlspecialchars($c['nombre_botica']['descripcion'] ?? ''); ?></small>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label">R.U.C / NIT</label>
                            <div class="form-control-custom-icon">
                                <i class="bi bi-card-text"></i>
                                <input type="text" name="ruc" class="form-control-custom" value="<?php echo htmlspecialchars($c['ruc']['valor'] ?? ''); ?>" required>
                            </div>
                            <small style="color:var(--text-secondary); font-size: 11px;"><?php echo htmlspecialchars($c['ruc']['descripcion'] ?? ''); ?></small>
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label class="form-label">Teléfono Comercial</label>
                            <div class="form-control-custom-icon">
                                <i class="bi bi-telephone-fill"></i>
                                <input type="text" name="telefono" class="form-control-custom" value="<?php echo htmlspecialchars($c['telefono']['valor'] ?? ''); ?>">
                            </div>
                            <small style="color:var(--text-secondary); font-size: 11px;"><?php echo htmlspecialchars($c['telefono']['descripcion'] ?? ''); ?></small>
                        </div>

                        <div class="col-md-12 form-group">
                            <label class="form-label">Dirección Fiscal / Establecimiento</label>
                            <div class="form-control-custom-icon">
                                <i class="bi bi-geo-alt-fill"></i>
                                <input type="text" name="direccion" class="form-control-custom" value="<?php echo htmlspecialchars($c['direccion']['valor'] ?? ''); ?>" required>
                            </div>
                            <small style="color:var(--text-secondary); font-size: 11px;"><?php echo htmlspecialchars($c['direccion']['descripcion'] ?? ''); ?></small>
                        </div>
                    </div>

                    <h5 class="section-title" style="margin-top: 35px;"><i class="bi bi-cash-coin"></i> Valores Financieros Base</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Símbolo de Moneda</label>
                            <div class="form-control-custom-icon">
                                <i class="bi bi-currency-exchange"></i>
                                <input type="text" name="moneda" class="form-control-custom" value="<?php echo htmlspecialchars($c['moneda']['valor'] ?? ''); ?>" required>
                            </div>
                            <small style="color:var(--text-secondary); font-size: 11px;"><?php echo htmlspecialchars($c['moneda']['descripcion'] ?? ''); ?></small>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label">Porcentaje de I.G.V. (%)</label>
                            <div class="form-control-custom-icon">
                                <i class="bi bi-percent"></i>
                                <input type="number" step="0.01" name="igv" class="form-control-custom" value="<?php echo htmlspecialchars($c['igv']['valor'] ?? ''); ?>" required>
                            </div>
                            <small style="color:var(--text-secondary); font-size: 11px;"><?php echo htmlspecialchars($c['igv']['descripcion'] ?? ''); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Guardar -->
        <div class="text-end mt-4">
            <button type="submit" class="btn-primary-custom" style="width: auto; padding: 12px 30px; font-size: 16px;">
                <i class="bi bi-save2-fill"></i> Aplicar Ajustes Globales
            </button>
        </div>
    </form>

    <?php if(($_SESSION['rol_id'] ?? null) == 1): ?>
    <!-- Zona de Peligro: Restablecer a Estado de Fábrica -->
    <div class="settings-card mt-4" style="border: 2px solid var(--danger);">
        <h5 style="color: var(--danger); font-size: 16px; margin-bottom: 10px; font-weight:700;">
            <i class="bi bi-exclamation-octagon-fill"></i> Zona de Peligro — Restablecer a Estado de Fábrica
        </h5>
        <p style="color: var(--text-secondary); font-size: 13px;">
            Esta acción elimina datos reales de la base de datos de forma <strong>permanente e irreversible</strong>.
            Al finalizar se cerrará tu sesión automáticamente y deberás iniciar sesión de nuevo.
        </p>

        <div style="background: var(--success-bg); border: 1px solid var(--accent-primary); border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; font-size: 13px; color: var(--text-primary);">
            <i class="bi bi-shield-check" style="color: var(--accent-primary);"></i>
            <strong>Tranquilo, tienes un paracaídas:</strong> justo antes de borrar cualquier dato, el sistema crea automáticamente una copia de seguridad completa (<code>.sql</code>) en la carpeta <code>app/backups/</code>. Si por algún motivo esa copia no se puede crear, el reseteo se cancela solo y no se toca nada. La ruta exacta del backup se te mostrará al terminar.
            Guía completa para cualquier persona (sin conocimientos técnicos): archivo <strong>MANUAL_RESPALDO_Y_RESETEO.md</strong> en la carpeta principal del sistema.
        </div>

        <form action="<?php echo BASE_URL; ?>configuracion/reset" method="POST" id="formReset"
              onsubmit="return confirm('Esta acción eliminará datos de forma PERMANENTE y no se puede deshacer.\n\n¿Estás completamente seguro de continuar?');">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="mb-3">
                <label class="form-label" style="color: var(--text-primary); font-weight:600;">Selecciona el tipo de limpieza</label>

                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="opcion_reset" id="optTotal" value="total" required>
                    <label class="form-check-label" for="optTotal" style="color: var(--text-primary);">
                        <strong>Limpieza Total para Producción</strong><br>
                        <small style="color: var(--text-secondary);">Elimina ventas, historial de caja, kardex, compras, clientes, proveedores y productos. Deja únicamente al usuario Administrador principal y los catálogos base limpios (categorías y laboratorios vacíos).</small>
                    </label>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="opcion_reset" id="optTransacciones" value="transacciones" required>
                    <label class="form-check-label" for="optTransacciones" style="color: var(--text-primary);">
                        <strong>Limpiar Transacciones y Mantener Catálogos</strong><br>
                        <small style="color: var(--text-secondary);">Vacía solo el historial operativo: ventas, compras, caja, kardex y devoluciones. Mantiene intacto el catálogo de productos, categorías, laboratorios, clientes y proveedores.</small>
                    </label>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" style="color: var(--text-primary);">Tu contraseña actual <span class="text-danger">*</span></label>
                    <input type="password" name="password_actual" class="form-control" required autocomplete="current-password">
                </div>
                <div class="col-md-6">
                    <label class="form-label" style="color: var(--text-primary);">Escribe <strong>RESTABLECER</strong> para confirmar <span class="text-danger">*</span></label>
                    <input type="text" name="confirmacion" id="inConfirmacion" class="form-control" required autocomplete="off" placeholder="RESTABLECER">
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="submit" id="btnReset" class="btn btn-danger" disabled>
                    <i class="bi bi-radioactive"></i> Restablecer Sistema
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('fileLogo').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').src = e.target.result;
            document.getElementById('logoContainer').style.borderColor = "#fff";
            setTimeout(() => { document.getElementById('logoContainer').style.borderColor = "var(--accent-primary)"; }, 300);
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});

var inConfirmacion = document.getElementById('inConfirmacion');
if (inConfirmacion) {
    inConfirmacion.addEventListener('input', function() {
        document.getElementById('btnReset').disabled = (this.value !== 'RESTABLECER');
    });
}
</script>
