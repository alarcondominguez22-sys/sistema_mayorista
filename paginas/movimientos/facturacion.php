<?php
$page_title = 'Caja Rápida / Facturación Mayorista';
$active_menu = 'facturacion';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/navbar.php';

$pdo = getDB();
$sucursalActual = (int)($_SESSION['id_sucursal'] ?? 1);

// Verificar caja abierta
$stmtCaja = $pdo->prepare("SELECT id_apertura_cierre_caja FROM apertura_cierre_caja WHERE id_sucursal = :suc AND estado = 'ABIERTA' ORDER BY id_apertura_cierre_caja DESC LIMIT 1");
$stmtCaja->execute([':suc' => $sucursalActual]);
$idCajaAbierta = $stmtCaja->fetchColumn();

// Clientes iniciales y cliente por defecto
$stmtClientes = $pdo->query("SELECT id_cliente, nombre_cliente, apellido_cliente, cedula_cliente, COALESCE(NULLIF(tipo_cliente, ''), 'MAYORISTA') AS tipo_cliente, telefono_cliente FROM clientes WHERE estado = 'ACTIVO' ORDER BY id_cliente ASC LIMIT 50");
$clientesLista = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
$clienteDefecto = $clientesLista[0] ?? [
  'id_cliente' => 1,
  'nombre_cliente' => 'CONSUMIDOR',
  'apellido_cliente' => 'FINAL',
  'cedula_cliente' => '000000',
  'tipo_cliente' => 'MAYORISTA',
  'telefono_cliente' => 'S/N'
];
?>

<main class="dashboard-content">
  <div class="container-fluid px-3 px-lg-4 py-3">
    
    <!-- Alerta si no hay caja abierta -->
    <?php if (!$idCajaAbierta): ?>
      <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between mb-3 py-2 px-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill fs-5"></i>
          <span><strong>Advertencia:</strong> No hay un turno de caja abierto en esta sucursal. Los cobros no se sumarán al arqueo de hoy.</span>
        </div>
        <a href="<?= BASE_URL ?>paginas/movimientos/caja.php" class="btn btn-sm btn-dark">
          <i class="bi bi-unlock me-1"></i> Abrir Caja Ahora
        </a>
      </div>
    <?php endif; ?>

    <!-- Encabezado de Caja Rápida -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
      <div>
        <h1 class="h4 fw-bold mb-0 text-dark">
          <i class="bi bi-cart-check-fill text-primary me-2"></i> Caja Rápida Mayorista
        </h1>
        <small class="text-muted">Sucursal <?= $sucursalActual == 1 ? '1 (Avda Lopez Godoy)' : '2 (Avda Mcal Lopez)' ?> • Terminal #1</small>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" title="Mayorista: 5% (1-4 un.) • 7% (5 un.) • 10% (>5 un.) | Cliente Fiel: 2%, 3%, 5%">
          <i class="bi bi-tags-fill me-1"></i> Descuentos por Escala Activos
        </span>
        <button class="btn btn-outline-danger btn-icon" onclick="limpiarCarrito()" title="Vaciar Carrito">
          <i class="bi bi-trash"></i>
        </button>
        <a href="<?= BASE_URL ?>paginas/movimientos/ventas_historial.php" class="btn btn-outline-primary btn-icon" title="Historial de Facturas">
          <i class="bi bi-clock-history"></i>
        </a>
      </div>
    </div>

    <!-- Layout Principal: 2 Columnas -->
    <div class="row g-3">
      
      <!-- Columna Izquierda: Búsqueda y Carrito -->
      <div class="col-12 col-xl-8">
        
        <!-- Barra de Búsqueda Interactiva de Productos con Precio Estirado Automáticamente -->
        <div class="card border-0 shadow-sm rounded-3 mb-2 position-relative">
          <div class="card-body p-2 px-3">
            <div class="row g-2 align-items-center">
              <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-muted mb-1">Producto (Escanear o Buscar)</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-white border-end-0 text-primary">
                    <i class="bi bi-upc-scan"></i>
                  </span>
                  <input type="text" id="inputBuscarProducto" class="form-control border-start-0 border-end-0 ps-1" 
                         placeholder="Código o nombre del producto..." 
                         autocomplete="off" onkeyup="buscarProductoTeclado(event)" onfocus="mostrarSugerenciasIniciales()">
                  <button class="btn btn-outline-secondary border-start-0" type="button" onclick="ejecutarBusquedaManual()" title="Buscar">
                    <i class="bi bi-search"></i>
                  </button>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Precio Estirado (Gs.)</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-light text-success fw-bold">Gs.</span>
                  <input type="text" id="inputPrecioEstirado" class="form-control fw-bold text-end text-primary bg-light" value="0" readonly title="Precio del último producto seleccionado">
                </div>
              </div>
              <div class="col-6 col-md-3 d-flex align-items-end pt-3">
                <button class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-1 fw-semibold shadow-sm" type="button" onclick="abrirModalCatalogoStock()" title="Ver catálogo de productos disponibles con stock">
                  <i class="bi bi-grid-3x3-gap-fill"></i>
                  <span>Catálogo con Stock</span>
                </button>
              </div>
            </div>
            
            <!-- Desplegable flotante de resultados (Solo productos con stock > 0) -->
            <div id="dropdownResultados" class="search-result-dropdown d-none"></div>
          </div>
        </div>

        <!-- Tabla del Carrito de Ventas -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-bold text-dark"><i class="bi bi-cart3 me-2 text-primary"></i> Ítems en Factura</span>
            <span class="badge bg-primary-subtle text-primary fw-semibold" id="contadorItems">0 productos</span>
          </div>
          
          <div class="card-body p-0">
            <div class="table-responsive" style="min-height: 210px;">
              <table class="table table-hover align-middle mb-0 factura-grid">
                <thead>
                  <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th>Descripción del Producto</th>
                    <th style="width: 140px;" class="text-end">Precio Unit. (Gs.)</th>
                    <th style="width: 130px;" class="text-center">Cantidad</th>
                    <th style="width: 140px;" class="text-end">Subtotal (Gs.)</th>
                    <th style="width: 50px;" class="text-center"></th>
                  </tr>
                </thead>
                <tbody id="tablaCarrito">
                  <tr id="filaVacia">
                    <td colspan="6" class="text-center py-5 text-muted">
                      <div class="py-4">
                        <i class="bi bi-cart-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                        <h6 class="fw-bold text-dark">El carrito de facturación está vacío</h6>
                        <p class="small text-muted mb-0">Escanea un código de barras o busca productos arriba para comenzar la venta.</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- Columna Derecha: Totales y Opciones de Pago -->
      <div class="col-12 col-xl-4">
        
        <!-- Tarjeta de Gran Total -->
        <div class="total-box mb-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-uppercase small tracking-wide opacity-75">Total a Pagar</span>
            <span class="badge bg-white text-dark small font-monospace" id="badgeCondicion">CONTADO</span>
          </div>
          <div class="total-highlight" id="lblTotalVenta">0 Gs.</div>
          <hr class="border-secondary my-2">
          <div class="d-flex justify-content-between small opacity-75">
            <span>Liquidación IVA 10%:</span>
            <span id="lblIva10">0 Gs.</span>
          </div>
          <div class="d-flex justify-content-between small opacity-75">
            <span>Liquidación IVA 5%:</span>
            <span id="lblIva5">0 Gs.</span>
          </div>
        </div>

        <!-- Opciones de Factura y Cliente -->
        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-body p-3">
            
            <!-- Selector y Buscador Inteligente de Cliente (Nombre, CI o RUC) -->
            <div class="mb-3 position-relative">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-semibold small mb-0">Cliente / Razón Social <span class="text-danger">*</span></label>
                <div class="d-flex gap-1">
                  <button type="button" class="btn btn-outline-secondary btn-icon" style="width:26px;height:26px;" onclick="abrirModalBuscarCliente()" title="Directorio de Clientes">
                    <i class="bi bi-people-fill"></i>
                  </button>
                  <button type="button" class="btn btn-outline-primary btn-icon" style="width:26px;height:26px;" onclick="abrirModalNuevoCliente()" title="Registrar Nuevo Cliente">
                    <i class="bi bi-person-plus-fill"></i>
                  </button>
                </div>
              </div>

              <!-- Buscador con filtro por Nombre, CI o RUC -->
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0 text-primary">
                  <i class="bi bi-search"></i>
                </span>
                <input type="text" id="inputBuscarCliente" class="form-control border-start-0 ps-1" 
                       placeholder="Filtrar por Nombre, CI o RUC..." 
                       autocomplete="off" onkeyup="buscarClienteTeclado(event)" onfocus="mostrarDropdownClientes()">
                <button class="btn btn-outline-secondary" type="button" onclick="limpiarBuscadorCliente()" title="Limpiar filtro">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>

              <!-- Desplegable flotante de clientes encontrados -->
              <div id="dropdownClientes" class="search-result-dropdown d-none shadow border" style="max-height: 250px; overflow-y: auto;"></div>

              <!-- Ficha del Cliente Activo en la Factura -->
              <input type="hidden" id="id_cliente" value="<?= $clienteDefecto['id_cliente'] ?>">
              <input type="hidden" id="cliente_nombre_completo" value="<?= htmlspecialchars(trim($clienteDefecto['nombre_cliente'] . ' ' . $clienteDefecto['apellido_cliente'])) ?>">
              <input type="hidden" id="cliente_tipo_actual" value="<?= htmlspecialchars(strtoupper($clienteDefecto['tipo_cliente'] ?? 'MAYORISTA')) ?>">
              
              <div id="boxClienteActivo" class="mt-2 p-2 rounded bg-light border border-primary-subtle d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                  <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; flex-shrink: 0; font-size: 0.85rem;">
                    <i class="bi bi-person-check-fill"></i>
                  </div>
                  <div class="text-truncate">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                      <strong class="text-dark text-truncate small" id="lblClienteNombre">
                        <?= htmlspecialchars(trim($clienteDefecto['nombre_cliente'] . ' ' . $clienteDefecto['apellido_cliente'])) ?>
                      </strong>
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.65rem;" id="lblClienteTipo">
                        <?= htmlspecialchars($clienteDefecto['tipo_cliente'] ?? 'MAYORISTA') ?>
                      </span>
                    </div>
                    <small class="text-muted font-monospace" id="lblClienteDoc">
                      CI/RUC: <?= htmlspecialchars($clienteDefecto['cedula_cliente']) ?>
                      <?= !empty($clienteDefecto['telefono_cliente']) ? ' • Tel: ' . htmlspecialchars($clienteDefecto['telefono_cliente']) : '' ?>
                    </small>
                  </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="abrirModalBuscarCliente()" title="Buscar o cambiar cliente">
                  <i class="bi bi-arrow-repeat"></i>
                </button>
              </div>
            </div>

            <!-- Nro Factura -->
            <div class="mb-3">
              <label class="form-label fw-semibold small mb-1">Nro. de Factura</label>
              <div class="input-group input-group-sm">
                <input type="text" class="form-control font-monospace fw-bold" id="nro_factura" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="cargarSiguienteFactura()" title="Generar nuevo">
                  <i class="bi bi-arrow-clockwise"></i>
                </button>
              </div>
            </div>

            <!-- Condición de Factura: CONTADO o CRÉDITO -->
            <div class="mb-2">
              <label class="form-label fw-semibold small mb-1">Condición de Venta <span class="text-danger">*</span></label>
              <input type="hidden" id="condicion_pago" value="CONTADO">
              <div class="btn-group w-100" role="group">
                <button type="button" class="btn btn-sm btn-primary fw-bold" id="btnCondContado" onclick="seleccionarCondicionPago('CONTADO', false)">
                  <i class="bi bi-cash-coin me-1"></i> CONTADO
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" id="btnCondCredito" onclick="seleccionarCondicionPago('CREDITO', true)">
                  <i class="bi bi-calendar2-week me-1"></i> CRÉDITO (Cuotas)
                </button>
              </div>
            </div>

            <!-- =========================================================
                 PANEL PAGO AL CONTADO: SIMPLE (1 forma) o MÚLTIPLE (Varias)
                 ========================================================= -->
            <div id="panelPagoContado" class="p-2 bg-light border rounded-3 mb-2">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small fw-bold text-dark"><i class="bi bi-wallet2 text-primary me-1"></i> Modalidad Contado:</span>
                <input type="hidden" id="tipo_pago_contado" value="SIMPLE">
                <div class="btn-group btn-group-sm" role="group">
                  <button type="button" class="btn btn-primary py-0 px-2 fw-semibold" id="btnModoSimple" onclick="cambiarModoContado('SIMPLE')">
                    Simple (1 Forma)
                  </button>
                  <button type="button" class="btn btn-outline-primary py-0 px-2 fw-semibold" id="btnModoMultiple" onclick="cambiarModoContado('MULTIPLE')">
                    Múltiple (Combinado)
                  </button>
                </div>
              </div>

              <!-- PAGO SIMPLE: 1 sola forma de pago (Efectivo, Transferencia, QR) -->
              <div id="boxPagoSimple">
                <input type="hidden" id="metodo_pago" value="EFECTIVO">
                <div class="row g-1">
                  <div class="col-4">
                    <button type="button" class="btn btn-sm btn-success w-100 py-2 fw-bold btn-metodo-simple" id="btnSimple_EFECTIVO" onclick="seleccionarMetodoSimple('EFECTIVO')">
                      <i class="bi bi-cash-stack d-block fs-5"></i> Efectivo
                    </button>
                  </div>
                  <div class="col-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 fw-bold btn-metodo-simple" id="btnSimple_TRANSFERENCIA" onclick="seleccionarMetodoSimple('TRANSFERENCIA')">
                      <i class="bi bi-bank d-block fs-5"></i> Transf.
                    </button>
                  </div>
                  <div class="col-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-2 fw-bold btn-metodo-simple" id="btnSimple_QR" onclick="seleccionarMetodoSimple('QR')">
                      <i class="bi bi-qr-code-scan d-block fs-5"></i> QR
                    </button>
                  </div>
                </div>
              </div>

              <!-- PAGO MÚLTIPLE: Combinar Efectivo, Transferencia y QR -->
              <div id="boxPagoMultiple" style="display: none;">
                <div class="small text-muted mb-1" style="font-size: 0.73rem;">
                  Combine los montos en Efectivo, Transferencia y/o QR hasta cubrir el total:
                </div>

                <!-- Efectivo -->
                <div class="input-group input-group-sm mb-1">
                  <span class="input-group-text bg-white fw-semibold" style="width: 118px;">
                    <i class="bi bi-cash-stack text-success me-1"></i> Efectivo
                  </span>
                  <input type="number" class="form-control text-end fw-bold" id="pago_mult_efectivo" value="0" min="0" step="1000" onkeyup="actualizarSumaMultiple()" onchange="actualizarSumaMultiple()">
                  <button class="btn btn-outline-secondary px-2" type="button" onclick="asignarRestoMultiple('efectivo')" title="Completar saldo faltante aquí">
                    Resto
                  </button>
                </div>

                <!-- Transferencia -->
                <div class="input-group input-group-sm mb-1">
                  <span class="input-group-text bg-white fw-semibold" style="width: 118px;">
                    <i class="bi bi-bank text-primary me-1"></i> Transf.
                  </span>
                  <input type="number" class="form-control text-end fw-bold" id="pago_mult_transferencia" value="0" min="0" step="1000" onkeyup="actualizarSumaMultiple()" onchange="actualizarSumaMultiple()">
                  <button class="btn btn-outline-secondary px-2" type="button" onclick="asignarRestoMultiple('transferencia')" title="Completar saldo faltante aquí">
                    Resto
                  </button>
                </div>

                <!-- QR -->
                <div class="input-group input-group-sm mb-2">
                  <span class="input-group-text bg-white fw-semibold" style="width: 118px;">
                    <i class="bi bi-qr-code-scan text-info me-1"></i> Código QR
                  </span>
                  <input type="number" class="form-control text-end fw-bold" id="pago_mult_qr" value="0" min="0" step="1000" onkeyup="actualizarSumaMultiple()" onchange="actualizarSumaMultiple()">
                  <button class="btn btn-outline-secondary px-2" type="button" onclick="asignarRestoMultiple('qr')" title="Completar saldo faltante aquí">
                    Resto
                  </button>
                </div>

                <!-- Resumen de validación de Pago Múltiple -->
                <div class="d-flex justify-content-between align-items-center bg-white border rounded px-2 py-1 small">
                  <span class="text-muted">Suma asignada: <strong id="lblSumaMultiple">0 Gs.</strong></span>
                  <span class="badge bg-secondary-subtle text-secondary" id="badgeEstadoMultiple">Pendiente</span>
                </div>
              </div>
            </div>

            <!-- =========================================================
                 RESUMEN COMPACTO DE CRÉDITO (Y botón para abrir Ventana de Cuotas)
                 ========================================================= -->
            <div id="panelResumenCredito" class="p-2 bg-warning-subtle border border-warning rounded-3 mb-2" style="display: none;">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="fw-bold text-dark small"><i class="bi bi-calendar2-check text-warning me-1"></i> Venta a Crédito</span>
                <button type="button" class="btn btn-sm btn-warning fw-bold py-0 px-2" onclick="abrirModalCuotasCredito()">
                  <i class="bi bi-sliders me-1"></i> Configurar Cuotas
                </button>
              </div>
              <div class="bg-white rounded border p-2 small">
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Saldo Financiado:</span>
                  <strong class="text-danger" id="lblResumenCreditoSaldo">0 Gs.</strong>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Plan Seleccionado:</span>
                  <strong class="text-primary" id="lblResumenCreditoPlan">1 cuota</strong>
                </div>
              </div>
            </div>

            <!-- Entrega y Delivery -->
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label fw-semibold small mb-1">Entrega</label>
                <select class="form-select form-select-sm" id="metodo_entrega" onchange="toggleDelivery()">
                  <option value="MOSTRADOR">Mostrador</option>
                  <option value="DELIVERY">Delivery</option>
                </select>
              </div>
              <div class="col-6" id="boxMontoDelivery" style="display: none;">
                <label class="form-label fw-semibold small mb-1">Flete Gs.</label>
                <input type="number" class="form-control form-control-sm text-end" id="monto_delivery" value="0" min="0" step="1000" onkeyup="recalcularTotales()">
              </div>
            </div>

            <!-- Botón de Cobro / Procesar Venta -->
            <button type="button" class="btn btn-success btn-lg w-100 py-3 shadow fw-bold d-flex align-items-center justify-content-center gap-2" id="btnConfirmarVenta" onclick="procesarVenta()">
              <i class="bi bi-check2-circle fs-4"></i> FINALIZAR COBRO / CAJA RÁPIDA
            </button>
          </div>
        </div>

      </div>

    </div>

    <!-- Sección de Facturas Procesadas Recientes (Impresión y Anulación Rápida) -->
    <div class="card border-0 shadow-sm rounded-3 mt-3">
      <div class="card-header bg-white py-2 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="fw-bold text-dark"><i class="bi bi-receipt-cutoff text-primary me-1"></i> Últimas Facturas Procesadas</span>
          <span class="badge bg-light text-secondary border" id="contadorRecientesPOS">Cargando...</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="cargarFacturasRecientesPOS()">
            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
          </button>
          <a href="<?= BASE_URL ?>paginas/movimientos/ventas_historial.php" class="btn btn-sm btn-outline-primary py-0 px-2">
            Ver Historial Completo →
          </a>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="text-center" style="width: 60px;">ID</th>
                <th>Nro. Factura</th>
                <th>Fecha</th>
                <th>Cliente / Razón Social</th>
                <th class="text-center">Condición</th>
                <th class="text-center">Pago</th>
                <th class="text-end">Total</th>
                <th class="text-center">Estado</th>
                <th class="text-end pe-3" style="width: 190px;">Acciones</th>
              </tr>
            </thead>
            <tbody id="tbodyFacturasRecientesPOS">
              <tr>
                <td colspan="9" class="text-center py-3 text-muted">Cargando facturas procesadas...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- Modal Ticket / Comprobante Listo para Imprimir -->
