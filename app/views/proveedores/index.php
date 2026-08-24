<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Proveedores</h1>
            <div class="page-subtitle">Gestiona la base de datos de proveedores para compras.</div>
        </div>
        <button class="btn-primary-custom" style="width: auto; padding: 10px 20px;" data-bs-toggle="modal" data-bs-target="#modalProveedor" onclick="nuevoRegistro()">
            <i class="bi bi-truck"></i> Nuevo Proveedor
        </button>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th width="120">RUC</th>
                        <th>Razón Social</th>
                        <th>Representante</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th width="120" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['proveedores'])): ?>
                    <tr><td colspan="6" class="text-center text-muted">No hay datos registrados</td></tr>
                    <?php else: ?>
                    <?php foreach($data['proveedores'] as $prov): ?>
                    <tr>
                        <td style="color:var(--text-secondary); font-family:monospace;"><?php echo htmlspecialchars($prov['ruc'] ?? ''); ?></td>
                        <td style="font-weight:700; color:var(--text-primary);"><?php echo htmlspecialchars($prov['razon_social']); ?></td>
                        <td><?php echo htmlspecialchars($prov['representante'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($prov['telefono'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($prov['direccion'] ?? ''); ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm" style="color: #00CFE8;" onclick="editarRegistro(<?php echo htmlspecialchars(json_encode($prov)); ?>)">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form action="<?php echo BASE_URL; ?>proveedor/delete/<?php echo $prov['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este proveedor?');">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-sm" style="color: var(--danger);">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Proveedor -->
<div class="modal fade" id="modalProveedor" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="background-color: var(--bg-card); border: 1px solid var(--border-color);">
      <form action="<?php echo BASE_URL; ?>proveedor/save" method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="id" id="txtId">
          <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
            <h5 class="modal-title" id="modalTitle" style="color: var(--text-primary); font-size: 16px; font-weight:700;">Nuevo Proveedor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body row g-3">
              <div class="col-md-4 form-group">
                  <label class="form-label">RUC</label>
                  <input type="text" class="form-control-custom" name="ruc" id="txtRuc" required maxlength="20">
              </div>
              <div class="col-md-8 form-group">
                  <label class="form-label">Razón Social</label>
                  <input type="text" class="form-control-custom" name="razon_social" id="txtRazon" required>
              </div>
              <div class="col-md-6 form-group">
                  <label class="form-label">Representante</label>
                  <input type="text" class="form-control-custom" name="representante" id="txtRep">
              </div>
              <div class="col-md-6 form-group">
                  <label class="form-label">Teléfono</label>
                  <input type="text" class="form-control-custom" name="telefono" id="txtTel">
              </div>
              <div class="col-12 form-group mb-0">
                  <label class="form-label">Dirección</label>
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
    document.getElementById('txtRuc').value = '';
    document.getElementById('txtRazon').value = '';
    document.getElementById('txtRep').value = '';
    document.getElementById('txtTel').value = '';
    document.getElementById('txtDir').value = '';
    document.getElementById('modalTitle').innerText = 'Nuevo Proveedor';
}

function editarRegistro(obj) {
    document.getElementById('txtId').value = obj.id;
    document.getElementById('txtRuc').value = obj.ruc;
    document.getElementById('txtRazon').value = obj.razon_social;
    document.getElementById('txtRep').value = obj.representante;
    document.getElementById('txtTel').value = obj.telefono;
    document.getElementById('txtDir').value = obj.direccion;
    document.getElementById('modalTitle').innerText = 'Editar Proveedor';
    var modal = new bootstrap.Modal(document.getElementById('modalProveedor'));
    modal.show();
}
</script>
