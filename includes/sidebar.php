<?php
if (!isset($active_menu)) {
    $active_menu = '';
}
$rolActualSidebar = strtoupper((string)($_SESSION['rol_nombre'] ?? 'ADMINISTRADOR'));
$esAdminSidebar = esAdmin();
?>
    <aside class="admin-sidebar" id="adminSidebar" aria-label="Navegación Principal">
      <div class="sidebar-header">
        <a class="brand-mark" href="<?= BASE_URL ?>index.php" aria-label="Inicio Sistema Mayorista">
          <span class="brand-icon"><i class="bi bi-shop text-primary" aria-hidden="true"></i></span>
          <span class="brand-copy">
            <span class="brand-title">MAYORISTA</span>
            <span class="brand-subtitle">Gestión & Facturación</span>
          </span>
        </a>
      </div>

      <nav class="sidebar-nav">
        <?php if (tienePermiso('dashboard')): ?>
        <a class="nav-link <?= $active_menu === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php">
          <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
          <span class="nav-text">Panel Principal</span>
        </a>
        <?php endif; ?>

        <div class="sidebar-heading">Facturación & Caja</div>

        <?php if (tienePermiso('facturacion')): ?>
        <a class="nav-link <?= $active_menu === 'facturacion' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/movimientos/facturacion.php">
          <span class="nav-icon"><i class="bi bi-cart-check-fill text-success" aria-hidden="true"></i></span>
          <span class="nav-text fw-bold">Caja Rápida</span>
          <span class="badge bg-success ms-auto small">Cobrar</span>
        </a>
        <?php endif; ?>

        <?php if (tienePermiso('ventas_historial')): ?>
        <a class="nav-link <?= $active_menu === 'ventas_historial' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/movimientos/ventas_historial.php">
          <span class="nav-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
          <span class="nav-text">Historial Facturas</span>
        </a>
        <?php endif; ?>

        <?php if (tienePermiso('caja')): ?>
        <a class="nav-link <?= $active_menu === 'caja' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/movimientos/caja.php">
          <span class="nav-icon"><i class="bi bi-cash-stack text-warning" aria-hidden="true"></i></span>
          <span class="nav-text">Control de Caja</span>
        </a>
        <?php endif; ?>

        <?php if (tienePermiso('cuentas_cobrar')): ?>
        <a class="nav-link <?= $active_menu === 'cuentas_cobrar' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/movimientos/cuentas_cobrar.php">
          <span class="nav-icon"><i class="bi bi-wallet2 text-info" aria-hidden="true"></i></span>
          <span class="nav-text">Cuentas por Cobrar</span>
          <span class="badge bg-danger-subtle text-danger ms-auto small">Créditos</span>
        </a>
        <?php endif; ?>

        <?php if (tienePermiso('compras') || tienePermiso('informe_ganancias') || tienePermiso('productos') || tienePermiso('ajuste_stock')): ?>
        <div class="sidebar-heading">Inventario & Stock</div>
        <?php endif; ?>

        <?php if (tienePermiso('compras')): ?>
        <a class="nav-link <?= $active_menu === 'compras' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/movimientos/compras.php">
          <span class="nav-icon"><i class="bi bi-bag-plus-fill text-primary" aria-hidden="true"></i></span>
          <span class="nav-text fw-semibold">Factura Compra</span>
          <span class="badge bg-primary-subtle text-primary ms-auto small">+Stock</span>
        </a>
        <?php endif; ?>

        <?php if (tienePermiso('informe_ganancias')): ?>
        <a class="nav-link <?= $active_menu === 'informe_ganancias' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/movimientos/compras.php?tab=ganancias">
          <span class="nav-icon"><i class="bi bi-graph-up-arrow text-success" aria-hidden="true"></i></span>
          <span class="nav-text fw-semibold">Informe Ganancias</span>
        </a>
        <?php endif; ?>

        <?php if (tienePermiso('productos')): ?>
        <a class="nav-link <?= $active_menu === 'productos' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/referenciales/productos.php">
          <span class="nav-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
          <span class="nav-text">Productos & Precios</span>
        </a>
        <?php endif; ?>

        <?php if (tienePermiso('ajuste_stock')): ?>
        <a class="nav-link <?= $active_menu === 'ajuste_stock' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/movimientos/ajuste_stock.php">
          <span class="nav-icon"><i class="bi bi-sliders" aria-hidden="true"></i></span>
          <span class="nav-text">Ajuste de Stock</span>
        </a>
        <?php endif; ?>

        <?php if (tienePermiso('clientes') || tienePermiso('proveedores') || tienePermiso('usuarios')): ?>
        <div class="sidebar-heading">Clientes & Personal</div>
        <?php endif; ?>

        <?php if (tienePermiso('clientes')): ?>
        <a class="nav-link <?= $active_menu === 'clientes' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/referenciales/clientes.php">
          <span class="nav-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
          <span class="nav-text">Clientes</span>
        </a>
        <?php endif; ?>

        <?php if (tienePermiso('proveedores')): ?>
        <a class="nav-link <?= $active_menu === 'proveedores' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/referenciales/proveedores.php">
          <span class="nav-icon"><i class="bi bi-truck" aria-hidden="true"></i></span>
          <span class="nav-text">Proveedores</span>
        </a>
        <?php endif; ?>

        <?php if (tienePermiso('usuarios')): ?>
        <a class="nav-link <?= $active_menu === 'usuarios' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/referenciales/usuarios.php">
          <span class="nav-icon"><i class="bi bi-shield-lock-fill text-primary" aria-hidden="true"></i></span>
          <span class="nav-text fw-semibold">Usuarios y Accesos</span>
          <span class="badge bg-primary-subtle text-primary ms-auto small">Roles</span>
        </a>
        <?php endif; ?>

        <?php if (tienePermiso('referenciales')): ?>
        <div class="sidebar-heading">Tablas Referenciales</div>

        <a class="nav-link <?= $active_menu === 'sucursales' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/referenciales/sucursales.php">
          <span class="nav-icon"><i class="bi bi-building" aria-hidden="true"></i></span>
          <span class="nav-text">Sucursales</span>
        </a>

        <a class="nav-link <?= $active_menu === 'marcas' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/referenciales/marcas.php">
          <span class="nav-icon"><i class="bi bi-tags" aria-hidden="true"></i></span>
          <span class="nav-text">Marcas</span>
        </a>

        <a class="nav-link <?= $active_menu === 'categorias' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/referenciales/categorias.php">
          <span class="nav-icon"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i></span>
          <span class="nav-text">Categorías</span>
        </a>

        <a class="nav-link <?= $active_menu === 'ciudades' ? 'active' : '' ?>" href="<?= BASE_URL ?>paginas/referenciales/ciudades.php">
          <span class="nav-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
          <span class="nav-text">Ciudades</span>
        </a>
        <?php endif; ?>
      </nav>

      <div class="sidebar-user">
        <div class="d-flex align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center gap-2 overflow-hidden">
            <div class="avatar-sm <?= $esAdminSidebar ? 'bg-primary' : 'bg-success' ?> text-white rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 36px; height: 36px;">
              <i class="bi <?= $esAdminSidebar ? 'bi-shield-lock-fill' : 'bi-person-badge-fill' ?>"></i>
            </div>
            <div class="text-truncate">
              <strong class="d-block text-truncate" style="font-size:0.8rem;"><?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Admin') ?></strong>
              <small class="d-block text-muted" style="font-size:0.7rem;">
                <?= htmlspecialchars($rolActualSidebar) ?> • Suc. <?= (int)($_SESSION['id_sucursal'] ?? 1) ?>
              </small>
            </div>
          </div>
          <a href="<?= BASE_URL ?>login.php" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Cambiar de Usuario / Login">
            <i class="bi bi-arrow-left-right"></i>
          </a>
        </div>
      </div>

      <div class="sidebar-footer">
        <span class="status-dot bg-success"></span>
        <span class="sidebar-footer-text">Base de datos Conectada</span>
      </div>
    </aside>
