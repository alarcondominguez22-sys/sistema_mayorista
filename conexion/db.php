<?php

class DB {
    // Atributos principales de conexión (conservados para compatibilidad)
    private $host;
    private $port;
    private $usuario;
    private $pass;
    private $base_de_datos;
    private $charset;

    // Configuraciones de ambos entornos (LOCAL XAMPP y PRODUCCIÓN InfinityFree)
    private $configLocal = [];
    private $configProduccion = [];
    private $entornoActual = 'LOCAL';

    public function __construct(?string $forzarEntorno = null) {
        // 1. Conexión LOCAL (XAMPP) - Conservada íntegramente
        $this->configLocal = [
            'host'          => 'localhost',
            'port'          => 3306,
            'base_de_datos' => 'sistema_mayorista',
            'usuario'       => 'root',
            'pass'          => '',
            'charset'       => 'utf8mb4',
        ];

        // 2. Conexión PRODUCCIÓN (InfinityFree)
        // La contraseña se carga desde variable de entorno o archivo privado (credenciales.local.php)
        $credencialesPrivadas = [];
        $rutaCredenciales = __DIR__ . '/credenciales.local.php';
        if (is_file($rutaCredenciales)) {
            $cargado = include $rutaCredenciales;
            if (is_array($cargado) && isset($cargado['produccion'])) {
                $credencialesPrivadas = $cargado['produccion'];
            }
        }

        $passProduccion = getenv('DB_PROD_PASS')
            ?: ($_ENV['DB_PROD_PASS'] ?? $_SERVER['DB_PROD_PASS'] ?? ($credencialesPrivadas['pass'] ?? ''));

        $this->configProduccion = [
            'host'          => getenv('DB_PROD_HOST') ?: ($credencialesPrivadas['host'] ?? 'sql207.infinityfree.com'),
            'port'          => (int)(getenv('DB_PROD_PORT') ?: ($credencialesPrivadas['port'] ?? 3306)),
            'base_de_datos' => getenv('DB_PROD_NAME') ?: ($credencialesPrivadas['base_de_datos'] ?? 'if0_43398802_db_mayorista'),
            'usuario'       => getenv('DB_PROD_USER') ?: ($credencialesPrivadas['usuario'] ?? 'if0_43398802'),
            'pass'          => $passProduccion,
            'charset'       => $credencialesPrivadas['charset'] ?? 'utf8mb4',
        ];

        // 3. Detección automática del entorno (LOCAL vs PRODUCCION)
        if ($forzarEntorno !== null) {
            $this->entornoActual = (strtoupper($forzarEntorno) === 'PRODUCCION') ? 'PRODUCCION' : 'LOCAL';
        } else {
            $this->entornoActual = self::esEntornoLocal() ? 'LOCAL' : 'PRODUCCION';
        }

        $cfgActiva = ($this->entornoActual === 'LOCAL') ? $this->configLocal : $this->configProduccion;

        $this->host          = $cfgActiva['host'];
        $this->port          = $cfgActiva['port'];
        $this->base_de_datos = $cfgActiva['base_de_datos'];
        $this->usuario       = $cfgActiva['usuario'];
        $this->pass          = $cfgActiva['pass'];
        $this->charset       = $cfgActiva['charset'];
    }

    /**
     * Detecta automáticamente si el sistema se está ejecutando en entorno LOCAL (XAMPP)
     * o en el servidor de PRODUCCIÓN (InfinityFree).
     */
    public static function esEntornoLocal(): bool {
        // Permitir override explícito por variable de entorno si se define
        $appEnv = strtolower((string)(getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? '')));
        if (in_array($appEnv, ['prod', 'production', 'produccion', 'infinityfree'], true)) {
            return false;
        }
        if (in_array($appEnv, ['local', 'development', 'dev', 'xampp'], true)) {
            return true;
        }

        // Si se ejecuta por consola (CLI) y no estamos en un servidor de hosting Linux de InfinityFree
        if (PHP_SAPI === 'cli' && empty($_SERVER['HTTP_HOST']) && empty($_SERVER['SERVER_NAME'])) {
            return true;
        }

