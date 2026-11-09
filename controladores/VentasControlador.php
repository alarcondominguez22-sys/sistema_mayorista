<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';
$idSucursal = (int)($_SESSION['id_sucursal'] ?? 1);
$idUsuario = (int)($_SESSION['idusuario'] ?? 1);

try {
    if ($accion === 'siguiente_factura') {
        $suc = (int)($_GET['sucursal'] ?? $idSucursal);
        $prefijo = sprintf("001-%03d-", $suc);

        $stmt = $pdo->prepare("SELECT nro_factura FROM ventas WHERE nro_factura LIKE :pref ORDER BY id_venta DESC LIMIT 1");
        $stmt->execute([':pref' => "$prefijo%"]);
        $ultimo = $stmt->fetchColumn();

        $numero = 1;
        if ($ultimo) {
            $partes = explode('-', $ultimo);
            if (isset($partes[2])) {
                $numero = (int)$partes[2] + 1;
            }
        }

        $siguiente = $prefijo . str_pad((string)$numero, 7, '0', STR_PAD_LEFT);
        echo json_encode(['status' => 'success', 'nro_factura' => $siguiente]);
        exit;
    }

    if ($accion === 'buscar_cliente') {
        $q = trim($_GET['q'] ?? '');
        $sql = "SELECT id_cliente, nombre_cliente, apellido_cliente, cedula_cliente, telefono_cliente, direccion_cliente 
                FROM clientes 
                WHERE estado = 'ACTIVO'";
        $params = [];
        if (!empty($q)) {
            $sql .= " AND (nombre_cliente LIKE :q1 OR apellido_cliente LIKE :q2 OR cedula_cliente LIKE :q3 OR telefono_cliente LIKE :q4)";
            $term = "%$q%";
            $params = [':q1' => $term, ':q2' => $term, ':q3' => $term, ':q4' => $term];
        }
        $sql .= " ORDER BY id_cliente DESC LIMIT 15";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $clientes]);
        exit;
    }

    if ($accion === 'estado_credito_cliente') {
        $idCliente = (int)($_GET['id_cliente'] ?? 0);
        if (!$idCliente) {
            echo json_encode(['status' => 'error', 'message' => 'Cliente no especificado']);
            exit;
        }

        $stmtCli = $pdo->prepare("SELECT id_cliente, nombre_cliente, apellido_cliente, cedula_cliente FROM clientes WHERE id_cliente = :cli");
        $stmtCli->execute([':cli' => $idCliente]);
        $cli = $stmtCli->fetch(PDO::FETCH_ASSOC);

        $nombreUpper = strtoupper(trim(($cli['nombre_cliente'] ?? '') . ' ' . ($cli['apellido_cliente'] ?? '')));
        $cedula = trim($cli['cedula_cliente'] ?? '');
        $esConsumidorFinal = ($idCliente == 1 || strpos($nombreUpper, 'CONSUMIDOR') !== false || empty($cedula) || $cedula === '000000');

        // Consultar deudas activas en cuentas__cobrar
        $stmtDeuda = $pdo->prepare("SELECT 
            COUNT(CASE WHEN cc.saldo_pendiente > 0 THEN 1 END) as cant_cuentas,
            COALESCE(SUM(cc.saldo_pendiente), 0) as total_saldo_pendiente,
            COUNT(CASE WHEN cc.fecha_vencimiento < CURDATE() AND cc.saldo_pendiente > 0 THEN 1 END) as cant_vencidas,
            COALESCE(SUM(CASE WHEN cc.fecha_vencimiento < CURDATE() AND cc.saldo_pendiente > 0 THEN cc.saldo_pendiente ELSE 0 END), 0) as total_vencido
            FROM cuentas__cobrar cc
            INNER JOIN ventas v ON cc.id_venta = v.id_venta
            WHERE v.id_cliente = :cli AND cc.estado != 'ANULADO'");
        $stmtDeuda->execute([':cli' => $idCliente]);
        $resumen = $stmtDeuda->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'id_cliente' => $idCliente,
                'es_consumidor_final' => $esConsumidorFinal,
                'total_saldo_pendiente' => (int)$resumen['total_saldo_pendiente'],
                'cant_cuentas' => (int)$resumen['cant_cuentas'],
                'cant_vencidas' => (int)$resumen['cant_vencidas'],
                'total_vencido' => (int)$resumen['total_vencido'],
                'tiene_deuda' => (int)$resumen['total_saldo_pendiente'] > 0,
                'tiene_vencidas' => (int)$resumen['cant_vencidas'] > 0
            ]
        ]);
        exit;
    }

    if ($accion === 'registrar_venta') {
        $payloadRaw = file_get_contents('php://input');
        $data = json_decode($payloadRaw, true);
        if (!$data) {
            $data = $_POST;
        }

        $idCliente = !empty($data['id_cliente']) ? (int)$data['id_cliente'] : null;
        $condicion = $data['condicion_pago'] ?? 'CONTADO';
        $metodoPago = ($condicion === 'CREDITO') ? 'CREDITO' : ($data['metodo_pago'] ?? 'EFECTIVO');
        $metodoEntrega = $data['metodo_entrega'] ?? 'MOSTRADOR';
        $montoDelivery = (int)($data['monto_delivery'] ?? 0);
        $nroFactura = trim($data['nro_factura'] ?? '');
        $sucursal = (int)($data['id_sucursal'] ?? $idSucursal);
        $items = $data['items'] ?? [];

        if (empty($items)) {
            echo json_encode(['status' => 'error', 'message' => 'El carrito de ventas no contiene ningún producto.']);
            exit;
        }

        if (!$idCliente) {
            // Asignar primer cliente (Consumidor Final)
            $firstCl = $pdo->query("SELECT id_cliente FROM clientes LIMIT 1")->fetchColumn();
            $idCliente = $firstCl ?: 1;
        }

        // Si es venta a crédito, validar que el cliente esté debidamente identificado con Cédula/RUC
        if ($condicion === 'CREDITO') {
            $stmtCheckCli = $pdo->prepare("SELECT id_cliente, nombre_cliente, apellido_cliente, cedula_cliente FROM clientes WHERE id_cliente = :cli");
            $stmtCheckCli->execute([':cli' => $idCliente]);
            $cliInfo = $stmtCheckCli->fetch(PDO::FETCH_ASSOC);

            $nomUpper = strtoupper(trim(($cliInfo['nombre_cliente'] ?? '') . ' ' . ($cliInfo['apellido_cliente'] ?? '')));
            $docCli = trim($cliInfo['cedula_cliente'] ?? '');

            if ($idCliente == 1 || strpos($nomUpper, 'CONSUMIDOR') !== false || empty($docCli) || $docCli === '000000') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No es posible otorgar una venta a CRÉDITO a Consumidor Final. Debe seleccionar o registrar un cliente con Cédula de Identidad o RUC.'
                ]);
                exit;
            }
        }

        // Buscar caja abierta para esta sucursal
        $stmtCaja = $pdo->prepare("SELECT id_apertura_cierre_caja FROM apertura_cierre_caja WHERE id_sucursal = :suc AND estado = 'ABIERTA' ORDER BY id_apertura_cierre_caja DESC LIMIT 1");
        $stmtCaja->execute([':suc' => $sucursal]);
        $idCaja = $stmtCaja->fetchColumn();

        if (!$idCaja) {
            // Si no hay caja abierta, tomamos la última o null para no bloquear en caso de pruebas
            $stmtUltima = $pdo->prepare("SELECT id_apertura_cierre_caja FROM apertura_cierre_caja ORDER BY id_apertura_cierre_caja DESC LIMIT 1");
            $stmtUltima->execute();
            $idCaja = $stmtUltima->fetchColumn() ?: 1;
        }

        // Generar número de factura si no viene
        if (empty($nroFactura)) {
            $prefijo = sprintf("001-%03d-", $sucursal);
            $stmtUlt = $pdo->prepare("SELECT nro_factura FROM ventas WHERE nro_factura LIKE :pref ORDER BY id_venta DESC LIMIT 1");
            $stmtUlt->execute([':pref' => "$prefijo%"]);
            $ultFac = $stmtUlt->fetchColumn();
            $nextNum = 1;
            if ($ultFac) {
                $p = explode('-', $ultFac);
                if (isset($p[2])) $nextNum = (int)$p[2] + 1;
            }
            $nroFactura = $prefijo . str_pad((string)$nextNum, 7, '0', STR_PAD_LEFT);
        }

        // Iniciar Transacción
        $pdo->beginTransaction();

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (int)$item['cantidad'] * (int)$item['precio_unitario'];
        }
        $totalVenta = $subtotal + $montoDelivery;

        // Procesar modalidad de pago CONTADO (SIMPLE o MULTIPLE)
        $tipoPagoContado = strtoupper(trim($data['tipo_pago_contado'] ?? 'SIMPLE'));
        $pagosDetalleArray = [];

        if ($condicion === 'CONTADO') {
            if ($tipoPagoContado === 'MULTIPLE') {
                $pm = $data['pagos_multiple'] ?? [];
                $montoEf = max(0, (int)($pm['efectivo'] ?? 0));
                $montoTr = max(0, (int)($pm['transferencia'] ?? 0));
                $montoQr = max(0, (int)($pm['qr'] ?? 0));
                $sumaMultiple = $montoEf + $montoTr + $montoQr;

                if ($sumaMultiple !== $totalVenta) {
                    $pdo->rollBack();
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'En Pago Múltiple, la suma de Efectivo + Transferencia + QR (' . formatoMoneda($sumaMultiple) . ') debe coincidir con el Total de la Factura (' . formatoMoneda($totalVenta) . ').'
                    ]);
                    exit;
                }

                if ($montoEf > 0) $pagosDetalleArray[] = ['metodo' => 'EFECTIVO', 'monto' => $montoEf];
                if ($montoTr > 0) $pagosDetalleArray[] = ['metodo' => 'TRANSFERENCIA', 'monto' => $montoTr];
                if ($montoQr > 0) $pagosDetalleArray[] = ['metodo' => 'QR', 'monto' => $montoQr];

                if (count($pagosDetalleArray) === 1) {
                    $metodoPago = $pagosDetalleArray[0]['metodo'];
                } else {
                    $metodoPago = 'MULTIPLE';
                }
            } else {
                $metodoPago = strtoupper(trim($data['metodo_pago'] ?? 'EFECTIVO'));
                if (!in_array($metodoPago, ['EFECTIVO', 'TRANSFERENCIA', 'QR', 'TARJETA'], true)) {
                    $metodoPago = 'EFECTIVO';
                }
                $pagosDetalleArray[] = ['metodo' => $metodoPago, 'monto' => $totalVenta];
            }
        } else {
            $metodoPago = 'CREDITO';
        }

        $pagosVentaJson = json_encode($pagosDetalleArray, JSON_UNESCAPED_UNICODE);

        // 1. Insertar Cabecera de Venta
        $stmtVenta = $pdo->prepare("INSERT INTO ventas 
            (id_apertura_cierre_caja, id_cliente, fecha_venta, nro_factura, condicion_pago, total_venta, 
             estado, metodo_entrega, monto_delivery, metodo_pago, id_sucursal, idusuario, pagos_json) 
            VALUES (:caja, :cli, CURDATE(), :nro, :cond, :tot, 'CONFIRMADO', :ent, :del, :met, :suc, :usr, :pjson)");
        
        $stmtVenta->execute([
            ':caja' => $idCaja,
            ':cli' => $idCliente,
            ':nro' => $nroFactura,
            ':cond' => $condicion,
            ':tot' => $totalVenta,
            ':ent' => $metodoEntrega,
            ':del' => $montoDelivery,
            ':met' => $metodoPago,
            ':suc' => $sucursal,
            ':usr' => $idUsuario,
            ':pjson' => $pagosVentaJson
        ]);

        // Si es CONTADO, actualizar ingresos en apertura_cierre_caja según los métodos utilizados
        if ($condicion === 'CONTADO' && $idCaja) {
            foreach ($pagosDetalleArray as $pItem) {
                $colCajaCont = 'ingreso_efectivo';
                if ($pItem['metodo'] === 'TRANSFERENCIA') $colCajaCont = 'ingreso_transferencia';
                elseif ($pItem['metodo'] === 'QR') $colCajaCont = 'ingreso_qr';
                elseif ($pItem['metodo'] === 'TARJETA') $colCajaCont = 'ingreso_tarjeta';

                $stmtUpdCajaCont = $pdo->prepare("UPDATE apertura_cierre_caja SET $colCajaCont = $colCajaCont + :m WHERE id_apertura_cierre_caja = :caja");
                $stmtUpdCajaCont->execute([':m' => (int)$pItem['monto'], ':caja' => $idCaja]);
            }
        }

        $idVenta = (int)$pdo->lastInsertId();

        // 2. Insertar Detalles de Venta y Descontar Stock
        $stmtDetalle = $pdo->prepare("INSERT INTO ventas_detalle 
            (id_venta, id_producto, descripcion, cantidad, precio_unitario, iva_10, iva_5, exenta) 
            VALUES (:idv, :idp, :des, :cant, :pre, :i10, :i5, :exe)");

        $colStock = ($sucursal == 2) ? 'stock_sucursal2' : 'stock_sucursal1';

        $stmtStock = $pdo->prepare("UPDATE productos SET 
            stock_actual = GREATEST(0, stock_actual - :c1),
            $colStock = GREATEST(0, $colStock - :c2)
            WHERE id_producto = :idp");

        foreach ($items as $it) {
            $idProducto = (int)$it['id_producto'];
            $cant = (int)$it['cantidad'];
            $precio = (int)$it['precio_unitario'];
            $tipoImp = (int)($it['id_tipo_impuesto'] ?? 1);
            $nombreProd = trim($it['descripcion'] ?? $it['nombre_prod'] ?? "Producto #$idProducto");

            // Validar stock disponible en la sucursal activa antes de facturar
            $stmtCheckStk = $pdo->prepare("SELECT $colStock FROM productos WHERE id_producto = :idp");
            $stmtCheckStk->execute([':idp' => $idProducto]);
            $stkActual = (int)$stmtCheckStk->fetchColumn();
            if ($stkActual < $cant) {
                throw new Exception("El producto '$nombreProd' no cuenta con suficiente stock en esta sucursal (Disponible: $stkActual un., Solicitado: $cant un.).");
            }

            $lineTotal = $cant * $precio;
            $iva10 = 0;
            $iva5 = 0;
            $exenta = 0;

            if ($tipoImp === 1) { // IVA 10%
                $iva10 = $lineTotal;
            } else if ($tipoImp === 2) { // IVA 5%
                $iva5 = $lineTotal;
            } else { // Exenta
                $exenta = $lineTotal;
            }

            $stmtDetalle->execute([
                ':idv' => $idVenta,
                ':idp' => $idProducto,
                ':des' => $nombreProd,
                ':cant' => $cant,
                ':pre' => $precio,
                ':i10' => $iva10,
                ':i5' => $iva5,
                ':exe' => $exenta
            ]);

            // Descontar inventario
            $stmtStock->execute([
                ':c1' => $cant,
                ':c2' => $cant,
                ':idp' => $idProducto
            ]);
        }

        // Si la condición es CREDITO, registrar en cuentas__cobrar y tabla cuotas
        $cuotasGeneradas = [];
        $infoCreditoResponse = null;

        if ($condicion === 'CREDITO') {
            $cantCuotas = max(1, min(24, (int)($data['cantidad_cuotas'] ?? 1)));
            $plazoDias = max(1, (int)($data['plazo_dias'] ?? 30));
            $entregaInicial = max(0, min($totalVenta, (int)($data['entrega_inicial'] ?? 0)));
            $metodoEntregaInicial = strtoupper(trim($data['metodo_entrega_inicial'] ?? 'EFECTIVO'));
            $primerVenc = !empty($data['fecha_primer_vencimiento']) ? trim($data['fecha_primer_vencimiento']) : date('Y-m-d', strtotime("+$plazoDias days"));

            if ($entregaInicial >= $totalVenta) {
                throw new Exception("La entrega inicial no puede ser igual o mayor al total de la venta. Si paga el 100%, la condición debe ser CONTADO.");
            }

            $saldoAFinanciar = $totalVenta - $entregaInicial;
            $montoPorCuotaBase = floor($saldoAFinanciar / $cantCuotas);
            $diferenciaResto = $saldoAFinanciar - ($montoPorCuotaBase * $cantCuotas);

            // Calcular fecha del último vencimiento
            $fechaUltimoVenc = $primerVenc;
            if ($cantCuotas > 1) {
                $fechaUltimoVenc = date('Y-m-d', strtotime("$primerVenc + " . (($cantCuotas - 1) * $plazoDias) . " days"));
            }

            $pagosJsonInit = [];
            $estadoInicialCC = ($entregaInicial > 0) ? 'PARCIAL' : 'CUOTAS_GENERADA';

            // 1. Insertar Cabecera de Cuenta por Cobrar
            $stmtCobrar = $pdo->prepare("INSERT INTO cuentas__cobrar 
                (id_venta, monto_total, saldo_pendiente, fecha_vencimiento, estado, pagos_json) 
                VALUES (:idv, :tot, :saldo, :venc, :st, '[]')");
            $stmtCobrar->execute([
                ':idv' => $idVenta,
                ':tot' => $totalVenta,
                ':saldo' => $saldoAFinanciar,
                ':venc' => $fechaUltimoVenc,
                ':st' => $estadoInicialCC
            ]);
            $idCuentaCobrar = (int)$pdo->lastInsertId();

            // 2. Si hubo Entrega Inicial, registrar en cobros, impactar Caja Abierta y registrar Cuota 0
            if ($entregaInicial > 0) {
                $stmtCobro = $pdo->prepare("INSERT INTO cobros (id_apertura_cierre_caja, fecha, total_cobrado, idusuario) 
                                            VALUES (:caja, CURDATE(), :tot, :usr)");
                $stmtCobro->execute([
                    ':caja' => $idCaja,
                    ':tot' => $entregaInicial,
                    ':usr' => $idUsuario
                ]);
                $idCobroInicial = (int)$pdo->lastInsertId();

                $stmtCobroDet = $pdo->prepare("INSERT INTO cobros_detalle (id_cuenta_cobrar, id_cobro, metodos_pago, monto_pagado, forma_pago) 
                                              VALUES (:idc, :idcob, 1, :monto, :forma)");
                $stmtCobroDet->execute([
                    ':idc' => $idCuentaCobrar,
                    ':idcob' => $idCobroInicial,
                    ':monto' => $entregaInicial,
                    ':forma' => $metodoEntregaInicial
                ]);

                // Insertar Cuota 0 en tabla cuotas (Entrega Inicial Pagada)
                $stmtCuota0 = $pdo->prepare("INSERT INTO cuotas 
                    (id_cuenta_cobrar, numero_cuota, monto_cuota, monto_pagado, saldo, fecha_vencimiento, estado) 
                    VALUES (:idc, 0, :monto, :pagado, 0, CURDATE(), 'PAGADA')");
                $stmtCuota0->execute([
                    ':idc' => $idCuentaCobrar,
                    ':monto' => $entregaInicial,
                    ':pagado' => $entregaInicial
                ]);

                // Actualizar pagos_json en cuentas__cobrar
                $pagosJsonInit[] = [
                    'id_cobro' => $idCobroInicial,
                    'nro_recibo' => sprintf("ANT-%06d", $idCobroInicial),
                    'fecha_hora' => date('Y-m-d H:i:s'),
                    'monto' => $entregaInicial,
                    'metodo_pago' => $metodoEntregaInicial,
                    'referencia' => 'Entrega Inicial / Anticipo al facturar',
                    'usuario' => $_SESSION['nombre_usuario'] ?? 'Administrador',
                    'saldo_anterior' => $totalVenta,
                    'saldo_restante' => $saldoAFinanciar
                ];

                $stmtUpdCC = $pdo->prepare("UPDATE cuentas__cobrar SET pagos_json = :pj WHERE id_cuenta_cobrar = :idc");
                $stmtUpdCC->execute([
                    ':pj' => json_encode($pagosJsonInit, JSON_UNESCAPED_UNICODE),
                    ':idc' => $idCuentaCobrar
                ]);

                // Sumar ingreso en caja abierta según método
                $colCaja = 'ingreso_efectivo';
                if ($metodoEntregaInicial === 'TRANSFERENCIA') $colCaja = 'ingreso_transferencia';
                elseif ($metodoEntregaInicial === 'QR') $colCaja = 'ingreso_qr';
                elseif ($metodoEntregaInicial === 'TARJETA') $colCaja = 'ingreso_tarjeta';

                $stmtUpdCaja = $pdo->prepare("UPDATE apertura_cierre_caja SET $colCaja = $colCaja + :m WHERE id_apertura_cierre_caja = :caja");
                $stmtUpdCaja->execute([':m' => $entregaInicial, ':caja' => $idCaja]);
            }

            // 3. Insertar las cuotas pactadas en la tabla `cuotas`
            $stmtInsertCuota = $pdo->prepare("INSERT INTO cuotas 
                (id_cuenta_cobrar, numero_cuota, monto_cuota, monto_pagado, saldo, fecha_vencimiento, estado) 
                VALUES (:idc, :num, :monto, 0, :saldo, :venc, 'PENDIENTE')");

            for ($i = 1; $i <= $cantCuotas; $i++) {
                $montoCuotaActual = $montoPorCuotaBase;
                if ($i === 1 && $diferenciaResto > 0) {
                    $montoCuotaActual += $diferenciaResto;
                }

                $fechaVencCuota = date('Y-m-d', strtotime("$primerVenc + " . (($i - 1) * $plazoDias) . " days"));

                $stmtInsertCuota->execute([
                    ':idc' => $idCuentaCobrar,
                    ':num' => $i,
                    ':monto' => $montoCuotaActual,
                    ':saldo' => $montoCuotaActual,
                    ':venc' => $fechaVencCuota
                ]);

                $cuotasGeneradas[] = [
                    'numero' => $i,
                    'monto' => $montoCuotaActual,
                    'monto_formateado' => formatoMoneda($montoCuotaActual),
                    'vencimiento' => date('d/m/Y', strtotime($fechaVencCuota))
                ];
            }

            $infoCreditoResponse = [
                'id_cuenta_cobrar' => $idCuentaCobrar,
                'total_credito' => $totalVenta,
                'entrega_inicial' => $entregaInicial,
                'saldo_a_financiar' => $saldoAFinanciar,
                'cantidad_cuotas' => $cantCuotas,
                'plazo_dias' => $plazoDias,
                'primer_vencimiento' => date('d/m/Y', strtotime($primerVenc)),
                'ultimo_vencimiento' => date('d/m/Y', strtotime($fechaUltimoVenc)),
                'cuotas' => $cuotasGeneradas
            ];
        }

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => ($condicion === 'CREDITO') ? 'Venta a CRÉDITO procesada y plan de cuotas registrado correctamente.' : 'Venta procesada con éxito.',
            'id_venta' => $idVenta,
            'nro_factura' => $nroFactura,
            'total_venta' => $totalVenta,
            'condicion_pago' => $condicion,
            'metodo_pago' => $metodoPago,
            'pagos_detalle' => $pagosDetalleArray,
            'credito' => $infoCreditoResponse
        ]);
        exit;
    }

    if ($accion === 'listar') {
        $buscar = trim($_GET['buscar'] ?? '');
        $fechaDesde = $_GET['fecha_desde'] ?? '';
        $fechaHasta = $_GET['fecha_hasta'] ?? '';
        $suc = !empty($_GET['sucursal']) ? (int)$_GET['sucursal'] : null;

        $sql = "SELECT v.id_venta, v.fecha_venta, v.nro_factura, v.condicion_pago, v.total_venta, 
                       v.estado, v.metodo_pago, v.pagos_json, v.metodo_entrega, v.id_sucursal,
                       c.nombre_cliente, c.apellido_cliente, c.cedula_cliente,
                       COALESCE(s.nombre_sucursakl, CONCAT('Sucursal ', v.id_sucursal)) as sucursal_nombre
                FROM ventas v
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                LEFT JOIN sucursal s ON v.id_sucursal = s.id_sucursal
                WHERE 1=1";
        
        $params = [];
        if (!empty($buscar)) {
            $sql .= " AND (v.nro_factura LIKE :b1 OR c.nombre_cliente LIKE :b2 OR c.apellido_cliente LIKE :b3 OR c.cedula_cliente LIKE :b4)";
            $term = "%$buscar%";
            $params[':b1'] = $term;
            $params[':b2'] = $term;
            $params[':b3'] = $term;
            $params[':b4'] = $term;
        }

        if (!empty($fechaDesde)) {
            $sql .= " AND v.fecha_venta >= :fd";
            $params[':fd'] = $fechaDesde;
        }

        if (!empty($fechaHasta)) {
            $sql .= " AND v.fecha_venta <= :fh";
            $params[':fh'] = $fechaHasta;
        }

        if ($suc) {
            $sql .= " AND v.id_sucursal = :suc";
            $params[':suc'] = $suc;
        }

        $sql .= " ORDER BY v.id_venta DESC LIMIT 100";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $ventas, 'total' => count($ventas)]);
        exit;
    }

    if ($accion === 'obtener_detalle') {
        $idVenta = (int)($_GET['id_venta'] ?? 0);

        // Cabecera
        $stmtCab = $pdo->prepare("SELECT v.*, c.nombre_cliente, c.apellido_cliente, c.cedula_cliente, c.telefono_cliente, c.direccion_cliente,
                                         COALESCE(s.nombre_sucursakl, CONCAT('Sucursal ', v.id_sucursal)) as sucursal_nombre, 
                                         s.direccion_sucursal as sucursal_direccion
                                  FROM ventas v
                                  LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                                  LEFT JOIN sucursal s ON v.id_sucursal = s.id_sucursal
                                  WHERE v.id_venta = :id");
        $stmtCab->execute([':id' => $idVenta]);
        $cabecera = $stmtCab->fetch(PDO::FETCH_ASSOC);

        if (!$cabecera) {
            echo json_encode(['status' => 'error', 'message' => 'Venta no encontrada.']);
            exit;
        }

        // Detalles
        $stmtDet = $pdo->prepare("SELECT vd.*, p.nombre_prod, p.codigo_barra
                                  FROM ventas_detalle vd
                                  LEFT JOIN productos p ON vd.id_producto = p.id_producto
                                  WHERE vd.id_venta = :id");
        $stmtDet->execute([':id' => $idVenta]);
        $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        // Si es a crédito, recuperar el plan de cuotas y saldos de cuentas__cobrar
        $credito = null;
        if (strtoupper($cabecera['condicion_pago'] ?? '') === 'CREDITO') {
            $stmtCC = $pdo->prepare("SELECT * FROM cuentas__cobrar WHERE id_venta = :id ORDER BY id_cuenta_cobrar DESC LIMIT 1");
            $stmtCC->execute([':id' => $idVenta]);
            $cc = $stmtCC->fetch(PDO::FETCH_ASSOC);
            if ($cc) {
                $stmtCuo = $pdo->prepare("SELECT * FROM cuotas WHERE id_cuenta_cobrar = :idc ORDER BY numero_cuota ASC");
                $stmtCuo->execute([':idc' => $cc['id_cuenta_cobrar']]);
                $cuotas = $stmtCuo->fetchAll(PDO::FETCH_ASSOC);

                $totalCred = (int)$cc['monto_total'];
                $saldoFinanc = (int)$cc['saldo_pendiente'];
                $entregaInit = max(0, $totalCred - $saldoFinanc);

                $credito = [
                    'id_cuenta_cobrar' => (int)$cc['id_cuenta_cobrar'],
                    'monto_total' => $totalCred,
                    'saldo_pendiente' => $saldoFinanc,
                    'saldo_a_financiar' => $saldoFinanc,
                    'entrega_inicial' => $entregaInit,
                    'fecha_vencimiento' => $cc['fecha_vencimiento'],
                    'estado' => $cc['estado'],
                    'cantidad_cuotas' => count($cuotas),
                    'cuotas' => $cuotas
                ];
            }
        }

        echo json_encode([
            'status' => 'success',
            'cabecera' => $cabecera,
            'detalles' => $detalles,
            'credito' => $credito
        ]);
        exit;
    }

    if ($accion === 'anular_venta') {
        $idVenta = (int)($_POST['id_venta'] ?? 0);

        $stmtCab = $pdo->prepare("SELECT estado, id_sucursal FROM ventas WHERE id_venta = :id");
        $stmtCab->execute([':id' => $idVenta]);
        $venta = $stmtCab->fetch(PDO::FETCH_ASSOC);

        if (!$venta) {
            echo json_encode(['status' => 'error', 'message' => 'Venta no encontrada.']);
            exit;
        }

        if (in_array(strtoupper($venta['estado']), ['ANULADO', 'ANULADA'], true)) {
            echo json_encode(['status' => 'error', 'message' => 'Esta factura ya se encuentra anulada.']);
            exit;
        }

        $pdo->beginTransaction();

        // 1. Revertir stock
        $stmtDet = $pdo->prepare("SELECT id_producto, cantidad FROM ventas_detalle WHERE id_venta = :id");
        $stmtDet->execute([':id' => $idVenta]);
        $items = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        $colStock = ($venta['id_sucursal'] == 2) ? 'stock_sucursal2' : 'stock_sucursal1';
        $stmtStockRevert = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual + :c1, $colStock = $colStock + :c2 WHERE id_producto = :idp");

        foreach ($items as $it) {
            $stmtStockRevert->execute([
                ':c1' => (int)$it['cantidad'],
                ':c2' => (int)$it['cantidad'],
                ':idp' => (int)$it['id_producto']
            ]);
        }

        // 2. Marcar venta como ANULADA
        $stmtAnular = $pdo->prepare("UPDATE ventas SET estado = 'ANULADO' WHERE id_venta = :id");
        $stmtAnular->execute([':id' => $idVenta]);

        // Si tenía cuenta a cobrar, anularla y cancelar sus cuotas
        $stmtAnularCC = $pdo->prepare("UPDATE cuentas__cobrar SET estado = 'ANULADO', saldo_pendiente = 0 WHERE id_venta = :id");
        $stmtAnularCC->execute([':id' => $idVenta]);

        $stmtAnularCuotas = $pdo->prepare("UPDATE cuotas SET estado = 'ANULADO', saldo = 0 WHERE id_cuenta_cobrar IN (SELECT id_cuenta_cobrar FROM cuentas__cobrar WHERE id_venta = :id)");
        $stmtAnularCuotas->execute([':id' => $idVenta]);

        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => "Venta #$idVenta anulada con éxito y stock repuesto en catálogo."]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
