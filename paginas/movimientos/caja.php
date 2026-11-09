<?php
$page_title = 'Control de Caja & Tesorería';
$active_menu = 'caja';
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
            <li class="breadcrumb-item active" aria-current="page">Control de Caja</li>
          </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Apertura y Cierre de Caja</h1>
        <p class="text-muted small mb-0">Control de fondos en mostrador, arqueo de valores, ingresos de ventas y registro de egresos.</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-icon-lg" onclick="cargarEstadoCaja()" title="Actualizar Estado de Caja">
          <i class="bi bi-arrow-clockwise"></i>
        </button>
        <a href="<?= BASE_URL ?>paginas/movimientos/facturacion.php" class="btn btn-primary btn-icon-lg shadow-sm" title="Ir a Caja Rápida POS">
          <i class="bi bi-cart-check-fill"></i>
        </a>
      </div>
    </div>

    <!-- SECCIÓN DINÁMICA: Si la caja está abierta o cerrada -->
    <div id="contenedorEstadoCaja">
      <div class="card border-0 shadow-sm rounded-3 py-5 text-center">
        <div class="spinner-border text-primary mx-auto mb-2" role="status"></div>
        <p class="text-muted mb-0">Consultando estado del punto de venta...</p>
      </div>
    </div>

    <!-- Historial de Cajas Anteriores -->
    <div class="card border-0 shadow-sm rounded-3 mt-4">
      <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="card-title fw-bold mb-0 text-dark">
          <i class="bi bi-clock-history me-2 text-primary"></i> Historial Reciente de Cajas
        </h5>
        <span class="badge bg-light text-secondary border">Últimos registros</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width: 70px;">ID</th>
                <th>Sucursal</th>
                <th>Apertura</th>
                <th class="text-end">Monto Inicial</th>
                <th>Cierre</th>
                <th class="text-end">Monto Cierre</th>
                <th class="text-center">Estado</th>
                <th class="text-center" style="width: 110px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaHistorialCajas">
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">Cargando historial de cajas...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- Modal Abrir Caja -->
<div class="modal fade" id="modalAbrirCaja" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold"><i class="bi bi-unlock text-success me-2"></i> Apertura de Caja</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formAbrirCaja" onsubmit="ejecutarAperturaCaja(event)">
        <div class="modal-body py-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Sucursal</label>
            <select class="form-select" id="abrir_id_sucursal" name="id_sucursal">
              <option value="1">SUCURSAL 1 (Avda Lopez Godoy)</option>
              <option value="2">SUCURSAL 2 (Avda Mcal Lopez)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Fondo Inicial de Caja / Vuelto (Gs.) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Gs.</span>
              <input type="number" class="form-control form-control-lg text-end fw-bold text-success" id="abrir_monto" name="monto_apertura" value="500000" min="0" step="1000" required>
            </div>
            <div class="form-text">Monto en billetes y monedas con el que arranca la jornada para dar vuelto.</div>
          </div>
        </div>
        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success px-4" id="btnConfirmarApertura">
            <i class="bi bi-check-circle me-1"></i> Abrir Turno de Caja
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Registrar Gasto / Egreso -->
<div class="modal fade" id="modalGasto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold text-danger"><i class="bi bi-arrow-down-circle me-2"></i> Registrar Gasto / Retiro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formGasto" onsubmit="guardarGasto(event)">
        <input type="hidden" id="gasto_id_caja" name="id_apertura_cierre_caja">
        <div class="modal-body py-4">
          <div class="mb-3">
            <label class="form-label fw-semibold">Concepto / Motivo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="gasto_descripcion" name="descripcion" placeholder="Ej: Compra de insumos de limpieza, pago de flete, viáticos..." required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Monto del Retiro (Gs.) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Gs.</span>
              <input type="number" class="form-control text-end fw-bold text-danger" id="gasto_monto" name="monto" min="1000" step="1000" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Método de Extracción</label>
            <select class="form-select" id="gasto_metodo" name="metodo_pago">
              <option value="EFECTIVO">Efectivo de Caja</option>
              <option value="TRANSFERENCIA">Transferencia Bancaria</option>
            </select>
          </div>
        </div>
        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger px-4" id="btnConfirmarGasto">
            <i class="bi bi-check-lg me-1"></i> Registrar Salida
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Cierre de Caja (Arqueo) -->
<div class="modal fade" id="modalCerrarCaja" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-lock me-2 text-primary"></i> Arqueo y Cierre de Caja</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formCerrarCaja" onsubmit="ejecutarCierreCaja(event)">
        <input type="hidden" id="cerrar_id_caja" name="id_apertura_cierre_caja">
        <div class="modal-body py-4">
          <div class="alert alert-primary py-2 px-3 mb-3">
            <div class="d-flex justify-content-between">
              <span class="small fw-semibold">Saldo Efectivo Estimado:</span>
              <strong id="labelSaldoEsperado">Gs. 0</strong>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Efectivo Físico en Gaveta (Gs.) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Gs.</span>
              <input type="number" class="form-control form-control-lg text-end fw-bold text-dark" id="cerrar_monto_real" name="monto_real_efectivo" min="0" step="1000" onkeyup="calcularDiferenciaCierre()" required>
            </div>
            <div class="form-text">Cuente los billetes y monedas disponibles al momento del cierre.</div>
          </div>

          <div class="alert alert-light border py-2 px-3 mb-0 d-flex justify-content-between align-items-center">
            <span class="small text-muted">Diferencia de arqueo:</span>
            <span class="badge bg-secondary-subtle text-secondary fw-bold" id="badgeDiferencia">Gs. 0</span>
          </div>
        </div>
        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
          <button type="submit" class="btn btn-primary px-4" id="btnConfirmarCierre">
            <i class="bi bi-lock-fill me-1"></i> Cerrar Caja Definitivamente
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const BASE_URL_CAJA = '<?= BASE_URL ?>controladores/CajaControlador.php';
let modalAbrir = null;
let modalGasto = null;
let modalCerrar = null;
let datosCajaActual = null;