<div class="modal fade" id="modalTicket" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom bg-light py-2">
        <h6 class="modal-title fw-bold text-success mb-0"><i class="bi bi-check-circle me-1"></i> Factura Registrada</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3 font-monospace small" id="contenidoImpresionTicket">
        <!-- Render dinámico del ticket -->
      </div>
      <div class="modal-footer border-top bg-light py-2">
        <div class="d-flex gap-2 w-100">
          <button type="button" class="btn btn-primary btn-sm flex-fill" onclick="imprimirTicket('ticket')">
            <i class="bi bi-receipt me-1"></i> Ticket (80mm)
          </button>
          <button type="button" class="btn btn-dark btn-sm flex-fill" onclick="imprimirTicket('a4')">
            <i class="bi bi-file-earmark-text me-1"></i> Factura A4
          </button>
        </div>
        <div class="d-flex gap-2 w-100 mt-1">
          <button type="button" class="btn btn-outline-danger btn-sm w-50" onclick="anularUltimoTicket()">
            <i class="bi bi-x-circle me-1"></i> Anular
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm w-50" data-bs-dismiss="modal" onclick="limpiarCarrito()">
            <i class="bi bi-plus-lg me-1"></i> Nueva Venta
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Configuración de Cuotas (Salta automáticamente al elegir CRÉDITO) -->
<div class="modal fade" id="modalConfigCuotas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-warning-subtle border-bottom">
        <div>
          <h5 class="modal-title fw-bold text-dark mb-0">
            <i class="bi bi-calendar2-week text-warning me-2"></i> Plan de Financiación / Cuotas a Crédito
          </h5>
          <small class="text-muted">Configure la cantidad de cuotas, plazos y entrega inicial</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <!-- Estado de Crédito del Cliente -->
        <div class="d-flex align-items-center justify-content-between bg-light border rounded p-2 mb-2">
          <div>
            <small class="text-muted d-block">Cliente para el Crédito:</small>
            <strong class="text-dark small" id="lblModalCreditoCliente">Seleccione un cliente</strong>
          </div>
          <span class="badge bg-secondary-subtle text-secondary small" id="badgeEstadoCreditoCliente">Verificando...</span>
        </div>

        <!-- Alerta si el cliente es Consumidor Final -->
        <div id="alertaClienteCredito" class="alert alert-danger py-2 px-2 small mb-2 d-none d-flex align-items-center justify-content-between">
          <span><i class="bi bi-exclamation-triangle-fill me-1"></i> Seleccione un cliente con Cédula/RUC.</span>
          <button type="button" class="btn btn-xs btn-danger btn-sm py-0 px-2" onclick="modalConfigCuotas.hide(); abrirModalBuscarCliente();">
            Elegir Cliente
          </button>
        </div>

        <!-- Frecuencia y Cantidad de Cuotas -->
        <div class="row g-2 mb-2">
          <div class="col-6">
            <label class="form-label fw-semibold small mb-1">Frecuencia / Plazo</label>
            <select class="form-select form-select-sm" id="credito_plazo_dias" onchange="calcularPlanCuotas()">
              <option value="15">Cada 15 días (Quincenal)</option>
              <option value="30" selected>Cada 30 días (Mensual)</option>
              <option value="45">Cada 45 días</option>
              <option value="60">Cada 60 días (Bimestral)</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold small mb-1">Cantidad de Cuotas</label>
            <select class="form-select form-select-sm fw-bold" id="credito_cuotas" onchange="calcularPlanCuotas()">
              <option value="1" selected>1 Pago (A vencimiento)</option>
              <option value="2">2 Cuotas</option>
              <option value="3">3 Cuotas</option>
              <option value="4">4 Cuotas</option>
              <option value="5">5 Cuotas</option>
              <option value="6">6 Cuotas</option>
              <option value="10">10 Cuotas</option>
              <option value="12">12 Cuotas</option>
            </select>
          </div>
        </div>

        <!-- Primer Vencimiento y Entrega Inicial -->
        <div class="row g-2 mb-2">
          <div class="col-6">
            <label class="form-label fw-semibold small mb-1">1er Vencimiento</label>
            <input type="date" class="form-control form-control-sm" id="credito_primer_venc" onchange="calcularPlanCuotas()">
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold small mb-1">Entrega Inicial (Gs.)</label>
            <input type="number" class="form-control form-control-sm text-end fw-bold text-success" id="credito_entrega_inicial" value="0" min="0" step="1000" onkeyup="calcularPlanCuotas()" onchange="calcularPlanCuotas()">
          </div>
        </div>

        <!-- Medio de Pago para la Entrega Inicial -->
        <div class="mb-2" id="boxMetodoEntregaInicial" style="display: none;">
          <label class="form-label fw-semibold small mb-1">Forma de Pago de la Entrega Inicial</label>
          <select class="form-select form-select-sm" id="credito_metodo_entrega">
            <option value="EFECTIVO" selected>💵 Efectivo (Ingresa a caja hoy)</option>
            <option value="TRANSFERENCIA">🏦 Transferencia Bancaria</option>
            <option value="QR">📱 Código QR</option>
          </select>
        </div>

        <!-- Resumen y Cuotas Proyectadas -->
        <div class="bg-light rounded border p-2 mt-2">
          <div class="d-flex justify-content-between small mb-1">
            <span class="text-muted">Saldo a Financiar:</span>
            <strong class="text-danger fs-6" id="lblSaldoAFinanciar">0 Gs.</strong>
          </div>
          <div class="d-flex justify-content-between small mb-1">
            <span class="text-muted">Monto por Cuota:</span>
            <strong class="text-primary" id="lblMontoPorCuota">0 Gs.</strong>
          </div>
          <div class="border-top pt-2 mt-1 small" id="lblDetalleFechasCuotas" style="max-height: 150px; overflow-y: auto; font-size: 0.78rem;">
            <!-- Lista de cuotas proyectadas -->
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light py-2 border-top d-flex justify-content-between">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="seleccionarCondicionPago('CONTADO', false)" data-bs-dismiss="modal">
          Volver a Contado
        </button>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-primary btn-sm px-3 fw-semibold" data-bs-dismiss="modal">
            <i class="bi bi-check2 me-1"></i> Confirmar Cuotas
          </button>
          <button type="button" class="btn btn-success btn-sm px-3 fw-bold" onclick="modalConfigCuotas.hide(); procesarVenta();">
            <i class="bi bi-check2-circle me-1"></i> Facturar Ahora
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Rápido: Nuevo Cliente -->
<div class="modal fade" id="modalRapidoCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom">
        <h5 class="modal-title fw-bold">Nuevo Cliente Rápido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formClienteRapido" onsubmit="guardarClienteRapido(event)">
        <div class="modal-body py-3">
          <div class="mb-3">
            <label class="form-label fw-semibold small">Tipo de Cliente <span class="text-danger">*</span></label>
            <select class="form-select" id="rapido_tipo_cliente" required>
              <option value="MAYORISTA" selected>🏢 Mayorista (Compras por volumen)</option>
              <option value="CLIENTE FIEL">⭐ Cliente Fiel (Comprador frecuente)</option>
              <option value="MINORISTA">👤 Minorista / Particular</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">Nombre / Razón Social <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="rapido_nombre" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">Cédula o RUC <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="rapido_cedula" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">Teléfono</label>
            <input type="text" class="form-control" id="rapido_telefono">
          </div>
        </div>
        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-sm px-3">Guardar y Seleccionar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Catálogo de Productos con Stock Disponible -->
