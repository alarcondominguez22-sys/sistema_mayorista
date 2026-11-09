-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-11-2026 a las 23:50:21
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_mayorista`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ajuste_stock`
--

CREATE TABLE `ajuste_stock` (
  `id_ajuste_stock` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `tipo_ajuste` varchar(100) NOT NULL,
  `fecha_ajuste` date NOT NULL,
  `motiv_ajust` varchar(30) DEFAULT NULL,
  `id_sucursal` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ajuste_stock`
--

INSERT INTO `ajuste_stock` (`id_ajuste_stock`, `idusuario`, `tipo_ajuste`, `fecha_ajuste`, `motiv_ajust`, `id_sucursal`) VALUES
(1, 1, 'NEGATIVO', '2026-09-24', 'ROTURA', 2),
(2, 1, 'NEGATIVO', '2026-10-19', 'VENCIMIENTO', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apertura_cierre_caja`
--

CREATE TABLE `apertura_cierre_caja` (
  `id_apertura_cierre_caja` int(11) NOT NULL,
  `fecha_apertura` date NOT NULL,
  `monto_apertura` int(11) NOT NULL,
  `fecha_cierre` date NOT NULL,
  `ingreso_efectivo` int(11) NOT NULL,
  `ingreso_cheque` int(11) NOT NULL,
  `ingreso_tarjeta` int(11) NOT NULL,
  `monto_cierre` int(11) NOT NULL,
  `hora_apertura` time NOT NULL,
  `hora_cierre` time NOT NULL,
  `idusuario` int(11) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `saldo_caja_vuelto` int(11) DEFAULT 0,
  `saldo_transferencia_apertura` int(11) DEFAULT 0,
  `saldo_qr_apertura` int(11) DEFAULT 0,
  `ingreso_transferencia` int(11) DEFAULT 0,
  `ingreso_qr` int(11) DEFAULT 0,
  `gastos` int(11) DEFAULT 0,
  `id_sucursal` int(11) DEFAULT NULL,
  `cierre_caja_chica` int(11) DEFAULT 0,
  `cierre_caja_vuelto` int(11) DEFAULT 0,
  `cierre_transferencia` int(11) DEFAULT 0,
  `cierre_qr` int(11) DEFAULT 0,
  `movimientos_cierre_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `apertura_cierre_caja`
--

INSERT INTO `apertura_cierre_caja` (`id_apertura_cierre_caja`, `fecha_apertura`, `monto_apertura`, `fecha_cierre`, `ingreso_efectivo`, `ingreso_cheque`, `ingreso_tarjeta`, `monto_cierre`, `hora_apertura`, `hora_cierre`, `idusuario`, `estado`, `saldo_caja_vuelto`, `saldo_transferencia_apertura`, `saldo_qr_apertura`, `ingreso_transferencia`, `ingreso_qr`, `gastos`, `id_sucursal`, `cierre_caja_chica`, `cierre_caja_vuelto`, `cierre_transferencia`, `cierre_qr`, `movimientos_cierre_json`) VALUES
(1, '2026-07-29', 50000, '2026-07-29', 0, 0, 0, 114000, '00:00:00', '00:00:00', 1, 'CERRADA', 50000, 50000, 0, 0, 0, 0, 2, 50000, 14000, 50000, 0, NULL),
(2, '2026-08-01', 100000, '2026-08-01', 185000, 0, 0, 455000, '00:00:00', '00:00:00', 1, 'CERRADA', 100000, 100000, 0, 0, 0, 30000, 2, 100000, 255000, 100000, 0, NULL),
(3, '2026-08-03', 0, '2026-08-03', 585000, 0, 0, 2944557, '00:00:00', '00:00:00', 1, 'CERRADA', 982500, 1309057, 0, 120000, 0, 52000, 2, 0, 1515500, 1429057, 0, NULL),
(4, '2026-08-04', 1700000, '2026-08-12', 0, 0, 0, 2440318, '00:00:00', '00:00:00', 1, 'CERRADA', 612500, 634, 127184, 0, 0, 0, 2, 1700000, 612500, 634, 127184, NULL),
(5, '2026-08-17', 1153000, '2026-08-17', 465000, 0, 0, 3692500, '00:00:00', '00:00:00', 1, 'CERRADA', 451500, 777000, 815000, 120000, 0, 89000, 2, 1084000, 896500, 897000, 815000, NULL),
(6, '2026-08-24', 2484000, '2026-08-26', 240000, 0, 0, 4434500, '00:00:00', '00:00:00', 1, 'CERRADA', 366500, 125000, 97000, 1136000, 0, 14000, 2, 2484000, 606500, 1247000, 97000, NULL),
(7, '2026-08-26', 2915000, '2026-08-26', 290000, 0, 0, 7283500, '00:00:00', '00:00:00', 1, 'CERRADA', 539500, 2421000, 1008000, 210000, 0, 100000, 2, 2915000, 829500, 2531000, 1008000, NULL),
(8, '2026-08-26', 1915000, '2026-08-26', 325000, 0, 0, 4743500, '00:00:00', '00:00:00', 1, 'CERRADA', 529500, 2531000, 1008000, 150000, 0, 1715000, 2, 1915000, 746500, 1074000, 1008000, NULL),
(9, '2026-09-30', 50000, '2026-10-22', 1000000, 0, 0, 1150000, '00:00:00', '00:00:00', 1, 'CERRADA', 50000, 50000, 0, 0, 0, 0, 2, 50000, 1050000, 50000, 0, NULL),
(10, '2026-10-22', 940000, '2026-10-22', 0, 0, 0, 0, '00:00:00', '00:00:00', 1, 'CERRADA', 376500, 1080000, 446000, 0, 0, 0, 2, 0, 0, 0, 0, NULL),
(11, '2026-10-22', 940000, '2026-10-23', 1252000, 0, 0, 4044500, '00:00:00', '13:45:00', 1, 'CERRADA', 376500, 1080000, 446000, 235000, 0, 285000, 2, 1795000, 678500, 1125000, 446000, '[{\"origen\":\"VUELTO\",\"destino\":\"CHICA\",\"origen_nombre\":\"Vuelto en Caja\",\"destino_nombre\":\"Caja Chica\",\"monto\":950000,\"motivo\":\"GANANCIAS SOBRANTES\"}]'),
(12, '2026-10-23', 1795000, '2026-10-23', 565002, 0, 0, 4769502, '13:55:00', '14:32:00', 1, 'CERRADA', 678500, 1125000, 446000, 160000, 0, 0, 2, 2295000, 743502, 1285000, 446000, '[{\"origen\":\"VUELTO\",\"destino\":\"CHICA\",\"origen_nombre\":\"Vuelto en Caja\",\"destino_nombre\":\"Caja Chica\",\"monto\":500000,\"motivo\":\"SOBRANTE\"}]'),
(13, '2026-10-23', 2295000, '2026-10-24', 155000, 0, 0, 4612500, '14:34:00', '12:42:00', 1, 'CERRADA', 743500, 1285000, 446000, 35000, 0, 347000, 2, 1948000, 898500, 1320000, 446000, '[]'),
(14, '2026-10-24', 1948000, '2026-10-26', 257000, 0, 0, 3955500, '12:54:00', '14:09:00', 1, 'CERRADA', 898500, 1320000, 446000, 0, 0, 914000, 2, 1768000, 801500, 940000, 446000, '[{\"origen\":\"VUELTO\",\"destino\":\"CHICA\",\"origen_nombre\":\"Vuelto en Caja\",\"destino_nombre\":\"Caja Chica\",\"monto\":320000,\"motivo\":\"Traspaso de fondos al cierre\"}]'),
(15, '2026-10-26', 1768000, '2026-10-29', 495000, 0, 0, 6126500, '14:18:00', '12:01:00', 1, 'CERRADA', 801500, 940000, 446000, 1909000, 0, 233000, 2, 2295000, 536500, 2849000, 446000, '[{\"origen\":\"VUELTO\",\"destino\":\"CHICA\",\"origen_nombre\":\"Vuelto en Caja\",\"destino_nombre\":\"Caja Chica\",\"monto\":640000,\"motivo\":\"Traspaso de fondos al cierre\"},{\"origen\":\"VUELTO\",\"destino\":\"CHICA\",\"origen_nombre\":\"Vuelto en Caja\",\"destino_nombre\":\"Caja Chica\",\"monto\":50000,\"motivo\":\"Traspaso de fondos al cierre\"},{\"origen\":\"VUELTO\",\"destino\":\"CHICA\",\"origen_nombre\":\"Vuelto en Caja\",\"destino_nombre\":\"Caja Chica\",\"monto\":10000,\"motivo\":\"Traspaso de fondos al cierre\"}]'),
(16, '2026-10-29', 2295000, '2026-10-29', 323000, 0, 0, 6709500, '09:56:00', '12:04:00', 1, 'CERRADA', 536500, 2849000, 446000, 190000, 70000, 0, 2, 2295000, 859500, 3039000, 516000, '[]'),
(17, '2026-10-29', 2295000, '2026-10-29', 430000, 0, 0, 6130000, '12:06:00', '14:38:00', 1, 'CERRADA', 859500, 3039000, 516000, 205000, 45000, 1259500, 2, 2585000, 867000, 2072000, 606000, '[{\"origen\":\"VUELTO\",\"destino\":\"QR\",\"origen_nombre\":\"Vuelto en Caja\",\"destino_nombre\":\"QR (MANGO)\",\"monto\":45000,\"motivo\":\"VENTA MAL CARGADA\"},{\"origen\":\"VUELTO\",\"destino\":\"CHICA\",\"origen_nombre\":\"Vuelto en Caja\",\"destino_nombre\":\"Caja Chica\",\"monto\":300000,\"motivo\":\"Traspaso de fondos al cierre\"}]'),
(18, '2026-10-29', 2585000, '2026-10-30', 165000, 0, 0, 6246000, '14:40:00', '12:00:00', 1, 'CERRADA', 867000, 2072000, 606000, 0, 0, 49000, 2, 3128000, 440000, 2072000, 606000, '[{\"origen\":\"VUELTO\",\"destino\":\"CHICA\",\"origen_nombre\":\"Vuelto en Caja\",\"destino_nombre\":\"Caja Chica\",\"monto\":580000,\"motivo\":\"Traspaso de fondos al cierre\"}]'),
(19, '2026-10-29', 1657000, '1000-01-01', 50000, 0, 0, 0, '14:43:00', '00:00:00', 1, 'ABIERTA', 515000, 1337000, 0, 0, 0, 84000, 1, 0, 0, 0, 0, NULL),
(20, '2026-10-30', 3128000, '1000-01-01', 0, 0, 0, 0, '12:15:00', '00:00:00', 1, 'ABIERTA', 440000, 2072000, 606000, 0, 0, 1682875, 2, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nombre`, `descripcion`) VALUES
(2, 'Accesorios ', 'Accesorios '),
(3, 'Equipos', 'Equipos'),
(4, 'MUEBLES Y EQUIPOS', 'MUEBLES Y EQUIPOS'),
(5, 'SERVICIOS', 'SERVICIOS'),
(6, 'Repuestos', 'Repuestos'),
(7, 'Informßtica', 'Informßtica'),
(8, 'Televisores', 'Televisores'),
(9, 'Tablets', 'Tablets'),
(10, 'Notebooks', 'Notebooks'),
(11, 'Celulares', 'Celulares');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categorias` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categorias`, `descripcion`, `estado`) VALUES
(2, 'Accesorios ', 'ACTIVO'),
(3, 'Equipos', 'ACTIVO'),
(4, 'MUEBLES Y EQUIPOS', 'ACTIVO'),
(5, 'SERVICIOS', 'ACTIVO'),
(6, 'Repuestos', 'ACTIVO'),
(7, 'Informßtica', 'ACTIVO'),
(8, 'Televisores', 'ACTIVO'),
(9, 'Tablets', 'ACTIVO'),
(10, 'Notebooks', 'ACTIVO'),
(11, 'Celulares', 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudades`
--

CREATE TABLE `ciudades` (
  `id_ciudad` int(11) NOT NULL,
  `nombre_ciudad` varchar(100) NOT NULL,
  `estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ciudades`
--

INSERT INTO `ciudades` (`id_ciudad`, `nombre_ciudad`, `estado`) VALUES
(1, 'CAPIATA', 'ACTIVO'),
(2, 'ITAUGUA', 'ACTIVO'),
(5, 'ASUNCION', 'ACTIVO'),
(6, 'SAN LORENZO', 'ACTIVO'),
(7, 'AREGUA', 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `id_ciudad` int(11) NOT NULL,
  `nombre_cliente` varchar(50) NOT NULL,
  `apellido_cliente` varchar(50) NOT NULL,
  `cedula_cliente` varchar(20) NOT NULL,
  `telefono_cliente` varchar(30) NOT NULL DEFAULT '',
  `direccion_cliente` varchar(250) NOT NULL,
  `tipo_cliente` varchar(30) NOT NULL DEFAULT 'MAYORISTA',
  `estado` varchar(10) DEFAULT 'ACTIVO',
  `correo` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `id_ciudad`, `nombre_cliente`, `apellido_cliente`, `cedula_cliente`, `telefono_cliente`, `direccion_cliente`, `tipo_cliente`, `estado`, `correo`, `password`) VALUES
(1, 1, 'VICTORIA', 'ALARCON', '4048095', '0994848359', 'CALLE AMISTAD 5TA COMPAÑIA ', 'MAYORISTA', 'ACTIVO', NULL, NULL),
(2, 1, 'JAQUELIN', 'ALARCON', '7692015', '0991895140', 'CALLE AMISTAD 5TA COMPAÑIA ', 'MAYORISTA', 'ACTIVO', NULL, NULL),
(3, 1, 'JOSE', 'VARGAS', '6169904', '0991791457', 'Avda Lopes Godoy', 'MAYORISTA', 'ACTIVO', NULL, NULL),
(4, 7, 'PASTOR', 'LLANES', '6571299', '0982508794', '', 'MAYORISTA', 'ACTIVO', NULL, NULL),
(5, 1, 'OLGA ', 'FERNANDEZ', '21829764', '0983133757', '', 'MAYORISTA', 'ACTIVO', NULL, NULL),
(10, 1, 'Yenifer', 'Dominguez', '8024151', '0991869868', 'Avda LopeZ Godoy', 'MAYORISTA', 'ACTIVO', NULL, NULL),
(11, 1, 'JOHN', 'BENEGAS', '8059048', '0994848359', 'Avda LopeZ Godoy', 'MAYORISTA', 'ACTIVO', NULL, NULL),
(12, 1, 'Erica', 'Arias', '4678110-0', '0986281844', 'Avda Lopez Godoy', 'MAYORISTA', 'ACTIVO', 'ericaarias18@gmail.com', '$2y$10$WyI/UEN6vF4MCgTGNFiELO5Gl1SqdnyQ8OymHUC07UYPQrAyrhqRO'),
(13, 1, 'SOLEDAD ', 'SPAINI', '5296623', '0984667870', 'BARRIO SAN MIGUEL 5TA COMPAÑIA YATAITY', 'MAYORISTA', 'ACTIVO', NULL, NULL),
(14, 1, 'SIN ', 'NOMBRE', '1234567', '0976696819', 'AVDA LOPEZ GODOY CALLE AMISTAD', 'MAYORISTA', 'ACTIVO', NULL, NULL),
(15, 1, 'REPAIR SHOP', '', '5736771-0', '0983133757', '', 'CLIENTE FIEL', 'ACTIVO', '', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cobros`
--

CREATE TABLE `cobros` (
  `id_cobro` int(11) NOT NULL,
  `id_apertura_cierre_caja` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `total_cobrado` int(11) NOT NULL,
  `idusuario` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cobros`
--

INSERT INTO `cobros` (`id_cobro`, `id_apertura_cierre_caja`, `fecha`, `total_cobrado`, `idusuario`) VALUES
(58, 19, '2026-10-30', 50000, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cobros_detalle`
--

CREATE TABLE `cobros_detalle` (
  `id_cuenta_cobrar` int(11) NOT NULL,
  `id_cobro` int(11) NOT NULL,
  `metodos_pago` int(11) NOT NULL,
  `monto_pagado` int(11) NOT NULL,
  `forma_pago` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cobros_detalle`
--

INSERT INTO `cobros_detalle` (`id_cuenta_cobrar`, `id_cobro`, `metodos_pago`, `monto_pagado`, `forma_pago`) VALUES
(74, 58, 1, 50000, 'EFECTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra`
--

CREATE TABLE `compra` (
  `id_compra` int(11) NOT NULL,
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
  `idusuario` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compra`
--

INSERT INTO `compra` (`id_compra`, `id_orden_compra`, `id_proveedor_`, `fecha`, `condicion`, `nro_factura`, `nro_timbrado`, `fecha_vencimiento_timbrado`, `estado`, `forma_de_pago`, `metodo_pago`, `cantidad_cuotas`, `iva5`, `iva10`, `exenta`, `total`, `id_sucursal`, `idusuario`) VALUES
(2, 0, 2, '2026-07-29', 1, '001-001-0000000', '18216563', '2027-02-09', 'ANULADO', '2026-07-29', 'EFECTIVO', 1, 0, 59000, 0, 59000, 2, 1),
(4, 0, 1, '2026-08-01', 1, '001-001-0000002', '18216563', '2027-02-09', 'ANULADO', '2026-08-01', 'EFECTIVO', 1, 0, 1396000, 0, 1396000, 2, 1),
(5, 0, 2, '2026-10-23', 1, '001-001-0000177', '18216563', '0000-00-00', 'ACTIVO', '2026-10-23', 'EFECTIVO', 1, 0, 69000, 0, 69000, 2, 1),
(6, 0, 2, '2026-10-23', 1, '002-001-0000001', '15165165', '0000-00-00', 'ACTIVO', '2026-10-23', 'EFECTIVO', 1, 0, 78000, 0, 78000, 2, 1),
(7, 0, 1, '2026-10-23', 1, '001-001-0000001', '18216563', '2026-10-08', 'ACTIVO', '2026-10-23', 'EFECTIVO', 1, 0, 104000, 0, 104000, 2, 1),
(8, 0, 1, '2026-10-27', 1, '002-001-00000178', '18216563', '2027-10-27', 'ACTIVO', '2026-10-27', 'EFECTIVO', 1, 0, 960693, 0, 960693, 2, 1),
(9, 0, 2, '2026-10-31', 1, '001-001-0000123', '18909654', '2027-10-31', 'ACTIVO', '2026-10-31', 'EFECTIVO', 1, 0, 72000, 0, 72000, 2, 1),
(10, 0, 1, '2026-11-05', 1, '001-001-0020121', '16543210', '2026-12-31', 'ACTIVO', '2026-11-05', 'EFECTIVO', 1, 0, 3400636, 0, 37407000, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras_detalle`
--

CREATE TABLE `compras_detalle` (
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `tipo_item` varchar(20) NOT NULL DEFAULT 'PRODUCTO',
  `cantidad` int(11) NOT NULL,
  `costo_unitario` int(11) NOT NULL,
  `iva_10` decimal(10,0) NOT NULL DEFAULT 0,
  `id_detalle_compra` int(11) NOT NULL DEFAULT 1,
  `iva_5` decimal(10,0) NOT NULL DEFAULT 0,
  `exenta` decimal(10,0) NOT NULL DEFAULT 0,
  `id_sucursal` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras_detalle`
--

INSERT INTO `compras_detalle` (`id_compra`, `id_producto`, `tipo_item`, `cantidad`, `costo_unitario`, `iva_10`, `id_detalle_compra`, `iva_5`, `exenta`, `id_sucursal`) VALUES
(2, 35, 'PRODUCTO', 1, 36000, 36000, 0, 0, 0, 2),
(2, 36, 'PRODUCTO', 1, 23000, 23000, 0, 0, 0, 2),
(4, 15, 'PRODUCTO', 1, 41300, 41300, 0, 0, 0, 2),
(4, 16, 'PRODUCTO', 2, 67600, 135200, 0, 0, 0, 2),
(4, 17, 'PRODUCTO', 1, 156900, 156900, 0, 0, 0, 2),
(4, 18, 'PRODUCTO', 1, 148900, 148900, 0, 0, 0, 2),
(4, 19, 'PRODUCTO', 1, 171200, 171200, 0, 0, 0, 2),
(4, 20, 'PRODUCTO', 1, 94200, 94200, 0, 0, 0, 2),
(4, 21, 'PRODUCTO', 1, 94200, 94200, 0, 0, 0, 2),
(4, 22, 'PRODUCTO', 1, 62300, 62300, 0, 0, 0, 2),
(4, 23, 'PRODUCTO', 1, 94800, 94800, 0, 0, 0, 2),
(4, 24, 'PRODUCTO', 1, 95000, 95000, 0, 0, 0, 2),
(4, 25, 'PRODUCTO', 7, 4000, 28000, 0, 0, 0, 2),
(4, 26, 'PRODUCTO', 2, 17800, 35600, 0, 0, 0, 2),
(4, 27, 'PRODUCTO', 2, 16500, 33000, 0, 0, 0, 2),
(4, 28, 'PRODUCTO', 2, 11100, 22200, 0, 0, 0, 2),
(4, 29, 'PRODUCTO', 2, 20300, 40600, 0, 0, 0, 2),
(4, 30, 'PRODUCTO', 2, 17400, 34800, 0, 0, 0, 2),
(4, 31, 'PRODUCTO', 2, 4000, 8000, 0, 0, 0, 2),
(4, 32, 'PRODUCTO', 3, 4000, 12000, 0, 0, 0, 2),
(4, 33, 'PRODUCTO', 4, 4000, 16000, 0, 0, 0, 2),
(4, 34, 'PRODUCTO', 5, 4000, 20000, 0, 0, 0, 2),
(4, 37, 'PRODUCTO', 2, 5900, 11800, 0, 0, 0, 2),
(4, 38, 'PRODUCTO', 5, 4000, 20000, 0, 0, 0, 2),
(5, 362, 'PRODUCTO', 2, 21000, 42000, 0, 0, 0, 2),
(5, 989, 'PRODUCTO', 1, 27000, 27000, 0, 0, 0, 2),
(6, 23, 'REPUESTO', 1, 78000, 78000, 0, 0, 0, 2),
(7, 9, 'REPUESTO', 1, 100000, 100000, 0, 0, 0, 2),
(7, 31, 'PRODUCTO', 1, 4000, 4000, 0, 0, 0, 2),
(8, 28, 'REPUESTO', 1, 15200, 15200, 0, 0, 0, 2),
(8, 169, 'PRODUCTO', 3, 8075, 24225, 0, 0, 0, 2),
(8, 745, 'PRODUCTO', 3, 23275, 69825, 0, 0, 0, 2),
(8, 752, 'PRODUCTO', 1, 62700, 62700, 0, 0, 0, 2),
(8, 922, 'PRODUCTO', 1, 65280, 65280, 0, 0, 0, 2),
(8, 993, 'PRODUCTO', 1, 32300, 32300, 0, 0, 0, 2),
(8, 994, 'PRODUCTO', 4, 64390, 257560, 0, 0, 0, 2),
(8, 995, 'PRODUCTO', 2, 19000, 38000, 0, 0, 0, 2),
(8, 996, 'PRODUCTO', 2, 17100, 34200, 0, 0, 0, 2),
(8, 997, 'PRODUCTO', 3, 11875, 35625, 0, 0, 0, 2),
(8, 998, 'PRODUCTO', 1, 6650, 6650, 0, 0, 0, 2),
(8, 999, 'PRODUCTO', 2, 6650, 13300, 0, 0, 0, 2),
(8, 1000, 'PRODUCTO', 3, 4275, 12825, 0, 0, 0, 2),
(8, 1001, 'PRODUCTO', 1, 84480, 84480, 0, 0, 0, 2),
(8, 1002, 'PRODUCTO', 3, 26125, 78375, 0, 0, 0, 2),
(8, 1003, 'PRODUCTO', 3, 15200, 45600, 0, 0, 0, 2),
(8, 1004, 'PRODUCTO', 2, 19000, 38000, 0, 0, 0, 2),
(8, 1005, 'PRODUCTO', 3, 7916, 23748, 0, 0, 0, 2),
(8, 1006, 'PRODUCTO', 3, 7600, 22800, 0, 0, 0, 2),
(9, 30, 'REPUESTO', 1, 72000, 72000, 0, 0, 0, 2),
(10, 1232, 'PRODUCTO', 4, 753000, 3012000, 1, 0, 0, 1),
(10, 1203, 'PRODUCTO', 10, 2642500, 26425000, 2, 0, 0, 1),
(10, 1198, 'PRODUCTO', 5, 1594000, 7970000, 3, 0, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas__cobrar`
--

CREATE TABLE `cuentas__cobrar` (
  `id_cuenta_cobrar` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `monto_total` int(11) NOT NULL,
  `saldo_pendiente` int(11) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` varchar(15) NOT NULL,
  `pagos_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cuentas__cobrar`
--

INSERT INTO `cuentas__cobrar` (`id_cuenta_cobrar`, `id_venta`, `monto_total`, `saldo_pendiente`, `fecha_vencimiento`, `estado`, `pagos_json`) VALUES
(74, 73, 300000, 250000, '2027-01-28', 'PARCIAL', '[{\"id_cobro\":58,\"nro_recibo\":\"ANT-000058\",\"fecha_hora\":\"2026-10-30 19:55:07\",\"monto\":50000,\"metodo_pago\":\"EFECTIVO\",\"referencia\":\"Entrega Inicial \\/ Anticipo al facturar\",\"usuario\":\"Administrador\",\"saldo_anterior\":300000,\"saldo_restante\":250000}]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuotas`
--

CREATE TABLE `cuotas` (
  `id_cuota` int(11) NOT NULL,
  `id_cuenta_cobrar` int(11) NOT NULL,
  `numero_cuota` int(11) NOT NULL,
  `monto_cuota` decimal(10,2) NOT NULL,
  `monto_pagado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo` decimal(10,2) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'PENDIENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cuotas`
--

INSERT INTO `cuotas` (`id_cuota`, `id_cuenta_cobrar`, `numero_cuota`, `monto_cuota`, `monto_pagado`, `saldo`, `fecha_vencimiento`, `estado`) VALUES
(23, 74, 0, 50000.00, 50000.00, 0.00, '2026-10-30', 'PAGADA'),
(24, 74, 1, 83334.00, 0.00, 83334.00, '2026-11-29', 'PENDIENTE'),
(25, 74, 2, 83333.00, 0.00, 83333.00, '2026-12-29', 'PENDIENTE'),
(26, 74, 3, 83333.00, 0.00, 83333.00, '2027-01-28', 'PENDIENTE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deposito`
--

CREATE TABLE `deposito` (
  `id_deposito` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `nombre_deposito` varchar(100) NOT NULL,
  `direccion_deposito` varchar(50) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `deposito`
--

INSERT INTO `deposito` (`id_deposito`, `id_sucursal`, `nombre_deposito`, `direccion_deposito`, `stock_actual`, `estado`) VALUES
(1, 1, 'LOCAL CENTRAL', 'AVDA  LOPEZ GODOY', 1, 'ACTIVO'),
(2, 2, 'TIENDA 2', 'AVDA MCAL LOPEZ', 1, 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ajuste_stock`
--

CREATE TABLE `detalle_ajuste_stock` (
  `id_ajuste_stock` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_deposito` int(11) NOT NULL,
  `cantidad_anterior` int(11) NOT NULL,
  `cantidad_nueva` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_ajuste_stock`
--

INSERT INTO `detalle_ajuste_stock` (`id_ajuste_stock`, `id_producto`, `id_deposito`, `cantidad_anterior`, `cantidad_nueva`) VALUES
(1, 469, 2, 5, 4),
(2, 88, 2, 6, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id_gasto` int(11) NOT NULL,
  `id_apertura_cierre_caja` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `monto` int(11) NOT NULL,
  `metodo_pago` varchar(20) DEFAULT 'EFECTIVO',
  `estado` varchar(20) DEFAULT 'ACTIVO',
  `idusuario` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id_gasto`, `id_apertura_cierre_caja`, `fecha`, `descripcion`, `monto`, `metodo_pago`, `estado`, `idusuario`) VALUES
(1, 4, '2026-07-18', 'bolt de llave', 10000, 'EFECTIVO', 'ANULADO', 1),
(2, 1, '2026-07-29', 'COMPRA DE MERCADERIA', 36000, 'EFECTIVO', 'ACTIVO', 1),
(3, 2, '2026-08-01', 'ALMUERZO', 30000, 'EFECTIVO', 'ACTIVO', 1),
(4, 3, '2026-08-03', 'TRIUM', 52000, 'EFECTIVO', 'ACTIVO', 1),
(5, 5, '2026-08-17', 'ALMUERZO', 20000, 'VUELTO EN CAJA', 'ACTIVO', 1),
(6, 5, '2026-08-17', 'TRIUM', 69000, 'CAPITAL', 'ACTIVO', 1),
(7, 6, '2026-08-25', 'TRIUM', 14000, 'TRANSFERENCIA (ITAU)', 'ACTIVO', 1),
(8, 7, '2026-08-26', 'COMBUSTIBLE AUTO', 100000, 'TRANSFERENCIA (ITAU)', 'ACTIVO', 1),
(9, 8, '2026-08-26', 'TRIUM', 98000, 'VUELTO EN CAJA', 'ACTIVO', 1),
(10, 8, '2026-08-26', 'VIATICO', 10000, 'VUELTO EN CAJA', 'ACTIVO', 1),
(11, 8, '2026-08-26', 'MERCADERIA', 1607000, 'TRANSFERENCIA (ITAU)', 'ACTIVO', 1),
(12, 11, '2026-10-22', 'Display a54', 190000, 'TRANSFERENCIA (ITAU)', 'ACTIVO', 1),
(13, 11, '2026-10-22', 'Bateria ip13 pro max ', 95000, 'CAPITAL', 'ACTIVO', 1),
(14, 13, '2026-10-23', 'FACTURA COMPRA ID: 5 - NRO: 001-001-0000177', 69000, 'CAPITAL', 'ANULADO', 1),
(15, 13, '2026-10-23', 'FACTURA COMPRA ID: 6 - NRO: 002-001-0000001', 78000, 'CAPITAL', 'ANULADO', 1),
(16, 13, '2026-10-23', 'TRIUM', 150000, 'CAPITAL', 'ANULADO', 1),
(17, 13, '2026-10-23', 'TRIUM', 50000, 'CAPITAL', 'ANULADO', 1),
(18, 13, '2026-10-23', 'FACTURA COMPRA ID: 7 - NRO: 001-001-0000001', 104000, 'CAPITAL', 'ANULADO', 1),
(19, 13, '2026-10-24', 'gastos ', 347000, 'CAPITAL', 'ACTIVO', 1),
(20, 14, '2026-10-26', 'PANTALLA  PAD 2 PRO', 380000, 'TRANSFERENCIA (ITAU)', 'ACTIVO', 1),
(21, 14, '2026-10-26', 'BOLT DISPLAY ', 14000, 'VUELTO EN CAJA', 'ACTIVO', 1),
(22, 14, '2026-10-26', 'ALMUERZO', 20000, 'VUELTO EN CAJA', 'ACTIVO', 1),
(23, 14, '2026-10-26', 'SEÑA ALQUILER', 500000, 'CAPITAL', 'ACTIVO', 1),
(24, 15, '2026-10-26', 'TRIUM ', 173000, 'CAPITAL', 'ACTIVO', 1),
(25, 15, '2026-10-26', 'FALTANTE ', 60000, 'VUELTO EN CAJA', 'ACTIVO', 1),
(26, 15, '2026-10-27', 'FACTURA COMPRA ID: 8 - NRO: 002-001-00000178', 960693, 'TRANSFERENCIA (ITAU)', 'ANULADO', 1),
(27, 15, '2026-10-28', 'TRIUM', 212000, 'TRANSFERENCIA (ITAU)', 'ANULADO', 1),
(28, 17, '2026-10-29', 'TRIUM Y SUPER', 1172000, 'TRANSFERENCIA (ITAU)', 'ACTIVO', 1),
(29, 17, '2026-10-29', 'VARIOS ', 72500, 'VUELTO EN CAJA', 'ACTIVO', 1),
(30, 17, '2026-10-29', 'FALTANTE ', 10000, 'CAPITAL', 'ACTIVO', 1),
(31, 17, '2026-10-29', 'FALTANTE ', 5000, 'VUELTO EN CAJA', 'ACTIVO', 1),
(32, 19, '2026-10-29', 'LCD NOTE 11 Y VIDRIO IP11', 84000, 'CAPITAL', 'ACTIVO', 1),
(33, 18, '2026-10-30', 'TRANSFROMADOR', 37000, 'CAPITAL', 'ACTIVO', 1),
(34, 18, '2026-10-30', 'AGUA', 12000, 'VUELTO EN CAJA', 'ACTIVO', 1),
(35, 20, '2026-10-31', 'FACTURA COMPRA ID: 9 - NRO: 001-001-0000123', 72000, 'CAPITAL', 'ACTIVO', 1),
(36, 20, '2026-10-31', 'TRIUM', 10000, 'CAPITAL', 'ACTIVO', 1),
(37, 20, '2026-10-31', 'COMPRA POD Y ALMUERZO', 1600875, 'TRANSFERENCIA (ITAU)', 'ACTIVO', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id_marcas` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id_marcas`, `descripcion`, `estado`) VALUES
(1, 'REDMI', 'ACTIVO'),
(2, 'SAMSUNG', 'ACTIVO'),
(3, 'APPLE', 'ACTIVO'),
(4, 'NORMAL', 'ACTIVO'),
(5, '9D', 'ACTIVO'),
(6, 'ANTICURIOSO VIDRIO', 'ACTIVO'),
(7, 'LISO', 'ACTIVO'),
(8, 'DISEÑO', 'ACTIVO'),
(9, 'Cables', 'ACTIVO'),
(10, 'REPUESTO', 'ACTIVO'),
(11, 'ECOPOWER', 'ACTIVO'),
(12, 'HAVIT', 'ACTIVO'),
(13, 'HAYLOU', 'ACTIVO'),
(14, 'TP LINK', 'ACTIVO'),
(15, 'XIAOMI', 'ACTIVO'),
(16, 'LUO', 'ACTIVO'),
(17, 'BRILLO', 'ACTIVO'),
(18, 'UR', 'ACTIVO'),
(19, 'MOX', 'ACTIVO'),
(20, 'LELONG', 'ACTIVO'),
(21, 'MXQPRO', 'ACTIVO'),
(22, 'KRA', 'ACTIVO'),
(23, 'LIBRITO', 'ACTIVO'),
(24, 'LIFE POD', 'ACTIVO'),
(25, 'PROTECTOR CAMARA', 'ACTIVO'),
(26, 'NOKIA', 'ACTIVO'),
(27, 'MOTOROLA', 'ACTIVO'),
(28, 'KINGSTON', 'ACTIVO'),
(29, 'SANDISK', 'ACTIVO'),
(30, 'AIWA', 'ACTIVO'),
(31, 'AUDISAT', 'ACTIVO'),
(32, 'MTEK', 'ACTIVO'),
(33, 'TOSHIBA', 'ACTIVO'),
(34, 'SUN', 'ACTIVO'),
(35, 'KEEN', 'ACTIVO'),
(36, 'LENOVO', 'ACTIVO'),
(37, 'OUKITEL', 'ACTIVO'),
(38, 'TCL', 'ACTIVO'),
(39, 'ATOUCH', 'ACTIVO'),
(40, 'BLACKVIEW', 'ACTIVO'),
(41, 'CIDEA', 'ACTIVO'),
(42, 'HONOR', 'ACTIVO'),
(43, 'INFINIX', 'ACTIVO'),
(44, 'IPRO', 'ACTIVO'),
(45, 'ACER', 'ACTIVO'),
(46, 'GIGABYTE', 'ACTIVO'),
(47, 'HP', 'ACTIVO'),
(48, 'REALME', 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `precio_producto`
--

CREATE TABLE `precio_producto` (
  `id_producto` int(11) NOT NULL,
  `id_tipo_precio` int(11) NOT NULL,
  `precio` int(11) NOT NULL,
  `cantidad_minima` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `codigo_barras` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `precio_costo` decimal(10,0) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `stock_minimo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `id_categoria`, `codigo_barras`, `nombre`, `descripcion`, `precio_costo`, `stock_actual`, `stock_minimo`) VALUES
(1008, 6, 'PRD001008', 'DISPLAY LCD SAMSUNG A03 CORE / A03 TOUCH', 'Módulo display pantalla completa con touch para Samsung A03 Core / A03 Incell calidad AAA', 110000, 12, 2),
(1009, 6, 'PRD001009', 'DISPLAY LCD SAMSUNG A10S / A107 CON TOUCH', 'Pantalla completa módulo LCD Samsung Galaxy A10s A107M con touch screen', 115000, 9, 2),
(1010, 6, 'PRD001010', 'DISPLAY LCD SAMSUNG A12 / A125 CON TOUCH', 'Módulo LCD Samsung Galaxy A12 A125F calidad original con marco integrado', 125000, 18, 2),
(1011, 6, 'PRD001011', 'DISPLAY LCD SAMSUNG A14 4G / 5G CON TOUCH', 'Módulo pantalla LCD para Samsung Galaxy A14 versión 4G y 5G alta resolución', 135000, 19, 2),
(1012, 6, 'PRD001012', 'DISPLAY LCD SAMSUNG A32 4G OLED C/ HUELLA', 'Pantalla completa OLED con soporte de lector de huella digital en pantalla para Samsung A32 4G', 240000, 7, 2),
(1013, 6, 'PRD001013', 'DISPLAY LCD SAMSUNG A52 / A52S 5G OLED', 'Pantalla OLED 120Hz para Samsung Galaxy A52 y A52s 5G con touch y huella activa', 280000, 5, 2),
(1014, 6, 'PRD001014', 'DISPLAY LCD IPHONE X OLED ALTA CALIDAD', 'Pantalla completa iPhone X Super Retina OLED con soporte TrueTone y 3D Touch', 190000, 11, 2),
(1015, 6, 'PRD001015', 'DISPLAY LCD IPHONE 11 INCELL JK', 'Pantalla completa LCD Incell JK con colores vivos y touch 120Hz para Apple iPhone 11', 175000, 16, 2),
(1016, 6, 'PRD001016', 'DISPLAY LCD IPHONE 12 / 12 PRO OLED ZY', 'Módulo pantalla Super Retina XDR OLED calidad ZY para iPhone 12 y iPhone 12 Pro', 290000, 9, 2),
(1017, 6, 'PRD001017', 'DISPLAY LCD IPHONE 13 PRO MAX OLED ORIG.', 'Pantalla completa iPhone 13 Pro Max OLED ProMotion 120Hz calidad premium', 850000, 4, 2),
(1018, 6, 'PRD001018', 'DISPLAY LCD XIAOMI REDMI NOTE 11 4G OLED', 'Módulo pantalla AMOLED FHD+ para Xiaomi Redmi Note 11 versión 4G', 195000, 12, 2),
(1019, 6, 'PRD001019', 'DISPLAY LCD XIAOMI REDMI 10C / 10A TOUCH', 'Módulo pantalla display Xiaomi Redmi 10C y Redmi 10A con touch screen', 120000, 10, 2),
(1020, 6, 'PRD001020', 'DISPLAY LCD MOTOROLA MOTO G20 / G30 / G10', 'Pantalla completa display para Motorola Moto G20, G30 y G10 con marco', 115000, 14, 2),
(1021, 6, 'PRD001021', 'DISPLAY LCD MOTOROLA MOTO E20 CON TOUCH', 'Módulo display pantalla Motorola Moto E20 con digitalizador', 110000, 10, 2),
(1022, 6, 'PRD001022', 'BATERIA IPHONE 11 3110MAH ALTA CAPACIDAD', 'Batería interna de litio alta capacidad 3110 mAh para Apple iPhone 11 con chip', 65000, 25, 2),
(1023, 6, 'PRD001023', 'BATERIA IPHONE 12 / 12 PRO 2815MAH C/CHIP', 'Batería original de repuesto para iPhone 12 y 12 Pro 2815mAh 0 ciclos', 75000, 18, 2),
(1024, 6, 'PRD001024', 'BATERIA IPHONE 13 3227MAH PREMIUM', 'Batería interna para iPhone 13 3227mAh celda grado A con protección térmica', 85000, 14, 2),
(1025, 6, 'PRD001025', 'BATERIA SAMSUNG A10/A20/A30/A50 BA505ABU', 'Batería original Samsung modelo EB-BA505ABU 4000mAh para serie Galaxy A', 45000, 35, 2),
(1026, 6, 'PRD001026', 'BATERIA SAMSUNG A12/A02/A03 BA217ABY', 'Batería de repuesto Samsung Galaxy modelo EB-BA217ABY 5000mAh de larga duración', 48000, 30, 2),
(1027, 6, 'PRD001027', 'BATERIA SAMSUNG A22 5G / A32 BA426ABY', 'Batería interna para Samsung A22 5G y A32 modelo EB-BA426ABY 5000mAh', 50000, 24, 2),
(1028, 6, 'PRD001028', 'BATERIA XIAOMI REDMI NOTE 8 (BN46) 4000MAH', 'Batería para Xiaomi Redmi Note 8 y Redmi 7 modelo BN46 4000mAh', 45000, 20, 2),
(1029, 6, 'PRD001029', 'BATERIA XIAOMI REDMI NOTE 10/11 (BN5A)', 'Batería interna Xiaomi modelo BN5A 5000mAh compatible con Note 10 y Note 11', 52000, 27, 2),
(1030, 6, 'PRD001030', 'BATERIA MOTO G7 / G8 POWER (JK50) 5000MAH', 'Batería para Motorola Moto G7 Power y Moto G8 Power Lite modelo JK50 5000mAh', 48000, 18, 2),
(1031, 6, 'PRD001031', 'BATERIA MOTO G9 PLAY / E7 PLUS (MK50)', 'Batería para Motorola Moto G9 Play y E7 Plus modelo MK50 5000mAh', 50000, 19, 2),
(1032, 6, 'PRD001032', 'FICHA CARGA TIPO C SAMSUNG A02S / A03S', 'Placa conector de carga Tipo C con micrófono para Samsung Galaxy A02s y A03s', 18000, 45, 2),
(1033, 6, 'PRD001033', 'FICHA CARGA TIPO C SAMSUNG A12 SUB-PLACA', 'Módulo puerto de carga Tipo C con conector jack 3.5mm para Samsung A12 A125F', 20000, 55, 2),
(1034, 6, 'PRD001034', 'FICHA CARGA TIPO C SAMSUNG A14 4G CON IC', 'Sub-placa de carga Tipo C con circuito integrado IC para carga rápida en Samsung A14 4G', 22000, 35, 2),
(1035, 6, 'PRD001035', 'FICHA CARGA TIPO C SAMSUNG A51 SUB-PLACA', 'Placa pin de carga Tipo C con micrófono y jack para Samsung Galaxy A51 A515F', 25000, 27, 2),
(1036, 6, 'PRD001036', 'FICHA CARGA FLEX COMPLETO IPHONE 11 NEGRO', 'Cable flex con conector Lightning de carga y micrófono doble para Apple iPhone 11', 35000, 20, 2),
(1037, 6, 'PRD001037', 'FICHA CARGA FLEX IPHONE 12 / 12 PRO', 'Flex con puerto Lightning de carga original para iPhone 12 y 12 Pro', 45000, 16, 2),
(1038, 6, 'PRD001038', 'FICHA CARGA SUB-PLACA XIAOMI NOTE 9S/PRO', 'Sub placa de carga Tipo C con soporte Quick Charge para Xiaomi Redmi Note 9S / Note 9 Pro', 22000, 30, 2),
(1039, 6, 'PRD001039', 'FICHA CARGA SUB-PLACA XIAOMI NOTE 11 TIPO C', 'Módulo de carga USB-C con micrófono para Xiaomi Redmi Note 11 versión 4G', 24000, 38, 2),
(1040, 6, 'PRD001040', 'FICHA CARGA SUB-PLACA MOTO G20/G30 TIPO C', 'Placa pin de carga Tipo C original para Motorola Moto G20 y Moto G30', 20000, 30, 2),
(1041, 6, 'PRD001041', 'PIN CARGA UNIVERSAL MICRO USB V8 PACK X10', 'Pack de 10 unidades de pines de carga Micro USB V8 para soldadura en celulares y parlantes', 10000, 90, 2),
(1042, 2, 'PRD001042', 'LAMINA 9D CERAMICA MATE SAMSUNG A14 / A15', 'Protector de pantalla cerámica flexible mate antirreflejo y antihuellas para Samsung A14 / A15', 3500, 105, 2),
(1043, 2, 'PRD001043', 'LAMINA VIDRIO 9D IPHONE 11 / XR COMPLETA', 'Vidrio templado 9D full glue borde negro cobertura total para iPhone 11 y iPhone XR', 3500, 145, 2),
(1044, 2, 'PRD001044', 'LAMINA 9D PRIVACIDAD ANTIESPIA IPHONE 13/14', 'Vidrio templado antiespía con filtro de privacidad 28 grados para iPhone 13 y 14', 6000, 90, 2),
(1045, 2, 'PRD001045', 'LAMINA 9D PRIVACIDAD IPHONE 14 PRO MAX', 'Lámina antiespía privacy glass pantalla completa para iPhone 14 Pro Max y 15 Plus', 6500, 97, 2),
(1046, 2, 'PRD001046', 'LAMINA VIDRIO 9D IPHONE 15 PRO / PRO MAX', 'Lámina de cristal templado 9D de alta dureza 9H para iPhone 15 Pro y 15 Pro Max', 4500, 120, 2),
(1047, 2, 'PRD001047', 'LAMINA 9D VIDRIO SAMSUNG A54 5G / A34 5G', 'Protector 9D full coverage alta transparencia para Samsung Galaxy A54 5G y A34 5G', 3500, 95, 2),
(1048, 2, 'PRD001048', 'LAMINA CERAMICA FLEXIBLE XIAOMI NOTE 12/13', 'Lámina cerámica irrompible flexible para Xiaomi Redmi Note 12 y Redmi Note 13', 4000, 80, 2),
(1049, 2, 'PRD001049', 'LAMINA HIDROGEL HD AUTORREPARABLE PACK X5', 'Pack de 5 láminas de hidrogel autorreparable HD para plotters de corte o colocación manual', 25000, 55, 2),
(1050, 2, 'PRD001050', 'LAMINA VIDRIO 9D MOTO G23 / G13 / G53', 'Vidrio templado 9D borde negro cobertura total para serie Motorola Moto G 2023', 3500, 70, 2),
(1051, 2, 'PRD001051', 'ESTUCHE SILICONE CASE FELPA IPHONE 13/14', 'Funda silicone case con interior de microfibra de felpa suave para iPhone 13 y 14', 12000, 75, 2),
(1052, 2, 'PRD001052', 'ESTUCHE MAGSAFE REFORZADO IPHONE 14 PRO MAX', 'Funda transparente acrílica con aro magnético MagSafe integrado para iPhone 14 Pro Max', 18000, 55, 2),
(1053, 2, 'PRD001053', 'ESTUCHE MAGSAFE REFORZADO IPHONE 15 PRO MAX', 'Funda clear MagSafe con bordes antichoque reforzados para iPhone 15 Pro Max', 18000, 65, 2),
(1054, 2, 'PRD001054', 'ESTUCHE SPACE CLEAR SAMSUNG A14 / A15', 'Funda Space Clear trasera dura acrílica y bordes TPU anticaídas para Samsung A14 y A15', 10000, 90, 2),
(1055, 2, 'PRD001055', 'ESTUCHE SILICONE CASE PASTEL SAMSUNG A54 5G', 'Funda suave silicone case tonos pastel con protección de cámaras para Samsung A54 5G', 12000, 60, 2),
(1056, 2, 'PRD001056', 'ESTUCHE ARMOR CON ANILLO XIAOMI NOTE 12', 'Funda de uso rudo blindada con soporte de anillo magnético giratorio para Xiaomi Note 12', 15000, 45, 2),
(1057, 2, 'PRD001057', 'ESTUCHE LISO COLOR MOTO G22 / G32 SILICONA', 'Funda de silicona líquida suave al tacto con botones cromados para Moto G22 y Moto G32', 10000, 50, 2),
(1058, 2, 'PRD001058', 'ESTUCHE DISEÑO FASHION DAMA IPHONE 11/12', 'Funda decorada con estampado premium marmoleado y glitter para iPhone 11 y 12', 16000, 50, 2),
(1059, 2, 'PRD001059', 'ESTUCHE AIRBAG ANTICAIDA SAMSUNG A04/A04E', 'Funda transparente flexible con esquinas con cápsula de aire anti impacto para Samsung A04', 8000, 80, 2),
(1060, 7, 'PRD001060', 'MOUSE GAMER HAVIT MS1026 RGB 6400 DPI', 'Mouse gamer óptico ergonómico con iluminación RGB y 6 niveles de DPI ajustables hasta 6400', 38000, 27, 2),
(1061, 7, 'PRD001061', 'MOUSE INALAMBRICO HAVIT MS76GT SILENCIOSO', 'Mouse inalámbrico con receptor USB nano alcance 10 metros y clics silenciosos', 28000, 38, 2),
(1062, 7, 'PRD001062', 'TECLADO MECANICO HAVIT GAMER RGB OUTEMU', 'Teclado mecánico switches Outemu Blue con retroiluminación RGB configurable anti-ghosting', 135000, 14, 2),
(1063, 7, 'PRD001063', 'TECLADO + MOUSE INALAMBRICO HAVIT KB255G', 'Combo teclado español con teclado numérico y mouse inalámbrico 2.4GHz para oficina', 75000, 18, 2),
(1064, 7, 'PRD001064', 'ADAPTADOR HUB USB-C 5 EN 1 HDMI 4K + 3X USB', 'Concentrador multipuerto Tipo C con salida de video HDMI 4K, 3 puertos USB 3.0 y puerto de carga PD 87W', 68000, 22, 2),
(1065, 7, 'PRD001065', 'CABLE HDMI 2.0 4K ULTRA HD 2M MALLADO', 'Cable HDMI 2.0 de alta velocidad con conectores dorados y mallado de nylon resistente', 15000, 55, 2),
(1066, 7, 'PRD001066', 'PENDRIVE KINGSTON DATATRAVELER 64GB USB 3.2', 'Memoria USB 3.2 Gen 1 Kingston DataTraveler Exodia de 64 GB con capuchón protector', 32000, 45, 2),
(1067, 7, 'PRD001067', 'PENDRIVE SANDISK CRUISER BLADE 32GB USB 2.0', 'Pendrive compacto SanDisk Cruzer Blade de 32 GB para almacenamiento portátil', 24000, 65, 2),
(1068, 7, 'PRD001068', 'MICRO SD 64GB SANDISK ULTRA CLASE 10 C/ADAP', 'Tarjeta de memoria MicroSDXC SanDisk Ultra 64GB 100MB/s A1 Clase 10 con adaptador SD', 35000, 55, 2),
(1069, 7, 'PRD001069', 'MICRO SD 128GB KINGSTON CANVAS SELECT PLUS', 'Tarjeta Micro SD Kingston 128GB Canvas Select Plus U1 V10 para celulares y cámaras de seguridad', 55000, 38, 2),
(1070, 2, 'PRD001070', 'CARGADOR CARGA RAPIDA 20W TIPO C IPHONE/SAM', 'Cabezal cargador USB-C Power Delivery 20W de carga rápida para iPhone 11 al 15 y Samsung Galaxy', 22000, 75, 2),
(1071, 2, 'PRD001071', 'CARGADOR TURBO CHARGE 33W XIAOMI ORIGINAL', 'Cargador de pared original Xiaomi Mi 33W Wall Charger Type-A + Type-C con tecnología Turbo Charge', 35000, 45, 2),
(1072, 7, 'PRD001072', 'CARGADOR GAN 65W MULTIPUERTO 2X TYPE-C + USB', 'Cargador de tecnología Nitruro de Galio (GaN) 65W para notebooks, MacBooks y celulares de alta gama', 78000, 18, 2),
(1073, 2, 'PRD001073', 'CABLE TIPO C A TIPO C 60W CARGA RAPIDA 1M', 'Cable USB-C a USB-C de transferencia de datos y carga rápida 60W Power Delivery con refuerzo de caucho', 12000, 90, 2),
(1074, 2, 'PRD001074', 'CABLE LIGHTNING A TIPO C CARGA RAPIDA 20W', 'Cable Lightning a Type-C compatible con carga rápida de 20W para Apple iPhone y iPad', 14000, 105, 2),
(1075, 2, 'PRD001075', 'AURICULAR BLUETOOTH HAYLOU GT1/GT7 TWS', 'Auriculares True Wireless Bluetooth 5.2 con estuche de carga, micrófono integrado y baja latencia', 72000, 27, 2),
(1076, 7, 'PRD001076', 'SOPORTE METALICO ALUMINIO NOTEBOOK Y TABLET', 'Base soporte plegable de aluminio antideslizante con ajuste de ángulo y altura para laptops', 38000, 24, 2),
(1077, 8, 'PRD001077', 'TV SMART 43\" MTEK MK43FSAF FULL HD', 'Televisor Smart TV 43 pulgadas MTEK Full HD modelo MK43FSAF con Wi-Fi y HDMI', 1266000, 9, 2),
(1078, 8, 'PRD001078', 'TV SMART AIWA 32\" AW32B4SM', 'Televisor Smart TV 32 pulgadas AIWA HD modelo AW32B4SM Android TV', 941500, 14, 2),
(1079, 8, 'PRD001079', 'TV SMART AIWA 43\" AW43B4SMFL', 'Televisor Smart TV 43 pulgadas AIWA Full HD modelo AW43B4SMFL diseño frameless', 1586000, 11, 2),
(1080, 8, 'PRD001080', 'TV SMART AIWA 65\" AW65B4QFG 4K QLED GOOGLE TV', 'Televisor Smart TV 65 pulgadas AIWA 4K QLED con sistema operativo Google TV', 2901000, 5, 2),
(1081, 8, 'PRD001081', 'TV SMART AIWA 75\" AW75B4QFG QLED GOOGLE TV 4K UHD', 'Televisor Smart TV 75 pulgadas AIWA 4K UHD QLED Google TV sonido Dolby Audio', 4187000, 4, 2),
(1082, 8, 'PRD001082', 'TV SMART AUDISAT 32\" HD AD-32', 'Televisor Smart TV 32 pulgadas AUDISAT HD modelo AD-32 con sintonizador digital', 681000, 18, 2),
(1083, 8, 'PRD001083', 'TV SMART AUDISAT 40\" AD-40Q QLED', 'Televisor Smart TV 40 pulgadas AUDISAT pantalla QLED modelo AD-40Q', 981000, 12, 2),
(1084, 8, 'PRD001084', 'TV SMART AUDISAT 43\" AD-43Q QLED FHD+', 'Televisor Smart TV 43 pulgadas AUDISAT panel QLED FHD+ con apps integradas', 1111500, 11, 2),
(1085, 8, 'PRD001085', 'TV SMART AUDISAT 50\" AD-50Q2K QLED', 'Televisor Smart TV 50 pulgadas AUDISAT 2K QLED modelo AD-50Q2K', 1455500, 7, 2),
(1086, 8, 'PRD001086', 'TV SMART AUDISAT 65\" AD-65Q-GO', 'Televisor Smart TV 65 pulgadas AUDISAT pantalla gigante QLED con Google TV', 3351500, 5, 2),
(1087, 8, 'PRD001087', 'TV SMART ECOP 32\" EP-TV032 ISDB-T', 'Televisor Smart TV 32 pulgadas Ecopower EP-TV032 sintonizador digital ISDB-T', 656000, 20, 2),
(1088, 8, 'PRD001088', 'TV SMART ECOP 43\" EP-TV043 ISDB-T', 'Televisor Smart TV 43 pulgadas Ecopower EP-TV043 Full HD con Wi-Fi', 1095500, 13, 2),
(1089, 8, 'PRD001089', 'TV SMART ECOP 43\" EP-TV043G GOOGLE TV', 'Televisor Smart TV 43 pulgadas Ecopower EP-TV043G Google TV y comando de voz', 1289500, 10, 2),
(1090, 8, 'PRD001090', 'TV SMART ECOP 50\" EP-TV050 4K', 'Televisor Smart TV 50 pulgadas Ecopower EP-TV050 resolución 4K Ultra HD', 1576500, 8, 2),
(1091, 8, 'PRD001091', 'TV SMART ECOP 55\" EP-TV055 4K', 'Televisor Smart TV 55 pulgadas Ecopower EP-TV055 4K con aplicaciones streaming', 1927000, 7, 2),
(1092, 8, 'PRD001092', 'TV SMART ECOP 75\" EP-TV075 4K', 'Televisor Smart TV 75 pulgadas Ecopower EP-TV075 4K HDR cinematográfico', 3587500, 3, 2),
(1093, 8, 'PRD001093', 'TV SMART LUO 32\" LU-510', 'Televisor Smart TV 32 pulgadas LUO modelo LU-510 con Netflix y YouTube', 607000, 16, 2),
(1094, 8, 'PRD001094', 'TV SMART LUO 50\" LU-512', 'Televisor Smart TV 50 pulgadas LUO modelo LU-512 4K Ultra HD frameless', 1679500, 7, 2),
(1095, 8, 'PRD001095', 'TV SMART MTEK 65\" MK65FSAU', 'Televisor Smart TV 65 pulgadas MTEK MK65FSAU Ultra HD 4K Android TV', 3030500, 5, 2),
(1096, 8, 'PRD001096', 'TV SMART SAMSUNG 32\" UN32T4202AG', 'Televisor Smart TV 32 pulgadas Samsung UN32T4202AG con tecnología PurColor', 1290000, 11, 2),
(1097, 8, 'PRD001097', 'TV SMART SAMSUNG 43\" UN43AU7090G 4G ULTRA HD', 'Televisor Smart TV 43 pulgadas Samsung Crystal UHD UN43AU7090G 4K', 2294500, 9, 2),
(1098, 8, 'PRD001098', 'TV SMART SUN 55\"', 'Televisor Smart TV 55 pulgadas SUN 4K Ultra HD con HDMI y USB', 1917000, 7, 2),
(1099, 8, 'PRD001099', 'TV SMART TOSHIBA 43\"', 'Televisor Smart TV 43 pulgadas TOSHIBA Full HD con conectividad avanzada', 1927500, 9, 2),
(1100, 9, 'PRD001100', 'TAB KEEN 15 PRO MAX 1TB + 16GB RAM AZUL + ACC', 'Tablet KEEN 15 Pro Max 1TB memoria, 16GB RAM color azul con accesorios completos', 608000, 14, 2),
(1101, 9, 'PRD001101', 'TAB KEEN 15 PRO MAX 1TB + 16GB RAM DORADO + ACC', 'Tablet KEEN 15 Pro Max 1TB memoria, 16GB RAM color dorado con accesorios', 608000, 11, 2),
(1102, 9, 'PRD001102', 'TAB KEEN 15 PRO MAX 1TB + 16GB RAM GRIS + ACC', 'Tablet KEEN 15 Pro Max 1TB memoria, 16GB RAM color gris con estuche y accesorios', 608000, 13, 2),
(1103, 9, 'PRD001103', 'TAB KEEN 15 PRO MAX 1TB + 16GB RAM NEGRO + ACC', 'Tablet KEEN 15 Pro Max 1TB memoria, 16GB RAM color negro con teclado y stylus', 608000, 15, 2),
(1104, 9, 'PRD001104', 'TAB KEEN 17 MAX 10\" 1TB + 16GB RAM DORADO', 'Tablet KEEN 17 Max 10 pulgadas 1TB almacenamiento, 16GB RAM color dorado', 608000, 11, 2),
(1105, 9, 'PRD001105', 'TAB KEEN 17 MAX 10\" 1TB + 16GB RAM GRIS', 'Tablet KEEN 17 Max 10 pulgadas 1TB almacenamiento, 16GB RAM color gris', 608000, 12, 2),
(1106, 9, 'PRD001106', 'TAB KEEN 17 MAX 10\" 1TB + 16GB RAM NARANJA', 'Tablet KEEN 17 Max 10 pulgadas 1TB almacenamiento, 16GB RAM color naranja', 608000, 9, 2),
(1107, 9, 'PRD001107', 'TAB KEEN 17 MAX 10\" 1TB + 16GB RAM NEGRO', 'Tablet KEEN 17 Max 10 pulgadas 1TB almacenamiento, 16GB RAM color negro', 608000, 14, 2),
(1108, 9, 'PRD001108', 'TAB KEEN A83 256GB + 4GB RAM AZUL', 'Tablet económica KEEN A83 pantalla HD 256GB y 4GB RAM color azul', 294000, 22, 2),
(1109, 9, 'PRD001109', 'TAB KEEN A83 256GB + 4GB RAM NARANJA', 'Tablet económica KEEN A83 pantalla HD 256GB y 4GB RAM color naranja', 294000, 18, 2),
(1110, 9, 'PRD001110', 'TAB KEEN A83 256GB + 4GB RAM ROSADO', 'Tablet infantil KEEN A83 pantalla HD 256GB y 4GB RAM color rosado', 294000, 18, 2),
(1111, 9, 'PRD001111', 'TAB KEEN A83 256GB + 4GB RAM VERDE', 'Tablet KEEN A83 pantalla HD 256GB y 4GB RAM color verde', 294000, 18, 2),
(1112, 9, 'PRD001112', 'TAB KEEN KN90 7\" 64GB + 2 GB RAM', 'Tablet infantil compacta KEEN KN90 7 pulgadas 64GB + 2GB RAM para niños', 374500, 27, 2),
(1113, 9, 'PRD001113', 'TAB KEEN S26 ULTRA+ 1TB + 16GB RAM AZUL + ACC', 'Tablet KEEN S26 Ultra+ 1TB + 16GB RAM azul con funda, teclado y accesorios', 608000, 14, 2),
(1114, 9, 'PRD001114', 'TAB KEEN S26 ULTRA+ 1TB + 16GB RAM GRIS + ACC', 'Tablet KEEN S26 Ultra+ 1TB + 16GB RAM gris con accesorios completos', 608000, 12, 2),
(1115, 9, 'PRD001115', 'TAB KEEN S26 ULTRA+ 1TB + 16GB RAM NARANJA + ACC', 'Tablet KEEN S26 Ultra+ 1TB + 16GB RAM color naranja con accesorios', 608000, 10, 2),
(1116, 9, 'PRD001116', 'TAB KEEN S26 ULTRA+ 1TB + 16GB RAM NEGRO + ACC', 'Tablet KEEN S26 Ultra+ 1TB + 16GB RAM negro con teclado inalámbrico y funda', 608000, 15, 2),
(1117, 9, 'PRD001117', 'TAB LENOVO K11 TB-330XUP 11\" 128GB + 8GB RAM + ESTUCHE C/ TI', 'Tablet Lenovo K11 TB-330XUP 11 pulgadas 128GB + 8GB RAM con funda y teclado', 2079000, 6, 2),
(1118, 9, 'PRD001118', 'TAB LUO HOTWAV TAB R8 128GB + 4GB RAM REFORZADO', 'Tablet rugerizada uso rudo LUO Hotwav Tab R8 128GB + 4GB RAM antichoque', 695500, 11, 2),
(1119, 9, 'PRD001119', 'TAB OUKITEL RT3 PLUS 8\" 128GB + 16GB RAM AZUL', 'Tablet blindada OUKITEL RT3 Plus 8 pulgadas 128GB + 16GB RAM color azul', 1215000, 9, 2),
(1120, 9, 'PRD001120', 'TAB OUKITEL RT3 PLUS 8\" 128GB + 16GB RAM NEGRO', 'Tablet blindada OUKITEL RT3 Plus 8 pulgadas 128GB + 16GB RAM color negro militar', 1215000, 9, 2),
(1121, 9, 'PRD001121', 'TAB OUKITEL RT8 11\" LTE 256GB + 6GB RAM NEGRO + ACC', 'Tablet todoterreno OUKITEL RT8 11 pulgadas 4G LTE 256GB + 6GB RAM con correa y accesorios', 1484000, 7, 2),
(1122, 9, 'PRD001122', 'TAB SAM A11 SM-X133 WIFI 128GB + 8GB RAM', 'Tablet Samsung Galaxy Tab A11 SM-X133 Wi-Fi 128GB almacenamiento y 8GB RAM', 1138000, 11, 2),
(1123, 9, 'PRD001123', 'TAB SAM A11 SM-X135 LTE 128GB + 8GB RAM', 'Tablet Samsung Galaxy Tab A11 SM-X135 con conectividad celular 4G LTE 128GB', 1373500, 10, 2),
(1124, 9, 'PRD001124', 'TAB SAM A11 SM-X135 LTE 64GB + 4GB RAM', 'Tablet Samsung Galaxy Tab A11 SM-X135 4G LTE 64GB y 4GB RAM', 1139000, 12, 2),
(1125, 9, 'PRD001125', 'TAB SAM A11+ X236B 5G 128GB + 6GB RAM GRIS', 'Tablet Samsung Galaxy Tab A11+ modelo SM-X236B 5G 128GB + 6GB RAM color gris', 1794000, 8, 2),
(1126, 9, 'PRD001126', 'TAB SAM S10 LITE SM-X400 128GB + 6GB RAM WIFI', 'Tablet Samsung Galaxy Tab S10 Lite SM-X400 Wi-Fi 128GB pantalla AMOLED con S-Pen', 1851500, 7, 2),
(1127, 9, 'PRD001127', 'TAB TCL TAB11 WIFI 128GB + 4GB RAM', 'Tablet TCL Tab 11 pantalla 2K NXTVISION 128GB almacenamiento Wi-Fi', 1239500, 10, 2),
(1128, 9, 'PRD001128', 'TAB XIA POCO PAD X1 11,2\" WIFI 512GB + 8GB RAM', 'Tablet Xiaomi Poco Pad X1 pantalla 11.2 pulgadas 120Hz 512GB + 8GB RAM', 2333500, 7, 2),
(1129, 9, 'PRD001129', 'TAB XIA RD PAD 2 9,7\" WIFI 128GB + 4GB RAM', 'Tablet Xiaomi Redmi Pad 2 pantalla 9.7 pulgadas Wi-Fi 128GB + 4GB RAM', 1080000, 14, 2),
(1130, 9, 'PRD001130', 'TAB XIA RD PAD 2 PRO 11\" WIFI 256GB + 8GB', 'Tablet Xiaomi Redmi Pad 2 Pro pantalla 11 pulgadas 2.5K 256GB + 8GB RAM', 1996500, 9, 2),
(1131, 9, 'PRD001131', 'TAB ATOUCH KC64 KIDS 7.85\" 256GB + 1GB RAM AZUL + ACC.', 'Tablet infantil Atouch KC64 Kids 7.85 pulgadas con juegos didácticos y funda azul', 360000, 24, 2),
(1132, 9, 'PRD001132', 'TAB ATOUCH KC64 KIDS 7.85\" 256GB + 1GB ROSA + ACC.', 'Tablet infantil Atouch KC64 Kids 7.85 pulgadas funda reforzada rosa con accesorios', 360000, 24, 2),
(1133, 9, 'PRD001133', 'TAB BLACKVIEW LINK 1 64GB + 4GB RAM WIFI AZUL + ESTUCHE', 'Tablet Blackview Link 1 64GB + 4GB RAM Wi-Fi color azul con funda incluida', 504000, 14, 2),
(1134, 9, 'PRD001134', 'TAB BLACKVIEW LINK 1 64GB + 4GB RAM WIFI LILA + ESTUCHE', 'Tablet Blackview Link 1 64GB + 4GB RAM Wi-Fi color lila con estuche', 504000, 14, 2),
(1135, 9, 'PRD001135', 'TAB BLACKVIEW LINK 2 128GB + 4GB RAM WIFI AZUL + ESTUCHE', 'Tablet Blackview Link 2 pantalla HD 128GB + 4GB RAM Wi-Fi color azul', 635000, 12, 2),
(1136, 9, 'PRD001136', 'TAB BLACKVIEW LINK 8 256GB + 6GB RAM WIFI GRIS + ACCESORIC', 'Tablet Blackview Link 8 256GB almacenamiento + 6GB RAM color gris con accesorios', 1484000, 8, 2),
(1137, 9, 'PRD001137', 'TAB CIDEA C8800 ULTRA 5G LTE 10\" 1TB + 8GB NEGRO + ACCESORI', 'Tablet Cidea C8800 Ultra 10 pulgadas 5G LTE 1TB almacenamiento + 8GB RAM negro', 622500, 14, 2),
(1138, 9, 'PRD001138', 'TAB CIDEA C8800 ULTRA 5G LTE 10\" 1TB + 8GB RAM DORADO + AC', 'Tablet Cidea C8800 Ultra 10 pulgadas 5G LTE 1TB almacenamiento + 8GB RAM dorado', 622500, 11, 2),
(1139, 9, 'PRD001139', 'TAB CIDEA C8800 ULTRA 5G LTE 10\" 1TB + 8GB RAM GRIS + ACCESC', 'Tablet Cidea C8800 Ultra 10 pulgadas 5G LTE 1TB almacenamiento + 8GB RAM gris', 622500, 13, 2),
(1140, 9, 'PRD001140', 'TAB CIDEA CM10017 ULTRA 5G LTE 10\" 1TB + 8GB RAM AZUL + ACC', 'Tablet Cidea CM10017 Ultra 10 pulgadas 5G LTE 1TB + 8GB RAM color azul con teclado', 664000, 14, 2),
(1141, 9, 'PRD001141', 'TAB CIDEA CM10017 ULTRA 5G LTE 10\" 1TB + 8GB RAM GRIS + ACC.', 'Tablet Cidea CM10017 Ultra 10 pulgadas 5G LTE 1TB + 8GB RAM color gris con accesorios', 664000, 12, 2),
(1142, 9, 'PRD001142', 'TAB CIDEA CM517 AIR 5G LTE 7\" 256GB + 8GB RAM GRIS + ACC', 'Tablet Cidea CM517 Air 7 pulgadas 5G LTE 256GB + 8GB RAM color gris portátil', 461500, 16, 2),
(1143, 9, 'PRD001143', 'TAB CIDEA CM517 AIR 5G LTE 7\" 256GB + 8RAM AZUL + ACC.', 'Tablet Cidea CM517 Air 7 pulgadas 5G LTE 256GB + 8GB RAM color azul', 461500, 14, 2),
(1144, 9, 'PRD001144', 'TAB CIDEA CM517 AIR 5G LTE 7\" 256GB + 8RAM NARANJA + ACC.', 'Tablet Cidea CM517 Air 7 pulgadas 5G LTE 256GB + 8GB RAM color naranja', 461500, 11, 2),
(1145, 9, 'PRD001145', 'TAB CIDEA CM517 AIR 5G LTE 7\" 256GB + 8RAM NEGRO + ACC.', 'Tablet Cidea CM517 Air 7 pulgadas 5G LTE 256GB + 8GB RAM color negro con accesorios', 461500, 15, 2),
(1146, 9, 'PRD001146', 'TAB CIDEA CM528 5G LTE 7\" 256GB + 8GB RAM NEGRO + ACC', 'Tablet Cidea CM528 5G LTE pantalla 7 pulgadas 256GB + 8GB RAM negro', 435500, 15, 2),
(1147, 9, 'PRD001147', 'TAB CIDEA CM528 5G LTE 7\" 256GB + 8GB RAM VERDE + ACC', 'Tablet Cidea CM528 5G LTE pantalla 7 pulgadas 256GB + 8GB RAM verde', 435500, 12, 2),
(1148, 9, 'PRD001148', 'TAB CIDEA CM817 AIR 5G LTE 8\" 512GB + 8GB RAM NARANJA + ACC', 'Tablet Cidea CM817 Air 8 pulgadas 5G LTE 512GB + 8GB RAM color naranja con estuche', 462000, 14, 2),
(1149, 9, 'PRD001149', 'TAB CIDEA CM936 9\" 5G AZUL + ACC', 'Tablet Cidea CM936 pantalla 9 pulgadas 5G color azul con funda y cargador', 500500, 14, 2),
(1150, 9, 'PRD001150', 'TAB CIDEA KIDS CM10000 PLUS 10\" 5G AZUL', 'Tablet infantil Cidea CM10000 Plus 10 pulgadas 5G color azul con soporte de goma', 547500, 18, 2),
(1151, 9, 'PRD001151', 'TAB CIDEA KIDS CM10000 PLUS 10\" 5G VERDE', 'Tablet infantil Cidea CM10000 Plus 10 pulgadas 5G color verde anti golpes', 547500, 18, 2),
(1152, 9, 'PRD001152', 'TAB HONOR PAD X8a LITE 128GB + 4GB RAM 11.5\" ELN-L03', 'Tablet Honor Pad X8a Lite pantalla 11.5 pulgadas FHD 128GB + 4GB RAM ELN-L03', 1435000, 10, 2),
(1153, 9, 'PRD001153', 'TAB HONOR PAD X8B 128GB + 4GB RAM', 'Tablet Honor Pad X8b pantalla ultra delgada 128GB + 4GB RAM con audio envolvente', 1005000, 12, 2),
(1154, 9, 'PRD001154', 'TAB INFINIX XPAD 20 X1102 LTE 11\" 128GB + 4GB RAM + ESTUCHE', 'Tablet Infinix XPAD 20 modelo X1102 4G LTE 11 pulgadas 128GB + 4GB RAM con estuche', 1101500, 11, 2),
(1155, 9, 'PRD001155', 'TAB IPRO KIDS TURBO5 TURBO6 7\" 32GB + 2GB RAM', 'Tablet para niños IPRO Kids Turbo 7 pulgadas 32GB + 2GB RAM con asa anticaídas', 319500, 22, 2),
(1156, 9, 'PRD001156', 'TAB IPRO SPEED 4 DUAL LTE 32GB + 2GB RAM NEGRO + ACC', 'Tablet IPRO Speed 4 Dual SIM 4G LTE 32GB + 2GB RAM color negro con accesorios', 276000, 27, 2),
(1157, 9, 'PRD001157', 'TAB IPRO SPEED 4 SPEED 5 4G 32GB + 2GB RAM', 'Tablet IPRO Speed 5 conectividad 4G LTE 32GB almacenamiento y 2GB RAM', 437000, 18, 2),
(1158, 10, 'PRD001158', 'EQ NOTEBOOK ACER AL15-36P-32XP 128GB/8GB 15.6\"', 'Notebook Acer Aspire Lite AL15-36P-32XP Intel N100 128GB SSD 8GB RAM pantalla 15.6 pulgadas', 2341000, 8, 2),
(1159, 10, 'PRD001159', 'EQ NOTEBOOK AUDISAT X105 I5 512GB 16RAM 15,6\"', 'Notebook Audisat X105 procesador Intel Core i5 512GB SSD NVMe 16GB RAM pantalla 15.6 pulgadas FHD', 2698500, 7, 2),
(1160, 10, 'PRD001160', 'EQ NOTEBOOK GAMER GIGABYTE A16 RYZEN 7 16GB/1TB SSD 16.0\" FH', 'Notebook Gamer Gigabyte A16 procesador AMD Ryzen 7 16GB RAM 1TB SSD pantalla 16.0 pulgadas 165Hz gráfica dedicada', 9771000, 3, 2),
(1161, 10, 'PRD001161', 'EQ NOTEBOOK HP 14\" 14-DQ6102DX N150 128GB + 4GB W11', 'Notebook HP 14 pulgadas modelo 14-DQ6102DX Intel N150 128GB UFS + 4GB RAM Windows 11 Home', 1730500, 10, 2),
(1162, 10, 'PRD001162', 'EQ NOTEBOOK HP 14\" 14-DQ6105DX N150 128GB + 4GB W11', 'Notebook HP 14 pulgadas modelo 14-DQ6105DX Intel N150 128GB + 4GB RAM Windows 11 portátil ligera', 1711500, 11, 2),
(1163, 11, 'PRD001163', 'CEL HONOR MAGIC 6 LITE 5G 256GB + 8RAM GB', 'Smartphone Honor Magic 6 Lite 5G pantalla curva AMOLED 120Hz 256GB + 8GB RAM cámara 108MP', 1557500, 10, 2),
(1164, 11, 'PRD001164', 'CEL HONOR MAGIC 7 LITE 5G DUAL 512GB + 12GB RAM + LITAG REG.', 'Smartphone Honor Magic 7 Lite 5G Dual SIM 512GB + 12GB RAM incluye Litag de regalo', 1845000, 8, 2),
(1165, 11, 'PRD001165', 'CEL HONOR PLAY 20A 128GB + 4GB RAM', 'Celular Honor Play 20A pantalla 6.5 pulgadas 128GB almacenamiento + 4GB RAM batería 5000mAh', 776000, 14, 2),
(1166, 11, 'PRD001166', 'CEL HONOR PLAY 20C 64GB + 4GB RAM', 'Celular Honor Play 20C pantalla HD 64GB almacenamiento + 4GB RAM color negro', 644000, 18, 2),
(1167, 11, 'PRD001167', 'CEL HONOR X5C 128GB + 4GB RAM', 'Smartphone Honor X5C pantalla 6.56 pulgadas 128GB + 4GB RAM batería de larga duración', 715000, 16, 2),
(1168, 11, 'PRD001168', 'CEL HONOR X5C PLUS 256GB + 4GB RAM', 'Smartphone Honor X5C Plus gran almacenamiento 256GB + 4GB RAM cámara dual', 765000, 14, 2),
(1169, 11, 'PRD001169', 'CEL HONOR X5C PLUS 256GB + 4GB RAM C/ HOPPY', 'Smartphone Honor X5C Plus 256GB + 4GB RAM combo especial con hoppy térmico incluido', 822500, 12, 2),
(1170, 11, 'PRD001170', 'CEL HONOR X6B 256GB + 4GB RAM', 'Smartphone Honor X6b pantalla 90Hz 256GB + 4GB RAM cámara 50MP carga rápida 35W', 966000, 14, 2),
(1171, 11, 'PRD001171', 'CEL HONOR X7D 4G 256GB + 6GB RAM', 'Smartphone Honor X7d pantalla fluida 256GB almacenamiento + 6GB RAM procesador Snapdragon', 112000, 11, 2),
(1172, 11, 'PRD001172', 'CEL HONOR X7D 4G 256GB + 8GB RAM', 'Smartphone Honor X7d 256GB almacenamiento + 8GB RAM cámara triple con IA', 1200000, 12, 2),
(1173, 11, 'PRD001173', 'CEL SAM A07 128GB + 4GB RAM', 'Samsung Galaxy A07 pantalla 6.7 pulgadas 128GB + 4GB RAM batería 5000mAh', 917500, 14, 2),
(1174, 11, 'PRD001174', 'CEL SAM A07 64GB + 4GB RAM', 'Samsung Galaxy A07 versión 64GB + 4GB RAM pantalla HD+ 90Hz', 785000, 18, 2),
(1175, 11, 'PRD001175', 'CEL SAM A16 128GB + 4GB RAM', 'Samsung Galaxy A16 pantalla Super AMOLED FHD+ 128GB + 4GB RAM cámara 50MP', 945000, 21, 2),
(1176, 11, 'PRD001176', 'CEL SAM A17 128GB + 4GB RAM', 'Samsung Galaxy A17 pantalla Super AMOLED 128GB + 4GB RAM diseño ultra delgado', 1046500, 14, 2),
(1177, 11, 'PRD001177', 'CEL SAM A17 256GB + 8GB RAM', 'Samsung Galaxy A17 gran capacidad 256GB + 8GB RAM procesador Octa-Core', 1691500, 12, 2),
(1178, 11, 'PRD001178', 'CEL SAM A27 5G 256GB + 8GB RAM', 'Samsung Galaxy A27 conectividad 5G 256GB + 8GB RAM pantalla 120Hz con lector de huella', 1992000, 10, 2),
(1179, 11, 'PRD001179', 'CEL SAM A36 5G 128GB + 8GB RAM', 'Samsung Galaxy A36 5G resistencia IP67 128GB + 8GB RAM cámara con estabilización OIS', 1770000, 11, 2),
(1180, 11, 'PRD001180', 'CEL SAM A37 5G 128GB + 6GB RAM', 'Samsung Galaxy A37 5G pantalla Super AMOLED Plus 128GB + 6GB RAM', 1935000, 9, 2),
(1181, 11, 'PRD001181', 'CEL SAM A37 5G 256GB + 8GB RAM', 'Samsung Galaxy A37 5G versión premium 256GB + 8GB RAM sonido estéreo Dolby Atmos', 2355000, 10, 2),
(1182, 11, 'PRD001182', 'CEL SAM A56 5G 256GB + 8GB RAM', 'Samsung Galaxy A56 5G cámara profesional 50MP 256GB + 8GB RAM cuerpo de vidrio y metal', 2243000, 9, 2),
(1183, 11, 'PRD001183', 'CEL SAM A57 5G 256GB + 8GB RAM', 'Samsung Galaxy A57 5G pantalla 120Hz Vision Booster 256GB + 8GB RAM', 2544500, 7, 2),
(1184, 11, 'PRD001184', 'CEL SAM S26 256GB + 12GB RAM', 'Samsung Galaxy S26 gama alta pantalla Dynamic AMOLED 2X 256GB + 12GB RAM Snapdragon 8 Gen', 4592500, 5, 2),
(1185, 11, 'PRD001185', 'CEL SAM S26 FE 256GB + 8GB RAM', 'Samsung Galaxy S26 Fan Edition 256GB + 8GB RAM cámaras flagship y Galaxy AI', 4282000, 6, 2),
(1186, 11, 'PRD001186', 'CEL SAM S26 ULTRA DUAL 5G 256GB + 12GB RAM', 'Samsung Galaxy S26 Ultra Dual 5G con S-Pen integrado cámara 200MP zoom 100x 256GB + 12GB RAM', 5895500, 4, 2),
(1187, 11, 'PRD001187', 'CEL OUKITEL C17 128GB + 4GB RAM', 'Smartphone OUKITEL C17 pantalla perforada HD+ 128GB + 4GB RAM', 604500, 14, 2),
(1188, 11, 'PRD001188', 'CEL OUKITEL C17 PLUS 256GB + 6GB RAM', 'Smartphone OUKITEL C17 Plus mayor capacidad 256GB + 6GB RAM batería 4500mAh', 887500, 12, 2),
(1189, 11, 'PRD001189', 'CEL OUKITEL C26 128GB + 4GB RAM', 'Smartphone OUKITEL C26 pantalla fluida 128GB + 4GB RAM lector de huella', 576000, 16, 2),
(1190, 11, 'PRD001190', 'CEL OUKITEL C26 128GB + 6GB RAM', 'Smartphone OUKITEL C26 versión 128GB + 6GB RAM cámara triple con flash', 725000, 14, 2),
(1191, 11, 'PRD001191', 'CEL OUKITEL C3 256GB + 4GB RAM', 'Smartphone OUKITEL C3 pantalla 6.5 pulgadas 256GB + 4GB RAM', 630000, 14, 2),
(1192, 11, 'PRD001192', 'CEL OUKITEL C65 128GB + 4GB RAM', 'Smartphone OUKITEL C65 diseño moderno 128GB + 4GB RAM Android puro', 569500, 18, 2),
(1193, 11, 'PRD001193', 'CEL OUKITEL C68 256GB + 8GB RAM', 'Smartphone OUKITEL C68 pantalla 6.78 pulgadas 256GB + 8GB RAM 50MP', 791000, 14, 2),
(1194, 11, 'PRD001194', 'CEL OUKITEL C68 512GB + 8GB RAM', 'Smartphone OUKITEL C68 enorme memoria 512GB + 8GB RAM batería 5000mAh', 829000, 12, 2),
(1195, 11, 'PRD001195', 'CEL OUKITEL C68 PLUS 128GB + 4GB RAM', 'Smartphone OUKITEL C68 Plus pantalla FHD+ 128GB + 4GB RAM', 803500, 11, 2),
(1196, 11, 'PRD001196', 'CEL OUKITEL C68 PLUS 512GB + 8GB RAM', 'Smartphone OUKITEL C68 Plus tope de gama 512GB + 8GB RAM cámara IA', 990000, 10, 2),
(1197, 11, 'PRD001197', 'CEL OUKITEL P1 PRO 256GB + 4GB RAM', 'Smartphone OUKITEL P1 Pro pantalla AMOLED 120Hz 256GB + 4GB RAM', 1085000, 9, 2),
(1198, 11, 'PRD001198', 'CEL OUKITEL WP60 256GB + 8GB RAM', 'Celular blindado militar rugerizado OUKITEL WP60 256GB + 8GB RAM sumergible y anticaídas', 1594000, 8, 2),
(1199, 11, 'PRD001199', 'CEL REALME 12 PRO+ 512GB + 12GB RAM', 'Smartphone Realme 12 Pro+ lente periscópico cámara Sony 512GB + 12GB RAM cuero vegano', 3011500, 7, 2),
(1200, 11, 'PRD001200', 'CEL REALME C21Y 64GB + 4GB RAM', 'Smartphone Realme C21Y batería gigante 5000mAh 64GB + 4GB RAM triple cámara', 800000, 16, 2),
(1201, 11, 'PRD001201', 'CEL REALME C65 256GB + 8GB RAM', 'Smartphone Realme C65 diseño ultradelgado 256GB + 8GB RAM carga rápida 45W', 1065000, 12, 2),
(1202, 11, 'PRD001202', 'CEL REALME NOTE 60 256GB + 4GB RAM', 'Smartphone Realme Note 60 pantalla 90Hz ArmorShell 256GB + 4GB RAM', 679500, 18, 2),
(1203, 11, 'PRD001203', 'CEL XIA 12 LITE 128GB + 8GB RAM', 'Xiaomi 12 Lite pantalla AMOLED 120Hz HDR10+ 128GB + 8GB RAM cámara 108MP', 2642500, 6, 2),
(1204, 11, 'PRD001204', 'CEL XIA 12 LITE 256GB + 8GB RAM', 'Xiaomi 12 Lite versión 256GB + 8GB RAM diseño ligero color tornasol', 2877500, 7, 2),
(1205, 11, 'PRD001205', 'CEL XIA NOTE 11 PRO+ 256GB + 8GB RAM', 'Redmi Note 11 Pro+ carga ultrarrápida 120W HyperCharge 256GB + 8GB RAM sonido JBL', 2535000, 9, 2),
(1206, 11, 'PRD001206', 'CEL XIA NOTE 12 PRO 5G 128GB + 6GB RAM', 'Redmi Note 12 Pro 5G pantalla OLED 128GB + 6GB RAM cámara Sony IMX766 OIS', 2073000, 5, 2),
(1207, 11, 'PRD001207', 'CEL XIA NOTE 12 PRO+ 256GB + 8GB RAM', 'Redmi Note 12 Pro+ cámara 200MP carga 120W 256GB + 8GB RAM pantalla 120Hz', 2737000, 8, 2),
(1208, 11, 'PRD001208', 'CEL XIA NOTE 14 PRO+ 5G 256GB + 8GB RAM', 'Redmi Note 14 Pro+ 5G pantalla curva AMOLED 1.5K resistencia al agua IP68 256GB', 2160000, 10, 2),
(1209, 11, 'PRD001209', 'CEL XIA NOTE 15 128GB + 6GB RAM', 'Redmi Note 15 pantalla FHD+ AMOLED 128GB + 6GB RAM batería 5000mAh', 1168500, 16, 2),
(1210, 11, 'PRD001210', 'CEL XIA NOTE 15 256GB + 8GB RAM', 'Redmi Note 15 mayor almacenamiento 256GB + 8GB RAM cámara triple 108MP', 1375000, 18, 2),
(1211, 11, 'PRD001211', 'CEL XIA NOTE 15 PRO 256GB + 8GB RAM', 'Redmi Note 15 Pro procesador MediaTek Helio G99-Ultra 256GB + 8GB RAM carga 67W', 1605000, 14, 2),
(1212, 11, 'PRD001212', 'CEL XIA NOTE 15 PRO 5G 256GB + 8GB RAM', 'Redmi Note 15 Pro 5G conectividad de última generación 256GB + 8GB RAM', 1895000, 12, 2),
(1213, 11, 'PRD001213', 'CEL XIA NOTE 15 PRO 5G 512GB + 12GB RAM', 'Redmi Note 15 Pro 5G máxima configuración 512GB + 12GB RAM pantalla AMOLED 120Hz', 2255000, 4, 2),
(1214, 11, 'PRD001214', 'CEL XIA NOTE 17 4G 128GB + 4GB RAM', 'Redmi Note 17 4G pantalla 6.67 pulgadas 128GB + 4GB RAM batería de larga duración', 1255000, 14, 2),
(1215, 11, 'PRD001215', 'CEL XIA NOTE 17 4G 256GB + 6GB RAM', 'Redmi Note 17 4G versión 256GB + 6GB RAM cámara 108MP carga turbo 33W', 1475000, 12, 2),
(1216, 11, 'PRD001216', 'CEL XIA NOTE 17 PRO MAX 5G 256GB + 8GB RAM', 'Redmi Note 17 Pro Max 5G pantalla 144Hz 256GB + 8GB RAM cámaras Leica avanzadas', 2685000, 9, 2),
(1217, 11, 'PRD001217', 'CEL XIA NOTE 17 PRO MAX 5G 512GB + 8GB RAM', 'Redmi Note 17 Pro Max 5G versión 512GB almacenamiento + 8GB RAM carga 120W', 2985000, 7, 2),
(1218, 11, 'PRD001218', 'CEL XIA POCO F3 128GB + 6GB RAM', 'Xiaomi Poco F3 procesador Snapdragon 870 pantalla E4 AMOLED 120Hz 128GB + 6GB RAM', 2315000, 10, 2),
(1219, 11, 'PRD001219', 'CEL XIA POCO F5 256GB + 12GB RAM', 'Xiaomi Poco F5 Snapdragon 7+ Gen 2 pantalla Flow AMOLED 120Hz 256GB + 12GB RAM', 2510000, 9, 2),
(1220, 11, 'PRD001220', 'CEL XIA POCO F5 PRO 512GB + 12GB RAM', 'Xiaomi Poco F5 Pro pantalla WQHD+ 120Hz Snapdragon 8+ Gen 1 512GB + 12GB RAM carga inalámbrica', 3570000, 6, 2),
(1221, 11, 'PRD001221', 'CEL XIA POCO M4 5G 64GB + 4GB RAM', 'Xiaomi Poco M4 5G pantalla 90Hz 64GB + 4GB RAM procesador MediaTek Dimensity 700', 1263000, 14, 2),
(1222, 11, 'PRD001222', 'CEL XIA POCO M5S 128GB + 6GB RAM', 'Xiaomi Poco M5s pantalla AMOLED altavoces duales 128GB + 6GB RAM cámara cuádruple 64MP', 1245500, 14, 2),
(1223, 11, 'PRD001223', 'CEL XIA POCO M8 5G 256GB + 8GB RAM', 'Xiaomi Poco M8 5G conectividad ultra rápida 256GB + 8GB RAM batería 5000mAh', 1465000, 12, 2),
(1224, 11, 'PRD001224', 'CEL XIA POCO X4 PRO 128GB + 6GB RAM', 'Xiaomi Poco X4 Pro 5G pantalla AMOLED 120Hz 128GB + 6GB RAM cámara 108MP carga 67W', 2116500, 10, 2),
(1225, 11, 'PRD001225', 'CEL XIA POCO X4 PRO 256GB + 8GB RAM', 'Xiaomi Poco X4 Pro 5G versión 256GB + 8GB RAM diseño láser de cristal', 2391500, 9, 2),
(1226, 11, 'PRD001226', 'CEL XIA RD 10 64GB + 4GB RAM', 'Xiaomi Redmi 10 pantalla 90Hz AdaptiveSync 64GB + 4GB RAM cámara 50MP', 1290000, 14, 2),
(1227, 11, 'PRD001227', 'CEL XIA RD 15C 128GB + 4GB RAM', 'Xiaomi Redmi 15C pantalla inmersiva 6.88 pulgadas 120Hz 128GB + 4GB RAM', 828500, 21, 2),
(1228, 11, 'PRD001228', 'CEL XIA RD 15C 256GB + 8GB RAM', 'Xiaomi Redmi 15C versión 256GB almacenamiento + 8GB RAM cámara 50MP con IA', 984500, 18, 2),
(1229, 11, 'PRD001229', 'CEL XIA RD 17 4G 128GB + 4GB RAM', 'Xiaomi Redmi 17 4G pantalla fluida 128GB + 4GB RAM batería de larga duración', 1015000, 16, 2),
(1230, 11, 'PRD001230', 'CEL XIA RD 17 4G 256GB + 4GB RAM', 'Xiaomi Redmi 17 4G gran almacenamiento 256GB + 4GB RAM carga rápida USB-C', 1200000, 14, 2),
(1231, 11, 'PRD001231', 'CEL XIA RD 9T 64GB + 4GB RAM', 'Xiaomi Redmi 9T altavoces estéreo batería colosal 6000mAh 64GB + 4GB RAM', 1255000, 12, 2),
(1232, 11, 'PRD001232', 'CEL XIA RD A7 PRO 128GB + 4GB RAM', 'Xiaomi Redmi A7 Pro pantalla HD+ 128GB + 4GB RAM sensor de huellas dactilares', 753000, 17, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `id_tipo_impuesto` int(11) NOT NULL,
  `id_marcas` int(11) DEFAULT NULL,
  `tipo_producto` enum('EQUIPO','ACCESORIO') DEFAULT 'EQUIPO',
  `nombre_prod` varchar(120) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `precio_compra` int(11) NOT NULL,
  `precio_venta` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `estado` varchar(10) DEFAULT 'ACTIVO',
  `imagen_url` varchar(255) DEFAULT NULL,
  `stock_sucursal1` int(11) NOT NULL DEFAULT 0,
  `stock_sucursal2` int(11) NOT NULL DEFAULT 0,
  `id_categoria` int(11) DEFAULT NULL,
  `bloqueado` tinyint(1) NOT NULL DEFAULT 0,
  `codigo_barra` varchar(100) DEFAULT NULL,
  `fecha_bloqueo` datetime DEFAULT NULL,
  `tiene_variantes` tinyint(1) DEFAULT 0,
  `tipo_variante` varchar(20) DEFAULT 'ninguno',
  `opciones_colores` text DEFAULT NULL,
  `opciones_disenos` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `id_tipo_impuesto`, `id_marcas`, `tipo_producto`, `nombre_prod`, `descripcion`, `precio_compra`, `precio_venta`, `stock_actual`, `estado`, `imagen_url`, `stock_sucursal1`, `stock_sucursal2`, `id_categoria`, `bloqueado`, `codigo_barra`, `fecha_bloqueo`, `tiene_variantes`, `tipo_variante`, `opciones_colores`, `opciones_disenos`) VALUES
(1008, 1, 2, 'EQUIPO', 'DISPLAY LCD SAMSUNG A03 CORE / A03 TOUCH', 'Módulo display pantalla completa con touch para Samsung A03 Core / A03 Incell calidad AAA', 110000, 165000, 12, 'ACTIVO', NULL, 6, 6, 6, 0, 'PRD001008', NULL, 0, 'ninguno', NULL, NULL),
(1009, 1, 2, 'EQUIPO', 'DISPLAY LCD SAMSUNG A10S / A107 CON TOUCH', 'Pantalla completa módulo LCD Samsung Galaxy A10s A107M con touch screen', 115000, 170000, 9, 'ACTIVO', NULL, 5, 4, 6, 0, 'PRD001009', NULL, 0, 'ninguno', NULL, NULL),
(1010, 1, 2, 'EQUIPO', 'DISPLAY LCD SAMSUNG A12 / A125 CON TOUCH', 'Módulo LCD Samsung Galaxy A12 A125F calidad original con marco integrado', 125000, 185000, 18, 'ACTIVO', NULL, 10, 8, 6, 0, 'PRD001010', NULL, 0, 'ninguno', NULL, NULL),
(1011, 1, 2, 'EQUIPO', 'DISPLAY LCD SAMSUNG A14 4G / 5G CON TOUCH', 'Módulo pantalla LCD para Samsung Galaxy A14 versión 4G y 5G alta resolución', 135000, 195000, 19, 'ACTIVO', NULL, 12, 7, 6, 0, 'PRD001011', NULL, 0, 'ninguno', NULL, NULL),
(1012, 1, 2, 'EQUIPO', 'DISPLAY LCD SAMSUNG A32 4G OLED C/ HUELLA', 'Pantalla completa OLED con soporte de lector de huella digital en pantalla para Samsung A32 4G', 240000, 350000, 7, 'ACTIVO', NULL, 4, 3, 6, 0, 'PRD001012', NULL, 0, 'ninguno', NULL, NULL),
(1013, 1, 2, 'EQUIPO', 'DISPLAY LCD SAMSUNG A52 / A52S 5G OLED', 'Pantalla OLED 120Hz para Samsung Galaxy A52 y A52s 5G con touch y huella activa', 280000, 395000, 5, 'ACTIVO', NULL, 3, 2, 6, 0, 'PRD001013', NULL, 0, 'ninguno', NULL, NULL),
(1014, 1, 3, 'EQUIPO', 'DISPLAY LCD IPHONE X OLED ALTA CALIDAD', 'Pantalla completa iPhone X Super Retina OLED con soporte TrueTone y 3D Touch', 190000, 290000, 11, 'ACTIVO', NULL, 6, 5, 6, 0, 'PRD001014', NULL, 0, 'ninguno', NULL, NULL),
(1015, 1, 3, 'EQUIPO', 'DISPLAY LCD IPHONE 11 INCELL JK', 'Pantalla completa LCD Incell JK con colores vivos y touch 120Hz para Apple iPhone 11', 175000, 260000, 16, 'ACTIVO', NULL, 9, 7, 6, 0, 'PRD001015', NULL, 0, 'ninguno', NULL, NULL),
(1016, 1, 3, 'EQUIPO', 'DISPLAY LCD IPHONE 12 / 12 PRO OLED ZY', 'Módulo pantalla Super Retina XDR OLED calidad ZY para iPhone 12 y iPhone 12 Pro', 290000, 420000, 9, 'ACTIVO', NULL, 5, 4, 6, 0, 'PRD001016', NULL, 0, 'ninguno', NULL, NULL),
(1017, 1, 3, 'EQUIPO', 'DISPLAY LCD IPHONE 13 PRO MAX OLED ORIG.', 'Pantalla completa iPhone 13 Pro Max OLED ProMotion 120Hz calidad premium', 850000, 1250000, 4, 'ACTIVO', NULL, 2, 2, 6, 0, 'PRD001017', NULL, 0, 'ninguno', NULL, NULL),
(1018, 1, 15, 'EQUIPO', 'DISPLAY LCD XIAOMI REDMI NOTE 11 4G OLED', 'Módulo pantalla AMOLED FHD+ para Xiaomi Redmi Note 11 versión 4G', 195000, 280000, 12, 'ACTIVO', NULL, 7, 5, 6, 0, 'PRD001018', NULL, 0, 'ninguno', NULL, NULL),
(1019, 1, 15, 'EQUIPO', 'DISPLAY LCD XIAOMI REDMI 10C / 10A TOUCH', 'Módulo pantalla display Xiaomi Redmi 10C y Redmi 10A con touch screen', 120000, 175000, 10, 'ACTIVO', NULL, 6, 4, 6, 0, 'PRD001019', NULL, 0, 'ninguno', NULL, NULL),
(1020, 1, 27, 'EQUIPO', 'DISPLAY LCD MOTOROLA MOTO G20 / G30 / G10', 'Pantalla completa display para Motorola Moto G20, G30 y G10 con marco', 115000, 165000, 14, 'ACTIVO', NULL, 8, 6, 6, 0, 'PRD001020', NULL, 0, 'ninguno', NULL, NULL),
(1021, 1, 27, 'EQUIPO', 'DISPLAY LCD MOTOROLA MOTO E20 CON TOUCH', 'Módulo display pantalla Motorola Moto E20 con digitalizador', 110000, 160000, 10, 'ACTIVO', NULL, 5, 5, 6, 0, 'PRD001021', NULL, 0, 'ninguno', NULL, NULL),
(1022, 1, 3, 'ACCESORIO', 'BATERIA IPHONE 11 3110MAH ALTA CAPACIDAD', 'Batería interna de litio alta capacidad 3110 mAh para Apple iPhone 11 con chip', 65000, 120000, 25, 'ACTIVO', NULL, 15, 10, 6, 0, 'PRD001022', NULL, 0, 'ninguno', NULL, NULL),
(1023, 1, 3, 'ACCESORIO', 'BATERIA IPHONE 12 / 12 PRO 2815MAH C/CHIP', 'Batería original de repuesto para iPhone 12 y 12 Pro 2815mAh 0 ciclos', 75000, 135000, 18, 'ACTIVO', NULL, 10, 8, 6, 0, 'PRD001023', NULL, 0, 'ninguno', NULL, NULL),
(1024, 1, 3, 'ACCESORIO', 'BATERIA IPHONE 13 3227MAH PREMIUM', 'Batería interna para iPhone 13 3227mAh celda grado A con protección térmica', 85000, 150000, 14, 'ACTIVO', NULL, 8, 6, 6, 0, 'PRD001024', NULL, 0, 'ninguno', NULL, NULL),
(1025, 1, 2, 'ACCESORIO', 'BATERIA SAMSUNG A10/A20/A30/A50 BA505ABU', 'Batería original Samsung modelo EB-BA505ABU 4000mAh para serie Galaxy A', 45000, 85000, 35, 'ACTIVO', NULL, 20, 15, 6, 0, 'PRD001025', NULL, 0, 'ninguno', NULL, NULL),
(1026, 1, 2, 'ACCESORIO', 'BATERIA SAMSUNG A12/A02/A03 BA217ABY', 'Batería de repuesto Samsung Galaxy modelo EB-BA217ABY 5000mAh de larga duración', 48000, 90000, 30, 'ACTIVO', NULL, 18, 12, 6, 0, 'PRD001026', NULL, 0, 'ninguno', NULL, NULL),
(1027, 1, 2, 'ACCESORIO', 'BATERIA SAMSUNG A22 5G / A32 BA426ABY', 'Batería interna para Samsung A22 5G y A32 modelo EB-BA426ABY 5000mAh', 50000, 95000, 24, 'ACTIVO', NULL, 14, 10, 6, 0, 'PRD001027', NULL, 0, 'ninguno', NULL, NULL),
(1028, 1, 15, 'ACCESORIO', 'BATERIA XIAOMI REDMI NOTE 8 (BN46) 4000MAH', 'Batería para Xiaomi Redmi Note 8 y Redmi 7 modelo BN46 4000mAh', 45000, 85000, 20, 'ACTIVO', NULL, 12, 8, 6, 0, 'PRD001028', NULL, 0, 'ninguno', NULL, NULL),
(1029, 1, 15, 'ACCESORIO', 'BATERIA XIAOMI REDMI NOTE 10/11 (BN5A)', 'Batería interna Xiaomi modelo BN5A 5000mAh compatible con Note 10 y Note 11', 52000, 98000, 27, 'ACTIVO', NULL, 16, 11, 6, 0, 'PRD001029', NULL, 0, 'ninguno', NULL, NULL),
(1030, 1, 27, 'ACCESORIO', 'BATERIA MOTO G7 / G8 POWER (JK50) 5000MAH', 'Batería para Motorola Moto G7 Power y Moto G8 Power Lite modelo JK50 5000mAh', 48000, 90000, 18, 'ACTIVO', NULL, 10, 8, 6, 0, 'PRD001030', NULL, 0, 'ninguno', NULL, NULL),
(1031, 1, 27, 'ACCESORIO', 'BATERIA MOTO G9 PLAY / E7 PLUS (MK50)', 'Batería para Motorola Moto G9 Play y E7 Plus modelo MK50 5000mAh', 50000, 95000, 19, 'ACTIVO', NULL, 12, 7, 6, 0, 'PRD001031', NULL, 0, 'ninguno', NULL, NULL),
(1032, 1, 2, 'ACCESORIO', 'FICHA CARGA TIPO C SAMSUNG A02S / A03S', 'Placa conector de carga Tipo C con micrófono para Samsung Galaxy A02s y A03s', 18000, 40000, 45, 'ACTIVO', NULL, 25, 20, 6, 0, 'PRD001032', NULL, 0, 'ninguno', NULL, NULL),
(1033, 1, 2, 'ACCESORIO', 'FICHA CARGA TIPO C SAMSUNG A12 SUB-PLACA', 'Módulo puerto de carga Tipo C con conector jack 3.5mm para Samsung A12 A125F', 20000, 45000, 55, 'ACTIVO', NULL, 30, 25, 6, 0, 'PRD001033', NULL, 0, 'ninguno', NULL, NULL),
(1034, 1, 2, 'ACCESORIO', 'FICHA CARGA TIPO C SAMSUNG A14 4G CON IC', 'Sub-placa de carga Tipo C con circuito integrado IC para carga rápida en Samsung A14 4G', 22000, 50000, 35, 'ACTIVO', NULL, 20, 15, 6, 0, 'PRD001034', NULL, 0, 'ninguno', NULL, NULL),
(1035, 1, 2, 'ACCESORIO', 'FICHA CARGA TIPO C SAMSUNG A51 SUB-PLACA', 'Placa pin de carga Tipo C con micrófono y jack para Samsung Galaxy A51 A515F', 25000, 55000, 27, 'ACTIVO', NULL, 15, 12, 6, 0, 'PRD001035', NULL, 0, 'ninguno', NULL, NULL),
(1036, 1, 3, 'ACCESORIO', 'FICHA CARGA FLEX COMPLETO IPHONE 11 NEGRO', 'Cable flex con conector Lightning de carga y micrófono doble para Apple iPhone 11', 35000, 75000, 20, 'ACTIVO', NULL, 12, 8, 6, 0, 'PRD001036', NULL, 0, 'ninguno', NULL, NULL),
(1037, 1, 3, 'ACCESORIO', 'FICHA CARGA FLEX IPHONE 12 / 12 PRO', 'Flex con puerto Lightning de carga original para iPhone 12 y 12 Pro', 45000, 90000, 16, 'ACTIVO', NULL, 10, 6, 6, 0, 'PRD001037', NULL, 0, 'ninguno', NULL, NULL),
(1038, 1, 15, 'ACCESORIO', 'FICHA CARGA SUB-PLACA XIAOMI NOTE 9S/PRO', 'Sub placa de carga Tipo C con soporte Quick Charge para Xiaomi Redmi Note 9S / Note 9 Pro', 22000, 48000, 30, 'ACTIVO', NULL, 18, 12, 6, 0, 'PRD001038', NULL, 0, 'ninguno', NULL, NULL),
(1039, 1, 15, 'ACCESORIO', 'FICHA CARGA SUB-PLACA XIAOMI NOTE 11 TIPO C', 'Módulo de carga USB-C con micrófono para Xiaomi Redmi Note 11 versión 4G', 24000, 52000, 38, 'ACTIVO', NULL, 22, 16, 6, 0, 'PRD001039', NULL, 0, 'ninguno', NULL, NULL),
(1040, 1, 27, 'ACCESORIO', 'FICHA CARGA SUB-PLACA MOTO G20/G30 TIPO C', 'Placa pin de carga Tipo C original para Motorola Moto G20 y Moto G30', 20000, 45000, 30, 'ACTIVO', NULL, 16, 14, 6, 0, 'PRD001040', NULL, 0, 'ninguno', NULL, NULL),
(1041, 1, 10, 'ACCESORIO', 'PIN CARGA UNIVERSAL MICRO USB V8 PACK X10', 'Pack de 10 unidades de pines de carga Micro USB V8 para soldadura en celulares y parlantes', 10000, 25000, 90, 'ACTIVO', NULL, 50, 40, 6, 0, 'PRD001041', NULL, 0, 'ninguno', NULL, NULL),
(1042, 1, 5, 'ACCESORIO', 'LAMINA 9D CERAMICA MATE SAMSUNG A14 / A15', 'Protector de pantalla cerámica flexible mate antirreflejo y antihuellas para Samsung A14 / A15', 3500, 15000, 105, 'ACTIVO', NULL, 60, 45, 2, 0, 'PRD001042', NULL, 0, 'ninguno', NULL, NULL),
(1043, 1, 5, 'ACCESORIO', 'LAMINA VIDRIO 9D IPHONE 11 / XR COMPLETA', 'Vidrio templado 9D full glue borde negro cobertura total para iPhone 11 y iPhone XR', 3500, 15000, 145, 'ACTIVO', NULL, 80, 65, 2, 0, 'PRD001043', NULL, 0, 'ninguno', NULL, NULL),
(1044, 1, 6, 'ACCESORIO', 'LAMINA 9D PRIVACIDAD ANTIESPIA IPHONE 13/14', 'Vidrio templado antiespía con filtro de privacidad 28 grados para iPhone 13 y 14', 6000, 25000, 90, 'ACTIVO', NULL, 50, 40, 2, 0, 'PRD001044', NULL, 0, 'ninguno', NULL, NULL),
(1045, 1, 6, 'ACCESORIO', 'LAMINA 9D PRIVACIDAD IPHONE 14 PRO MAX', 'Lámina antiespía privacy glass pantalla completa para iPhone 14 Pro Max y 15 Plus', 6500, 25000, 97, 'ACTIVO', NULL, 52, 45, 2, 0, 'PRD001045', NULL, 0, 'ninguno', NULL, NULL),
(1046, 1, 5, 'ACCESORIO', 'LAMINA VIDRIO 9D IPHONE 15 PRO / PRO MAX', 'Lámina de cristal templado 9D de alta dureza 9H para iPhone 15 Pro y 15 Pro Max', 4500, 20000, 120, 'ACTIVO', NULL, 70, 50, 2, 0, 'PRD001046', NULL, 0, 'ninguno', NULL, NULL),
(1047, 1, 5, 'ACCESORIO', 'LAMINA 9D VIDRIO SAMSUNG A54 5G / A34 5G', 'Protector 9D full coverage alta transparencia para Samsung Galaxy A54 5G y A34 5G', 3500, 15000, 95, 'ACTIVO', NULL, 55, 40, 2, 0, 'PRD001047', NULL, 0, 'ninguno', NULL, NULL),
(1048, 1, 5, 'ACCESORIO', 'LAMINA CERAMICA FLEXIBLE XIAOMI NOTE 12/13', 'Lámina cerámica irrompible flexible para Xiaomi Redmi Note 12 y Redmi Note 13', 4000, 18000, 80, 'ACTIVO', NULL, 45, 35, 2, 0, 'PRD001048', NULL, 0, 'ninguno', NULL, NULL),
(1049, 1, 4, 'ACCESORIO', 'LAMINA HIDROGEL HD AUTORREPARABLE PACK X5', 'Pack de 5 láminas de hidrogel autorreparable HD para plotters de corte o colocación manual', 25000, 60000, 55, 'ACTIVO', NULL, 30, 25, 2, 0, 'PRD001049', NULL, 0, 'ninguno', NULL, NULL),
(1050, 1, 5, 'ACCESORIO', 'LAMINA VIDRIO 9D MOTO G23 / G13 / G53', 'Vidrio templado 9D borde negro cobertura total para serie Motorola Moto G 2023', 3500, 15000, 70, 'ACTIVO', NULL, 40, 30, 2, 0, 'PRD001050', NULL, 0, 'ninguno', NULL, NULL),
(1051, 1, 7, 'ACCESORIO', 'ESTUCHE SILICONE CASE FELPA IPHONE 13/14', 'Funda silicone case con interior de microfibra de felpa suave para iPhone 13 y 14', 12000, 35000, 75, 'ACTIVO', NULL, 40, 35, 2, 0, 'PRD001051', NULL, 0, 'ninguno', NULL, NULL),
(1052, 1, 3, 'ACCESORIO', 'ESTUCHE MAGSAFE REFORZADO IPHONE 14 PRO MAX', 'Funda transparente acrílica con aro magnético MagSafe integrado para iPhone 14 Pro Max', 18000, 45000, 55, 'ACTIVO', NULL, 30, 25, 2, 0, 'PRD001052', NULL, 0, 'ninguno', NULL, NULL),
(1053, 1, 3, 'ACCESORIO', 'ESTUCHE MAGSAFE REFORZADO IPHONE 15 PRO MAX', 'Funda clear MagSafe con bordes antichoque reforzados para iPhone 15 Pro Max', 18000, 45000, 65, 'ACTIVO', NULL, 35, 30, 2, 0, 'PRD001053', NULL, 0, 'ninguno', NULL, NULL),
(1054, 1, 2, 'ACCESORIO', 'ESTUCHE SPACE CLEAR SAMSUNG A14 / A15', 'Funda Space Clear trasera dura acrílica y bordes TPU anticaídas para Samsung A14 y A15', 10000, 30000, 90, 'ACTIVO', NULL, 50, 40, 2, 0, 'PRD001054', NULL, 0, 'ninguno', NULL, NULL),
(1055, 1, 7, 'ACCESORIO', 'ESTUCHE SILICONE CASE PASTEL SAMSUNG A54 5G', 'Funda suave silicone case tonos pastel con protección de cámaras para Samsung A54 5G', 12000, 35000, 60, 'ACTIVO', NULL, 35, 25, 2, 0, 'PRD001055', NULL, 0, 'ninguno', NULL, NULL),
(1056, 1, 15, 'ACCESORIO', 'ESTUCHE ARMOR CON ANILLO XIAOMI NOTE 12', 'Funda de uso rudo blindada con soporte de anillo magnético giratorio para Xiaomi Note 12', 15000, 40000, 45, 'ACTIVO', NULL, 25, 20, 2, 0, 'PRD001056', NULL, 0, 'ninguno', NULL, NULL),
(1057, 1, 7, 'ACCESORIO', 'ESTUCHE LISO COLOR MOTO G22 / G32 SILICONA', 'Funda de silicona líquida suave al tacto con botones cromados para Moto G22 y Moto G32', 10000, 30000, 50, 'ACTIVO', NULL, 30, 20, 2, 0, 'PRD001057', NULL, 0, 'ninguno', NULL, NULL),
(1058, 1, 8, 'ACCESORIO', 'ESTUCHE DISEÑO FASHION DAMA IPHONE 11/12', 'Funda decorada con estampado premium marmoleado y glitter para iPhone 11 y 12', 16000, 45000, 50, 'ACTIVO', NULL, 25, 25, 2, 0, 'PRD001058', NULL, 0, 'ninguno', NULL, NULL),
(1059, 1, 2, 'ACCESORIO', 'ESTUCHE AIRBAG ANTICAIDA SAMSUNG A04/A04E', 'Funda transparente flexible con esquinas con cápsula de aire anti impacto para Samsung A04', 8000, 25000, 80, 'ACTIVO', NULL, 45, 35, 2, 0, 'PRD001059', NULL, 0, 'ninguno', NULL, NULL),
(1060, 1, 12, 'ACCESORIO', 'MOUSE GAMER HAVIT MS1026 RGB 6400 DPI', 'Mouse gamer óptico ergonómico con iluminación RGB y 6 niveles de DPI ajustables hasta 6400', 38000, 70000, 27, 'ACTIVO', NULL, 15, 12, 7, 0, 'PRD001060', NULL, 0, 'ninguno', NULL, NULL),
(1061, 1, 12, 'ACCESORIO', 'MOUSE INALAMBRICO HAVIT MS76GT SILENCIOSO', 'Mouse inalámbrico con receptor USB nano alcance 10 metros y clics silenciosos', 28000, 55000, 38, 'ACTIVO', NULL, 20, 18, 7, 0, 'PRD001061', NULL, 0, 'ninguno', NULL, NULL),
(1062, 1, 12, 'ACCESORIO', 'TECLADO MECANICO HAVIT GAMER RGB OUTEMU', 'Teclado mecánico switches Outemu Blue con retroiluminación RGB configurable anti-ghosting', 135000, 220000, 14, 'ACTIVO', NULL, 8, 6, 7, 0, 'PRD001062', NULL, 0, 'ninguno', NULL, NULL),
(1063, 1, 12, 'ACCESORIO', 'TECLADO + MOUSE INALAMBRICO HAVIT KB255G', 'Combo teclado español con teclado numérico y mouse inalámbrico 2.4GHz para oficina', 75000, 130000, 18, 'ACTIVO', NULL, 10, 8, 7, 0, 'PRD001063', NULL, 0, 'ninguno', NULL, NULL),
(1064, 1, 12, 'ACCESORIO', 'ADAPTADOR HUB USB-C 5 EN 1 HDMI 4K + 3X USB', 'Concentrador multipuerto Tipo C con salida de video HDMI 4K, 3 puertos USB 3.0 y puerto de carga PD 87W', 68000, 125000, 22, 'ACTIVO', NULL, 12, 10, 7, 0, 'PRD001064', NULL, 0, 'ninguno', NULL, NULL),
(1065, 1, 9, 'ACCESORIO', 'CABLE HDMI 2.0 4K ULTRA HD 2M MALLADO', 'Cable HDMI 2.0 de alta velocidad con conectores dorados y mallado de nylon resistente', 15000, 35000, 55, 'ACTIVO', NULL, 30, 25, 7, 0, 'PRD001065', NULL, 0, 'ninguno', NULL, NULL),
(1066, 1, 28, 'ACCESORIO', 'PENDRIVE KINGSTON DATATRAVELER 64GB USB 3.2', 'Memoria USB 3.2 Gen 1 Kingston DataTraveler Exodia de 64 GB con capuchón protector', 32000, 60000, 45, 'ACTIVO', NULL, 25, 20, 7, 0, 'PRD001066', NULL, 0, 'ninguno', NULL, NULL),
(1067, 1, 29, 'ACCESORIO', 'PENDRIVE SANDISK CRUISER BLADE 32GB USB 2.0', 'Pendrive compacto SanDisk Cruzer Blade de 32 GB para almacenamiento portátil', 24000, 45000, 65, 'ACTIVO', NULL, 35, 30, 7, 0, 'PRD001067', NULL, 0, 'ninguno', NULL, NULL),
(1068, 1, 29, 'ACCESORIO', 'MICRO SD 64GB SANDISK ULTRA CLASE 10 C/ADAP', 'Tarjeta de memoria MicroSDXC SanDisk Ultra 64GB 100MB/s A1 Clase 10 con adaptador SD', 35000, 65000, 55, 'ACTIVO', NULL, 30, 25, 7, 0, 'PRD001068', NULL, 0, 'ninguno', NULL, NULL),
(1069, 1, 28, 'ACCESORIO', 'MICRO SD 128GB KINGSTON CANVAS SELECT PLUS', 'Tarjeta Micro SD Kingston 128GB Canvas Select Plus U1 V10 para celulares y cámaras de seguridad', 55000, 95000, 38, 'ACTIVO', NULL, 20, 18, 7, 0, 'PRD001069', NULL, 0, 'ninguno', NULL, NULL),
(1070, 1, 18, 'ACCESORIO', 'CARGADOR CARGA RAPIDA 20W TIPO C IPHONE/SAM', 'Cabezal cargador USB-C Power Delivery 20W de carga rápida para iPhone 11 al 15 y Samsung Galaxy', 22000, 50000, 75, 'ACTIVO', NULL, 40, 35, 2, 0, 'PRD001070', NULL, 0, 'ninguno', NULL, NULL),
(1071, 1, 15, 'ACCESORIO', 'CARGADOR TURBO CHARGE 33W XIAOMI ORIGINAL', 'Cargador de pared original Xiaomi Mi 33W Wall Charger Type-A + Type-C con tecnología Turbo Charge', 35000, 75000, 45, 'ACTIVO', NULL, 25, 20, 2, 0, 'PRD001071', NULL, 0, 'ninguno', NULL, NULL),
(1072, 1, 11, 'ACCESORIO', 'CARGADOR GAN 65W MULTIPUERTO 2X TYPE-C + USB', 'Cargador de tecnología Nitruro de Galio (GaN) 65W para notebooks, MacBooks y celulares de alta gama', 78000, 140000, 18, 'ACTIVO', NULL, 10, 8, 7, 0, 'PRD001072', NULL, 0, 'ninguno', NULL, NULL),
(1073, 1, 9, 'ACCESORIO', 'CABLE TIPO C A TIPO C 60W CARGA RAPIDA 1M', 'Cable USB-C a USB-C de transferencia de datos y carga rápida 60W Power Delivery con refuerzo de caucho', 12000, 30000, 90, 'ACTIVO', NULL, 50, 40, 2, 0, 'PRD001073', NULL, 0, 'ninguno', NULL, NULL),
(1074, 1, 9, 'ACCESORIO', 'CABLE LIGHTNING A TIPO C CARGA RAPIDA 20W', 'Cable Lightning a Type-C compatible con carga rápida de 20W para Apple iPhone y iPad', 14000, 35000, 105, 'ACTIVO', NULL, 60, 45, 2, 0, 'PRD001074', NULL, 0, 'ninguno', NULL, NULL),
(1075, 1, 13, 'ACCESORIO', 'AURICULAR BLUETOOTH HAYLOU GT1/GT7 TWS', 'Auriculares True Wireless Bluetooth 5.2 con estuche de carga, micrófono integrado y baja latencia', 72000, 135000, 27, 'ACTIVO', NULL, 15, 12, 2, 0, 'PRD001075', NULL, 0, 'ninguno', NULL, NULL),
(1076, 1, 12, 'ACCESORIO', 'SOPORTE METALICO ALUMINIO NOTEBOOK Y TABLET', 'Base soporte plegable de aluminio antideslizante con ajuste de ángulo y altura para laptops', 38000, 75000, 24, 'ACTIVO', NULL, 14, 10, 7, 0, 'PRD001076', NULL, 0, 'ninguno', NULL, NULL),
(1077, 1, 32, 'EQUIPO', 'TV SMART 43\" MTEK MK43FSAF FULL HD', 'Televisor Smart TV 43 pulgadas MTEK Full HD modelo MK43FSAF con Wi-Fi y HDMI', 1266000, 1416000, 9, 'ACTIVO', NULL, 5, 4, 8, 0, 'PRD001077', NULL, 0, 'ninguno', NULL, NULL),
(1078, 1, 30, 'EQUIPO', 'TV SMART AIWA 32\" AW32B4SM', 'Televisor Smart TV 32 pulgadas AIWA HD modelo AW32B4SM Android TV', 941500, 1091500, 14, 'ACTIVO', NULL, 8, 6, 8, 0, 'PRD001078', NULL, 0, 'ninguno', NULL, NULL),
(1079, 1, 30, 'EQUIPO', 'TV SMART AIWA 43\" AW43B4SMFL', 'Televisor Smart TV 43 pulgadas AIWA Full HD modelo AW43B4SMFL diseño frameless', 1586000, 1736000, 11, 'ACTIVO', NULL, 6, 5, 8, 0, 'PRD001079', NULL, 0, 'ninguno', NULL, NULL),
(1080, 1, 30, 'EQUIPO', 'TV SMART AIWA 65\" AW65B4QFG 4K QLED GOOGLE TV', 'Televisor Smart TV 65 pulgadas AIWA 4K QLED con sistema operativo Google TV', 2901000, 3051000, 5, 'ACTIVO', NULL, 3, 2, 8, 0, 'PRD001080', NULL, 0, 'ninguno', NULL, NULL),
(1081, 1, 30, 'EQUIPO', 'TV SMART AIWA 75\" AW75B4QFG QLED GOOGLE TV 4K UHD', 'Televisor Smart TV 75 pulgadas AIWA 4K UHD QLED Google TV sonido Dolby Audio', 4187000, 4337000, 4, 'ACTIVO', NULL, 2, 2, 8, 0, 'PRD001081', NULL, 0, 'ninguno', NULL, NULL),
(1082, 1, 31, 'EQUIPO', 'TV SMART AUDISAT 32\" HD AD-32', 'Televisor Smart TV 32 pulgadas AUDISAT HD modelo AD-32 con sintonizador digital', 681000, 831000, 18, 'ACTIVO', NULL, 10, 8, 8, 0, 'PRD001082', NULL, 0, 'ninguno', NULL, NULL),
(1083, 1, 31, 'EQUIPO', 'TV SMART AUDISAT 40\" AD-40Q QLED', 'Televisor Smart TV 40 pulgadas AUDISAT pantalla QLED modelo AD-40Q', 981000, 1131000, 12, 'ACTIVO', NULL, 7, 5, 8, 0, 'PRD001083', NULL, 0, 'ninguno', NULL, NULL),
(1084, 1, 31, 'EQUIPO', 'TV SMART AUDISAT 43\" AD-43Q QLED FHD+', 'Televisor Smart TV 43 pulgadas AUDISAT panel QLED FHD+ con apps integradas', 1111500, 1261500, 11, 'ACTIVO', NULL, 6, 5, 8, 0, 'PRD001084', NULL, 0, 'ninguno', NULL, NULL),
(1085, 1, 31, 'EQUIPO', 'TV SMART AUDISAT 50\" AD-50Q2K QLED', 'Televisor Smart TV 50 pulgadas AUDISAT 2K QLED modelo AD-50Q2K', 1455500, 1605500, 7, 'ACTIVO', NULL, 4, 3, 8, 0, 'PRD001085', NULL, 0, 'ninguno', NULL, NULL),
(1086, 1, 31, 'EQUIPO', 'TV SMART AUDISAT 65\" AD-65Q-GO', 'Televisor Smart TV 65 pulgadas AUDISAT pantalla gigante QLED con Google TV', 3351500, 3501500, 5, 'ACTIVO', NULL, 3, 2, 8, 0, 'PRD001086', NULL, 0, 'ninguno', NULL, NULL),
(1087, 1, 11, 'EQUIPO', 'TV SMART ECOP 32\" EP-TV032 ISDB-T', 'Televisor Smart TV 32 pulgadas Ecopower EP-TV032 sintonizador digital ISDB-T', 656000, 806000, 20, 'ACTIVO', NULL, 12, 8, 8, 0, 'PRD001087', NULL, 0, 'ninguno', NULL, NULL),
(1088, 1, 11, 'EQUIPO', 'TV SMART ECOP 43\" EP-TV043 ISDB-T', 'Televisor Smart TV 43 pulgadas Ecopower EP-TV043 Full HD con Wi-Fi', 1095500, 1245500, 13, 'ACTIVO', NULL, 7, 6, 8, 0, 'PRD001088', NULL, 0, 'ninguno', NULL, NULL),
(1089, 1, 11, 'EQUIPO', 'TV SMART ECOP 43\" EP-TV043G GOOGLE TV', 'Televisor Smart TV 43 pulgadas Ecopower EP-TV043G Google TV y comando de voz', 1289500, 1439500, 10, 'ACTIVO', NULL, 6, 4, 8, 0, 'PRD001089', NULL, 0, 'ninguno', NULL, NULL),
(1090, 1, 11, 'EQUIPO', 'TV SMART ECOP 50\" EP-TV050 4K', 'Televisor Smart TV 50 pulgadas Ecopower EP-TV050 resolución 4K Ultra HD', 1576500, 1726500, 8, 'ACTIVO', NULL, 5, 3, 8, 0, 'PRD001090', NULL, 0, 'ninguno', NULL, NULL),
(1091, 1, 11, 'EQUIPO', 'TV SMART ECOP 55\" EP-TV055 4K', 'Televisor Smart TV 55 pulgadas Ecopower EP-TV055 4K con aplicaciones streaming', 1927000, 2077000, 7, 'ACTIVO', NULL, 4, 3, 8, 0, 'PRD001091', NULL, 0, 'ninguno', NULL, NULL),
(1092, 1, 11, 'EQUIPO', 'TV SMART ECOP 75\" EP-TV075 4K', 'Televisor Smart TV 75 pulgadas Ecopower EP-TV075 4K HDR cinematográfico', 3587500, 3737500, 3, 'ACTIVO', NULL, 2, 1, 8, 0, 'PRD001092', NULL, 0, 'ninguno', NULL, NULL),
(1093, 1, 16, 'EQUIPO', 'TV SMART LUO 32\" LU-510', 'Televisor Smart TV 32 pulgadas LUO modelo LU-510 con Netflix y YouTube', 607000, 757000, 16, 'ACTIVO', NULL, 9, 7, 8, 0, 'PRD001093', NULL, 0, 'ninguno', NULL, NULL),
(1094, 1, 16, 'EQUIPO', 'TV SMART LUO 50\" LU-512', 'Televisor Smart TV 50 pulgadas LUO modelo LU-512 4K Ultra HD frameless', 1679500, 1829500, 7, 'ACTIVO', NULL, 4, 3, 8, 0, 'PRD001094', NULL, 0, 'ninguno', NULL, NULL),
(1095, 1, 32, 'EQUIPO', 'TV SMART MTEK 65\" MK65FSAU', 'Televisor Smart TV 65 pulgadas MTEK MK65FSAU Ultra HD 4K Android TV', 3030500, 3180500, 5, 'ACTIVO', NULL, 3, 2, 8, 0, 'PRD001095', NULL, 0, 'ninguno', NULL, NULL),
(1096, 1, 2, 'EQUIPO', 'TV SMART SAMSUNG 32\" UN32T4202AG', 'Televisor Smart TV 32 pulgadas Samsung UN32T4202AG con tecnología PurColor', 1290000, 1440000, 11, 'ACTIVO', NULL, 6, 5, 8, 0, 'PRD001096', NULL, 0, 'ninguno', NULL, NULL),
(1097, 1, 2, 'EQUIPO', 'TV SMART SAMSUNG 43\" UN43AU7090G 4G ULTRA HD', 'Televisor Smart TV 43 pulgadas Samsung Crystal UHD UN43AU7090G 4K', 2294500, 2444500, 9, 'ACTIVO', NULL, 5, 4, 8, 0, 'PRD001097', NULL, 0, 'ninguno', NULL, NULL),
(1098, 1, 34, 'EQUIPO', 'TV SMART SUN 55\"', 'Televisor Smart TV 55 pulgadas SUN 4K Ultra HD con HDMI y USB', 1917000, 2067000, 7, 'ACTIVO', NULL, 4, 3, 8, 0, 'PRD001098', NULL, 0, 'ninguno', NULL, NULL),
(1099, 1, 33, 'EQUIPO', 'TV SMART TOSHIBA 43\"', 'Televisor Smart TV 43 pulgadas TOSHIBA Full HD con conectividad avanzada', 1927500, 2077500, 9, 'ACTIVO', NULL, 5, 4, 8, 0, 'PRD001099', NULL, 0, 'ninguno', NULL, NULL),
(1100, 1, 35, 'EQUIPO', 'TAB KEEN 15 PRO MAX 1TB + 16GB RAM AZUL + ACC', 'Tablet KEEN 15 Pro Max 1TB memoria, 16GB RAM color azul con accesorios completos', 608000, 758000, 14, 'ACTIVO', NULL, 8, 6, 9, 0, 'PRD001100', NULL, 0, 'ninguno', NULL, NULL),
(1101, 1, 35, 'EQUIPO', 'TAB KEEN 15 PRO MAX 1TB + 16GB RAM DORADO + ACC', 'Tablet KEEN 15 Pro Max 1TB memoria, 16GB RAM color dorado con accesorios', 608000, 758000, 11, 'ACTIVO', NULL, 6, 5, 9, 0, 'PRD001101', NULL, 0, 'ninguno', NULL, NULL),
(1102, 1, 35, 'EQUIPO', 'TAB KEEN 15 PRO MAX 1TB + 16GB RAM GRIS + ACC', 'Tablet KEEN 15 Pro Max 1TB memoria, 16GB RAM color gris con estuche y accesorios', 608000, 758000, 13, 'ACTIVO', NULL, 7, 6, 9, 0, 'PRD001102', NULL, 0, 'ninguno', NULL, NULL),
(1103, 1, 35, 'EQUIPO', 'TAB KEEN 15 PRO MAX 1TB + 16GB RAM NEGRO + ACC', 'Tablet KEEN 15 Pro Max 1TB memoria, 16GB RAM color negro con teclado y stylus', 608000, 758000, 15, 'ACTIVO', NULL, 8, 7, 9, 0, 'PRD001103', NULL, 0, 'ninguno', NULL, NULL),
(1104, 1, 35, 'EQUIPO', 'TAB KEEN 17 MAX 10\" 1TB + 16GB RAM DORADO', 'Tablet KEEN 17 Max 10 pulgadas 1TB almacenamiento, 16GB RAM color dorado', 608000, 758000, 11, 'ACTIVO', NULL, 6, 5, 9, 0, 'PRD001104', NULL, 0, 'ninguno', NULL, NULL),
(1105, 1, 35, 'EQUIPO', 'TAB KEEN 17 MAX 10\" 1TB + 16GB RAM GRIS', 'Tablet KEEN 17 Max 10 pulgadas 1TB almacenamiento, 16GB RAM color gris', 608000, 758000, 12, 'ACTIVO', NULL, 7, 5, 9, 0, 'PRD001105', NULL, 0, 'ninguno', NULL, NULL),
(1106, 1, 35, 'EQUIPO', 'TAB KEEN 17 MAX 10\" 1TB + 16GB RAM NARANJA', 'Tablet KEEN 17 Max 10 pulgadas 1TB almacenamiento, 16GB RAM color naranja', 608000, 758000, 9, 'ACTIVO', NULL, 5, 4, 9, 0, 'PRD001106', NULL, 0, 'ninguno', NULL, NULL),
(1107, 1, 35, 'EQUIPO', 'TAB KEEN 17 MAX 10\" 1TB + 16GB RAM NEGRO', 'Tablet KEEN 17 Max 10 pulgadas 1TB almacenamiento, 16GB RAM color negro', 608000, 758000, 14, 'ACTIVO', NULL, 8, 6, 9, 0, 'PRD001107', NULL, 0, 'ninguno', NULL, NULL),
(1108, 1, 35, 'EQUIPO', 'TAB KEEN A83 256GB + 4GB RAM AZUL', 'Tablet económica KEEN A83 pantalla HD 256GB y 4GB RAM color azul', 294000, 444000, 22, 'ACTIVO', NULL, 12, 10, 9, 0, 'PRD001108', NULL, 0, 'ninguno', NULL, NULL),
(1109, 1, 35, 'EQUIPO', 'TAB KEEN A83 256GB + 4GB RAM NARANJA', 'Tablet económica KEEN A83 pantalla HD 256GB y 4GB RAM color naranja', 294000, 444000, 18, 'ACTIVO', NULL, 10, 8, 9, 0, 'PRD001109', NULL, 0, 'ninguno', NULL, NULL),
(1110, 1, 35, 'EQUIPO', 'TAB KEEN A83 256GB + 4GB RAM ROSADO', 'Tablet infantil KEEN A83 pantalla HD 256GB y 4GB RAM color rosado', 294000, 444000, 18, 'ACTIVO', NULL, 10, 8, 9, 0, 'PRD001110', NULL, 0, 'ninguno', NULL, NULL),
(1111, 1, 35, 'EQUIPO', 'TAB KEEN A83 256GB + 4GB RAM VERDE', 'Tablet KEEN A83 pantalla HD 256GB y 4GB RAM color verde', 294000, 444000, 18, 'ACTIVO', NULL, 10, 8, 9, 0, 'PRD001111', NULL, 0, 'ninguno', NULL, NULL),
(1112, 1, 35, 'EQUIPO', 'TAB KEEN KN90 7\" 64GB + 2 GB RAM', 'Tablet infantil compacta KEEN KN90 7 pulgadas 64GB + 2GB RAM para niños', 374500, 524500, 27, 'ACTIVO', NULL, 15, 12, 9, 0, 'PRD001112', NULL, 0, 'ninguno', NULL, NULL),
(1113, 1, 35, 'EQUIPO', 'TAB KEEN S26 ULTRA+ 1TB + 16GB RAM AZUL + ACC', 'Tablet KEEN S26 Ultra+ 1TB + 16GB RAM azul con funda, teclado y accesorios', 608000, 758000, 14, 'ACTIVO', NULL, 8, 6, 9, 0, 'PRD001113', NULL, 0, 'ninguno', NULL, NULL),
(1114, 1, 35, 'EQUIPO', 'TAB KEEN S26 ULTRA+ 1TB + 16GB RAM GRIS + ACC', 'Tablet KEEN S26 Ultra+ 1TB + 16GB RAM gris con accesorios completos', 608000, 758000, 12, 'ACTIVO', NULL, 7, 5, 9, 0, 'PRD001114', NULL, 0, 'ninguno', NULL, NULL),
(1115, 1, 35, 'EQUIPO', 'TAB KEEN S26 ULTRA+ 1TB + 16GB RAM NARANJA + ACC', 'Tablet KEEN S26 Ultra+ 1TB + 16GB RAM color naranja con accesorios', 608000, 758000, 10, 'ACTIVO', NULL, 6, 4, 9, 0, 'PRD001115', NULL, 0, 'ninguno', NULL, NULL),
(1116, 1, 35, 'EQUIPO', 'TAB KEEN S26 ULTRA+ 1TB + 16GB RAM NEGRO + ACC', 'Tablet KEEN S26 Ultra+ 1TB + 16GB RAM negro con teclado inalámbrico y funda', 608000, 758000, 15, 'ACTIVO', NULL, 8, 7, 9, 0, 'PRD001116', NULL, 0, 'ninguno', NULL, NULL),
(1117, 1, 36, 'EQUIPO', 'TAB LENOVO K11 TB-330XUP 11\" 128GB + 8GB RAM + ESTUCHE C/ TI', 'Tablet Lenovo K11 TB-330XUP 11 pulgadas 128GB + 8GB RAM con funda y teclado', 2079000, 2229000, 6, 'ACTIVO', NULL, 3, 3, 9, 0, 'PRD001117', NULL, 0, 'ninguno', NULL, NULL),
(1118, 1, 16, 'EQUIPO', 'TAB LUO HOTWAV TAB R8 128GB + 4GB RAM REFORZADO', 'Tablet rugerizada uso rudo LUO Hotwav Tab R8 128GB + 4GB RAM antichoque', 695500, 845500, 11, 'ACTIVO', NULL, 6, 5, 9, 0, 'PRD001118', NULL, 0, 'ninguno', NULL, NULL),
(1119, 1, 37, 'EQUIPO', 'TAB OUKITEL RT3 PLUS 8\" 128GB + 16GB RAM AZUL', 'Tablet blindada OUKITEL RT3 Plus 8 pulgadas 128GB + 16GB RAM color azul', 1215000, 1365000, 9, 'ACTIVO', NULL, 5, 4, 9, 0, 'PRD001119', NULL, 0, 'ninguno', NULL, NULL),
(1120, 1, 37, 'EQUIPO', 'TAB OUKITEL RT3 PLUS 8\" 128GB + 16GB RAM NEGRO', 'Tablet blindada OUKITEL RT3 Plus 8 pulgadas 128GB + 16GB RAM color negro militar', 1215000, 1365000, 9, 'ACTIVO', NULL, 5, 4, 9, 0, 'PRD001120', NULL, 0, 'ninguno', NULL, NULL),
(1121, 1, 37, 'EQUIPO', 'TAB OUKITEL RT8 11\" LTE 256GB + 6GB RAM NEGRO + ACC', 'Tablet todoterreno OUKITEL RT8 11 pulgadas 4G LTE 256GB + 6GB RAM con correa y accesorios', 1484000, 1634000, 7, 'ACTIVO', NULL, 4, 3, 9, 0, 'PRD001121', NULL, 0, 'ninguno', NULL, NULL),
(1122, 1, 2, 'EQUIPO', 'TAB SAM A11 SM-X133 WIFI 128GB + 8GB RAM', 'Tablet Samsung Galaxy Tab A11 SM-X133 Wi-Fi 128GB almacenamiento y 8GB RAM', 1138000, 1288000, 11, 'ACTIVO', NULL, 6, 5, 9, 0, 'PRD001122', NULL, 0, 'ninguno', NULL, NULL),
(1123, 1, 2, 'EQUIPO', 'TAB SAM A11 SM-X135 LTE 128GB + 8GB RAM', 'Tablet Samsung Galaxy Tab A11 SM-X135 con conectividad celular 4G LTE 128GB', 1373500, 1523500, 10, 'ACTIVO', NULL, 6, 4, 9, 0, 'PRD001123', NULL, 0, 'ninguno', NULL, NULL),
(1124, 1, 2, 'EQUIPO', 'TAB SAM A11 SM-X135 LTE 64GB + 4GB RAM', 'Tablet Samsung Galaxy Tab A11 SM-X135 4G LTE 64GB y 4GB RAM', 1139000, 1289000, 12, 'ACTIVO', NULL, 7, 5, 9, 0, 'PRD001124', NULL, 0, 'ninguno', NULL, NULL),
(1125, 1, 2, 'EQUIPO', 'TAB SAM A11+ X236B 5G 128GB + 6GB RAM GRIS', 'Tablet Samsung Galaxy Tab A11+ modelo SM-X236B 5G 128GB + 6GB RAM color gris', 1794000, 1944000, 8, 'ACTIVO', NULL, 5, 3, 9, 0, 'PRD001125', NULL, 0, 'ninguno', NULL, NULL),
(1126, 1, 2, 'EQUIPO', 'TAB SAM S10 LITE SM-X400 128GB + 6GB RAM WIFI', 'Tablet Samsung Galaxy Tab S10 Lite SM-X400 Wi-Fi 128GB pantalla AMOLED con S-Pen', 1851500, 2001500, 7, 'ACTIVO', NULL, 4, 3, 9, 0, 'PRD001126', NULL, 0, 'ninguno', NULL, NULL),
(1127, 1, 38, 'EQUIPO', 'TAB TCL TAB11 WIFI 128GB + 4GB RAM', 'Tablet TCL Tab 11 pantalla 2K NXTVISION 128GB almacenamiento Wi-Fi', 1239500, 1389500, 10, 'ACTIVO', NULL, 6, 4, 9, 0, 'PRD001127', NULL, 0, 'ninguno', NULL, NULL),
(1128, 1, 15, 'EQUIPO', 'TAB XIA POCO PAD X1 11,2\" WIFI 512GB + 8GB RAM', 'Tablet Xiaomi Poco Pad X1 pantalla 11.2 pulgadas 120Hz 512GB + 8GB RAM', 2333500, 2483500, 7, 'ACTIVO', NULL, 4, 3, 9, 0, 'PRD001128', NULL, 0, 'ninguno', NULL, NULL),
(1129, 1, 15, 'EQUIPO', 'TAB XIA RD PAD 2 9,7\" WIFI 128GB + 4GB RAM', 'Tablet Xiaomi Redmi Pad 2 pantalla 9.7 pulgadas Wi-Fi 128GB + 4GB RAM', 1080000, 1230000, 14, 'ACTIVO', NULL, 8, 6, 9, 0, 'PRD001129', NULL, 0, 'ninguno', NULL, NULL),
(1130, 1, 15, 'EQUIPO', 'TAB XIA RD PAD 2 PRO 11\" WIFI 256GB + 8GB', 'Tablet Xiaomi Redmi Pad 2 Pro pantalla 11 pulgadas 2.5K 256GB + 8GB RAM', 1996500, 2146500, 9, 'ACTIVO', NULL, 5, 4, 9, 0, 'PRD001130', NULL, 0, 'ninguno', NULL, NULL),
(1131, 1, 39, 'EQUIPO', 'TAB ATOUCH KC64 KIDS 7.85\" 256GB + 1GB RAM AZUL + ACC.', 'Tablet infantil Atouch KC64 Kids 7.85 pulgadas con juegos didácticos y funda azul', 360000, 510000, 24, 'ACTIVO', NULL, 14, 10, 9, 0, 'PRD001131', NULL, 0, 'ninguno', NULL, NULL),
(1132, 1, 39, 'EQUIPO', 'TAB ATOUCH KC64 KIDS 7.85\" 256GB + 1GB ROSA + ACC.', 'Tablet infantil Atouch KC64 Kids 7.85 pulgadas funda reforzada rosa con accesorios', 360000, 510000, 24, 'ACTIVO', NULL, 14, 10, 9, 0, 'PRD001132', NULL, 0, 'ninguno', NULL, NULL),
(1133, 1, 40, 'EQUIPO', 'TAB BLACKVIEW LINK 1 64GB + 4GB RAM WIFI AZUL + ESTUCHE', 'Tablet Blackview Link 1 64GB + 4GB RAM Wi-Fi color azul con funda incluida', 504000, 654000, 14, 'ACTIVO', NULL, 8, 6, 9, 0, 'PRD001133', NULL, 0, 'ninguno', NULL, NULL),
(1134, 1, 40, 'EQUIPO', 'TAB BLACKVIEW LINK 1 64GB + 4GB RAM WIFI LILA + ESTUCHE', 'Tablet Blackview Link 1 64GB + 4GB RAM Wi-Fi color lila con estuche', 504000, 654000, 14, 'ACTIVO', NULL, 8, 6, 9, 0, 'PRD001134', NULL, 0, 'ninguno', NULL, NULL),
(1135, 1, 40, 'EQUIPO', 'TAB BLACKVIEW LINK 2 128GB + 4GB RAM WIFI AZUL + ESTUCHE', 'Tablet Blackview Link 2 pantalla HD 128GB + 4GB RAM Wi-Fi color azul', 635000, 785000, 12, 'ACTIVO', NULL, 7, 5, 9, 0, 'PRD001135', NULL, 0, 'ninguno', NULL, NULL),
(1136, 1, 40, 'EQUIPO', 'TAB BLACKVIEW LINK 8 256GB + 6GB RAM WIFI GRIS + ACCESORIC', 'Tablet Blackview Link 8 256GB almacenamiento + 6GB RAM color gris con accesorios', 1484000, 1634000, 8, 'ACTIVO', NULL, 5, 3, 9, 0, 'PRD001136', NULL, 0, 'ninguno', NULL, NULL),
(1137, 1, 41, 'EQUIPO', 'TAB CIDEA C8800 ULTRA 5G LTE 10\" 1TB + 8GB NEGRO + ACCESORI', 'Tablet Cidea C8800 Ultra 10 pulgadas 5G LTE 1TB almacenamiento + 8GB RAM negro', 622500, 772500, 14, 'ACTIVO', NULL, 8, 6, 9, 0, 'PRD001137', NULL, 0, 'ninguno', NULL, NULL),
(1138, 1, 41, 'EQUIPO', 'TAB CIDEA C8800 ULTRA 5G LTE 10\" 1TB + 8GB RAM DORADO + AC', 'Tablet Cidea C8800 Ultra 10 pulgadas 5G LTE 1TB almacenamiento + 8GB RAM dorado', 622500, 772500, 11, 'ACTIVO', NULL, 6, 5, 9, 0, 'PRD001138', NULL, 0, 'ninguno', NULL, NULL),
(1139, 1, 41, 'EQUIPO', 'TAB CIDEA C8800 ULTRA 5G LTE 10\" 1TB + 8GB RAM GRIS + ACCESC', 'Tablet Cidea C8800 Ultra 10 pulgadas 5G LTE 1TB almacenamiento + 8GB RAM gris', 622500, 772500, 13, 'ACTIVO', NULL, 7, 6, 9, 0, 'PRD001139', NULL, 0, 'ninguno', NULL, NULL),
(1140, 1, 41, 'EQUIPO', 'TAB CIDEA CM10017 ULTRA 5G LTE 10\" 1TB + 8GB RAM AZUL + ACC', 'Tablet Cidea CM10017 Ultra 10 pulgadas 5G LTE 1TB + 8GB RAM color azul con teclado', 664000, 814000, 14, 'ACTIVO', NULL, 8, 6, 9, 0, 'PRD001140', NULL, 0, 'ninguno', NULL, NULL),
(1141, 1, 41, 'EQUIPO', 'TAB CIDEA CM10017 ULTRA 5G LTE 10\" 1TB + 8GB RAM GRIS + ACC.', 'Tablet Cidea CM10017 Ultra 10 pulgadas 5G LTE 1TB + 8GB RAM color gris con accesorios', 664000, 814000, 12, 'ACTIVO', NULL, 7, 5, 9, 0, 'PRD001141', NULL, 0, 'ninguno', NULL, NULL),
(1142, 1, 41, 'EQUIPO', 'TAB CIDEA CM517 AIR 5G LTE 7\" 256GB + 8GB RAM GRIS + ACC', 'Tablet Cidea CM517 Air 7 pulgadas 5G LTE 256GB + 8GB RAM color gris portátil', 461500, 611500, 16, 'ACTIVO', NULL, 9, 7, 9, 0, 'PRD001142', NULL, 0, 'ninguno', NULL, NULL),
(1143, 1, 41, 'EQUIPO', 'TAB CIDEA CM517 AIR 5G LTE 7\" 256GB + 8RAM AZUL + ACC.', 'Tablet Cidea CM517 Air 7 pulgadas 5G LTE 256GB + 8GB RAM color azul', 461500, 611500, 14, 'ACTIVO', NULL, 8, 6, 9, 0, 'PRD001143', NULL, 0, 'ninguno', NULL, NULL),
(1144, 1, 41, 'EQUIPO', 'TAB CIDEA CM517 AIR 5G LTE 7\" 256GB + 8RAM NARANJA + ACC.', 'Tablet Cidea CM517 Air 7 pulgadas 5G LTE 256GB + 8GB RAM color naranja', 461500, 611500, 11, 'ACTIVO', NULL, 6, 5, 9, 0, 'PRD001144', NULL, 0, 'ninguno', NULL, NULL),
(1145, 1, 41, 'EQUIPO', 'TAB CIDEA CM517 AIR 5G LTE 7\" 256GB + 8RAM NEGRO + ACC.', 'Tablet Cidea CM517 Air 7 pulgadas 5G LTE 256GB + 8GB RAM color negro con accesorios', 461500, 611500, 15, 'ACTIVO', NULL, 8, 7, 9, 0, 'PRD001145', NULL, 0, 'ninguno', NULL, NULL),
(1146, 1, 41, 'EQUIPO', 'TAB CIDEA CM528 5G LTE 7\" 256GB + 8GB RAM NEGRO + ACC', 'Tablet Cidea CM528 5G LTE pantalla 7 pulgadas 256GB + 8GB RAM negro', 435500, 585500, 15, 'ACTIVO', NULL, 9, 6, 9, 0, 'PRD001146', NULL, 0, 'ninguno', NULL, NULL),
(1147, 1, 41, 'EQUIPO', 'TAB CIDEA CM528 5G LTE 7\" 256GB + 8GB RAM VERDE + ACC', 'Tablet Cidea CM528 5G LTE pantalla 7 pulgadas 256GB + 8GB RAM verde', 435500, 585500, 12, 'ACTIVO', NULL, 7, 5, 9, 0, 'PRD001147', NULL, 0, 'ninguno', NULL, NULL),
(1148, 1, 41, 'EQUIPO', 'TAB CIDEA CM817 AIR 5G LTE 8\" 512GB + 8GB RAM NARANJA + ACC', 'Tablet Cidea CM817 Air 8 pulgadas 5G LTE 512GB + 8GB RAM color naranja con estuche', 462000, 612000, 14, 'ACTIVO', NULL, 8, 6, 9, 0, 'PRD001148', NULL, 0, 'ninguno', NULL, NULL),
(1149, 1, 41, 'EQUIPO', 'TAB CIDEA CM936 9\" 5G AZUL + ACC', 'Tablet Cidea CM936 pantalla 9 pulgadas 5G color azul con funda y cargador', 500500, 650500, 14, 'ACTIVO', NULL, 8, 6, 9, 0, 'PRD001149', NULL, 0, 'ninguno', NULL, NULL),
(1150, 1, 41, 'EQUIPO', 'TAB CIDEA KIDS CM10000 PLUS 10\" 5G AZUL', 'Tablet infantil Cidea CM10000 Plus 10 pulgadas 5G color azul con soporte de goma', 547500, 697500, 18, 'ACTIVO', NULL, 10, 8, 9, 0, 'PRD001150', NULL, 0, 'ninguno', NULL, NULL),
(1151, 1, 41, 'EQUIPO', 'TAB CIDEA KIDS CM10000 PLUS 10\" 5G VERDE', 'Tablet infantil Cidea CM10000 Plus 10 pulgadas 5G color verde anti golpes', 547500, 697500, 18, 'ACTIVO', NULL, 10, 8, 9, 0, 'PRD001151', NULL, 0, 'ninguno', NULL, NULL),
(1152, 1, 42, 'EQUIPO', 'TAB HONOR PAD X8a LITE 128GB + 4GB RAM 11.5\" ELN-L03', 'Tablet Honor Pad X8a Lite pantalla 11.5 pulgadas FHD 128GB + 4GB RAM ELN-L03', 1435000, 1585000, 10, 'ACTIVO', NULL, 6, 4, 9, 0, 'PRD001152', NULL, 0, 'ninguno', NULL, NULL),
(1153, 1, 42, 'EQUIPO', 'TAB HONOR PAD X8B 128GB + 4GB RAM', 'Tablet Honor Pad X8b pantalla ultra delgada 128GB + 4GB RAM con audio envolvente', 1005000, 1155000, 12, 'ACTIVO', NULL, 7, 5, 9, 0, 'PRD001153', NULL, 0, 'ninguno', NULL, NULL),
(1154, 1, 43, 'EQUIPO', 'TAB INFINIX XPAD 20 X1102 LTE 11\" 128GB + 4GB RAM + ESTUCHE', 'Tablet Infinix XPAD 20 modelo X1102 4G LTE 11 pulgadas 128GB + 4GB RAM con estuche', 1101500, 1251500, 11, 'ACTIVO', NULL, 6, 5, 9, 0, 'PRD001154', NULL, 0, 'ninguno', NULL, NULL),
(1155, 1, 44, 'EQUIPO', 'TAB IPRO KIDS TURBO5 TURBO6 7\" 32GB + 2GB RAM', 'Tablet para niños IPRO Kids Turbo 7 pulgadas 32GB + 2GB RAM con asa anticaídas', 319500, 469500, 22, 'ACTIVO', NULL, 12, 10, 9, 0, 'PRD001155', NULL, 0, 'ninguno', NULL, NULL),
(1156, 1, 44, 'EQUIPO', 'TAB IPRO SPEED 4 DUAL LTE 32GB + 2GB RAM NEGRO + ACC', 'Tablet IPRO Speed 4 Dual SIM 4G LTE 32GB + 2GB RAM color negro con accesorios', 276000, 426000, 27, 'ACTIVO', NULL, 15, 12, 9, 0, 'PRD001156', NULL, 0, 'ninguno', NULL, NULL),
(1157, 1, 44, 'EQUIPO', 'TAB IPRO SPEED 4 SPEED 5 4G 32GB + 2GB RAM', 'Tablet IPRO Speed 5 conectividad 4G LTE 32GB almacenamiento y 2GB RAM', 437000, 587000, 18, 'ACTIVO', NULL, 10, 8, 9, 0, 'PRD001157', NULL, 0, 'ninguno', NULL, NULL),
(1158, 1, 45, 'EQUIPO', 'EQ NOTEBOOK ACER AL15-36P-32XP 128GB/8GB 15.6\"', 'Notebook Acer Aspire Lite AL15-36P-32XP Intel N100 128GB SSD 8GB RAM pantalla 15.6 pulgadas', 2341000, 2491000, 8, 'ACTIVO', NULL, 5, 3, 10, 0, 'PRD001158', NULL, 0, 'ninguno', NULL, NULL),
(1159, 1, 31, 'EQUIPO', 'EQ NOTEBOOK AUDISAT X105 I5 512GB 16RAM 15,6\"', 'Notebook Audisat X105 procesador Intel Core i5 512GB SSD NVMe 16GB RAM pantalla 15.6 pulgadas FHD', 2698500, 2848500, 7, 'ACTIVO', NULL, 4, 3, 10, 0, 'PRD001159', NULL, 0, 'ninguno', NULL, NULL),
(1160, 1, 46, 'EQUIPO', 'EQ NOTEBOOK GAMER GIGABYTE A16 RYZEN 7 16GB/1TB SSD 16.0\" FH', 'Notebook Gamer Gigabyte A16 procesador AMD Ryzen 7 16GB RAM 1TB SSD pantalla 16.0 pulgadas 165Hz gráfica dedicada', 9771000, 9921000, 3, 'ACTIVO', NULL, 2, 1, 10, 0, 'PRD001160', NULL, 0, 'ninguno', NULL, NULL),
(1161, 1, 47, 'EQUIPO', 'EQ NOTEBOOK HP 14\" 14-DQ6102DX N150 128GB + 4GB W11', 'Notebook HP 14 pulgadas modelo 14-DQ6102DX Intel N150 128GB UFS + 4GB RAM Windows 11 Home', 1730500, 1880500, 10, 'ACTIVO', NULL, 6, 4, 10, 0, 'PRD001161', NULL, 0, 'ninguno', NULL, NULL),
(1162, 1, 47, 'EQUIPO', 'EQ NOTEBOOK HP 14\" 14-DQ6105DX N150 128GB + 4GB W11', 'Notebook HP 14 pulgadas modelo 14-DQ6105DX Intel N150 128GB + 4GB RAM Windows 11 portátil ligera', 1711500, 1861500, 11, 'ACTIVO', NULL, 6, 5, 10, 0, 'PRD001162', NULL, 0, 'ninguno', NULL, NULL),
(1163, 1, 42, 'EQUIPO', 'CEL HONOR MAGIC 6 LITE 5G 256GB + 8RAM GB', 'Smartphone Honor Magic 6 Lite 5G pantalla curva AMOLED 120Hz 256GB + 8GB RAM cámara 108MP', 1557500, 1707500, 10, 'ACTIVO', NULL, 6, 4, 11, 0, 'PRD001163', NULL, 0, 'ninguno', NULL, NULL),
(1164, 1, 42, 'EQUIPO', 'CEL HONOR MAGIC 7 LITE 5G DUAL 512GB + 12GB RAM + LITAG REG.', 'Smartphone Honor Magic 7 Lite 5G Dual SIM 512GB + 12GB RAM incluye Litag de regalo', 1845000, 1995000, 8, 'ACTIVO', NULL, 5, 3, 11, 0, 'PRD001164', NULL, 0, 'ninguno', NULL, NULL),
(1165, 1, 42, 'EQUIPO', 'CEL HONOR PLAY 20A 128GB + 4GB RAM', 'Celular Honor Play 20A pantalla 6.5 pulgadas 128GB almacenamiento + 4GB RAM batería 5000mAh', 776000, 926000, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001165', NULL, 0, 'ninguno', NULL, NULL),
(1166, 1, 42, 'EQUIPO', 'CEL HONOR PLAY 20C 64GB + 4GB RAM', 'Celular Honor Play 20C pantalla HD 64GB almacenamiento + 4GB RAM color negro', 644000, 794000, 18, 'ACTIVO', NULL, 10, 8, 11, 0, 'PRD001166', NULL, 0, 'ninguno', NULL, NULL),
(1167, 1, 42, 'EQUIPO', 'CEL HONOR X5C 128GB + 4GB RAM', 'Smartphone Honor X5C pantalla 6.56 pulgadas 128GB + 4GB RAM batería de larga duración', 715000, 865000, 16, 'ACTIVO', NULL, 9, 7, 11, 0, 'PRD001167', NULL, 0, 'ninguno', NULL, NULL),
(1168, 1, 42, 'EQUIPO', 'CEL HONOR X5C PLUS 256GB + 4GB RAM', 'Smartphone Honor X5C Plus gran almacenamiento 256GB + 4GB RAM cámara dual', 765000, 915000, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001168', NULL, 0, 'ninguno', NULL, NULL),
(1169, 1, 42, 'EQUIPO', 'CEL HONOR X5C PLUS 256GB + 4GB RAM C/ HOPPY', 'Smartphone Honor X5C Plus 256GB + 4GB RAM combo especial con hoppy térmico incluido', 822500, 972500, 12, 'ACTIVO', NULL, 7, 5, 11, 0, 'PRD001169', NULL, 0, 'ninguno', NULL, NULL),
(1170, 1, 42, 'EQUIPO', 'CEL HONOR X6B 256GB + 4GB RAM', 'Smartphone Honor X6b pantalla 90Hz 256GB + 4GB RAM cámara 50MP carga rápida 35W', 966000, 1116000, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001170', NULL, 0, 'ninguno', NULL, NULL),
(1171, 1, 42, 'EQUIPO', 'CEL HONOR X7D 4G 256GB + 6GB RAM', 'Smartphone Honor X7d pantalla fluida 256GB almacenamiento + 6GB RAM procesador Snapdragon', 112000, 262000, 11, 'ACTIVO', NULL, 6, 5, 11, 0, 'PRD001171', NULL, 0, 'ninguno', NULL, NULL),
(1172, 1, 42, 'EQUIPO', 'CEL HONOR X7D 4G 256GB + 8GB RAM', 'Smartphone Honor X7d 256GB almacenamiento + 8GB RAM cámara triple con IA', 1200000, 1350000, 12, 'ACTIVO', NULL, 7, 5, 11, 0, 'PRD001172', NULL, 0, 'ninguno', NULL, NULL),
(1173, 1, 2, 'EQUIPO', 'CEL SAM A07 128GB + 4GB RAM', 'Samsung Galaxy A07 pantalla 6.7 pulgadas 128GB + 4GB RAM batería 5000mAh', 917500, 1067500, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001173', NULL, 0, 'ninguno', NULL, NULL),
(1174, 1, 2, 'EQUIPO', 'CEL SAM A07 64GB + 4GB RAM', 'Samsung Galaxy A07 versión 64GB + 4GB RAM pantalla HD+ 90Hz', 785000, 935000, 18, 'ACTIVO', NULL, 10, 8, 11, 0, 'PRD001174', NULL, 0, 'ninguno', NULL, NULL),
(1175, 1, 2, 'EQUIPO', 'CEL SAM A16 128GB + 4GB RAM', 'Samsung Galaxy A16 pantalla Super AMOLED FHD+ 128GB + 4GB RAM cámara 50MP', 945000, 1095000, 21, 'ACTIVO', NULL, 12, 9, 11, 0, 'PRD001175', NULL, 0, 'ninguno', NULL, NULL),
(1176, 1, 2, 'EQUIPO', 'CEL SAM A17 128GB + 4GB RAM', 'Samsung Galaxy A17 pantalla Super AMOLED 128GB + 4GB RAM diseño ultra delgado', 1046500, 1196500, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001176', NULL, 0, 'ninguno', NULL, NULL),
(1177, 1, 2, 'EQUIPO', 'CEL SAM A17 256GB + 8GB RAM', 'Samsung Galaxy A17 gran capacidad 256GB + 8GB RAM procesador Octa-Core', 1691500, 1841500, 12, 'ACTIVO', NULL, 7, 5, 11, 0, 'PRD001177', NULL, 0, 'ninguno', NULL, NULL),
(1178, 1, 2, 'EQUIPO', 'CEL SAM A27 5G 256GB + 8GB RAM', 'Samsung Galaxy A27 conectividad 5G 256GB + 8GB RAM pantalla 120Hz con lector de huella', 1992000, 2142000, 10, 'ACTIVO', NULL, 6, 4, 11, 0, 'PRD001178', NULL, 0, 'ninguno', NULL, NULL),
(1179, 1, 2, 'EQUIPO', 'CEL SAM A36 5G 128GB + 8GB RAM', 'Samsung Galaxy A36 5G resistencia IP67 128GB + 8GB RAM cámara con estabilización OIS', 1770000, 1920000, 11, 'ACTIVO', NULL, 6, 5, 11, 0, 'PRD001179', NULL, 0, 'ninguno', NULL, NULL),
(1180, 1, 2, 'EQUIPO', 'CEL SAM A37 5G 128GB + 6GB RAM', 'Samsung Galaxy A37 5G pantalla Super AMOLED Plus 128GB + 6GB RAM', 1935000, 2085000, 9, 'ACTIVO', NULL, 5, 4, 11, 0, 'PRD001180', NULL, 0, 'ninguno', NULL, NULL),
(1181, 1, 2, 'EQUIPO', 'CEL SAM A37 5G 256GB + 8GB RAM', 'Samsung Galaxy A37 5G versión premium 256GB + 8GB RAM sonido estéreo Dolby Atmos', 2355000, 2505000, 10, 'ACTIVO', NULL, 6, 4, 11, 0, 'PRD001181', NULL, 0, 'ninguno', NULL, NULL),
(1182, 1, 2, 'EQUIPO', 'CEL SAM A56 5G 256GB + 8GB RAM', 'Samsung Galaxy A56 5G cámara profesional 50MP 256GB + 8GB RAM cuerpo de vidrio y metal', 2243000, 2393000, 9, 'ACTIVO', NULL, 5, 4, 11, 0, 'PRD001182', NULL, 0, 'ninguno', NULL, NULL),
(1183, 1, 2, 'EQUIPO', 'CEL SAM A57 5G 256GB + 8GB RAM', 'Samsung Galaxy A57 5G pantalla 120Hz Vision Booster 256GB + 8GB RAM', 2544500, 2694500, 7, 'ACTIVO', NULL, 4, 3, 11, 0, 'PRD001183', NULL, 0, 'ninguno', NULL, NULL),
(1184, 1, 2, 'EQUIPO', 'CEL SAM S26 256GB + 12GB RAM', 'Samsung Galaxy S26 gama alta pantalla Dynamic AMOLED 2X 256GB + 12GB RAM Snapdragon 8 Gen', 4592500, 4742500, 5, 'ACTIVO', NULL, 3, 2, 11, 0, 'PRD001184', NULL, 0, 'ninguno', NULL, NULL),
(1185, 1, 2, 'EQUIPO', 'CEL SAM S26 FE 256GB + 8GB RAM', 'Samsung Galaxy S26 Fan Edition 256GB + 8GB RAM cámaras flagship y Galaxy AI', 4282000, 4432000, 6, 'ACTIVO', NULL, 3, 3, 11, 0, 'PRD001185', NULL, 0, 'ninguno', NULL, NULL),
(1186, 1, 2, 'EQUIPO', 'CEL SAM S26 ULTRA DUAL 5G 256GB + 12GB RAM', 'Samsung Galaxy S26 Ultra Dual 5G con S-Pen integrado cámara 200MP zoom 100x 256GB + 12GB RAM', 5895500, 6045500, 4, 'ACTIVO', NULL, 2, 2, 11, 0, 'PRD001186', NULL, 0, 'ninguno', NULL, NULL),
(1187, 1, 37, 'EQUIPO', 'CEL OUKITEL C17 128GB + 4GB RAM', 'Smartphone OUKITEL C17 pantalla perforada HD+ 128GB + 4GB RAM', 604500, 754500, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001187', NULL, 0, 'ninguno', NULL, NULL),
(1188, 1, 37, 'EQUIPO', 'CEL OUKITEL C17 PLUS 256GB + 6GB RAM', 'Smartphone OUKITEL C17 Plus mayor capacidad 256GB + 6GB RAM batería 4500mAh', 887500, 1037500, 12, 'ACTIVO', NULL, 7, 5, 11, 0, 'PRD001188', NULL, 0, 'ninguno', NULL, NULL),
(1189, 1, 37, 'EQUIPO', 'CEL OUKITEL C26 128GB + 4GB RAM', 'Smartphone OUKITEL C26 pantalla fluida 128GB + 4GB RAM lector de huella', 576000, 726000, 16, 'ACTIVO', NULL, 9, 7, 11, 0, 'PRD001189', NULL, 0, 'ninguno', NULL, NULL),
(1190, 1, 37, 'EQUIPO', 'CEL OUKITEL C26 128GB + 6GB RAM', 'Smartphone OUKITEL C26 versión 128GB + 6GB RAM cámara triple con flash', 725000, 875000, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001190', NULL, 0, 'ninguno', NULL, NULL),
(1191, 1, 37, 'EQUIPO', 'CEL OUKITEL C3 256GB + 4GB RAM', 'Smartphone OUKITEL C3 pantalla 6.5 pulgadas 256GB + 4GB RAM', 630000, 780000, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001191', NULL, 0, 'ninguno', NULL, NULL),
(1192, 1, 37, 'EQUIPO', 'CEL OUKITEL C65 128GB + 4GB RAM', 'Smartphone OUKITEL C65 diseño moderno 128GB + 4GB RAM Android puro', 569500, 719500, 18, 'ACTIVO', NULL, 10, 8, 11, 0, 'PRD001192', NULL, 0, 'ninguno', NULL, NULL),
(1193, 1, 37, 'EQUIPO', 'CEL OUKITEL C68 256GB + 8GB RAM', 'Smartphone OUKITEL C68 pantalla 6.78 pulgadas 256GB + 8GB RAM 50MP', 791000, 941000, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001193', NULL, 0, 'ninguno', NULL, NULL),
(1194, 1, 37, 'EQUIPO', 'CEL OUKITEL C68 512GB + 8GB RAM', 'Smartphone OUKITEL C68 enorme memoria 512GB + 8GB RAM batería 5000mAh', 829000, 979000, 12, 'ACTIVO', NULL, 7, 5, 11, 0, 'PRD001194', NULL, 0, 'ninguno', NULL, NULL),
(1195, 1, 37, 'EQUIPO', 'CEL OUKITEL C68 PLUS 128GB + 4GB RAM', 'Smartphone OUKITEL C68 Plus pantalla FHD+ 128GB + 4GB RAM', 803500, 953500, 11, 'ACTIVO', NULL, 6, 5, 11, 0, 'PRD001195', NULL, 0, 'ninguno', NULL, NULL),
(1196, 1, 37, 'EQUIPO', 'CEL OUKITEL C68 PLUS 512GB + 8GB RAM', 'Smartphone OUKITEL C68 Plus tope de gama 512GB + 8GB RAM cámara IA', 990000, 1140000, 10, 'ACTIVO', NULL, 6, 4, 11, 0, 'PRD001196', NULL, 0, 'ninguno', NULL, NULL),
(1197, 1, 37, 'EQUIPO', 'CEL OUKITEL P1 PRO 256GB + 4GB RAM', 'Smartphone OUKITEL P1 Pro pantalla AMOLED 120Hz 256GB + 4GB RAM', 1085000, 1235000, 9, 'ACTIVO', NULL, 5, 4, 11, 0, 'PRD001197', NULL, 0, 'ninguno', NULL, NULL),
(1198, 1, 37, 'EQUIPO', 'CEL OUKITEL WP60 256GB + 8GB RAM', 'Celular blindado militar rugerizado OUKITEL WP60 256GB + 8GB RAM sumergible y anticaídas', 1594000, 1744000, 13, 'ACTIVO', NULL, 10, 3, 11, 0, 'PRD001198', NULL, 0, 'ninguno', NULL, NULL),
(1199, 1, 48, 'EQUIPO', 'CEL REALME 12 PRO+ 512GB + 12GB RAM', 'Smartphone Realme 12 Pro+ lente periscópico cámara Sony 512GB + 12GB RAM cuero vegano', 3011500, 3161500, 7, 'ACTIVO', NULL, 4, 3, 11, 0, 'PRD001199', NULL, 0, 'ninguno', NULL, NULL),
(1200, 1, 48, 'EQUIPO', 'CEL REALME C21Y 64GB + 4GB RAM', 'Smartphone Realme C21Y batería gigante 5000mAh 64GB + 4GB RAM triple cámara', 800000, 950000, 16, 'ACTIVO', NULL, 9, 7, 11, 0, 'PRD001200', NULL, 0, 'ninguno', NULL, NULL),
(1201, 1, 48, 'EQUIPO', 'CEL REALME C65 256GB + 8GB RAM', 'Smartphone Realme C65 diseño ultradelgado 256GB + 8GB RAM carga rápida 45W', 1065000, 1215000, 12, 'ACTIVO', NULL, 7, 5, 11, 0, 'PRD001201', NULL, 0, 'ninguno', NULL, NULL),
(1202, 1, 48, 'EQUIPO', 'CEL REALME NOTE 60 256GB + 4GB RAM', 'Smartphone Realme Note 60 pantalla 90Hz ArmorShell 256GB + 4GB RAM', 679500, 829500, 18, 'ACTIVO', NULL, 10, 8, 11, 0, 'PRD001202', NULL, 0, 'ninguno', NULL, NULL),
(1203, 1, 15, 'EQUIPO', 'CEL XIA 12 LITE 128GB + 8GB RAM', 'Xiaomi 12 Lite pantalla AMOLED 120Hz HDR10+ 128GB + 8GB RAM cámara 108MP', 2642500, 2792500, 16, 'ACTIVO', NULL, 12, 4, 11, 0, 'PRD001203', NULL, 0, 'ninguno', NULL, NULL),
(1204, 1, 15, 'EQUIPO', 'CEL XIA 12 LITE 256GB + 8GB RAM', 'Xiaomi 12 Lite versión 256GB + 8GB RAM diseño ligero color tornasol', 2877500, 3027500, 7, 'ACTIVO', NULL, 4, 3, 11, 0, 'PRD001204', NULL, 0, 'ninguno', NULL, NULL),
(1205, 1, 15, 'EQUIPO', 'CEL XIA NOTE 11 PRO+ 256GB + 8GB RAM', 'Redmi Note 11 Pro+ carga ultrarrápida 120W HyperCharge 256GB + 8GB RAM sonido JBL', 2535000, 2685000, 9, 'ACTIVO', NULL, 5, 4, 11, 0, 'PRD001205', NULL, 0, 'ninguno', NULL, NULL),
(1206, 1, 15, 'EQUIPO', 'CEL XIA NOTE 12 PRO 5G 128GB + 6GB RAM', 'Redmi Note 12 Pro 5G pantalla OLED 128GB + 6GB RAM cámara Sony IMX766 OIS', 2073000, 2223000, 5, 'ACTIVO', NULL, 0, 5, 11, 0, 'PRD001206', NULL, 0, 'ninguno', NULL, NULL),
(1207, 1, 15, 'EQUIPO', 'CEL XIA NOTE 12 PRO+ 256GB + 8GB RAM', 'Redmi Note 12 Pro+ cámara 200MP carga 120W 256GB + 8GB RAM pantalla 120Hz', 2737000, 2887000, 8, 'ACTIVO', NULL, 5, 3, 11, 0, 'PRD001207', NULL, 0, 'ninguno', NULL, NULL),
(1208, 1, 15, 'EQUIPO', 'CEL XIA NOTE 14 PRO+ 5G 256GB + 8GB RAM', 'Redmi Note 14 Pro+ 5G pantalla curva AMOLED 1.5K resistencia al agua IP68 256GB', 2160000, 2310000, 10, 'ACTIVO', NULL, 6, 4, 11, 0, 'PRD001208', NULL, 0, 'ninguno', NULL, NULL),
(1209, 1, 15, 'EQUIPO', 'CEL XIA NOTE 15 128GB + 6GB RAM', 'Redmi Note 15 pantalla FHD+ AMOLED 128GB + 6GB RAM batería 5000mAh', 1168500, 1318500, 16, 'ACTIVO', NULL, 9, 7, 11, 0, 'PRD001209', NULL, 0, 'ninguno', NULL, NULL),
(1210, 1, 15, 'EQUIPO', 'CEL XIA NOTE 15 256GB + 8GB RAM', 'Redmi Note 15 mayor almacenamiento 256GB + 8GB RAM cámara triple 108MP', 1375000, 1525000, 18, 'ACTIVO', NULL, 10, 8, 11, 0, 'PRD001210', NULL, 0, 'ninguno', NULL, NULL),
(1211, 1, 15, 'EQUIPO', 'CEL XIA NOTE 15 PRO 256GB + 8GB RAM', 'Redmi Note 15 Pro procesador MediaTek Helio G99-Ultra 256GB + 8GB RAM carga 67W', 1605000, 1755000, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001211', NULL, 0, 'ninguno', NULL, NULL),
(1212, 1, 15, 'EQUIPO', 'CEL XIA NOTE 15 PRO 5G 256GB + 8GB RAM', 'Redmi Note 15 Pro 5G conectividad de última generación 256GB + 8GB RAM', 1895000, 2045000, 12, 'ACTIVO', NULL, 7, 5, 11, 0, 'PRD001212', NULL, 0, 'ninguno', NULL, NULL);
INSERT INTO `productos` (`id_producto`, `id_tipo_impuesto`, `id_marcas`, `tipo_producto`, `nombre_prod`, `descripcion`, `precio_compra`, `precio_venta`, `stock_actual`, `estado`, `imagen_url`, `stock_sucursal1`, `stock_sucursal2`, `id_categoria`, `bloqueado`, `codigo_barra`, `fecha_bloqueo`, `tiene_variantes`, `tipo_variante`, `opciones_colores`, `opciones_disenos`) VALUES
(1213, 1, 15, 'EQUIPO', 'CEL XIA NOTE 15 PRO 5G 512GB + 12GB RAM', 'Redmi Note 15 Pro 5G máxima configuración 512GB + 12GB RAM pantalla AMOLED 120Hz', 2255000, 2405000, 4, 'ACTIVO', NULL, 0, 4, 11, 0, 'PRD001213', NULL, 0, 'ninguno', NULL, NULL),
(1214, 1, 15, 'EQUIPO', 'CEL XIA NOTE 17 4G 128GB + 4GB RAM', 'Redmi Note 17 4G pantalla 6.67 pulgadas 128GB + 4GB RAM batería de larga duración', 1255000, 1405000, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001214', NULL, 0, 'ninguno', NULL, NULL),
(1215, 1, 15, 'EQUIPO', 'CEL XIA NOTE 17 4G 256GB + 6GB RAM', 'Redmi Note 17 4G versión 256GB + 6GB RAM cámara 108MP carga turbo 33W', 1475000, 1625000, 12, 'ACTIVO', NULL, 7, 5, 11, 0, 'PRD001215', NULL, 0, 'ninguno', NULL, NULL),
(1216, 1, 15, 'EQUIPO', 'CEL XIA NOTE 17 PRO MAX 5G 256GB + 8GB RAM', 'Redmi Note 17 Pro Max 5G pantalla 144Hz 256GB + 8GB RAM cámaras Leica avanzadas', 2685000, 2835000, 9, 'ACTIVO', NULL, 5, 4, 11, 0, 'PRD001216', NULL, 0, 'ninguno', NULL, NULL),
(1217, 1, 15, 'EQUIPO', 'CEL XIA NOTE 17 PRO MAX 5G 512GB + 8GB RAM', 'Redmi Note 17 Pro Max 5G versión 512GB almacenamiento + 8GB RAM carga 120W', 2985000, 3135000, 7, 'ACTIVO', NULL, 4, 3, 11, 0, 'PRD001217', NULL, 0, 'ninguno', NULL, NULL),
(1218, 1, 15, 'EQUIPO', 'CEL XIA POCO F3 128GB + 6GB RAM', 'Xiaomi Poco F3 procesador Snapdragon 870 pantalla E4 AMOLED 120Hz 128GB + 6GB RAM', 2315000, 2465000, 10, 'ACTIVO', NULL, 6, 4, 11, 0, 'PRD001218', NULL, 0, 'ninguno', NULL, NULL),
(1219, 1, 15, 'EQUIPO', 'CEL XIA POCO F5 256GB + 12GB RAM', 'Xiaomi Poco F5 Snapdragon 7+ Gen 2 pantalla Flow AMOLED 120Hz 256GB + 12GB RAM', 2510000, 2660000, 9, 'ACTIVO', NULL, 5, 4, 11, 0, 'PRD001219', NULL, 0, 'ninguno', NULL, NULL),
(1220, 1, 15, 'EQUIPO', 'CEL XIA POCO F5 PRO 512GB + 12GB RAM', 'Xiaomi Poco F5 Pro pantalla WQHD+ 120Hz Snapdragon 8+ Gen 1 512GB + 12GB RAM carga inalámbrica', 3570000, 3720000, 6, 'ACTIVO', NULL, 3, 3, 11, 0, 'PRD001220', NULL, 0, 'ninguno', NULL, NULL),
(1221, 1, 15, 'EQUIPO', 'CEL XIA POCO M4 5G 64GB + 4GB RAM', 'Xiaomi Poco M4 5G pantalla 90Hz 64GB + 4GB RAM procesador MediaTek Dimensity 700', 1263000, 1413000, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001221', NULL, 0, 'ninguno', NULL, NULL),
(1222, 1, 15, 'EQUIPO', 'CEL XIA POCO M5S 128GB + 6GB RAM', 'Xiaomi Poco M5s pantalla AMOLED altavoces duales 128GB + 6GB RAM cámara cuádruple 64MP', 1245500, 1395500, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001222', NULL, 0, 'ninguno', NULL, NULL),
(1223, 1, 15, 'EQUIPO', 'CEL XIA POCO M8 5G 256GB + 8GB RAM', 'Xiaomi Poco M8 5G conectividad ultra rápida 256GB + 8GB RAM batería 5000mAh', 1465000, 1615000, 12, 'ACTIVO', NULL, 7, 5, 11, 0, 'PRD001223', NULL, 0, 'ninguno', NULL, NULL),
(1224, 1, 15, 'EQUIPO', 'CEL XIA POCO X4 PRO 128GB + 6GB RAM', 'Xiaomi Poco X4 Pro 5G pantalla AMOLED 120Hz 128GB + 6GB RAM cámara 108MP carga 67W', 2116500, 2266500, 10, 'ACTIVO', NULL, 6, 4, 11, 0, 'PRD001224', NULL, 0, 'ninguno', NULL, NULL),
(1225, 1, 15, 'EQUIPO', 'CEL XIA POCO X4 PRO 256GB + 8GB RAM', 'Xiaomi Poco X4 Pro 5G versión 256GB + 8GB RAM diseño láser de cristal', 2391500, 2541500, 9, 'ACTIVO', NULL, 5, 4, 11, 0, 'PRD001225', NULL, 0, 'ninguno', NULL, NULL),
(1226, 1, 15, 'EQUIPO', 'CEL XIA RD 10 64GB + 4GB RAM', 'Xiaomi Redmi 10 pantalla 90Hz AdaptiveSync 64GB + 4GB RAM cámara 50MP', 1290000, 1440000, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001226', NULL, 0, 'ninguno', NULL, NULL),
(1227, 1, 15, 'EQUIPO', 'CEL XIA RD 15C 128GB + 4GB RAM', 'Xiaomi Redmi 15C pantalla inmersiva 6.88 pulgadas 120Hz 128GB + 4GB RAM', 828500, 978500, 21, 'ACTIVO', NULL, 12, 9, 11, 0, 'PRD001227', NULL, 0, 'ninguno', NULL, NULL),
(1228, 1, 15, 'EQUIPO', 'CEL XIA RD 15C 256GB + 8GB RAM', 'Xiaomi Redmi 15C versión 256GB almacenamiento + 8GB RAM cámara 50MP con IA', 984500, 1134500, 18, 'ACTIVO', NULL, 10, 8, 11, 0, 'PRD001228', NULL, 0, 'ninguno', NULL, NULL),
(1229, 1, 15, 'EQUIPO', 'CEL XIA RD 17 4G 128GB + 4GB RAM', 'Xiaomi Redmi 17 4G pantalla fluida 128GB + 4GB RAM batería de larga duración', 1015000, 1165000, 16, 'ACTIVO', NULL, 9, 7, 11, 0, 'PRD001229', NULL, 0, 'ninguno', NULL, NULL),
(1230, 1, 15, 'EQUIPO', 'CEL XIA RD 17 4G 256GB + 4GB RAM', 'Xiaomi Redmi 17 4G gran almacenamiento 256GB + 4GB RAM carga rápida USB-C', 1200000, 1350000, 14, 'ACTIVO', NULL, 8, 6, 11, 0, 'PRD001230', NULL, 0, 'ninguno', NULL, NULL),
(1231, 1, 15, 'EQUIPO', 'CEL XIA RD 9T 64GB + 4GB RAM', 'Xiaomi Redmi 9T altavoces estéreo batería colosal 6000mAh 64GB + 4GB RAM', 1255000, 1405000, 12, 'ACTIVO', NULL, 7, 5, 11, 0, 'PRD001231', NULL, 0, 'ninguno', NULL, NULL),
(1232, 1, 15, 'EQUIPO', 'CEL XIA RD A7 PRO 128GB + 4GB RAM', 'Xiaomi Redmi A7 Pro pantalla HD+ 128GB + 4GB RAM sensor de huellas dactilares', 753000, 903000, 21, 'ACTIVO', NULL, 12, 9, 11, 0, 'PRD001232', NULL, 0, 'ninguno', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor_` int(11) NOT NULL,
  `id_ciudad` int(11) NOT NULL DEFAULT 1,
  `nombre_apellido_prov` varchar(100) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `telefono_proveedor` varchar(30) NOT NULL DEFAULT '',
  `ruc_proveedor` varchar(30) NOT NULL,
  `direccion_proveedor` varchar(150) NOT NULL DEFAULT '',
  `email_proveedor` varchar(100) NOT NULL DEFAULT '',
  `estado` varchar(15) NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor_`, `id_ciudad`, `nombre_apellido_prov`, `razon_social`, `telefono_proveedor`, `ruc_proveedor`, `direccion_proveedor`, `email_proveedor`, `estado`) VALUES
(1, 5, 'TECNOMOVIL BATTILANA ', 'TECNOMOVIL', '981121200', '1221225-0', 'BATTILANA C/RODRIGUEZ DE FRANCIA', 'alarcon.dominguez22@gmail.com', 'ACTIVO'),
(2, 6, 'TRIUM', 'TRIUM', '994848359', '8024151-0', 'Avda Lopes Godoy', 'alarcon.dominguez22@gmail.com', 'ACTIVO'),
(3, 1, '', 'Prueba', '0994848359', '8002259-8', 'Avda Lopes Godoy', 'alarcon.dominguez22@gmail.com', 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `descripcion_rol` varchar(100) NOT NULL,
  `estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `descripcion_rol`, `estado`) VALUES
(1, 'SUPERADMIN', 'ACTIVO'),
(2, 'ADMIN', 'ACTIVO'),
(3, 'TECNICO', 'ACTIVO'),
(4, 'TECNICO Y SUPERVISOR', 'ACTIVO'),
(5, 'CAJERA', 'ACTIVO'),
(6, 'VENDEDOR', 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stock_deposito`
--

CREATE TABLE `stock_deposito` (
  `id_producto` int(11) NOT NULL,
  `id_deposito` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `stock_deposito`
--

INSERT INTO `stock_deposito` (`id_producto`, `id_deposito`, `cantidad`) VALUES
(1008, 1, 12),
(1009, 1, 9),
(1010, 1, 18),
(1011, 1, 19),
(1012, 1, 7),
(1013, 1, 5),
(1014, 1, 11),
(1015, 1, 16),
(1016, 1, 9),
(1017, 1, 4),
(1018, 1, 12),
(1019, 1, 10),
(1020, 1, 14),
(1021, 1, 10),
(1022, 1, 25),
(1023, 1, 18),
(1024, 1, 14),
(1025, 1, 35),
(1026, 1, 30),
(1027, 1, 24),
(1028, 1, 20),
(1029, 1, 27),
(1030, 1, 18),
(1031, 1, 19),
(1032, 1, 45),
(1033, 1, 55),
(1034, 1, 35),
(1035, 1, 27),
(1036, 1, 20),
(1037, 1, 16),
(1038, 1, 30),
(1039, 1, 38),
(1040, 1, 30),
(1041, 1, 90),
(1042, 1, 105),
(1043, 1, 145),
(1044, 1, 90),
(1045, 1, 97),
(1046, 1, 120),
(1047, 1, 95),
(1048, 1, 80),
(1049, 1, 55),
(1050, 1, 70),
(1051, 1, 75),
(1052, 1, 55),
(1053, 1, 65),
(1054, 1, 90),
(1055, 1, 60),
(1056, 1, 45),
(1057, 1, 50),
(1058, 1, 50),
(1059, 1, 80),
(1060, 1, 27),
(1061, 1, 38),
(1062, 1, 14),
(1063, 1, 18),
(1064, 1, 22),
(1065, 1, 55),
(1066, 1, 45),
(1067, 1, 65),
(1068, 1, 55),
(1069, 1, 38),
(1070, 1, 75),
(1071, 1, 45),
(1072, 1, 18),
(1073, 1, 90),
(1074, 1, 105),
(1075, 1, 27),
(1076, 1, 24),
(1077, 1, 9),
(1078, 1, 14),
(1079, 1, 11),
(1080, 1, 5),
(1081, 1, 4),
(1082, 1, 18),
(1083, 1, 12),
(1084, 1, 11),
(1085, 1, 7),
(1086, 1, 5),
(1087, 1, 20),
(1088, 1, 13),
(1089, 1, 10),
(1090, 1, 8),
(1091, 1, 7),
(1092, 1, 3),
(1093, 1, 16),
(1094, 1, 7),
(1095, 1, 5),
(1096, 1, 11),
(1097, 1, 9),
(1098, 1, 7),
(1099, 1, 9),
(1100, 1, 14),
(1101, 1, 11),
(1102, 1, 13),
(1103, 1, 15),
(1104, 1, 11),
(1105, 1, 12),
(1106, 1, 9),
(1107, 1, 14),
(1108, 1, 22),
(1109, 1, 18),
(1110, 1, 18),
(1111, 1, 18),
(1112, 1, 27),
(1113, 1, 14),
(1114, 1, 12),
(1115, 1, 10),
(1116, 1, 15),
(1117, 1, 6),
(1118, 1, 11),
(1119, 1, 9),
(1120, 1, 9),
(1121, 1, 7),
(1122, 1, 11),
(1123, 1, 10),
(1124, 1, 12),
(1125, 1, 8),
(1126, 1, 7),
(1127, 1, 10),
(1128, 1, 7),
(1129, 1, 14),
(1130, 1, 9),
(1131, 1, 24),
(1132, 1, 24),
(1133, 1, 14),
(1134, 1, 14),
(1135, 1, 12),
(1136, 1, 8),
(1137, 1, 14),
(1138, 1, 11),
(1139, 1, 13),
(1140, 1, 14),
(1141, 1, 12),
(1142, 1, 16),
(1143, 1, 14),
(1144, 1, 11),
(1145, 1, 15),
(1146, 1, 15),
(1147, 1, 12),
(1148, 1, 14),
(1149, 1, 14),
(1150, 1, 18),
(1151, 1, 18),
(1152, 1, 10),
(1153, 1, 12),
(1154, 1, 11),
(1155, 1, 22),
(1156, 1, 27),
(1157, 1, 18),
(1158, 1, 8),
(1159, 1, 7),
(1160, 1, 3),
(1161, 1, 10),
(1162, 1, 11),
(1163, 1, 10),
(1164, 1, 8),
(1165, 1, 14),
(1166, 1, 18),
(1167, 1, 16),
(1168, 1, 14),
(1169, 1, 12),
(1170, 1, 14),
(1171, 1, 11),
(1172, 1, 12),
(1173, 1, 14),
(1174, 1, 18),
(1175, 1, 21),
(1176, 1, 14),
(1177, 1, 12),
(1178, 1, 10),
(1179, 1, 11),
(1180, 1, 9),
(1181, 1, 10),
(1182, 1, 9),
(1183, 1, 7),
(1184, 1, 5),
(1185, 1, 6),
(1186, 1, 4),
(1187, 1, 14),
(1188, 1, 12),
(1189, 1, 16),
(1190, 1, 14),
(1191, 1, 14),
(1192, 1, 18),
(1193, 1, 14),
(1194, 1, 12),
(1195, 1, 11),
(1196, 1, 10),
(1197, 1, 9),
(1198, 1, 8),
(1199, 1, 7),
(1200, 1, 16),
(1201, 1, 12),
(1202, 1, 18),
(1203, 1, 6),
(1204, 1, 7),
(1205, 1, 9),
(1206, 1, 5),
(1207, 1, 8),
(1208, 1, 10),
(1209, 1, 16),
(1210, 1, 18),
(1211, 1, 14),
(1212, 1, 12),
(1213, 1, 4),
(1214, 1, 14),
(1215, 1, 12),
(1216, 1, 9),
(1217, 1, 7),
(1218, 1, 10),
(1219, 1, 9),
(1220, 1, 6),
(1221, 1, 14),
(1222, 1, 14),
(1223, 1, 12),
(1224, 1, 10),
(1225, 1, 9),
(1226, 1, 14),
(1227, 1, 21),
(1228, 1, 18),
(1229, 1, 16),
(1230, 1, 14),
(1231, 1, 12),
(1232, 1, 17);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucursal`
--

CREATE TABLE `sucursal` (
  `id_sucursal` int(11) NOT NULL,
  `id_ciudad` int(11) NOT NULL,
  `nombre_sucursakl` varchar(100) NOT NULL,
  `direccion_sucursal` varchar(100) NOT NULL,
  `estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sucursal`
--

INSERT INTO `sucursal` (`id_sucursal`, `id_ciudad`, `nombre_sucursakl`, `direccion_sucursal`, `estado`) VALUES
(1, 1, 'SUCURSAL 1', 'AVDA LOPEZ GODOY', 'ACTIVO'),
(2, 1, 'SUCURSAL 2', 'AVDA MCAL LOPEZ', 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_cliente`
--

CREATE TABLE `tipo_cliente` (
  `id_tipo_cliente` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `porcentaje_descuento` int(11) NOT NULL,
  `descripcion` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_impuesto`
--

CREATE TABLE `tipo_impuesto` (
  `id_tipo_impuesto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_impuesto`
--

INSERT INTO `tipo_impuesto` (`id_tipo_impuesto`) VALUES
(1),
(2),
(3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_precio`
--

CREATE TABLE `tipo_precio` (
  `id_tipo_precio` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(30) NOT NULL,
  `estado` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `cod_usuario` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `nombre_apellido` varchar(50) NOT NULL,
  `nick_name` varchar(20) NOT NULL,
  `password` varchar(100) NOT NULL,
  `estado` varchar(10) NOT NULL,
  `intentos` int(3) NOT NULL,
  `limite_intentos` int(3) NOT NULL,
  `requiere_cambio_pass` tinyint(1) DEFAULT 0,
  `permisos_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`cod_usuario`, `id_sucursal`, `id_rol`, `nombre_apellido`, `nick_name`, `password`, `estado`, `intentos`, `limite_intentos`, `requiere_cambio_pass`, `permisos_json`) VALUES
(1, 2, 5, 'YENIFER DOMINGUEZ', 'YDDA', 'a6718e2219f01f8dbf44f5265b3c6331', 'ACTIVO', 0, 3, 0, NULL),
(3, 1, 3, 'JOHN BENEGAS', 'JAB', '202cb962ac59075b964b07152d234b70', 'ACTIVO', 0, 3, 0, NULL),
(4, 2, 1, 'JAVIER ARIAS', 'JAG', '202cb962ac59075b964b07152d234b70', 'ACTIVO', 0, 3, 0, NULL),
(5, 1, 1, 'ADMINISTRADOR GENERAL', 'ADMIN', '202cb962ac59075b964b07152d234b70', 'ACTIVO', 0, 5, 0, NULL),
(6, 1, 6, 'FRANCISCO LOPEZ AGUILAR', 'FLA', '2c2ddcb714befc3f2addcd9001288965', 'ACTIVO', 0, 5, 0, '[\"facturacion\",\"ventas_historial\",\"caja\",\"cuentas_cobrar\",\"compras\",\"productos\",\"clientes\"]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_roles`
--

CREATE TABLE `usuarios_roles` (
  `cod_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_roles`
--

INSERT INTO `usuarios_roles` (`cod_usuario`, `id_rol`) VALUES
(1, 5),
(3, 3),
(4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL,
  `id_pedido_cliente` int(11) DEFAULT NULL,
  `id_apertura_cierre_caja` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha_venta` date NOT NULL,
  `nro_factura` varchar(20) NOT NULL,
  `condicion_pago` varchar(10) NOT NULL,
  `total_venta` int(11) NOT NULL,
  `estado` varchar(20) DEFAULT 'ACTIVO',
  `metodo_entrega` varchar(50) DEFAULT 'MOSTRADOR',
  `monto_delivery` int(11) DEFAULT 0,
  `metodo_pago` varchar(50) DEFAULT 'EFECTIVO',
  `nro_comprobante` varchar(100) DEFAULT NULL,
  `id_orden_servicio` int(11) DEFAULT NULL,
  `id_entrega` int(11) DEFAULT NULL,
  `id_sucursal` int(11) NOT NULL DEFAULT 1,
  `idusuario` int(11) DEFAULT 1,
  `pagos_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `id_pedido_cliente`, `id_apertura_cierre_caja`, `id_cliente`, `fecha_venta`, `nro_factura`, `condicion_pago`, `total_venta`, `estado`, `metodo_entrega`, `monto_delivery`, `metodo_pago`, `nro_comprobante`, `id_orden_servicio`, `id_entrega`, `id_sucursal`, `idusuario`, `pagos_json`) VALUES
(70, NULL, 19, 15, '2026-10-30', '001-001-0000202', 'CONTADO', 10866000, 'CONFIRMADO', 'MOSTRADOR', 0, 'EFECTIVO', NULL, NULL, NULL, 1, 1, '[]'),
(71, NULL, 19, 15, '2026-10-30', '001-001-0000203', 'CONTADO', 14768250, 'CONFIRMADO', 'DELIVERY', 30000, 'EFECTIVO', NULL, NULL, NULL, 1, 1, '[]'),
(72, NULL, 19, 1, '2026-10-30', '001-001-0000204', 'CONTADO', 11423750, 'CONFIRMADO', 'MOSTRADOR', 0, 'EFECTIVO', NULL, NULL, NULL, 1, 1, '[]'),
(73, NULL, 19, 14, '2026-10-30', '001-001-0000205', 'CREDITO', 300000, 'CONFIRMADO', 'MOSTRADOR', 0, 'EFECTIVO', NULL, NULL, NULL, 1, 1, '[]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_detalle`
--

CREATE TABLE `ventas_detalle` (
  `id_detalle_venta` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` int(11) NOT NULL,
  `iva_10` decimal(10,0) NOT NULL,
  `iva_5` decimal(10,0) NOT NULL,
  `exenta` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas_detalle`
--

INSERT INTO `ventas_detalle` (`id_detalle_venta`, `id_venta`, `id_producto`, `descripcion`, `cantidad`, `precio_unitario`, `iva_10`, `iva_5`, `exenta`) VALUES
(1, 70, 1045, 'LAMINA 9D PRIVACIDAD IPHONE 14 PRO MAX', 3, 6500, 19500, 0, 0),
(2, 70, 1203, 'CEL XIA 12 LITE 128GB + 8GB RAM', 3, 2752500, 8257500, 0, 0),
(3, 70, 1232, 'CEL XIA RD A7 PRO 128GB + 4GB RAM', 3, 863000, 2589000, 0, 0),
(4, 71, 1047, 'LAMINA 9D VIDRIO SAMSUNG A54 5G / A34 5G', 5, 14250, 71250, 0, 0),
(5, 71, 1117, 'TAB LENOVO K11 TB-330XUP 11\" 128GB + 8GB RAM + ESTUCHE C/ TI', 1, 2229000, 2229000, 0, 0),
(6, 71, 1206, 'CEL XIA NOTE 12 PRO 5G 128GB + 6GB RAM', 6, 2073000, 12438000, 0, 0),
(7, 72, 1213, 'CEL XIA NOTE 15 PRO 5G 512GB + 12GB RAM', 5, 2284750, 11423750, 0, 0),
(8, 73, 1008, 'DISPLAY LCD SAMSUNG A03 CORE / A03 TOUCH', 2, 150000, 300000, 0, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ajuste_stock`
--
ALTER TABLE `ajuste_stock`
  ADD PRIMARY KEY (`id_ajuste_stock`),
  ADD KEY `usuarios_ajuste_stock_fk` (`idusuario`);

--
-- Indices de la tabla `apertura_cierre_caja`
--
ALTER TABLE `apertura_cierre_caja`
  ADD PRIMARY KEY (`id_apertura_cierre_caja`),
  ADD KEY `usuarios_apertura_cierre_caja_fk` (`idusuario`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categorias`);

--
-- Indices de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  ADD PRIMARY KEY (`id_ciudad`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `ciudades_clientes_fk` (`id_ciudad`);

--
-- Indices de la tabla `cobros`
--
ALTER TABLE `cobros`
  ADD PRIMARY KEY (`id_cobro`),
  ADD KEY `apertura_cierre_caja_cobros_fk` (`id_apertura_cierre_caja`);

--
-- Indices de la tabla `cobros_detalle`
--
ALTER TABLE `cobros_detalle`
  ADD PRIMARY KEY (`id_cuenta_cobrar`,`id_cobro`),
  ADD KEY `metodos_pago_cobros_detalle_fk` (`metodos_pago`),
  ADD KEY `cobros_cobros_detalle_fk` (`id_cobro`);

--
-- Indices de la tabla `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`id_compra`),
  ADD KEY `proveedores_Compra_fk` (`id_proveedor_`),
  ADD KEY `orden_compra_Compra_fk` (`id_orden_compra`);

--
-- Indices de la tabla `compras_detalle`
--
ALTER TABLE `compras_detalle`
  ADD PRIMARY KEY (`id_compra`,`id_detalle_compra`,`id_producto`),
  ADD KEY `productos_compras_detalle_fk` (`id_producto`);

--
-- Indices de la tabla `cuentas__cobrar`
--
ALTER TABLE `cuentas__cobrar`
  ADD PRIMARY KEY (`id_cuenta_cobrar`),
  ADD KEY `ventas_cuentas__cobrar_fk` (`id_venta`);

--
-- Indices de la tabla `cuotas`
--
ALTER TABLE `cuotas`
  ADD PRIMARY KEY (`id_cuota`),
  ADD KEY `fk_cuota_cuenta` (`id_cuenta_cobrar`);

--
-- Indices de la tabla `deposito`
--
ALTER TABLE `deposito`
  ADD PRIMARY KEY (`id_deposito`),
  ADD KEY `sucursal_deposito_fk` (`id_sucursal`);

--
-- Indices de la tabla `detalle_ajuste_stock`
--
ALTER TABLE `detalle_ajuste_stock`
  ADD PRIMARY KEY (`id_ajuste_stock`,`id_producto`),
  ADD KEY `deposito_detalle_ajuste_stock_fk` (`id_deposito`),
  ADD KEY `productos_detalle_ajuste_stock_fk` (`id_producto`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id_gasto`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id_marcas`);

--
-- Indices de la tabla `precio_producto`
--
ALTER TABLE `precio_producto`
  ADD PRIMARY KEY (`id_producto`,`id_tipo_precio`),
  ADD KEY `tipo_precio_precio_producto_fk` (`id_tipo_precio`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `categoria_producto_fk` (`id_categoria`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `codigo_barra` (`codigo_barra`),
  ADD KEY `tipo_impuesto_productos_fk` (`id_tipo_impuesto`),
  ADD KEY `marcas_productos_fk` (`id_marcas`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor_`),
  ADD KEY `ciudades_proveedores_fk` (`id_ciudad`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `stock_deposito`
--
ALTER TABLE `stock_deposito`
  ADD PRIMARY KEY (`id_producto`,`id_deposito`),
  ADD KEY `deposito_stock_deposito_fk` (`id_deposito`);

--
-- Indices de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  ADD PRIMARY KEY (`id_sucursal`),
  ADD KEY `ciudades_sucursal_fk` (`id_ciudad`);

--
-- Indices de la tabla `tipo_cliente`
--
ALTER TABLE `tipo_cliente`
  ADD PRIMARY KEY (`id_tipo_cliente`);

--
-- Indices de la tabla `tipo_impuesto`
--
ALTER TABLE `tipo_impuesto`
  ADD PRIMARY KEY (`id_tipo_impuesto`);

--
-- Indices de la tabla `tipo_precio`
--
ALTER TABLE `tipo_precio`
  ADD PRIMARY KEY (`id_tipo_precio`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`cod_usuario`),
  ADD KEY `sucursal_usuarios_fk` (`id_sucursal`),
  ADD KEY `roles_usuarios_fk` (`id_rol`);

--
-- Indices de la tabla `usuarios_roles`
--
ALTER TABLE `usuarios_roles`
  ADD PRIMARY KEY (`cod_usuario`,`id_rol`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `clientes_ventas_fk` (`id_cliente`),
  ADD KEY `pedidos_clientes_ventas_fk` (`id_pedido_cliente`),
  ADD KEY `apertura_cierre_caja_ventas_fk` (`id_apertura_cierre_caja`);

--
-- Indices de la tabla `ventas_detalle`
--
ALTER TABLE `ventas_detalle`
  ADD PRIMARY KEY (`id_detalle_venta`),
  ADD KEY `productos_ventas_detalle_fk` (`id_producto`),
  ADD KEY `idx_ventas_detalle_venta` (`id_venta`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ajuste_stock`
--
ALTER TABLE `ajuste_stock`
  MODIFY `id_ajuste_stock` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `apertura_cierre_caja`
--
ALTER TABLE `apertura_cierre_caja`
  MODIFY `id_apertura_cierre_caja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categorias` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  MODIFY `id_ciudad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `cobros`
--
ALTER TABLE `cobros`
  MODIFY `id_cobro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `compra`
--
ALTER TABLE `compra`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `cuentas__cobrar`
--
ALTER TABLE `cuentas__cobrar`
  MODIFY `id_cuenta_cobrar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT de la tabla `cuotas`
--
ALTER TABLE `cuotas`
  MODIFY `id_cuota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `deposito`
--
ALTER TABLE `deposito`
  MODIFY `id_deposito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id_marcas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1233;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor_` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  MODIFY `id_sucursal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipo_cliente`
--
ALTER TABLE `tipo_cliente`
  MODIFY `id_tipo_cliente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_precio`
--
ALTER TABLE `tipo_precio`
  MODIFY `id_tipo_precio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `cod_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT de la tabla `ventas_detalle`
--
ALTER TABLE `ventas_detalle`
  MODIFY `id_detalle_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `precio_producto`
--
ALTER TABLE `precio_producto`
  ADD CONSTRAINT `producto_precio_producto_fk` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `tipo_precio_precio_producto_fk` FOREIGN KEY (`id_tipo_precio`) REFERENCES `tipo_precio` (`id_tipo_precio`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `categoria_producto_fk` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `stock_deposito`
--
ALTER TABLE `stock_deposito`
  ADD CONSTRAINT `deposito_stock_deposito_fk` FOREIGN KEY (`id_deposito`) REFERENCES `deposito` (`id_deposito`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `producto_stock_deposito_fk` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
