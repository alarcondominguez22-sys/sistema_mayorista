<?php
$page_title = 'Gestión de Proveedores';
$active_menu = 'proveedores';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/navbar.php';
?>

<main class="dashboard-content">
  <div class="container-fluid px-3 px-lg-4 py-4">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Referenciales</li>
            <li class="breadcrumb-item active" aria-current="page">Proveedores</li>
          </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Proveedores & Distribuidores</h1>
        <p class="text-muted small mb-0">Directorio de importadores y empresas proveedoras de mercaderías e insumos.</p>
      </div>
      <div>
        <button type="button" class="btn btn-primary btn-icon-lg shadow-sm" onclick="abrirModalNuevo()" title="Nuevo Proveedor">
          <i class="bi bi-plus-lg"></i>
        </button>
      </div>
    </div>

    <!-- Tarjeta Principal -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded">Listado General</span>
          <span class="text-muted small" id="contadorProveedores">Cargando...</span>
        </div>
        <div class="input-group" style="max-width: 320px;">
          <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" id="inputBuscar" class="form-control border-start-0 bg-light" placeholder="Buscar por Razón Social, RUC o Ciudad..." onkeyup="cargarProveedores()">
        </div>
      </div>
      
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 70px;" class="text-center">ID</th>
                <th>Razón Social / Proveedor</th>
                <th>RUC</th>
                <th>Contacto & Teléfono</th>
                <th>Ciudad & Dirección</th>
                <th>Correo Electrónico</th>
                <th style="width: 120px;" class="text-center">Estado</th>
                <th style="width: 140px;" class="text-end pe-4">Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaProveedores">
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Cargando proveedores...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Modal Crear / Editar Proveedor -->
<div class="modal fade" id="modalProveedor" tabindex="-1" aria-labelledby="modalProveedorLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="modalProveedorLabel">Nuevo Proveedor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formProveedor" onsubmit="guardarProveedor(event)">
        <div class="modal-body py-4">
          <input type="hidden" id="id_proveedor_" name="id_proveedor_">
          
          <div class="row g-3">
            <div class="col-md-6">
              <label for="razon_social" class="form-label fw-semibold">Razón Social / Empresa <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="razon_social" name="razon_social" placeholder="Ej: TECNOMOVIL S.A. / TRIUM DISTRIBUIDORA" required>
            </div>
            <div class="col-md-6">
              <label for="nombre_apellido_prov" class="form-label fw-semibold">Contacto / Representante</label>
              <input type="text" class="form-control" id="nombre_apellido_prov" name="nombre_apellido_prov" placeholder="Ej: Juan Pérez">
            </div>

            <div class="col-md-6">
              <label for="ruc_proveedor" class="form-label fw-semibold">RUC <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="ruc_proveedor" name="ruc_proveedor" placeholder="Ej: 80012345-6" required>
            </div>
            <div class="col-md-6">
              <label for="telefono_proveedor" class="form-label fw-semibold">Teléfono</label>
              <input type="text" class="form-control" id="telefono_proveedor" name="telefono_proveedor" placeholder="Ej: 0981121200">
            </div>

            <div class="col-md-6">
              <label for="id_ciudad" class="form-label fw-semibold">Ciudad</label>
              <select class="form-select" id="id_ciudad" name="id_ciudad">
                <option value="">Seleccione una ciudad</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="email_proveedor" class="form-label fw-semibold">Correo Electrónico</label>
              <input type="email" class="form-control" id="email_proveedor" name="email_proveedor" placeholder="ventas@proveedor.com">
            </div>

            <div class="col-md-8">
              <label for="direccion_proveedor" class="form-label fw-semibold">Dirección Comercial</label>
              <input type="text" class="form-control" id="direccion_proveedor" name="direccion_proveedor" placeholder="Ej: Avda. Rodríguez de Francia c/ Battilana">
            </div>
            <div class="col-md-4">
              <label for="estado" class="form-label fw-semibold">Estado</label>
              <select class="form-select" id="estado" name="estado">
                <option value="ACTIVO">ACTIVO</option>
                <option value="INACTIVO">INACTIVO</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary px-4" id="btnGuardar">
            <i class="bi bi-check-lg me-1"></i> Guardar Proveedor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const BASE_URL_CTRL = '<?= BASE_URL ?>controladores/ProveedoresControlador.php';
let modalProveedor = null;
let listaProveedores = [];

document.addEventListener('DOMContentLoaded', () => {
  modalProveedor = new bootstrap.Modal(document.getElementById('modalProveedor'));
  cargarCiudades();
  cargarProveedores();
});

async function cargarCiudades() {
  try {
    const res = await fetch(`${BASE_URL_CTRL}?accion=listar_ciudades`);
    const json = await res.json();
    if (json.status === 'success') {
      const sel = document.getElementById('id_ciudad');
      sel.innerHTML = '<option value="">Seleccione una ciudad</option>';
      json.ciudades.forEach(c => {
        sel.innerHTML += `<option value="${c.id_ciudad}">${c.nombre_ciudad}</option>`;
      });
    }
  } catch (err) {
    console.error('Error cargando ciudades:', err);
  }
}

