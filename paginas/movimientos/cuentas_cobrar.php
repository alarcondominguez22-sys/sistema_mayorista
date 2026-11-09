<?php
$page_title = 'Cuentas por Cobrar & Créditos';
$active_menu = 'cuentas_cobrar';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/navbar.php';

$pdo = getDB();
$sucursalActual = (int)($_SESSION['id_sucursal'] ?? 1);

// Verificar caja abierta en la sucursal actual
$stmtCaja = $pdo->prepare("SELECT id_apertura_cierre_caja FROM apertura_cierre_caja WHERE id_sucursal = :suc AND estado = 'ABIERTA' ORDER BY id_apertura_cierre_caja DESC LIMIT 1");
$stmtCaja->execute([':suc' => $sucursalActual]);
$idCajaAbierta = $stmtCaja->fetchColumn();
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
            <li class="breadcrumb-item active" aria-current="page">Cuentas por Cobrar</li>
          </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Gestión de Cuentas por Cobrar & Créditos</h1>
        <p class="text-muted small mb-0">Seguimiento de ventas a crédito de clientes mayoristas, registro de pagos parciales/totales e impacto en caja.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>paginas/movimientos/caja.php" class="btn btn-outline-secondary btn-icon-lg shadow-sm" title="Control de Caja">
          <i class="bi bi-cash-stack"></i>
        </a>
        <a href="<?= BASE_URL ?>paginas/movimientos/facturacion.php" class="btn btn-primary btn-icon-lg shadow-sm" title="Ir a Caja Rápida POS">
          <i class="bi bi-cart-check-fill"></i>
        </a>
      </div>
    </div>

    <!-- Alerta informativa si no hay caja abierta -->
    <?php if (!$idCajaAbierta): ?>
      <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between mb-4 py-2 px-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill fs-5"></i>
          <span><strong>Atención:</strong> La sucursal activa no tiene un turno de caja abierto. Los cobros registrados se asociarán al último registro de caja disponible.</span>
        </div>
        <a href="<?= BASE_URL ?>paginas/movimientos/caja.php" class="btn btn-sm btn-dark">
          <i class="bi bi-unlock me-1"></i> Abrir Turno de Caja
        </a>
      </div>
    <?php endif; ?>

    <!-- TARJETAS DE MÉTRICAS CLAVE -->
    <div class="row g-3 mb-4">
      <!-- Total Cartera por Cobrar -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 p-lg-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold text-uppercase">Cartera por Cobrar</span>
              <div class="avatar-sm bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-wallet2 fs-5"></i>
              </div>
            </div>
            <h3 class="fw-bold text-danger mb-1" id="metricaTotalPendiente">0 Gs.</h3>
            <small class="text-muted" id="metricaCantPendientes">0 cuentas con saldo pendiente</small>
          </div>
        </div>
      </div>

      <!-- Cuentas Vencidas -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 p-lg-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold text-uppercase">Deuda Vencida</span>
              <div class="avatar-sm bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-exclamation-octagon fs-5"></i>
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-1" id="metricaTotalVencido">0 Gs.</h3>
            <small class="text-danger fw-semibold" id="metricaCantVencidas"><i class="bi bi-bell-fill me-1"></i>0 cuentas vencidas</small>
          </div>
        </div>
      </div>

      <!-- Cobrado en el Mes -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 p-lg-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold text-uppercase">Cobrado este Mes</span>
              <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-check2-circle fs-5"></i>
              </div>
            </div>
            <h3 class="fw-bold text-success mb-1" id="metricaTotalMes">0 Gs.</h3>
            <small class="text-muted" id="metricaCantMes">0 recibos de cobro emitidos</small>
          </div>
        </div>
      </div>

      <!-- Acceso Rápido y Estado -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-primary text-white">
          <div class="card-body p-3 p-lg-4 d-flex flex-column justify-content-between">
            <div>
              <span class="small fw-semibold text-white-50 text-uppercase">Sucursal Activa</span>
              <h4 class="fw-bold text-white mb-1"><?= $sucursalActual == 2 ? 'Sucursal 2 (Mcal López)' : 'Sucursal 1 (Lopez Godoy)' ?></h4>
              <small class="text-white-50">Caja: <?= $idCajaAbierta ? "Turno #$idCajaAbierta Activo" : 'Sin turno abierto' ?></small>
            </div>
            <div class="mt-2">
              <button class="btn btn-light btn-sm fw-semibold w-100" onclick="cargarTodo()">
                <i class="bi bi-arrow-clockwise me-1"></i> Actualizar Listado
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
      <div class="card-body py-3">
        <div class="row g-2 align-items-center">
          <!-- Búsqueda -->
          <div class="col-12 col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
              <input type="text" id="filtroBuscar" class="form-control border-start-0 bg-light" placeholder="Buscar por Factura, Cliente o Cédula/RUC..." onkeyup="debounceBuscar()">
            </div>
          </div>

          <!-- Filtro de Estado -->
          <div class="col-6 col-md-2">
            <select id="filtroEstado" class="form-select bg-light" onchange="cargarCuentas()">
              <option value="">Todos los Estados</option>
              <option value="PENDIENTE" selected>Pendientes y Parciales</option>
              <option value="VENCIDO">Solo Vencidas</option>
              <option value="PARCIAL">Solo Pagos Parciales</option>
              <option value="PAGADA">Canceladas / Pagadas</option>
            </select>
          </div>

          <!-- Filtro de Sucursal -->
          <div class="col-6 col-md-2">
            <select id="filtroSucursal" class="form-select bg-light" onchange="cargarTodo()">
              <option value="">Todas las Sucursales</option>
              <option value="1" <?= $sucursalActual == 1 ? 'selected' : '' ?>>Sucursal 1</option>
              <option value="2" <?= $sucursalActual == 2 ? 'selected' : '' ?>>Sucursal 2</option>
            </select>
          </div>

          <!-- Rango de Fechas -->
          <div class="col-6 col-md-2">
            <input type="date" id="filtroDesde" class="form-control bg-light" title="Fecha Venta Desde" onchange="cargarCuentas()">
          </div>
          <div class="col-6 col-md-2 d-flex gap-2">
            <input type="date" id="filtroHasta" class="form-control bg-light" title="Fecha Venta Hasta" onchange="cargarCuentas()">
            <button class="btn btn-light border" type="button" onclick="limpiarFiltros()" title="Restablecer Filtros">
              <i class="bi bi-arrow-counterclockwise"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TABLA DE CUENTAS POR COBRAR -->
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded">Cartera de Clientes</span>
          <span class="text-muted small" id="contadorCuentas">Cargando registros...</span>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-secondary" onclick="exportarCSV()">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar
          </button>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width: 70px;">ID</th>
                <th>Nro. Factura</th>
                <th>Cliente / Razón Social</th>
                <th>Sucursal</th>
                <th>Emisión</th>
                <th>Vencimiento</th>
                <th class="text-end">Total Crédito</th>
                <th class="text-end">Saldo Pendiente</th>
                <th class="text-center">Estado</th>
                <th class="text-end pe-4" style="width: 170px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaCuentas">
              <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Consultando cartera de cuentas por cobrar...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- MODAL: REGISTRAR COBRO -->