const formatGs = (n) => new Intl.NumberFormat('es-PY').format(n || 0) + ' Gs.';

document.addEventListener('DOMContentLoaded', () => {
  modalAbrir = new bootstrap.Modal(document.getElementById('modalAbrirCaja'));
  modalGasto = new bootstrap.Modal(document.getElementById('modalGasto'));
  modalCerrar = new bootstrap.Modal(document.getElementById('modalCerrarCaja'));
  
  cargarEstadoCaja();
  cargarHistorialCajas();
});

async function cargarEstadoCaja() {
  const contenedor = document.getElementById('contenedorEstadoCaja');
  try {
    const res = await fetch(`${BASE_URL_CAJA}?accion=estado_actual`);
    const json = await res.json();

    if (json.status !== 'success') {
      contenedor.innerHTML = `<div class="alert alert-danger">${json.message}</div>`;
      return;
    }

    if (!json.tiene_caja_abierta) {
      // CAJA CERRADA
      contenedor.innerHTML = `
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden border-start border-warning border-4">
          <div class="card-body p-4 p-lg-5 text-center">
            <div class="avatar-lg bg-warning-subtle text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
              <i class="bi bi-exclamation-triangle-fill fs-2"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">No hay ninguna caja abierta en este turno</h3>
            <p class="text-muted mb-4 mx-auto" style="max-width: 520px;">
              Para comenzar a facturar en mostrador, registrar cobros y movimientos de stock, es necesario habilitar una caja con su fondo inicial.
            </p>
            <button type="button" class="btn btn-success btn-lg px-4 shadow-sm" onclick="modalAbrir.show()">
              <i class="bi bi-unlock-fill me-2"></i> Abrir Nueva Caja
            </button>
          </div>
        </div>
      `;
    } else {
      // CAJA ABIERTA
      datosCajaActual = json;
      const c = json.caja;
      const v = json.ventas;
      const totalGastos = json.total_gastos;
      const saldoEsperado = json.saldo_efectivo_esperado;
      const cb = json.cobros || { total_cobros: 0, cobros_efectivo: 0, cant_cobros: 0 };

      contenedor.innerHTML = `
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
          <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                <i class="bi bi-circle-fill small me-1"></i> CAJA #${c.id_apertura_cierre_caja} ABIERTA
              </span>
              <span class="text-muted small">Apertura: ${c.fecha_apertura} ${c.hora_apertura}</span>
            </div>
            <div class="d-flex gap-2">
              <a href="<?= BASE_URL ?>paginas/movimientos/cuentas_cobrar.php" class="btn btn-outline-info btn-icon" title="Cobranzas de Crédito">
                <i class="bi bi-wallet2"></i>
              </a>
              <button class="btn btn-outline-danger btn-icon" onclick="abrirModalGasto(${c.id_apertura_cierre_caja})" title="Registrar Gasto / Salida de Efectivo">
                <i class="bi bi-dash-circle-fill"></i>
              </button>
              <button class="btn btn-dark btn-icon" onclick="abrirModalCierre(${c.id_apertura_cierre_caja}, ${saldoEsperado})" title="Arqueo y Cerrar Caja">
                <i class="bi bi-lock-fill"></i>
              </button>
            </div>
          </div>

          <div class="card-body p-4">
            <!-- Grid de Tarjetas de Valores -->
            <div class="row g-3 mb-4">
              <div class="col-6 col-lg-2">
                <div class="p-3 bg-light rounded-3 border h-100">
                  <span class="text-muted small d-block mb-1">Monto Inicial (Vuelto)</span>
                  <h5 class="fw-bold text-dark mb-0">${formatGs(c.monto_apertura)}</h5>
                </div>
              </div>
              <div class="col-6 col-lg-3">
                <div class="p-3 bg-success-subtle rounded-3 border border-success-subtle h-100">
                  <span class="text-success small fw-semibold d-block mb-1">Ventas (${v.cant_ventas} oper.)</span>
                  <h4 class="fw-bold text-success mb-0">${formatGs(v.total_ventas)}</h4>
                </div>
              </div>
              <div class="col-6 col-lg-2">
                <div class="p-3 bg-info-subtle rounded-3 border border-info-subtle h-100">
                  <span class="text-info-emphasis small fw-semibold d-block mb-1">Cobros Créditos (${cb.cant_cobros})</span>
                  <h5 class="fw-bold text-info-emphasis mb-0">${formatGs(cb.total_cobros)}</h5>
                </div>
              </div>
              <div class="col-6 col-lg-2">
                <div class="p-3 bg-danger-subtle rounded-3 border border-danger-subtle h-100">
                  <span class="text-danger small fw-semibold d-block mb-1">Gastos / Egresos</span>
                  <h5 class="fw-bold text-danger mb-0">${formatGs(totalGastos)}</h5>
                </div>
              </div>
              <div class="col-12 col-lg-3">
                <div class="p-3 bg-primary-subtle rounded-3 border border-primary-subtle h-100">
                  <span class="text-primary small fw-semibold d-block mb-1">Efectivo en Gaveta</span>
                  <h4 class="fw-bold text-primary mb-0">${formatGs(saldoEsperado)}</h4>
                  <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">(Inicio + Ventas Ef. + Cobros Ef. - Gastos)</small>
                </div>
              </div>
            </div>

            <!-- Desglose por Medio de Pago -->
            <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-pie-chart me-1"></i> Desglose de Ventas por Método de Pago</h6>
            <div class="row g-2">
              <div class="col-6 col-md-3">
                <div class="border rounded p-2 text-center bg-white">
                  <small class="text-muted d-block">Efectivo</small>
                  <strong class="text-dark">${formatGs(v.ventas_efectivo)}</strong>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="border rounded p-2 text-center bg-white">
                  <small class="text-muted d-block">Tarjetas (Débito/Crédito)</small>
                  <strong class="text-dark">${formatGs(v.ventas_tarjeta)}</strong>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="border rounded p-2 text-center bg-white">
                  <small class="text-muted d-block">Código QR / Billeteras</small>
                  <strong class="text-dark">${formatGs(v.ventas_qr)}</strong>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="border rounded p-2 text-center bg-white">
                  <small class="text-muted d-block">Transferencias SIPAP</small>
                  <strong class="text-dark">${formatGs(v.ventas_transferencia)}</strong>
                </div>
              </div>
            </div>

          </div>
        </div>
      `;
    }
  } catch (err) {
    console.error(err);
    contenedor.innerHTML = `<div class="alert alert-danger">Error de conexión al cargar estado de caja.</div>`;
  }
}

