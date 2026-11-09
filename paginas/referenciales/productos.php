<?php
$page_title = 'Gestión de Productos & Precios';
$active_menu = 'productos';
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
            <li class="breadcrumb-item active" aria-current="page">Productos</li>
          </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Catálogo de Productos & Precios</h1>
        <p class="text-muted small mb-0">Gestión de inventario por sucursal, códigos de barra, costos y precios de venta mayorista.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-success btn-icon-lg shadow-sm" onclick="exportarExcel()" title="Exportar catálogo a Excel">
          <i class="bi bi-file-earmark-excel-fill"></i>
        </button>
        <a href="<?= BASE_URL ?>paginas/movimientos/facturacion.php" class="btn btn-outline-primary btn-icon-lg shadow-sm" title="Ir a Caja Rápida POS">
          <i class="bi bi-cart-check-fill"></i>
        </a>
        <button type="button" class="btn btn-primary btn-icon-lg shadow-sm" onclick="abrirModalNuevo()" title="Nuevo Producto">
          <i class="bi bi-plus-lg"></i>
        </button>
      </div>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body py-3">
        <div class="row g-2 align-items-center">
          <div class="col-12 col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
              <input type="text" id="inputBuscar" class="form-control border-start-0 bg-light" placeholder="Buscar por código, nombre o descripción..." onkeyup="debounceBuscar()">
            </div>
          </div>
          <div class="col-6 col-md-3">
            <select id="filtroCategoria" class="form-select bg-light" onchange="cargarProductos(1)">
              <option value="">Todas las Categorías</option>
            </select>
          </div>
          <div class="col-6 col-md-3">
            <select id="filtroMarca" class="form-select bg-light" onchange="cargarProductos(1)">
              <option value="">Todas las Marcas</option>
            </select>
          </div>
          <div class="col-12 col-md-2 text-md-end">
            <button class="btn btn-light border w-100" type="button" onclick="limpiarFiltros()" title="Restablecer filtros">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tarjeta de Contenido / Tabla -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded">Inventario Central</span>
          <span class="text-muted small" id="contadorProductos">Cargando...</span>
        </div>
        <div id="paginadorTop" class="d-flex align-items-center gap-1"></div>
      </div>
      
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 70px;" class="text-center">ID</th>
                <th>Producto / Código</th>
                <th>Marca / Cat.</th>
                <th class="text-end">Costo Compra</th>
                <th class="text-end">Precio Venta</th>
                <th class="text-center">Suc. 1</th>
                <th class="text-center">Suc. 2</th>
                <th class="text-center">Total Stock</th>
                <th style="width: 110px;" class="text-center">Estado</th>
                <th style="width: 140px;" class="text-end pe-3">Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaProductos">
              <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Cargando inventario de productos...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pie de Tabla y Paginación -->
      <div class="card-footer bg-white border-top py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="text-muted small" id="infoPaginacion">
          Mostrando página 1
        </div>
        <nav aria-label="Navegación de páginas">
          <ul class="pagination pagination-sm mb-0" id="paginacionLista">
            <!-- Paginador dinámico -->
          </ul>
        </nav>
      </div>
    </div>
  </div>
</main>

