<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';
$idUsuario = (int)($_SESSION['idusuario'] ?? 1);
$idSucursal = (int)($_SESSION['id_sucursal'] ?? 1);

try {
    if ($accion === 'listar') {
        $sql = "SELECT a.id_ajuste_stock, a.tipo_ajuste, a.fecha_ajuste, a.motiv_ajust, a.id_sucursal,
                       COALESCE(s.nombre_sucursakl, CONCAT('Sucursal ', a.id_sucursal)) as sucursal_nombre,
                       d.id_producto, d.cantidad_anterior, d.cantidad_nueva,
                       p.nombre_prod, p.codigo_barra
                FROM ajuste_stock a
                LEFT JOIN sucursal s ON a.id_sucursal = s.id_sucursal
                LEFT JOIN detalle_ajuste_stock d ON a.id_ajuste_stock = d.id_ajuste_stock
                LEFT JOIN productos p ON d.id_producto = p.id_producto
                ORDER BY a.id_ajuste_stock DESC LIMIT 50";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($accion === 'guardar') {
        $idProducto = (int)($_POST['id_producto'] ?? 0);
        $sucursal = (int)($_POST['id_sucursal'] ?? $idSucursal);
        $tipoAjuste = $_POST['tipo_ajuste'] ?? 'POSITIVO'; // POSITIVO o NEGATIVO
        $cantidad = (int)($_POST['cantidad'] ?? 0);
        $motivo = trim($_POST['motiv_ajust'] ?? 'CONTEO FISICO');

        if (!$idProducto || $cantidad <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Seleccione un producto e ingrese una cantidad válida mayor a cero.']);
            exit;
        }

        // Obtener stock actual del producto
        $colStock = ($sucursal == 2) ? 'stock_sucursal2' : 'stock_sucursal1';
        $stmtProd = $pdo->prepare("SELECT id_producto, stock_actual, $colStock as stock_suc FROM productos WHERE id_producto = :id");
        $stmtProd->execute([':id' => $idProducto]);
        $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);

        if (!$prod) {
            echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado.']);
            exit;
        }

        $stockAnterior = (int)$prod['stock_suc'];
        $cantidadDelta = ($tipoAjuste === 'POSITIVO') ? $cantidad : -$cantidad;
        $stockNuevo = max(0, $stockAnterior + $cantidadDelta);

        // Obtener deposito para la sucursal
        $stmtDep = $pdo->prepare("SELECT id_deposito FROM deposito WHERE id_sucursal = :suc LIMIT 1");
        $stmtDep->execute([':suc' => $sucursal]);
        $idDeposito = $stmtDep->fetchColumn() ?: 1;

        $pdo->beginTransaction();

        // 1. Insertar cabecera de ajuste
        $stmtAjuste = $pdo->prepare("INSERT INTO ajuste_stock (idusuario, tipo_ajuste, fecha_ajuste, motiv_ajust, id_sucursal) 
            VALUES (:usr, :tip, CURDATE(), :mot, :suc)");
        $stmtAjuste->execute([
            ':usr' => $idUsuario,
            ':tip' => $tipoAjuste,
            ':mot' => $motivo,
            ':suc' => $sucursal
        ]);
        $idAjuste = (int)$pdo->lastInsertId();

        // 2. Insertar detalle de ajuste
        $stmtDet = $pdo->prepare("INSERT INTO detalle_ajuste_stock (id_ajuste_stock, id_producto, id_deposito, cantidad_anterior, cantidad_nueva) 
            VALUES (:ida, :idp, :idd, :ca, :cn)");
        $stmtDet->execute([
            ':ida' => $idAjuste,
            ':idp' => $idProducto,
            ':idd' => $idDeposito,
            ':ca' => $stockAnterior,
            ':cn' => $stockNuevo
        ]);

        // 3. Actualizar inventario en tabla productos
        $stmtUpd = $pdo->prepare("UPDATE productos SET 
            $colStock = :sn,
            stock_actual = GREATEST(0, stock_actual + :delta)
            WHERE id_producto = :idp");
        $stmtUpd->execute([
            ':sn' => $stockNuevo,
            ':delta' => $cantidadDelta,
            ':idp' => $idProducto
        ]);

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => "Ajuste #$idAjuste aplicado correctamente. Stock anterior: $stockAnterior, Nuevo stock: $stockNuevo.",
            'nuevo_stock' => $stockNuevo
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
