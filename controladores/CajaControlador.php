<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'estado_actual';
$idSucursal = (int)($_SESSION['id_sucursal'] ?? 1);
$idUsuario = (int)($_SESSION['idusuario'] ?? 1);

try {
    if ($accion === 'estado_actual') {
        $sucursal = (int)($_GET['sucursal'] ?? $idSucursal);

        // Buscar caja abierta para esta sucursal
        $stmt = $pdo->prepare("SELECT * FROM apertura_cierre_caja WHERE id_sucursal = :suc AND estado = 'ABIERTA' ORDER BY id_apertura_cierre_caja DESC LIMIT 1");
        $stmt->execute([':suc' => $sucursal]);
        $caja = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$caja) {
            echo json_encode([
                'status' => 'success',
                'tiene_caja_abierta' => false,
                'sucursal' => $sucursal
            ]);
            exit;
        }

        $idCaja = (int)$caja['id_apertura_cierre_caja'];

        // Sumar ventas asociadas a esta caja abierta
        $stmtVentas = $pdo->prepare("SELECT total_venta, metodo_pago, pagos_json 
            FROM ventas 
            WHERE id_apertura_cierre_caja = :idCaja AND estado IN ('CONFIRMADO', 'FACTURADO')");
        $stmtVentas->execute([':idCaja' => $idCaja]);
        $listaVentasCaja = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);

        $resumenVentas = [
            'cant_ventas' => count($listaVentasCaja),
            'total_ventas' => 0,
            'ventas_efectivo' => 0,
            'ventas_tarjeta' => 0,
            'ventas_qr' => 0,
            'ventas_transferencia' => 0
        ];

        foreach ($listaVentasCaja as $vc) {
            $tv = (int)$vc['total_venta'];
            $resumenVentas['total_ventas'] += $tv;
            $mp = strtoupper(trim($vc['metodo_pago'] ?? 'EFECTIVO'));

            if ($mp === 'MULTIPLE' && !empty($vc['pagos_json'])) {
                $pArr = json_decode($vc['pagos_json'], true);
                if (is_array($pArr)) {
                    foreach ($pArr as $pi) {
                        $mSub = strtoupper(trim($pi['metodo'] ?? ''));
                        $mVal = (int)($pi['monto'] ?? 0);
                        if ($mSub === 'EFECTIVO') $resumenVentas['ventas_efectivo'] += $mVal;
                        elseif ($mSub === 'TRANSFERENCIA') $resumenVentas['ventas_transferencia'] += $mVal;
                        elseif ($mSub === 'QR') $resumenVentas['ventas_qr'] += $mVal;
                        elseif ($mSub === 'TARJETA') $resumenVentas['ventas_tarjeta'] += $mVal;
                    }
                }
            } elseif ($mp === 'EFECTIVO' || $mp === 'SIMPLE') {
                $resumenVentas['ventas_efectivo'] += $tv;
            } elseif ($mp === 'TRANSFERENCIA') {
                $resumenVentas['ventas_transferencia'] += $tv;
            } elseif ($mp === 'QR') {
                $resumenVentas['ventas_qr'] += $tv;
            } elseif ($mp === 'TARJETA') {
                $resumenVentas['ventas_tarjeta'] += $tv;
            }
        }

        // Sumar cobros / recupero de créditos asociados a esta caja abierta
        $stmtCobros = $pdo->prepare("SELECT 
            COUNT(*) as cant_cobros,
            COALESCE(SUM(c.total_cobrado), 0) as total_cobros,
            COALESCE(SUM(CASE WHEN cd.forma_pago = 'EFECTIVO' OR cd.forma_pago = 'SIMPLE' OR cd.forma_pago = 'VARIOS' THEN cd.monto_pagado ELSE 0 END), 0) as cobros_efectivo,
            COALESCE(SUM(CASE WHEN cd.forma_pago = 'TARJETA' THEN cd.monto_pagado ELSE 0 END), 0) as cobros_tarjeta,
            COALESCE(SUM(CASE WHEN cd.forma_pago = 'QR' THEN cd.monto_pagado ELSE 0 END), 0) as cobros_qr,
            COALESCE(SUM(CASE WHEN cd.forma_pago = 'TRANSFERENCIA' THEN cd.monto_pagado ELSE 0 END), 0) as cobros_transferencia
            FROM cobros c
            LEFT JOIN cobros_detalle cd ON c.id_cobro = cd.id_cobro
            WHERE c.id_apertura_cierre_caja = :idCaja");
        $stmtCobros->execute([':idCaja' => $idCaja]);
        $resumenCobros = $stmtCobros->fetch(PDO::FETCH_ASSOC);

        // Sumar gastos / retiros asociados a esta caja
        $stmtGastos = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) as total_gastos FROM gastos WHERE id_apertura_cierre_caja = :idCaja AND estado = 'ACTIVO'");
        $stmtGastos->execute([':idCaja' => $idCaja]);
        $totalGastos = (int)$stmtGastos->fetchColumn();

        // Saldo esperado en efectivo en cajón (Apertura + Ventas Efectivo + Cobros Crédito Efectivo - Gastos)
        $montoApertura = (int)$caja['monto_apertura'];
        $efectivoVentas = (int)$resumenVentas['ventas_efectivo'];
        $efectivoCobros = (int)$resumenCobros['cobros_efectivo'];
        $saldoEfectivoEsperado = $montoApertura + $efectivoVentas + $efectivoCobros - $totalGastos;

        echo json_encode([
            'status' => 'success',
            'tiene_caja_abierta' => true,
            'caja' => $caja,
            'ventas' => $resumenVentas,
            'cobros' => $resumenCobros,
            'total_gastos' => $totalGastos,
            'saldo_efectivo_esperado' => $saldoEfectivoEsperado
        ]);
        exit;
    }

    if ($accion === 'abrir_caja') {
        $montoApertura = (int)($_POST['monto_apertura'] ?? 0);
        $sucursal = (int)($_POST['id_sucursal'] ?? $idSucursal);

        // Verificar que no haya otra caja abierta en la misma sucursal
        $stmtCheck = $pdo->prepare("SELECT id_apertura_cierre_caja FROM apertura_cierre_caja WHERE id_sucursal = :suc AND estado = 'ABIERTA'");
        $stmtCheck->execute([':suc' => $sucursal]);
        if ($stmtCheck->fetchColumn()) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe una caja abierta para esta sucursal. Debe cerrarla antes de abrir una nueva.']);
            exit;
        }

        $fecha = date('Y-m-d');
        $hora = date('H:i:s');

        $stmt = $pdo->prepare("INSERT INTO apertura_cierre_caja 
            (fecha_apertura, monto_apertura, fecha_cierre, ingreso_efectivo, ingreso_cheque, ingreso_tarjeta, 
             monto_cierre, hora_apertura, hora_cierre, idusuario, estado, saldo_caja_vuelto, 
             saldo_transferencia_apertura, saldo_qr_apertura, ingreso_transferencia, ingreso_qr, gastos, id_sucursal) 
            VALUES (:fa, :ma, '1000-01-01', 0, 0, 0, 0, :ha, '00:00:00', :usr, 'ABIERTA', 0, 0, 0, 0, 0, 0, :suc)");
        $stmt->execute([
            ':fa' => $fecha,
            ':ma' => $montoApertura,
            ':ha' => $hora,
            ':usr' => $idUsuario,
            ':suc' => $sucursal
        ]);

        $idNuevaCaja = $pdo->lastInsertId();

        echo json_encode([
            'status' => 'success', 
            'message' => "Caja #$idNuevaCaja abierta exitosamente con " . number_format($montoApertura, 0, ',', '.') . " Gs.",
            'id_caja' => $idNuevaCaja
        ]);
        exit;
    }

    if ($accion === 'cerrar_caja') {
        $idCaja = (int)($_POST['id_apertura_cierre_caja'] ?? 0);
        $montoRealEfectivo = (int)($_POST['monto_real_efectivo'] ?? 0);

        if (!$idCaja) {
            echo json_encode(['status' => 'error', 'message' => 'ID de caja inválido.']);
            exit;
        }

        // Obtener resumen de ventas (incluyendo desglose de MULTIPLE)
        $stmtVentas = $pdo->prepare("SELECT total_venta, metodo_pago, pagos_json 
            FROM ventas 
            WHERE id_apertura_cierre_caja = :idCaja AND estado IN ('CONFIRMADO', 'FACTURADO')");
        $stmtVentas->execute([':idCaja' => $idCaja]);
        $listaVCierre = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);

        $v = [
            'total_ventas' => 0,
            'efectivo' => 0,
            'tarjeta' => 0,
            'qr' => 0,
            'transferencia' => 0
        ];
        foreach ($listaVCierre as $vc) {
            $tv = (int)$vc['total_venta'];
            $v['total_ventas'] += $tv;
            $mp = strtoupper(trim($vc['metodo_pago'] ?? 'EFECTIVO'));
            if ($mp === 'MULTIPLE' && !empty($vc['pagos_json'])) {
                $pArr = json_decode($vc['pagos_json'], true);
                if (is_array($pArr)) {
                    foreach ($pArr as $pi) {
                        $mSub = strtoupper(trim($pi['metodo'] ?? ''));
                        $mVal = (int)($pi['monto'] ?? 0);
                        if ($mSub === 'EFECTIVO') $v['efectivo'] += $mVal;
                        elseif ($mSub === 'TRANSFERENCIA') $v['transferencia'] += $mVal;
                        elseif ($mSub === 'QR') $v['qr'] += $mVal;
                        elseif ($mSub === 'TARJETA') $v['tarjeta'] += $mVal;
                    }
                }
            } elseif ($mp === 'EFECTIVO' || $mp === 'SIMPLE') {
                $v['efectivo'] += $tv;
            } elseif ($mp === 'TRANSFERENCIA') {
                $v['transferencia'] += $tv;
            } elseif ($mp === 'QR') {
                $v['qr'] += $tv;
            } elseif ($mp === 'TARJETA') {
                $v['tarjeta'] += $tv;
            }
        }

        // Sumar cobros en esta caja
        $stmtCobros = $pdo->prepare("SELECT 
            COALESCE(SUM(c.total_cobrado), 0) as total_cobros,
            COALESCE(SUM(CASE WHEN cd.forma_pago = 'EFECTIVO' OR cd.forma_pago = 'SIMPLE' OR cd.forma_pago = 'VARIOS' THEN cd.monto_pagado ELSE 0 END), 0) as efectivo,
            COALESCE(SUM(CASE WHEN cd.forma_pago = 'TARJETA' THEN cd.monto_pagado ELSE 0 END), 0) as tarjeta,
            COALESCE(SUM(CASE WHEN cd.forma_pago = 'QR' THEN cd.monto_pagado ELSE 0 END), 0) as qr,
            COALESCE(SUM(CASE WHEN cd.forma_pago = 'TRANSFERENCIA' THEN cd.monto_pagado ELSE 0 END), 0) as transferencia
            FROM cobros c
            LEFT JOIN cobros_detalle cd ON c.id_cobro = cd.id_cobro
            WHERE c.id_apertura_cierre_caja = :idCaja");
        $stmtCobros->execute([':idCaja' => $idCaja]);
        $cbr = $stmtCobros->fetch(PDO::FETCH_ASSOC);

        $totEfectivo = (int)$v['efectivo'] + (int)$cbr['efectivo'];
        $totTarjeta = (int)$v['tarjeta'] + (int)$cbr['tarjeta'];
        $totQr = (int)$v['qr'] + (int)$cbr['qr'];
        $totTransferencia = (int)$v['transferencia'] + (int)$cbr['transferencia'];

        // Gastos
        $stmtGastos = $pdo->prepare("SELECT COALESCE(SUM(monto), 0) FROM gastos WHERE id_apertura_cierre_caja = :idCaja AND estado = 'ACTIVO'");
        $stmtGastos->execute([':idCaja' => $idCaja]);
        $totalGastos = (int)$stmtGastos->fetchColumn();

        $fechaCierre = date('Y-m-d');
        $horaCierre = date('H:i:s');

        $stmt = $pdo->prepare("UPDATE apertura_cierre_caja SET 
            fecha_cierre = :fc,
            hora_cierre = :hc,
            ingreso_efectivo = :ef,
            ingreso_tarjeta = :tj,
            ingreso_qr = :qr,
            ingreso_transferencia = :tr,
            gastos = :ga,
            monto_cierre = :mc,
            estado = 'CERRADA'
            WHERE id_apertura_cierre_caja = :id");
        $stmt->execute([
            ':fc' => $fechaCierre,
            ':hc' => $horaCierre,
            ':ef' => $totEfectivo,
            ':tj' => $totTarjeta,
            ':qr' => $totQr,
            ':tr' => $totTransferencia,
            ':ga' => $totalGastos,
            ':mc' => $montoRealEfectivo,
            ':id' => $idCaja
        ]);

        echo json_encode(['status' => 'success', 'message' => "Caja #$idCaja cerrada correctamente."]);
        exit;
    }

    if ($accion === 'registrar_gasto') {
        $idCaja = (int)($_POST['id_apertura_cierre_caja'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $monto = (int)($_POST['monto'] ?? 0);
        $metodoPago = $_POST['metodo_pago'] ?? 'EFECTIVO';

        if (!$idCaja || empty($descripcion) || $monto <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Complete todos los datos del gasto / egreso.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO gastos (id_apertura_cierre_caja, fecha, descripcion, monto, metodo_pago, estado, idusuario) 
            VALUES (:caja, CURDATE(), :des, :monto, :met, 'ACTIVO', :usr)");
        $stmt->execute([
            ':caja' => $idCaja,
            ':des' => $descripcion,
            ':monto' => $monto,
            ':met' => $metodoPago,
            ':usr' => $idUsuario
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Gasto / Egreso registrado con éxito.']);
        exit;
    }

    if ($accion === 'listar_gastos_caja') {
        $idCaja = (int)($_GET['id_apertura_cierre_caja'] ?? 0);
        $stmt = $pdo->prepare("SELECT id_gasto, fecha, descripcion, monto, metodo_pago FROM gastos WHERE id_apertura_cierre_caja = :caja AND estado = 'ACTIVO' ORDER BY id_gasto DESC");
        $stmt->execute([':caja' => $idCaja]);
        $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $gastos]);
        exit;
    }

    if ($accion === 'listar_historial') {
        $stmt = $pdo->query("SELECT a.*, COALESCE(s.nombre_sucursakl, CONCAT('Sucursal ', a.id_sucursal)) as sucursal_nombre
            FROM apertura_cierre_caja a
            LEFT JOIN sucursal s ON a.id_sucursal = s.id_sucursal
            ORDER BY a.id_apertura_cierre_caja DESC LIMIT 30");
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $historial]);
        exit;
    }

    if ($accion === 'comprobante_cierre') {
        $idCaja = (int)($_GET['id_apertura_cierre_caja'] ?? 0);
        $stmtCaja = $pdo->prepare("SELECT a.*, 
            COALESCE(s.nombre_sucursakl, CONCAT('Sucursal ', a.id_sucursal)) as sucursal_nombre,
            s.direccion_sucursal,
            COALESCE(u.nombre_apellido, CONCAT('Usuario #', a.idusuario)) as cajero_nombre
            FROM apertura_cierre_caja a
            LEFT JOIN sucursal s ON a.id_sucursal = s.id_sucursal
            LEFT JOIN usuarios u ON a.idusuario = u.cod_usuario
            WHERE a.id_apertura_cierre_caja = :id");
        $stmtCaja->execute([':id' => $idCaja]);
        $caja = $stmtCaja->fetch(PDO::FETCH_ASSOC);

        if (!$caja) {
            echo json_encode(['status' => 'error', 'message' => 'Caja no encontrada.']);
            exit;
        }

        echo json_encode([
            'status' => 'success',
            'caja' => $caja
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
