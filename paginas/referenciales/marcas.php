<?php
$page_title = 'Gestión de Marcas';
$active_menu = 'marcas';
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
            <li class="breadcrumb-item active" aria-current="page">Marcas</li>
          </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Marcas de Productos</h1>
        <p class="text-muted small mb-0">Administra los fabricantes y marcas del catálogo de mercaderías.</p>
      </div>
      <div>
        <button type="button" class="btn btn-primary btn-icon-lg shadow-sm" onclick="abrirModalNuevo()" title="Nueva Marca">
          <i class="bi bi-plus-lg"></i>
        </button>
      </div>
    </div>

    <!-- Tarjeta de Contenido -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded">Listado General</span>
          <span class="text-muted small" id="contadorMarcas">Cargando...</span>
        </div>
        <div class="input-group" style="max-width: 280px;">
          <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" id="inputBuscar" class="form-control border-start-0 bg-light" placeholder="Buscar por nombre..." onkeyup="cargarMarcas()">
        </div>
      </div>
      
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 80px;" class="text-center">ID</th>
                <th>Descripción / Marca</th>
                <th style="width: 140px;" class="text-center">Estado</th>
                <th style="width: 160px;" class="text-end pe-4">Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaMarcas">
              <tr>
                <td colspan="4" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Cargando marcas...
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
<div class="modal fade" id="modalMarca" tabindex="-1" aria-labelledby="modalMarcaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="modalMarcaLabel">Nueva Marca</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formMarca" onsubmit="guardarMarca(event)">
        <div class="modal-body py-4">
          <input type="hidden" id="id_marcas" name="id_marcas">
          
          <div class="mb-3">
            <label for="descripcion" class="form-label fw-semibold">Nombre de la Marca <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Ej: Samsung, Apple, Xiaomi, Motorola..." required>
          </div>

          <div class="mb-3">
            <label for="estado" class="form-label fw-semibold">Estado</label>
            <select class="form-select" id="estado" name="estado">
              <option value="ACTIVO">ACTIVO</option>
              <option value="INACTIVO">INACTIVO</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary px-4" id="btnGuardar">
            <i class="bi bi-check-lg me-1"></i> Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const BASE_URL_CTRL = '<?= BASE_URL ?>controladores/MarcasControlador.php';
let modalMarca = null;

document.addEventListener('DOMContentLoaded', () => {
  modalMarca = new bootstrap.Modal(document.getElementById('modalMarca'));
  cargarMarcas();
});

async function cargarMarcas() {
  const buscar = document.getElementById('inputBuscar').value.trim();
  const tbody = document.getElementById('tablaMarcas');
  
  try {
    const res = await fetch(`${BASE_URL_CTRL}?accion=listar&buscar=${encodeURIComponent(buscar)}`);
    const json = await res.json();

    if (json.status === 'success') {
      const data = json.data;
      document.getElementById('contadorMarcas').textContent = `${data.length} registradas`;

      if (data.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="4" class="text-center py-5 text-muted">
              <i class="bi bi-inbox fs-2 d-block mb-2"></i>
              No se encontraron marcas registradas.
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

        html += `
          <tr>
            <td class="text-center fw-semibold text-muted">#${item.id_marcas}</td>
            <td class="fw-semibold text-dark">
              <i class="bi bi-tag me-2 text-primary"></i>${escapeHtml(item.descripcion)}
            </td>
            <td class="text-center">
              <span class="badge ${badgeClass} px-3 py-1 rounded-pill">${item.estado}</span>
            </td>
            <td class="text-end pe-4">
              <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-light" title="${textoTituloEstado}" onclick="cambiarEstado(${item.id_marcas}, '${item.estado}')">
                  <i class="bi ${iconoEstado} fs-6"></i>
                </button>
                <button type="button" class="btn btn-light text-primary" title="Editar" onclick='abrirModalEditar(${jsonAttr(item)})'>
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button type="button" class="btn btn-light text-danger" title="Eliminar" onclick="eliminarMarca(${item.id_marcas}, '${escapeHtml(item.descripcion)}')">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>`;
      });
      tbody.innerHTML = html;
    } else {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">${json.message}</td></tr>`;
    }
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">Error al conectar con el servidor.</td></tr>`;
  }
}

function abrirModalNuevo() {
  document.getElementById('formMarca').reset();
  document.getElementById('id_marcas').value = '';
  document.getElementById('modalMarcaLabel').textContent = 'Nueva Marca';
  document.getElementById('estado').value = 'ACTIVO';
  modalMarca.show();
  setTimeout(() => document.getElementById('descripcion').focus(), 300);
}

function abrirModalEditar(item) {
  document.getElementById('id_marcas').value = item.id_marcas;
  document.getElementById('descripcion').value = item.descripcion;
  document.getElementById('estado').value = item.estado;
  document.getElementById('modalMarcaLabel').textContent = 'Editar Marca #' + item.id_marcas;
  modalMarca.show();
  setTimeout(() => document.getElementById('descripcion').focus(), 300);
}

async function guardarMarca(e) {
  e.preventDefault();
  const btn = document.getElementById('btnGuardar');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

  const formData = new FormData(document.getElementById('formMarca'));
  formData.append('accion', 'guardar');

  try {
    const res = await fetch(BASE_URL_CTRL, { method: 'POST', body: formData });
    const json = await res.json();

    if (json.status === 'success') {
      modalMarca.hide();
      Swal.fire({ icon: 'success', title: '¡Éxito!', text: json.message, timer: 1500, showConfirmButton: false });
      cargarMarcas();
    } else {
      Swal.fire({ icon: 'error', title: 'Atención', text: json.message });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error al guardar la marca.' });
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar';
  }
}

async function cambiarEstado(id, estadoActual) {
  const formData = new FormData();
  formData.append('accion', 'cambiar_estado');
  formData.append('id_marcas', id);
  formData.append('estado', estadoActual);

  try {
    const res = await fetch(BASE_URL_CTRL, { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
      cargarMarcas();
    }
  } catch (err) {
    console.error(err);
  }
}

async function eliminarMarca(id, nombre) {
  const confirmacion = await Swal.fire({
    title: '¿Eliminar marca?',
    html: `¿Estás seguro de que deseas eliminar la marca <strong>${nombre}</strong>?`,
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
    formData.append('id_marcas', id);

    try {
      const res = await fetch(BASE_URL_CTRL, { method: 'POST', body: formData });
      const json = await res.json();

      if (json.status === 'success') {
        Swal.fire({ icon: 'success', title: 'Eliminado', text: json.message, timer: 1500, showConfirmButton: false });
        cargarMarcas();
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