<div class="modal fade" id="modalCobrar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom bg-light">
        <h5 class="modal-title fw-bold text-dark">
          <i class="bi bi-cash-coin text-success me-2"></i> Registrar Cobro / Abono
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="formCobro" onsubmit="ejecutarCobro(event)">
        <input type="hidden" id="cobro_id_cuenta" name="id_cuenta_cobrar">
        
        <div class="modal-body py-3">
          <!-- Resumen de la Factura y Cliente -->
          <div class="p-3 bg-light rounded-3 border mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Factura:</span>
              <strong id="cobro_nro_factura" class="text-dark">-</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Cliente:</span>
              <strong id="cobro_nombre_cliente" class="text-dark">-</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Total Original:</span>
              <span id="cobro_monto_total" class="fw-semibold">-</span>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-danger fw-bold">Saldo Pendiente Actual:</span>
              <h5 class="fw-bold text-danger mb-0" id="cobro_saldo_actual">0 Gs.</h5>
            </div>
          </div>

          <!-- Monto a cobrar -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Monto a Cobrar (Gs.) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Gs.</span>
              <input type="number" class="form-control form-control-lg text-end fw-bold text-success" id="cobro_monto" required min="1" step="1000">
            </div>
            <!-- Botones de atajo rápido -->
            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn btn-xs btn-outline-success py-1 px-2 small flex-fill" onclick="setMontoCobro(1.0)">
                <i class="bi bi-check-all me-1"></i> Saldar Total (100%)
              </button>
              <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2 small flex-fill" onclick="setMontoCobro(0.5)">
                50% del saldo
              </button>
              <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2 small flex-fill" onclick="setMontoCobro(0.25)">
                25% del saldo
              </button>
            </div>
          </div>

          <!-- Método de Pago -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Medio de Pago <span class="text-danger">*</span></label>
            <select class="form-select" id="cobro_metodo" required>
              <option value="EFECTIVO" selected>💵 Efectivo (Ingresa a caja)</option>
              <option value="TRANSFERENCIA">🏦 Transferencia Bancaria / SIPAP</option>
              <option value="QR">📱 Código QR / Billetera</option>
              <option value="TARJETA">💳 Tarjeta Débito / Crédito</option>
              <option value="CHEQUE">📑 Cheque</option>
            </select>
          </div>

          <!-- Referencia / Nro Comprobante -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Nro. Comprobante / Referencia Bancaria</label>
            <input type="text" class="form-control" id="cobro_referencia" placeholder="Ej: Transf. 984124 o Nro. Recibo manual...">
          </div>

          <!-- Observaciones -->
          <div class="mb-2">
            <label class="form-label fw-semibold">Observación / Nota interna</label>
            <textarea class="form-control" id="cobro_observacion" rows="2" placeholder="Nota opcional sobre este cobro..."></textarea>
          </div>
        </div>

        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success px-4" id="btnGuardarCobro">
            <i class="bi bi-check2-circle me-1"></i> Confirmar Cobro
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: DETALLE DE CUENTA & HISTORIAL DE PAGOS -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold text-dark">
          <i class="bi bi-card-checklist text-primary me-2"></i> Detalle de Crédito & Historial de Pagos
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body py-3" id="detalleContenido">
        <div class="text-center py-4 text-muted">
          <div class="spinner-border spinner-border-sm text-primary me-2"></div> Cargando detalle...
        </div>
      </div>

      <div class="modal-footer border-top bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: RECIBO OFICIAL IMPRIMIBLE -->