<div class="modal fade" id="modalCatalogoStock" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light py-3 border-bottom">
        <div>
          <h5 class="modal-title fw-bold text-dark mb-0">
            <i class="bi bi-boxes text-primary me-2"></i> Catálogo de Productos con Stock Disponible
          </h5>
          <small class="text-muted">Sucursal <?= $sucursalActual == 1 ? '1 (Avda Lopez Godoy)' : '2 (Avda Mcal Lopez)' ?> • Solo ítems con existencia activa (> 0)</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <div class="row g-2 mb-3">
          <div class="col-12 col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control" id="filtroModalCatalogo" placeholder="Buscar por nombre, código o marca..." onkeyup="filtrarCatalogoModal(event)">
            </div>
          </div>
          <div class="col-12 col-md-4">
            <select class="form-select" id="filtroCatModal" onchange="cargarCatalogoStock()">
              <option value="">-- Todas las Categorías --</option>
              <?php
              $cats = $pdo->query("SELECT id_categorias, descripcion FROM categorias WHERE estado = 'ACTIVO' ORDER BY descripcion ASC")->fetchAll(PDO::FETCH_ASSOC);
              foreach ($cats as $c) {
                echo "<option value=\"{$c['id_categorias']}\">" . htmlspecialchars($c['descripcion']) . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-12 col-md-2 text-end">
            <button class="btn btn-outline-secondary w-100" type="button" onclick="cargarCatalogoStock()">
              <i class="bi bi-arrow-clockwise me-1"></i> Actualizar
            </button>
          </div>
        </div>

        <div class="table-responsive" style="max-height: 480px;">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light sticky-top">
              <tr>
                <th style="width: 110px;">Código</th>
                <th>Producto / Descripción</th>
                <th>Marca / Categoría</th>
                <th style="width: 140px;" class="text-end">Precio (Gs.)</th>
                <th style="width: 120px;" class="text-center">Stock Disp.</th>
                <th style="width: 110px;" class="text-center">Acción</th>
              </tr>
            </thead>
            <tbody id="tbodyCatalogoStock">
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div> Cargando productos disponibles...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer bg-light py-2 border-top d-flex justify-content-between">
        <span class="small text-muted" id="lblInfoCatalogo">Mostrando productos en stock</span>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Directorio Completo de Clientes -->
<div class="modal fade" id="modalBuscarCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light py-2 border-bottom">
        <div>
          <h6 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i> Directorio de Clientes</h6>
          <small class="text-muted">Busca y selecciona el cliente por Nombre, Apellido, Cédula de Identidad o RUC</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <div class="d-flex gap-2 mb-3">
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" id="filtroModalClientes" class="form-control" 
                   placeholder="Escribir nombre, apellido, CI o RUC a filtrar..." 
                   autocomplete="off" onkeyup="filtrarTablaClientesModal(event)">
          </div>
          <button class="btn btn-outline-secondary" type="button" onclick="cargarClientesEnModal()" title="Recargar">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
          <button class="btn btn-primary text-nowrap" type="button" onclick="abrirModalNuevoClienteDesdeListado()">
            <i class="bi bi-person-plus me-1"></i> + Nuevo
          </button>
        </div>

        <div class="table-responsive" style="max-height: 400px;">
          <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light sticky-top">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Cliente / Razón Social</th>
                <th style="width: 140px;">CI / RUC</th>
                <th style="width: 130px;">Teléfono</th>
                <th>Ciudad</th>
                <th style="width: 110px;" class="text-center">Acción</th>
              </tr>
            </thead>
            <tbody id="tbodyClientesModal">
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div> Cargando clientes...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer bg-light py-2 border-top d-flex justify-content-between">
        <span class="small text-muted" id="lblInfoModalClientes">0 clientes</span>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
const URL_PROD = '<?= BASE_URL ?>controladores/ProductosControlador.php';
const URL_VENTAS = '<?= BASE_URL ?>controladores/VentasControlador.php';
const URL_CLIENTES = '<?= BASE_URL ?>controladores/ClientesControlador.php';
const ID_SUCURSAL = <?= $sucursalActual ?>;

let carrito = [];
let debounceProd = null;
let modalTicket = null;
let modalRapidoCli = null;
let modalCatalogo = null;
let modalBuscarCli = null;
let modalConfigCuotas = null;
let ultimoDetalleVenta = null;
let debounceModalCat = null;
let debounceCliente = null;
let debounceModalCli = null;

const formatGs = (val) => new Intl.NumberFormat('es-PY').format(val || 0) + ' Gs.';

document.addEventListener('DOMContentLoaded', () => {
  modalTicket = new bootstrap.Modal(document.getElementById('modalTicket'));
  modalRapidoCli = new bootstrap.Modal(document.getElementById('modalRapidoCliente'));
  modalCatalogo = new bootstrap.Modal(document.getElementById('modalCatalogoStock'));
  modalBuscarCli = new bootstrap.Modal(document.getElementById('modalBuscarCliente'));
  modalConfigCuotas = new bootstrap.Modal(document.getElementById('modalConfigCuotas'));

  cargarSiguienteFactura();
  cargarFacturasRecientesPOS();

  // Cerrar dropdowns al hacer click fuera
  document.addEventListener('click', (e) => {
    const boxProd = document.getElementById('dropdownResultados');
    if (boxProd && !e.target.closest('#inputBuscarProducto') && !e.target.closest('#dropdownResultados')) {
      boxProd.classList.add('d-none');
    }

    const boxCli = document.getElementById('dropdownClientes');
    if (boxCli && !e.target.closest('#inputBuscarCliente') && !e.target.closest('#dropdownClientes')) {
      boxCli.classList.add('d-none');
    }
  });

  document.getElementById('inputBuscarProducto').focus();
});

// ==========================================
// BUSCADOR Y SELECTOR DE CLIENTES (Nombre, CI o RUC)
// ==========================================
function mostrarDropdownClientes() {
  const q = document.getElementById('inputBuscarCliente').value.trim();
  buscarClienteAjax(q);
}

function limpiarBuscadorCliente() {
  document.getElementById('inputBuscarCliente').value = '';
  document.getElementById('dropdownClientes').classList.add('d-none');
}

function buscarClienteTeclado(e) {
  const q = e.target.value.trim();
  const dropdown = document.getElementById('dropdownClientes');

  if (e.key === 'Escape') {
    dropdown.classList.add('d-none');
    return;
  }

  clearTimeout(debounceCliente);
  debounceCliente = setTimeout(() => {
    buscarClienteAjax(q);
  }, 200);
}

function badgeTipoClienteHtml(tipo) {
  const t = (tipo || 'MAYORISTA').toUpperCase();
  if (t === 'CLIENTE FIEL') {
    return `<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill ms-1" style="font-size: 0.65rem;"><i class="bi bi-star-fill me-1"></i>CLIENTE FIEL</span>`;
  }
  if (t === 'MINORISTA') {
    return `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill ms-1" style="font-size: 0.65rem;">MINORISTA</span>`;
  }
  return `<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1" style="font-size: 0.65rem;"><i class="bi bi-building-check me-1"></i>MAYORISTA</span>`;
}

async function buscarClienteAjax(q) {
  const dropdown = document.getElementById('dropdownClientes');
  try {
    const res = await fetch(`${URL_CLIENTES}?accion=buscar_cliente&q=${encodeURIComponent(q)}`);
    const json = await res.json();

    if (json.status === 'success' && json.data && json.data.length > 0) {
      let html = '<div class="px-2 py-1 bg-light border-bottom text-muted small fw-semibold">Seleccionar cliente coincidente:</div>';
      json.data.forEach(c => {
        const nombreCompleto = `${c.nombre_cliente} ${c.apellido_cliente || ''}`.trim();
        html += `
          <div class="search-result-item d-flex align-items-center justify-content-between py-2 px-3 border-bottom" 
               style="cursor: pointer;" onclick='seleccionarCliente(${jsonAttr(c)})'>
            <div class="overflow-hidden me-2">
              <div class="d-flex align-items-center flex-wrap">
                <strong class="text-dark text-truncate">${escapeHtml(nombreCompleto)}</strong>
                ${badgeTipoClienteHtml(c.tipo_cliente)}
              </div>
              <small class="text-muted font-monospace">
                <i class="bi bi-card-text me-1"></i>CI/RUC: <strong>${escapeHtml(c.cedula_cliente)}</strong>
                ${c.telefono_cliente ? ' • Tel: ' + escapeHtml(c.telefono_cliente) : ''}
                ${c.nombre_ciudad ? ' • ' + escapeHtml(c.nombre_ciudad) : ''}
              </small>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-nowrap">
              <i class="bi bi-check2"></i> Elegir
            </span>
          </div>
        `;
      });
      dropdown.innerHTML = html;
      dropdown.classList.remove('d-none');
    } else {
      dropdown.innerHTML = '<div class="p-3 text-muted text-center small">No se encontró ningún cliente con ese Nombre o CI/RUC.</div>';
      dropdown.classList.remove('d-none');
    }
  } catch (err) {
    console.error(err);
  }
}

function seleccionarCliente(c) {
  const nombreCompleto = `${c.nombre_cliente} ${c.apellido_cliente || ''}`.trim();
  const tipoCli = (c.tipo_cliente || 'MAYORISTA').toUpperCase();
  document.getElementById('id_cliente').value = c.id_cliente;
  document.getElementById('cliente_nombre_completo').value = nombreCompleto;
  const inputTipo = document.getElementById('cliente_tipo_actual');
  if (inputTipo) inputTipo.value = tipoCli;
  document.getElementById('lblClienteNombre').innerText = nombreCompleto;
  const lblTipo = document.getElementById('lblClienteTipo');
  if (lblTipo) {
    lblTipo.innerText = tipoCli;
    lblTipo.className = 'badge rounded-pill border ' + (
      tipoCli === 'CLIENTE FIEL'
        ? 'bg-warning-subtle text-warning border-warning-subtle'
        : tipoCli === 'MINORISTA'
          ? 'bg-secondary-subtle text-secondary border-secondary-subtle'
          : 'bg-primary-subtle text-primary border-primary-subtle'
    );
  }
  document.getElementById('lblClienteDoc').innerText = `CI/RUC: ${c.cedula_cliente}${c.telefono_cliente ? ' • Tel: ' + c.telefono_cliente : ''}`;
  
  document.getElementById('dropdownClientes').classList.add('d-none');
  document.getElementById('inputBuscarCliente').value = '';
  
  if (modalBuscarCli) {
    modalBuscarCli.hide();
  }

  // Recalcular descuentos de todos los productos en el carrito según el tipo del nuevo cliente
  if (carrito.length > 0) {
    carrito.forEach(it => aplicarDescuentoMayorista(it));
    renderCarrito();
  }

  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 1500
  });
  Toast.fire({
    icon: 'success',
    title: `Cliente: ${nombreCompleto} (${tipoCli})`
  });

  if (document.getElementById('condicion_pago').value === 'CREDITO') {
    verificarCreditoCliente(c.id_cliente);
    calcularPlanCuotas();
  }
}