async function cargarHistorialCajas() {
  const tbody = document.getElementById('tablaHistorialCajas');
  try {
    const res = await fetch(`${BASE_URL_CAJA}?accion=listar_historial`);
    const json = await res.json();

    if (json.status === 'success' && json.data.length > 0) {
      let html = '';
      json.data.forEach(item => {
        const estadoBadge = item.estado === 'ABIERTA' 
          ? '<span class="badge bg-success-subtle text-success px-2 py-1">ABIERTA</span>'
          : '<span class="badge bg-secondary-subtle text-secondary px-2 py-1">CERRADA</span>';

        html += `
          <tr>
            <td class="text-center fw-bold text-muted">#${item.id_apertura_cierre_caja}</td>
            <td><span class="fw-semibold text-dark">${item.sucursal_nombre || 'Sucursal ' + item.id_sucursal}</span></td>
            <td>${item.fecha_apertura} <small class="text-muted">${item.hora_apertura || ''}</small></td>
            <td class="text-end fw-semibold text-dark">${formatGs(item.monto_apertura)}</td>
            <td>${item.estado === 'CERRADA' ? item.fecha_cierre + ' <small class="text-muted">' + (item.hora_cierre || '') + '</small>' : '<span class="text-muted">-</span>'}</td>
            <td class="text-end fw-bold ${item.estado === 'CERRADA' ? 'text-primary' : 'text-muted'}">${item.estado === 'CERRADA' ? formatGs(item.monto_cierre) : '-'}</td>
            <td class="text-center">${estadoBadge}</td>
            <td class="text-center">
              <button type="button" class="btn btn-outline-primary btn-icon" title="Imprimir Comprobante de Arqueo y Cierre" onclick="imprimirCierreCaja(${item.id_apertura_cierre_caja})">
                <i class="bi bi-printer-fill"></i>
              </button>
            </td>
          </tr>
        `;
      });
      tbody.innerHTML = html;
    } else {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">No se registran aperturas anteriores.</td></tr>`;
    }
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger">Error al cargar historial.</td></tr>`;
  }
}