<div class="modal fade" id="modalRecibo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom bg-light">
        <h5 class="modal-title fw-bold text-dark">
          <i class="bi bi-printer text-primary me-2"></i> Recibo Oficial de Cobro
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-4" id="areaImpresionRecibo">
        <!-- Contenido renderizado dinámicamente -->
      </div>

      <div class="modal-footer border-top bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-outline-primary" onclick="imprimirReciboActual('a4')">
          <i class="bi bi-file-earmark-text me-1"></i> Recibo A4
        </button>
        <button type="button" class="btn btn-primary" onclick="imprimirReciboActual('ticket')">
          <i class="bi bi-receipt me-1"></i> Imprimir Ticket
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ESTILOS Y SCRIPT DE CUENTAS POR COBRAR -->
<style>
@media print {
  body * {
    visibility: hidden;
  }
  #areaImpresionRecibo, #areaImpresionRecibo * {
    visibility: visible;
  }
  #areaImpresionRecibo {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    margin: 0;
    padding: 20px;
    background: white;
  }
  .modal-footer, .modal-header, .btn-close {
    display: none !important;
  }
}
.recibo-box {
  border: 2px dashed #cbd5e1;
  border-radius: 8px;
  padding: 20px;
  background: #ffffff;
  font-family: 'Courier New', Courier, monospace;
}
</style>

<script>
const URL_CC = '<?= BASE_URL ?>controladores/CuentasCobrarControlador.php';
let cuentasData = [];
let cuentaSeleccionada = null;
let debounceTimer = null;
let ultimoRecibo = null;

document.addEventListener('DOMContentLoaded', () => {
  const urlParams = new URLSearchParams(window.location.search);
  const buscarParam = urlParams.get('buscar');
  if (buscarParam) {
    document.getElementById('filtroBuscar').value = buscarParam;
    document.getElementById('filtroEstado').value = '';
    document.getElementById('filtroSucursal').value = '';
  }
  cargarTodo();
});