function abrirModalBuscarCliente() {
  modalBuscarCli.show();
  cargarClientesEnModal();
}

function filtrarTablaClientesModal(e) {
  clearTimeout(debounceModalCli);
  debounceModalCli = setTimeout(() => {
    const q = document.getElementById('filtroModalClientes').value.trim();
    cargarClientesEnModal(q);
  }, 250);
}

async function cargarClientesEnModal(q = '') {
  const tbody = document.getElementById('tbodyClientesModal');
  const info = document.getElementById('lblInfoModalClientes');

  tbody.innerHTML = `
    <tr>
      <td colspan="6" class="text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm text-primary me-2"></div> Buscando clientes...
      </td>
    </tr>
  `;

  try {
    const res = await fetch(`${URL_CLIENTES}?accion=buscar_cliente&q=${encodeURIComponent(q)}`);
    const json = await res.json();

    if (json.status === 'success' && json.data && json.data.length > 0) {
      let html = '';
      json.data.forEach((c, idx) => {
        const nombreCompleto = `${c.nombre_cliente} ${c.apellido_cliente || ''}`.trim();
        html += `
          <tr>
            <td class="text-center text-muted fw-bold">${idx + 1}</td>
            <td>
              <div class="d-flex align-items-center flex-wrap">
                <strong class="text-dark">${escapeHtml(nombreCompleto)}</strong>
                ${badgeTipoClienteHtml(c.tipo_cliente)}
              </div>
              ${c.direccion_cliente ? `<small class="text-muted d-block text-truncate" style="max-width: 250px;">${escapeHtml(c.direccion_cliente)}</small>` : ''}
            </td>
            <td><span class="badge bg-light text-dark border font-monospace fs-6">${escapeHtml(c.cedula_cliente)}</span></td>
            <td>${escapeHtml(c.telefono_cliente || 'S/N')}</td>
            <td><span class="badge bg-light text-secondary border">${escapeHtml(c.nombre_ciudad || 'Central')}</span></td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-2 py-1" onclick='seleccionarCliente(${jsonAttr(c)})'>
                <i class="bi bi-check2-circle me-1"></i> Seleccionar
              </button>
            </td>
          </tr>
        `;
      });
      tbody.innerHTML = html;
      info.innerText = `${json.data.length} cliente(s) encontrado(s)`;
    } else {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron clientes coincidentes.</td></tr>`;
      info.innerText = `0 clientes`;
    }
  } catch (err) {
    console.error(err);
    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">Error al consultar clientes.</td></tr>`;
  }
}

function abrirModalNuevoClienteDesdeListado() {
  modalBuscarCli.hide();
  abrirModalNuevoCliente();
}

async function cargarSiguienteFactura() {
  try {
    const res = await fetch(`${URL_VENTAS}?accion=siguiente_factura&sucursal=${ID_SUCURSAL}`);
    const json = await res.json();
    if (json.status === 'success') {
      document.getElementById('nro_factura').value = json.nro_factura;
    }
  } catch (err) {
    console.error(err);
  }
}

function mostrarSugerenciasIniciales() {
  const query = document.getElementById('inputBuscarProducto').value.trim();
  ejecutarBusquedaDropdown(query);
}

function ejecutarBusquedaManual() {
  const query = document.getElementById('inputBuscarProducto').value.trim();
  ejecutarBusquedaDropdown(query);
}

function buscarProductoTeclado(e) {
  const query = e.target.value.trim();
  const dropdown = document.getElementById('dropdownResultados');

  if (e.key === 'Enter') {
    e.preventDefault();
    if (query) {
      ejecutarBusquedaExacta(query);
    }
    return;
  }

  clearTimeout(debounceProd);
  debounceProd = setTimeout(() => {
    ejecutarBusquedaDropdown(query);
  }, 200);
}

async function ejecutarBusquedaExacta(q) {
  try {
    const res = await fetch(`${URL_PROD}?accion=buscar_para_factura&q=${encodeURIComponent(q)}&sucursal=${ID_SUCURSAL}`);
    const json = await res.json();
    // Excluir terminantemente productos que no tengan stock
    const prods = (json.data || []).filter(p => parseInt(p.stock_disponible || 0) > 0);

    if (json.status === 'success' && prods.length > 0) {
      // Si hay coincidencia exacta o primer elemento con stock, agregarlo
      agregarAlCarrito(prods[0]);
      document.getElementById('inputBuscarProducto').value = '';
      document.getElementById('dropdownResultados').classList.add('d-none');
    } else {
      Swal.fire({
        icon: 'info',
        title: 'Sin Stock o No Encontrado',
        text: `El producto "${q}" no cuenta con stock disponible en esta sucursal o no fue encontrado.`,
        timer: 2000,
        showConfirmButton: false
      });
    }
  } catch (err) {
    console.error(err);
  }
}

async function ejecutarBusquedaDropdown(q) {
  const dropdown = document.getElementById('dropdownResultados');
  try {
    const res = await fetch(`${URL_PROD}?accion=buscar_para_factura&q=${encodeURIComponent(q)}&sucursal=${ID_SUCURSAL}`);
    const json = await res.json();

    if (json.status === 'success' && json.data && json.data.length > 0) {
      // Filtrar estrictamente productos que tengan stock > 0
      const prods = json.data.filter(p => parseInt(p.stock_disponible || 0) > 0);

      if (prods.length === 0) {
        dropdown.innerHTML = '<div class="p-3 text-muted text-center small"><i class="bi bi-box-seam me-1"></i>No hay productos con stock disponible para esta búsqueda.</div>';
        dropdown.classList.remove('d-none');
        return;
      }

      let html = '';
      if (!q) {
        html += '<div class="px-3 py-2 bg-light border-bottom text-muted small fw-semibold d-flex justify-content-between align-items-center"><span><i class="bi bi-lightning-charge text-primary me-1"></i> Productos disponibles con Stock (Sucursal ' + ID_SUCURSAL + ')</span><span class="badge bg-success-subtle text-success">' + prods.length + ' ítems</span></div>';
      }
      prods.forEach(p => {
        const stockDispo = parseInt(p.stock_disponible || 0);
        const stockColor = stockDispo <= 5 ? 'text-warning' : 'text-success';

        html += `
          <div class="search-result-item d-flex align-items-center justify-content-between" onclick='seleccionarProducto(${jsonAttr(p)})'>
            <div>
              <strong class="text-dark d-block">${escapeHtml(p.nombre_prod)}</strong>
              <div class="small text-muted">
                <span class="badge bg-light text-dark border font-monospace me-1">${escapeHtml(p.codigo_barra || 'S/C')}</span>
                ${p.marca_nombre ? `<span class="me-2 text-secondary">${escapeHtml(p.marca_nombre)}</span>` : ''}
                <span class="${stockColor} fw-semibold"><i class="bi bi-box-seam me-1"></i>Stock: ${stockDispo} un.</span>
              </div>
            </div>
            <div class="text-end">
              <span class="fw-bold fs-6 text-primary">${formatGs(p.precio_venta)}</span>
            </div>
          </div>
        `;
      });
      dropdown.innerHTML = html;
      dropdown.classList.remove('d-none');
    } else {
      dropdown.innerHTML = '<div class="p-3 text-muted text-center small">No se encontraron productos con stock disponible.</div>';
      dropdown.classList.remove('d-none');
    }
  } catch (err) {
    console.error(err);
  }
}

function abrirModalCatalogoStock() {
  modalCatalogo.show();
  cargarCatalogoStock();
}

function filtrarCatalogoModal(e) {
  clearTimeout(debounceModalCat);
  debounceModalCat = setTimeout(() => {
    cargarCatalogoStock();
  }, 250);
}

async function cargarCatalogoStock() {
  const tbody = document.getElementById('tbodyCatalogoStock');
  const info = document.getElementById('lblInfoCatalogo');
  const q = (document.getElementById('filtroModalCatalogo')?.value || '').trim();
  const cat = document.getElementById('filtroCatModal')?.value || '';

  tbody.innerHTML = `
    <tr>
      <td colspan="6" class="text-center py-4 text-muted">
        <div class="spinner-border spinner-border-sm text-primary me-2"></div> Buscando productos con existencia...
      </td>
    </tr>
  `;

  try {
    const res = await fetch(`${URL_PROD}?accion=catalogo_factura&sucursal=${ID_SUCURSAL}&categoria=${encodeURIComponent(cat)}&q=${encodeURIComponent(q)}`);
    const json = await res.json();

    if (json.status === 'success' && json.data && json.data.length > 0) {
      // Filtrar estrictamente productos que tengan stock > 0
      const prods = json.data.filter(p => parseInt(p.stock_disponible || 0) > 0);

      if (prods.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron productos con stock disponible.</td></tr>`;
        info.innerText = `0 productos en existencia`;
        return;
      }

      let html = '';
      prods.forEach(p => {
        const stk = parseInt(p.stock_disponible || 0);
        const badgeColor = stk <= 5 ? 'bg-warning-subtle text-warning border-warning-subtle' : 'bg-success-subtle text-success border-success-subtle';

        html += `
          <tr>
            <td><span class="badge bg-light text-dark border font-monospace">${escapeHtml(p.codigo_barra || 'S/C')}</span></td>
            <td>
              <div class="fw-bold text-dark">${escapeHtml(p.nombre_prod)}</div>
              ${p.descripcion ? `<small class="text-muted text-truncate d-block" style="max-width: 320px;">${escapeHtml(p.descripcion)}</small>` : ''}
            </td>
            <td>
              <span class="badge bg-light text-secondary border me-1">${escapeHtml(p.marca_nombre || 'Genérica')}</span>
              ${p.categoria_nombre ? `<small class="text-muted">${escapeHtml(p.categoria_nombre)}</small>` : ''}
            </td>
            <td class="text-end fw-bold text-primary fs-6">${formatGs(p.precio_venta)}</td>
            <td class="text-center">
              <span class="badge border ${badgeColor} px-2 py-1 fs-6">
                <i class="bi bi-box-seam me-1"></i>${stk} un.
              </span>
            </td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-success fw-bold px-2 py-1 shadow-sm d-flex align-items-center justify-content-center gap-1 mx-auto"
                      onclick='facturarDesdeModal(${jsonAttr(p)})'>
                <i class="bi bi-plus-circle"></i> + Agregar
              </button>
            </td>
          </tr>
        `;
      });
      tbody.innerHTML = html;
      info.innerText = `Mostrando ${prods.length} productos con stock disponible (Sucursal ${ID_SUCURSAL})`;
    } else {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron productos con stock en esta sucursal.</td></tr>`;
      info.innerText = `0 productos`;
    }
  } catch (err) {
    console.error(err);
    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">Error al consultar el catálogo.</td></tr>`;
  }
}

function facturarDesdeModal(p) {
  agregarAlCarrito(p);
  const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 1200,
    timerProgressBar: true
  });
  Toast.fire({
    icon: 'success',
    title: `"${p.nombre_prod}" agregado a la factura`
  });
}

