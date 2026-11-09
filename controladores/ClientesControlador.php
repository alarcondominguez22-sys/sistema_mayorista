<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';

try {
    if ($accion === 'listar') {
        $buscar = trim($_GET['buscar'] ?? '');
        $tipoFiltro = trim($_GET['tipo_cliente'] ?? '');
        $sql = "SELECT c.id_cliente, c.id_ciudad, c.nombre_cliente, c.apellido_cliente, c.cedula_cliente, 
                       COALESCE(NULLIF(c.tipo_cliente, ''), 'MAYORISTA') AS tipo_cliente,
                       c.telefono_cliente, c.direccion_cliente, c.estado, c.correo, ciu.nombre_ciudad
                FROM clientes c
                LEFT JOIN ciudades ciu ON c.id_ciudad = ciu.id_ciudad
                WHERE 1=1";
        $params = [];
        if (!empty($buscar)) {
            $sql .= " AND (c.nombre_cliente LIKE :b1 
                         OR c.apellido_cliente LIKE :b2 
                         OR c.cedula_cliente LIKE :b3 
                         OR c.telefono_cliente LIKE :b4
                         OR c.tipo_cliente LIKE :b5)";
            $params[':b1'] = "%$buscar%";
            $params[':b2'] = "%$buscar%";
            $params[':b3'] = "%$buscar%";
            $params[':b4'] = "%$buscar%";
            $params[':b5'] = "%$buscar%";
        }
        if (!empty($tipoFiltro)) {
            $sql .= " AND c.tipo_cliente = :tipo";
            $params[':tipo'] = $tipoFiltro;
        }
        $sql .= " ORDER BY c.id_cliente DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($accion === 'buscar_cliente') {
        $q = trim($_GET['q'] ?? '');
        if (empty($q)) {
            $sql = "SELECT c.id_cliente, c.nombre_cliente, c.apellido_cliente, c.cedula_cliente,
                           COALESCE(NULLIF(c.tipo_cliente, ''), 'MAYORISTA') AS tipo_cliente,
                           c.telefono_cliente, c.direccion_cliente, ciu.nombre_ciudad 
                    FROM clientes c
                    LEFT JOIN ciudades ciu ON c.id_ciudad = ciu.id_ciudad
                    WHERE c.estado = 'ACTIVO' 
                    ORDER BY c.id_cliente ASC 
                    LIMIT 50";
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sql = "SELECT c.id_cliente, c.nombre_cliente, c.apellido_cliente, c.cedula_cliente,
                           COALESCE(NULLIF(c.tipo_cliente, ''), 'MAYORISTA') AS tipo_cliente,
                           c.telefono_cliente, c.direccion_cliente, ciu.nombre_ciudad 
                    FROM clientes c
                    LEFT JOIN ciudades ciu ON c.id_ciudad = ciu.id_ciudad
                    WHERE (c.nombre_cliente LIKE :q1 
                       OR c.apellido_cliente LIKE :q2 
                       OR c.cedula_cliente LIKE :q3 
                       OR c.telefono_cliente LIKE :q4
                       OR CONCAT(c.nombre_cliente, ' ', c.apellido_cliente) LIKE :q5
                       OR c.tipo_cliente LIKE :q6)
                      AND c.estado = 'ACTIVO' 
                    ORDER BY (c.cedula_cliente = :exactCed) DESC, c.nombre_cliente ASC
                    LIMIT 30";
            $stmt = $pdo->prepare($sql);
            $term = "%$q%";
            $stmt->execute([
                ':q1' => $term, 
                ':q2' => $term, 
                ':q3' => $term, 
                ':q4' => $term,
                ':q5' => $term,
                ':q6' => $term,
                ':exactCed' => $q
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($accion === 'guardar') {
        $id = !empty($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : null;
        $nombre = trim($_POST['nombre_cliente'] ?? '');
        $apellido = trim($_POST['apellido_cliente'] ?? '');
        $cedula = trim($_POST['cedula_cliente'] ?? '');
        $tipo_cliente = strtoupper(trim($_POST['tipo_cliente'] ?? 'MAYORISTA'));
        if (!in_array($tipo_cliente, ['MAYORISTA', 'CLIENTE FIEL', 'MINORISTA'], true)) {
            $tipo_cliente = 'MAYORISTA';
        }
        $telefono = trim($_POST['telefono_cliente'] ?? '');
        $direccion = trim($_POST['direccion_cliente'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $id_ciudad = !empty($_POST['id_ciudad']) ? (int)$_POST['id_ciudad'] : 1;
        $estado = $_POST['estado'] ?? 'ACTIVO';

        if (empty($nombre) || empty($cedula)) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre y la Cédula/RUC son campos obligatorios.']);
            exit;
        }

        // Verificar si la cédula ya existe para otro cliente
        $sqlCheck = "SELECT id_cliente FROM clientes WHERE cedula_cliente = :ced";
        $paramsCheck = [':ced' => $cedula];
        if ($id) {
            $sqlCheck .= " AND id_cliente != :id";
            $paramsCheck[':id'] = $id;
        }
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute($paramsCheck);
        if ($stmtCheck->fetch()) {
            echo json_encode(['status' => 'error', 'message' => "La Cédula/RUC $cedula ya está registrada para otro cliente."]);
            exit;
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE clientes SET 
                id_ciudad = :ciudad, 
                nombre_cliente = :nom, 
                apellido_cliente = :ape, 
                cedula_cliente = :ced, 
                tipo_cliente = :tipo,
                telefono_cliente = :tel, 
                direccion_cliente = :dir, 
                correo = :correo, 
                estado = :est 
                WHERE id_cliente = :id");
            $stmt->execute([
                ':ciudad' => $id_ciudad,
                ':nom' => $nombre,
                ':ape' => $apellido,
                ':ced' => $cedula,
                ':tipo' => $tipo_cliente,
                ':tel' => $telefono,
                ':dir' => $direccion,
                ':correo' => $correo,
                ':est' => $estado,
                ':id' => $id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Cliente actualizado correctamente.', 'id_cliente' => $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO clientes 
                (id_ciudad, nombre_cliente, apellido_cliente, cedula_cliente, tipo_cliente, telefono_cliente, direccion_cliente, correo, estado) 
                VALUES (:ciudad, :nom, :ape, :ced, :tipo, :tel, :dir, :correo, :est)");
            $stmt->execute([
                ':ciudad' => $id_ciudad,
                ':nom' => $nombre,
                ':ape' => $apellido,
                ':ced' => $cedula,
                ':tipo' => $tipo_cliente,
                ':tel' => $telefono,
                ':dir' => $direccion,
                ':correo' => $correo,
                ':est' => $estado
            ]);
            $nuevoId = (int)$pdo->lastInsertId();
            echo json_encode([
                'status' => 'success', 
                'message' => 'Cliente registrado exitosamente.', 
                'id_cliente' => $nuevoId,
                'cliente' => [
                    'id_cliente' => $nuevoId,
                    'nombre_cliente' => $nombre,
                    'apellido_cliente' => $apellido,
                    'cedula_cliente' => $cedula,
                    'tipo_cliente' => $tipo_cliente,
                    'telefono_cliente' => $telefono,
                    'direccion_cliente' => $direccion
                ]
            ]);
        }
        exit;
    }

    if ($accion === 'cambiar_estado') {
        $id = (int)($_POST['id_cliente'] ?? 0);
        $estado = $_POST['estado'] ?? 'ACTIVO';
        $nuevoEstado = ($estado === 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';

        $stmt = $pdo->prepare("UPDATE clientes SET estado = :est WHERE id_cliente = :id");
        $stmt->execute([':est' => $nuevoEstado, ':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => "Estado actualizado a $nuevoEstado.", 'nuevo_estado' => $nuevoEstado]);
        exit;
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id_cliente'] ?? 0);

        // Validar si tiene ventas asociadas
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM ventas WHERE id_cliente = :id");
        $stmtCheck->execute([':id' => $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar el cliente porque tiene ventas/facturas asociadas. Se recomienda desactivarlo.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id_cliente = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Cliente eliminado correctamente.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