function debounceBuscar() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    cargarCuentas();
  }, 300);
}

function formatGs(valor) {
  return new Intl.NumberFormat('es-PY').format(Number(valor) || 0) + ' Gs.';
}

async function cargarTodo() {
  await Promise.all([cargarMetricas(), cargarCuentas()]);
}

async function cargarMetricas() {
  const suc = document.getElementById('filtroSucursal').value;
  try {
    const res = await fetch(`${URL_CC}?accion=resumen_metricas&sucursal=${suc}`);
    const json = await res.json();
    if (json.status === 'success') {
      const d = json.data;
      document.getElementById('metricaTotalPendiente').innerText = formatGs(d.total_pendiente);
      document.getElementById('metricaCantPendientes').innerText = `${d.cant_pendientes} cuentas con saldo pendiente`;
      
      document.getElementById('metricaTotalVencido').innerText = formatGs(d.total_vencido);
      document.getElementById('metricaCantVencidas').innerHTML = `<i class="bi bi-bell-fill me-1"></i>${d.cant_vencidas} cuentas vencidas`;

      document.getElementById('metricaTotalMes').innerText = formatGs(d.total_cobrado_mes);
      document.getElementById('metricaCantMes').innerText = `${d.cant_cobros_mes} cobros registrados este mes`;
    }
  } catch (err) {
    console.error('Error cargando métricas:', err);
  }
}