async function ejecutarAperturaCaja(e) {
  e.preventDefault();
  const btn = document.getElementById('btnConfirmarApertura');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Abriendo...';

  const formData = new FormData(document.getElementById('formAbrirCaja'));
  formData.append('accion', 'abrir_caja');

  try {
    const res = await fetch(BASE_URL_CAJA, { method: 'POST', body: formData });
    const json = await res.json();

    if (json.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Caja Habilitada',
        text: json.message,
        timer: 1600,
        showConfirmButton: false
      });
      modalAbrir.hide();
      cargarEstadoCaja();
      cargarHistorialCajas();
    } else {
      Swal.fire('Atención', json.message, 'warning');
    }
  } catch (err) {
    Swal.fire('Error', 'Error al abrir caja.', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Abrir Turno de Caja';
  }
}

function abrirModalGasto(idCaja) {
  document.getElementById('formGasto').reset();
  document.getElementById('gasto_id_caja').value = idCaja;
  modalGasto.show();
}

async function guardarGasto(e) {
  e.preventDefault();
  const btn = document.getElementById('btnConfirmarGasto');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Registrando...';

  const formData = new FormData(document.getElementById('formGasto'));
  formData.append('accion', 'registrar_gasto');

  try {
    const res = await fetch(BASE_URL_CAJA, { method: 'POST', body: formData });
    const json = await res.json();

    if (json.status === 'success') {
      Swal.fire({ icon: 'success', title: 'Registrado', text: json.message, timer: 1500, showConfirmButton: false });
      modalGasto.hide();
      cargarEstadoCaja();
    } else {
      Swal.fire('Atención', json.message, 'warning');
    }
  } catch (err) {
    Swal.fire('Error', 'Error al registrar egreso.', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Registrar Salida';
  }
}

function abrirModalCierre(idCaja, saldoEsperado) {
  document.getElementById('formCerrarCaja').reset();
  document.getElementById('cerrar_id_caja').value = idCaja;
  document.getElementById('cerrar_monto_real').value = saldoEsperado;
  document.getElementById('labelSaldoEsperado').innerText = formatGs(saldoEsperado);
  calcularDiferenciaCierre();
  modalCerrar.show();
}

function calcularDiferenciaCierre() {
  const esperado = datosCajaActual ? datosCajaActual.saldo_efectivo_esperado : 0;
  const real = parseFloat(document.getElementById('cerrar_monto_real').value) || 0;
  const dif = real - esperado;
  const badge = document.getElementById('badgeDiferencia');

  if (dif === 0) {
    badge.className = 'badge bg-success-subtle text-success fw-bold';
    badge.innerText = 'Caja Cuadrada (Gs. 0)';
  } else if (dif > 0) {
    badge.className = 'badge bg-info-subtle text-info fw-bold';
    badge.innerText = `Sobrante: +${formatGs(dif)}`;
  } else {
    badge.className = 'badge bg-danger-subtle text-danger fw-bold';
    badge.innerText = `Faltante: ${formatGs(dif)}`;
  }
}

async function ejecutarCierreCaja(e) {
  e.preventDefault();

  const confirm = await Swal.fire({
    title: '¿Confirmar cierre de caja?',
    text: 'Una vez cerrada la caja, se finalizará el turno y no se podrán asentar más cobros a este registro.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, cerrar caja',
    cancelButtonText: 'Cancelar'
  });

  if (!confirm.isConfirmed) return;

  const btn = document.getElementById('btnConfirmarCierre');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Cerrando...';

  const formData = new FormData(document.getElementById('formCerrarCaja'));
  formData.append('accion', 'cerrar_caja');

  try {
    const res = await fetch(BASE_URL_CAJA, { method: 'POST', body: formData });
    const json = await res.json();

    if (json.status === 'success') {
      const idCajaCerrada = document.getElementById('cerrar_id_caja').value;
      modalCerrar.hide();
      cargarEstadoCaja();
      cargarHistorialCajas();

      Swal.fire({
        icon: 'success',
        title: 'Caja Cerrada',
        text: json.message,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-printer me-1"></i> Imprimir Arqueo',
        cancelButtonText: 'Aceptar'
      }).then((resAlert) => {
        if (resAlert.isConfirmed && idCajaCerrada) {
          imprimirCierreCaja(idCajaCerrada);
        }
      });
    } else {
      Swal.fire('Atención', json.message, 'warning');
    }
  } catch (err) {
    Swal.fire('Error', 'Error al cerrar caja.', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Cerrar Caja Definitivamente';
  }
}

async function imprimirCierreCaja(idCaja, formato = 'ticket') {
  try {
    const res = await fetch(`${BASE_URL_CAJA}?accion=comprobante_cierre&id_apertura_cierre_caja=${idCaja}`);
    const json = await res.json();
    if (json.status !== 'success') {
      Swal.fire('Error', json.message || 'No se pudo obtener la información de la caja.', 'error');
      return;
    }
    if (window.ImpresorMayorista) {
      ImpresorMayorista.verCierreCaja(json, formato);
    }
  } catch (err) {
    console.error(err);
    Swal.fire('Error', 'Error al preparar impresión del cierre de caja.', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
