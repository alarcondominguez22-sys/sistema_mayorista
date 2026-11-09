<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';

try {
    if ($accion === 'listar') {
        $buscar = $_GET['buscar'] ?? '';
        $sql = "SELECT id_ciudad, nombre_ciudad, estado FROM ciudades";
        $params = [];
        if (!empty($buscar)) {
            $sql .= " WHERE nombre_ciudad LIKE :buscar";
            $params[':buscar'] = "%$buscar%";
        }
        $sql .= " ORDER BY nombre_ciudad ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($accion === 'guardar') {
        $id = !empty($_POST['id_ciudad']) ? (int)$_POST['id_ciudad'] : null;
        $nombre = trim($_POST['nombre_ciudad'] ?? '');
        $estado = $_POST['estado'] ?? 'ACTIVO';

        if (empty($nombre)) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre de la ciudad es obligatorio.']);
            exit;
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE ciudades SET nombre_ciudad = :nombre, estado = :est WHERE id_ciudad = :id");
            $stmt->execute([':nombre' => $nombre, ':est' => $estado, ':id' => $id]);
            echo json_encode(['status' => 'success', 'message' => 'Ciudad actualizada correctamente.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO ciudades (nombre_ciudad, estado) VALUES (:nombre, :est)");
            $stmt->execute([':nombre' => $nombre, ':est' => $estado]);
            echo json_encode(['status' => 'success', 'message' => 'Ciudad registrada con éxito.']);
        }
        exit;
    }

    if ($accion === 'cambiar_estado') {
        $id = (int)($_POST['id_ciudad'] ?? 0);
        $estado = $_POST['estado'] ?? 'ACTIVO';
        $nuevoEstado = ($estado === 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';

        $stmt = $pdo->prepare("UPDATE ciudades SET estado = :est WHERE id_ciudad = :id");
        $stmt->execute([':est' => $nuevoEstado, ':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => "Estado actualizado a $nuevoEstado.", 'nuevo_estado' => $nuevoEstado]);
        exit;
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id_ciudad'] ?? 0);

        // Validar si tiene sucursales o clientes asociados
        $stmtCheckSuc = $pdo->prepare("SELECT COUNT(*) FROM sucursal WHERE id_ciudad = :id");
        $stmtCheckSuc->execute([':id' => $id]);
        if ($stmtCheckSuc->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar la ciudad porque tiene sucursales asociadas.']);
            exit;
        }

        $stmtCheckCli = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE id_ciudad = :id");
        $stmtCheckCli->execute([':id' => $id]);
        if ($stmtCheckCli->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar la ciudad porque tiene clientes asociados.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM ciudades WHERE id_ciudad = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Ciudad eliminada correctamente.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
