<?php
$page_title = 'Ajuste de Inventario & Stock';
$active_menu = 'ajuste_stock';
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
            <li class="breadcrumb-item text-muted">Movimientos</li>
            <li class="breadcrumb-item active" aria-current="page">Ajuste de Stock</li>
          </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Ajuste de Inventario & Stock</h1>
        <p class="text-muted small mb-0">Corrección manual de diferencias de inventario físico, mermas, averías y sobrantes.</p>
      </div>
      <div>
        <button type="button" class="btn btn-primary btn-icon-lg shadow-sm" onclick="abrirModalAjuste()" title="Registrar Ajuste de Stock">
          <i class="bi bi-plus-lg"></i>
        </button>
      </div>
    </div>

    <!-- Tarjeta Principal con Historial -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded">Movimientos de Inventario</span>
        <button class="btn btn-outline-secondary btn-icon" onclick="cargarAjustes()" title="Actualizar">
          <i class="bi bi-arrow-clockwise"></i>
        </button>
      </div>
      
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width: 70px;">ID</th>
                <th>Fecha</th>
                <th>Sucursal</th>
                <th>Producto</th>
                <th class="text-center">Tipo</th>
                <th class="text-center">Stock Anterior</th>
                <th class="text-center">Stock Nuevo</th>
                <th>Motivo / Justificación</th>
              </tr>
            </thead>
            <tbody id="tablaAjustes">
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Cargando registros de ajuste...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- Modal Registrar Ajuste de Stock -->
<div class="modal fade" id="modalAjuste" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold"><i class="bi bi-sliders text-primary me-2"></i> Nuevo Ajuste de Stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formAjuste" onsubmit="guardarAjuste(event)">
        <div class="modal-body py-4">
          
          <div class="mb-3">
            <label class="form-label fw-semibold">Sucursal a Ajustar <span class="text-danger">*</span></label>
            <select class="form-select" id="ajuste_sucursal" name="id_sucursal" onchange="actualizarStockActualProducto()">
              <option value="1">SUCURSAL 1 (Avda Lopez Godoy)</option>
              <option value="2">SUCURSAL 2 (Avda Mcal Lopez)</option>
            </select>
          </div>

          <div class="mb-3 position-relative">
            <label class="form-label fw-semibold">Producto <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" id="inputBuscarProdAjuste" class="form-control" placeholder="Escriba para buscar producto..." onkeyup="buscarProdAjuste(event)">
            </div>
            <div id="dropdownAjuste" class="search-result-dropdown d-none"></div>
            <input type="hidden" id="ajuste_id_producto" name="id_producto" required>
            <div id="lblProdSeleccionado" class="small fw-bold text-primary mt-1 d-none"></div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Tipo de Ajuste</label>
              <select class="form-select" id="ajuste_tipo" name="tipo_ajuste" onchange="previsualizarAjuste()">
                <option value="POSITIVO">ENTRADA (+) Sobrante</option>
                <option value="NEGATIVO">SALIDA (-) Avería / Merma</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Cantidad Unidades <span class="text-danger">*</span></label>
              <input type="number" class="form-control text-center fw-bold" id="ajuste_cantidad" name="cantidad" value="1" min="1" onkeyup="previsualizarAjuste()" required>
            </div>
          </div>

          <!-- Preview de Stock -->
          <div class="alert alert-light border py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
            <span class="small text-muted">Stock actual: <strong id="lblStockActual" class="text-dark">0</strong></span>
            <i class="bi bi-arrow-right text-muted"></i>
            <span class="small text-muted">Resultado: <strong id="lblStockResultante" class="text-primary fs-6">0</strong></span>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Motivo / Causa del Ajuste</label>
            <select class="form-select" id="ajuste_motivo" name="motiv_ajust">
              <option value="CONTEO FISICO">Conteo físico / Auditoría</option>
              <option value="ROTURA / AVERIA">Rotura o Avería de mercadería</option>
              <option value="VENCIMIENTO">Vencimiento o caducidad</option>
              <option value="PERDIDA O HURTO">Pérdida o Faltante desconocido</option>
              <option value="SOBRANTE">Sobrante de mercadería</option>
            </select>
          </div>

        </div>
        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary px-4" id="btnGuardarAjuste">
            <i class="bi bi-check-lg me-1"></i> Aplicar Ajuste
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const URL_AJUSTE = '<?= BASE_URL ?>controladores/AjusteStockControlador.php';
const URL_PROD = '<?= BASE_URL ?>controladores/ProductosControlador.php';
let modalAjuste = null;
let productoSeleccionado = null;
let debounceAjuste = null;

document.addEventListener('DOMContentLoaded', () => {
  modalAjuste = new bootstrap.Modal(document.getElementById('modalAjuste'));
  cargarAjustes();

  document.addEventListener('click', (e) => {
    if (!e.target.closest('#inputBuscarProdAjuste') && !e.target.closest('#dropdownAjuste')) {
      document.getElementById('dropdownAjuste').classList.add('d-none');
    }
  });
});

