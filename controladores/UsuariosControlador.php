<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';

try {
    switch ($accion) {
        case 'listar':
            $stmtUsers = $pdo->query("
                SELECT u.cod_usuario, u.id_sucursal, u.id_rol, u.nombre_apellido, u.nick_name,
                       u.estado, u.intentos, u.limite_intentos, u.permisos_json,
                       COALESCE(r.descripcion_rol, 'VENDEDOR') AS descripcion_rol,
                       COALESCE(s.nombre_sucursakl, CONCAT('Sucursal ', u.id_sucursal)) AS nombre_sucursal
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id_rol
                LEFT JOIN sucursal s ON u.id_sucursal = s.id_sucursal
                ORDER BY u.estado ASC, u.id_rol ASC, u.nombre_apellido ASC
            ");
            $usuarios = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            foreach ($usuarios as &$u) {
                $idRol = (int)$u['id_rol'];
                $rolNom = strtoupper(trim($u['descripcion_rol']));
                $u['es_admin'] = in_array($idRol, [1, 2], true) || strpos($rolNom, 'ADMIN') !== false;

                $permisos = [];
                if (!empty($u['permisos_json'])) {
                    $dec = json_decode($u['permisos_json'], true);
                    if (is_array($dec)) {
                        $permisos = $dec;
                    }
                }
                if (empty($permisos)) {
                    $permisos = obtenerPermisosPorDefectoRol($idRol, $rolNom);
                }
                $u['permisos'] = array_values(array_unique($permisos));
            }
            unset($u);

            $roles = $pdo->query("SELECT id_rol, descripcion_rol, estado FROM roles WHERE estado = 'ACTIVO' ORDER BY id_rol ASC")->fetchAll(PDO::FETCH_ASSOC);
            $sucursales = $pdo->query("SELECT id_sucursal, nombre_sucursakl AS nombre_sucursal, direccion_sucursal AS direccion FROM sucursal WHERE estado = 'ACTIVO' ORDER BY id_sucursal ASC")->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'status' => 'success',
                'data' => $usuarios,
                'roles' => $roles,
                'sucursales' => $sucursales,
                'catalogo_modulos' => catalogoModulosSistema()
            ]);
            break;

        case 'guardar':
            $id = !empty($_POST['cod_usuario']) ? (int)$_POST['cod_usuario'] : null;
            $nombre = strtoupper(trim($_POST['nombre_apellido'] ?? ''));
            $nick = strtoupper(trim($_POST['nick_name'] ?? ''));
            $idRol = (int)($_POST['id_rol'] ?? 6);
            $idSucursal = (int)($_POST['id_sucursal'] ?? 1);
            $estado = strtoupper(trim($_POST['estado'] ?? 'ACTIVO'));
            $password = trim($_POST['password'] ?? '');

            if (empty($nombre) || empty($nick)) {
                throw new Exception("El Nombre y Apellido y el Usuario (Login) son obligatorios.");
            }

            // Verificar duplicado de nick_name
            $sqlCheck = "SELECT cod_usuario FROM usuarios WHERE UPPER(nick_name) = :nick" . ($id ? " AND cod_usuario != :id" : "");
            $stmtCheck = $pdo->prepare($sqlCheck);
            $paramsCheck = [':nick' => $nick];
            if ($id) {
                $paramsCheck[':id'] = $id;
            }
            $stmtCheck->execute($paramsCheck);
            if ($stmtCheck->fetch()) {
                throw new Exception("El nombre de usuario '$nick' ya está en uso por otro operador.");
            }

            // Obtener nombre del rol para evaluar permisos por defecto si no se enviaron
            $stmtRol = $pdo->prepare("SELECT descripcion_rol FROM roles WHERE id_rol = :rol");
            $stmtRol->execute([':rol' => $idRol]);
            $rolNombre = strtoupper(trim((string)$stmtRol->fetchColumn() ?: 'VENDEDOR'));

            // Procesar permisos enviados
            $permisosRecibidos = $_POST['permisos'] ?? null;
            if (is_string($permisosRecibidos)) {
                $permisosRecibidos = json_decode($permisosRecibidos, true);
            }
            if (!is_array($permisosRecibidos)) {
                $permisosRecibidos = obtenerPermisosPorDefectoRol($idRol, $rolNombre);
            }

            // Validar contra el catálogo oficial
            $catalogoKeys = array_keys(catalogoModulosSistema());
            $permisosValidos = array_values(array_intersect($catalogoKeys, $permisosRecibidos));
            if (empty($permisosValidos)) {
                $permisosValidos = ['facturacion'];
            }
            $permisosJson = json_encode($permisosValidos, JSON_UNESCAPED_UNICODE);

            if ($id) {
                if (!empty($password)) {
                    if (strlen($password) < 3) {
                        throw new Exception("La contraseña debe tener al menos 3 caracteres.");
                    }
                    $stmtUpd = $pdo->prepare("
                        UPDATE usuarios 
                        SET nombre_apellido = :nom, nick_name = :nick, id_rol = :rol, id_sucursal = :suc,
                            estado = :est, password = :pass, intentos = 0, permisos_json = :perm
                        WHERE cod_usuario = :id
                    ");
                    $stmtUpd->execute([
                        ':nom' => $nombre,
                        ':nick' => $nick,
                        ':rol' => $idRol,
                        ':suc' => $idSucursal,
                        ':est' => $estado,
                        ':pass' => md5($password),
                        ':perm' => $permisosJson,
                        ':id' => $id
                    ]);
                } else {
                    $stmtUpd = $pdo->prepare("
                        UPDATE usuarios 
                        SET nombre_apellido = :nom, nick_name = :nick, id_rol = :rol, id_sucursal = :suc,
                            estado = :est, permisos_json = :perm
                        WHERE cod_usuario = :id
                    ");
                    $stmtUpd->execute([
                        ':nom' => $nombre,
                        ':nick' => $nick,
                        ':rol' => $idRol,
                        ':suc' => $idSucursal,
                        ':est' => $estado,
                        ':perm' => $permisosJson,
                        ':id' => $id
                    ]);
                }
                $msg = "Usuario y accesos actualizados correctamente.";
                $targetId = $id;
            } else {
                if (strlen($password) < 3) {
                    throw new Exception("Para crear un nuevo usuario debe asignar una contraseña (mínimo 3 caracteres).");
                }
                $stmtIns = $pdo->prepare("
                    INSERT INTO usuarios (id_sucursal, id_rol, nombre_apellido, nick_name, password, estado, intentos, limite_intentos, requiere_cambio_pass, permisos_json)
                    VALUES (:suc, :rol, :nom, :nick, :pass, :est, 0, 5, 0, :perm)
                ");
                $stmtIns->execute([
                    ':suc' => $idSucursal,
                    ':rol' => $idRol,
                    ':nom' => $nombre,
                    ':nick' => $nick,
                    ':pass' => md5($password),
                    ':est' => $estado,
                    ':perm' => $permisosJson
                ]);
                $targetId = (int)$pdo->lastInsertId();
                $msg = "Nuevo usuario '$nick' creado y habilitado con éxito.";
            }

            // Si el usuario editado es el usuario en sesión actual, refrescar sus permisos en vivo
            if (isset($_SESSION['idusuario']) && (int)$_SESSION['idusuario'] === (int)$targetId) {
                $esAdminUser = in_array($idRol, [1, 2], true) || strpos($rolNombre, 'ADMIN') !== false;
                $_SESSION['nombre_usuario'] = $nombre;
                $_SESSION['nick_name'] = $nick;
                $_SESSION['id_rol'] = $idRol;
                $_SESSION['rol_nombre'] = $rolNombre;
                $_SESSION['es_admin'] = $esAdminUser;
                $_SESSION['permisos'] = $permisosValidos;
            }

            echo json_encode(['status' => 'success', 'message' => $msg, 'cod_usuario' => $targetId]);
            break;

        case 'cambiar_estado':
            $id = (int)($_POST['cod_usuario'] ?? 0);
            $estado = strtoupper(trim($_POST['estado'] ?? 'ACTIVO'));
            if ($id <= 0) {
                throw new Exception("Usuario inválido.");
            }
            $stmt = $pdo->prepare("UPDATE usuarios SET estado = :est, intentos = 0 WHERE cod_usuario = :id");
            $stmt->execute([':est' => $estado, ':id' => $id]);
            echo json_encode([
                'status' => 'success',
                'message' => "El usuario ahora se encuentra $estado."
            ]);
            break;

        case 'eliminar':
            $id = (int)($_POST['cod_usuario'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID de usuario inválido.");
            }
            if (isset($_SESSION['idusuario']) && (int)$_SESSION['idusuario'] === $id) {
                throw new Exception("No puede eliminar su propio usuario en sesión activa.");
            }
            try {
                $stmtDel = $pdo->prepare("DELETE FROM usuarios WHERE cod_usuario = :id");
                $stmtDel->execute([':id' => $id]);
                echo json_encode(['status' => 'success', 'message' => 'Usuario eliminado permanentemente.']);
            } catch (PDOException $ex) {
                // Si tiene registros históricos asociados (ventas, caja, compras), se inactiva en vez de borrar
                $pdo->prepare("UPDATE usuarios SET estado = 'INACTIVO' WHERE cod_usuario = :id")->execute([':id' => $id]);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'El usuario posee historial de operaciones, por lo que fue pasado a estado INACTIVO para preservar la auditoría.'
                ]);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida en UsuariosControlador.']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