async function cargarProveedores() {
  const buscar = document.getElementById('inputBuscar').value.trim();
  const tbody = document.getElementById('tablaProveedores');
  const contador = document.getElementById('contadorProveedores');
  
  try {
    const res = await fetch(`${BASE_URL_CTRL}?accion=listar&buscar=${encodeURIComponent(buscar)}`);
    const json = await res.json();
    
    if (json.status === 'success') {
      listaProveedores = json.data;
      contador.innerText = `${json.total} registrados`;
      
      if (json.data.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" class="text-center py-4 text-muted">
              No se encontraron proveedores registrados.
            </td>
          </tr>
        `;
        return;
      }

      let html = '';
      json.data.forEach(p => {
        const estadoBadge = p.estado === 'ACTIVO' 
          ? '<span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-check-circle me-1"></i> ACTIVO</span>'
          : '<span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i> INACTIVO</span>';

        html += `
          <tr>
            <td class="text-center text-muted fw-bold">${p.id_proveedor_}</td>
            <td>
              <div class="fw-bold text-dark">${escapeHtml(p.razon_social || p.nombre_apellido_prov)}</div>
              ${p.razon_social && p.nombre_apellido_prov ? `<small class="text-muted d-block">${escapeHtml(p.nombre_apellido_prov)}</small>` : ''}
            </td>
            <td><span class="badge bg-light text-dark border font-monospace">${escapeHtml(p.ruc_proveedor)}</span></td>
            <td>
              <div class="small fw-semibold text-dark"><i class="bi bi-telephone me-1 text-muted"></i>${p.telefono_proveedor ? escapeHtml(p.telefono_proveedor.toString()) : 'S/T'}</div>
            </td>
            <td>
              <div class="small fw-semibold text-secondary">${escapeHtml(p.nombre_ciudad || 'Sin ciudad')}</div>
              <small class="text-muted text-truncate d-block" style="max-width: 220px;">${escapeHtml(p.direccion_proveedor || '-')}</small>
            </td>
            <td>
              <small class="text-muted">${p.email_proveedor ? `<i class="bi bi-envelope me-1"></i>${escapeHtml(p.email_proveedor)}` : '-'}</small>
            </td>
            <td class="text-center">${estadoBadge}</td>
            <td class="text-end pe-4">
              <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary" title="Editar" onclick="abrirModalEditar(${p.id_proveedor_})">
                  <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-outline-warning" title="Cambiar Estado" onclick="cambiarEstado(${p.id_proveedor_}, '${p.estado}')">
                  <i class="bi bi-arrow-repeat"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" title="Eliminar" onclick="eliminarProveedor(${p.id_proveedor_}, '${escapeHtml(p.razon_social || p.nombre_apellido_prov)}')">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        `;
      });
      tbody.innerHTML = html;
    } else {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">${json.message}</td></tr>`;
    }
  } catch (err) {
    console.error(err);
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Error al cargar proveedores.</td></tr>`;
  }
}

function abrirModalNuevo() {
  document.getElementById('formProveedor').reset();
  document.getElementById('id_proveedor_').value = '';
  document.getElementById('modalProveedorLabel').innerText = 'Nuevo Proveedor';
  modalProveedor.show();
}

function abrirModalEditar(id) {
  const p = listaProveedores.find(item => item.id_proveedor_ == id);
  if (!p) return;

  document.getElementById('formProveedor').reset();
  document.getElementById('id_proveedor_').value = p.id_proveedor_;
  document.getElementById('razon_social').value = p.razon_social || '';
  document.getElementById('nombre_apellido_prov').value = p.nombre_apellido_prov || '';
  document.getElementById('ruc_proveedor').value = p.ruc_proveedor || '';
  document.getElementById('telefono_proveedor').value = p.telefono_proveedor || '';
  document.getElementById('id_ciudad').value = p.id_ciudad || '';
  document.getElementById('direccion_proveedor').value = p.direccion_proveedor || '';
  document.getElementById('email_proveedor').value = p.email_proveedor || '';
  document.getElementById('estado').value = p.estado || 'ACTIVO';
  
  document.getElementById('modalProveedorLabel').innerText = `Editar Proveedor #${p.id_proveedor_}`;
  modalProveedor.show();
}

async function guardarProveedor(e) {
  e.preventDefault();
  const btn = document.getElementById('btnGuardar');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

  const formData = new FormData(document.getElementById('formProveedor'));
  formData.append('accion', 'guardar');

  try {
    const res = await fetch(BASE_URL_CTRL, { method: 'POST', body: formData });
    const json = await res.json();

    if (json.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: json.message,
        timer: 1600,
        showConfirmButton: false
      });
      modalProveedor.hide();
      cargarProveedores();
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Atención',
        text: json.message
      });
    }
  } catch (err) {
    Swal.fire('Error', 'Error de conexión con el servidor', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar Proveedor';
  }
}

async function cambiarEstado(id, estadoActual) {
  const formData = new FormData();
  formData.append('accion', 'cambiar_estado');
  formData.append('id_proveedor_', id);
  formData.append('estado', estadoActual);

  try {
    const res = await fetch(BASE_URL_CTRL, { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
      cargarProveedores();
    } else {
      Swal.fire('Error', json.message, 'error');
    }
  } catch (err) {
    Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
  }
}

async function eliminarProveedor(id, nombre) {
  const result = await Swal.fire({
    title: '¿Eliminar proveedor?',
    text: `¿Estás seguro de eliminar a "${nombre}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  });

  if (!result.isConfirmed) return;

  const formData = new FormData();
  formData.append('accion', 'eliminar');
  formData.append('id_proveedor_', id);

  try {
    const res = await fetch(BASE_URL_CTRL, { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
      Swal.fire({ icon: 'success', title: 'Eliminado', text: json.message, timer: 1500, showConfirmButton: false });
      cargarProveedores();
    } else {
      Swal.fire('Atención', json.message, 'warning');
    }
  } catch (err) {
    Swal.fire('Error', 'Error al procesar eliminación.', 'error');
  }
}

function escapeHtml(text) {
  if (!text) return '';
  return text.toString()
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
