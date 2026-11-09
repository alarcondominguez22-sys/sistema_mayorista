<?php
$page_title = 'Historial de Facturas & Ventas';
$active_menu = 'ventas_historial';
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
            <li class="breadcrumb-item active" aria-current="page">Historial de Ventas</li>
          </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Historial de Facturas & Ventas</h1>
        <p class="text-muted small mb-0">Registro histórico de comprobantes emitidos, reimpresión de tickets y anulación de ventas.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>paginas/movimientos/cuentas_cobrar.php" class="btn btn-outline-danger btn-icon-lg shadow-sm" title="Ir a Cuentas por Cobrar">
          <i class="bi bi-wallet2"></i>
        </a>
        <a href="<?= BASE_URL ?>paginas/movimientos/facturacion.php" class="btn btn-primary btn-icon-lg shadow-sm" title="Ir a Caja Rápida POS">
          <i class="bi bi-cart-check-fill"></i>
        </a>
      </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body py-3">
        <div class="row g-2 align-items-center">
          <div class="col-12 col-md-3">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
              <input type="text" id="inputBuscar" class="form-control border-start-0 bg-light" placeholder="Nro Factura o Cliente..." onkeyup="cargarVentas()">
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="input-group">
              <span class="input-group-text bg-light small">Desde</span>
              <input type="date" id="fechaDesde" class="form-control bg-light" onchange="cargarVentas()">
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="input-group">
              <span class="input-group-text bg-light small">Hasta</span>
              <input type="date" id="fechaHasta" class="form-control bg-light" onchange="cargarVentas()">
            </div>
          </div>
          <div class="col-12 col-md-3 text-md-end d-flex gap-2">
            <select id="filtroSucursal" class="form-select bg-light" onchange="cargarVentas()">
              <option value="">Todas las Sucursales</option>
              <option value="1">Sucursal 1</option>
              <option value="2">Sucursal 2</option>
            </select>
            <button class="btn btn-light border" type="button" onclick="limpiarFiltros()" title="Limpiar Filtros">
              <i class="bi bi-arrow-counterclockwise"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tarjeta Principal con Tabla -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded">Listado de Operaciones</span>
        <span class="text-muted small" id="contadorVentas">Cargando...</span>
      </div>
      
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width: 55px;">ID</th>
                <th>Nro. Factura</th>
                <th>Fecha</th>
                <th>Sucursal</th>
                <th>Cliente / Razón Social</th>
                <th class="text-center">Condición</th>
                <th class="text-center">Pago</th>
                <th class="text-end">Total</th>
                <th class="text-center">Estado</th>
                <th class="text-end pe-3" style="width: 220px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaVentas">
              <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Cargando operaciones de venta...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- Modal Detalle de Factura -->
<div class="modal fade" id="modalDetalleVenta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold" id="tituloModalDetalle">Detalle de Factura</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3" id="cuerpoDetalleVenta">
        <!-- Contenido dinámico -->
      </div>
      <div class="modal-footer border-top bg-light d-flex justify-content-between">
        <div>
          <button type="button" class="btn btn-danger btn-sm d-none" id="btnAnularDesdeModal" onclick="anularDesdeModalDetalle()">
            <i class="bi bi-x-circle me-1"></i> Anular Factura
          </button>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary btn-sm" onclick="imprimirModalDetalle()">
            <i class="bi bi-printer-fill me-1"></i> Imprimir Comprobante
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const BASE_URL_VENTAS = '<?= BASE_URL ?>controladores/VentasControlador.php';
let modalDetalle = null;
let datosVentaDetalle = null;

const formatGs = (n) => new Intl.NumberFormat('es-PY').format(n || 0) + ' Gs.';

document.addEventListener('DOMContentLoaded', () => {
  modalDetalle = new bootstrap.Modal(document.getElementById('modalDetalleVenta'));
  cargarVentas();
});

function limpiarFiltros() {
  document.getElementById('inputBuscar').value = '';
  document.getElementById('fechaDesde').value = '';
  document.getElementById('fechaHasta').value = '';
  document.getElementById('filtroSucursal').value = '';
  cargarVentas();
}

