<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Laboratorios</h1>
            <div class="page-subtitle">Gestiona las marcas y laboratorios de los productos.</div>
        </div>
        <button class="btn-primary-custom" style="width: auto; padding: 10px 20px;" data-bs-toggle="modal" data-bs-target="#modalLaboratorio" onclick="nuevoRegistro()">
            <i class="bi bi-plus-lg"></i> Nuevo Laboratorio
        </button>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th width="150" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['laboratorios'])): ?>
                    <tr><td colspan="4" class="text-center text-muted">No hay datos registrados</td></tr>
                    <?php else: ?>
                    <?php foreach($data['laboratorios'] as $lab): ?>
                    <tr>
                        <td><?php echo $lab['id']; ?></td>
                        <td style="font-weight:700; color:var(--text-primary);"><?php echo htmlspecialchars($lab['nombre']); ?></td>
                        <td style="color:var(--text-secondary);"><?php echo htmlspecialchars($lab['descripcion'] ?? ''); ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm" style="color: #00CFE8;" onclick="editarRegistro(<?php echo htmlspecialchars(json_encode($lab)); ?>)">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <a href="<?php echo BASE_URL; ?>laboratorio/delete/<?php echo $lab['id']; ?>" class="btn btn-sm" style="color: var(--danger);" onclick="return confirm('¿Seguro que deseas eliminar este laboratorio?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Laboratorio -->
<div class="modal fade" id="modalLaboratorio" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content" style="background-color: var(--bg-card); border: 1px solid var(--border-color);">
      <form action="<?php echo BASE_URL; ?>laboratorio/save" method="POST">
          <input type="hidden" name="id" id="txtId">
          <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
            <h5 class="modal-title" id="modalTitle" style="color: var(--text-primary); font-size: 16px; font-weight:700;">Nuevo Laboratorio</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <div class="form-group">
                  <label class="form-label">Nombre del Laboratorio</label>
                  <input type="text" class="form-control-custom" name="nombre" id="txtNombre" required>
              </div>
              <div class="form-group mb-0">
                  <label class="form-label">Descripción (Opcional)</label>
                  <textarea class="form-control-custom" name="descripcion" id="txtDesc" rows="3"></textarea>
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
    document.getElementById('txtNombre').value = '';
    document.getElementById('txtDesc').value = '';
    document.getElementById('modalTitle').innerText = 'Nuevo Laboratorio';
}

function editarRegistro(obj) {
    document.getElementById('txtId').value = obj.id;
    document.getElementById('txtNombre').value = obj.nombre;
    document.getElementById('txtDesc').value = obj.descripcion;
    document.getElementById('modalTitle').innerText = 'Editar Laboratorio';
    var modal = new bootstrap.Modal(document.getElementById('modalLaboratorio'));
    modal.show();
}
</script>
