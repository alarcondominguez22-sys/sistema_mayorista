<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';
$idSucursal = (int)($_SESSION['id_sucursal'] ?? 1);
$idUsuario = (int)($_SESSION['idusuario'] ?? 1);

try {
    // 1. Resumen de Métricas de Créditos
    if ($accion === 'resumen_metricas') {
        $suc = !empty($_GET['sucursal']) ? (int)$_GET['sucursal'] : null;

        // Base where condition
        $sucWhere = "";
        $params = [];
        if ($suc) {
            $sucWhere = " AND v.id_sucursal = :suc";
            $params[':suc'] = $suc;
        }

        // Saldo total pendiente de cobro y cantidad de cuentas activas
        $sqlPendiente = "SELECT 
            COALESCE(SUM(cc.saldo_pendiente), 0) as total_pendiente,
            COUNT(CASE WHEN cc.saldo_pendiente > 0 THEN 1 END) as cant_pendientes,
            COALESCE(SUM(CASE WHEN cc.fecha_vencimiento < CURDATE() AND cc.saldo_pendiente > 0 THEN cc.saldo_pendiente ELSE 0 END), 0) as total_vencido,
            COUNT(CASE WHEN cc.fecha_vencimiento < CURDATE() AND cc.saldo_pendiente > 0 THEN 1 END) as cant_vencidas
            FROM cuentas__cobrar cc
            LEFT JOIN ventas v ON cc.id_venta = v.id_venta
            WHERE cc.estado != 'ANULADO' $sucWhere";
        $stmtPend = $pdo->prepare($sqlPendiente);
        $stmtPend->execute($params);
        $resumen = $stmtPend->fetch(PDO::FETCH_ASSOC);

        // Total cobrado en el mes en curso
        $sqlMes = "SELECT COALESCE(SUM(c.total_cobrado), 0) as total_cobrado_mes, COUNT(c.id_cobro) as cant_cobros_mes 
                   FROM cobros c 
                   LEFT JOIN apertura_cierre_caja a ON c.id_apertura_cierre_caja = a.id_apertura_cierre_caja
                   WHERE MONTH(c.fecha) = MONTH(CURDATE()) AND YEAR(c.fecha) = YEAR(CURDATE())";
        if ($suc) {
            $sqlMes .= " AND a.id_sucursal = :suc";
            $stmtMes = $pdo->prepare($sqlMes);
            $stmtMes->execute([':suc' => $suc]);
        } else {
            $stmtMes = $pdo->prepare($sqlMes);
            $stmtMes->execute();
        }
        $mes = $stmtMes->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_pendiente' => (int)$resumen['total_pendiente'],
                'cant_pendientes' => (int)$resumen['cant_pendientes'],
                'total_vencido' => (int)$resumen['total_vencido'],
                'cant_vencidas' => (int)$resumen['cant_vencidas'],
                'total_cobrado_mes' => (int)$mes['total_cobrado_mes'],
                'cant_cobros_mes' => (int)$mes['cant_cobros_mes']
            ]
        ]);
        exit;
    }

    // 2. Listado de Cuentas por Cobrar con Filtros
    if ($accion === 'listar') {
        $buscar = trim($_GET['buscar'] ?? '');
        $estado = trim($_GET['estado'] ?? '');
        $suc = !empty($_GET['sucursal']) ? (int)$_GET['sucursal'] : null;
        $fechaDesde = trim($_GET['fecha_desde'] ?? '');
        $fechaHasta = trim($_GET['fecha_hasta'] ?? '');

        $sql = "SELECT cc.id_cuenta_cobrar, cc.id_venta, cc.monto_total, cc.saldo_pendiente, 
                       cc.fecha_vencimiento, cc.estado, cc.pagos_json,
                       v.nro_factura, v.fecha_venta, v.total_venta, v.id_sucursal,
                       c.id_cliente, c.nombre_cliente, c.apellido_cliente, c.cedula_cliente, c.telefono_cliente,
                       COALESCE(s.nombre_sucursakl, CONCAT('Sucursal ', v.id_sucursal)) as sucursal_nombre,
                       DATEDIFF(cc.fecha_vencimiento, CURDATE()) as dias_para_vencer
                FROM cuentas__cobrar cc
                LEFT JOIN ventas v ON cc.id_venta = v.id_venta
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                LEFT JOIN sucursal s ON v.id_sucursal = s.id_sucursal
                WHERE cc.estado != 'ANULADO'";
        
        $params = [];

        if (!empty($buscar)) {
            $sql .= " AND (v.nro_factura LIKE :b1 OR c.nombre_cliente LIKE :b2 OR c.apellido_cliente LIKE :b3 OR c.cedula_cliente LIKE :b4)";
            $term = "%$buscar%";
            $params[':b1'] = $term;
            $params[':b2'] = $term;
            $params[':b3'] = $term;
            $params[':b4'] = $term;
        }

        if ($suc) {
            $sql .= " AND v.id_sucursal = :suc";
            $params[':suc'] = $suc;
        }

        if (!empty($fechaDesde)) {
            $sql .= " AND v.fecha_venta >= :fd";
            $params[':fd'] = $fechaDesde;
        }

        if (!empty($fechaHasta)) {
            $sql .= " AND v.fecha_venta <= :fh";
            $params[':fh'] = $fechaHasta;
        }

        // Filtro de Estado inteligente
        if ($estado === 'PENDIENTE') {
            $sql .= " AND cc.saldo_pendiente > 0";
        } elseif ($estado === 'PARCIAL') {
            $sql .= " AND cc.saldo_pendiente > 0 AND cc.saldo_pendiente < cc.monto_total";
        } elseif ($estado === 'VENCIDO') {
            $sql .= " AND cc.saldo_pendiente > 0 AND cc.fecha_vencimiento < CURDATE()";
        } elseif ($estado === 'PAGADA' || $estado === 'CANCELADO') {
            $sql .= " AND (cc.saldo_pendiente = 0 OR cc.estado IN ('PAGADA', 'CANCELADO'))";
        }

        $sql .= " ORDER BY (cc.saldo_pendiente > 0) DESC, cc.fecha_vencimiento ASC, cc.id_cuenta_cobrar DESC LIMIT 100";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Procesar estado calculado y formateos
        foreach ($cuentas as &$cta) {
            $cta['monto_total'] = (int)$cta['monto_total'];
            $cta['saldo_pendiente'] = (int)$cta['saldo_pendiente'];
            $cta['total_pagado'] = $cta['monto_total'] - $cta['saldo_pendiente'];
            $dias = (int)$cta['dias_para_vencer'];

            if ($cta['saldo_pendiente'] <= 0) {
                $cta['estado_display'] = 'PAGADA';
                $cta['badge_class'] = 'badge-soft-success';
                $cta['estado_texto'] = 'Cancelada';
            } elseif ($dias < 0) {
                $cta['estado_display'] = 'VENCIDO';
                $cta['badge_class'] = 'badge-soft-danger';
                $cta['estado_texto'] = 'Vencida (' . abs($dias) . ' d.)';
            } elseif ($cta['saldo_pendiente'] < $cta['monto_total']) {
                $cta['estado_display'] = 'PARCIAL';
                $cta['badge_class'] = 'badge-soft-warning';
                $cta['estado_texto'] = 'Pago Parcial';
            } else {
                $cta['estado_display'] = 'PENDIENTE';
                $cta['badge_class'] = 'badge-soft-primary';
                $cta['estado_texto'] = 'Pendiente';
            }
        }
        unset($cta);

        echo json_encode(['status' => 'success', 'data' => $cuentas]);
        exit;
    }

    // 3. Obtener detalle completo de una cuenta a cobrar
    if ($accion === 'obtener_detalle') {
        $idCuenta = (int)($_GET['id_cuenta_cobrar'] ?? 0);
        if (!$idCuenta) {
            echo json_encode(['status' => 'error', 'message' => 'ID de cuenta no proporcionado.']);
            exit;
        }

        $sql = "SELECT cc.*, v.nro_factura, v.fecha_venta, v.total_venta, v.id_sucursal, v.metodo_pago,
                       c.id_cliente, c.nombre_cliente, c.apellido_cliente, c.cedula_cliente, c.telefono_cliente, c.direccion_cliente,
                       COALESCE(s.nombre_sucursakl, CONCAT('Sucursal ', v.id_sucursal)) as sucursal_nombre
                FROM cuentas__cobrar cc
                LEFT JOIN ventas v ON cc.id_venta = v.id_venta
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                LEFT JOIN sucursal s ON v.id_sucursal = s.id_sucursal
                WHERE cc.id_cuenta_cobrar = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $idCuenta]);
        $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cuenta) {
            echo json_encode(['status' => 'error', 'message' => 'Cuenta a cobrar no encontrada.']);
            exit;
        }

        // Obtener productos de la venta
        $stmtDetalle = $pdo->prepare("SELECT descripcion, cantidad, precio_unitario, (cantidad * precio_unitario) as total 
                                      FROM ventas_detalle WHERE id_venta = :idv");
        $stmtDetalle->execute([':idv' => $cuenta['id_venta']]);
        $items = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

        // Obtener cuotas si existen
        $stmtCuotas = $pdo->prepare("SELECT id_cuota, numero_cuota, monto_cuota, monto_pagado, saldo, fecha_vencimiento, estado 
                                     FROM cuotas WHERE id_cuenta_cobrar = :idc ORDER BY numero_cuota ASC");
        $stmtCuotas->execute([':idc' => $idCuenta]);
        $cuotas = $stmtCuotas->fetchAll(PDO::FETCH_ASSOC);

        // Obtener historial de pagos desde cobros y cobros_detalle
        $stmtPagos = $pdo->prepare("SELECT c.id_cobro, c.fecha, c.total_cobrado, cd.monto_pagado, cd.forma_pago, c.idusuario
                                    FROM cobros_detalle cd
                                    INNER JOIN cobros c ON cd.id_cobro = c.id_cobro
                                    WHERE cd.id_cuenta_cobrar = :idc
                                    ORDER BY c.id_cobro DESC");
        $stmtPagos->execute([':idc' => $idCuenta]);
        $cobrosHistorico = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

        // Pagos almacenados en JSON si los hay
        $pagosJson = [];
        if (!empty($cuenta['pagos_json'])) {
            $decoded = json_decode($cuenta['pagos_json'], true);
            if (is_array($decoded)) {
                $pagosJson = $decoded;
            }
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'cuenta' => $cuenta,
                'items' => $items,
                'cuotas' => $cuotas,
                'cobros' => $cobrosHistorico,
                'pagos_json' => $pagosJson
            ]
        ]);
        exit;
    }

    // 4. Registrar Cobro / Pago de Cuota
    if ($accion === 'registrar_cobro') {
        $payloadRaw = file_get_contents('php://input');
        $data = json_decode($payloadRaw, true);
        if (!$data) {
            $data = $_POST;
        }

        $idCuenta = (int)($data['id_cuenta_cobrar'] ?? 0);
        $montoCobro = (int)($data['monto_cobro'] ?? 0);
        $metodoPago = strtoupper(trim($data['metodo_pago'] ?? 'EFECTIVO'));
        $referencia = trim($data['referencia'] ?? '');
        $observacion = trim($data['observacion'] ?? '');
        $sucursal = (int)($data['id_sucursal'] ?? $idSucursal);

        if (!$idCuenta || $montoCobro <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'El monto del cobro debe ser mayor a 0.']);
            exit;
        }

        // Obtener cuenta actual con bloqueo para asegurar consistencia
        $stmtCuenta = $pdo->prepare("SELECT cc.*, v.nro_factura, v.id_sucursal, c.nombre_cliente, c.apellido_cliente, c.cedula_cliente 
                                     FROM cuentas__cobrar cc 
                                     LEFT JOIN ventas v ON cc.id_venta = v.id_venta
                                     LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                                     WHERE cc.id_cuenta_cobrar = :id");
        $stmtCuenta->execute([':id' => $idCuenta]);
        $cuenta = $stmtCuenta->fetch(PDO::FETCH_ASSOC);

        if (!$cuenta) {
            echo json_encode(['status' => 'error', 'message' => 'Cuenta a cobrar no encontrada.']);
            exit;
        }

        $saldoActual = (int)$cuenta['saldo_pendiente'];
        if ($saldoActual <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Esta cuenta ya se encuentra completamente cancelada.']);
            exit;
        }

        if ($montoCobro > $saldoActual) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'El monto ingresado (' . formatoMoneda($montoCobro) . ') supera el saldo pendiente de la cuenta (' . formatoMoneda($saldoActual) . ').'
            ]);
            exit;
        }

        // Buscar caja abierta para asociar el ingreso
        $stmtCaja = $pdo->prepare("SELECT id_apertura_cierre_caja FROM apertura_cierre_caja 
                                   WHERE id_sucursal = :suc AND estado = 'ABIERTA' 
                                   ORDER BY id_apertura_cierre_caja DESC LIMIT 1");
        $stmtCaja->execute([':suc' => $sucursal]);
        $idCaja = $stmtCaja->fetchColumn();

        if (!$idCaja) {
            // Si no hay caja abierta en esta sucursal, buscar alguna caja abierta o la última
            $stmtCajaAny = $pdo->prepare("SELECT id_apertura_cierre_caja FROM apertura_cierre_caja WHERE estado = 'ABIERTA' ORDER BY id_apertura_cierre_caja DESC LIMIT 1");
            $stmtCajaAny->execute();
            $idCaja = $stmtCajaAny->fetchColumn();

            if (!$idCaja) {
                // Tomar última caja registrada
                $stmtUlt = $pdo->query("SELECT id_apertura_cierre_caja FROM apertura_cierre_caja ORDER BY id_apertura_cierre_caja DESC LIMIT 1");
                $idCaja = $stmtUlt->fetchColumn() ?: 1;
            }
        }

        // INICIAR TRANSACCIÓN ATÓMICA
        $pdo->beginTransaction();

        // 1. Insertar en tabla `cobros`
        $stmtCobro = $pdo->prepare("INSERT INTO cobros (id_apertura_cierre_caja, fecha, total_cobrado, idusuario) 
                                    VALUES (:caja, CURDATE(), :tot, :usr)");
        $stmtCobro->execute([
            ':caja' => $idCaja,
            ':tot' => $montoCobro,
            ':usr' => $idUsuario
        ]);
        $idCobro = (int)$pdo->lastInsertId();

        // 2. Insertar en `cobros_detalle`
        $stmtDetalle = $pdo->prepare("INSERT INTO cobros_detalle (id_cuenta_cobrar, id_cobro, metodos_pago, monto_pagado, forma_pago) 
                                      VALUES (:idc, :idcob, 1, :monto, :forma)");
        $stmtDetalle->execute([
            ':idc' => $idCuenta,
            ':idcob' => $idCobro,
            ':monto' => $montoCobro,
            ':forma' => $metodoPago
        ]);

        // 3. Amortizar cuotas si existen
        $stmtCheckCuotas = $pdo->prepare("SELECT id_cuota, numero_cuota, monto_cuota, monto_pagado, saldo 
                                          FROM cuotas 
                                          WHERE id_cuenta_cobrar = :idc AND estado != 'PAGADA' 
                                          ORDER BY numero_cuota ASC");
        $stmtCheckCuotas->execute([':idc' => $idCuenta]);
        $cuotasPend = $stmtCheckCuotas->fetchAll(PDO::FETCH_ASSOC);

        $montoRestanteAplicar = $montoCobro;
        if (!empty($cuotasPend)) {
            $stmtUpdCuota = $pdo->prepare("UPDATE cuotas SET monto_pagado = :mp, saldo = :sd, estado = :st WHERE id_cuota = :idq");
            foreach ($cuotasPend as $q) {
                if ($montoRestanteAplicar <= 0) break;
                $saldoCuota = (float)$q['saldo'];
                if ($montoRestanteAplicar >= $saldoCuota) {
                    $montoRestanteAplicar -= $saldoCuota;
                    $nuevoPagado = (float)$q['monto_pagado'] + $saldoCuota;
                    $stmtUpdCuota->execute([
                        ':mp' => $nuevoPagado,
                        ':sd' => 0,
                        ':st' => 'PAGADA',
                        ':idq' => $q['id_cuota']
                    ]);
                } else {
                    $nuevoPagado = (float)$q['monto_pagado'] + $montoRestanteAplicar;
                    $nuevoSaldoCuota = $saldoCuota - $montoRestanteAplicar;
                    $montoRestanteAplicar = 0;
                    $stmtUpdCuota->execute([
                        ':mp' => $nuevoPagado,
                        ':sd' => $nuevoSaldoCuota,
                        ':st' => 'PARCIAL',
                        ':idq' => $q['id_cuota']
                    ]);
                }
            }
        }

        // 4. Calcular nuevo saldo de la cuenta
        $nuevoSaldo = max(0, $saldoActual - $montoCobro);
        $nuevoEstado = ($nuevoSaldo === 0) ? 'PAGADA' : 'PARCIAL';

        // 5. Actualizar historial de pagos en JSON
        $pagosArray = [];
        if (!empty($cuenta['pagos_json'])) {
            $dec = json_decode($cuenta['pagos_json'], true);
            if (is_array($dec)) {
                $pagosArray = $dec;
            }
        }

        $nroRecibo = sprintf("REC-%06d", $idCobro);

        $pagosArray[] = [
            'id_cobro' => $idCobro,
            'nro_recibo' => $nroRecibo,
            'fecha_hora' => date('Y-m-d H:i:s'),
            'monto' => $montoCobro,
            'metodo_pago' => $metodoPago,
            'referencia' => $referencia,
            'observacion' => $observacion,
            'id_caja' => $idCaja,
            'usuario' => $_SESSION['nombre_usuario'] ?? 'Administrador',
            'saldo_anterior' => $saldoActual,
            'saldo_restante' => $nuevoSaldo
        ];

        // 6. Actualizar `cuentas__cobrar`
        $stmtUpdCuenta = $pdo->prepare("UPDATE cuentas__cobrar SET 
            saldo_pendiente = :ns,
            estado = :ne,
            pagos_json = :pj
            WHERE id_cuenta_cobrar = :idc");
        $stmtUpdCuenta->execute([
            ':ns' => $nuevoSaldo,
            ':ne' => $nuevoEstado,
            ':pj' => json_encode($pagosArray, JSON_UNESCAPED_UNICODE),
            ':idc' => $idCuenta
        ]);

        // 7. Impactar en `apertura_cierre_caja` según método de pago
        $colCaja = 'ingreso_efectivo';
        if ($metodoPago === 'TRANSFERENCIA') {
            $colCaja = 'ingreso_transferencia';
        } elseif ($metodoPago === 'QR') {
            $colCaja = 'ingreso_qr';
        } elseif ($metodoPago === 'TARJETA') {
            $colCaja = 'ingreso_tarjeta';
        }

        $stmtUpdCaja = $pdo->prepare("UPDATE apertura_cierre_caja SET $colCaja = $colCaja + :m WHERE id_apertura_cierre_caja = :caja");
        $stmtUpdCaja->execute([
            ':m' => $montoCobro,
            ':caja' => $idCaja
        ]);

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Cobro registrado exitosamente por ' . formatoMoneda($montoCobro) . '.',
            'id_cobro' => $idCobro,
            'nro_recibo' => $nroRecibo,
            'monto_cobrado' => $montoCobro,
            'saldo_restante' => $nuevoSaldo,
            'nuevo_estado' => $nuevoEstado,
            'recibo' => [
                'id_cobro' => $idCobro,
                'nro_recibo' => $nroRecibo,
                'fecha' => date('d/m/Y H:i'),
                'cliente' => trim(($cuenta['nombre_cliente'] ?? '') . ' ' . ($cuenta['apellido_cliente'] ?? '')),
                'cedula' => $cuenta['cedula_cliente'] ?? '',
                'nro_factura' => $cuenta['nro_factura'] ?? '',
                'monto' => $montoCobro,
                'monto_formateado' => formatoMoneda($montoCobro),
                'metodo_pago' => $metodoPago,
                'referencia' => $referencia,
                'saldo_anterior' => $saldoActual,
                'saldo_restante' => $nuevoSaldo,
                'cajero' => $_SESSION['nombre_usuario'] ?? 'Administrador',
                'sucursal' => $sucursal
            ]
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
