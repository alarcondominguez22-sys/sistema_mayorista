<?php
$page_title = 'Usuarios, Roles y Accesos';
$active_menu = 'usuarios';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/navbar.php';

$catalogoModulos = catalogoModulosSistema();
$gruposModulos = [];
foreach ($catalogoModulos as $clave => $info) {
    $gruposModulos[$info['grupo']][$clave] = $info;
}
?>

      <main class="dashboard-content">
        <div class="container-fluid py-4">
          
          <!-- Encabezado -->
          <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
              <h1 class="h3 fw-bold mb-1 text-dark">
                <i class="bi bi-shield-lock-fill text-primary me-2"></i>Usuarios, Roles y Accesos
              </h1>
              <p class="text-muted small mb-0">Administración de operadores, sucursales y permisos por módulo.</p>
            </div>
            <div class="d-flex gap-2">
              <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-secondary btn-icon-lg shadow-sm" title="Ir a Pantalla de Login">
                <i class="bi bi-box-arrow-in-right"></i>
              </a>
              <button class="btn btn-primary btn-icon-lg shadow-sm" onclick="abrirModalUsuario()" title="Nuevo Usuario / Dar Acceso">
                <i class="bi bi-person-plus-fill"></i>
              </button>
            </div>
          </div>

          <!-- Tarjetas de Resumen KPI -->
          <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Usuarios</span>
                    <h3 class="fw-bold mb-0 mt-1" id="kpiTotalUsuarios">0</h3>
                  </div>
                  <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                    <i class="bi bi-people-fill fs-4"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-muted small fw-semibold text-uppercase">Administradores</span>
                    <h3 class="fw-bold mb-0 mt-1 text-primary" id="kpiAdmins">0</h3>
                  </div>
                  <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                    <i class="bi bi-shield-check fs-4"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-muted small fw-semibold text-uppercase">Vendedores / Cajeros</span>
                    <h3 class="fw-bold mb-0 mt-1 text-success" id="kpiVendedores">0</h3>
                  </div>
                  <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                    <i class="bi bi-person-badge-fill fs-4"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                  <div>
                    <span class="text-muted small fw-semibold text-uppercase">Cuentas Activas</span>
                    <h3 class="fw-bold mb-0 mt-1 text-dark" id="kpiActivos">0</h3>
                  </div>
                  <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Filtros y Tabla de Usuarios -->
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
              <div class="row g-2 align-items-center">
                <div class="col-md-5">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="filtroBusqueda" class="form-control border-start-0" placeholder="Buscar por nombre, apellido o usuario (ej: ADMIN, JAB, YDDA)..." oninput="renderizarTablaUsuarios()">
                  </div>
                </div>
                <div class="col-md-3">
                  <select id="filtroRol" class="form-select form-select-sm" onchange="renderizarTablaUsuarios()">
                    <option value="">Todos los Roles / Perfiles</option>
                    <option value="ADMIN">Administradores</option>
                    <option value="VENDEDOR">Vendedores / Cajeros / Técnicos</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <select id="filtroSucursal" class="form-select form-select-sm" onchange="renderizarTablaUsuarios()">
                    <option value="">Todas las Sucursales</option>
                    <option value="1">Sucursal 1 (Avda Lopez Godoy)</option>
                    <option value="2">Sucursal 2 (Avda Mcal Lopez)</option>
                  </select>
                </div>
                <div class="col-md-1 text-end">
                  <button class="btn btn-sm btn-outline-secondary w-100" onclick="cargarUsuarios()" title="Recargar lista">
                    <i class="bi bi-arrow-clockwise"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-4">ID</th>
                      <th>Nombre y Apellido</th>
                      <th>Usuario (Login)</th>
                      <th>Rol / Perfil</th>
                      <th>Sucursal Asignada</th>
                      <th>Accesos Habilitados</th>
                      <th>Estado</th>
                      <th class="text-end pe-4">Acciones</th>
                    </tr>
                  </thead>
                  <tbody id="tablaUsuarios">
                    <tr><td colspan="8" class="text-center py-4 text-muted">Cargando usuarios y permisos...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </main>

      <!-- Modal Crear / Editar Usuario y Accesos -->
      <div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title fw-bold" id="tituloModalUsuario">
                <i class="bi bi-person-gear me-2"></i>Crear Usuario y Asignar Accesos
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUsuario" onsubmit="guardarUsuario(event)">
              <div class="modal-body">
                <input type="hidden" id="cod_usuario" name="cod_usuario">

                <div class="row g-4">
                  <!-- Columna Izquierda: Datos de la Cuenta -->
                  <div class="col-lg-5 border-end-lg">
                    <h6 class="fw-bold text-primary text-uppercase small mb-3">
                      <i class="bi bi-person-vcard me-1"></i> 1. Datos de Identificación y Login
                    </h6>

                    <div class="mb-3">
                      <label class="form-label fw-semibold">Nombre y Apellido Completo <span class="text-danger">*</span></label>
                      <input type="text" id="nombre_apellido" name="nombre_apellido" class="form-control text-uppercase" placeholder="Ej: CARLOS GÓMEZ" required>
                    </div>

                    <div class="row g-2 mb-3">
                      <div class="col-sm-6">
                        <label class="form-label fw-semibold">Usuario (Login) <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <span class="input-group-text bg-light"><i class="bi bi-at"></i></span>
                          <input type="text" id="nick_name" name="nick_name" class="form-control text-uppercase fw-bold" placeholder="Ej: CGOMEZ" required>
                        </div>
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label fw-semibold" id="lblPassword">Contraseña <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <input type="password" id="password_usuario" name="password" class="form-control" placeholder="Mín. 3 caracteres">
                          <button class="btn btn-outline-secondary" type="button" onclick="toggleVerPassword()" title="Mostrar / Ocultar contraseña">
                            <i class="bi bi-eye" id="iconoVerPass"></i>
                          </button>
                        </div>
                        <small class="text-muted" id="ayudaPassword">Obligatorio para usuarios nuevos.</small>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-semibold">Rol en el Sistema <span class="text-danger">*</span></label>
                      <select id="id_rol" name="id_rol" class="form-select fw-semibold" onchange="alCambiarRolSelect()">
                        <option value="6">VENDEDOR (Ventas y Facturación POS)</option>
                        <option value="5">CAJERA (Facturación, Caja y Cobranzas)</option>
                        <option value="3">TECNICO (Operador de Sucursal)</option>
                        <option value="4">TECNICO Y SUPERVISOR (Ventas + Stock + Compras)</option>
                        <option value="2">ADMIN (Administrador de Sistema)</option>
                        <option value="1">SUPERADMIN (Control Total)</option>
                      </select>
                    </div>

                    <div class="row g-2 mb-3">
                      <div class="col-sm-7">
                        <label class="form-label fw-semibold">Sucursal Asignada <span class="text-danger">*</span></label>
                        <select id="id_sucursal" name="id_sucursal" class="form-select">
                          <option value="1">Sucursal 1 (Avda Lopez Godoy)</option>
                          <option value="2">Sucursal 2 (Avda Mcal Lopez)</option>
                        </select>
                      </div>
                      <div class="col-sm-5">
                        <label class="form-label fw-semibold">Estado de Cuenta</label>
                        <select id="estado_usuario" name="estado" class="form-select">
                          <option value="ACTIVO">ACTIVO</option>
                          <option value="INACTIVO">INACTIVO</option>
                        </select>
                      </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-0">
                      <i class="bi bi-info-circle-fill me-1"></i>
                      <strong>Redirección inteligente:</strong> Al iniciar sesión, los <strong>Vendedores y Cajeros</strong> ingresan directo a <em>Caja Rápida POS</em>, mientras que los <strong>Administradores</strong> ingresan al <em>Panel Principal</em>.
                    </div>
                  </div>

                  <!-- Columna Derecha: Matriz de Permisos y Accesos -->
                  <div class="col-lg-7">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                      <h6 class="fw-bold text-primary text-uppercase small mb-0">
                        <i class="bi bi-key-fill me-1"></i> 2. Permisos (<span id="contadorPermisos">0</span>/<?= count($catalogoModulos) ?>)
                      </h6>
                      <div class="d-flex gap-1">
                        <button type="button" class="btn btn-outline-primary btn-icon" onclick="aplicarPlantillaPermisos('ADMIN')" title="Seleccionar Todo (Perfil Administrador)">
                          <i class="bi bi-shield-fill-check"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success btn-icon" onclick="aplicarPlantillaPermisos('VENDEDOR')" title="Perfil Vendedor / Cajero POS">
                          <i class="bi bi-cart-check-fill"></i>
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-icon" onclick="aplicarPlantillaPermisos('SUPERVISOR')" title="Perfil Supervisor">
                          <i class="bi bi-boxes"></i>
                        </button>
                      </div>
                    </div>
                    <p class="text-muted small mb-3">Marque o desmarque los módulos a los que este usuario tendrá acceso en el menú lateral y barra superior:</p>

                    <div class="row g-3">
                      <?php foreach ($gruposModulos as $nombreGrupo => $modulos): ?>
                      <div class="col-md-6">
                        <div class="card border h-100 bg-light bg-opacity-50">
                          <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-uppercase text-secondary"><?= htmlspecialchars($nombreGrupo) ?></span>
                          </div>
                          <div class="card-body py-2 px-3">
                            <?php foreach ($modulos as $clave => $mod): ?>
                            <div class="form-check form-switch my-2">
                              <input class="form-check-input check-permiso" type="checkbox" id="perm_<?= $clave ?>" value="<?= $clave ?>" onchange="actualizarContadorPermisos()">
                              <label class="form-check-label small d-flex align-items-center gap-2 cursor-pointer" for="perm_<?= $clave ?>">
                                <i class="bi <?= $mod['icono'] ?> text-primary"></i>
                                <span><?= htmlspecialchars($mod['nombre']) ?></span>
                              </label>
                            </div>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>

              </div>
              <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary fw-bold px-4" id="btnGuardarUsuario">
                  <i class="bi bi-check2-circle me-1"></i> Guardar Usuario y Accesos
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <script>
      const BASE_URL = '<?= BASE_URL ?>';
      const USUARIO_SESION_ACTUAL = <?= (int)($_SESSION['idusuario'] ?? 0) ?>;
      let listaUsuariosGlobal = [];
      let catalogoModulosGlobal = {};
      let modalUsuarioObj = null;

      document.addEventListener('DOMContentLoaded', () => {
        modalUsuarioObj = new bootstrap.Modal(document.getElementById('modalUsuario'));
        cargarUsuarios();
      });

      async function cargarUsuarios() {
        try {
          const res = await fetch(`${BASE_URL}controladores/UsuariosControlador.php?accion=listar`);
          const json = await res.json();
          if (json.status === 'success') {
            listaUsuariosGlobal = json.data || [];
            catalogoModulosGlobal = json.catalogo_modulos || {};
            if (Array.isArray(json.roles) && json.roles.length > 0) {
              const selRol = document.getElementById('id_rol');
              selRol.innerHTML = json.roles.map(r => `<option value="${r.id_rol}">${r.descripcion_rol}</option>`).join('');
            }
            if (Array.isArray(json.sucursales) && json.sucursales.length > 0) {
              const selSuc = document.getElementById('id_sucursal');
              selSuc.innerHTML = json.sucursales.map(s => `<option value="${s.id_sucursal}">Sucursal ${s.id_sucursal} (${s.nombre_sucursal})</option>`).join('');
            }
            actualizarKPIs();
            renderizarTablaUsuarios();
          }
        } catch (e) {
          console.error(e);
        }
      }

      function actualizarKPIs() {
        const total = listaUsuariosGlobal.length;
        const admins = listaUsuariosGlobal.filter(u => u.es_admin).length;
        const vendedores = total - admins;
        const activos = listaUsuariosGlobal.filter(u => String(u.estado).toUpperCase() === 'ACTIVO').length;

        document.getElementById('kpiTotalUsuarios').textContent = total;
        document.getElementById('kpiAdmins').textContent = admins;
        document.getElementById('kpiVendedores').textContent = vendedores;
        document.getElementById('kpiActivos').textContent = activos;
      }

      function renderizarTablaUsuarios() {
        const tbody = document.getElementById('tablaUsuarios');
        const q = (document.getElementById('filtroBusqueda').value || '').trim().toUpperCase();
        const fRol = document.getElementById('filtroRol').value;
        const fSuc = document.getElementById('filtroSucursal').value;

        const filtrados = listaUsuariosGlobal.filter(u => {
          const matchTexto = !q ||
            String(u.nombre_apellido).toUpperCase().includes(q) ||
            String(u.nick_name).toUpperCase().includes(q) ||
            String(u.descripcion_rol).toUpperCase().includes(q);
          const matchRol = !fRol || (fRol === 'ADMIN' ? u.es_admin : !u.es_admin);
          const matchSuc = !fSuc || String(u.id_sucursal) === String(fSuc);
          return matchTexto && matchRol && matchSuc;
        });

        if (filtrados.length === 0) {
          tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">No se encontraron usuarios con ese criterio.</td></tr>`;
          return;
        }

        const totalModulos = Object.keys(catalogoModulosGlobal).length || 15;

        tbody.innerHTML = filtrados.map(u => {
          const esActual = Number(u.cod_usuario) === USUARIO_SESION_ACTUAL;
          const badgeRolClass = u.es_admin
            ? 'bg-primary text-white'
            : (String(u.descripcion_rol).includes('SUPERVISOR') ? 'bg-info text-dark' : 'bg-success-subtle text-success border border-success-subtle');
          const iconoRol = u.es_admin ? 'bi-shield-lock-fill' : 'bi-person-badge';
          const permisos = Array.isArray(u.permisos) ? u.permisos : [];
          const cantPermisos = u.es_admin ? totalModulos : permisos.length;

          const etiquetasPermisos = u.es_admin
            ? `<span class="badge bg-primary-subtle text-primary border border-primary-subtle" title="Acceso total a todos los módulos"><i class="bi bi-check-all me-1"></i>${totalModulos}/${totalModulos}</span>`
            : `<span class="badge bg-light text-dark border" title="${permisos.map(p => catalogoModulosGlobal[p]?.nombre || p).join(', ')}"><i class="bi bi-key me-1 text-primary"></i>${cantPermisos}/${totalModulos}</span>`;

          const activo = String(u.estado).toUpperCase() === 'ACTIVO';
          const badgeEstado = activo
            ? `<span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>ACTIVO</span>`
            : `<span class="badge badge-soft-danger"><i class="bi bi-slash-circle me-1"></i>INACTIVO</span>`;

          return `
            <tr>
              <td class="ps-4 fw-bold text-muted">#${u.cod_usuario}</td>
              <td>
                <div class="fw-bold text-dark">${u.nombre_apellido}</div>
                ${esActual ? '<span class="badge bg-warning-subtle text-warning border border-warning-subtle small">Sesión actual</span>' : ''}
              </td>
              <td>
                <span class="badge bg-dark font-monospace px-2 py-1"><i class="bi bi-person-fill me-1"></i>${u.nick_name}</span>
              </td>
              <td>
                <span class="badge ${badgeRolClass} px-2 py-1">
                  <i class="bi ${iconoRol} me-1"></i>${u.descripcion_rol}
                </span>
              </td>
              <td>
                <span class="text-muted small fw-semibold"><i class="bi bi-shop text-primary me-1"></i>Suc. ${u.id_sucursal}</span>
              </td>
              <td>${etiquetasPermisos}</td>
              <td>${badgeEstado}</td>
              <td class="text-end pe-4">
                <div class="action-icons">
                  <button type="button" class="btn btn-outline-primary btn-icon" onclick='editarUsuario(${JSON.stringify(u)})' title="Configurar Accesos y Editar Usuario">
                    <i class="bi bi-shield-lock-fill"></i>
                  </button>
                  <button type="button" class="btn btn-outline-${activo ? 'warning' : 'success'} btn-icon" onclick="cambiarEstadoUsuario(${u.cod_usuario}, '${activo ? 'INACTIVO' : 'ACTIVO'}')" title="${activo ? 'Inactivar Cuenta' : 'Activar Cuenta'}">
                    <i class="bi bi-${activo ? 'person-slash' : 'person-check-fill'}"></i>
                  </button>
                  ${!esActual ? `
                  <button type="button" class="btn btn-outline-danger btn-icon" onclick="eliminarUsuario(${u.cod_usuario}, '${u.nick_name}')" title="Eliminar Usuario">
                    <i class="bi bi-trash"></i>
                  </button>` : ''}
                </div>
              </td>
            </tr>
          `;
        }).join('');
      }

      function toggleVerPassword() {
        const inp = document.getElementById('password_usuario');
        const ico = document.getElementById('iconoVerPass');
        if (inp.type === 'password') {
          inp.type = 'text';
          ico.className = 'bi bi-eye-slash';
        } else {
          inp.type = 'password';
          ico.className = 'bi bi-eye';
        }
      }

      function marcarCheckboxesPermisos(listaPermisos) {
        const setP = new Set(listaPermisos || []);
        document.querySelectorAll('.check-permiso').forEach(chk => {
          chk.checked = setP.has(chk.value);
        });
        actualizarContadorPermisos();
      }

      function obtenerPermisosMarcados() {
        const seleccionados = [];
        document.querySelectorAll('.check-permiso:checked').forEach(chk => {
          seleccionados.push(chk.value);
        });
        return seleccionados;
      }

      function actualizarContadorPermisos() {
        const cant = document.querySelectorAll('.check-permiso:checked').length;
        document.getElementById('contadorPermisos').textContent = cant;
      }

      function aplicarPlantillaPermisos(tipo) {
        const todos = Array.from(document.querySelectorAll('.check-permiso')).map(c => c.value);
        if (tipo === 'ADMIN') {
          marcarCheckboxesPermisos(todos);
        } else if (tipo === 'SUPERVISOR') {
          marcarCheckboxesPermisos([
            'dashboard', 'facturacion', 'ventas_historial', 'caja', 'cuentas_cobrar',
            'compras', 'productos', 'ajuste_stock', 'clientes', 'proveedores',
            'cambiar_sucursal', 'anular_comprobantes'
          ]);
        } else {
          // VENDEDOR / CAJERA POS
          marcarCheckboxesPermisos([
            'facturacion', 'ventas_historial', 'caja', 'cuentas_cobrar', 'clientes', 'productos'
          ]);
        }
      }

      function alCambiarRolSelect() {
        const sel = document.getElementById('id_rol');
        const textoRol = (sel.options[sel.selectedIndex]?.text || '').toUpperCase();
        const valRol = Number(sel.value);
        if (valRol === 1 || valRol === 2 || textoRol.includes('ADMIN')) {
          aplicarPlantillaPermisos('ADMIN');
        } else if (valRol === 4 || textoRol.includes('SUPERVISOR')) {
          aplicarPlantillaPermisos('SUPERVISOR');
        } else {
          aplicarPlantillaPermisos('VENDEDOR');
        }
      }

      function abrirModalUsuario() {
        document.getElementById('formUsuario').reset();
        document.getElementById('cod_usuario').value = '';
        document.getElementById('tituloModalUsuario').innerHTML = '<i class="bi bi-person-plus-fill me-2"></i>Nuevo Usuario y Configuración de Accesos';
        document.getElementById('password_usuario').required = true;
        document.getElementById('lblPassword').innerHTML = 'Contraseña <span class="text-danger">*</span>';
        document.getElementById('ayudaPassword').textContent = 'Ingrese la clave con la que el usuario iniciará sesión (ej: 123).';

        // Por defecto seleccionar VENDEDOR si existe (id_rol 6 o 5)
        const selRol = document.getElementById('id_rol');
        if ([...selRol.options].some(o => o.value === '6')) {
          selRol.value = '6';
        } else if ([...selRol.options].some(o => o.value === '5')) {
          selRol.value = '5';
        }
        aplicarPlantillaPermisos('VENDEDOR');
        modalUsuarioObj.show();
      }

      function editarUsuario(u) {
        document.getElementById('formUsuario').reset();
        document.getElementById('cod_usuario').value = u.cod_usuario;
        document.getElementById('nombre_apellido').value = u.nombre_apellido;
        document.getElementById('nick_name').value = u.nick_name;
        document.getElementById('id_rol').value = u.id_rol;
        document.getElementById('id_sucursal').value = u.id_sucursal;
        document.getElementById('estado_usuario').value = u.estado || 'ACTIVO';
        document.getElementById('password_usuario').value = '';
        document.getElementById('password_usuario').required = false;
        document.getElementById('lblPassword').innerHTML = 'Nueva Contraseña (Opcional)';
        document.getElementById('ayudaPassword').textContent = 'Déjelo en blanco para mantener la contraseña actual.';

        document.getElementById('tituloModalUsuario').innerHTML = `<i class="bi bi-shield-lock-fill me-2"></i>Accesos y Perfil de: ${u.nick_name}`;

        if (u.es_admin && (!u.permisos || u.permisos.length === 0)) {
          aplicarPlantillaPermisos('ADMIN');
        } else {
          marcarCheckboxesPermisos(u.permisos || []);
        }

        modalUsuarioObj.show();
      }

      async function guardarUsuario(e) {
        e.preventDefault();
        const btn = document.getElementById('btnGuardarUsuario');
        btn.disabled = true;

        const fd = new FormData(document.getElementById('formUsuario'));
        fd.append('accion', 'guardar');
        fd.append('permisos', JSON.stringify(obtenerPermisosMarcados()));

        try {
          const res = await fetch(`${BASE_URL}controladores/UsuariosControlador.php`, {
            method: 'POST',
            body: fd
          });
          const json = await res.json();
          if (json.status === 'success') {
            modalUsuarioObj.hide();
            Swal.fire({
              icon: 'success',
              title: 'Guardado',
              text: json.message,
              timer: 1500,
              showConfirmButton: false
            });
            cargarUsuarios();
          } else {
            Swal.fire('Atención', json.message || 'No se pudo guardar el usuario.', 'warning');
          }
        } catch (err) {
          Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
        } finally {
          btn.disabled = false;
        }
      }

      async function cambiarEstadoUsuario(id, nuevoEstado) {
        const fd = new FormData();
        fd.append('accion', 'cambiar_estado');
        fd.append('cod_usuario', id);
        fd.append('estado', nuevoEstado);

        const res = await fetch(`${BASE_URL}controladores/UsuariosControlador.php`, { method: 'POST', body: fd });
        const json = await res.json();
        if (json.status === 'success') {
          cargarUsuarios();
        } else {
          Swal.fire('Error', json.message, 'error');
        }
      }

      function eliminarUsuario(id, nick) {
        Swal.fire({
          title: `¿Eliminar usuario ${nick}?`,
          text: 'Si el usuario tiene ventas o cajas registradas, se desactivará automáticamente para resguardar el historial.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          confirmButtonText: 'Sí, continuar',
          cancelButtonText: 'Cancelar'
        }).then(async (result) => {
          if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('accion', 'eliminar');
            fd.append('cod_usuario', id);
            const res = await fetch(`${BASE_URL}controladores/UsuariosControlador.php`, { method: 'POST', body: fd });
            const json = await res.json();
            if (json.status === 'success') {
              Swal.fire('Listo', json.message, 'success');
              cargarUsuarios();
            } else {
              Swal.fire('Atención', json.message, 'warning');
            }
          }
        });
      }
      </script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

