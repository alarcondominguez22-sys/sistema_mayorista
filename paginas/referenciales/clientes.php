<?php
$page_title = 'Gestión de Clientes';
$active_menu = 'clientes';
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
            <li class="breadcrumb-item active" aria-current="page">Clientes</li>
          </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Clientes Registrados</h1>
        <p class="text-muted small mb-0">Directorio de compradores mayoristas, minoristas y particulares.</p>
      </div>
      <div>
        <button type="button" class="btn btn-primary btn-icon-lg shadow-sm" onclick="abrirModalNuevo()" title="Nuevo Cliente">
          <i class="bi bi-person-plus-fill"></i>
        </button>
      </div>
    </div>

    <!-- Tarjeta Principal -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded">Cartera de Clientes</span>
          <span class="text-muted small" id="contadorClientes">Cargando...</span>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <select id="filtroTipoCliente" class="form-select form-select-sm" style="width: 190px;" onchange="cargarClientes()">
            <option value="">Todos los tipos</option>
            <option value="MAYORISTA">🏢 Mayorista</option>
            <option value="CLIENTE FIEL">⭐ Cliente Fiel</option>
            <option value="MINORISTA">👤 Minorista</option>
          </select>
          <div class="input-group input-group-sm" style="width: 300px;">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="inputBuscar" class="form-control border-start-0 bg-light" placeholder="Buscar por nombre, RUC/CI o teléfono..." onkeyup="cargarClientes()">
          </div>
        </div>
      </div>
      
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 70px;" class="text-center">ID</th>
                <th>Cliente / Razón Social</th>
                <th>Tipo de Cliente</th>
                <th>Cédula / RUC</th>
                <th>Teléfono</th>
                <th>Ciudad / Dirección</th>
                <th style="width: 120px;" class="text-center">Estado</th>
                <th style="width: 150px;" class="text-end pe-4">Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaClientes">
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Cargando clientes...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Modal Crear / Editar -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="modalClienteLabel">Nuevo Cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formCliente" onsubmit="guardarCliente(event)">
        <div class="modal-body py-4">
          <input type="hidden" id="id_cliente" name="id_cliente">
          
          <div class="row g-3">
            <!-- Selector de Tipo de Cliente -->
            <div class="col-12">
              <label class="form-label fw-semibold mb-2">Categoría / Tipo de Cliente <span class="text-danger">*</span></label>
              <input type="hidden" id="tipo_cliente" name="tipo_cliente" value="MAYORISTA">
              <div class="row g-2">
                <div class="col-md-4">
                  <div class="border rounded-3 p-2 px-3 d-flex align-items-center gap-2 opcion-tipo-cliente" id="card_tipo_MAYORISTA" style="cursor: pointer;" onclick="seleccionarTipoClienteUI('MAYORISTA')">
                    <i class="bi bi-building-check fs-4 text-primary"></i>
                    <div>
                      <div class="fw-bold small">Mayorista</div>
                      <small class="text-muted" style="font-size: 0.72rem;">Compras por volumen / RUC</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="border rounded-3 p-2 px-3 d-flex align-items-center gap-2 opcion-tipo-cliente" id="card_tipo_CLIENTE_FIEL" style="cursor: pointer;" onclick="seleccionarTipoClienteUI('CLIENTE FIEL')">
                    <i class="bi bi-star-fill fs-4 text-warning"></i>
                    <div>
                      <div class="fw-bold small">Cliente Fiel</div>
                      <small class="text-muted" style="font-size: 0.72rem;">Comprador frecuente / Preferencial</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="border rounded-3 p-2 px-3 d-flex align-items-center gap-2 opcion-tipo-cliente" id="card_tipo_MINORISTA" style="cursor: pointer;" onclick="seleccionarTipoClienteUI('MINORISTA')">
                    <i class="bi bi-person-badge fs-4 text-secondary"></i>
                    <div>
                      <div class="fw-bold small">Minorista / Particular</div>
                      <small class="text-muted" style="font-size: 0.72rem;">Cliente ocasional</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <label for="nombre_cliente" class="form-label fw-semibold">Nombre / Razón Social <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" placeholder="Ej: Juan Carlos / Comercial El Sol" required>
            </div>
            <div class="col-md-6">
              <label for="apellido_cliente" class="form-label fw-semibold">Apellido</label>
              <input type="text" class="form-control" id="apellido_cliente" name="apellido_cliente" placeholder="Ej: Gómez (opcional para empresas)">
            </div>

            <div class="col-md-6">
              <label for="cedula_cliente" class="form-label fw-semibold">Cédula de Identidad o RUC <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cedula_cliente" name="cedula_cliente" placeholder="Ej: 4048095 o 80012345-6" required>
            </div>
            <div class="col-md-6">
              <label for="telefono_cliente" class="form-label fw-semibold">Teléfono / WhatsApp</label>
              <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente" placeholder="Ej: 0981 123 456">
            </div>

            <div class="col-md-6">
              <label for="id_ciudad" class="form-label fw-semibold">Ciudad</label>
              <select class="form-select" id="id_ciudad" name="id_ciudad">
                <option value="">Cargando ciudades...</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="correo" class="form-label fw-semibold">Correo Electrónico</label>
              <input type="email" class="form-control" id="correo" name="correo" placeholder="cliente@ejemplo.com">
            </div>

            <div class="col-md-8">
              <label for="direccion_cliente" class="form-label fw-semibold">Dirección</label>
              <input type="text" class="form-control" id="direccion_cliente" name="direccion_cliente" placeholder="Calle, Barrio, Número de casa">
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
            <i class="bi bi-check-lg me-1"></i> Guardar Cliente
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const BASE_URL_CTRL = '<?= BASE_URL ?>controladores/ClientesControlador.php';
const BASE_URL_CIU = '<?= BASE_URL ?>controladores/SucursalesControlador.php';
let modalCliente = null;