<!-- Modal Crear / Editar Producto -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="modalProductoLabel">Nuevo Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formProducto" onsubmit="guardarProducto(event)">
        <div class="modal-body py-4">
          <input type="hidden" id="id_producto" name="id_producto">
          
          <div class="row g-3">
            <!-- Nombre y Código de Barra -->
            <div class="col-md-8">
              <label for="nombre_prod" class="form-label fw-semibold">Nombre del Producto <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nombre_prod" name="nombre_prod" placeholder="Ej: Cargador Rápido 20W Tipo C" required>
            </div>
            <div class="col-md-4">
              <label for="codigo_barra" class="form-label fw-semibold">Código de Barras</label>
              <div class="input-group">
                <input type="text" class="form-control" id="codigo_barra" name="codigo_barra" placeholder="PRD000123">
                <button class="btn btn-outline-secondary" type="button" onclick="generarCodigoBarra()" title="Generar código automático">
                  <i class="bi bi-upc-scan"></i>
                </button>
              </div>
            </div>

            <!-- Descripción -->
            <div class="col-12">
              <label for="descripcion" class="form-label fw-semibold">Descripción Detallada</label>
              <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Especificaciones, modelo, compatibilidad...">
            </div>

            <!-- Marca, Categoría, Tipo -->
            <div class="col-md-4">
              <label for="id_marcas" class="form-label fw-semibold">Marca</label>
              <select class="form-select" id="id_marcas" name="id_marcas">
                <option value="">-- Sin Marca --</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="id_categoria" class="form-label fw-semibold">Categoría</label>
              <select class="form-select" id="id_categoria" name="id_categoria">
                <option value="">-- Sin Categoría --</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="tipo_producto" class="form-label fw-semibold">Tipo Producto</label>
              <select class="form-select" id="tipo_producto" name="tipo_producto">
                <option value="EQUIPO">EQUIPO</option>
                <option value="REPUESTO">REPUESTO</option>
                <option value="ACCESORIO">ACCESORIO</option>
              </select>
            </div>

            <!-- Precios y Tipo de Impuesto -->
            <div class="col-md-4">
              <label for="precio_compra" class="form-label fw-semibold">Costo Compra (Gs.)</label>
              <div class="input-group">
                <span class="input-group-text">Gs.</span>
                <input type="number" class="form-control text-end" id="precio_compra" name="precio_compra" value="0" min="0" step="100" onkeyup="calcularMargen()">
              </div>
            </div>
            <div class="col-md-4">
              <label for="precio_venta" class="form-label fw-semibold">Precio Venta (Gs.) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">Gs.</span>
                <input type="number" class="form-control text-end fw-bold text-primary" id="precio_venta" name="precio_venta" value="0" min="0" step="100" required onkeyup="calcularMargen()">
              </div>
            </div>
            <div class="col-md-4">
              <label for="id_tipo_impuesto" class="form-label fw-semibold">Impuesto IVA</label>
              <select class="form-select" id="id_tipo_impuesto" name="id_tipo_impuesto">
                <option value="1">IVA 10% (General)</option>
                <option value="2">IVA 5% (Canasta)</option>
                <option value="3">Exenta</option>
              </select>
            </div>

            <!-- Indicador de Margen de Ganancia -->
            <div class="col-12">
              <div class="alert alert-light border py-2 px-3 mb-0 d-flex align-items-center justify-content-between">
                <span class="small text-muted"><i class="bi bi-info-circle me-1"></i> Margen bruto estimado:</span>
                <span class="badge bg-success-subtle text-success fw-bold" id="badgeMargen">0% (Gs. 0)</span>
              </div>
            </div>

            <!-- Stock por Sucursales -->
            <div class="col-md-4">
              <label for="stock_sucursal1" class="form-label fw-semibold">Stock Sucursal 1 (Godoy)</label>
              <input type="number" class="form-control text-center" id="stock_sucursal1" name="stock_sucursal1" value="0" min="0">
            </div>
            <div class="col-md-4">
              <label for="stock_sucursal2" class="form-label fw-semibold">Stock Sucursal 2 (Mcal Lopez)</label>
              <input type="number" class="form-control text-center" id="stock_sucursal2" name="stock_sucursal2" value="0" min="0">
            </div>
            <div class="col-md-4">
              <label for="estado" class="form-label fw-semibold">Estado en Catálogo</label>
              <select class="form-select" id="estado" name="estado">
                <option value="ACTIVO">ACTIVO (Visible para venta)</option>
                <option value="INACTIVO">INACTIVO (Oculto)</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary px-4" id="btnGuardar">
            <i class="bi bi-check-lg me-1"></i> Guardar Producto
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const BASE_URL_CTRL = '<?= BASE_URL ?>controladores/ProductosControlador.php';
let modalProducto = null;
let paginaActual = 1;
let debounceTimeout = null;
let listaProductosActual = [];

document.addEventListener('DOMContentLoaded', () => {
  modalProducto = new bootstrap.Modal(document.getElementById('modalProducto'));
  cargarReferencialesAux();
  cargarProductos(1);
});

function debounceBuscar() {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    cargarProductos(1);
  }, 350);
}