        $hostHttp   = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? '')));
        $serverName = strtolower(trim((string)($_SERVER['SERVER_NAME'] ?? '')));
        $serverAddr = trim((string)($_SERVER['SERVER_ADDR'] ?? ''));
        $remoteAddr = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));

        // Quitar puerto si viene en HTTP_HOST (ej: localhost:8080)
        $hostSinPuerto = preg_replace('/:\d+$/', '', $hostHttp ?: $serverName);

        $hostsLocales = ['localhost', '127.0.0.1', '::1'];
        if (in_array($hostSinPuerto, $hostsLocales, true) || in_array($serverName, $hostsLocales, true)) {
            return true;
        }

        if (in_array($serverAddr, ['127.0.0.1', '::1'], true) && in_array($remoteAddr, ['127.0.0.1', '::1'], true)) {
            return true;
        }

        // Red local privada (ej. pruebas en LAN desde XAMPP: 192.168.x.x o 10.x.x.x)
        if (preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $hostSinPuerto)) {
            return true;
        }

        // Cualquier otro dominio público (ej. infinityfreeapp.com, epizy.com, rf.gd, dominio propio) es PRODUCCIÓN
        return false;
    }

    public function obtenerEntorno(): string {
        return $this->entornoActual;
    }

    public function conectar() {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $lastException = null;

        if ($this->entornoActual === 'LOCAL') {
            // Conexión LOCAL (XAMPP): prueba localhost / 127.0.0.1 y base de datos local existente
            $hosts = array_unique([$this->host, '127.0.0.1', 'localhost']);
            $dbs   = array_unique([$this->base_de_datos, 'gestion_ventas']);

            foreach ($dbs as $dbName) {
                foreach ($hosts as $hostName) {
                    try {
                        $dsn = "mysql:host={$hostName};port={$this->port};dbname={$dbName};charset={$this->charset}";
                        $pdo = new PDO($dsn, $this->usuario, $this->pass, $options);
                        $this->asegurarEsquema($pdo);
                        return $pdo;
                    } catch (PDOException $e) {
                        $lastException = $e;
                        if (strpos($e->getMessage(), '1049') !== false) {
                            break; // Pasar a la siguiente base de datos si no existe
                        }
                    }
                }
            }
        } else {
            // Conexión PRODUCCIÓN (InfinityFree)
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->base_de_datos};charset={$this->charset}";
                $pdo = new PDO($dsn, $this->usuario, $this->pass, $options);
                $this->asegurarEsquema($pdo);
                return $pdo;
            } catch (PDOException $e) {
                $lastException = $e;
            }
        }

        // Registrar detalle técnico solo en el log interno del servidor (sin exponer credenciales en pantalla)
        if ($lastException) {
            error_log('[DB::conectar][' . $this->entornoActual . '] ' . $lastException->getMessage());
        }

        $mensajeSeguro = 'No se pudo establecer la conexión con la base de datos del sistema. Verifique que el servicio MySQL esté activo.';

        // Si la petición espera JSON (controladores AJAX), devolver JSON válido sin filtrar datos sensibles
        $isAjaxOrController = (
            (!empty($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/controladores/') !== false) ||
            (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        );

        if ($isAjaxOrController) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            die(json_encode([
                'status'  => 'error',
                'mensaje' => $mensajeSeguro,
                'message' => $mensajeSeguro
            ]));
        }

        die($mensajeSeguro);
    }

    private function asegurarEsquema(PDO $pdo) {
        if (!empty($_SESSION['esquema_db_verificado_v2'])) {
            return;
        }
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `tipo_impuesto` (
                `id_tipo_impuesto` int(11) NOT NULL,
                PRIMARY KEY (`id_tipo_impuesto`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
            $pdo->exec("INSERT IGNORE INTO `tipo_impuesto` (`id_tipo_impuesto`) VALUES (1), (2), (3)");

            $pdo->exec("DROP TABLE IF EXISTS `proveedor`");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `proveedores` (
                `id_proveedor_` int(11) NOT NULL AUTO_INCREMENT,
                `id_ciudad` int(11) NOT NULL DEFAULT 1,
                `nombre_apellido_prov` varchar(100) NOT NULL,
                `razon_social` varchar(100) NOT NULL,
                `telefono_proveedor` varchar(30) NOT NULL DEFAULT '',
                `ruc_proveedor` varchar(30) NOT NULL,
                `direccion_proveedor` varchar(150) NOT NULL DEFAULT '',
                `email_proveedor` varchar(100) NOT NULL DEFAULT '',
                `estado` varchar(15) NOT NULL DEFAULT 'ACTIVO',
                PRIMARY KEY (`id_proveedor_`),
                KEY `ciudades_proveedores_fk` (`id_ciudad`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `compra` (
                `id_compra` int(11) NOT NULL AUTO_INCREMENT,
                `id_orden_compra` int(11) NOT NULL DEFAULT 0,
                `id_proveedor_` int(11) NOT NULL,
                `fecha` date NOT NULL,
                `condicion` int(11) NOT NULL DEFAULT 1,
                `nro_factura` varchar(50) NOT NULL,
                `nro_timbrado` varchar(50) NOT NULL DEFAULT '0',
                `fecha_vencimiento_timbrado` date NOT NULL,
                `estado` varchar(50) NOT NULL DEFAULT 'ACTIVO',
                `forma_de_pago` varchar(50) NOT NULL DEFAULT '',
                `metodo_pago` varchar(30) NOT NULL DEFAULT 'EFECTIVO',
                `cantidad_cuotas` int(11) NOT NULL DEFAULT 1,
                `iva5` int(11) NOT NULL DEFAULT 0,
                `iva10` int(11) NOT NULL DEFAULT 0,
                `exenta` int(11) NOT NULL DEFAULT 0,
                `total` int(11) NOT NULL DEFAULT 0,
                `id_sucursal` int(11) NOT NULL DEFAULT 1,
                `idusuario` int(11) DEFAULT 1,
                PRIMARY KEY (`id_compra`),
                KEY `proveedores_Compra_fk` (`id_proveedor_`),
                KEY `orden_compra_Compra_fk` (`id_orden_compra`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `compras_detalle` (
                `id_compra` int(11) NOT NULL,
                `id_producto` int(11) NOT NULL,
                `tipo_item` varchar(20) NOT NULL DEFAULT 'PRODUCTO',
                `cantidad` int(11) NOT NULL,
                `costo_unitario` int(11) NOT NULL,
                `iva_10` decimal(10,0) NOT NULL DEFAULT 0,
                `id_detalle_compra` int(11) NOT NULL DEFAULT 1,
                `iva_5` decimal(10,0) NOT NULL DEFAULT 0,
                `exenta` decimal(10,0) NOT NULL DEFAULT 0,
                `id_sucursal` int(11) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id_compra`,`id_detalle_compra`,`id_producto`),
                KEY `productos_compras_detalle_fk` (`id_producto`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $colTipoCli = $pdo->query("SHOW COLUMNS FROM `clientes` LIKE 'tipo_cliente'")->fetch();
            if (!$colTipoCli) {
                $pdo->exec("ALTER TABLE `clientes` ADD COLUMN `tipo_cliente` varchar(30) NOT NULL DEFAULT 'MAYORISTA' AFTER `direccion_cliente`");
            }

            $colMetPago = $pdo->query("SHOW COLUMNS FROM `compra` LIKE 'metodo_pago'")->fetch();
            if (!$colMetPago) {
                $pdo->exec("ALTER TABLE `compra` ADD COLUMN `metodo_pago` varchar(30) NOT NULL DEFAULT 'EFECTIVO' AFTER `forma_de_pago`");
            }

            $colPermisos = $pdo->query("SHOW COLUMNS FROM `usuarios` LIKE 'permisos_json'")->fetch();
            if (!$colPermisos) {
                $pdo->exec("ALTER TABLE `usuarios` ADD COLUMN `permisos_json` TEXT DEFAULT NULL");
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['esquema_db_verificado_v2'] = true;
            }
        } catch (Exception $e) {
            // Ignorar silenciosamente si algún chequeo menor falla
        }
    }
}