document.addEventListener('DOMContentLoaded', async () => {
  modalCliente = new bootstrap.Modal(document.getElementById('modalCliente'));
  await cargarComboCiudades();
  seleccionarTipoClienteUI('MAYORISTA');
  cargarClientes();
});

function seleccionarTipoClienteUI(tipo) {
  const tipoVal = (tipo || 'MAYORISTA').toUpperCase();
  document.getElementById('tipo_cliente').value = tipoVal;
  document.querySelectorAll('.opcion-tipo-cliente').forEach(el => {
    el.classList.remove('border-primary', 'bg-primary-subtle', 'shadow-sm');
  });
  const key = tipoVal.replace(/\s+/g, '_');
  const activeCard = document.getElementById('card_tipo_' + key) || document.getElementById('card_tipo_MAYORISTA');
  if (activeCard) {
    activeCard.classList.add('border-primary', 'bg-primary-subtle', 'shadow-sm');
  }
}

function renderBadgeTipoCliente(tipo) {
  const t = (tipo || 'MAYORISTA').toUpperCase();
  if (t === 'CLIENTE FIEL') {
    return `<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill fw-semibold"><i class="bi bi-star-fill me-1"></i>CLIENTE FIEL</span>`;
  }
  if (t === 'MINORISTA') {
    return `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill fw-semibold"><i class="bi bi-person me-1"></i>MINORISTA</span>`;
  }
  return `<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold"><i class="bi bi-building-check me-1"></i>MAYORISTA</span>`;
}

async function cargarComboCiudades() {
  try {
    const res = await fetch(`${BASE_URL_CIU}?accion=listar_ciudades`);
    const json = await res.json();
    if (json.status === 'success') {
      const select = document.getElementById('id_ciudad');
      select.innerHTML = '<option value="">Seleccione una ciudad...</option>';
      json.data.forEach(c => {
        select.innerHTML += `<option value="${c.id_ciudad}">${escapeHtml(c.nombre_ciudad)}</option>`;
      });
    }
  } catch (err) {
    console.error('Error al cargar ciudades', err);
  }
}

