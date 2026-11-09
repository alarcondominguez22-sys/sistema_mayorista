<?php
$tabInicial = $_GET['tab'] ?? 'nueva';
$page_title = ($tabInicial === 'ganancias') ? 'Informe de Ganancias y Rentabilidad' : 'Factura de Compra e Ingreso de Stock';
$active_menu = ($tabInicial === 'ganancias') ? 'informe_ganancias' : 'compras';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/navbar.php';

$pdo = getDB();
$sucursalActual = (int)($_SESSION['id_sucursal'] ?? 1);

// Proveedores activos
$stmtProv = $pdo->query("SELECT id_proveedor_, razon_social, nombre_apellido_prov, ruc_proveedor, telefono_proveedor 
                         FROM proveedores 
                         WHERE estado = 'ACTIVO' 
                         ORDER BY razon_social ASC, nombre_apellido_prov ASC");
$proveedores = $stmtProv->fetchAll(PDO::FETCH_ASSOC);

// Sucursales
$stmtSuc = $pdo->query("SELECT id_sucursal, nombre_sucursakl FROM sucursal WHERE estado = 'ACTIVO' ORDER BY id_sucursal ASC");
$sucursales = $stmtSuc->fetchAll(PDO::FETCH_ASSOC);

// Ciudades para modal rápido de proveedor
$stmtCiu = $pdo->query("SELECT id_ciudad, nombre_ciudad FROM ciudades WHERE estado = 'ACTIVO' ORDER BY nombre_ciudad ASC");
$ciudades = $stmtCiu->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="dashboard-content">
  <div class="container-fluid px-3 py-3">

    <!-- ENCABEZADO Y NAVEGACIÓN DE PESTAÑAS -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
      <div>
        <h4 class="fw-bold mb-0">
          <i class="bi bi-bag-plus-fill text-primary me-2"></i>Compras de Mercadería, Stock e Informe de Ganancias
        </h4>
        <small class="text-muted">Cargue facturas de compra para dar stock automático a sus productos, actualice costos/precios y consulte su ganancia real.</small>
      </div>
      <ul class="nav nav-pills bg-white p-1 rounded-3 shadow-sm border" id="tabsCompras" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link <?= $tabInicial === 'nueva' ? 'active' : '' ?> fw-semibold btn-sm px-3 py-2" id="tab-nueva-btn" data-bs-toggle="pill" data-bs-target="#paneNuevaCompra" type="button" role="tab">
            <i class="bi bi-cart-plus me-1"></i> 1. Factura Compra (Dar Stock)
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link <?= $tabInicial === 'historial' ? 'active' : '' ?> fw-semibold btn-sm px-3 py-2" id="tab-historial-btn" data-bs-toggle="pill" data-bs-target="#paneHistorialCompras" type="button" role="tab" onclick="cargarHistorialCompras()">
            <i class="bi bi-journal-text me-1"></i> 2. Historial de Compras
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link <?= $tabInicial === 'ganancias' ? 'active' : '' ?> fw-semibold btn-sm px-3 py-2" id="tab-ganancias-btn" data-bs-toggle="pill" data-bs-target="#paneInformeGanancias" type="button" role="tab" onclick="cargarInformeGanancias()">
            <i class="bi bi-graph-up-arrow me-1"></i> 3. Informe de Ganancias
          </button>
        </li>
      </ul>
    </div>

    <div class="tab-content" id="tabsComprasContent">

      <!-- ======================================================================= -->
      <!-- PESTAÑA 1: NUEVA FACTURA DE COMPRA E INGRESO DE STOCK                   -->
      <!-- ======================================================================= -->
      <div class="tab-pane fade <?= $tabInicial === 'nueva' ? 'show active' : '' ?>" id="paneNuevaCompra" role="tabpanel">
        <div class="row g-3">
          
          <!-- COLUMNA IZQUIERDA: DATOS DE LA FACTURA DE COMPRA Y CARGA DE PRODUCTOS -->
          <div class="col-12 col-xl-8">
            
            <!-- 1. Datos del Proveedor y Comprobante -->
            <div class="card border-0 shadow-sm rounded-3 mb-3">
              <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small text-uppercase">
                  <i class="bi bi-file-earmark-text text-primary me-1"></i> Datos de la Factura de Compra
                </span>
                <button type="button" class="btn btn-outline-primary btn-icon" data-bs-toggle="modal" data-bs-target="#modalRapidoProveedor" title="Registrar Nuevo Proveedor">
                  <i class="bi bi-person-plus-fill"></i>
                </button>
              </div>
              <div class="card-body p-3">
                <div class="row g-2">
                  <!-- Proveedor -->
                  <div class="col-12 col-md-5">
                    <label class="form-label small fw-semibold mb-1">Proveedor / Distribuidor <span class="text-danger">*</span></label>
                    <select id="compraProveedor" class="form-select form-select-sm" required>
                      <option value="">-- Seleccione Proveedor --</option>
                      <?php foreach ($proveedores as $pr): 
                        $nomProv = !empty($pr['razon_social']) ? $pr['razon_social'] : $pr['nombre_apellido_prov'];
                      ?>
                        <option value="<?= $pr['id_proveedor_'] ?>" data-ruc="<?= htmlspecialchars($pr['ruc_proveedor'] ?? '') ?>">
                          <?= htmlspecialchars($nomProv) ?> (RUC: <?= htmlspecialchars($pr['ruc_proveedor'] ?? 'S/R') ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- Nro. Factura Compra -->
                  <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Nro. Factura Compra <span class="text-danger">*</span></label>
                    <input type="text" id="compraNroFactura" class="form-control form-control-sm fw-bold" placeholder="001-001-0012345" value="001-001-00<?= rand(10000, 99999) ?>">
                  </div>

                  <!-- Nro. Timbrado -->
                  <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Nro. Timbrado <span class="text-danger">*</span></label>
                    <input type="number" id="compraTimbrado" class="form-control form-control-sm" placeholder="16543210" value="16543210">
                  </div>

                  <!-- Vto. Timbrado -->
                  <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Vto. Timbrado</label>
                    <input type="date" id="compraVtoTimbrado" class="form-control form-control-sm" value="<?= date('Y-12-31') ?>">
                  </div>

                  <!-- Fecha de Compra -->
                  <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Fecha de Emisión</label>
                    <input type="date" id="compraFecha" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                  </div>

                  <!-- Sucursal Destino del Stock -->
                  <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1 text-primary">Sucursal que Recibe Stock</label>
                    <select id="compraSucursal" class="form-select form-select-sm fw-semibold border-primary" onchange="actualizarEtiquetaSucursal()">
                      <?php foreach ($sucursales as $s): ?>
                        <option value="<?= $s['id_sucursal'] ?>" <?= ((int)$s['id_sucursal'] === $sucursalActual) ? 'selected' : '' ?>>
                          Sucursal <?= $s['id_sucursal'] ?> - <?= htmlspecialchars($s['nombre_sucursakl']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- Condición de Compra -->
                  <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Condición</label>
                    <select id="compraCondicion" class="form-select form-select-sm" onchange="toggleCondicionCompra()">
                      <option value="1">CONTADO</option>
                      <option value="2">CRÉDITO</option>
                    </select>
                  </div>

                  <!-- Medio de Pago -->
                  <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Medio de Pago</label>
                    <select id="compraMetodoPago" class="form-select form-select-sm">
                      <option value="EFECTIVO">EFECTIVO</option>
                      <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                      <option value="QR">PAGO QR</option>
                      <option value="CHEQUE">CHEQUE</option>
                    </select>
                  </div>

                  <!-- Cuotas / Vto (para Crédito) -->
                  <div class="col-6 col-md-2 d-none" id="boxCreditoCompra">
                    <label class="form-label small fw-semibold mb-1">Cant. Cuotas</label>
                    <input type="number" id="compraCuotas" class="form-control form-control-sm" value="1" min="1" max="36">
                  </div>
                </div>
              </div>
            </div>

            <!-- 2. Buscador y Selector de Producto para Ingresar Stock -->
            <div class="card border-0 shadow-sm rounded-3 mb-3 border-start border-4 border-success">
              <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small text-uppercase">
                  <i class="bi bi-box-arrow-in-down text-success me-1"></i> Seleccionar Producto para Dar Stock y Fijar Costo/Precio
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle" id="badgeSucursalDestino">
                  Suma Stock a Sucursal <?= $sucursalActual ?>
                </span>
              </div>
              <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                  <!-- Buscador de Producto -->
                  <div class="col-12 col-md-5 position-relative">
                    <label class="form-label small fw-semibold mb-1">Buscar Producto (Nombre o Código)</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text bg-light"><i class="bi bi-upc-scan text-primary"></i></span>
                      <input type="text" id="inputBuscarProdCompra" class="form-control" placeholder="Escriba nombre o código de barra..." autocomplete="off">
                    </div>
                    <div id="resultadosProdCompra" class="list-group position-absolute w-100 shadow-lg d-none" style="z-index: 1055; max-height: 260px; overflow-y: auto;"></div>
                  </div>

                  <!-- Stock Actual (Informativo) -->
                  <div class="col-4 col-md-1">
                    <label class="form-label small fw-semibold mb-1 text-muted">Stock Act.</label>
                    <input type="text" id="prodCompraStockActual" class="form-control form-control-sm bg-light text-center fw-bold" value="0" readonly>
                  </div>

                  <!-- Cantidad a Ingresar -->
                  <div class="col-4 col-md-1">
                    <label class="form-label small fw-semibold mb-1 text-success">Cant. +</label>
                    <input type="number" id="prodCompraCantidad" class="form-control form-control-sm text-center fw-bold border-success" value="1" min="1">
                  </div>

                  <!-- Costo Compra Unitario (Estirado automáticamente) -->
                  <div class="col-4 col-md-2">
                    <label class="form-label small fw-semibold mb-1 text-danger">Costo Compra Gs.</label>
                    <input type="number" id="prodCompraCosto" class="form-control form-control-sm fw-bold text-end border-danger" value="0" min="0" step="500" oninput="calcularMargenPreview()">
                  </div>

                  <!-- Precio Venta (Estirado automáticamente, editable) -->
                  <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1 text-primary">Precio Venta Gs.</label>
                    <input type="number" id="prodCompraPrecioVenta" class="form-control form-control-sm fw-bold text-end border-primary" value="0" min="0" step="500" oninput="calcularMargenPreview()">
                  </div>

                  <!-- Botón Agregar al Detalle -->
                  <div class="col-6 col-md-1 d-grid">
                    <button type="button" class="btn btn-success btn-sm fw-bold" onclick="agregarItemCompra()" title="Agregar a la Factura">
                      <i class="bi bi-plus-lg"></i>
                    </button>
                  </div>
                </div>

                <!-- Barra de Previsualización del Producto Seleccionado y Margen -->
                <div id="infoProductoSeleccionadoCompra" class="mt-2 p-2 rounded-2 bg-light border d-none">
                  <div class="d-flex flex-wrap justify-content-between align-items-center small gap-2">
                    <div>
                      <span class="badge bg-dark me-1" id="lblProdCompraCodigo">PRD000</span>
                      <strong id="lblProdCompraNombre" class="text-dark">Seleccione un producto</strong>
                      <span class="text-muted ms-2" id="lblProdCompraMarca"></span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                      <span>IVA: 
                        <select id="prodCompraIva" class="form-select form-select-sm d-inline-block w-auto py-0 px-2">
                          <option value="10" selected>10%</option>
                          <option value="5">5%</option>
                          <option value="0">Exenta</option>
                        </select>
                      </span>
                      <span>Ganancia Unit.: <strong class="text-success" id="lblProdCompraGananciaUnit">0 Gs.</strong></span>
                      <span class="badge bg-success" id="lblProdCompraMargen">Margen: 0%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 3. Tabla de Ítems de la Factura de Compra -->
            <div class="card border-0 shadow-sm rounded-3">
              <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small text-uppercase">
                  <i class="bi bi-list-check text-primary me-1"></i> Detalle de Productos a Ingresar al Stock
                </span>
                <button type="button" class="btn btn-outline-danger btn-icon" onclick="limpiarCarritoCompra()" title="Vaciar Lista">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Código / Producto</th>
                        <th class="text-center">Stock Ant.</th>
                        <th class="text-center">Cant. Compra</th>
                        <th class="text-center">Nuevo Stock</th>
                        <th class="text-end">Costo Compra</th>
                        <th class="text-end">Precio Venta</th>
                        <th class="text-center">Margen</th>
                        <th class="text-end">Subtotal Costo</th>
                        <th class="text-center">Quitar</th>
                      </tr>
                    </thead>
                    <tbody id="tbodyCarritoCompra">
                      <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                          <i class="bi bi-box-seam fs-3 d-block mb-1"></i>
                          Busque o seleccione productos arriba para cargar la factura de compra y sumar stock.
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </div>

          <!-- COLUMNA DERECHA: RESUMEN DE LIQUIDACIÓN E IMPACTO EN GANANCIAS -->
          <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 75px;">
              <div class="card-header bg-primary text-white py-2">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-bold"><i class="bi bi-calculator me-1"></i> Liquidación de Compra</span>
                  <span class="badge bg-white text-primary" id="resumenCantItemsCompra">0 ítems</span>
                </div>
              </div>
              <div class="card-body p-3">
                
                <!-- Desglose Fiscal -->
                <div class="bg-light p-2 rounded-2 border mb-3 small">
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Total Unidades a Ingresar:</span>
                    <strong class="text-dark" id="resumenUnidadesCompra">0 unid.</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Gravadas Exentas:</span>
                    <span id="resumenExentaCompra">0 Gs.</span>
                  </div>
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Liquidación IVA 5%:</span>
                    <span id="resumenIva5Compra">0 Gs.</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="text-muted">Liquidación IVA 10%:</span>
                    <span id="resumenIva10Compra">0 Gs.</span>
                  </div>
                </div>

                <!-- Total Factura de Compra -->
                <div class="p-3 rounded-3 bg-dark text-white mb-3 text-center">
                  <small class="text-uppercase text-white-50 d-block">Total Factura de Compra (Costo)</small>
                  <h2 class="fw-bold text-warning mb-0" id="resumenTotalCompra">0 Gs.</h2>
                </div>

                <!-- Proyección de Venta y Ganancia del Lote -->
                <div class="p-2 rounded-3 bg-success-subtle border border-success-subtle mb-3 small">
                  <div class="fw-bold text-success mb-1">
                    <i class="bi bi-graph-up-arrow me-1"></i> Rentabilidad Proyectada de esta Compra
                  </div>
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark">Valor Total de Venta Estimado:</span>
                    <strong class="text-dark" id="resumenValorVentaLote">0 Gs.</strong>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="text-dark">Ganancia Bruta Estimada del Lote:</span>
                    <strong class="text-success fs-6" id="resumenGananciaLote">+0 Gs.</strong>
                  </div>
                </div>

                <div class="form-check mb-3 small">
                  <input class="form-check-input" type="checkbox" id="chkActualizarPrecios" checked>
                  <label class="form-check-label fw-semibold" for="chkActualizarPrecios">
                    Actualizar automáticamente el Costo de Compra y Precio de Venta en el catálogo
                  </label>
                </div>

                <div class="d-grid gap-2">
                  <button type="button" class="btn btn-primary btn-lg fw-bold shadow-sm" id="btnGuardarCompra" onclick="procesarFacturaCompra()">
                    <i class="bi bi-check2-circle me-1"></i> Procesar Compra y Cargar Stock
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ======================================================================= -->
      <!-- PESTAÑA 2: HISTORIAL DE FACTURAS DE COMPRA                              -->
      <!-- ======================================================================= -->
      <div class="tab-pane fade <?= $tabInicial === 'historial' ? 'show active' : '' ?>" id="paneHistorialCompras" role="tabpanel">
        
        <div class="card border-0 shadow-sm rounded-3 mb-3">
          <div class="card-body p-3">
            <div class="row g-2 align-items-end">
              <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold mb-1">Buscar Factura / Timbrado / Proveedor</label>
                <input type="text" id="histCompraBuscar" class="form-control form-control-sm" placeholder="Nro. factura, RUC o proveedor..." oninput="cargarHistorialCompras()">
              </div>
              <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Desde</label>
                <input type="date" id="histCompraDesde" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>" onchange="cargarHistorialCompras()">
              </div>
              <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Hasta</label>
                <input type="date" id="histCompraHasta" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" onchange="cargarHistorialCompras()">
              </div>
              <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Sucursal</label>
                <select id="histCompraSucursal" class="form-select form-select-sm" onchange="cargarHistorialCompras()">
                  <option value="0">Todas las Sucursales</option>
                  <?php foreach ($sucursales as $s): ?>
                    <option value="<?= $s['id_sucursal'] ?>">Sucursal <?= $s['id_sucursal'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-6 col-md-2 d-grid">
                <button type="button" class="btn btn-primary btn-sm" onclick="cargarHistorialCompras()">
                  <i class="bi bi-funnel me-1"></i> Filtrar Compras
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
          <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small text-uppercase">
              <i class="bi bi-receipt-cutoff text-primary me-1"></i> Facturas de Compra Registradas
            </span>
            <span class="badge bg-primary" id="badgeTotalComprasHist">0 compras</span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Nro. Factura / Timbrado</th>
                    <th>Fecha</th>
                    <th>Proveedor / RUC</th>
                    <th>Sucursal</th>
                    <th>Condición / Pago</th>
                    <th class="text-end">IVA Total</th>
                    <th class="text-end">Total Compra</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end pe-3">Acciones</th>
                  </tr>
                </thead>
                <tbody id="tbodyHistorialCompras">
                  <tr><td colspan="10" class="text-center py-4 text-muted">Cargando historial de compras...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- ======================================================================= -->
      <!-- PESTAÑA 3: INFORME DE GANANCIAS Y RENTABILIDAD                          -->
      <!-- ======================================================================= -->
      <div class="tab-pane fade <?= $tabInicial === 'ganancias' ? 'show active' : '' ?>" id="paneInformeGanancias" role="tabpanel">
        
        <!-- Filtros del Informe de Ganancias -->
        <div class="card border-0 shadow-sm rounded-3 mb-3">
          <div class="card-body p-3">
            <div class="row g-2 align-items-end">
              <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold mb-1">Fecha Desde</label>
                <input type="date" id="gananciasDesde" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold mb-1">Fecha Hasta</label>
                <input type="date" id="gananciasHasta" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold mb-1">Sucursal</label>
                <select id="gananciasSucursal" class="form-select form-select-sm">
                  <option value="0">Consolidado (Todas las Sucursales)</option>
                  <?php foreach ($sucursales as $s): ?>
                    <option value="<?= $s['id_sucursal'] ?>">Sucursal <?= $s['id_sucursal'] ?> - <?= htmlspecialchars($s['nombre_sucursakl']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-6 col-md-3 d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm flex-grow-1 fw-semibold" onclick="cargarInformeGanancias()">
                  <i class="bi bi-bar-chart-line me-1"></i> Generar Informe
                </button>
                <button type="button" class="btn btn-outline-dark btn-sm fw-semibold" onclick="imprimirInformeGanancias()">
                  <i class="bi bi-printer me-1"></i> Imprimir
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 6 TARJETAS EJECUTIVAS DE GANANCIA -->
        <div class="row g-2 mb-3">
          <!-- 1. Ingresos por Ventas -->
          <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-primary">
              <div class="card-body p-2 p-lg-3">
                <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Ingresos por Ventas</small>
                <h5 class="fw-bold text-primary mb-0 mt-1" id="kpiIngresosVentas">0 Gs.</h5>
                <small class="text-muted" style="font-size: 0.72rem;" id="kpiCantVentas">0 facturas emitidas</small>
              </div>
            </div>
          </div>

          <!-- 2. Costo de Mercadería Vendida (CMV) -->
          <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-danger">
              <div class="card-body p-2 p-lg-3">
                <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Costo Mercadería (CMV)</small>
                <h5 class="fw-bold text-danger mb-0 mt-1" id="kpiCostoVendido">0 Gs.</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Costo de compra vendido</small>
              </div>
            </div>
          </div>

          <!-- 3. Ganancia Bruta Comercial -->
          <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-success">
              <div class="card-body p-2 p-lg-3">
                <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Ganancia Bruta</small>
                <h5 class="fw-bold text-success mb-0 mt-1" id="kpiGananciaBruta">0 Gs.</h5>
                <span class="badge bg-success-subtle text-success mt-1" id="kpiMargenBruto">Margen: 0%</span>
              </div>
            </div>
          </div>

          <!-- 4. Gastos Operativos de Caja -->
          <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-warning">
              <div class="card-body p-2 p-lg-3">
                <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Egresos / Gastos Caja</small>
                <h5 class="fw-bold text-warning mb-0 mt-1" id="kpiGastosCaja">0 Gs.</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Gastos operativos</small>
              </div>
            </div>
          </div>

          <!-- 5. Ganancia Neta Real -->
          <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-success text-white">
              <div class="card-body p-2 p-lg-3">
                <small class="text-white-50 text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Ganancia Neta Real</small>
                <h5 class="fw-bold text-white mb-0 mt-1" id="kpiGananciaNeta">0 Gs.</h5>
                <small class="text-white-50" style="font-size: 0.72rem;">Bruta - Gastos de Caja</small>
              </div>
            </div>
          </div>

          <!-- 6. Total Compras de Stock en el Periodo -->
          <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-info">
              <div class="card-body p-2 p-lg-3">
                <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.7rem;">Compras de Stock</small>
                <h5 class="fw-bold text-info mb-0 mt-1" id="kpiTotalCompras">0 Gs.</h5>
                <small class="text-muted" style="font-size: 0.72rem;" id="kpiCantCompras">0 facturas compra</small>
              </div>
            </div>
          </div>
        </div>

        <!-- TABLAS DE DESGLOSE DE GANANCIAS -->
        <div class="row g-3">
          
          <!-- Desglose 1: Ganancia por Producto Vendido -->
          <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
              <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small text-uppercase">
                  <i class="bi bi-box-seam text-success me-1"></i> Rentabilidad y Ganancia por Producto Vendido
                </span>
                <span class="badge bg-success-subtle text-success" id="badgeCantProdGanancia">0 productos</span>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 440px;">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                      <tr>
                        <th>Producto / Código</th>
                        <th class="text-center">Unid. Vend.</th>
                        <th class="text-end">Costo Prom.</th>
                        <th class="text-end">Venta Prom.</th>
                        <th class="text-end">Total Costo</th>
                        <th class="text-end">Total Venta</th>
                        <th class="text-end">Ganancia Gs.</th>
                        <th class="text-center">Margen</th>
                      </tr>
                    </thead>
                    <tbody id="tbodyGananciasPorProducto">
                      <tr><td colspan="8" class="text-center py-4 text-muted">Cargando rentabilidad por producto...</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Desglose 2: Ganancia por Factura de Venta -->
          <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
              <div class="card-header bg-white py-2 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small text-uppercase">
                  <i class="bi bi-receipt text-primary me-1"></i> Ganancia por Factura de Venta Emitida
                </span>
                <span class="badge bg-primary-subtle text-primary" id="badgeCantFactGanancia">0 facturas</span>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 440px;">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light sticky-top">
                      <tr>
                        <th>Factura / Fecha</th>
                        <th>Cliente</th>
                        <th class="text-end">Venta</th>
                        <th class="text-end">Costo</th>
                        <th class="text-end">Ganancia</th>
                        <th class="text-center">%</th>
                      </tr>
                    </thead>
                    <tbody id="tbodyGananciasPorFactura">
                      <tr><td colspan="6" class="text-center py-4 text-muted">Cargando ganancia por factura...</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>
  </div>
</main>

<!-- MODAL: NUEVO PROVEEDOR RÁPIDO -->
<div class="modal fade" id="modalRapidoProveedor" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white py-2">
        <h6 class="modal-title fw-bold"><i class="bi bi-truck me-1"></i> Registrar Nuevo Proveedor</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="formRapidoProveedor" onsubmit="guardarProveedorRapido(event)">
        <div class="modal-body p-3">
          <div class="row g-2">
            <div class="col-12 col-md-7">
              <label class="form-label small fw-semibold mb-1">Razón Social / Empresa <span class="text-danger">*</span></label>
              <input type="text" name="razon_social" class="form-control form-control-sm" required placeholder="Ej: Importadora del Este S.A.">
            </div>
            <div class="col-12 col-md-5">
              <label class="form-label small fw-semibold mb-1">RUC <span class="text-danger">*</span></label>
              <input type="text" name="ruc_proveedor" class="form-control form-control-sm" required placeholder="80012345-6">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold mb-1">Nombre Contacto</label>
              <input type="text" name="nombre_apellido_prov" class="form-control form-control-sm" placeholder="Representante de ventas">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold mb-1">Teléfono</label>
              <input type="text" name="telefono_proveedor" class="form-control form-control-sm" placeholder="0981 000 000">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold mb-1">Ciudad</label>
              <select name="id_ciudad" class="form-select form-select-sm">
                <?php foreach ($ciudades as $c): ?>
                  <option value="<?= $c['id_ciudad'] ?>"><?= htmlspecialchars($c['nombre_ciudad']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold mb-1">Dirección</label>
              <input type="text" name="direccion_proveedor" class="form-control form-control-sm" placeholder="Avda. Principal">
            </div>
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-sm fw-bold">Guardar Proveedor</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: VER DETALLE DE FACTURA DE COMPRA -->
<div class="modal fade" id="modalDetalleCompra" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white py-2">
        <h6 class="modal-title fw-bold" id="tituloModalDetalleCompra">Detalle de Factura de Compra</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3" id="cuerpoModalDetalleCompra">
        Cargando...
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-outline-dark btn-sm" id="btnImprimirDesdeModalCompra">
          <i class="bi bi-printer me-1"></i> Imprimir Comprobante
        </button>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
const URL_COMPRAS_CTRL = '<?= BASE_URL ?>controladores/ComprasControlador.php';
const URL_PROV_CTRL    = '<?= BASE_URL ?>controladores/ProveedoresControlador.php';

let productoActualCompra = null;
let carritoCompra = [];
let ultimoInformeGanancias = null;

function fmtGs(n) {
  return new Intl.NumberFormat('es-PY').format(Math.round(Number(n) || 0)) + ' Gs.';
}

function toggleCondicionCompra() {
  const cond = document.getElementById('compraCondicion').value;
  const box = document.getElementById('boxCreditoCompra');
  if (cond === '2') {
    box.classList.remove('d-none');
  } else {
    box.classList.add('d-none');
  }
}

function actualizarEtiquetaSucursal() {
  const suc = document.getElementById('compraSucursal').value;
  document.getElementById('badgeSucursalDestino').textContent = 'Suma Stock a Sucursal ' + suc;
  if (productoActualCompra) {
    const st = (parseInt(suc) === 2) ? parseInt(productoActualCompra.stock_sucursal2 || 0) : parseInt(productoActualCompra.stock_sucursal1 || 0);
    document.getElementById('prodCompraStockActual').value = st;
  }
}

// =========================================================================
// BUSCADOR DE PRODUCTOS PARA DAR STOCK (ESTIRA COSTO Y PRECIO VENTA)
// =========================================================================
const inputBuscarProd = document.getElementById('inputBuscarProdCompra');
const listaResultadosProd = document.getElementById('resultadosProdCompra');
let timerBuscarProd = null;

inputBuscarProd.addEventListener('input', function() {
  clearTimeout(timerBuscarProd);
  const q = this.value.trim();
  timerBuscarProd = setTimeout(() => buscarProductosCompra(q), 200);
});

inputBuscarProd.addEventListener('focus', function() {
  buscarProductosCompra(this.value.trim());
});

document.addEventListener('click', function(e) {
  if (!inputBuscarProd.contains(e.target) && !listaResultadosProd.contains(e.target)) {
    listaResultadosProd.classList.add('d-none');
  }
});

async function buscarProductosCompra(q) {
  const suc = document.getElementById('compraSucursal').value;
  try {
    const res = await fetch(`${URL_COMPRAS_CTRL}?accion=buscar_producto_compra&buscar=${encodeURIComponent(q)}&sucursal=${suc}`);
    const json = await res.json();
    if (json.status !== 'success') return;

    if (!json.data || json.data.length === 0) {
      listaResultadosProd.innerHTML = `<div class="list-group-item small text-muted">No se encontraron productos coincidentes.</div>`;
      listaResultadosProd.classList.remove('d-none');
      return;
    }

    listaResultadosProd.innerHTML = json.data.map(p => {
      const costo = Number(p.precio_compra) || 0;
      const venta = Number(p.precio_venta) || 0;
      const stockSuc = Number(p.stock_sucursal) || 0;
      return `
        <button type="button" class="list-group-item list-group-item-action py-2 px-3" onclick='seleccionarProductoCompra(${JSON.stringify(p).replace(/'/g, "&#39;")})'>
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <span class="badge bg-secondary me-1">${p.codigo_barra || ('ID:' + p.id_producto)}</span>
              <strong class="text-dark">${p.nombre_prod}</strong>
              <small class="text-muted ms-1">(${p.nombre_marca})</small>
            </div>
            <span class="badge ${stockSuc <= 5 ? 'bg-danger' : 'bg-success'}">Stock Suc: ${stockSuc}</span>
          </div>
          <div class="d-flex justify-content-between small mt-1 text-muted">
            <span>Costo Compra: <strong class="text-danger">${fmtGs(costo)}</strong></span>
            <span>Precio Venta: <strong class="text-primary">${fmtGs(venta)}</strong></span>
          </div>
        </button>
      `;
    }).join('');
    listaResultadosProd.classList.remove('d-none');
  } catch (err) {
    console.error(err);
  }
}

function seleccionarProductoCompra(prod) {
  productoActualCompra = prod;
  listaResultadosProd.classList.add('d-none');
  inputBuscarProd.value = `${prod.codigo_barra ? prod.codigo_barra + ' - ' : ''}${prod.nombre_prod}`;

  const suc = parseInt(document.getElementById('compraSucursal').value);
  const stockLocal = (suc === 2) ? parseInt(prod.stock_sucursal2 || 0) : parseInt(prod.stock_sucursal1 || 0);

  document.getElementById('prodCompraStockActual').value = stockLocal;
  document.getElementById('prodCompraCantidad').value = 10;
  document.getElementById('prodCompraCosto').value = Math.round(Number(prod.precio_compra) || 0);
  document.getElementById('prodCompraPrecioVenta').value = Math.round(Number(prod.precio_venta) || 0);

  document.getElementById('lblProdCompraCodigo').textContent = prod.codigo_barra || ('ID #' + prod.id_producto);
  document.getElementById('lblProdCompraNombre').textContent = prod.nombre_prod;
  document.getElementById('lblProdCompraMarca').textContent = prod.nombre_marca ? `• Marca: ${prod.nombre_marca}` : '';
  document.getElementById('infoProductoSeleccionadoCompra').classList.remove('d-none');

  calcularMargenPreview();
  document.getElementById('prodCompraCantidad').focus();
  document.getElementById('prodCompraCantidad').select();
}

function calcularMargenPreview() {
  const costo = Number(document.getElementById('prodCompraCosto').value) || 0;
  const venta = Number(document.getElementById('prodCompraPrecioVenta').value) || 0;
  const ganancia = venta - costo;
  const margen = venta > 0 ? ((ganancia / venta) * 100).toFixed(1) : 0;

  document.getElementById('lblProdCompraGananciaUnit').textContent = fmtGs(ganancia);
  const badge = document.getElementById('lblProdCompraMargen');
  badge.textContent = `Margen: ${margen}%`;
  badge.className = 'badge ' + (ganancia > 0 ? 'bg-success' : 'bg-danger');
}

function agregarItemCompra() {
  if (!productoActualCompra) {
    Swal.fire('Seleccione un producto', 'Busque y seleccione un producto de la lista para cargarlo a la factura de compra.', 'warning');
    return;
  }

  const cantidad = parseInt(document.getElementById('prodCompraCantidad').value) || 0;
  const costo = Number(document.getElementById('prodCompraCosto').value) || 0;
  const precioVenta = Number(document.getElementById('prodCompraPrecioVenta').value) || 0;
  const tipoIva = document.getElementById('prodCompraIva').value;
  const stockAnt = parseInt(document.getElementById('prodCompraStockActual').value) || 0;

  if (cantidad <= 0) {
    Swal.fire('Cantidad inválida', 'La cantidad comprada debe ser mayor a 0.', 'warning');
    return;
  }
  if (costo <= 0) {
    Swal.fire('Costo inválido', 'Ingrese el costo unitario de compra.', 'warning');
    return;
  }

  const existente = carritoCompra.find(i => parseInt(i.id_producto) === parseInt(productoActualCompra.id_producto));
  if (existente) {
    existente.cantidad += cantidad;
    existente.costo_unitario = costo;
    existente.precio_venta = precioVenta;
    existente.tipo_iva = tipoIva;
  } else {
    carritoCompra.push({
      id_producto: parseInt(productoActualCompra.id_producto),
      codigo_barra: productoActualCompra.codigo_barra || ('ID-' + productoActualCompra.id_producto),
      nombre_prod: productoActualCompra.nombre_prod,
      stock_anterior: stockAnt,
      cantidad: cantidad,
      costo_unitario: costo,
      precio_venta: precioVenta,
      tipo_iva: tipoIva
    });
  }

  // Limpiar selector para siguiente producto
  productoActualCompra = null;
  inputBuscarProd.value = '';
  document.getElementById('infoProductoSeleccionadoCompra').classList.add('d-none');
  renderCarritoCompra();
  inputBuscarProd.focus();
}

function cambiarCantidadCarritoCompra(idx, nuevaCant) {
  const c = parseInt(nuevaCant) || 1;
  if (c <= 0) return;
  carritoCompra[idx].cantidad = c;
  renderCarritoCompra();
}

function cambiarCostoCarritoCompra(idx, nuevoCosto) {
  const c = Number(nuevoCosto) || 0;
  if (c < 0) return;
  carritoCompra[idx].costo_unitario = c;
  renderCarritoCompra();
}

function cambiarVentaCarritoCompra(idx, nuevaVenta) {
  const v = Number(nuevaVenta) || 0;
  if (v < 0) return;
  carritoCompra[idx].precio_venta = v;
  renderCarritoCompra();
}

function quitarItemCompra(idx) {
  carritoCompra.splice(idx, 1);
  renderCarritoCompra();
}

function limpiarCarritoCompra() {
  carritoCompra = [];
  renderCarritoCompra();
}

function renderCarritoCompra() {
  const tbody = document.getElementById('tbodyCarritoCompra');
  if (carritoCompra.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="9" class="text-center py-4 text-muted">
          <i class="bi bi-box-seam fs-3 d-block mb-1"></i>
          Busque o seleccione productos arriba para cargar la factura de compra y sumar stock.
        </td>
      </tr>`;
    document.getElementById('resumenCantItemsCompra').textContent = '0 ítems';
    document.getElementById('resumenUnidadesCompra').textContent = '0 unid.';
    document.getElementById('resumenExentaCompra').textContent = '0 Gs.';
    document.getElementById('resumenIva5Compra').textContent = '0 Gs.';
    document.getElementById('resumenIva10Compra').textContent = '0 Gs.';
    document.getElementById('resumenTotalCompra').textContent = '0 Gs.';
    document.getElementById('resumenValorVentaLote').textContent = '0 Gs.';
    document.getElementById('resumenGananciaLote').textContent = '+0 Gs.';
    return;
  }

  let totalUnidades = 0;
  let totalCosto = 0;
  let totalVentaProyectada = 0;
  let totalIva10 = 0;
  let totalIva5 = 0;
  let totalExenta = 0;

  tbody.innerHTML = carritoCompra.map((item, idx) => {
    const subCosto = item.cantidad * item.costo_unitario;
    const subVenta = item.cantidad * item.precio_venta;
    const nuevoStock = item.stock_anterior + item.cantidad;
    const margenPct = item.precio_venta > 0 ? (((item.precio_venta - item.costo_unitario) / item.precio_venta) * 100).toFixed(1) : 0;

    totalUnidades += item.cantidad;
    totalCosto += subCosto;
    totalVentaProyectada += subVenta;

    if (item.tipo_iva === '10') {
      totalIva10 += Math.round(subCosto / 11);
    } else if (item.tipo_iva === '5') {
      totalIva5 += Math.round(subCosto / 21);
    } else {
      totalExenta += subCosto;
    }

    return `
      <tr>
        <td>
          <span class="badge bg-light text-dark border me-1">${item.codigo_barra}</span>
          <strong class="text-dark">${item.nombre_prod}</strong>
        </td>
        <td class="text-center text-muted">${item.stock_anterior}</td>
        <td class="text-center" style="width: 90px;">
          <input type="number" class="form-control form-control-sm text-center fw-bold border-success" value="${item.cantidad}" min="1" onchange="cambiarCantidadCarritoCompra(${idx}, this.value)">
        </td>
        <td class="text-center">
          <span class="badge bg-success">${nuevoStock}</span>
        </td>
        <td class="text-end" style="width: 125px;">
          <input type="number" class="form-control form-control-sm text-end fw-semibold" value="${item.costo_unitario}" min="0" step="500" onchange="cambiarCostoCarritoCompra(${idx}, this.value)">
        </td>
        <td class="text-end" style="width: 125px;">
          <input type="number" class="form-control form-control-sm text-end fw-semibold text-primary" value="${item.precio_venta}" min="0" step="500" onchange="cambiarVentaCarritoCompra(${idx}, this.value)">
        </td>
        <td class="text-center">
          <span class="badge ${margenPct > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}">${margenPct}%</span>
        </td>
        <td class="text-end fw-bold text-dark">${fmtGs(subCosto)}</td>
        <td class="text-center">
          <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" onclick="quitarItemCompra(${idx})">
            <i class="bi bi-x-lg"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');

  const gananciaLote = totalVentaProyectada - totalCosto;

  document.getElementById('resumenCantItemsCompra').textContent = `${carritoCompra.length} ítems`;
  document.getElementById('resumenUnidadesCompra').textContent = `${totalUnidades} unid.`;
  document.getElementById('resumenExentaCompra').textContent = fmtGs(totalExenta);
  document.getElementById('resumenIva5Compra').textContent = fmtGs(totalIva5);
  document.getElementById('resumenIva10Compra').textContent = fmtGs(totalIva10);
  document.getElementById('resumenTotalCompra').textContent = fmtGs(totalCosto);
  document.getElementById('resumenValorVentaLote').textContent = fmtGs(totalVentaProyectada);
  document.getElementById('resumenGananciaLote').textContent = (gananciaLote >= 0 ? '+' : '') + fmtGs(gananciaLote);
}

// =========================================================================
// PROCESAR FACTURA DE COMPRA Y DAR STOCK
// =========================================================================
async function procesarFacturaCompra() {
  const idProveedor = document.getElementById('compraProveedor').value;
  const nroFactura  = document.getElementById('compraNroFactura').value.trim();
  const nroTimbrado = document.getElementById('compraTimbrado').value.trim();

  if (!idProveedor) {
    Swal.fire('Falta Proveedor', 'Seleccione el proveedor de la factura de compra.', 'warning');
    return;
  }
  if (!nroFactura) {
    Swal.fire('Falta Nro. de Factura', 'Ingrese el número de la factura de compra.', 'warning');
    return;
  }
  if (carritoCompra.length === 0) {
    Swal.fire('Factura vacía', 'Agregue al menos un producto para ingresar al stock.', 'warning');
    return;
  }

  const payload = {
    id_proveedor: idProveedor,
    nro_factura: nroFactura,
    nro_timbrado: nroTimbrado || 0,
    fecha_vencimiento_timbrado: document.getElementById('compraVtoTimbrado').value,
    fecha: document.getElementById('compraFecha').value,
    id_sucursal: document.getElementById('compraSucursal').value,
    condicion: document.getElementById('compraCondicion').value,
    metodo_pago: document.getElementById('compraMetodoPago').value,
    cantidad_cuotas: document.getElementById('compraCuotas').value || 1,
    actualizar_precios: document.getElementById('chkActualizarPrecios').checked,
    items: carritoCompra
  };

  const btn = document.getElementById('btnGuardarCompra');
  btn.disabled = true;

  try {
    const res = await fetch(`${URL_COMPRAS_CTRL}?accion=registrar_compra`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();

    if (json.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: '¡Compra y Stock Registrados!',
        text: json.message,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-printer me-1"></i> Imprimir Comprobante',
        cancelButtonText: 'Continuar'
      }).then(result => {
        if (result.isConfirmed) {
          imprimirComprobanteCompra(json.id_compra);
        }
      });

      limpiarCarritoCompra();
      document.getElementById('compraNroFactura').value = '001-001-00' + Math.floor(10000 + Math.random() * 89999);
    } else {
      Swal.fire('Error', json.message || 'No se pudo registrar la compra.', 'error');
    }
  } catch (err) {
    Swal.fire('Error de conexión', err.message, 'error');
  } finally {
    btn.disabled = false;
  }
}

// =========================================================================
// PROVEEDOR RÁPIDO DESDE MODAL
// =========================================================================
async function guardarProveedorRapido(e) {
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);
  fd.append('accion', 'guardar');

  try {
    const res = await fetch(URL_PROV_CTRL, { method: 'POST', body: fd });
    const json = await res.json();
    if (json.status === 'success') {
      bootstrap.Modal.getInstance(document.getElementById('modalRapidoProveedor')).hide();
      form.reset();
      // Recargar lista de proveedores
      const resList = await fetch(`${URL_PROV_CTRL}?accion=listar`);
      const jsonList = await resList.json();
      if (jsonList.status === 'success' && jsonList.data.length > 0) {
        const sel = document.getElementById('compraProveedor');
        sel.innerHTML = '<option value="">-- Seleccione Proveedor --</option>' +
          jsonList.data.map(p => {
            const nom = p.razon_social || p.nombre_apellido_prov;
            return `<option value="${p.id_proveedor_}">${nom} (RUC: ${p.ruc_proveedor || 'S/R'})</option>`;
          }).join('');
        sel.value = jsonList.data[0].id_proveedor_;
      }
      Swal.fire('Proveedor Creado', json.message, 'success');
    } else {
      Swal.fire('Atención', json.message, 'warning');
    }
  } catch (err) {
    Swal.fire('Error', err.message, 'error');
  }
}

// =========================================================================
// HISTORIAL DE FACTURAS DE COMPRA (LISTAR, DETALLE, IMPRIMIR, ANULAR)
// =========================================================================
async function cargarHistorialCompras() {
  const buscar = document.getElementById('histCompraBuscar').value.trim();
  const desde  = document.getElementById('histCompraDesde').value;
  const hasta  = document.getElementById('histCompraHasta').value;
  const suc    = document.getElementById('histCompraSucursal').value;

  const tbody = document.getElementById('tbodyHistorialCompras');
  try {
    const url = `${URL_COMPRAS_CTRL}?accion=listar_compras&buscar=${encodeURIComponent(buscar)}&fecha_desde=${desde}&fecha_hasta=${hasta}&sucursal=${suc}`;
    const res = await fetch(url);
    const json = await res.json();
    if (json.status !== 'success') return;

    document.getElementById('badgeTotalComprasHist').textContent = `${json.data.length} compras`;

    if (json.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-muted">No se encontraron facturas de compra en el rango seleccionado.</td></tr>`;
      return;
    }

    tbody.innerHTML = json.data.map(c => {
      const esAnulado = (String(c.estado).toUpperCase() === 'ANULADO');
      const condTxt = (parseInt(c.condicion) === 2) ? `CRÉDITO (${c.cantidad_cuotas} c.)` : 'CONTADO';
      const ivaTotal = (Number(c.iva10) || 0) + (Number(c.iva5) || 0);
      const provNom = c.razon_social || c.nombre_apellido_prov || 'Proveedor General';

      return `
        <tr class="${esAnulado ? 'table-danger opacity-75' : ''}">
          <td class="fw-bold">#${c.id_compra}</td>
          <td>
            <strong class="text-dark d-block">${c.nro_factura}</strong>
            <small class="text-muted">Timb: ${c.nro_timbrado || '-'}</small>
          </td>
          <td>${c.fecha}</td>
          <td>
            <strong class="text-dark d-block">${provNom}</strong>
            <small class="text-muted">RUC: ${c.ruc_proveedor || '-'}</small>
          </td>
          <td><span class="badge bg-light text-dark border">${c.sucursal_nombre}</span></td>
          <td>
            <span class="badge ${parseInt(c.condicion) === 2 ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info'}">${condTxt}</span>
            <small class="d-block text-muted">${c.metodo_pago || 'EFECTIVO'}</small>
          </td>
          <td class="text-end text-muted">${fmtGs(ivaTotal)}</td>
          <td class="text-end fw-bold text-dark">${fmtGs(c.total)}</td>
          <td class="text-center">
            <span class="badge ${esAnulado ? 'bg-danger' : 'bg-success'}">${c.estado}</span>
          </td>
          <td class="text-end pe-3">
            <div class="action-icons">
              <button type="button" class="btn btn-outline-primary btn-icon" onclick="verDetalleCompra(${c.id_compra})" title="Ver Detalle">
                <i class="bi bi-eye"></i>
              </button>
              <button type="button" class="btn btn-outline-dark btn-icon" onclick="imprimirComprobanteCompra(${c.id_compra})" title="Imprimir Factura Compra">
                <i class="bi bi-printer-fill"></i>
              </button>
              ${!esAnulado ? `
                <button type="button" class="btn btn-outline-danger btn-icon" onclick="anularFacturaCompra(${c.id_compra}, '${c.nro_factura}')" title="Anular y Revertir Stock">
                  <i class="bi bi-x-circle-fill"></i>
                </button>
              ` : ''}
            </div>
          </td>
        </tr>
      `;
    }).join('');
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="10" class="text-center py-4 text-danger">Error al cargar compras: ${err.message}</td></tr>`;
  }
}

async function verDetalleCompra(idCompra) {
  try {
    const res = await fetch(`${URL_COMPRAS_CTRL}?accion=obtener_detalle_compra&id_compra=${idCompra}`);
    const json = await res.json();
    if (json.status !== 'success') return;

    const c = json.cabecera;
    const det = json.detalles || [];
    const provNom = c.razon_social || c.nombre_apellido_prov || 'Proveedor';

    document.getElementById('tituloModalDetalleCompra').textContent = `Factura de Compra Nro. ${c.nro_factura} (ID #${c.id_compra})`;
    document.getElementById('btnImprimirDesdeModalCompra').onclick = () => imprimirComprobanteCompra(c.id_compra);

    document.getElementById('cuerpoModalDetalleCompra').innerHTML = `
      <div class="row g-2 mb-3 small bg-light p-2 rounded border">
        <div class="col-md-6">
          <div><strong>Proveedor:</strong> ${provNom} (RUC: ${c.ruc_proveedor || '-'})</div>
          <div><strong>Factura Nro:</strong> ${c.nro_factura} | <strong>Timbrado:</strong> ${c.nro_timbrado}</div>
        </div>
        <div class="col-md-6 text-md-end">
          <div><strong>Fecha:</strong> ${c.fecha} | <strong>Sucursal:</strong> ${c.sucursal_nombre}</div>
          <div><strong>Estado:</strong> <span class="badge ${c.estado === 'ANULADO' ? 'bg-danger' : 'bg-success'}">${c.estado}</span></div>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-2">
          <thead class="table-light">
            <tr>
              <th>Código</th>
              <th>Producto</th>
              <th class="text-center">Cantidad</th>
              <th class="text-end">Costo Unit.</th>
              <th class="text-end">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            ${det.map(d => `
              <tr>
                <td>${d.codigo_barra || d.id_producto}</td>
                <td>${d.nombre_prod}</td>
                <td class="text-center fw-bold">${d.cantidad}</td>
                <td class="text-end">${fmtGs(d.costo_unitario)}</td>
                <td class="text-end fw-bold">${fmtGs(d.cantidad * d.costo_unitario)}</td>
              </tr>
            `).join('')}
          </tbody>
          <tfoot class="table-light">
            <tr>
              <th colspan="4" class="text-end">TOTAL FACTURA COMPRA:</th>
              <th class="text-end text-primary fs-6">${fmtGs(c.total)}</th>
            </tr>
          </tfoot>
        </table>
      </div>
    `;

    new bootstrap.Modal(document.getElementById('modalDetalleCompra')).show();
  } catch (err) {
    console.error(err);
  }
}

async function imprimirComprobanteCompra(idCompra) {
  try {
    const res = await fetch(`${URL_COMPRAS_CTRL}?accion=obtener_detalle_compra&id_compra=${idCompra}`);
    const json = await res.json();
    if (json.status !== 'success') return;
    if (window.ImpresorMayorista) {
      ImpresorMayorista.verCompra(json, 'a4');
      return;
    }

    const c = json.cabecera;
    const det = json.detalles || [];
    const provNom = c.razon_social || c.nombre_apellido_prov || 'Proveedor';

    const win = window.open('', '_blank', 'width=850,height=700');
    win.document.write(`
      <html>
      <head>
        <title>Comprobante de Compra #${c.nro_factura}</title>
        <style>
          body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 20px; }
          .header { border-bottom: 2px solid #111; padding-bottom: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; }
          table { width: 100%; border-collapse: collapse; margin-top: 10px; }
          th, td { border: 1px solid #ccc; padding: 6px 8px; }
          th { background: #f1f5f9; text-align: left; }
          .text-end { text-align: right; }
          .text-center { text-align: center; }
        </style>
      </head>
      <body onload="window.print();">
        <div class="header">
          <div>
            <h2 style="margin:0;">SISTEMA MAYORISTA - RECEPCIÓN DE COMPRA Y STOCK</h2>
            <div>Sucursal Receptora: <strong>${c.sucursal_nombre}</strong></div>
            <div>Proveedor: <strong>${provNom}</strong> | RUC: <strong>${c.ruc_proveedor || '-'}</strong></div>
          </div>
          <div class="text-end">
            <h3 style="margin:0;">FACTURA COMPRA: ${c.nro_factura}</h3>
            <div>Timbrado: ${c.nro_timbrado} | Fecha: ${c.fecha}</div>
            <div>Estado: <strong>${c.estado}</strong> | Pago: ${c.metodo_pago || 'EFECTIVO'}</div>
          </div>
        </div>
        <table>
          <thead>
            <tr>
              <th>Código</th>
              <th>Descripción del Producto</th>
              <th class="text-center">Cant. Ingresada</th>
              <th class="text-end">Costo Unit.</th>
              <th class="text-end">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            ${det.map(d => `
              <tr>
                <td>${d.codigo_barra || d.id_producto}</td>
                <td>${d.nombre_prod}</td>
                <td class="text-center">${d.cantidad}</td>
                <td class="text-end">${fmtGs(d.costo_unitario)}</td>
                <td class="text-end">${fmtGs(d.cantidad * d.costo_unitario)}</td>
              </tr>
            `).join('')}
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" class="text-end">IVA 10%: ${fmtGs(c.iva10)} | IVA 5%: ${fmtGs(c.iva5)} | TOTAL COMPRA:</th>
              <th class="text-end">${fmtGs(c.total)}</th>
            </tr>
          </tfoot>
        </table>
      </body>
      </html>
    `);
    win.document.close();
  } catch (err) {
    console.error(err);
  }
}

function anularFacturaCompra(idCompra, nroFactura) {
  Swal.fire({
    icon: 'warning',
    title: `¿Anular Factura de Compra ${nroFactura}?`,
    text: 'Se descontará del inventario el stock que fue ingresado con esta factura de compra.',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    confirmButtonText: 'Sí, Anular y Revertir Stock',
    cancelButtonText: 'Cancelar'
  }).then(async result => {
    if (!result.isConfirmed) return;
    const fd = new FormData();
    fd.append('accion', 'anular_compra');
    fd.append('id_compra', idCompra);

    const res = await fetch(URL_COMPRAS_CTRL, { method: 'POST', body: fd });
    const json = await res.json();
    if (json.status === 'success') {
      Swal.fire('Compra Anulada', json.message, 'success');
      cargarHistorialCompras();
    } else {
      Swal.fire('Error', json.message, 'error');
    }
  });
}

// =========================================================================
// INFORME DE GANANCIAS Y RENTABILIDAD
// =========================================================================
async function cargarInformeGanancias() {
  const desde = document.getElementById('gananciasDesde').value;
  const hasta = document.getElementById('gananciasHasta').value;
  const suc   = document.getElementById('gananciasSucursal').value;

  try {
    const res = await fetch(`${URL_COMPRAS_CTRL}?accion=informe_ganancias&fecha_desde=${desde}&fecha_hasta=${hasta}&sucursal=${suc}`);
    const json = await res.json();
    if (json.status !== 'success') return;

    ultimoInformeGanancias = json;
    const r = json.resumen;

    document.getElementById('kpiIngresosVentas').textContent = fmtGs(r.ingresos_ventas);
    document.getElementById('kpiCantVentas').textContent = `${r.cantidad_ventas} facturas emitidas`;
    document.getElementById('kpiCostoVendido').textContent = fmtGs(r.costo_mercaderia_vendida);
    document.getElementById('kpiGananciaBruta').textContent = fmtGs(r.ganancia_bruta);
    document.getElementById('kpiMargenBruto').textContent = `Margen: ${r.margen_bruto_pct}%`;
    document.getElementById('kpiGastosCaja').textContent = fmtGs(r.gastos_operativos_caja);
    document.getElementById('kpiGananciaNeta').textContent = fmtGs(r.ganancia_neta);
    document.getElementById('kpiTotalCompras').textContent = fmtGs(r.total_compras_periodo);
    document.getElementById('kpiCantCompras').textContent = `${r.cantidad_compras_periodo} facturas compra`;

    // Tabla por Producto
    const prodList = json.por_producto || [];
    document.getElementById('badgeCantProdGanancia').textContent = `${prodList.length} productos`;
    const tbodyProd = document.getElementById('tbodyGananciasPorProducto');

    if (prodList.length === 0) {
      tbodyProd.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">No se registran ventas confirmadas en este periodo.</td></tr>`;
    } else {
      tbodyProd.innerHTML = prodList.map(p => {
        const gan = Number(p.ganancia_producto) || 0;
        const ventaTot = Number(p.total_facturado) || 0;
        const margen = ventaTot > 0 ? ((gan / ventaTot) * 100).toFixed(1) : 0;
        return `
          <tr>
            <td>
              <span class="badge bg-light text-dark border me-1">${p.codigo_barra || ('ID:' + p.id_producto)}</span>
              <strong class="text-dark">${p.nombre_prod}</strong>
            </td>
            <td class="text-center fw-bold">${p.unidades_vendidas}</td>
            <td class="text-end text-muted">${fmtGs(p.costo_unitario_actual)}</td>
            <td class="text-end">${fmtGs(p.precio_venta_promedio)}</td>
            <td class="text-end text-danger">${fmtGs(p.costo_total_vendido)}</td>
            <td class="text-end fw-semibold">${fmtGs(ventaTot)}</td>
            <td class="text-end fw-bold ${gan >= 0 ? 'text-success' : 'text-danger'}">${fmtGs(gan)}</td>
            <td class="text-center">
              <span class="badge ${gan >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}">${margen}%</span>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Tabla por Factura de Venta
    const factList = json.por_factura || [];
    document.getElementById('badgeCantFactGanancia').textContent = `${factList.length} facturas`;
    const tbodyFact = document.getElementById('tbodyGananciasPorFactura');

    if (factList.length === 0) {
      tbodyFact.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No hay facturas en el periodo seleccionado.</td></tr>`;
    } else {
      tbodyFact.innerHTML = factList.map(f => {
        const gan = Number(f.ganancia_factura) || 0;
        const venta = Number(f.total_venta) || 0;
        const pct = venta > 0 ? ((gan / venta) * 100).toFixed(1) : 0;
        return `
          <tr>
            <td>
              <strong class="text-dark d-block">${f.nro_factura || ('#' + f.id_venta)}</strong>
              <small class="text-muted">${f.fecha_venta}</small>
            </td>
            <td>
              <span class="d-block text-dark">${f.cliente}</span>
              <span class="badge bg-secondary-subtle text-secondary" style="font-size:0.65rem;">${f.tipo_cliente}</span>
            </td>
            <td class="text-end fw-semibold">${fmtGs(venta)}</td>
            <td class="text-end text-danger">${fmtGs(f.costo_factura)}</td>
            <td class="text-end fw-bold ${gan >= 0 ? 'text-success' : 'text-danger'}">${fmtGs(gan)}</td>
            <td class="text-center"><span class="badge bg-success-subtle text-success">${pct}%</span></td>
          </tr>
        `;
      }).join('');
    }
  } catch (err) {
    console.error(err);
  }
}

function imprimirInformeGanancias() {
  if (!ultimoInformeGanancias) {
    cargarInformeGanancias().then(() => ejecutarImpresionGanancias());
  } else {
    ejecutarImpresionGanancias();
  }
}

function ejecutarImpresionGanancias() {
  if (!ultimoInformeGanancias) return;
  const r = ultimoInformeGanancias.resumen;
  const prods = ultimoInformeGanancias.por_producto || [];
  const desde = document.getElementById('gananciasDesde').value;
  const hasta = document.getElementById('gananciasHasta').value;

  const win = window.open('', '_blank', 'width=980,height=750');
  win.document.write(`
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <title>Informe de Ganancias (${desde} al ${hasta})</title>
      <style>
        @page { size: A4 landscape; margin: 8mm; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #0f172a; margin: 0; padding: 12px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px; }
        .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .kpi-box { border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: #f8fafc; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; }
        th { background: #0f172a; color: #ffffff; text-align: left; font-size: 10.5px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
      </style>
    </head>
    <body>
      <div class="header">
        <div>
          <h2 style="margin:0 0 2px 0; color:#0d6efd;">SISTEMA MAYORISTA</h2>
          <div style="font-weight:bold; font-size:13px;">INFORME EJECUTIVO DE GANANCIAS Y RENTABILIDAD</div>
        </div>
        <div style="text-align:right;">
          <div>Periodo: <strong>${desde}</strong> al <strong>${hasta}</strong></div>
          <div style="font-size:10px; color:#64748b;">Generado el ${new Date().toLocaleString('es-PY')}</div>
        </div>
      </div>
      <div class="kpi-grid">
        <div class="kpi-box"><strong>Ingresos por Ventas:</strong><br><span style="font-size:15px; font-weight:800; color:#0f172a;">${fmtGs(r.ingresos_ventas)}</span> (${r.cantidad_ventas} ventas)</div>
        <div class="kpi-box"><strong>Costo Mercadería Vendida (CMV):</strong><br><span style="font-size:15px; font-weight:800; color:#475569;">${fmtGs(r.costo_mercaderia_vendida)}</span></div>
        <div class="kpi-box"><strong>Ganancia Bruta Comercial:</strong><br><span style="font-size:15px; color:#15803d; font-weight:800;">${fmtGs(r.ganancia_bruta)} (${r.margen_bruto_pct}%)</span></div>
        <div class="kpi-box"><strong>Gastos Operativos de Caja:</strong><br><span style="font-size:15px; font-weight:800; color:#dc2626;">-${fmtGs(r.gastos_operativos_caja)}</span></div>
        <div class="kpi-box" style="border-color:#15803d; background:#f0fdf4;"><strong>GANANCIA NETA REAL:</strong><br><span style="font-size:17px; color:#15803d; font-weight:900;">${fmtGs(r.ganancia_neta)} (${r.margen_neto_pct}%)</span></div>
        <div class="kpi-box"><strong>Compras Invertidas en Stock:</strong><br><span style="font-size:15px; font-weight:800; color:#0284c7;">${fmtGs(r.total_compras_periodo)}</span> (${r.cantidad_compras_periodo} compras)</div>
      </div>
      <h3 style="font-size:13px; margin: 12px 0 6px 0; border-bottom:1px solid #cbd5e1; padding-bottom:4px;">Rentabilidad Detallada por Producto Vendido</h3>
      <table>
        <thead>
          <tr>
            <th style="width: 14%;">Código</th>
            <th style="width: 36%;">Producto</th>
            <th class="text-center" style="width: 8%;">Unid.</th>
            <th class="text-end" style="width: 14%;">Costo Total</th>
            <th class="text-end" style="width: 14%;">Venta Total</th>
            <th class="text-end" style="width: 14%;">Ganancia Bruta</th>
          </tr>
        </thead>
        <tbody>
          ${prods.map(p => `
            <tr>
              <td style="font-family:monospace;">${p.codigo_barra || p.id_producto}</td>
              <td>${p.nombre_prod}</td>
              <td class="text-center">${p.unidades_vendidas}</td>
              <td class="text-end">${fmtGs(p.costo_total_vendido)}</td>
              <td class="text-end">${fmtGs(p.total_facturado)}</td>
              <td class="text-end" style="font-weight:bold; color:#15803d;">${fmtGs(p.ganancia_producto)}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
      <script>
        window.addEventListener('DOMContentLoaded', () => {
          setTimeout(() => { window.print(); }, 300);
        });
      </script>
    </body>
    </html>
  `);
  win.document.close();
}

document.addEventListener('DOMContentLoaded', () => {
  const tabInicial = '<?= htmlspecialchars($tabInicial) ?>';
  if (tabInicial === 'historial') {
    cargarHistorialCompras();
  } else if (tabInicial === 'ganancias') {
    cargarInformeGanancias();
  }
});
</script>
