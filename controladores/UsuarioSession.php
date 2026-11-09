<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'login';

try {
    if ($accion === 'cambiar_sucursal') {
        if (!tienePermiso('cambiar_sucursal')) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Su perfil de usuario solo tiene acceso a su sucursal asignada.'
            ]);
            exit;
        }
        $sucursal = (int)($_POST['id_sucursal'] ?? $_GET['id_sucursal'] ?? 1);
        if (!in_array($sucursal, [1, 2], true)) {
            $sucursal = 1;
        }
        $_SESSION['id_sucursal'] = $sucursal;
        echo json_encode([
            'status' => 'success',
            'message' => "Sucursal activa cambiada a Sucursal $sucursal.",
            'id_sucursal' => $sucursal
        ]);
        exit;
    }

    if ($accion === 'login') {
        $usuario = trim($_POST['usuario'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $sucursal = !empty($_POST['id_sucursal']) ? (int)$_POST['id_sucursal'] : null;

        if (empty($usuario) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Por favor ingrese su usuario y contraseña.']);
            exit;
        }

        // Validación contra tabla usuarios unida a roles
        $stmt = $pdo->prepare("SELECT u.cod_usuario, u.id_sucursal, u.id_rol, u.nombre_apellido, u.nick_name, 
                                      u.password, u.estado, u.permisos_json,
                                      COALESCE(r.descripcion_rol, 'VENDEDOR') AS descripcion_rol
                               FROM usuarios u
                               LEFT JOIN roles r ON u.id_rol = r.id_rol
                               WHERE (UPPER(u.nick_name) = UPPER(:u1) OR UPPER(u.nombre_apellido) = UPPER(:u2))
                               LIMIT 1");
        $stmt->execute([':u1' => $usuario, ':u2' => $usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $hashMd5 = md5($password);

        if ($user) {
            if (strtoupper($user['estado']) !== 'ACTIVO') {
                echo json_encode(['status' => 'error', 'message' => 'El usuario se encuentra inactivo. Contacte al administrador.']);
                exit;
            }

            if ($user['password'] === $hashMd5 || $user['password'] === $password || password_verify($password, $user['password'])) {
                $idRol = (int)$user['id_rol'];
                $rolNombre = strtoupper(trim($user['descripcion_rol']));
                $esAdminUser = in_array($idRol, [1, 2], true) || strpos($rolNombre, 'ADMIN') !== false;

                $permisos = [];
                if (!empty($user['permisos_json'])) {
                    $dec = json_decode($user['permisos_json'], true);
                    if (is_array($dec)) {
                        $permisos = $dec;
                    }
                }
                if (empty($permisos)) {
                    $permisos = obtenerPermisosPorDefectoRol($idRol, $rolNombre);
                }

                // Determinar sucursal activa
                $puedeCambiarSuc = $esAdminUser || in_array('cambiar_sucursal', $permisos, true);
                $sucFinal = $puedeCambiarSuc ? ($sucursal ?: (int)$user['id_sucursal']) : (int)$user['id_sucursal'];

                $_SESSION['logueado'] = true;
                $_SESSION['idusuario'] = (int)$user['cod_usuario'];
                $_SESSION['nombre_usuario'] = $user['nombre_apellido'];
                $_SESSION['nick_name'] = $user['nick_name'];
                $_SESSION['id_sucursal'] = $sucFinal ?: 1;
                $_SESSION['id_rol'] = $idRol;
                $_SESSION['rol_nombre'] = $rolNombre;
                $_SESSION['es_admin'] = $esAdminUser;
                $_SESSION['permisos'] = $permisos;

                // Resetear intentos fallidos
                $pdo->prepare("UPDATE usuarios SET intentos = 0 WHERE cod_usuario = :id")->execute([':id' => $user['cod_usuario']]);

                // Redirección inteligente: Admin o con permiso dashboard -> index.php; Vendedor/Cajero -> Caja Rápida POS
                $redirectUrl = ($esAdminUser || in_array('dashboard', $permisos, true))
                    ? BASE_URL . 'index.php'
                    : BASE_URL . 'paginas/movimientos/facturacion.php';

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Bienvenido/a ' . $user['nombre_apellido'] . ' (' . $rolNombre . ')',
                    'rol' => $rolNombre,
                    'es_admin' => $esAdminUser,
                    'redirect' => $redirectUrl
                ]);
                exit;
            }
        }

        // Credenciales de respaldo de administrador (admin / 123456 o admin / 123)
        if (strtolower($usuario) === 'admin' && in_array($password, ['123456', '123', 'admin'], true)) {
            $_SESSION['logueado'] = true;
            $_SESSION['idusuario'] = 1;
            $_SESSION['nombre_usuario'] = 'ADMINISTRADOR GENERAL';
            $_SESSION['nick_name'] = 'ADMIN';
            $_SESSION['id_sucursal'] = $sucursal ?: 1;
            $_SESSION['id_rol'] = 1;
            $_SESSION['rol_nombre'] = 'SUPERADMIN';
            $_SESSION['es_admin'] = true;
            $_SESSION['permisos'] = array_keys(catalogoModulosSistema());
            echo json_encode([
                'status' => 'success',
                'message' => 'Bienvenido Administrador',
                'rol' => 'SUPERADMIN',
                'es_admin' => true,
                'redirect' => BASE_URL . 'index.php'
            ]);
            exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Usuario o contraseña incorrectos.']);
        exit;
    }

    if ($accion === 'registrar') {
        $nombre = strtoupper(trim($_POST['nombre_apellido'] ?? ''));
        $nick = strtoupper(trim($_POST['nick_name'] ?? ''));
        $password = trim($_POST['password'] ?? '');
        $idSucursal = (int)($_POST['id_sucursal'] ?? 1);
        $tipoCuenta = strtoupper(trim($_POST['tipo_cuenta'] ?? 'VENDEDOR'));

        if (empty($nombre) || empty($nick) || strlen($password) < 3) {
            echo json_encode(['status' => 'error', 'message' => 'Complete todos los campos obligatorios (contraseña mín. 3 caracteres).']);
            exit;
        }

        $stmtCheck = $pdo->prepare("SELECT cod_usuario FROM usuarios WHERE UPPER(nick_name) = :nick");
        $stmtCheck->execute([':nick' => $nick]);
        if ($stmtCheck->fetch()) {
            echo json_encode(['status' => 'error', 'message' => "El usuario '$nick' ya está registrado."]);
            exit;
        }

        // Buscar rol según tipo de cuenta solicitada (VENDEDOR por defecto o ADMIN)
        if ($tipoCuenta === 'ADMIN') {
            $idRol = (int)$pdo->query("SELECT id_rol FROM roles WHERE UPPER(descripcion_rol) LIKE '%ADMIN%' ORDER BY id_rol ASC LIMIT 1")->fetchColumn() ?: 2;
            $rolNombre = 'ADMIN';
        } else {
            $idRol = (int)$pdo->query("SELECT id_rol FROM roles WHERE UPPER(descripcion_rol) IN ('VENDEDOR', 'CAJERA') ORDER BY id_rol DESC LIMIT 1")->fetchColumn() ?: 5;
            $rolNombre = 'VENDEDOR';
        }

        $permisosDef = obtenerPermisosPorDefectoRol($idRol, $rolNombre);
        $permisosJson = json_encode($permisosDef, JSON_UNESCAPED_UNICODE);

        $stmtIns = $pdo->prepare("INSERT INTO usuarios (id_sucursal, id_rol, nombre_apellido, nick_name, password, estado, intentos, limite_intentos, requiere_cambio_pass, permisos_json) 
                                  VALUES (:suc, :rol, :nom, :nick, :pass, 'ACTIVO', 0, 5, 0, :perm)");
        $stmtIns->execute([
            ':suc' => $idSucursal,
            ':rol' => $idRol,
            ':nom' => $nombre,
            ':nick' => $nick,
            ':pass' => md5($password),
            ':perm' => $permisosJson
        ]);

        $nuevoId = (int)$pdo->lastInsertId();
        $esAdminUser = ($tipoCuenta === 'ADMIN');

        $_SESSION['logueado'] = true;
        $_SESSION['idusuario'] = $nuevoId;
        $_SESSION['nombre_usuario'] = $nombre;
        $_SESSION['nick_name'] = $nick;
        $_SESSION['id_sucursal'] = $idSucursal;
        $_SESSION['id_rol'] = $idRol;
        $_SESSION['rol_nombre'] = $rolNombre;
        $_SESSION['es_admin'] = $esAdminUser;
        $_SESSION['permisos'] = $permisosDef;

        $redirectUrl = ($esAdminUser || in_array('dashboard', $permisosDef, true))
            ? BASE_URL . 'index.php'
            : BASE_URL . 'paginas/movimientos/facturacion.php';

        echo json_encode([
            'status' => 'success',
            'message' => 'Cuenta creada exitosamente.',
            'redirect' => $redirectUrl
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
