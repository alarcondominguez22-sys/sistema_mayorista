<?php
// Configuración global del sistema
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Zona horaria
date_default_timezone_set('America/Asuncion');

// Ruta base del sistema (detecta automáticamente subcarpeta en XAMPP o raíz en InfinityFree)
if (!defined('BASE_URL')) {
    $detectedBaseUrl = '/SISTEMA_MAYORISTA/';
    if (!empty($_SERVER['SCRIPT_NAME']) && preg_match('#^(.*?/sistema_mayorista/)#i', $_SERVER['SCRIPT_NAME'], $matches)) {
        $detectedBaseUrl = $matches[1];
    } elseif (PHP_SAPI !== 'cli' && !empty($_SERVER['SCRIPT_NAME'])) {
        $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        $basePrefix = preg_replace('#/(paginas/(movimientos|referenciales)|controladores)/[^/]+$#i', '/', $scriptPath);
        if ($basePrefix === $scriptPath) {
            $basePrefix = rtrim(str_replace('\\', '/', dirname($scriptPath)), '/') . '/';
        }
        $detectedBaseUrl = $basePrefix ?: '/';
    }
    define('BASE_URL', $detectedBaseUrl);
}

// Conexión a Base de Datos
require_once __DIR__ . '/../conexion/db.php';

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $db = new DB();
        $pdo = $db->conectar();
    }
    return $pdo;
}

// Formateador de moneda (Guaraníes)
function formatoMoneda($monto) {
    return number_format((float)$monto, 0, ',', '.') . ' Gs.';
}

// Sanitizar entradas
function limpiarInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Catálogo de módulos y permisos del sistema
function catalogoModulosSistema(): array {
    return [
        'dashboard'          => ['nombre' => 'Panel Principal (Dashboard)',      'grupo' => 'Ventas y Caja',       'icono' => 'bi-speedometer2'],
        'facturacion'        => ['nombre' => 'Caja Rápida / Facturación POS',    'grupo' => 'Ventas y Caja',       'icono' => 'bi-cart-check-fill'],
        'ventas_historial'   => ['nombre' => 'Historial de Facturas',            'grupo' => 'Ventas y Caja',       'icono' => 'bi-clock-history'],
        'caja'               => ['nombre' => 'Control de Apertura/Cierre Caja',  'grupo' => 'Ventas y Caja',       'icono' => 'bi-cash-stack'],
        'cuentas_cobrar'     => ['nombre' => 'Cuentas por Cobrar y Cuotas',      'grupo' => 'Ventas y Caja',       'icono' => 'bi-wallet2'],
        'compras'            => ['nombre' => 'Factura de Compra (+Stock)',       'grupo' => 'Inventario y Costos', 'icono' => 'bi-bag-plus-fill'],
        'informe_ganancias'  => ['nombre' => 'Informe de Ganancias',             'grupo' => 'Inventario y Costos', 'icono' => 'bi-graph-up-arrow'],
        'productos'          => ['nombre' => 'Productos y Precios',              'grupo' => 'Inventario y Costos', 'icono' => 'bi-box-seam'],
        'ajuste_stock'       => ['nombre' => 'Ajuste Manual de Stock',           'grupo' => 'Inventario y Costos', 'icono' => 'bi-sliders'],
        'clientes'           => ['nombre' => 'Gestión de Clientes',              'grupo' => 'Administración',      'icono' => 'bi-people'],
        'proveedores'        => ['nombre' => 'Gestión de Proveedores',           'grupo' => 'Administración',      'icono' => 'bi-truck'],
        'referenciales'      => ['nombre' => 'Tablas Referenciales',             'grupo' => 'Administración',      'icono' => 'bi-grid-3x3-gap'],
        'usuarios'           => ['nombre' => 'Usuarios, Roles y Accesos',        'grupo' => 'Administración',      'icono' => 'bi-shield-lock-fill'],
        'cambiar_sucursal'   => ['nombre' => 'Cambiar de Sucursal Libremente',   'grupo' => 'Permisos Especiales', 'icono' => 'bi-building'],
        'anular_comprobantes'=> ['nombre' => 'Anular Facturas y Compras',        'grupo' => 'Permisos Especiales', 'icono' => 'bi-x-circle']
    ];
}

// Permisos predeterminados según rol cuando el usuario no tiene permisos personalizados
function obtenerPermisosPorDefectoRol(int $idRol, string $rolNombre = ''): array {
    $todos = array_keys(catalogoModulosSistema());
    $upper = strtoupper(trim($rolNombre));

    if (in_array($idRol, [1, 2], true) || strpos($upper, 'ADMIN') !== false) {
        return $todos;
    }

    if ($idRol === 4 || strpos($upper, 'SUPERVISOR') !== false) {
        return [
            'dashboard', 'facturacion', 'ventas_historial', 'caja', 'cuentas_cobrar',
            'compras', 'productos', 'ajuste_stock', 'clientes', 'proveedores',
            'cambiar_sucursal', 'anular_comprobantes'
        ];
    }

    // Por defecto para VENDEDOR / CAJERA / TECNICO
    return [
        'facturacion',
        'ventas_historial',
        'caja',
        'cuentas_cobrar',
        'clientes',
        'productos'
    ];
}

// Usuario por defecto si no hay sesión iniciada
if (!isset($_SESSION['idusuario'])) {
    $_SESSION['idusuario'] = 1;
    $_SESSION['nombre_usuario'] = 'Administrador';
    $_SESSION['nick_name'] = 'ADMIN';
    $_SESSION['id_sucursal'] = 1;
    $_SESSION['id_rol'] = 1;
    $_SESSION['rol_nombre'] = 'ADMINISTRADOR';
    $_SESSION['es_admin'] = true;
    $_SESSION['permisos'] = array_keys(catalogoModulosSistema());
}

if (!isset($_SESSION['id_rol'])) {
    $_SESSION['id_rol'] = 1;
    $_SESSION['rol_nombre'] = 'ADMINISTRADOR';
    $_SESSION['es_admin'] = true;
}

if (!isset($_SESSION['permisos']) || !is_array($_SESSION['permisos'])) {
    $_SESSION['permisos'] = obtenerPermisosPorDefectoRol((int)$_SESSION['id_rol'], (string)($_SESSION['rol_nombre'] ?? ''));
}

function esAdmin(): bool {
    $idRol = (int)($_SESSION['id_rol'] ?? 1);
    $rolNom = strtoupper(trim((string)($_SESSION['rol_nombre'] ?? '')));
    return !empty($_SESSION['es_admin']) || in_array($idRol, [1, 2], true) || strpos($rolNom, 'ADMIN') !== false;
}

function tienePermiso(string $modulo): bool {
    if (esAdmin()) {
        return true;
    }
    $permisos = $_SESSION['permisos'] ?? [];
    if (in_array($modulo, ['sucursales', 'marcas', 'categorias', 'ciudades'], true)) {
        return in_array('referenciales', $permisos, true) || in_array($modulo, $permisos, true);
    }
    return in_array($modulo, $permisos, true);
}