function limpiarFiltros() {
  document.getElementById('inputBuscar').value = '';
  document.getElementById('filtroCategoria').value = '';
  document.getElementById('filtroMarca').value = '';
  cargarProductos(1);
}

function exportarExcel() {
  const buscar = document.getElementById('inputBuscar').value.trim();
  const categoria = document.getElementById('filtroCategoria').value;
  const marca = document.getElementById('filtroMarca').value;

  const url = `${BASE_URL_CTRL}?accion=exportar_excel&buscar=${encodeURIComponent(buscar)}&categoria=${encodeURIComponent(categoria)}&marca=${encodeURIComponent(marca)}`;
  
  // Notificación y descarga
  Swal.fire({
    title: 'Generando archivo Excel...',
    text: 'Se descargará el catálogo con los filtros aplicados.',
    icon: 'info',
    timer: 1500,
    showConfirmButton: false
  });

  window.location.href = url;
}

async function cargarReferencialesAux() {
  try {
    const res = await fetch(`${BASE_URL_CTRL}?accion=listar_referenciales_aux`);
    const json = await res.json();
    if (json.status === 'success') {
      // Poblar filtros y select del modal
      const selFiltroCat = document.getElementById('filtroCategoria');
      const selModalCat = document.getElementById('id_categoria');
      json.categorias.forEach(cat => {
        selFiltroCat.innerHTML += `<option value="${cat.id_categorias}">${cat.descripcion}</option>`;
        selModalCat.innerHTML += `<option value="${cat.id_categorias}">${cat.descripcion}</option>`;
      });

      const selFiltroMar = document.getElementById('filtroMarca');
      const selModalMar = document.getElementById('id_marcas');
      json.marcas.forEach(mar => {
        selFiltroMar.innerHTML += `<option value="${mar.id_marcas}">${mar.descripcion}</option>`;
        selModalMar.innerHTML += `<option value="${mar.id_marcas}">${mar.descripcion}</option>`;
      });
    }
  } catch (err) {
    console.error('Error cargando marcas y categorías:', err);
  }
}