async function cargarCuentas() {
  const buscar = encodeURIComponent(document.getElementById('filtroBuscar').value.trim());
  const estado = encodeURIComponent(document.getElementById('filtroEstado').value);
  const sucursal = encodeURIComponent(document.getElementById('filtroSucursal').value);
  const desde = encodeURIComponent(document.getElementById('filtroDesde').value);
  const hasta = encodeURIComponent(document.getElementById('filtroHasta').value);

  const tbody = document.getElementById('tablaCuentas');
  tbody.innerHTML = `<tr><td colspan="10" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Buscando cuentas...</td></tr>`;

  try {
    const res = await fetch(`${URL_CC}?accion=listar&buscar=${buscar}&estado=${estado}&sucursal=${sucursal}&fecha_desde=${desde}&fecha_hasta=${hasta}`);
    const json = await res.json();

    if (json.status !== 'success') {
      tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-danger">${json.message || 'Error al obtener datos.'}</td></tr>`;
      return;
    }

    cuentasData = json.data || [];
    document.getElementById('contadorCuentas').innerText = `${cuentasData.length} cuenta(s) encontrada(s)`;

    if (cuentasData.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="10" class="text-center py-5 text-muted">
            <i class="bi bi-wallet2 fs-2 d-block text-muted mb-2"></i>
            No se encontraron cuentas por cobrar con los filtros seleccionados.
          </td>
        </tr>`;
      return;
    }

    let html = '';
    cuentasData.forEach(c => {
      const clienteNombre = `${c.nombre_cliente || ''} ${c.apellido_cliente || ''}`.trim() || 'Cliente Sin Nombre';
      const saldoHtml = c.saldo_pendiente > 0 
        ? `<strong class="text-danger">${formatGs(c.saldo_pendiente)}</strong>`
        : `<span class="text-success fw-semibold">0 Gs.</span>`;

      const vencimientoBadge = (c.saldo_pendiente > 0 && c.dias_para_vencer < 0)
        ? `<div class="text-danger small fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>${c.fecha_vencimiento}</div>`
        : `<div class="text-muted small">${c.fecha_vencimiento || '-'}</div>`;

      // Botón cobrar solo si tiene saldo
      const btnCobrar = c.saldo_pendiente > 0 
        ? `<button class="btn btn-success btn-icon shadow-sm" onclick="abrirModalCobrar(${c.id_cuenta_cobrar})" title="Registrar Cobro / Abono">
             <i class="bi bi-cash-coin"></i>
           </button>`
        : `<button class="btn btn-light border btn-icon disabled text-muted" title="Totalmente cancelada">
             <i class="bi bi-check2-all text-success"></i>
           </button>`;

      html += `
        <tr>
          <td class="text-center text-muted fw-semibold">${c.id_cuenta_cobrar}</td>
          <td>
            <strong class="text-dark">${c.nro_factura || 'Factura #' + c.id_venta}</strong>
            <small class="d-block text-muted">Venta #${c.id_venta}</small>
          </td>
          <td>
            <div class="fw-bold text-dark">${clienteNombre}</div>
            <small class="text-muted"><i class="bi bi-card-text me-1"></i>${c.cedula_cliente || 'S/N'} ${c.telefono_cliente ? '• Tel: ' + c.telefono_cliente : ''}</small>
          </td>
          <td>
            <span class="badge bg-light text-secondary border">${c.sucursal_nombre}</span>
          </td>
          <td class="text-muted small">${c.fecha_venta || '-'}</td>
          <td>${vencimientoBadge}</td>
          <td class="text-end fw-semibold text-secondary">${formatGs(c.monto_total)}</td>
          <td class="text-end">${saldoHtml}</td>
          <td class="text-center">
            <span class="badge ${c.badge_class} px-2 py-1 rounded-pill">${c.estado_texto}</span>
          </td>
          <td class="text-end pe-3">
            <div class="action-icons">
              ${btnCobrar}
              <button class="btn btn-outline-primary btn-icon" onclick="verDetalle(${c.id_cuenta_cobrar})" title="Ver Detalle e Historial">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
  } catch (err) {
    console.error(err);
    tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-danger">Error de conexión al cargar cuentas por cobrar.</td></tr>`;
  }
}

function limpiarFiltros() {
  document.getElementById('filtroBuscar').value = '';
  document.getElementById('filtroEstado').value = '';
  document.getElementById('filtroDesde').value = '';
  document.getElementById('filtroHasta').value = '';
  cargarTodo();
}

function abrirModalCobrar(idCuenta) {
  const c = cuentasData.find(item => Number(item.id_cuenta_cobrar) === Number(idCuenta));
  if (!c) return;

  cuentaSeleccionada = c;
  document.getElementById('cobro_id_cuenta').value = c.id_cuenta_cobrar;
  document.getElementById('cobro_nro_factura').innerText = c.nro_factura || `Venta #${c.id_venta}`;
  document.getElementById('cobro_nombre_cliente').innerText = `${c.nombre_cliente || ''} ${c.apellido_cliente || ''}`.trim();
  document.getElementById('cobro_monto_total').innerText = formatGs(c.monto_total);
  document.getElementById('cobro_saldo_actual').innerText = formatGs(c.saldo_pendiente);

  // Default a saldo total
  document.getElementById('cobro_monto').value = c.saldo_pendiente;
  document.getElementById('cobro_monto').max = c.saldo_pendiente;
  document.getElementById('cobro_referencia').value = '';
  document.getElementById('cobro_observacion').value = '';
  document.getElementById('cobro_metodo').value = 'EFECTIVO';

  const modal = new bootstrap.Modal(document.getElementById('modalCobrar'));
  modal.show();
}

function setMontoCobro(porcentaje) {
  if (!cuentaSeleccionada) return;
  const saldo = Number(cuentaSeleccionada.saldo_pendiente) || 0;
  const monto = Math.round(saldo * porcentaje);
  document.getElementById('cobro_monto').value = monto;
}

async function ejecutarCobro(e) {
  e.preventDefault();
  const idCuenta = document.getElementById('cobro_id_cuenta').value;
  const monto = Number(document.getElementById('cobro_monto').value);
  const metodo = document.getElementById('cobro_metodo').value;
  const referencia = document.getElementById('cobro_referencia').value.trim();
  const observacion = document.getElementById('cobro_observacion').value.trim();

  if (!monto || monto <= 0) {
    Swal.fire({ icon: 'warning', title: 'Monto inválido', text: 'Ingrese un importe válido a cobrar.' });
    return;
  }

  if (cuentaSeleccionada && monto > Number(cuentaSeleccionada.saldo_pendiente)) {
    Swal.fire({ icon: 'warning', title: 'Monto excede saldo', text: 'El monto a cobrar no puede superar el saldo pendiente.' });
    return;
  }

  const btn = document.getElementById('btnGuardarCobro');
  btn.disabled = true;
  btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Procesando...`;

  try {
    const res = await fetch(URL_CC, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        accion: 'registrar_cobro',
        id_cuenta_cobrar: idCuenta,
        monto_cobro: monto,
        metodo_pago: metodo,
        referencia: referencia,
        observacion: observacion
      })
    });

    const json = await res.json();
    btn.disabled = false;
    btn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Confirmar Cobro`;

    if (json.status !== 'success') {
      Swal.fire({ icon: 'error', title: 'Error al cobrar', text: json.message || 'Ocurrió un error.' });
      return;
    }

    // Cerrar modal de cobro
    bootstrap.Modal.getInstance(document.getElementById('modalCobrar')).hide();

    // Guardar recibo para visualización/impresión
    ultimoRecibo = json.recibo;

    // Notificación de éxito con SweetAlert y opción de imprimir recibo
    Swal.fire({
      icon: 'success',
      title: '¡Cobro Registrado!',
      text: `${json.message} Saldo restante: ${formatGs(json.saldo_restante)}.`,
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-printer me-1"></i> Ver e Imprimir Recibo',
      cancelButtonText: 'Aceptar',
      confirmButtonColor: '#0d6efd'
    }).then((result) => {
      if (result.isConfirmed) {
        mostrarRecibo(json.recibo);
      }
    });

    // Recargar tabla y métricas
    await cargarTodo();

  } catch (err) {
    console.error(err);
    btn.disabled = false;
    btn.innerHTML = `<i class="bi bi-check2-circle me-1"></i> Confirmar Cobro`;
    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.' });
  }
}

