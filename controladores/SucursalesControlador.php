<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';

try {
    if ($accion === 'listar') {
        $buscar = $_GET['buscar'] ?? '';
        $sql = "SELECT s.id_sucursal, s.id_ciudad, s.nombre_sucursakl, s.direccion_sucursal, s.estado, c.nombre_ciudad 
                FROM sucursal s 
                LEFT JOIN ciudades c ON s.id_ciudad = c.id_ciudad";
        $params = [];
        if (!empty($buscar)) {
            $sql .= " WHERE s.nombre_sucursakl LIKE :b1 OR s.direccion_sucursal LIKE :b2 OR c.nombre_ciudad LIKE :b3";
            $term = "%$buscar%";
            $params[':b1'] = $term;
            $params[':b2'] = $term;
            $params[':b3'] = $term;
        }
        $sql .= " ORDER BY s.id_sucursal ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($accion === 'listar_ciudades') {
        $stmt = $pdo->query("SELECT id_ciudad, nombre_ciudad FROM ciudades WHERE estado = 'ACTIVO' ORDER BY nombre_ciudad ASC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($accion === 'guardar') {
        $id = !empty($_POST['id_sucursal']) ? (int)$_POST['id_sucursal'] : null;
        $id_ciudad = (int)($_POST['id_ciudad'] ?? 0);
        $nombre = trim($_POST['nombre_sucursakl'] ?? '');
        $direccion = trim($_POST['direccion_sucursal'] ?? '');
        $estado = $_POST['estado'] ?? 'ACTIVO';

        if (empty($nombre) || empty($direccion) || $id_ciudad <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre, la ciudad y la dirección son obligatorios.']);
            exit;
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE sucursal SET id_ciudad = :ciudad, nombre_sucursakl = :nombre, direccion_sucursal = :dir, estado = :est WHERE id_sucursal = :id");
            $stmt->execute([':ciudad' => $id_ciudad, ':nombre' => $nombre, ':dir' => $direccion, ':est' => $estado, ':id' => $id]);
            echo json_encode(['status' => 'success', 'message' => 'Sucursal actualizada con éxito.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO sucursal (id_ciudad, nombre_sucursakl, direccion_sucursal, estado) VALUES (:ciudad, :nombre, :dir, :est)");
            $stmt->execute([':ciudad' => $id_ciudad, ':nombre' => $nombre, ':dir' => $direccion, ':est' => $estado]);
            echo json_encode(['status' => 'success', 'message' => 'Sucursal registrada con éxito.']);
        }
        exit;
    }

    if ($accion === 'cambiar_estado') {
        $id = (int)($_POST['id_sucursal'] ?? 0);
        $estado = $_POST['estado'] ?? 'ACTIVO';
        $nuevoEstado = ($estado === 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';

        $stmt = $pdo->prepare("UPDATE sucursal SET estado = :est WHERE id_sucursal = :id");
        $stmt->execute([':est' => $nuevoEstado, ':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => "Estado actualizado a $nuevoEstado.", 'nuevo_estado' => $nuevoEstado]);
        exit;
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id_sucursal'] ?? 0);

        // Validar si tiene ventas asociadas
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM ventas WHERE id_sucursal = :id");
        $stmtCheck->execute([':id' => $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar la sucursal porque tiene facturas/ventas registradas. Se recomienda desactivarla.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM sucursal WHERE id_sucursal = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Sucursal eliminada correctamente.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