async function cargarClientes() {
  const buscar = document.getElementById('inputBuscar').value.trim();
  const tipoFiltro = document.getElementById('filtroTipoCliente').value;
  const tbody = document.getElementById('tablaClientes');
  
  try {
    const res = await fetch(`${BASE_URL_CTRL}?accion=listar&buscar=${encodeURIComponent(buscar)}&tipo_cliente=${encodeURIComponent(tipoFiltro)}`);
    const json = await res.json();

    if (json.status === 'success') {
      const data = json.data;
      document.getElementById('contadorClientes').textContent = `${data.length} registrados`;

      if (data.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" class="text-center py-5 text-muted">
              <i class="bi bi-people fs-2 d-block mb-2"></i>
              No se encontraron clientes registrados.
            </td>
          </tr>`;
        return;
      }

      let html = '';
      data.forEach(item => {
        const esActivo = item.estado === 'ACTIVO';
        const badgeClass = esActivo ? 'badge-soft-success' : 'badge-soft-danger';
        const iconoEstado = esActivo ? 'bi-toggle-on text-success' : 'bi-toggle-off text-muted';
        const textoTituloEstado = esActivo ? 'Desactivar' : 'Activar';
        const nombreCompleto = `${item.nombre_cliente} ${item.apellido_cliente || ''}`.trim();

        html += `
          <tr>
            <td class="text-center fw-semibold text-muted">#${item.id_cliente}</td>
            <td>
              <div class="fw-bold text-dark">${escapeHtml(nombreCompleto)}</div>
              ${item.correo ? `<small class="text-muted"><i class="bi bi-envelope me-1"></i>${escapeHtml(item.correo)}</small>` : ''}
            </td>
            <td>
              ${renderBadgeTipoCliente(item.tipo_cliente)}
            </td>
            <td>
              <span class="badge bg-light text-dark border font-monospace">
                <i class="bi bi-card-text me-1 text-primary"></i>${escapeHtml(item.cedula_cliente)}
              </span>
            </td>
            <td>
              ${item.telefono_cliente ? `<span class="small text-muted"><i class="bi bi-telephone me-1 text-success"></i>${escapeHtml(item.telefono_cliente)}</span>` : '<span class="text-muted small">-</span>'}
            </td>
            <td>
              <div class="small fw-semibold text-dark">${escapeHtml(item.nombre_ciudad || 'Sin ciudad')}</div>
              <div class="small text-muted text-truncate" style="max-width: 200px;">${escapeHtml(item.direccion_cliente || '')}</div>
            </td>
            <td class="text-center">
              <span class="badge ${badgeClass} px-3 py-1 rounded-pill">${item.estado}</span>
            </td>
            <td class="text-end pe-4">
              <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-light" title="${textoTituloEstado}" onclick="cambiarEstado(${item.id_cliente}, '${item.estado}')">
                  <i class="bi ${iconoEstado} fs-6"></i>
                </button>
                <button type="button" class="btn btn-light text-primary" title="Editar" onclick='abrirModalEditar(${jsonAttr(item)})'>
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button type="button" class="btn btn-light text-danger" title="Eliminar" onclick="eliminarCliente(${item.id_cliente}, '${escapeHtml(nombreCompleto)}')">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>`;
      });
      tbody.innerHTML = html;
    } else {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">${json.message}</td></tr>`;
    }
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Error al conectar con el servidor.</td></tr>`;
  }
}

function abrirModalNuevo() {
  document.getElementById('formCliente').reset();
  document.getElementById('id_cliente').value = '';
  document.getElementById('modalClienteLabel').textContent = 'Nuevo Cliente';
  document.getElementById('estado').value = 'ACTIVO';
  seleccionarTipoClienteUI('MAYORISTA');
  modalCliente.show();
  setTimeout(() => document.getElementById('nombre_cliente').focus(), 300);
}

function abrirModalEditar(item) {
  document.getElementById('id_cliente').value = item.id_cliente;
  document.getElementById('nombre_cliente').value = item.nombre_cliente;
  document.getElementById('apellido_cliente').value = item.apellido_cliente || '';
  document.getElementById('cedula_cliente').value = item.cedula_cliente;
  document.getElementById('telefono_cliente').value = item.telefono_cliente || '';
  document.getElementById('id_ciudad').value = item.id_ciudad || '';
  document.getElementById('direccion_cliente').value = item.direccion_cliente || '';
  document.getElementById('correo').value = item.correo || '';
  document.getElementById('estado').value = item.estado;
  seleccionarTipoClienteUI(item.tipo_cliente || 'MAYORISTA');
  document.getElementById('modalClienteLabel').textContent = 'Editar Cliente #' + item.id_cliente;
  modalCliente.show();
  setTimeout(() => document.getElementById('nombre_cliente').focus(), 300);
}

async function guardarCliente(e) {
  e.preventDefault();
  const btn = document.getElementById('btnGuardar');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

  const formData = new FormData(document.getElementById('formCliente'));
  formData.append('accion', 'guardar');

  try {
    const res = await fetch(BASE_URL_CTRL, { method: 'POST', body: formData });
    const json = await res.json();

    if (json.status === 'success') {
      modalCliente.hide();
      Swal.fire({ icon: 'success', title: '¡Éxito!', text: json.message, timer: 1500, showConfirmButton: false });
      cargarClientes();
    } else {
      Swal.fire({ icon: 'error', title: 'Atención', text: json.message });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error al guardar el cliente.' });
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar Cliente';
  }
}

async function cambiarEstado(id, estadoActual) {
  const formData = new FormData();
  formData.append('accion', 'cambiar_estado');
  formData.append('id_cliente', id);
  formData.append('estado', estadoActual);

  try {
    const res = await fetch(BASE_URL_CTRL, { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
      cargarClientes();
    }
  } catch (err) {
    console.error(err);
  }
}

async function eliminarCliente(id, nombre) {
  const confirmacion = await Swal.fire({
    title: '¿Eliminar cliente?',
    html: `¿Estás seguro de que deseas eliminar al cliente <strong>${nombre}</strong>?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  });

  if (confirmacion.isConfirmed) {
    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id_cliente', id);

    try {
      const res = await fetch(BASE_URL_CTRL, { method: 'POST', body: formData });
      const json = await res.json();

      if (json.status === 'success') {
        Swal.fire({ icon: 'success', title: 'Eliminado', text: json.message, timer: 1500, showConfirmButton: false });
        cargarClientes();
      } else {
        Swal.fire({ icon: 'error', title: 'No permitido', text: json.message });
      }
    } catch (err) {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Error al procesar la solicitud.' });
    }
  }
}

function jsonAttr(obj) {
  return JSON.stringify(obj)
    .replace(/&/g, "&amp;")
    .replace(/'/g, "&#39;")
    .replace(/"/g, "&quot;");
}

function escapeHtml(text) {
  if (!text) return '';
  return text.toString()
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