async function cargarAjustes() {
  const tbody = document.getElementById('tablaAjustes');
  try {
    const res = await fetch(`${URL_AJUSTE}?accion=listar`);
    const json = await res.json();

    if (json.status === 'success' && json.data.length > 0) {
      let html = '';
      json.data.forEach(item => {
        const tipoBadge = item.tipo_ajuste === 'POSITIVO'
          ? '<span class="badge bg-success-subtle text-success px-2 py-1">+ POSITIVO</span>'
          : '<span class="badge bg-danger-subtle text-danger px-2 py-1">- NEGATIVO</span>';

        html += `
          <tr>
            <td class="text-center fw-bold text-muted">${item.id_ajuste_stock}</td>
            <td><small class="text-muted">${item.fecha_ajuste}</small></td>
            <td><strong class="text-secondary small">${escapeHtml(item.sucursal_nombre || 'Sucursal ' + item.id_sucursal)}</strong></td>
            <td>
              <div class="fw-semibold text-dark">${escapeHtml(item.nombre_prod || 'Producto #' + item.id_producto)}</div>
              <small class="text-muted font-monospace">${escapeHtml(item.codigo_barra || '')}</small>
            </td>
            <td class="text-center">${tipoBadge}</td>
            <td class="text-center text-muted fw-bold">${item.cantidad_anterior ?? '-'}</td>
            <td class="text-center text-primary fw-bold">${item.cantidad_nueva ?? '-'}</td>
            <td><span class="badge bg-light text-dark border small">${escapeHtml(item.motiv_ajust || '-')}</span></td>
          </tr>
        `;
      });
      tbody.innerHTML = html;
    } else {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">No se registran ajustes de inventario previos.</td></tr>`;
    }
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Error al cargar ajustes.</td></tr>`;
  }
}

function abrirModalAjuste() {
  document.getElementById('formAjuste').reset();
  document.getElementById('ajuste_id_producto').value = '';
  document.getElementById('lblProdSeleccionado').classList.add('d-none');
  document.getElementById('lblStockActual').innerText = '0';
  document.getElementById('lblStockResultante').innerText = '0';
  productoSeleccionado = null;
  modalAjuste.show();
}

function buscarProdAjuste(e) {
  const q = e.target.value.trim();
  const dropdown = document.getElementById('dropdownAjuste');
  if (q.length < 2) {
    dropdown.classList.add('d-none');
    return;
  }

  clearTimeout(debounceAjuste);
  debounceAjuste = setTimeout(async () => {
    const suc = document.getElementById('ajuste_sucursal').value;
    const res = await fetch(`${URL_PROD}?accion=buscar_para_ajuste&q=${encodeURIComponent(q)}&sucursal=${suc}`);
    const json = await res.json();
    if (json.status === 'success' && json.data.length > 0) {
      let html = '';
      json.data.forEach(p => {
        const stockDispo = (suc == 2) ? (p.stock_sucursal2 || 0) : (p.stock_sucursal1 || 0);
        html += `
          <div class="search-result-item d-flex justify-content-between align-items-center" onclick='elegirProdAjuste(${jsonAttr(p)})'>
            <div>
              <strong>${escapeHtml(p.nombre_prod)}</strong>
              <small class="d-block text-muted font-monospace">${escapeHtml(p.codigo_barra || '')}</small>
            </div>
            <span class="badge bg-light text-dark border">Stock: ${stockDispo} un.</span>
          </div>
        `;
      });
      dropdown.innerHTML = html;
      dropdown.classList.remove('d-none');
    }
  }, 250);
}

function elegirProdAjuste(p) {
  productoSeleccionado = p;
  document.getElementById('ajuste_id_producto').value = p.id_producto;
  document.getElementById('inputBuscarProdAjuste').value = p.nombre_prod;
  document.getElementById('dropdownAjuste').classList.add('d-none');

  const lbl = document.getElementById('lblProdSeleccionado');
  lbl.innerText = `Seleccionado: ${p.nombre_prod} (Cód: ${p.codigo_barra || 'S/C'})`;
  lbl.classList.remove('d-none');

  actualizarStockActualProducto();
}

function actualizarStockActualProducto() {
  if (!productoSeleccionado) return;
  const suc = document.getElementById('ajuste_sucursal').value;
  const stock = (suc == 2) ? parseInt(productoSeleccionado.stock_sucursal2 || 0) : parseInt(productoSeleccionado.stock_sucursal1 || 0);
  document.getElementById('lblStockActual').innerText = `${stock} un.`;
  previsualizarAjuste();
}

function previsualizarAjuste() {
  if (!productoSeleccionado) return;
  const suc = document.getElementById('ajuste_sucursal').value;
  const stockActual = (suc == 2) ? parseInt(productoSeleccionado.stock_sucursal2 || 0) : parseInt(productoSeleccionado.stock_sucursal1 || 0);
  const tipo = document.getElementById('ajuste_tipo').value;
  const cant = parseInt(document.getElementById('ajuste_cantidad').value) || 0;

  const res = (tipo === 'POSITIVO') ? (stockActual + cant) : Math.max(0, stockActual - cant);
  document.getElementById('lblStockResultante').innerText = `${res} un.`;
}

async function guardarAjuste(e) {
  e.preventDefault();
  const btn = document.getElementById('btnGuardarAjuste');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Aplicando...';

  const formData = new FormData(document.getElementById('formAjuste'));
  formData.append('accion', 'guardar');

  try {
    const res = await fetch(URL_AJUSTE, { method: 'POST', body: formData });
    const json = await res.json();

    if (json.status === 'success') {
      Swal.fire({ icon: 'success', title: 'Inventario Actualizado', text: json.message });
      modalAjuste.hide();
      cargarAjustes();
    } else {
      Swal.fire('Atención', json.message, 'warning');
    }
  } catch (err) {
    Swal.fire('Error', 'No se pudo registrar el ajuste.', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Aplicar Ajuste';
  }
}

function jsonAttr(obj) {
  return JSON.stringify(obj)
    .replace(/&/g, '&amp;')
    .replace(/'/g, '&#39;')
    .replace(/"/g, '&quot;');
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
