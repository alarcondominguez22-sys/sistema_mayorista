<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar_compras';
$idSucursalSesion = (int)($_SESSION['id_sucursal'] ?? 1);
$idUsuario = (int)($_SESSION['idusuario'] ?? 1);

try {
    // 1. Buscar productos para Factura de Compra (incluye productos con o sin stock, estirando costo y precio venta)
    if ($accion === 'buscar_producto_compra') {
        $q = trim($_GET['q'] ?? $_GET['buscar'] ?? '');
        $sucursal = (int)($_GET['sucursal'] ?? $idSucursalSesion);

        if (empty($q)) {
            $sql = "SELECT p.id_producto, p.nombre_prod, p.descripcion, p.codigo_barra,
                           p.precio_compra, p.precio_venta, p.stock_actual,
                           p.stock_sucursal1, p.stock_sucursal2, p.id_tipo_impuesto,
                           m.descripcion AS marca_nombre, m.descripcion AS nombre_marca,
                           c.descripcion AS categoria_nombre, c.descripcion AS nombre_categoria
                    FROM productos p
                    LEFT JOIN marcas m ON p.id_marcas = m.id_marcas
                    LEFT JOIN categorias c ON p.id_categoria = c.id_categorias
                    WHERE p.estado = 'ACTIVO'
                    ORDER BY p.id_producto DESC
                    LIMIT 35";
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql = "SELECT p.id_producto, p.nombre_prod, p.descripcion, p.codigo_barra,
                           p.precio_compra, p.precio_venta, p.stock_actual,
                           p.stock_sucursal1, p.stock_sucursal2, p.id_tipo_impuesto,
                           m.descripcion AS marca_nombre, m.descripcion AS nombre_marca,
                           c.descripcion AS categoria_nombre, c.descripcion AS nombre_categoria
                    FROM productos p
                    LEFT JOIN marcas m ON p.id_marcas = m.id_marcas
                    LEFT JOIN categorias c ON p.id_categoria = c.id_categorias
                    WHERE p.estado = 'ACTIVO'
                      AND (p.codigo_barra = :exact OR p.nombre_prod LIKE :q1 OR p.descripcion LIKE :q2 OR p.codigo_barra LIKE :q3 OR m.descripcion LIKE :q4)
                    ORDER BY (p.codigo_barra = :exact2) DESC, p.nombre_prod ASC
                    LIMIT 35";
            $stmt = $pdo->prepare($sql);
            $term = "%$q%";
            $stmt->execute([
                ':exact' => $q,
                ':exact2' => $q,
                ':q1' => $term,
                ':q2' => $term,
                ':q3' => $term,
                ':q4' => $term
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach ($data as &$item) {
            $stockSuc = ($sucursal === 2) ? (int)$item['stock_sucursal2'] : (int)$item['stock_sucursal1'];
            $item['stock_sucursal_actual'] = $stockSuc;
            $item['stock_sucursal'] = $stockSuc;
            $pv = (int)($item['precio_venta'] ?? 0);
            $pc = (int)($item['precio_compra'] ?? 0);
            if ($pv <= 0) {
                $pv = ($pc > 0) ? (int)(round(($pc * 1.35) / 1000) * 1000) : 35000;
            }
            if ($pc <= 0) {
                $pc = (int)(round(($pv * 0.68) / 1000) * 1000);
            }
            $item['precio_compra'] = $pc;
            $item['precio_venta'] = $pv;
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // 2. Registrar Factura de Compra y sumar Stock a los productos
    if ($accion === 'registrar_compra') {
        $payloadRaw = file_get_contents('php://input');
        $data = json_decode($payloadRaw, true);
        if (!$data) {
            $data = $_POST;
        }

        $idProveedor = (int)($data['id_proveedor'] ?? 0);
        $nroFactura = trim($data['nro_factura'] ?? '');
        $nroTimbrado = trim($data['nro_timbrado'] ?? '12345678');
        $fechaVencTimbrado = !empty($data['fecha_vencimiento_timbrado']) ? trim($data['fecha_vencimiento_timbrado']) : date('Y-m-d', strtotime('+1 year'));
        $fechaCompra = !empty($data['fecha']) ? trim($data['fecha']) : date('Y-m-d');
        $rawCond = strtoupper(trim((string)($data['condicion_pago'] ?? $data['condicion'] ?? '1')));
        $esCredito = ($rawCond === 'CREDITO' || $rawCond === '2');
        $condicionInt = $esCredito ? 2 : 1;
        $metodoPago = $esCredito ? 'CREDITO' : strtoupper(trim($data['metodo_pago'] ?? 'EFECTIVO'));
        $cantCuotas = $esCredito ? max(1, (int)($data['cantidad_cuotas'] ?? 1)) : 1;
        $sucursal = (int)($data['id_sucursal'] ?? $idSucursalSesion);
        $items = $data['items'] ?? [];

        if (!$idProveedor) {
            echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar un proveedor para la factura de compra.', 'mensaje' => 'Debe seleccionar un proveedor para la factura de compra.']);
            exit;
        }
        if (empty($nroFactura)) {
            echo json_encode(['status' => 'error', 'message' => 'Debe ingresar el número de factura de compra del proveedor.', 'mensaje' => 'Debe ingresar el número de factura de compra del proveedor.']);
            exit;
        }
        if (empty($items)) {
            echo json_encode(['status' => 'error', 'message' => 'Debe agregar al menos un producto a la factura de compra.', 'mensaje' => 'Debe agregar al menos un producto a la factura de compra.']);
            exit;
        }

        $pdo->beginTransaction();

        $totalIva10 = 0;
        $totalIva5 = 0;
        $totalExenta = 0;
        $totalCompra = 0;

        foreach ($items as $it) {
            $cant = max(1, (int)$it['cantidad']);
            $costo = max(0, (int)$it['costo_unitario']);
            $sub = $cant * $costo;
            if (isset($it['tipo_iva'])) {
                $tIva = (int)$it['tipo_iva'];
                $tipoImp = ($tIva === 5) ? 2 : (($tIva === 0) ? 3 : 1);
            } else {
                $tipoImp = (int)($it['id_tipo_impuesto'] ?? 1);
            }
            $totalCompra += $sub;

            if ($tipoImp === 1) {
                $totalIva10 += (int)round($sub / 11);
            } elseif ($tipoImp === 2) {
                $totalIva5 += (int)round($sub / 21);
            } else {
                $totalExenta += $sub;
            }
        }

        // Insertar cabecera en `compra`
        $stmtCab = $pdo->prepare("INSERT INTO compra 
            (id_orden_compra, id_proveedor_, fecha, condicion, metodo_pago, nro_factura, nro_timbrado, 
             fecha_vencimiento_timbrado, estado, forma_de_pago, cantidad_cuotas, iva5, iva10, exenta, total, id_sucursal, idusuario)
            VALUES (0, :prov, :fec, :cond, :met, :fac, :tim, :vtim, 'ACTIVO', :fpago, :cuotas, :i5, :i10, :exe, :tot, :suc, :usr)");
        $stmtCab->execute([
            ':prov' => $idProveedor,
            ':fec' => $fechaCompra,
            ':cond' => $condicionInt,
            ':met' => $metodoPago,
            ':fac' => $nroFactura,
            ':tim' => $nroTimbrado ?: 'S/T',
            ':vtim' => $fechaVencTimbrado,
            ':fpago' => $fechaCompra,
            ':cuotas' => $cantCuotas,
            ':i5' => $totalIva5,
            ':i10' => $totalIva10,
            ':exe' => $totalExenta,
            ':tot' => $totalCompra,
            ':suc' => $sucursal,
            ':usr' => $idUsuario
        ]);

        $idCompra = (int)$pdo->lastInsertId();

        // Insertar detalles en `compras_detalle` y actualizar Stock + Costo + Precio Venta en `productos`
        $stmtDet = $pdo->prepare("INSERT INTO compras_detalle 
            (id_compra, id_producto, tipo_item, cantidad, costo_unitario, iva_10, id_detalle_compra, iva_5, exenta, id_sucursal)
            VALUES (:idc, :idp, 'PRODUCTO', :cant, :costo, :i10, :idd, :i5, :exe, :suc)");

        $colStock = ($sucursal === 2) ? 'stock_sucursal2' : 'stock_sucursal1';

        $stmtUpdProd = $pdo->prepare("UPDATE productos SET 
            stock_actual = stock_actual + :c1,
            $colStock = $colStock + :c2,
            precio_compra = :pc,
            precio_venta = CASE WHEN :pv > 0 THEN :pv2 ELSE precio_venta END
            WHERE id_producto = :idp");

        $numDet = 1;
        foreach ($items as $it) {
            $idProd = (int)$it['id_producto'];
            $cant = max(1, (int)$it['cantidad']);
            $costo = max(0, (int)$it['costo_unitario']);
            $precioVentaNuevo = max(0, (int)($it['precio_venta'] ?? 0));
            if (isset($it['tipo_iva'])) {
                $tIva = (int)$it['tipo_iva'];
                $tipoImp = ($tIva === 5) ? 2 : (($tIva === 0) ? 3 : 1);
            } else {
                $tipoImp = (int)($it['id_tipo_impuesto'] ?? 1);
            }
            $sub = $cant * $costo;

            $i10 = ($tipoImp === 1) ? $sub : 0;
            $i5 = ($tipoImp === 2) ? $sub : 0;
            $exe = ($tipoImp !== 1 && $tipoImp !== 2) ? $sub : 0;

            $stmtDet->execute([
                ':idc' => $idCompra,
                ':idp' => $idProd,
                ':cant' => $cant,
                ':costo' => $costo,
                ':i10' => $i10,
                ':idd' => $numDet++,
                ':i5' => $i5,
                ':exe' => $exe,
                ':suc' => $sucursal
            ]);

            $stmtUpdProd->execute([
                ':c1' => $cant,
                ':c2' => $cant,
                ':pc' => $costo,
                ':pv' => $precioVentaNuevo,
                ':pv2' => $precioVentaNuevo,
                ':idp' => $idProd
            ]);
        }

        $pdo->commit();

        $msgOk = "Factura de Compra #$nroFactura registrada. Se sumó el stock en Sucursal $sucursal y se actualizaron los costos.";
        echo json_encode([
            'status' => 'success',
            'message' => $msgOk,
            'mensaje' => $msgOk,
            'id_compra' => $idCompra,
            'total' => $totalCompra
        ]);
        exit;
    }

    // 3. Listar Facturas de Compra
    if ($accion === 'listar_compras') {
        $buscar = trim($_GET['buscar'] ?? '');
        $fechaDesde = $_GET['fecha_desde'] ?? '';
        $fechaHasta = $_GET['fecha_hasta'] ?? '';
        $suc = !empty($_GET['sucursal']) ? (int)$_GET['sucursal'] : null;

        $sql = "SELECT c.id_compra, c.fecha, c.condicion, COALESCE(c.metodo_pago, 'EFECTIVO') AS metodo_pago,
                       c.nro_factura, c.nro_timbrado, c.estado, c.cantidad_cuotas, c.total, c.iva10, c.iva5, c.exenta, c.id_sucursal,
                       p.nombre_apellido_prov, p.razon_social, p.ruc_proveedor,
                       COALESCE(s.nombre_sucursakl, CONCAT('Sucursal ', c.id_sucursal)) AS sucursal_nombre,
                       (SELECT COALESCE(SUM(cd.cantidad), 0) FROM compras_detalle cd WHERE cd.id_compra = c.id_compra) AS total_unidades
                FROM compra c
                LEFT JOIN proveedores p ON c.id_proveedor_ = p.id_proveedor_
                LEFT JOIN sucursal s ON c.id_sucursal = s.id_sucursal
                WHERE 1=1";
        $params = [];

        if (!empty($buscar)) {
            $sql .= " AND (c.nro_factura LIKE :b1 OR p.razon_social LIKE :b2 OR p.nombre_apellido_prov LIKE :b3 OR p.ruc_proveedor LIKE :b4)";
            $term = "%$buscar%";
            $params[':b1'] = $term;
            $params[':b2'] = $term;
            $params[':b3'] = $term;
            $params[':b4'] = $term;
        }
        if (!empty($fechaDesde)) {
            $sql .= " AND c.fecha >= :fd";
            $params[':fd'] = $fechaDesde;
        }
        if (!empty($fechaHasta)) {
            $sql .= " AND c.fecha <= :fh";
            $params[':fh'] = $fechaHasta;
        }
        if ($suc) {
            $sql .= " AND c.id_sucursal = :suc";
            $params[':suc'] = $suc;
        }

        $sql .= " ORDER BY c.id_compra DESC LIMIT 100";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data, 'total' => count($data)]);
        exit;
    }

    // 4. Obtener detalle de una Factura de Compra
    if ($accion === 'obtener_detalle_compra') {
        $idCompra = (int)($_GET['id_compra'] ?? 0);

        $stmtCab = $pdo->prepare("SELECT c.*, p.nombre_apellido_prov, p.razon_social, p.ruc_proveedor, p.telefono_proveedor,
                                         COALESCE(s.nombre_sucursakl, CONCAT('Sucursal ', c.id_sucursal)) AS sucursal_nombre
                                  FROM compra c
                                  LEFT JOIN proveedores p ON c.id_proveedor_ = p.id_proveedor_
                                  LEFT JOIN sucursal s ON c.id_sucursal = s.id_sucursal
                                  WHERE c.id_compra = :id");
        $stmtCab->execute([':id' => $idCompra]);
        $cabecera = $stmtCab->fetch(PDO::FETCH_ASSOC);

        if (!$cabecera) {
            echo json_encode(['status' => 'error', 'message' => 'Factura de compra no encontrada.']);
            exit;
        }

        $stmtDet = $pdo->prepare("SELECT cd.*, pr.nombre_prod, pr.codigo_barra, pr.precio_venta
                                  FROM compras_detalle cd
                                  LEFT JOIN productos pr ON cd.id_producto = pr.id_producto
                                  WHERE cd.id_compra = :id");
        $stmtDet->execute([':id' => $idCompra]);
        $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'cabecera' => $cabecera, 'detalles' => $detalles]);
        exit;
    }

    // 5. Anular Factura de Compra y revertir Stock
    if ($accion === 'anular_compra') {
        $idCompra = (int)($_POST['id_compra'] ?? 0);

        $stmtCab = $pdo->prepare("SELECT estado, id_sucursal, nro_factura FROM compra WHERE id_compra = :id");
        $stmtCab->execute([':id' => $idCompra]);
        $compra = $stmtCab->fetch(PDO::FETCH_ASSOC);

        if (!$compra) {
            echo json_encode(['status' => 'error', 'message' => 'Factura de compra no encontrada.']);
            exit;
        }
        if (in_array(strtoupper($compra['estado']), ['ANULADO', 'ANULADA'], true)) {
            echo json_encode(['status' => 'error', 'message' => 'Esta factura de compra ya se encuentra anulada.']);
            exit;
        }

        $pdo->beginTransaction();

        $stmtDet = $pdo->prepare("SELECT id_producto, cantidad FROM compras_detalle WHERE id_compra = :id");
        $stmtDet->execute([':id' => $idCompra]);
        $items = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        $colStock = ((int)$compra['id_sucursal'] === 2) ? 'stock_sucursal2' : 'stock_sucursal1';
        $stmtRevert = $pdo->prepare("UPDATE productos SET 
            stock_actual = GREATEST(0, stock_actual - :c1),
            $colStock = GREATEST(0, $colStock - :c2)
            WHERE id_producto = :idp");

        foreach ($items as $it) {
            $stmtRevert->execute([
                ':c1' => (int)$it['cantidad'],
                ':c2' => (int)$it['cantidad'],
                ':idp' => (int)$it['id_producto']
            ]);
        }

        $stmtUpd = $pdo->prepare("UPDATE compra SET estado = 'ANULADO' WHERE id_compra = :id");
        $stmtUpd->execute([':id' => $idCompra]);

        $pdo->commit();

        $msgAnul = "Factura de compra #{$compra['nro_factura']} anulada y stock descontado correctamente.";
        echo json_encode([
            'status' => 'success',
            'message' => $msgAnul,
            'mensaje' => $msgAnul
        ]);
        exit;
    }

    // 6. Informe de Ganancias y Rentabilidad (Ventas vs Costos de Compra vs Gastos)
    if ($accion === 'informe_ganancias') {
        $fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01');
        $fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
        $suc = !empty($_GET['sucursal']) ? (int)$_GET['sucursal'] : null;

        $whereVentas = " WHERE v.estado != 'ANULADO'";
        $paramsV = [];
        if (!empty($fechaDesde)) {
            $whereVentas .= " AND v.fecha_venta >= :fd";
            $paramsV[':fd'] = $fechaDesde;
        }
        if (!empty($fechaHasta)) {
            $whereVentas .= " AND v.fecha_venta <= :fh";
            $paramsV[':fh'] = $fechaHasta;
        }
        if ($suc) {
            $whereVentas .= " AND v.id_sucursal = :suc";
            $paramsV[':suc'] = $suc;
        }

        // A. Ganancia por Producto Vendido
        $sqlProd = "SELECT 
                        vd.id_producto,
                        COALESCE(p.codigo_barra, 'S/C') AS codigo_barra,
                        COALESCE(p.nombre_prod, vd.descripcion) AS nombre_prod,
                        SUM(vd.cantidad) AS unidades_vendidas,
                        ROUND(AVG(vd.precio_unitario)) AS precio_venta_prom,
                        ROUND(AVG(vd.precio_unitario)) AS precio_venta_promedio,
                        COALESCE(NULLIF(p.precio_compra, 0), ROUND(AVG(vd.precio_unitario) * 0.68)) AS costo_unitario_compra,
                        COALESCE(NULLIF(p.precio_compra, 0), ROUND(AVG(vd.precio_unitario) * 0.68)) AS costo_unitario_actual,
                        SUM(vd.cantidad * vd.precio_unitario) AS total_ingresos,
                        SUM(vd.cantidad * vd.precio_unitario) AS total_facturado,
                        SUM(vd.cantidad * COALESCE(NULLIF(p.precio_compra, 0), ROUND(vd.precio_unitario * 0.68))) AS total_costo,
                        SUM(vd.cantidad * COALESCE(NULLIF(p.precio_compra, 0), ROUND(vd.precio_unitario * 0.68))) AS costo_total_vendido,
                        SUM(vd.cantidad * vd.precio_unitario) - SUM(vd.cantidad * COALESCE(NULLIF(p.precio_compra, 0), ROUND(vd.precio_unitario * 0.68))) AS ganancia_bruta,
                        SUM(vd.cantidad * vd.precio_unitario) - SUM(vd.cantidad * COALESCE(NULLIF(p.precio_compra, 0), ROUND(vd.precio_unitario * 0.68))) AS ganancia_producto
                    FROM ventas_detalle vd
                    INNER JOIN ventas v ON vd.id_venta = v.id_venta
                    LEFT JOIN productos p ON vd.id_producto = p.id_producto
                    $whereVentas
                    GROUP BY vd.id_producto, p.codigo_barra, p.nombre_prod, p.precio_compra, vd.descripcion
                    ORDER BY ganancia_bruta DESC";
        $stmtProd = $pdo->prepare($sqlProd);
        $stmtProd->execute($paramsV);
        $porProducto = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

        // B. Ganancia por Factura Emitida
        $sqlFact = "SELECT 
                        v.id_venta,
                        v.nro_factura,
                        v.fecha_venta,
                        v.condicion_pago,
                        v.metodo_pago,
                        v.id_sucursal,
                        CONCAT(COALESCE(c.nombre_cliente, 'CLIENTE'), ' ', COALESCE(c.apellido_cliente, '')) AS cliente,
                        COALESCE(c.tipo_cliente, 'MAYORISTA') AS tipo_cliente,
                        SUM(vd.cantidad) AS cant_items,
                        SUM(vd.cantidad * vd.precio_unitario) AS total_venta,
                        SUM(vd.cantidad * COALESCE(NULLIF(p.precio_compra, 0), ROUND(vd.precio_unitario * 0.68))) AS costo_mercaderia,
                        SUM(vd.cantidad * COALESCE(NULLIF(p.precio_compra, 0), ROUND(vd.precio_unitario * 0.68))) AS costo_factura,
                        SUM(vd.cantidad * vd.precio_unitario) - SUM(vd.cantidad * COALESCE(NULLIF(p.precio_compra, 0), ROUND(vd.precio_unitario * 0.68))) AS ganancia_factura
                    FROM ventas v
                    INNER JOIN ventas_detalle vd ON v.id_venta = vd.id_venta
                    LEFT JOIN productos p ON vd.id_producto = p.id_producto
                    LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                    $whereVentas
                    GROUP BY v.id_venta, v.nro_factura, v.fecha_venta, v.condicion_pago, v.metodo_pago, v.id_sucursal, c.nombre_cliente, c.apellido_cliente, c.tipo_cliente
                    ORDER BY v.id_venta DESC
                    LIMIT 100";
        $stmtFact = $pdo->prepare($sqlFact);
        $stmtFact->execute($paramsV);
        $porFactura = $stmtFact->fetchAll(PDO::FETCH_ASSOC);

        // C. Calcular Totales Generales de Ventas y Costos
        $totalIngresosVentas = 0;
        $totalCostoMercaderia = 0;
        $totalUnidadesVendidas = 0;

        foreach ($porProducto as $row) {
            $totalIngresosVentas += (int)$row['total_ingresos'];
            $totalCostoMercaderia += (int)$row['total_costo'];
            $totalUnidadesVendidas += (int)$row['unidades_vendidas'];
        }
        $gananciaBruta = $totalIngresosVentas - $totalCostoMercaderia;
        $margenBrutoPct = ($totalIngresosVentas > 0) ? round(($gananciaBruta / $totalIngresosVentas) * 100, 1) : 0;

        // D. Total de Compras de Mercadería (Inversión en Stock) en el mismo rango
        $whereCompras = " WHERE c.estado = 'ACTIVO'";
        $paramsC = [];
        if (!empty($fechaDesde)) {
            $whereCompras .= " AND c.fecha >= :fd";
            $paramsC[':fd'] = $fechaDesde;
        }
        if (!empty($fechaHasta)) {
            $whereCompras .= " AND c.fecha <= :fh";
            $paramsC[':fh'] = $fechaHasta;
        }
        if ($suc) {
            $whereCompras .= " AND c.id_sucursal = :suc";
            $paramsC[':suc'] = $suc;
        }
        $stmtComp = $pdo->prepare("SELECT COUNT(*) AS cant_compras, COALESCE(SUM(c.total), 0) AS total_compras FROM compra c" . $whereCompras);
        $stmtComp->execute($paramsC);
        $resCompras = $stmtComp->fetch(PDO::FETCH_ASSOC);

        // E. Gastos Operativos de Caja en el mismo rango
        $whereGastos = " WHERE g.estado = 'ACTIVO'";
        $paramsG = [];
        if (!empty($fechaDesde)) {
            $whereGastos .= " AND g.fecha >= :fd";
            $paramsG[':fd'] = $fechaDesde;
        }
        if (!empty($fechaHasta)) {
            $whereGastos .= " AND g.fecha <= :fh";
            $paramsG[':fh'] = $fechaHasta;
        }
        $stmtGastos = $pdo->prepare("SELECT COALESCE(SUM(g.monto), 0) FROM gastos g" . $whereGastos);
        $stmtGastos->execute($paramsG);
        $totalGastos = (int)$stmtGastos->fetchColumn();

        $gananciaNeta = $gananciaBruta - $totalGastos;
        $margenNetoPct = ($totalIngresosVentas > 0) ? round(($gananciaNeta / $totalIngresosVentas) * 100, 1) : 0;

        echo json_encode([
            'status' => 'success',
            'resumen' => [
                'total_ingresos_ventas' => $totalIngresosVentas,
                'ingresos_ventas' => $totalIngresosVentas,
                'total_costo_mercaderia' => $totalCostoMercaderia,
                'costo_mercaderia_vendida' => $totalCostoMercaderia,
                'ganancia_bruta' => $gananciaBruta,
                'margen_bruto_pct' => $margenBrutoPct,
                'total_gastos' => $totalGastos,
                'gastos_operativos_caja' => $totalGastos,
                'ganancia_neta' => $gananciaNeta,
                'margen_neto_pct' => $margenNetoPct,
                'total_unidades_vendidas' => $totalUnidadesVendidas,
                'total_compras_periodo' => (int)$resCompras['total_compras'],
                'cant_compras_periodo' => (int)$resCompras['cant_compras'],
                'cantidad_compras_periodo' => (int)$resCompras['cant_compras'],
                'cant_facturas_venta' => count($porFactura),
                'cantidad_ventas' => count($porFactura)
            ],
            'por_producto' => $porProducto,
            'por_factura' => $porFactura
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.', 'mensaje' => 'Acción no válida.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage(), 'mensaje' => 'Error: ' . $e->getMessage()]);
}
