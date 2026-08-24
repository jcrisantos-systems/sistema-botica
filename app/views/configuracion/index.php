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
    background: rgba(0,0,0,0.3);
    cursor: pointer;
    transition: all 0.3s;
}
.logo-preview-container:hover {
    background: rgba(30, 215, 96, 0.1);
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
                    <h5 style="color: #fff; font-size: 16px; margin-bottom: 30px;">Identidad Visual</h5>
                    
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
                    <h5 style="color: #fff; font-size: 16px; margin-bottom: 25px;"><i class="bi bi-building"></i> Información Fiscal y Comercial</h5>
                    
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

                    <h5 style="color: #fff; font-size: 16px; margin-top: 35px; margin-bottom: 25px;"><i class="bi bi-cash-coin"></i> Valores Financieros Base</h5>
                    
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
</script>
