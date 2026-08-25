<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Directorio de Clientes</h1>
            <div class="page-subtitle">Gestiona a tus clientes habituales para facturación.</div>
        </div>
        <button class="btn-primary-custom" style="width: auto; padding: 10px 20px;" data-bs-toggle="modal" data-bs-target="#modalCliente" onclick="nuevoRegistro()">
            <i class="bi bi-person-plus"></i> Nuevo Cliente
        </button>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th width="120">Documento</th>
                        <th>Nombres o Razón Social</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th width="120" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['clientes'])): ?>
                    <tr><td colspan="5" class="text-center text-muted">No hay datos registrados</td></tr>
                    <?php else: ?>
                    <?php foreach($data['clientes'] as $cli): ?>
                    <tr>
                        <td style="color:var(--text-secondary); font-family:monospace;">
                            <span style="font-size: 11px;"><?php echo htmlspecialchars($cli['tipo_documento']); ?></span><br>
                            <?php echo htmlspecialchars($cli['num_documento']); ?>
                        </td>
                        <td style="font-weight:700; color:var(--text-primary);"><?php echo htmlspecialchars($cli['nombres']); ?></td>
                        <td><?php echo htmlspecialchars($cli['telefono'] ?? ''); ?></td>
                        <td style="font-size:12px;"><?php echo htmlspecialchars($cli['direccion'] ?? ''); ?></td>
                        <td class="text-end">
                            <?php if($cli['id'] == 1): ?>
                            <span class="badge bg-secondary">Genérico</span>
                            <?php else: ?>
                            <button class="btn btn-sm" style="color: var(--accent-primary);" onclick="editarRegistro(<?php echo htmlspecialchars(json_encode($cli)); ?>)">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form action="<?php echo BASE_URL; ?>cliente/delete/<?php echo $cli['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar el registro?');">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-sm" style="color: var(--danger);">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="background-color: var(--bg-card); border: 1px solid var(--border-color);">
      <form action="<?php echo BASE_URL; ?>cliente/save" method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="id" id="txtId">
          <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
            <h5 class="modal-title" id="modalTitle" style="color: var(--text-primary); font-size: 16px; font-weight:700;">Nuevo Cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body row g-3">
              <div class="col-md-4 form-group">
                  <label class="form-label">Tipo Documento</label>
                  <select class="form-control-custom" name="tipo_documento" id="txtTipo">
                      <option value="DNI">DNI (Boleta)</option>
                      <option value="RUC">RUC (Factura)</option>
                      <option value="Pasaporte">Pasaporte</option>
                  </select>
              </div>
              <div class="col-md-8 form-group">
                  <label class="form-label">Número Documento</label>
                  <input type="text" class="form-control-custom" name="num_documento" id="txtNum" required>
              </div>
              <div class="col-md-6 form-group">
                  <label class="form-label">Nombres Completos / Razón Social</label>
                  <input type="text" class="form-control-custom" name="nombres" id="txtNombres" required>
              </div>
              <div class="col-md-6 form-group">
                  <label class="form-label">Teléfono Celular</label>
                  <input type="text" class="form-control-custom" name="telefono" id="txtTel">
              </div>
              <div class="col-12 form-group mb-0">
                  <label class="form-label">Dirección Fiscal / Envío</label>
                  <input type="text" class="form-control-custom" name="direccion" id="txtDir">
              </div>
          </div>
          <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
            <button type="button" class="btn btn-secondary" style="border-radius:10px;" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn-primary-custom" style="width: auto; padding: 8px 20px;">Guardar Cambios</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function nuevoRegistro() {
    document.getElementById('txtId').value = '';
    document.getElementById('txtTipo').value = 'DNI';
    document.getElementById('txtNum').value = '';
    document.getElementById('txtNombres').value = '';
    document.getElementById('txtTel').value = '';
    document.getElementById('txtDir').value = '';
    document.getElementById('modalTitle').innerText = 'Nuevo Cliente';
}

function editarRegistro(obj) {
    document.getElementById('txtId').value = obj.id;
    document.getElementById('txtTipo').value = obj.tipo_documento;
    document.getElementById('txtNum').value = obj.num_documento;
    document.getElementById('txtNombres').value = obj.nombres;
    document.getElementById('txtTel').value = obj.telefono;
    document.getElementById('txtDir').value = obj.direccion;
    document.getElementById('modalTitle').innerText = 'Editar Cliente';
    var modal = new bootstrap.Modal(document.getElementById('modalCliente'));
    modal.show();
}
</script>