async function cargarProductos(pagina = 1) {
  paginaActual = pagina;
  const buscar = document.getElementById('inputBuscar').value.trim();
  const categoria = document.getElementById('filtroCategoria').value;
  const marca = document.getElementById('filtroMarca').value;
  const tbody = document.getElementById('tablaProductos');
  const contador = document.getElementById('contadorProductos');
  
  tbody.innerHTML = `
    <tr>
      <td colspan="10" class="text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Buscando productos...
      </td>
    </tr>
  `;

  try {
    const params = new URLSearchParams({
      accion: 'listar',
      pagina: paginaActual,
      buscar: buscar,
      categoria: categoria,
      marca: marca
    });

    const res = await fetch(`${BASE_URL_CTRL}?${params.toString()}`);
    const json = await res.json();

    if (json.status === 'success') {
      listaProductosActual = json.data;
      contador.innerText = `${json.total} productos registrados`;
      document.getElementById('infoPaginacion').innerText = `Página ${json.pagina} de ${json.total_paginas || 1} (${json.total} registros)`;

      if (json.data.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="10" class="text-center py-5 text-muted">
              <i class="bi bi-box-seam fs-2 d-block mb-2 text-secondary"></i>
              No se encontraron productos que coincidan con la búsqueda.
            </td>
          </tr>
        `;
        renderPaginador(0, 1);
        return;
      }

      let html = '';
      json.data.forEach(p => {
        const estadoBadge = p.estado === 'ACTIVO'
          ? '<span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-check-circle me-1"></i> ACTIVO</span>'
          : '<span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i> INACTIVO</span>';

        const totalStock = parseInt(p.stock_actual || 0);
        let stockBadge = 'bg-secondary';
        if (totalStock <= 0) {
          stockBadge = 'bg-danger text-white';
        } else if (totalStock <= 5) {
          stockBadge = 'bg-warning text-dark';
        } else {
          stockBadge = 'bg-success-subtle text-success border border-success-subtle';
        }

        const formatGuaranies = (val) => new Intl.NumberFormat('es-PY').format(val || 0) + ' Gs.';

        html += `
          <tr>
            <td class="text-center fw-bold">${p.id_producto}</td>
            <td>
              <div class="fw-bold text-dark fs-6">${escapeHtml(p.nombre_prod)}</div>
              <div class="d-flex align-items-center gap-2 mt-1">
                <span class="badge bg-light text-dark border font-monospace small"><i class="bi bi-upc me-1"></i>${escapeHtml(p.codigo_barra || 'S/C')}</span>
                ${p.descripcion ? `<small class="text-secondary text-truncate" style="max-width: 250px;">${escapeHtml(p.descripcion)}</small>` : ''}
              </div>
            </td>
            <td>
              <span class="d-block small fw-semibold text-dark">${escapeHtml(p.marca_nombre || 'Sin Marca')}</span>
              <span class="badge bg-light text-dark border small">${escapeHtml(p.categoria_nombre || 'General')}</span>
            </td>
            <td class="text-end fw-semibold small">${formatGuaranies(p.precio_compra)}</td>
            <td class="text-end fw-bold text-dark fs-6">${formatGuaranies(p.precio_venta)}</td>
            <td class="text-center">
              <span class="badge bg-light text-dark border px-2 py-1">${p.stock_sucursal1 ?? 0}</span>
            </td>
            <td class="text-center">
              <span class="badge bg-light text-dark border px-2 py-1">${p.stock_sucursal2 ?? 0}</span>
            </td>
            <td class="text-center">
              <span class="badge ${stockBadge} fw-bold px-2 py-1 rounded-pill">${totalStock} un.</span>
            </td>
            <td class="text-center">${estadoBadge}</td>
            <td class="text-end pe-3">
              <div class="action-icons">
                <button type="button" class="btn btn-outline-primary btn-icon" title="Editar Producto" onclick="abrirModalEditar(${p.id_producto})">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button type="button" class="btn btn-outline-warning btn-icon" title="Cambiar Estado" onclick="cambiarEstado(${p.id_producto}, '${p.estado}')">
                  <i class="bi bi-arrow-repeat"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-icon" title="Eliminar Producto" onclick="eliminarProducto(${p.id_producto}, '${escapeHtml(p.nombre_prod)}')">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        `;
      });
      tbody.innerHTML = html;
      renderPaginador(json.total_paginas, json.pagina);
    } else {
      tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-danger">${json.message || 'Error cargando datos'}</td></tr>`;
    }
  } catch (err) {
    console.error(err);
    tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-danger">No se pudo conectar con el servidor.</td></tr>`;
  }
}

function renderPaginador(totalPaginas, actual) {
  const ul = document.getElementById('paginacionLista');
  ul.innerHTML = '';
  if (totalPaginas <= 1) return;

  // Botón Anterior
  ul.innerHTML += `
    <li class="page-item ${actual === 1 ? 'disabled' : ''}">
      <a class="page-link" href="javascript:void(0)" onclick="cargarProductos(${actual - 1})">«</a>
    </li>
  `;

  // Rango de páginas visible
  let inicio = Math.max(1, actual - 2);
  let fin = Math.min(totalPaginas, actual + 2);

  for (let i = inicio; i <= fin; i++) {
    ul.innerHTML += `
      <li class="page-item ${i === actual ? 'active' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="cargarProductos(${i})">${i}</a>
      </li>
    `;
  }

  // Botón Siguiente
  ul.innerHTML += `
    <li class="page-item ${actual === totalPaginas ? 'disabled' : ''}">
      <a class="page-link" href="javascript:void(0)" onclick="cargarProductos(${actual + 1})">»</a>
    </li>
  `;
}

function abrirModalNuevo() {
  document.getElementById('formProducto').reset();
  document.getElementById('id_producto').value = '';
  document.getElementById('modalProductoLabel').innerText = 'Nuevo Producto';
  generarCodigoBarra();
  calcularMargen();
  modalProducto.show();
}