function seleccionarProducto(p) {
  agregarAlCarrito(p);
  document.getElementById('inputBuscarProducto').value = '';
  document.getElementById('dropdownResultados').classList.add('d-none');
  document.getElementById('inputBuscarProducto').focus();
}

function agregarAlCarrito(producto) {
  const stockDispo = producto.stock_disponible ?? (producto.stock_actual || 0);
  if (stockDispo <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Producto sin Stock',
      text: `El producto "${producto.nombre_prod}" no cuenta con unidades disponibles en esta sucursal.`,
      timer: 2000,
      showConfirmButton: false
    });
    return;
  }

  let itemActual = null;
  const idx = carrito.findIndex(it => it.id_producto == producto.id_producto);
  if (idx !== -1) {
    if (carrito[idx].cantidad + 1 > stockDispo) {
      Swal.fire({
        icon: 'warning',
        title: 'Límite de Stock',
        text: `Solo hay ${stockDispo} unidades disponibles en stock para "${producto.nombre_prod}".`,
        timer: 1800,
        showConfirmButton: false
      });
      return;
    }
    carrito[idx].cantidad += 1;
    aplicarDescuentoMayorista(carrito[idx]);
    itemActual = carrito[idx];
  } else {
    const precioLista = parseInt(producto.precio_venta || 0) || (parseInt(producto.precio_compra || 0) > 0 ? Math.round(parseInt(producto.precio_compra) * 1.35) : 35000);
    const item = {
      id_producto: producto.id_producto,
      nombre_prod: producto.nombre_prod,
      codigo_barra: producto.codigo_barra,
      precio_base: precioLista,
      precio_compra: parseInt(producto.precio_compra || 0),
      precio_unitario: precioLista,
      cantidad: 1,
      id_tipo_impuesto: producto.id_tipo_impuesto || 1,
      stock_disponible: stockDispo
    };
    aplicarDescuentoMayorista(item);
    carrito.push(item);
    itemActual = item;
  }

  // Mostrar el precio estirado inmediatamente en el visor superior
  const inputPrecioEst = document.getElementById('inputPrecioEstirado');
  if (inputPrecioEst && itemActual) {
    inputPrecioEst.value = new Intl.NumberFormat('es-PY').format(itemActual.precio_unitario);
  }

  renderCarrito();
}

function obtenerTipoClienteActivo() {
  const el = document.getElementById('cliente_tipo_actual');
  return (el && el.value ? el.value : 'MAYORISTA').toUpperCase();
}

function aplicarDescuentoMayorista(item) {
  const base = item.precio_base;
  if (!base) return;

  const tipoCliente = obtenerTipoClienteActivo();
  let pct = 0;
  let etiqueta = 'Precio Lista';
  let badge = 'bg-light text-muted border';

  if (tipoCliente === 'MAYORISTA') {
    // Mayorista tiene los mayores descuentos (5% desde 1 un., 7% en 5 un., 10% en >5 un.)
    if (item.cantidad > 5) {
      pct = 10;
      etiqueta = 'Mayorista >5 un. (10% OFF)';
      badge = 'bg-success text-white';
    } else if (item.cantidad >= 5) {
      pct = 7;
      etiqueta = 'Mayorista 5 un. (7% OFF)';
      badge = 'bg-primary text-white';
    } else {
      pct = 5;
      etiqueta = 'Mayorista (5% OFF)';
      badge = 'bg-primary-subtle text-primary border border-primary-subtle';
    }
  } else if (tipoCliente === 'CLIENTE FIEL') {
    // Cliente Fiel tiene menos descuento que un Mayorista (2% desde 1 un., 3% en 5 un., 5% en >5 un.)
    if (item.cantidad > 5) {
      pct = 5;
      etiqueta = 'Cliente Fiel >5 un. (5% OFF)';
      badge = 'bg-warning text-dark';
    } else if (item.cantidad >= 5) {
      pct = 3;
      etiqueta = 'Cliente Fiel 5 un. (3% OFF)';
      badge = 'bg-warning-subtle text-warning border border-warning-subtle';
    } else {
      pct = 2;
      etiqueta = 'Cliente Fiel (2% OFF)';
      badge = 'bg-warning-subtle text-warning border border-warning-subtle';
    }
  } else {
    // Minorista / Particular (0% en 1-4 un., 2% en 5+ un.)
    if (item.cantidad >= 5) {
      pct = 2;
      etiqueta = '5+ un. (2% OFF)';
      badge = 'bg-secondary text-white';
    } else {
      pct = 0;
      etiqueta = 'Minorista (Lista)';
      badge = 'bg-light text-muted border';
    }
  }

  const montoDesc = Math.round(base * (pct / 100));
  const pisoCosto = (item.precio_compra > 0 && item.precio_compra < base) ? item.precio_compra : 0;
  item.precio_unitario = (pct > 0) ? Math.max(pisoCosto, base - montoDesc) : base;
  item.descuento_tipo = etiqueta;
  item.descuento_badge = badge;
  item.porcentaje_desc = pct;
}

function renderCarrito() {
  const tbody = document.getElementById('tablaCarrito');
  const contador = document.getElementById('contadorItems');

  if (carrito.length === 0) {
    tbody.innerHTML = `
      <tr id="filaVacia">
        <td colspan="6" class="text-center py-5 text-muted">
          <div class="py-4">
            <i class="bi bi-cart-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
            <h6 class="fw-bold text-dark">El carrito de facturación está vacío</h6>
            <p class="small text-muted mb-0">Escanea un código de barras o busca productos arriba para comenzar la venta.</p>
          </div>
        </td>
      </tr>
    `;
    contador.innerText = '0 productos';
    recalcularTotales();
    return;
  }

  let html = '';
  carrito.forEach((item, index) => {
    const subtotal = item.cantidad * item.precio_unitario;
    const tieneDescuento = item.precio_unitario < item.precio_base;

    html += `
      <tr>
        <td class="text-center text-muted fw-bold">${index + 1}</td>
        <td>
          <div class="d-flex align-items-center gap-1 flex-wrap">
            <span class="fw-bold text-dark">${escapeHtml(item.nombre_prod)}</span>
            <span class="badge ${item.descuento_badge || 'bg-light text-muted border'} small" style="font-size: 0.72rem;">
              <i class="bi bi-tag-fill me-1"></i>${item.descuento_tipo || 'Minorista'}
            </span>
          </div>
          <div class="d-flex align-items-center gap-2 mt-1">
            <small class="badge bg-light text-muted border font-monospace">${escapeHtml(item.codigo_barra || 'S/C')}</small>
            <small class="text-muted"><i class="bi bi-box-seam me-1"></i>Disp: <strong>${item.stock_disponible}</strong> un.</small>
            ${tieneDescuento ? `<small class="text-success fw-semibold"><i class="bi bi-lightning-charge-fill"></i> Ahorro: ${formatGs(item.precio_base - item.precio_unitario)}/un. (-${item.porcentaje_desc}%)</small>` : ''}
          </div>
        </td>
        <td class="text-end">
          ${tieneDescuento ? `<small class="text-decoration-line-through text-muted d-block small">${formatGs(item.precio_base)}</small>` : ''}
          <input type="number" class="form-control form-control-sm text-end fw-bold ${tieneDescuento ? 'text-primary' : ''}" style="width: 125px; display: inline-block;" 
                 value="${item.precio_unitario}" min="0" step="100" onchange="actualizarPrecio(${index}, this.value)">
        </td>
        <td class="text-center">
          <div class="input-group input-group-sm justify-content-center" style="width: 110px; margin: auto;">
            <button class="btn btn-outline-secondary" type="button" onclick="modificarCantidad(${index}, -1)">-</button>
            <input type="number" class="form-control text-center fw-bold" value="${item.cantidad}" min="1" max="${item.stock_disponible}" onchange="setCantidad(${index}, this.value)">
            <button class="btn btn-outline-secondary" type="button" onclick="modificarCantidad(${index}, 1)">+</button>
          </div>
        </td>
        <td class="text-end fw-bold text-dark fs-6">${formatGs(subtotal)}</td>
        <td class="text-center">
          <button class="btn btn-link text-danger p-0" title="Quitar" onclick="eliminarDelCarrito(${index})">
            <i class="bi bi-trash fs-6"></i>
          </button>
        </td>
      </tr>
    `;
  });

  tbody.innerHTML = html;
  contador.innerText = `${carrito.length} productos (${carrito.reduce((acc, it) => acc + it.cantidad, 0)} un.)`;
  recalcularTotales();
}

