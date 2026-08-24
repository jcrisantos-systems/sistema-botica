<?php if (!empty($_SESSION['flash_messages'])): ?>
    <div class="px-4 pt-3">
        <?php foreach ($_SESSION['flash_messages'] as $flash):
            $tipo = ($flash['type'] === 'error') ? 'danger' : $flash['type'];
            $icono = $tipo === 'danger' ? 'bi-exclamation-triangle-fill' : ($tipo === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill');
        ?>
            <div class="alert alert-<?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?> alert-dismissible fade show mb-2" role="alert">
                <i class="bi <?php echo $icono; ?>"></i> <?php echo htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['flash_messages']); ?>
<?php endif; ?>