function abrirModalEditar(id) {
  const p = listaProductosActual.find(item => item.id_producto == id);
  if (!p) return;

  document.getElementById('formProducto').reset();
  document.getElementById('id_producto').value = p.id_producto;
  document.getElementById('nombre_prod').value = p.nombre_prod || '';
  document.getElementById('codigo_barra').value = p.codigo_barra || '';
  document.getElementById('descripcion').value = p.descripcion || '';
  document.getElementById('id_marcas').value = p.id_marcas || '';
  document.getElementById('id_categoria').value = p.id_categoria || '';
  document.getElementById('tipo_producto').value = p.tipo_producto || 'EQUIPO';
  document.getElementById('precio_compra').value = p.precio_compra || 0;
  document.getElementById('precio_venta').value = p.precio_venta || 0;
  document.getElementById('id_tipo_impuesto').value = p.id_tipo_impuesto || 1;
  document.getElementById('stock_sucursal1').value = p.stock_sucursal1 || 0;
  document.getElementById('stock_sucursal2').value = p.stock_sucursal2 || 0;
  document.getElementById('estado').value = p.estado || 'ACTIVO';
  
  document.getElementById('modalProductoLabel').innerText = `Editar Producto #${p.id_producto}`;
  calcularMargen();
  modalProducto.show();
}

function calcularMargen() {
  const compra = parseFloat(document.getElementById('precio_compra').value) || 0;
  const venta = parseFloat(document.getElementById('precio_venta').value) || 0;
  const badge = document.getElementById('badgeMargen');

  const ganancia = venta - compra;
  if (compra > 0) {
    const porcentaje = ((ganancia / compra) * 100).toFixed(1);
    const gananciaFormat = new Intl.NumberFormat('es-PY').format(ganancia);
    if (ganancia >= 0) {
      badge.className = 'badge bg-success-subtle text-success fw-bold';
      badge.innerText = `+${porcentaje}% (Gs. ${gananciaFormat})`;
    } else {
      badge.className = 'badge bg-danger-subtle text-danger fw-bold';
      badge.innerText = `${porcentaje}% (Gs. ${gananciaFormat})`;
    }
  } else {
    badge.className = 'badge bg-secondary-subtle text-secondary fw-bold';
    badge.innerText = `Gs. ${new Intl.NumberFormat('es-PY').format(ganancia)}`;
  }
}

function generarCodigoBarra() {
  const randomNum = Math.floor(100000 + Math.random() * 900000);
  document.getElementById('codigo_barra').value = `PRD${randomNum}`;
}

async function guardarProducto(e) {
  e.preventDefault();
  const btn = document.getElementById('btnGuardar');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

  const formData = new FormData(document.getElementById('formProducto'));
  formData.append('accion', 'guardar');

  try {
    const res = await fetch(BASE_URL_CTRL, {
      method: 'POST',
      body: formData
    });
    const json = await res.json();

    if (json.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: json.message,
        timer: 1600,
        showConfirmButton: false
      });
      modalProducto.hide();
      cargarProductos(paginaActual);
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Atención',
        text: json.message || 'No se pudo guardar el producto.'
      });
    }
  } catch (err) {
    console.error(err);
    Swal.fire({
      icon: 'error',
      title: 'Error de Red',
      text: 'Ocurrió un error al comunicarse con el servidor.'
    });
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Guardar Producto';
  }
}

async function cambiarEstado(id, estadoActual) {
  const formData = new FormData();
  formData.append('accion', 'cambiar_estado');
  formData.append('id_producto', id);
  formData.append('estado', estadoActual);

  try {
    const res = await fetch(BASE_URL_CTRL, { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
      cargarProductos(paginaActual);
    } else {
      Swal.fire('Error', json.message, 'error');
    }
  } catch (err) {
    Swal.fire('Error', 'No se pudo actualizar el estado', 'error');
  }
}

async function eliminarProducto(id, nombre) {
  const result = await Swal.fire({
    title: '¿Eliminar producto?',
    text: `¿Estás seguro de eliminar "${nombre}"? Esta acción no se puede deshacer.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  });

  if (!result.isConfirmed) return;

  const formData = new FormData();
  formData.append('accion', 'eliminar');
  formData.append('id_producto', id);

  try {
    const res = await fetch(BASE_URL_CTRL, { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Eliminado',
        text: json.message,
        timer: 1500,
        showConfirmButton: false
      });
      cargarProductos(paginaActual);
    } else {
      Swal.fire('Atención', json.message, 'warning');
    }
  } catch (err) {
    Swal.fire('Error', 'Error al procesar la solicitud.', 'error');
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