async function verDetalle(idCuenta) {
  const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
  const contenedor = document.getElementById('detalleContenido');
  contenedor.innerHTML = `<div class="text-center py-5 text-muted"><div class="spinner-border text-primary me-2"></div> Cargando detalle de la cuenta...</div>`;
  modal.show();

  try {
    const res = await fetch(`${URL_CC}?accion=obtener_detalle&id_cuenta_cobrar=${idCuenta}`);
    const json = await res.json();

    if (json.status !== 'success') {
      contenedor.innerHTML = `<div class="alert alert-danger">${json.message}</div>`;
      return;
    }

    const { cuenta, items, cuotas, cobros, pagos_json } = json.data;
    const clienteNombre = `${cuenta.nombre_cliente || ''} ${cuenta.apellido_cliente || ''}`.trim();

    // Tabla de pagos realizados
    let pagosHtml = '';
    const listaPagos = (pagos_json && pagos_json.length > 0) ? pagos_json : cobros;

    if (listaPagos.length > 0) {
      listaPagos.forEach((p, idx) => {
        const fechaPago = p.fecha_hora || p.fecha || '-';
        const medio = p.metodo_pago || p.forma_pago || 'EFECTIVO';
        const monto = p.monto || p.monto_pagado || p.total_cobrado;
        const recibo = p.nro_recibo || `Cobro #${p.id_cobro}`;
        const cajero = p.usuario || `Usuario #${p.idusuario || 1}`;

        pagosHtml += `
          <tr>
            <td class="text-muted">${idx + 1}</td>
            <td><strong>${recibo}</strong></td>
            <td>${fechaPago}</td>
            <td><span class="badge bg-light text-dark border">${medio}</span></td>
            <td><small class="text-muted">${cajero}</small></td>
            <td class="text-end fw-bold text-success">${formatGs(monto)}</td>
          </tr>
        `;
      });
    } else {
      pagosHtml = `<tr><td colspan="6" class="text-center text-muted py-3">No hay registros de cobros previos para esta cuenta.</td></tr>`;
    }

    // Tabla de cuotas pactadas
    let cuotasHtml = '';
    if (cuotas && cuotas.length > 0) {
      cuotas.forEach(q => {
        const estadoBadge = q.estado === 'PAGADA' 
          ? '<span class="badge bg-success-subtle text-success">PAGADA</span>' 
          : (q.estado === 'PARCIAL' ? '<span class="badge bg-warning-subtle text-warning">PARCIAL</span>' : '<span class="badge bg-light text-secondary border">PENDIENTE</span>');
        const numLabel = q.numero_cuota == 0 ? 'Entrega Inicial (Anticipo)' : `Cuota #${q.numero_cuota}`;
        cuotasHtml += `
          <tr>
            <td><strong>${numLabel}</strong></td>
            <td>${q.fecha_vencimiento}</td>
            <td class="text-end">${formatGs(q.monto_cuota)}</td>
            <td class="text-end text-success">${formatGs(q.monto_pagado)}</td>
            <td class="text-end fw-bold ${Number(q.saldo) > 0 ? 'text-danger' : 'text-muted'}">${formatGs(q.saldo)}</td>
            <td class="text-center">${estadoBadge}</td>
          </tr>
        `;
      });
    }

    // Tabla de productos de la venta
    let itemsHtml = '';
    if (items && items.length > 0) {
      items.forEach(it => {
        itemsHtml += `
          <tr>
            <td>${it.descripcion}</td>
            <td class="text-center">${it.cantidad}</td>
            <td class="text-end">${formatGs(it.precio_unitario)}</td>
            <td class="text-end fw-semibold">${formatGs(it.total)}</td>
          </tr>
        `;
      });
    } else {
      itemsHtml = `<tr><td colspan="4" class="text-center text-muted py-3">Sin ítems detallados.</td></tr>`;
    }

    contenedor.innerHTML = `
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <div class="p-3 bg-light rounded border h-100">
            <span class="text-muted small d-block">Cliente</span>
            <strong class="fs-6 text-dark">${clienteNombre}</strong>
            <small class="d-block text-muted">Cédula/RUC: ${cuenta.cedula_cliente || 'S/N'}</small>
            <small class="d-block text-muted">Teléfono: ${cuenta.telefono_cliente || 'S/N'}</small>
            <small class="d-block text-muted">Dirección: ${cuenta.direccion_cliente || 'No especificada'}</small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="p-3 bg-light rounded border h-100">
            <span class="text-muted small d-block">Comprobante Asociado</span>
            <strong class="fs-6 text-primary">${cuenta.nro_factura || 'Factura #' + cuenta.id_venta}</strong>
            <div class="d-flex justify-content-between mt-1">
              <span class="text-muted small">Fecha Venta:</span>
              <span>${cuenta.fecha_venta}</span>
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-muted small">Vencimiento:</span>
              <span class="fw-semibold text-danger">${cuenta.fecha_vencimiento}</span>
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-muted small">Monto Total:</span>
              <span class="fw-bold">${formatGs(cuenta.monto_total)}</span>
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-danger small fw-bold">Saldo Pendiente:</span>
              <span class="fw-bold text-danger">${formatGs(cuenta.saldo_pendiente)}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Sección Plan de Cuotas si existen -->
      ${cuotasHtml ? `
        <h6 class="fw-bold text-dark mt-4 mb-2"><i class="bi bi-calendar3 text-warning me-1"></i> Cronograma de Cuotas Pactadas</h6>
        <div class="table-responsive border rounded mb-4">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Cuota</th>
                <th>Fecha Vencimiento</th>
                <th class="text-end">Monto Cuota</th>
                <th class="text-end">Pagado</th>
                <th class="text-end">Saldo Pendiente</th>
                <th class="text-center">Estado</th>
              </tr>
            </thead>
            <tbody>
              ${cuotasHtml}
            </tbody>
          </table>
        </div>
      ` : ''}

      <!-- Pestañas o secciones -->
      <h6 class="fw-bold text-dark mt-4 mb-2"><i class="bi bi-cash-stack text-success me-1"></i> Historial de Cobros Realizados</h6>
      <div class="table-responsive border rounded mb-4">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 40px;">#</th>
              <th>Comprobante</th>
              <th>Fecha y Hora</th>
              <th>Medio de Pago</th>
              <th>Cajero / Usuario</th>
              <th class="text-end">Monto Cobrado</th>
            </tr>
          </thead>
          <tbody>
            ${pagosHtml}
          </tbody>
        </table>
      </div>

      <h6 class="fw-bold text-dark mb-2"><i class="bi bi-box-seam text-secondary me-1"></i> Mercadería Adquirida en esta Factura</h6>
      <div class="table-responsive border rounded">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Producto / Descripción</th>
              <th class="text-center" style="width: 90px;">Cantidad</th>
              <th class="text-end" style="width: 140px;">Precio Unit.</th>
              <th class="text-end" style="width: 140px;">Total</th>
            </tr>
          </thead>
          <tbody>
            ${itemsHtml}
          </tbody>
        </table>
      </div>
    `;

  } catch (err) {
    console.error(err);
    contenedor.innerHTML = `<div class="alert alert-danger">Error de conexión al cargar detalle.</div>`;
  }
}

