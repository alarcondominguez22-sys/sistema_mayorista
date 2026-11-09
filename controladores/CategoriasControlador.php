<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';

try {
    if ($accion === 'listar') {
        $buscar = $_GET['buscar'] ?? '';
        $sql = "SELECT id_categorias, descripcion, estado FROM categorias";
        $params = [];
        if (!empty($buscar)) {
            $sql .= " WHERE descripcion LIKE :buscar";
            $params[':buscar'] = "%$buscar%";
        }
        $sql .= " ORDER BY descripcion ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($accion === 'guardar') {
        $id = !empty($_POST['id_categorias']) ? (int)$_POST['id_categorias'] : null;
        $descripcion = trim($_POST['descripcion'] ?? '');
        $estado = $_POST['estado'] ?? 'ACTIVO';

        if (empty($descripcion)) {
            echo json_encode(['status' => 'error', 'message' => 'La descripción es obligatoria.']);
            exit;
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE categorias SET descripcion = :desc, estado = :est WHERE id_categorias = :id");
            $stmt->execute([':desc' => $descripcion, ':est' => $estado, ':id' => $id]);
            echo json_encode(['status' => 'success', 'message' => 'Categoría actualizada correctamente.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO categorias (descripcion, estado) VALUES (:desc, :est)");
            $stmt->execute([':desc' => $descripcion, ':est' => $estado]);
            echo json_encode(['status' => 'success', 'message' => 'Categoría creada con éxito.']);
        }
        exit;
    }

    if ($accion === 'cambiar_estado') {
        $id = (int)($_POST['id_categorias'] ?? 0);
        $estado = $_POST['estado'] ?? 'ACTIVO';
        $nuevoEstado = ($estado === 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';

        $stmt = $pdo->prepare("UPDATE categorias SET estado = :est WHERE id_categorias = :id");
        $stmt->execute([':est' => $nuevoEstado, ':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => "Estado actualizado a $nuevoEstado.", 'nuevo_estado' => $nuevoEstado]);
        exit;
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id_categorias'] ?? 0);

        // Validar si tiene productos asociados
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE id_categoria = :id");
        $stmtCheck->execute([':id' => $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar la categoría porque tiene productos vinculados. Se sugiere inactivarla.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id_categorias = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Categoría eliminada correctamente.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