function modificarCantidad(index, delta) {
  if (carrito[index]) {
    const nuevaCant = carrito[index].cantidad + delta;
    if (nuevaCant <= 0) {
      carrito.splice(index, 1);
    } else {
      if (nuevaCant > carrito[index].stock_disponible) {
        Swal.fire({
          icon: 'warning',
          title: 'Stock Límite',
          text: `Solo hay ${carrito[index].stock_disponible} unidades disponibles de "${carrito[index].nombre_prod}".`,
          timer: 1800,
          showConfirmButton: false
        });
        return;
      }
      carrito[index].cantidad = nuevaCant;
      aplicarDescuentoMayorista(carrito[index]);
    }
    renderCarrito();
  }
}

function setCantidad(index, val) {
  const c = parseInt(val) || 1;
  if (carrito[index]) {
    if (c > carrito[index].stock_disponible) {
      Swal.fire({
        icon: 'warning',
        title: 'Stock Límite',
        text: `Solo hay ${carrito[index].stock_disponible} unidades disponibles de este producto.`,
        timer: 1800,
        showConfirmButton: false
      });
      carrito[index].cantidad = carrito[index].stock_disponible;
    } else {
      carrito[index].cantidad = Math.max(1, c);
    }
    aplicarDescuentoMayorista(carrito[index]);
    renderCarrito();
  }
}

function actualizarPrecio(index, val) {
  const p = parseInt(val) || 0;
  if (carrito[index]) {
    carrito[index].precio_unitario = Math.max(0, p);
    renderCarrito();
  }
}

function eliminarDelCarrito(index) {
  carrito.splice(index, 1);
  renderCarrito();
}

function limpiarCarrito() {
  carrito = [];
  const ef = document.getElementById('pago_mult_efectivo');
  const tr = document.getElementById('pago_mult_transferencia');
  const qr = document.getElementById('pago_mult_qr');
  if (ef) ef.value = 0;
  if (tr) tr.value = 0;
  if (qr) qr.value = 0;
  renderCarrito();
}

function recalcularTotales() {
  let subtotal = 0;
  let gravada10 = 0;
  let gravada5 = 0;
  let exentas = 0;

  carrito.forEach(it => {
    const lin = it.cantidad * it.precio_unitario;
    subtotal += lin;
    if (it.id_tipo_impuesto == 1) {
      gravada10 += lin;
    } else if (it.id_tipo_impuesto == 2) {
      gravada5 += lin;
    } else {
      exentas += lin;
    }
  });

  const delivery = (document.getElementById('metodo_entrega').value === 'DELIVERY')
    ? (parseInt(document.getElementById('monto_delivery').value) || 0)
    : 0;

  const total = subtotal + delivery;

  // En Paraguay, IVA 10% = Total / 11
  const iva10 = Math.round(gravada10 / 11);
  const iva5 = Math.round(gravada5 / 21);

  document.getElementById('lblTotalVenta').innerText = formatGs(total);
  document.getElementById('lblIva10').innerText = formatGs(iva10);
  document.getElementById('lblIva5').innerText = formatGs(iva5);

  if (document.getElementById('condicion_pago').value === 'CREDITO') {
    calcularPlanCuotas();
  } else if (document.getElementById('tipo_pago_contado').value === 'MULTIPLE') {
    actualizarSumaMultiple();
  }
}

function obtenerTotalActualVenta() {
  const subtotal = carrito.reduce((sum, item) => sum + (item.cantidad * item.precio_unitario), 0);
  const delivery = (document.getElementById('metodo_entrega').value === 'DELIVERY')
    ? (parseInt(document.getElementById('monto_delivery').value) || 0)
    : 0;
  return subtotal + delivery;
}

function seleccionarCondicionPago(cond, abrirModal = false) {
  document.getElementById('condicion_pago').value = cond;
  document.getElementById('badgeCondicion').innerText = cond;

  const btnContado = document.getElementById('btnCondContado');
  const btnCredito = document.getElementById('btnCondCredito');
  const panelContado = document.getElementById('panelPagoContado');
  const panelResumenCredito = document.getElementById('panelResumenCredito');

  if (cond === 'CREDITO') {
    btnContado.className = 'btn btn-sm btn-outline-primary fw-bold';
    btnCredito.className = 'btn btn-sm btn-warning text-dark fw-bold shadow-sm';
    if (panelContado) panelContado.style.display = 'none';
    if (panelResumenCredito) panelResumenCredito.style.display = 'block';

    const fechaInput = document.getElementById('credito_primer_venc');
    if (fechaInput && !fechaInput.value) {
      const hoy = new Date();
      hoy.setDate(hoy.getDate() + 30);
      fechaInput.value = hoy.toISOString().split('T')[0];
    }

    const idCli = document.getElementById('id_cliente').value;
    verificarCreditoCliente(idCli);
    calcularPlanCuotas();

    if (abrirModal) {
      abrirModalCuotasCredito();
    }
  } else {
    btnContado.className = 'btn btn-sm btn-primary fw-bold shadow-sm';
    btnCredito.className = 'btn btn-sm btn-outline-warning text-dark fw-bold';
    if (panelContado) panelContado.style.display = 'block';
    if (panelResumenCredito) panelResumenCredito.style.display = 'none';
  }
}

function actualizarCondicionUI() {
  const cond = document.getElementById('condicion_pago').value;
  seleccionarCondicionPago(cond, cond === 'CREDITO');
}

function abrirModalCuotasCredito() {
  const nomCli = document.getElementById('lblClienteNombre').innerText;
  const docCli = document.getElementById('lblClienteDoc').innerText;
  const lblModalCli = document.getElementById('lblModalCreditoCliente');
  if (lblModalCli) {
    lblModalCli.innerText = `${nomCli} (${docCli})`;
  }
  const idCli = document.getElementById('id_cliente').value;
  verificarCreditoCliente(idCli);
  calcularPlanCuotas();
  if (modalConfigCuotas) {
    modalConfigCuotas.show();
  }
}

function cambiarModoContado(modo) {
  document.getElementById('tipo_pago_contado').value = modo;
  const btnSimple = document.getElementById('btnModoSimple');
  const btnMult = document.getElementById('btnModoMultiple');
  const boxSimple = document.getElementById('boxPagoSimple');
  const boxMult = document.getElementById('boxPagoMultiple');

  if (modo === 'MULTIPLE') {
    btnSimple.className = 'btn btn-outline-primary py-0 px-2 fw-semibold';
    btnMult.className = 'btn btn-primary py-0 px-2 fw-semibold';
    boxSimple.style.display = 'none';
    boxMult.style.display = 'block';

    // Si los 3 campos están en 0 y hay total > 0, pre-cargar mitad o efectivo inicial
    const tot = obtenerTotalActualVenta();
    const ef = parseInt(document.getElementById('pago_mult_efectivo').value) || 0;
    const tr = parseInt(document.getElementById('pago_mult_transferencia').value) || 0;
    const qr = parseInt(document.getElementById('pago_mult_qr').value) || 0;
    if (tot > 0 && (ef + tr + qr) === 0) {
      document.getElementById('pago_mult_efectivo').value = tot;
    }
    actualizarSumaMultiple();
  } else {
    btnSimple.className = 'btn btn-primary py-0 px-2 fw-semibold';
    btnMult.className = 'btn btn-outline-primary py-0 px-2 fw-semibold';
    boxSimple.style.display = 'block';
    boxMult.style.display = 'none';
  }
}

function seleccionarMetodoSimple(metodo) {
  document.getElementById('metodo_pago').value = metodo;
  ['EFECTIVO', 'TRANSFERENCIA', 'QR'].forEach(m => {
    const btn = document.getElementById('btnSimple_' + m);
    if (!btn) return;
    if (m === metodo) {
      btn.className = 'btn btn-sm btn-success w-100 py-2 fw-bold btn-metodo-simple shadow-sm';
    } else {
      btn.className = 'btn btn-sm btn-outline-secondary w-100 py-2 fw-bold btn-metodo-simple';
    }
  });
}

function asignarRestoMultiple(campo) {
  const total = obtenerTotalActualVenta();
  const ef = parseInt(document.getElementById('pago_mult_efectivo').value) || 0;
  const tr = parseInt(document.getElementById('pago_mult_transferencia').value) || 0;
  const qr = parseInt(document.getElementById('pago_mult_qr').value) || 0;

  if (campo === 'efectivo') {
    document.getElementById('pago_mult_efectivo').value = Math.max(0, total - tr - qr);
  } else if (campo === 'transferencia') {
    document.getElementById('pago_mult_transferencia').value = Math.max(0, total - ef - qr);
  } else if (campo === 'qr') {
    document.getElementById('pago_mult_qr').value = Math.max(0, total - ef - tr);
  }
  actualizarSumaMultiple();
}

function actualizarSumaMultiple() {
  const total = obtenerTotalActualVenta();
  const ef = Math.max(0, parseInt(document.getElementById('pago_mult_efectivo').value) || 0);
  const tr = Math.max(0, parseInt(document.getElementById('pago_mult_transferencia').value) || 0);
  const qr = Math.max(0, parseInt(document.getElementById('pago_mult_qr').value) || 0);
  const suma = ef + tr + qr;
  const diff = total - suma;

  const lblSuma = document.getElementById('lblSumaMultiple');
  const badge = document.getElementById('badgeEstadoMultiple');
  if (lblSuma) lblSuma.innerText = formatGs(suma);
  if (!badge) return;

  if (total === 0) {
    badge.className = 'badge bg-secondary-subtle text-secondary';
    badge.innerText = 'Agregue productos';
  } else if (diff === 0) {
    badge.className = 'badge bg-success-subtle text-success border border-success-subtle';
    badge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Total Cubierto';
  } else if (diff > 0) {
    badge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle';
    badge.innerText = `Falta: ${formatGs(diff)}`;
  } else {
    badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
    badge.innerText = `Excede: +${formatGs(Math.abs(diff))}`;
  }
}

