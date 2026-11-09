<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';

try {
    if ($accion === 'listar') {
        $buscar = trim($_GET['buscar'] ?? '');
        
        $sql = "SELECT p.id_proveedor_, p.id_ciudad, p.nombre_apellido_prov, p.razon_social,
                       p.telefono_proveedor, p.ruc_proveedor, p.direccion_proveedor, p.email_proveedor,
                       p.estado, c.nombre_ciudad
                FROM proveedores p
                LEFT JOIN ciudades c ON p.id_ciudad = c.id_ciudad
                WHERE 1=1";
        
        $params = [];
        if (!empty($buscar)) {
            $sql .= " AND (p.nombre_apellido_prov LIKE :b1 
                        OR p.razon_social LIKE :b2 
                        OR p.ruc_proveedor LIKE :b3 
                        OR p.telefono_proveedor LIKE :b4 
                        OR c.nombre_ciudad LIKE :b5)";
            $term = "%$buscar%";
            $params[':b1'] = $term;
            $params[':b2'] = $term;
            $params[':b3'] = $term;
            $params[':b4'] = $term;
            $params[':b5'] = $term;
        }

        $sql .= " ORDER BY p.id_proveedor_ DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data, 'total' => count($data)]);
        exit;
    }

    if ($accion === 'listar_ciudades') {
        $stmt = $pdo->query("SELECT id_ciudad, nombre_ciudad FROM ciudades WHERE estado = 'ACTIVO' ORDER BY nombre_ciudad ASC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'ciudades' => $data]);
        exit;
    }

    if ($accion === 'guardar') {
        $id = !empty($_POST['id_proveedor_']) ? (int)$_POST['id_proveedor_'] : null;
        $id_ciudad = !empty($_POST['id_ciudad']) ? (int)$_POST['id_ciudad'] : null;
        $nombre = trim($_POST['nombre_apellido_prov'] ?? '');
        $razon_social = trim($_POST['razon_social'] ?? '');
        $telefono = trim($_POST['telefono_proveedor'] ?? '');
        $ruc = trim($_POST['ruc_proveedor'] ?? '');
        $direccion = trim($_POST['direccion_proveedor'] ?? '');
        $email = trim($_POST['email_proveedor'] ?? '');
        $estado = $_POST['estado'] ?? 'ACTIVO';

        if (empty($razon_social) && empty($nombre)) {
            echo json_encode(['status' => 'error', 'message' => 'Debe ingresar la Razón Social o Nombre del proveedor.']);
            exit;
        }

        if (empty($ruc)) {
            echo json_encode(['status' => 'error', 'message' => 'El RUC del proveedor es obligatorio.']);
            exit;
        }

        if (!$id_ciudad) {
            // Asignar primera ciudad activa por defecto
            $firstCity = $pdo->query("SELECT id_ciudad FROM ciudades LIMIT 1")->fetchColumn();
            $id_ciudad = $firstCity ?: 1;
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE proveedores SET 
                id_ciudad = :ciu,
                nombre_apellido_prov = :nom,
                razon_social = :rs,
                telefono_proveedor = :tel,
                ruc_proveedor = :ruc,
                direccion_proveedor = :dir,
                email_proveedor = :mail,
                estado = :est
                WHERE id_proveedor_ = :id");
            $stmt->execute([
                ':ciu' => $id_ciudad,
                ':nom' => $nombre,
                ':rs' => $razon_social,
                ':tel' => $telefono,
                ':ruc' => $ruc,
                ':dir' => $direccion,
                ':mail' => $email,
                ':est' => $estado,
                ':id' => $id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Proveedor actualizado correctamente.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO proveedores 
                (id_ciudad, nombre_apellido_prov, razon_social, telefono_proveedor, ruc_proveedor, direccion_proveedor, email_proveedor, estado)
                VALUES (:ciu, :nom, :rs, :tel, :ruc, :dir, :mail, :est)");
            $stmt->execute([
                ':ciu' => $id_ciudad,
                ':nom' => $nombre,
                ':rs' => $razon_social,
                ':tel' => $telefono,
                ':ruc' => $ruc,
                ':dir' => $direccion,
                ':mail' => $email,
                ':est' => $estado
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Proveedor registrado con éxito.']);
        }
        exit;
    }

    if ($accion === 'cambiar_estado') {
        $id = (int)($_POST['id_proveedor_'] ?? 0);
        $estado = $_POST['estado'] ?? 'ACTIVO';
        $nuevoEstado = ($estado === 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';

        $stmt = $pdo->prepare("UPDATE proveedores SET estado = :est WHERE id_proveedor_ = :id");
        $stmt->execute([':est' => $nuevoEstado, ':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => "Estado actualizado a $nuevoEstado.", 'nuevo_estado' => $nuevoEstado]);
        exit;
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id_proveedor_'] ?? 0);

        // Validar si tiene compras asociadas
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM compra WHERE id_proveedor_ = :id");
        $stmtCheck->execute([':id' => $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar el proveedor porque posee registros de compras asociados. Se aconseja inactivarlo.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM proveedores WHERE id_proveedor_ = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Proveedor eliminado exitosamente.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
