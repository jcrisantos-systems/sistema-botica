<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($data['title']) ? $data['title'] . ' | Mi Botica' : 'Mi Botica'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>

<div id="wrapper">
    <!-- Sidebar -->
    <aside id="sidebar">
        <?php
        require_once '../app/models/Configuracion.php';
        $_globalConfigModel = new Configuracion();
        $_globalLogo = $_globalConfigModel->get('logo');

        require_once '../app/models/Inventario.php';
        $_globalInvModel = new Inventario();
        $_lotesVencer = $_globalInvModel->getLotesProximosVencer(90);
        $_stockBajo = $_globalInvModel->getProductosBajoStock(20);
        $_totalNotifs = count($_lotesVencer) + count($_stockBajo);
        ?>
        <a href="<?php echo BASE_URL; ?>auth/index" class="sidebar-logo text-center d-block">
            <?php if (!empty($_globalLogo)): ?>
                <img src="<?php echo htmlspecialchars($_globalLogo); ?>" alt="Logo Botica" style="max-height: 45px; max-width: 100%; object-fit: contain;">
            <?php else: ?>
                <i class="bi bi-heart-pulse-fill"></i> Mi Botica
            <?php endif; ?>
        </a>
        <ul class="sidebar-nav">
            <?php if($_SESSION['rol_id'] == 1): ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>dashboard/index" class="nav-link">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>
            <?php endif; ?>
            <?php if(in_array($_SESSION['rol_id'], [1, 2, 3])): ?>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuCaja" role="button" aria-expanded="false" aria-controls="menuCaja" style="color: #ff9800;">
                    <i class="bi bi-box-arrow-in-right"></i> Gestión de Caja
                    <i class="bi bi-chevron-down ms-auto" style="font-size:12px"></i>
                </a>
                <div class="collapse" id="menuCaja">
                    <ul class="nav flex-column ms-3 mt-1" style="font-size: 13px;">
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>caja/apertura" class="nav-link"><i class="bi bi-circle"></i> Abrir Turno (Caja)</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>caja/cierre" class="nav-link"><i class="bi bi-circle"></i> Cerrar / Arqueo</a>
                        </li>
                        <?php if($_SESSION['rol_id'] == 1): ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>caja/index" class="nav-link"><i class="bi bi-circle"></i> Historial Arqueos</a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>caja/historial_propio" class="nav-link"><i class="bi bi-circle"></i> Historial Arqueos</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>venta/pos" class="nav-link" id="nav-pos">
                    <i class="bi bi-cart-fill"></i> PUNTO DE VENTA (POS)
                </a>
            </li>
            <?php endif; ?>
            <?php if(in_array($_SESSION['rol_id'], [1, 2])): ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>venta/index" class="nav-link">
                    <i class="bi bi-receipt"></i> Historial Ventas
                </a>
            </li>
            <?php elseif($_SESSION['rol_id'] == 3): ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>venta/historial_propio" class="nav-link">
                    <i class="bi bi-receipt"></i> Historial Ventas
                </a>
            </li>
            <?php endif; ?>
            <?php if(in_array($_SESSION['rol_id'], [1, 2, 3])): ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>cliente/index" class="nav-link">
                    <i class="bi bi-people"></i> Clientes
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>importacion/index" class="nav-link">
                    <i class="bi bi-cloud-upload-fill"></i> Importación Masiva
                </a>
            </li>

            <?php if(in_array($_SESSION['rol_id'], [1, 2, 4])): ?>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuProductos" role="button" aria-expanded="false" aria-controls="menuProductos">
                    <i class="bi bi-box-seam"></i> Catálogo Maestro
                    <i class="bi bi-chevron-down ms-auto" style="font-size:12px"></i>
                </a>
                <div class="collapse" id="menuProductos">
                    <ul class="nav flex-column ms-3 mt-1" style="font-size: 13px;">
                        <?php if(in_array($_SESSION['rol_id'], [1, 2, 4])): ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>producto/index" class="nav-link"><i class="bi bi-circle"></i> Productos</a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array($_SESSION['rol_id'], [1, 4])): ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>categoria/index" class="nav-link"><i class="bi bi-circle"></i> Categorías</a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array($_SESSION['rol_id'], [1, 4])): ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>laboratorio/index" class="nav-link"><i class="bi bi-circle"></i> Laboratorios</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
            <?php if(in_array($_SESSION['rol_id'], [1, 4])): ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>compra/index" class="nav-link">
                    <i class="bi bi-cart"></i> Compras
                </a>
            </li>
            <?php endif; ?>
            <?php if(in_array($_SESSION['rol_id'], [1, 4])): ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>proveedor/index" class="nav-link">
                    <i class="bi bi-truck"></i> Proveedores
                </a>
            </li>
            <?php endif; ?>
            <?php if(in_array($_SESSION['rol_id'], [1, 4])): ?>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuInventario" role="button" aria-expanded="false" aria-controls="menuInventario">
                    <i class="bi bi-clipboard-data"></i> Inventario
                    <i class="bi bi-chevron-down ms-auto" style="font-size:12px"></i>
                </a>
                <div class="collapse" id="menuInventario">
                    <ul class="nav flex-column ms-3 mt-1" style="font-size: 13px;">
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>inventario/lotes" class="nav-link"><i class="bi bi-circle"></i> Fechas Vencimiento</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>inventario/kardex" class="nav-link"><i class="bi bi-circle"></i> Kardex General</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>inventariofisico/index" class="nav-link"><i class="bi bi-circle"></i> Inventario Físico</a>
                        </li>
                    </ul>
                </div>
            </li>
            <?php endif; ?>
            <?php if($_SESSION['rol_id'] == 1): ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>notificacion/index" class="nav-link">
                    <i class="bi bi-bell"></i> Alertas Sanitarias
                    <?php if($_totalNotifs > 0): ?>
                    <span class="badge-sidebar" style="background-color: var(--danger);"><?php echo $_totalNotifs; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>reporte/index" class="nav-link">
                    <i class="bi bi-bar-chart-fill"></i> Reportes PDF/Excel
                </a>
            </li>
            
            <li style="margin: 20px 15px 5px; font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase;">Ajustes</li>
            
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>usuario/index" class="nav-link">
                    <i class="bi bi-person-badge"></i> Gestión de Personal
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>configuracion/index" class="nav-link">
                    <i class="bi bi-gear-fill"></i> Configuración General
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>auditoria/index" class="nav-link" style="color: #00bcd4;">
                    <i class="bi bi-shield-check"></i> Logs de Auditoría
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>auth/logout" class="nav-link" style="color: var(--danger);">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </li>
        </ul>
    </aside>

    <!-- Content Wrapper -->
    <div id="content-wrapper">
        <!-- Topbar -->
        <header id="topbar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Buscar producto o código de barras...">
            </div>

            <div class="topbar-actions">
                <a href="<?php echo BASE_URL; ?>notificacion/index" class="topbar-icon" style="text-decoration: none;">
                    <i class="bi bi-bell-fill"></i>
                    <?php if($_totalNotifs > 0): ?>
                    <span class="badge rounded-pill bg-danger"><?php echo $_totalNotifs; ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown">
                    <a href="#" class="user-profile" id="userMenuToggle" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration:none;">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nombre'] ?? 'U'); ?>&background=059669&color=fff&bold=true" alt="User Avatar">
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="user-role">
                                <?php
                                $roles = [1 => 'Administrador', 2 => 'Farmacéutico', 3 => 'Cajero', 4 => 'Almacenero'];
                                echo $roles[$_SESSION['rol_id'] ?? 1];
                                ?>
                            </span>
                        </div>
                        <i class="bi bi-chevron-down ms-1" style="color: var(--text-secondary); font-size:12px;"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenuToggle">
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>usuario/perfil">
                                <i class="bi bi-person-fill me-2"></i> Ver / Editar Perfil
                            </a>
                        </li>
                        <?php if(($_SESSION['rol_id'] ?? null) == 1): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>configuracion/index">
                                <i class="bi bi-gear-fill me-2"></i> Configuración
                            </a>
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>auth/logout">
                                <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Mensajes flash globales (ver app/core/Controller.php::flash()) -->
        <?php require_once '../app/views/partials/flash.php'; ?>

        <!-- Main Content ( injected by views ) -->
        <?php require_once '../app/views/' . $view . '.php'; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let currentUrl = window.location.href.split('?')[0]; // Ignorar params
    let links = document.querySelectorAll('#sidebar .nav-link');
    
    links.forEach(link => {
        let href = link.getAttribute('href');
        if (href && href !== '#' && currentUrl.includes(href)) {
            // Si es POS, usa clase especial
            if(href.includes('venta/pos')) {
                link.classList.add('active-pos');
            } else {
                link.classList.add('active');
            }
            
            // Expandir menú padre si está colapsado
            let collapseParent = link.closest('.collapse');
            if (collapseParent) {
                new bootstrap.Collapse(collapseParent, {toggle: false}).show();
                let toggleBtn = document.querySelector('[aria-controls="' + collapseParent.id + '"]');
                if (toggleBtn) {
                    toggleBtn.setAttribute('aria-expanded', 'true');
                    toggleBtn.classList.add('active');
                }
            }
        }
    });
});
</script>
</body>
</html>