async function verificarCreditoCliente(idCliente) {
  const badge = document.getElementById('badgeEstadoCreditoCliente');
  const alerta = document.getElementById('alertaClienteCredito');
  if (!badge) return;

  if (!idCliente) {
    badge.className = 'badge bg-secondary-subtle text-secondary small';
    badge.innerText = 'Sin cliente seleccionado';
    if (alerta) alerta.classList.remove('d-none');
    return;
  }

  try {
    const res = await fetch(`${URL_VENTAS}?accion=estado_credito_cliente&id_cliente=${idCliente}`);
    const json = await res.json();

    if (json.status === 'success') {
      const d = json.data;
      if (d.es_consumidor_final) {
        badge.className = 'badge bg-danger-subtle text-danger small';
        badge.innerHTML = '<i class="bi bi-x-circle me-1"></i> Consumidor Final (No apto para crédito)';
        if (alerta) alerta.classList.remove('d-none');
      } else if (d.tiene_vencidas) {
        badge.className = 'badge bg-danger text-white small';
        badge.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> ⚠️ ${d.cant_vencidas} cuota(s) vencida(s)`;
        if (alerta) alerta.classList.add('d-none');
      } else if (d.tiene_deuda) {
        badge.className = 'badge bg-warning-subtle text-dark border border-warning small';
        badge.innerHTML = `<i class="bi bi-info-circle me-1"></i> Deuda activa: ${formatGs(d.total_saldo_pendiente)}`;
        if (alerta) alerta.classList.add('d-none');
      } else {
        badge.className = 'badge bg-success-subtle text-success small';
        badge.innerHTML = '<i class="bi bi-check-circle me-1"></i> Línea de crédito al día';
        if (alerta) alerta.classList.add('d-none');
      }
    }
  } catch (err) {
    console.error('Error verificando crédito de cliente:', err);
  }
}

function calcularPlanCuotas() {
  const totalSub = carrito.reduce((sum, item) => sum + (item.cantidad * item.precio_unitario), 0);
  const esDelivery = (document.getElementById('metodo_entrega').value === 'DELIVERY');
  const flete = esDelivery ? (parseInt(document.getElementById('monto_delivery').value) || 0) : 0;
  const total = totalSub + flete;

  const inputEntrega = document.getElementById('credito_entrega_inicial');
  let entrega = parseInt(inputEntrega.value) || 0;
  if (entrega < 0) entrega = 0;
  if (entrega >= total && total > 0) {
    entrega = Math.max(0, total - 1000);
    inputEntrega.value = entrega;
  }

  const boxMetodo = document.getElementById('boxMetodoEntregaInicial');
  if (boxMetodo) {
    boxMetodo.style.display = (entrega > 0) ? 'block' : 'none';
  }

  const saldo = Math.max(0, total - entrega);
  const cantCuotas = Math.max(1, parseInt(document.getElementById('credito_cuotas').value) || 1);
  const plazoDias = Math.max(1, parseInt(document.getElementById('credito_plazo_dias').value) || 30);
  const montoPorCuota = Math.round(saldo / cantCuotas);

  const lblSaldo = document.getElementById('lblSaldoAFinanciar');
  const lblCuota = document.getElementById('lblMontoPorCuota');
  const lblDetalle = document.getElementById('lblDetalleFechasCuotas');
  const lblResSaldo = document.getElementById('lblResumenCreditoSaldo');
  const lblResPlan = document.getElementById('lblResumenCreditoPlan');

  const textoPlan = (cantCuotas > 1) ? `${cantCuotas} cuotas de ${formatGs(montoPorCuota)}` : `1 Pago de ${formatGs(saldo)}`;

  if (lblSaldo) lblSaldo.innerText = formatGs(saldo);
  if (lblCuota) lblCuota.innerText = textoPlan;
  if (lblResSaldo) lblResSaldo.innerText = formatGs(saldo);
  if (lblResPlan) lblResPlan.innerText = textoPlan;

  if (lblDetalle) {
    let fechaBaseStr = document.getElementById('credito_primer_venc').value;
    let fechaBase = fechaBaseStr ? new Date(fechaBaseStr + 'T00:00:00') : new Date();
    if (!fechaBaseStr) {
      fechaBase.setDate(fechaBase.getDate() + plazoDias);
    }

    let cuotasHtml = '';
    if (entrega > 0) {
      cuotasHtml += `<div class="d-flex justify-content-between text-success"><span>Entrega Inicial:</span><strong>${formatGs(entrega)} (Hoy)</strong></div>`;
    }

    for (let i = 1; i <= cantCuotas; i++) {
      let fv = new Date(fechaBase);
      fv.setDate(fechaBase.getDate() + ((i - 1) * plazoDias));
      let fvStr = fv.toLocaleDateString('es-PY');
      cuotasHtml += `<div class="d-flex justify-content-between text-muted"><span>Cuota ${i}/${cantCuotas}:</span><span>${formatGs(montoPorCuota)} (Vence: ${fvStr})</span></div>`;
    }
    lblDetalle.innerHTML = cuotasHtml;
  }
}

function toggleDelivery() {
  const ent = document.getElementById('metodo_entrega').value;
  const box = document.getElementById('boxMontoDelivery');
  if (ent === 'DELIVERY') {
    box.style.display = 'block';
  } else {
    box.style.display = 'none';
  }
  recalcularTotales();
}

async function procesarVenta() {
  if (carrito.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Carrito vacío',
      text: 'Debe agregar al menos un producto para generar la factura.'
    });
    return;
  }

  const condicion = document.getElementById('condicion_pago').value;
  const tipoPagoContado = document.getElementById('tipo_pago_contado').value || 'SIMPLE';
  const idCli = document.getElementById('id_cliente').value;
  const totalActual = obtenerTotalActualVenta();

  // Validación estricta para CRÉDITO
  if (condicion === 'CREDITO') {
    const docCli = document.getElementById('lblClienteDoc').innerText;
    if (!idCli || idCli == '1' || docCli.includes('000000')) {
      Swal.fire({
        icon: 'warning',
        title: 'Cliente no identificado',
        text: 'Para emitir una factura a CRÉDITO debe seleccionar un cliente registrado con Cédula de Identidad o RUC.'
      });
      return;
    }
  }

  // Validación de Pago Múltiple en CONTADO
  const multEf = Math.max(0, parseInt(document.getElementById('pago_mult_efectivo').value) || 0);
  const multTr = Math.max(0, parseInt(document.getElementById('pago_mult_transferencia').value) || 0);
  const multQr = Math.max(0, parseInt(document.getElementById('pago_mult_qr').value) || 0);

  if (condicion === 'CONTADO' && tipoPagoContado === 'MULTIPLE') {
    const sumaMult = multEf + multTr + multQr;
    if (sumaMult !== totalActual) {
      Swal.fire({
        icon: 'warning',
        title: 'Verifique los montos de Pago Múltiple',
        html: `La suma combinada de Efectivo + Transferencia + QR (<strong>${formatGs(sumaMult)}</strong>) debe coincidir exactamente con el Total a Pagar (<strong>${formatGs(totalActual)}</strong>).`
      });
      return;
    }
  }

  const btn = document.getElementById('btnConfirmarVenta');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando venta...';

  const payload = {
    id_cliente: idCli,
    nro_factura: document.getElementById('nro_factura').value,
    condicion_pago: condicion,
    tipo_pago_contado: tipoPagoContado,
    metodo_pago: (condicion === 'CREDITO') ? 'CREDITO' : (tipoPagoContado === 'MULTIPLE' ? 'MULTIPLE' : document.getElementById('metodo_pago').value),
    pagos_multiple: {
      efectivo: multEf,
      transferencia: multTr,
      qr: multQr
    },
    metodo_entrega: document.getElementById('metodo_entrega').value,
    monto_delivery: (document.getElementById('metodo_entrega').value === 'DELIVERY') ? (parseInt(document.getElementById('monto_delivery').value) || 0) : 0,
    id_sucursal: ID_SUCURSAL,
    // Parámetros de crédito mayorista
    cantidad_cuotas: (condicion === 'CREDITO') ? (parseInt(document.getElementById('credito_cuotas').value) || 1) : 1,
    plazo_dias: (condicion === 'CREDITO') ? (parseInt(document.getElementById('credito_plazo_dias').value) || 30) : 30,
    fecha_primer_vencimiento: (condicion === 'CREDITO') ? document.getElementById('credito_primer_venc').value : '',
    entrega_inicial: (condicion === 'CREDITO') ? (parseInt(document.getElementById('credito_entrega_inicial').value) || 0) : 0,
    metodo_entrega_inicial: (condicion === 'CREDITO') ? document.getElementById('credito_metodo_entrega').value : 'EFECTIVO',
    items: carrito.map(it => ({
      id_producto: it.id_producto,
      descripcion: it.nombre_prod,
      cantidad: it.cantidad,
      precio_unitario: it.precio_unitario,
      id_tipo_impuesto: it.id_tipo_impuesto
    }))
  };

  try {
    const res = await fetch(`${URL_VENTAS}?accion=registrar_venta`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();

    if (json.status === 'success') {
      // Guardar info para impresión y pagaré
      ultimoDetalleVenta = {
        id_venta: json.id_venta,
        nro_factura: json.nro_factura,
        total_venta: json.total_venta,
        cliente: document.getElementById('cliente_nombre_completo').value || document.getElementById('lblClienteNombre').innerText,
        cedula_cliente: document.getElementById('lblClienteDoc').innerText.replace('CI/RUC:', '').trim(),
        condicion: payload.condicion_pago,
        metodo_pago: json.metodo_pago || payload.metodo_pago,
        pagos_detalle: json.pagos_detalle || [],
        fecha: new Date().toLocaleDateString('es-PY'),
        items: [...carrito],
        credito: json.credito
      };
      ultimoTicketVentaData = ultimoDetalleVenta;

      mostrarTicketModal(ultimoDetalleVenta);
      cargarSiguienteFactura();
      cargarFacturasRecientesPOS();
    } else {
      Swal.fire('Atención', json.message, 'error');
    }
  } catch (err) {
    console.error(err);
    Swal.fire('Error', 'Ocurrió un error al registrar la factura en la base de datos.', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-check2-circle fs-4"></i> FINALIZAR FACTURACIÓN';
  }
}

function mostrarTicketModal(v) {
  const contenedor = document.getElementById('contenidoImpresionTicket');
  let lineasHtml = '';
  v.items.forEach(it => {
    const sub = it.cantidad * it.precio_unitario;
    const tieneDesc = it.precio_unitario < it.precio_base;
    const descMonto = tieneDesc ? (it.precio_base - it.precio_unitario) * it.cantidad : 0;
    lineasHtml += `
      <div class="d-flex justify-content-between mb-1">
        <div>
          <span>${it.cantidad}x ${escapeHtml(it.nombre_prod)}</span>
          ${tieneDesc ? `<small class="d-block text-muted">Desc. ${it.porcentaje_desc || ''}%: -${formatGs(descMonto)}</small>` : ''}
        </div>
        <span class="text-end">${formatGs(sub)}</span>
      </div>
    `;
  });

  let seccionCreditoHtml = '';
  if (v.condicion === 'CREDITO' && v.credito) {
    let cuotasLineas = '';
    v.credito.cuotas.forEach(q => {
      cuotasLineas += `
        <div class="d-flex justify-content-between small">
          <span>Cuota ${q.numero} (${q.vencimiento}):</span>
          <strong>${q.monto_formateado}</strong>
        </div>
      `;
    });

    seccionCreditoHtml = `
      <div class="my-2 py-2 border-top border-bottom border-dark border-dashed">
        <div class="fw-bold text-center small mb-1">PLAN DE PAGOS A CRÉDITO</div>
        ${v.credito.entrega_inicial > 0 ? `
          <div class="d-flex justify-content-between small text-success">
            <span>Entrega Inicial Abonada:</span>
            <strong>${formatGs(v.credito.entrega_inicial)}</strong>
          </div>
        ` : ''}
        <div class="d-flex justify-content-between small">
          <span>Saldo Financiado:</span>
          <strong>${formatGs(v.credito.saldo_a_financiar)}</strong>
        </div>
        <div class="d-flex justify-content-between small mb-1">
          <span>Cantidad de Cuotas:</span>
          <span>${v.credito.cantidad_cuotas} cuota(s)</span>
        </div>
        <div class="pt-1 border-top border-secondary">
          ${cuotasLineas}
        </div>

        <!-- PAGARÉ A LA ORDEN / COMPROMISO DE PAGO -->
        <div class="mt-3 pt-2 border-top border-dark text-start" style="font-size: 0.68rem; line-height: 1.3;">
          <div class="fw-bold text-center mb-1">PAGARÉ A LA ORDEN / COMPROMISO DE PAGO</div>
          <p class="mb-2">Por este pagaré me comprometo a pagar incondicionalmente a SISTEMA MAYORISTA la suma de <strong>${formatGs(v.credito.saldo_a_financiar)}</strong> en las fechas y cuotas pactadas. La falta de pago devengará los intereses de rigor sin necesidad de interpelación alguna.</p>
          <div class="mt-4 pt-3 text-center border-top border-dark">
            <div>Firma del Deudor</div>
            <div class="fw-bold">${escapeHtml(v.cliente)}</div>
            <div>${escapeHtml(v.cedula_cliente || '')}</div>
          </div>
        </div>
      </div>
    `;
  }

  let seccionPagosContadoHtml = '';
  if (v.condicion === 'CONTADO' && Array.isArray(v.pagos_detalle) && v.pagos_detalle.length > 0) {
    let itemsPago = '';
    v.pagos_detalle.forEach(p => {
      itemsPago += `
        <div class="d-flex justify-content-between small">
          <span>${escapeHtml(p.metodo)}:</span>
          <strong>${formatGs(p.monto)}</strong>
        </div>`;
    });
    seccionPagosContadoHtml = `
      <div class="my-2 py-1 border-top border-dark border-dashed">
        <div class="fw-bold small mb-1">FORMA DE PAGO (${v.pagos_detalle.length > 1 ? 'MÚLTIPLE' : 'SIMPLE'}):</div>
        ${itemsPago}
      </div>
    `;
  }

  contenedor.innerHTML = `
    <div class="text-center pb-2 mb-2 border-bottom border-dark border-dashed">
      <h6 class="fw-bold mb-0">SISTEMA MAYORISTA</h6>
      <small class="d-block">RUC: 80012345-6</small>
      <small class="d-block">Sucursal #${ID_SUCURSAL}</small>
    </div>
    <div class="mb-2 pb-2 border-bottom border-dark border-dashed small">
      <div><strong>FACTURA:</strong> ${escapeHtml(v.nro_factura)}</div>
      <div><strong>FECHA:</strong> ${v.fecha}</div>
      <div><strong>CLIENTE:</strong> ${escapeHtml(v.cliente)}</div>
      <div><strong>CONDICIÓN:</strong> <span class="badge ${v.condicion === 'CREDITO' ? 'bg-warning text-dark' : 'bg-light text-dark'}">${v.condicion} - ${escapeHtml(v.metodo_pago || 'EFECTIVO')}</span></div>
    </div>
    <div class="mb-2 pb-2 border-bottom border-dark border-dashed">
      ${lineasHtml}
    </div>
    <div class="d-flex justify-content-between fw-bold fs-6 pt-1">
      <span>TOTAL FACTURA:</span>
      <span>${formatGs(v.total_venta)}</span>
    </div>
    ${seccionPagosContadoHtml}
    ${seccionCreditoHtml}
    <div class="text-center mt-3 pt-2 text-muted small">
      ¡Gracias por su compra mayorista!
    </div>
  `;

  modalTicket.show();
}

let ultimoTicketVentaData = null;

function imprimirTicket(formato = 'ticket') {
  if (ultimoTicketVentaData && window.ImpresorMayorista) {
    ImpresorMayorista.verVenta(ultimoTicketVentaData, formato);
    return;
  }
  const contenido = document.getElementById('contenidoImpresionTicket').innerHTML;
  const ventana = window.open('', '', 'width=380,height=600');
  ventana.document.write(`
    <html>
      <head>
        <title>Ticket de Factura</title>
        <style>
          body { font-family: monospace; font-size: 12px; margin: 15px; }
          .border-dashed { border-bottom: 1px dashed #000; }
          .d-flex { display: flex; justify-content: space-between; }
          .text-center { text-align: center; }
          .fw-bold { font-weight: bold; }
        </style>
      </head>
      <body onload="window.print();">
        ${contenido}
      </body>
    </html>
  `);
  ventana.document.close();
}

function abrirModalNuevoCliente() {
  document.getElementById('formClienteRapido').reset();
  modalRapidoCli.show();
}

async function cargarFacturasRecientesPOS() {
  const tbody = document.getElementById('tbodyFacturasRecientesPOS');
  const contador = document.getElementById('contadorRecientesPOS');
  if (!tbody) return;

  try {
    const res = await fetch(`${URL_VENTAS}?accion=listar&sucursal=${ID_SUCURSAL}`);
    const json = await res.json();
    if (json.status === 'success') {
      const lista = (json.data || []).slice(0, 10);
      if (contador) contador.innerText = `Últimas ${lista.length} de Sucursal #${ID_SUCURSAL}`;

      if (lista.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-3 text-muted">No hay facturas procesadas aún en esta sucursal.</td></tr>`;
        return;
      }

      let html = '';
      lista.forEach(v => {
        const estUpper = (v.estado || '').toUpperCase();
        const esAnulada = (estUpper === 'ANULADO' || estUpper === 'ANULADA');
        const estadoBadge = !esAnulada
          ? `<span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i>${escapeHtml(estUpper || 'CONFIRMADO')}</span>`
          : `<span class="badge bg-danger-subtle text-danger"><i class="bi bi-x-circle me-1"></i>ANULADO</span>`;

        const condBadge = v.condicion_pago === 'CONTADO'
          ? '<span class="badge bg-light text-dark border">CONTADO</span>'
          : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">CRÉDITO</span>';

        html += `
          <tr>
            <td class="text-center fw-bold text-muted">#${v.id_venta}</td>
            <td><span class="font-monospace fw-bold text-dark">${escapeHtml(v.nro_factura || '#' + v.id_venta)}</span></td>
            <td><small class="text-muted">${escapeHtml(v.fecha_venta)}</small></td>
            <td>
              <strong class="text-dark">${escapeHtml((v.nombre_cliente || 'CLIENTE') + ' ' + (v.apellido_cliente || ''))}</strong>
              <small class="text-muted ms-1">(${escapeHtml(v.cedula_cliente || '-')})</small>
            </td>
            <td class="text-center">${condBadge}</td>
            <td class="text-center"><span class="badge bg-light text-secondary border">${escapeHtml(v.metodo_pago)}</span></td>
            <td class="text-end fw-bold text-dark">${formatGs(v.total_venta)}</td>
            <td class="text-center">${estadoBadge}</td>
            <td class="text-end pe-3">
              <div class="d-inline-flex gap-1">
                <button type="button" class="btn btn-sm btn-primary px-2 py-1" title="Imprimir Factura" onclick="imprimirFacturaPOS(${v.id_venta})">
                  <i class="bi bi-printer-fill"></i> Imprimir
                </button>
                ${!esAnulada ? `
                  <button type="button" class="btn btn-sm btn-danger px-2 py-1" title="Anular Factura" onclick="anularFacturaPOS(${v.id_venta}, '${escapeHtml(v.nro_factura || '#' + v.id_venta)}')">
                    <i class="bi bi-x-circle-fill"></i> Anular
                  </button>
                ` : ''}
              </div>
            </td>
          </tr>
        `;
      });
      tbody.innerHTML = html;
    }
  } catch (err) {
    console.error(err);
  }
}

async function imprimirFacturaPOS(idVenta, formato = 'ticket') {
  try {
    const res = await fetch(`${URL_VENTAS}?accion=obtener_detalle&id_venta=${idVenta}`);
    const json = await res.json();
    if (json.status !== 'success') {
      Swal.fire('Error', json.message || 'No se pudo obtener la factura.', 'error');
      return;
    }
    if (window.ImpresorMayorista) {
      ImpresorMayorista.verVenta(json, formato);
      return;
    }
    const cab = json.cabecera;
    const det = json.detalles;
    let itemsHtml = '';
    det.forEach(it => {
      itemsHtml += `
        <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
          <span>${it.cantidad}x ${it.descripcion || it.nombre_prod}</span>
          <span>${formatGs(it.cantidad * it.precio_unitario)}</span>
        </div>`;
    });

    const ventana = window.open('', '', 'width=400,height=600');
    ventana.document.write(`
      <html>
        <head>
          <title>Factura ${cab.nro_factura || cab.id_venta}</title>
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
            <div>${cab.sucursal_nombre || 'Sucursal #' + ID_SUCURSAL}</div>
            <div>Factura: ${cab.nro_factura || ('#' + cab.id_venta)}</div>
            <div>Fecha: ${cab.fecha_venta}</div>
            <div>Cliente: ${cab.nombre_cliente || 'CLIENTE'} (${cab.cedula_cliente || '-'})</div>
            <div>Condición: ${cab.condicion_pago} - ${cab.metodo_pago}</div>
          </div>
          <div>${itemsHtml}</div>
          <div class="total">
            <span>TOTAL:</span>
            <span>${formatGs(cab.total_venta)}</span>
          </div>
          <div class="footer">Comprobante válido para operaciones comerciales.</div>
        </body>
      </html>
    `);
    ventana.document.close();
  } catch (err) {
    console.error(err);
    Swal.fire('Error', 'Error al abrir factura para imprimir.', 'error');
  }
}

async function anularFacturaPOS(idVenta, nroFactura) {
  const result = await Swal.fire({
    title: `¿Anular Factura ${nroFactura}?`,
    text: 'Esta acción anulará la factura y repondrá automáticamente el stock de los productos en el inventario.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    confirmButtonText: 'Sí, anular factura',
    cancelButtonText: 'Cancelar'
  });

  if (!result.isConfirmed) return;

  const formData = new FormData();
  formData.append('accion', 'anular_venta');
  formData.append('id_venta', idVenta);

  try {
    const res = await fetch(URL_VENTAS, { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
      Swal.fire({ icon: 'success', title: 'Factura Anulada', text: json.message, timer: 1600, showConfirmButton: false });
      cargarFacturasRecientesPOS();
    } else {
      Swal.fire('Error', json.message, 'error');
    }
  } catch (err) {
    Swal.fire('Error', 'No se pudo anular la factura.', 'error');
  }
}

function anularUltimoTicket() {
  if (!ultimoDetalleVenta || !ultimoDetalleVenta.id_venta) return;
  modalTicket.hide();
  limpiarCarrito();
  anularFacturaPOS(ultimoDetalleVenta.id_venta, ultimoDetalleVenta.nro_factura || ('#' + ultimoDetalleVenta.id_venta));
}

async function guardarClienteRapido(e) {
  e.preventDefault();
  const tipoCli = document.getElementById('rapido_tipo_cliente').value || 'MAYORISTA';
  const formData = new FormData();
  formData.append('accion', 'guardar');
  formData.append('tipo_cliente', tipoCli);
  formData.append('nombre_cliente', document.getElementById('rapido_nombre').value);
  formData.append('cedula_cliente', document.getElementById('rapido_cedula').value);
  formData.append('telefono_cliente', document.getElementById('rapido_telefono').value);
  formData.append('id_ciudad', 1);

  try {
    const res = await fetch(URL_CLIENTES, { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
      modalRapidoCli.hide();
      seleccionarCliente({
        id_cliente: json.id_cliente || 999,
        nombre_cliente: document.getElementById('rapido_nombre').value,
        apellido_cliente: '',
        cedula_cliente: document.getElementById('rapido_cedula').value,
        tipo_cliente: tipoCli,
        telefono_cliente: document.getElementById('rapido_telefono').value
      });
      document.getElementById('formClienteRapido').reset();
    } else {
      Swal.fire('Atención', json.message || 'No se pudo registrar el cliente.', 'warning');
    }
  } catch (err) {
    console.error(err);
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
