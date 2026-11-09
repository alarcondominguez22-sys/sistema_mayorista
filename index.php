<?php
$page_title = 'Panel Principal';
$active_menu = 'dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = getDB();
$sucursalActual = (int)($_SESSION['id_sucursal'] ?? 1);

// 1. Métricas de Ventas
$stmtVentasHoy = $pdo->prepare("SELECT COALESCE(SUM(total_venta), 0) as total, COUNT(*) as cantidad FROM ventas WHERE fecha_venta = CURDATE() AND estado IN ('CONFIRMADO', 'FACTURADO')");
$stmtVentasHoy->execute();
$ventasHoy = $stmtVentasHoy->fetch(PDO::FETCH_ASSOC);

$stmtVentasMes = $pdo->prepare("SELECT COALESCE(SUM(total_venta), 0) as total, COUNT(*) as cantidad FROM ventas WHERE MONTH(fecha_venta) = MONTH(CURDATE()) AND YEAR(fecha_venta) = YEAR(CURDATE()) AND estado IN ('CONFIRMADO', 'FACTURADO')");
$stmtVentasMes->execute();
$ventasMes = $stmtVentasMes->fetch(PDO::FETCH_ASSOC);

// 2. Conteo de entidades
$totalProductos = (int)$pdo->query("SELECT COUNT(*) FROM productos WHERE estado = 'ACTIVO'")->fetchColumn();
$totalClientes = (int)$pdo->query("SELECT COUNT(*) FROM clientes WHERE estado = 'ACTIVO'")->fetchColumn();
$totalProveedores = (int)$pdo->query("SELECT COUNT(*) FROM proveedores WHERE estado = 'ACTIVO'")->fetchColumn();

// 2b. Cartera de Cuentas por Cobrar
$stmtCreditos = $pdo->query("SELECT COALESCE(SUM(saldo_pendiente), 0) as total_pendiente, COUNT(CASE WHEN saldo_pendiente > 0 THEN 1 END) as cant_pendientes FROM cuentas__cobrar WHERE estado != 'ANULADO'");
$creditosResumen = $stmtCreditos->fetch(PDO::FETCH_ASSOC);

// 3. Estado de Cajas
$stmtCajas = $pdo->query("SELECT id_apertura_cierre_caja, id_sucursal, monto_apertura, fecha_apertura, hora_apertura FROM apertura_cierre_caja WHERE estado = 'ABIERTA'");
$cajasAbiertas = $stmtCajas->fetchAll(PDO::FETCH_ASSOC);

