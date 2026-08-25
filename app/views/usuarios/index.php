<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Gestión de Personal</h1>
            <div class="page-subtitle">Administra los usuarios del sistema y sus roles de acceso.</div>
        </div>
        <button class="btn-primary-custom" style="width: auto; padding: 10px 20px;" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetForm()">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </button>
    </div>

    <div class="card-metric">
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="width: 100%; font-size: 14px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombres y Apellidos</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Último Login</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['usuarios'] as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($u['email']); ?></small>
                            </td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($u['usuario']); ?></span></td>
                            <td>
                                <?php 
                                $badgeClass = 'bg-info';
                                if($u['rol_id'] == 1) $badgeClass = 'bg-danger';
                                if($u['rol_id'] == 2) $badgeClass = 'bg-success';
                                if($u['rol_id'] == 3) $badgeClass = 'bg-primary';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($u['rol_nombre']); ?></span>
                            </td>
                            <td><small><?php echo $u['ultimo_login'] ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : 'Nunca'; ?></small></td>
                            <td>
                                <?php if($u['estado'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick='editUser(<?php echo json_encode($u); ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if($u['id'] != 1): ?>
                                    <form action="<?php echo BASE_URL; ?>usuario/toggle/<?php echo $u['id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Confirma cambiar el estado de este usuario?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-<?php echo $u['estado'] ? 'danger' : 'success'; ?> ms-1" title="<?php echo $u['estado'] ? 'Desactivar' : 'Activar'; ?>">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo BASE_URL; ?>usuario/save" method="POST" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle" style="color: var(--text-primary); font-size: 16px; font-weight:700;">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="userId">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombres <span class="text-danger">*</span></label>
                        <input type="text" name="nombres" id="nombres" class="form-control-custom" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                        <input type="text" name="apellidos" id="apellidos" class="form-control-custom" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control-custom">
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre de Usuario <span class="text-danger">*</span></label>
                        <input type="text" name="usuario" id="usuario" class="form-control-custom" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rol <span class="text-danger">*</span></label>
                        <select name="rol_id" id="rol_id" class="form-control-custom" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['roles'] as $r): ?>
                                <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Contraseña <span id="passReq" class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" class="form-control-custom">
                    <small class="text-muted" id="passHelp" style="display:none;">Deje en blanco para no cambiarla.</small>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" style="border-radius:10px;" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-primary-custom" style="width: auto; padding: 8px 20px;">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').innerText = 'Nuevo Usuario';
    document.getElementById('userId').value = '';
    document.getElementById('nombres').value = '';
    document.getElementById('apellidos').value = '';
    document.getElementById('email').value = '';
    document.getElementById('usuario').value = '';
    document.getElementById('rol_id').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passReq').style.display = 'inline';
    document.getElementById('passHelp').style.display = 'none';
}

function editUser(user) {
    document.getElementById('modalTitle').innerText = 'Editar Usuario';
    document.getElementById('userId').value = user.id;
    document.getElementById('nombres').value = user.nombres;
    document.getElementById('apellidos').value = user.apellidos;
    document.getElementById('email').value = user.email;
    document.getElementById('usuario').value = user.usuario;
    document.getElementById('rol_id').value = user.rol_id;
    document.getElementById('password').value = '';
    
    // Al editar, la contraseña no es obligatoria
    document.getElementById('password').required = false;
    document.getElementById('passReq').style.display = 'none';
    document.getElementById('passHelp').style.display = 'block';
    
    new bootstrap.Modal(document.getElementById('userModal')).show();
}
</script>