function mostrarRecibo(recibo) {
  ultimoRecibo = recibo;
  const contenedor = document.getElementById('areaImpresionRecibo');
  contenedor.innerHTML = `
    <div class="recibo-box">
      <div class="text-center border-bottom pb-3 mb-3">
        <h4 class="fw-bold mb-0 text-dark">SISTEMA MAYORISTA</h4>
        <small class="text-muted d-block">Comercial & Distribución Mayorista</small>
        <span class="badge bg-dark text-white mt-1 px-3 py-1">RECIBO OFICIAL DE COBRO</span>
        <div class="fw-bold fs-5 text-primary mt-2">${recibo.nro_recibo}</div>
      </div>

      <div class="mb-3 small">
        <div class="d-flex justify-content-between mb-1">
          <span class="text-muted">Fecha y Hora:</span>
          <strong>${recibo.fecha}</strong>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-muted">Sucursal:</span>
          <strong>Sucursal ${recibo.sucursal}</strong>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-muted">Cajero:</span>
          <strong>${recibo.cajero}</strong>
        </div>
      </div>

      <div class="border-top border-bottom py-2 mb-3 small">
        <div class="mb-1">
          <span class="text-muted">Recibimos de:</span>
          <strong class="d-block fs-6 text-dark">${recibo.cliente}</strong>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-muted">Cédula / RUC:</span>
          <strong>${recibo.cedula || 'S/N'}</strong>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-muted">Por Concepto De:</span>
          <strong>Abono a Factura ${recibo.nro_factura}</strong>
        </div>
        <div class="d-flex justify-content-between">
          <span class="text-muted">Medio de Pago:</span>
          <strong>${recibo.metodo_pago} ${recibo.referencia ? '(' + recibo.referencia + ')' : ''}</strong>
        </div>
      </div>

      <div class="p-3 bg-light rounded text-center mb-3">
        <span class="text-muted small d-block">IMPORTE RECIBIDO:</span>
        <h3 class="fw-bold text-success mb-0">${recibo.monto_formateado}</h3>
      </div>

      <div class="d-flex justify-content-between small text-muted mb-4 px-1">
        <span>Saldo Anterior: ${formatGs(recibo.saldo_anterior)}</span>
        <strong class="text-danger">Saldo Restante: ${formatGs(recibo.saldo_restante)}</strong>
      </div>

      <div class="row pt-4 text-center text-muted" style="font-size: 0.75rem;">
        <div class="col-6">
          <div class="border-top pt-1">Firma Cajero</div>
        </div>
        <div class="col-6">
          <div class="border-top pt-1">Firma Cliente</div>
        </div>
      </div>
    </div>
  `;

  const modal = new bootstrap.Modal(document.getElementById('modalRecibo'));
  modal.show();
}

function imprimirReciboActual(formato = 'ticket') {
  if (ultimoRecibo && window.ImpresorMayorista) {
    ImpresorMayorista.verRecibo(ultimoRecibo, formato);
    return;
  }
  window.print();
}

function exportarCSV() {
  if (cuentasData.length === 0) {
    Swal.fire({ icon: 'info', title: 'Sin datos', text: 'No hay cuentas en la lista para exportar.' });
    return;
  }

  let csv = '\uFEFFID,Factura,Cliente,Cedula,Sucursal,FechaVenta,Vencimiento,TotalCredito,SaldoPendiente,Estado\n';
  cuentasData.forEach(c => {
    const cli = `"${((c.nombre_cliente || '') + ' ' + (c.apellido_cliente || '')).trim().replace(/"/g, '""')}"`;
    csv += `${c.id_cuenta_cobrar},${c.nro_factura || c.id_venta},${cli},${c.cedula_cliente || ''},"${c.sucursal_nombre}",${c.fecha_venta},${c.fecha_vencimiento},${c.monto_total},${c.saldo_pendiente},${c.estado_texto}\n`;
  });

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = `cuentas_por_cobrar_${new Date().toISOString().slice(0,10)}.csv`;
  link.click();
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