async function cargarVentas() {
  const buscar = document.getElementById('inputBuscar').value.trim();
  const desde = document.getElementById('fechaDesde').value;
  const hasta = document.getElementById('fechaHasta').value;
  const suc = document.getElementById('filtroSucursal').value;
  const tbody = document.getElementById('tablaVentas');
  const contador = document.getElementById('contadorVentas');

  try {
    const params = new URLSearchParams({
      accion: 'listar',
      buscar: buscar,
      fecha_desde: desde,
      fecha_hasta: hasta,
      sucursal: suc
    });

    const res = await fetch(`${BASE_URL_VENTAS}?${params.toString()}`);
    const json = await res.json();

    if (json.status === 'success') {
      contador.innerText = `${json.total} facturas listadas`;

      if (json.data.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="10" class="text-center py-5 text-muted">
              No se encontraron ventas con los filtros seleccionados.
            </td>
          </tr>
        `;
        return;
      }

      let html = '';
      json.data.forEach(v => {
        const estadoUpper = (v.estado || '').toUpperCase();
        const esAnulada = (estadoUpper === 'ANULADO' || estadoUpper === 'ANULADA');
        const estadoBadge = !esAnulada
          ? `<span class="badge bg-success-subtle text-success px-2 py-1"><i class="bi bi-check-circle me-1"></i> ${escapeHtml(estadoUpper || 'CONFIRMADO')}</span>`
          : `<span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i> ANULADO</span>`;

        const condBadge = v.condicion_pago === 'CONTADO'
          ? '<span class="badge bg-light text-dark border">CONTADO</span>'
          : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">CRÉDITO</span>';

        html += `
          <tr>
            <td class="text-center fw-bold text-muted">#${v.id_venta}</td>
            <td>
              <span class="font-monospace fw-bold text-dark">${escapeHtml(v.nro_factura || 'S/N')}</span>
            </td>
            <td><small class="text-muted">${v.fecha_venta}</small></td>
            <td><small class="text-secondary fw-semibold">${escapeHtml(v.sucursal_nombre || 'Sucursal ' + v.id_sucursal)}</small></td>
            <td>
              <strong class="text-dark d-block">${escapeHtml((v.nombre_cliente || 'CLIENTE') + ' ' + (v.apellido_cliente || ''))}</strong>
              <small class="text-muted">CI/RUC: ${escapeHtml(v.cedula_cliente || 'S/D')}</small>
            </td>
            <td class="text-center">${condBadge}</td>
            <td class="text-center"><span class="badge bg-light text-secondary border small">${escapeHtml(v.metodo_pago)}</span></td>
            <td class="text-end fw-bold text-dark">${formatGs(v.total_venta)}</td>
            <td class="text-center">${estadoBadge}</td>
            <td class="text-end pe-3">
              <div class="action-icons">
                <button type="button" class="btn btn-primary btn-icon" title="Imprimir Factura / Ticket" onclick="imprimirFacturaDirecto(${v.id_venta})">
                  <i class="bi bi-printer-fill"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-icon" title="Ver Detalle" onclick="verDetalleFactura(${v.id_venta})">
                  <i class="bi bi-eye"></i>
                </button>
                ${v.condicion_pago === 'CREDITO' && !esAnulada ? `
                  <a href="<?= BASE_URL ?>paginas/movimientos/cuentas_cobrar.php?buscar=${encodeURIComponent(v.nro_factura || '')}" class="btn btn-outline-warning btn-icon" title="Ver Cuenta por Cobrar">
                    <i class="bi bi-wallet2"></i>
                  </a>
                ` : ''}
                ${!esAnulada ? `
                  <button type="button" class="btn btn-outline-danger btn-icon" title="Anular Factura" onclick="anularFactura(${v.id_venta}, '${escapeHtml(v.nro_factura || '#' + v.id_venta)}')">
                    <i class="bi bi-x-circle-fill"></i>
                  </button>
                ` : ''}
              </div>
            </td>
          </tr>
        `;
      });
      tbody.innerHTML = html;
    } else {
      tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-danger">${json.message}</td></tr>`;
    }
  } catch (err) {
    console.error(err);
    tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-danger">Error al cargar listado de ventas.</td></tr>`;
  }
}

async function imprimirFacturaDirecto(idVenta, formato = 'ticket') {
  try {
    const res = await fetch(`${BASE_URL_VENTAS}?accion=obtener_detalle&id_venta=${idVenta}`);
    const json = await res.json();
    if (json.status === 'success') {
      datosVentaDetalle = json;
      if (window.ImpresorMayorista) {
        ImpresorMayorista.verVenta(json, formato);
      } else {
        imprimirModalDetalle(formato);
      }
    } else {
      Swal.fire('Error', json.message || 'No se pudo obtener la factura para imprimir.', 'error');
    }
  } catch (err) {
    console.error(err);
    Swal.fire('Error', 'Error al preparar impresión de la factura.', 'error');
  }
}

function anularDesdeModalDetalle() {
  if (!datosVentaDetalle || !datosVentaDetalle.cabecera) return;
  const cab = datosVentaDetalle.cabecera;
  modalDetalle.hide();
  anularFactura(cab.id_venta, cab.nro_factura || ('#' + cab.id_venta));
}

async function verDetalleFactura(idVenta) {
  try {
    const res = await fetch(`${BASE_URL_VENTAS}?accion=obtener_detalle&id_venta=${idVenta}`);
    const json = await res.json();

    if (json.status === 'success') {
      datosVentaDetalle = json;
      const cab = json.cabecera;
      const det = json.detalles;
      const estUpper = (cab.estado || '').toUpperCase();
      const esAnulada = (estUpper === 'ANULADO' || estUpper === 'ANULADA');
      const btnAnularModal = document.getElementById('btnAnularDesdeModal');
      if (btnAnularModal) {
        btnAnularModal.classList.toggle('d-none', esAnulada);
      }

      document.getElementById('tituloModalDetalle').innerText = `Factura #${cab.nro_factura || cab.id_venta}`;

      let itemsHtml = '';
      det.forEach((item, index) => {
        const sub = item.cantidad * item.precio_unitario;
        itemsHtml += `
          <tr>
            <td class="text-center text-muted">${index + 1}</td>
            <td>
              <div class="fw-semibold text-dark">${escapeHtml(item.descripcion || item.nombre_prod)}</div>
              <small class="text-muted font-monospace">${escapeHtml(item.codigo_barra || '')}</small>
            </td>
            <td class="text-end">${formatGs(item.precio_unitario)}</td>
            <td class="text-center fw-bold">${item.cantidad}</td>
            <td class="text-end fw-bold">${formatGs(sub)}</td>
          </tr>
        `;
      });

      document.getElementById('cuerpoDetalleVenta').innerHTML = `
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-3">
            <span class="text-muted small d-block">Número Factura:</span>
            <strong class="font-monospace text-primary fs-6">${escapeHtml(cab.nro_factura)}</strong>
          </div>
          <div class="col-6 col-md-3">
            <span class="text-muted small d-block">Fecha de Emisión:</span>
            <strong>${cab.fecha_venta}</strong>
          </div>
          <div class="col-6 col-md-3">
            <span class="text-muted small d-block">Cliente:</span>
            <strong>${escapeHtml(cab.nombre_cliente + ' ' + (cab.apellido_cliente || ''))}</strong>
            <small class="d-block text-muted">CI/RUC: ${escapeHtml(cab.cedula_cliente || '-')}</small>
          </div>
          <div class="col-6 col-md-3">
            <span class="text-muted small d-block">Condición / Pago:</span>
            <span class="badge bg-light text-dark border">${cab.condicion_pago}</span>
            <span class="badge bg-light text-secondary border">${cab.metodo_pago}</span>
          </div>
        </div>

        <div class="table-responsive border rounded mb-3">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 40px;" class="text-center">#</th>
                <th>Producto</th>
                <th class="text-end" style="width: 140px;">Precio Unit.</th>
                <th class="text-center" style="width: 100px;">Cant.</th>
                <th class="text-end" style="width: 140px;">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              ${itemsHtml}
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end">
          <div class="text-end" style="width: 260px;">
            ${cab.monto_delivery > 0 ? `
              <div class="d-flex justify-content-between mb-1 small text-muted">
                <span>Delivery / Flete:</span>
                <span>${formatGs(cab.monto_delivery)}</span>
              </div>
            ` : ''}
            <div class="d-flex justify-content-between fw-bold fs-5 text-dark border-top pt-2">
              <span>Total General:</span>
              <span class="text-primary">${formatGs(cab.total_venta)}</span>
            </div>
          </div>
        </div>
      `;

      modalDetalle.show();
    } else {
      Swal.fire('Error', json.message, 'error');
    }
  } catch (err) {
    console.error(err);
  }
}

function imprimirModalDetalle(formato = 'ticket') {
  if (!datosVentaDetalle) return;
  if (window.ImpresorMayorista) {
    ImpresorMayorista.verVenta(datosVentaDetalle, formato);
    return;
  }
  const cab = datosVentaDetalle.cabecera;
  const det = datosVentaDetalle.detalles;

  let itemsHtml = '';
  det.forEach(it => {
    itemsHtml += `
      <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
        <span>${it.cantidad}x ${it.descripcion || it.nombre_prod}</span>
        <span>${formatGs(it.cantidad * it.precio_unitario)}</span>
      </div>
    `;
  });

  const ventana = window.open('', '', 'width=400,height=600');
  ventana.document.write(`
    <html>
      <head>
        <title>Factura ${cab.nro_factura}</title>
        <style>
          body { font-family: monospace; font-size: 12px; margin: 20px; }
          .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
          .footer { text-align: center; border-top: 1px dashed #000; padding-top: 10px; margin-top: 15px; }
          .total { font-size: 14px; font-weight: bold; border-top: 1px dashed #000; padding-top: 5px; margin-top: 10px; display:flex; justify-content:space-between; }
        </style>
      </head>
      <body onload="window.print();">
        <div class="header">
          <h3>SISTEMA MAYORISTA</h3>
          <div>${cab.sucursal_nombre || 'Sucursal Central'}</div>
          <div>Factura: ${cab.nro_factura}</div>
          <div>Fecha: ${cab.fecha_venta}</div>
          <div>Cliente: ${cab.nombre_cliente} (${cab.cedula_cliente})</div>
          <div>Condición: ${cab.condicion_pago} - ${cab.metodo_pago}</div>
        </div>
        <div>
          ${itemsHtml}
        </div>
        <div class="total">
          <span>TOTAL:</span>
          <span>${formatGs(cab.total_venta)}</span>
        </div>
        <div class="footer">
          Comprobante válido para operaciones comerciales.
        </div>
      </body>
    </html>
  `);
  ventana.document.close();
}

async function anularFactura(idVenta, nroFactura) {
  const result = await Swal.fire({
    title: `¿Anular Factura ${nroFactura}?`,
    text: 'Esta acción anulará el comprobante y repondrá automáticamente el stock de los productos en el inventario.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    confirmButtonText: 'Sí, anular venta',
    cancelButtonText: 'Cancelar'
  });

  if (!result.isConfirmed) return;

  const formData = new FormData();
  formData.append('accion', 'anular_venta');
  formData.append('id_venta', idVenta);

  try {
    const res = await fetch(BASE_URL_VENTAS, { method: 'POST', body: formData });
    const json = await res.json();

    if (json.status === 'success') {
      Swal.fire({ icon: 'success', title: 'Anulada', text: json.message });
      cargarVentas();
    } else {
      Swal.fire('Error', json.message, 'error');
    }
  } catch (err) {
    Swal.fire('Error', 'No se pudo anular la venta.', 'error');
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
