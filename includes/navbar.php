<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

// Sucursal actual
$sucursalActual = (int)($_SESSION['id_sucursal'] ?? 1);
$esAdminNavbar = esAdmin();
$rolNombreNavbar = strtoupper((string)($_SESSION['rol_nombre'] ?? 'ADMINISTRADOR'));
$puedeCambiarSucursal = tienePermiso('cambiar_sucursal');

// Obtener info de cajas abiertas para la sucursal actual
$stmtCajas = $pdo->prepare("SELECT id_apertura_cierre_caja, id_sucursal, fecha_apertura FROM apertura_cierre_caja WHERE id_sucursal = :suc AND estado = 'ABIERTA' ORDER BY id_apertura_cierre_caja DESC");
$stmtCajas->execute([':suc' => $sucursalActual]);
$cajasAbiertas = $stmtCajas->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="admin-main">
      <nav class="navbar admin-navbar navbar-expand bg-white border-bottom shadow-sm">
        <div class="container-fluid px-3 px-lg-4">
          <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <div class="d-none d-md-flex align-items-center ms-3 gap-2">
            <?php if ($puedeCambiarSucursal): ?>
            <div class="dropdown">
              <button class="btn btn-sm bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Cambiar Sucursal Activa">
                <i class="bi bi-shop me-1"></i> Sucursal <?= $sucursalActual ?>
              </button>
              <ul class="dropdown-menu shadow-sm">
                <li><h6 class="dropdown-header">Seleccionar Sucursal</h6></li>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between <?= $sucursalActual == 1 ? 'active' : '' ?>" href="javascript:void(0)" onclick="cambiarSucursalActiva(1)">
                    <span><i class="bi bi-building me-2"></i>Sucursal 1 (Avda Lopez Godoy)</span>
                    <?php if ($sucursalActual == 1): ?><i class="bi bi-check2 ms-2"></i><?php endif; ?>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between <?= $sucursalActual == 2 ? 'active' : '' ?>" href="javascript:void(0)" onclick="cambiarSucursalActiva(2)">
                    <span><i class="bi bi-building me-2"></i>Sucursal 2 (Avda Mcal Lopez)</span>
                    <?php if ($sucursalActual == 2): ?><i class="bi bi-check2 ms-2"></i><?php endif; ?>
                  </a>
                </li>
              </ul>
            </div>
            <?php else: ?>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill" title="Sucursal Asignada">
                <i class="bi bi-shop me-1"></i> Sucursal <?= $sucursalActual ?>
              </span>
            <?php endif; ?>
            
            <?php if (!empty($cajasAbiertas)): ?>
              <a href="<?= BASE_URL ?>paginas/movimientos/caja.php" class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill text-decoration-none" title="Caja #<?= $cajasAbiertas[0]['id_apertura_cierre_caja'] ?> Abierta">
                <i class="bi bi-cash-stack me-1"></i> Caja #<?= $cajasAbiertas[0]['id_apertura_cierre_caja'] ?>
              </a>
            <?php else: ?>
              <a href="<?= BASE_URL ?>paginas/movimientos/caja.php" class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill text-decoration-none" title="Sin Caja Abierta - Clic para abrir">
                <i class="bi bi-exclamation-triangle me-1"></i> Caja Cerrada
              </a>
            <?php endif; ?>
          </div>

          <div class="navbar-actions ms-auto d-flex align-items-center gap-2">
            <?php if (tienePermiso('usuarios')): ?>
            <a href="<?= BASE_URL ?>paginas/referenciales/usuarios.php" class="btn btn-outline-primary btn-icon" title="Usuarios, Roles y Accesos">
              <i class="bi bi-shield-lock-fill"></i>
            </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>paginas/movimientos/facturacion.php" class="btn btn-primary btn-icon shadow-sm" title="Ir a Caja Rápida POS">
              <i class="bi bi-cart-check-fill"></i>
            </a>

            <button class="icon-button theme-toggle btn-icon" type="button" data-theme-toggle aria-label="Cambiar tema" title="Cambiar tema Claro / Oscuro">
              <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>

            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="<?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Admin') ?> (<?= htmlspecialchars($rolNombreNavbar) ?>)">
                <div class="avatar-sm <?= $esAdminNavbar ? 'bg-primary' : 'bg-success' ?> text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">
                  <i class="bi <?= $esAdminNavbar ? 'bi-shield-lock-fill' : 'bi-person-badge-fill' ?>"></i>
                </div>
                <span class="profile-name d-none d-sm-inline ms-2">
                  <?= htmlspecialchars($_SESSION['nick_name'] ?? $_SESSION['nombre_usuario'] ?? 'Admin') ?>
                </span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                  <div class="dropdown-item-text">
                    <div class="fw-bold text-dark"><?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Administrador') ?></div>
                    <div class="small text-muted">Rol: <strong><?= htmlspecialchars($rolNombreNavbar) ?></strong> • Suc. <?= $sucursalActual ?></div>
                  </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <?php if (tienePermiso('usuarios')): ?>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>paginas/referenciales/usuarios.php">
                    <i class="bi bi-shield-lock-fill me-2 text-primary"></i> Usuarios y Accesos
                  </a>
                </li>
                <?php endif; ?>
                <?php if ($puedeCambiarSucursal): ?>
                <li>
                  <a class="dropdown-item" href="javascript:void(0)" onclick="cambiarSucursalActiva(<?= $sucursalActual == 1 ? 2 : 1 ?>)">
                    <i class="bi bi-arrow-left-right me-2 text-info"></i> Cambiar a Sucursal <?= $sucursalActual == 1 ? 2 : 1 ?>
                  </a>
                </li>
                <?php endif; ?>
                <li>
                  <a class="dropdown-item" href="<?= BASE_URL ?>login.php">
                    <i class="bi bi-person-bounding-box me-2 text-success"></i> Cambiar de Usuario
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-danger" href="<?= BASE_URL ?>controladores/UsuarioCerrarSesion.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </nav>
      <script>
      async function cambiarSucursalActiva(idSuc) {
        try {
          const fd = new FormData();
          fd.append('accion', 'cambiar_sucursal');
          fd.append('id_sucursal', idSuc);
          const res = await fetch('<?= BASE_URL ?>controladores/UsuarioSession.php', { method: 'POST', body: fd });
          const json = await res.json();
          if (json.status === 'success') {
            window.location.reload();
          } else {
            Swal.fire('Acceso restringido', json.message || 'No puede cambiar de sucursal.', 'warning');
          }
        } catch (e) {
          console.error(e);
        }
      }
      </script>
      <?php if (!empty($active_menu) && !tienePermiso($active_menu)): ?>
      <main class="dashboard-content">
        <div class="container-fluid py-5">
          <div class="card border-0 shadow-sm mx-auto" style="max-width: 560px;">
            <div class="card-body text-center p-5">
              <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center mb-3" style="width:68px;height:68px;">
                <i class="bi bi-shield-exclamation fs-1"></i>
              </div>
              <h4 class="fw-bold text-dark mb-2">Acceso Restringido</h4>
              <p class="text-muted mb-4">
                Su usuario actual (<strong><?= htmlspecialchars($_SESSION['nombre_usuario'] ?? '') ?></strong>) no tiene permiso para abrir esta ventana.
              </p>
              <div class="d-flex justify-content-center gap-2">
                <a href="<?= BASE_URL ?>paginas/movimientos/facturacion.php" class="btn btn-success btn-icon-lg" title="Ir a Caja Rápida POS">
                  <i class="bi bi-cart-check-fill"></i>
                </a>
                <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-primary btn-icon-lg" title="Cambiar de Usuario">
                  <i class="bi bi-box-arrow-in-right"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </main>
      <?php
          require_once __DIR__ . '/footer.php';
          exit;
      endif;
      ?>