// 4. Alerta de Stock Bajo (menos de 5 unidades)
$colStock = ($sucursalActual == 2) ? 'stock_sucursal2' : 'stock_sucursal1';
$stmtBajoStock = $pdo->prepare("SELECT id_producto, nombre_prod, codigo_barra, stock_actual, $colStock as stock_local 
                               FROM productos 
                               WHERE estado = 'ACTIVO' AND $colStock <= 5 
                               ORDER BY $colStock ASC LIMIT 6");
$stmtBajoStock->execute();
$productosBajoStock = $stmtBajoStock->fetchAll(PDO::FETCH_ASSOC);

// 5. Últimas 10 ventas
$stmtUltimasVentas = $pdo->query("SELECT v.id_venta, v.fecha_venta, v.nro_factura, v.total_venta, v.condicion_pago, v.metodo_pago, v.estado,
                                         c.nombre_cliente, c.apellido_cliente, COALESCE(s.nombre_sucursakl, CONCAT('Sucursal ', v.id_sucursal)) as sucursal_nombre
                                  FROM ventas v
                                  LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                                  LEFT JOIN sucursal s ON v.id_sucursal = s.id_sucursal
                                  ORDER BY v.id_venta DESC LIMIT 10");
$ultimasVentas = $stmtUltimasVentas->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="dashboard-content">
  <div class="container-fluid px-3 px-lg-4 py-4">
    
    <!-- Encabezado de Bienvenida -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="h3 fw-bold mb-1">Panel de Control Mayorista</h1>
        <p class="text-muted small mb-0">Resumen operativo general, ventas del día, inventario en tiempo real y accesos rápidos.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="<?= BASE_URL ?>paginas/movimientos/compras.php" class="btn btn-outline-primary btn-icon-lg shadow-sm" title="Factura Compra (+Stock)">
          <i class="bi bi-bag-plus-fill"></i>
        </a>
        <a href="<?= BASE_URL ?>paginas/movimientos/compras.php?tab=ganancias" class="btn btn-outline-success btn-icon-lg shadow-sm" title="Informe de Ganancias">
          <i class="bi bi-graph-up-arrow"></i>
        </a>
        <a href="<?= BASE_URL ?>paginas/movimientos/cuentas_cobrar.php" class="btn btn-outline-danger btn-icon-lg shadow-sm" title="Cobranzas Crédito">
          <i class="bi bi-wallet2"></i>
        </a>
        <a href="<?= BASE_URL ?>paginas/movimientos/caja.php" class="btn btn-outline-secondary btn-icon-lg shadow-sm" title="Control de Caja">
          <i class="bi bi-cash-stack"></i>
        </a>
        <a href="<?= BASE_URL ?>paginas/movimientos/facturacion.php" class="btn btn-primary btn-icon-lg shadow-sm" title="Ir a Caja Rápida POS">
          <i class="bi bi-cart-check-fill"></i>
        </a>
      </div>
    </div>

    <!-- TARJETAS DE MÉTRICAS PRINCIPALES -->
    <div class="row g-3 mb-4">
      <!-- Ventas del Día -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 p-lg-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold text-uppercase">Ventas de Hoy</span>
              <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-currency-dollar fs-5"></i>
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= formatoMoneda($ventasHoy['total']) ?></h3>
            <small class="text-muted"><i class="bi bi-check2-all text-success me-1"></i><?= $ventasHoy['cantidad'] ?> facturas emitidas hoy</small>
          </div>
        </div>
      </div>

      <!-- Ventas del Mes -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 p-lg-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold text-uppercase">Facturación del Mes</span>
              <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-calendar-check fs-5"></i>
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= formatoMoneda($ventasMes['total']) ?></h3>
            <small class="text-muted"><i class="bi bi-graph-up-arrow text-primary me-1"></i><?= $ventasMes['cantidad'] ?> ventas acumuladas</small>
          </div>
        </div>
      </div>

      <!-- Productos Activos -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 p-lg-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold text-uppercase">Catálogo de Productos</span>
              <div class="avatar-sm bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-box-seam fs-5"></i>
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= number_format($totalProductos, 0, ',', '.') ?></h3>
            <small class="text-muted"><a href="<?= BASE_URL ?>paginas/referenciales/productos.php" class="text-decoration-none">Ver catálogo completo →</a></small>
          </div>
        </div>
      </div>

      <!-- Clientes Registrados -->
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-body p-3 p-lg-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small fw-semibold text-uppercase">Clientes Activos</span>
              <div class="avatar-sm bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-people fs-5"></i>
              </div>
            </div>
            <h3 class="fw-bold text-dark mb-1"><?= number_format($totalClientes, 0, ',', '.') ?></h3>
            <small class="text-muted"><a href="<?= BASE_URL ?>paginas/referenciales/clientes.php" class="text-decoration-none">Directorio de clientes →</a></small>
          </div>
        </div>
      </div>
    </div>

    <!-- ACCESOS RÁPIDOS A MÓDULOS DEL SISTEMA -->
    <div class="row g-3 mb-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 bg-white">
          <div class="card-header bg-white py-3 border-bottom">
            <span class="fw-bold text-dark"><i class="bi bi-grid-fill text-primary me-2"></i> Accesos Rápidos a Módulos Operativos</span>
          </div>
          <div class="card-body p-3">
            <div class="row g-3">
              <div class="col-6 col-md-4 col-xl-2">
                <a href="<?= BASE_URL ?>paginas/movimientos/facturacion.php" class="btn btn-light border w-100 p-3 text-start text-decoration-none rounded-3 h-100 d-flex flex-column justify-content-between hover-shadow">
                  <i class="bi bi-cart-check-fill fs-2 text-success mb-2"></i>
                  <div>
                    <strong class="d-block text-dark">Caja Rápida</strong>
                    <small class="text-muted">Mostrador y Mayorista</small>
                  </div>
                </a>
              </div>
              <div class="col-6 col-md-4 col-xl-2">
                <a href="<?= BASE_URL ?>paginas/movimientos/cuentas_cobrar.php" class="btn btn-light border w-100 p-3 text-start text-decoration-none rounded-3 h-100 d-flex flex-column justify-content-between hover-shadow">
                  <i class="bi bi-wallet2 fs-2 text-danger mb-2"></i>
                  <div>
                    <strong class="d-block text-dark">Por Cobrar</strong>
                    <small class="text-danger fw-semibold"><?= formatoMoneda($creditosResumen['total_pendiente']) ?></small>
                  </div>
                </a>
              </div>
              <div class="col-6 col-md-4 col-xl-2">
                <a href="<?= BASE_URL ?>paginas/referenciales/productos.php" class="btn btn-light border w-100 p-3 text-start text-decoration-none rounded-3 h-100 d-flex flex-column justify-content-between hover-shadow">
                  <i class="bi bi-box-seam fs-2 text-primary mb-2"></i>
                  <div>
                    <strong class="d-block text-dark">Productos</strong>
                    <small class="text-muted">Stock y Precios</small>
                  </div>
                </a>
              </div>
              <div class="col-6 col-md-4 col-xl-2">
                <a href="<?= BASE_URL ?>paginas/movimientos/caja.php" class="btn btn-light border w-100 p-3 text-start text-decoration-none rounded-3 h-100 d-flex flex-column justify-content-between hover-shadow">
                  <i class="bi bi-cash-stack fs-2 text-warning mb-2"></i>
                  <div>
                    <strong class="d-block text-dark">Caja y Arqueo</strong>
                    <small class="text-muted">Apertura y Cierre</small>
                  </div>
                </a>
              </div>
              <div class="col-6 col-md-4 col-xl-2">
                <a href="<?= BASE_URL ?>paginas/movimientos/ventas_historial.php" class="btn btn-light border w-100 p-3 text-start text-decoration-none rounded-3 h-100 d-flex flex-column justify-content-between hover-shadow">
                  <i class="bi bi-clock-history fs-2 text-secondary mb-2"></i>
                  <div>
                    <strong class="d-block text-dark">Historial Ventas</strong>
                    <small class="text-muted">Facturas y Anulaciones</small>
                  </div>
                </a>
              </div>
              <div class="col-6 col-md-4 col-xl-2">
                <a href="<?= BASE_URL ?>paginas/movimientos/ajuste_stock.php" class="btn btn-light border w-100 p-3 text-start text-decoration-none rounded-3 h-100 d-flex flex-column justify-content-between hover-shadow">
                  <i class="bi bi-sliders fs-2 text-info mb-2"></i>
                  <div>
                    <strong class="d-block text-dark">Ajuste de Stock</strong>
                    <small class="text-muted">Mermas y Conteos</small>
                  </div>
                </a>
              </div>
              <div class="col-6 col-md-4 col-xl-2">
                <a href="<?= BASE_URL ?>paginas/movimientos/compras.php" class="btn btn-light border w-100 p-3 text-start text-decoration-none rounded-3 h-100 d-flex flex-column justify-content-between hover-shadow">
                  <i class="bi bi-bag-plus-fill fs-2 text-primary mb-2"></i>
                  <div>
                    <strong class="d-block text-dark">Factura Compra</strong>
                    <small class="text-primary fw-semibold">Dar Stock a Productos</small>
                  </div>
                </a>
              </div>
              <div class="col-6 col-md-4 col-xl-2">
                <a href="<?= BASE_URL ?>paginas/movimientos/compras.php?tab=ganancias" class="btn btn-light border w-100 p-3 text-start text-decoration-none rounded-3 h-100 d-flex flex-column justify-content-between hover-shadow">
                  <i class="bi bi-graph-up-arrow fs-2 text-success mb-2"></i>
                  <div>
                    <strong class="d-block text-dark">Ganancias</strong>
                    <small class="text-success fw-semibold">Costos vs. Ventas</small>
                  </div>
                </a>
              </div>
              <div class="col-6 col-md-4 col-xl-2">
                <a href="<?= BASE_URL ?>paginas/referenciales/proveedores.php" class="btn btn-light border w-100 p-3 text-start text-decoration-none rounded-3 h-100 d-flex flex-column justify-content-between hover-shadow">
                  <i class="bi bi-truck fs-2 text-dark mb-2"></i>
                  <div>
                    <strong class="d-block text-dark">Proveedores</strong>
                    <small class="text-muted">Compras y Distribución</small>
                  </div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SECCIÓN DIVIDIDA: ÚLTIMAS VENTAS Y ALERTAS DE INVENTARIO -->
    <div class="row g-4">
      
      <!-- Tabla de Últimas Ventas -->
      <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-bold text-dark"><i class="bi bi-receipt me-2 text-primary"></i> Últimas Facturas Emitidas</span>
            <a href="<?= BASE_URL ?>paginas/movimientos/ventas_historial.php" class="small text-decoration-none fw-semibold">Ver todas →</a>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Nro. Factura</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Sucursal</th>
                    <th class="text-end">Total</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end pe-3">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($ultimasVentas)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No se registran ventas recientes.</td></tr>
                  <?php else: ?>
                    <?php foreach ($ultimasVentas as $uv): 
                      $estUpper = strtoupper($uv['estado'] ?? '');
                      $esAnulada = in_array($estUpper, ['ANULADO', 'ANULADA'], true);
                    ?>
                      <tr>
                        <td><span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($uv['nro_factura'] ?: '#' . $uv['id_venta']) ?></span></td>
                        <td><small class="text-muted"><?= htmlspecialchars($uv['fecha_venta']) ?></small></td>
                        <td><strong class="text-dark"><?= htmlspecialchars(($uv['nombre_cliente'] ?: 'CLIENTE') . ' ' . ($uv['apellido_cliente'] ?? '')) ?></strong></td>
                        <td><small class="text-secondary"><?= htmlspecialchars($uv['sucursal_nombre'] ?? 'Sucursal ' . ($uv['id_sucursal'] ?? 1)) ?></small></td>
                        <td class="text-end fw-bold text-dark"><?= formatoMoneda($uv['total_venta']) ?></td>
                        <td class="text-center">
                          <?php if (!$esAnulada): ?>
                            <span class="badge bg-success-subtle text-success px-2 py-1"><?= htmlspecialchars($estUpper ?: 'CONFIRMADO') ?></span>
                          <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger px-2 py-1">ANULADO</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-end pe-3">
                          <div class="d-inline-flex gap-1">
                            <button type="button" class="btn btn-sm btn-primary px-2 py-1" title="Imprimir Factura" onclick="imprimirVentaRapida(<?= (int)$uv['id_venta'] ?>)">
                              <i class="bi bi-printer-fill"></i>
                            </button>
                            <?php if (!$esAnulada): ?>
                              <button type="button" class="btn btn-sm btn-danger px-2 py-1" title="Anular Factura" onclick="anularVentaRapida(<?= (int)$uv['id_venta'] ?>, '<?= htmlspecialchars($uv['nro_factura'] ?: '#' . $uv['id_venta'], ENT_QUOTES) ?>')">
                                <i class="bi bi-x-circle-fill"></i>
                              </button>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Alertas de Stock Bajo -->
      <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
          <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Alertas de Stock Bajo (≤ 5 un.)</span>
            <span class="badge bg-danger-subtle text-danger small"><?= count($productosBajoStock) ?> productos</span>
          </div>
          <div class="card-body p-0">
            <ul class="list-group list-group-flush">
              <?php if (empty($productosBajoStock)): ?>
                <li class="list-group-item text-center py-4 text-muted">Todos los productos cuentan con stock suficiente.</li>
              <?php else: ?>
                <?php foreach ($productosBajoStock as $prod): ?>
                  <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                    <div>
                      <strong class="text-dark d-block small"><?= htmlspecialchars($prod['nombre_prod']) ?></strong>
                      <span class="badge bg-light text-muted border font-monospace small"><?= htmlspecialchars($prod['codigo_barra'] ?: 'S/C') ?></span>
                    </div>
                    <div class="text-end">
                      <span class="badge <?= $prod['stock_local'] <= 0 ? 'bg-danger text-white' : 'bg-warning text-dark' ?> fw-bold px-2 py-1 rounded-pill">
                        <?= $prod['stock_local'] ?> un.
                      </span>
                    </div>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </div>
          <div class="card-footer bg-white border-top text-center py-2">
            <a href="<?= BASE_URL ?>paginas/movimientos/ajuste_stock.php" class="btn btn-link btn-sm text-decoration-none">
              <i class="bi bi-sliders me-1"></i> Ir a Ajustar Stock
            </a>
          </div>
        </div>
      </div>

    </div>

  </div>
</main>

<script>
const URL_VENTAS_DASH = '<?= BASE_URL ?>controladores/VentasControlador.php';
const formatGsDash = (n) => new Intl.NumberFormat('es-PY').format(n || 0) + ' Gs.';

async function imprimirVentaRapida(idVenta, formato = 'ticket') {
  try {
    const res = await fetch(`${URL_VENTAS_DASH}?accion=obtener_detalle&id_venta=${idVenta}`);
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
          <span>${formatGsDash(it.cantidad * it.precio_unitario)}</span>
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
        <body onload="window.print(); window.close();">
          <div class="header">
            <h3>SISTEMA MAYORISTA</h3>
            <div>${cab.sucursal_nombre || 'Sucursal Central'}</div>
            <div>Factura: ${cab.nro_factura || ('#' + cab.id_venta)}</div>
            <div>Fecha: ${cab.fecha_venta}</div>
            <div>Cliente: ${cab.nombre_cliente || 'CLIENTE'} (${cab.cedula_cliente || '-'})</div>
            <div>Condición: ${cab.condicion_pago} - ${cab.metodo_pago}</div>
          </div>
          <div>${itemsHtml}</div>
          <div class="total">
            <span>TOTAL:</span>
            <span>${formatGsDash(cab.total_venta)}</span>
          </div>
          <div class="footer">Comprobante válido para operaciones comerciales.</div>
        </body>
      </html>
    `);
    ventana.document.close();
  } catch (err) {
    console.error(err);
    Swal.fire('Error', 'Error al imprimir la factura.', 'error');
  }
}

async function anularVentaRapida(idVenta, nroFactura) {
  const result = await Swal.fire({
    title: `¿Anular Factura ${nroFactura}?`,
    text: 'Esta acción anulará el comprobante y repondrá automáticamente el stock de los productos en el inventario.',
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
    const res = await fetch(URL_VENTAS_DASH, { method: 'POST', body: formData });
    const json = await res.json();
    if (json.status === 'success') {
      await Swal.fire({ icon: 'success', title: 'Factura Anulada', text: json.message, timer: 1600, showConfirmButton: false });
      window.location.reload();
    } else {
      Swal.fire('Error', json.message, 'error');
    }
  } catch (err) {
    Swal.fire('Error', 'No se pudo anular la factura.', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
