-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-10-2026 a las 19:46:08
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `contable`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajas`
--

CREATE TABLE `cajas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cajas`
--

INSERT INTO `cajas` (`id`, `nombre`, `usuario_id`, `descripcion`, `activa`) VALUES
(2, 'BANCO SANTA FE - 046', NULL, 'CUENTA CORRIENTE', 1),
(3, 'EFECTIVO', NULL, 'DINERO EN EFECTIVO', 1),
(5, 'CAJA ARQUITECTURA', 7, 'GASTOS DE ARQUITECTURA', 1),
(6, 'OM RAFAELA SANTA', 7, 'CAJA DE SANTA 1111', 1),
(10, 'SALDOS A  CANCELAR', NULL, 'SE CARGAN GASTOS NO PAGADOS AUN', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'MECANICA'),
(2, 'SUELDOS'),
(3, 'CORRALON'),
(4, 'SERVICES');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centros_costos`
--

CREATE TABLE `centros_costos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `centros_costos`
--

INSERT INTO `centros_costos` (`id`, `nombre`) VALUES
(2, 'OBRA MODULAR RAFAELA'),
(3, 'ARQUITECTURA'),
(4, 'ALQUILER MAQUINARIA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cheques`
--

CREATE TABLE `cheques` (
  `id` int(11) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_pago` date NOT NULL,
  `nro_cheque` varchar(50) NOT NULL,
  `importe` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tipo` enum('TERCERO','PROPIO','ECHEQ_TERCERO','ECHEQ_PROPIO') NOT NULL,
  `estado` enum('RECIBIDO','EMITIDO','ENDOSADO','COBRADO','PAGADO') NOT NULL DEFAULT 'RECIBIDO',
  `beneficiario` varchar(255) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cheques`
--

INSERT INTO `cheques` (`id`, `fecha_emision`, `fecha_pago`, `nro_cheque`, `importe`, `tipo`, `estado`, `beneficiario`, `observaciones`, `archivo`, `usuario_id`, `created_at`) VALUES
(8, '2026-09-01', '2026-09-17', '15624654', 1000.00, 'TERCERO', 'RECIBIDO', 'RECURSOS GLOBALES', '', NULL, 3, '2026-09-06 13:31:03'),
(9, '2026-09-01', '2026-09-23', '156111', 1000.00, 'PROPIO', 'EMITIDO', 'ELECTROVOLT SRL', '', NULL, 3, '2026-09-06 13:32:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `condicion_fiscal` varchar(50) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `cp` varchar(10) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `usuario_id`, `cuit`, `condicion_fiscal`, `direccion`, `localidad`, `provincia`, `cp`, `whatsapp`, `telefono`, `contacto`, `observaciones`) VALUES
(4, 'TEST', 2, '2035120716', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'JUAN PEREZ', 2, '20-35120717-6', 'RESPONSABLE INSCRIPTO', 'PASAJE LASSAGA 4850', 'SANTA FE ', 'SANTA FE', '3000', '3424357046', '3424332457', 'JUAN PEREZ OK', 'ESTE ES UN CLIENTE QUE NOS PIDE GENERALMENTE BOLUDECES'),
(6, 'SOFIA PEIRANO', 2, '20-35120717-6', 'RESPONSABLE INSCRIPTO', 'PASAJE LASSAGA 4850', 'SANTA FE', 'SANTA FE', '3000', '3424355553', '1345S65', '', ''),
(7, 'AGUAS SANTAFESINAS SOCIEDAD ANONIMA', 2, '30-70951414-4', 'RESPONSABLE INSCRIPTO', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_venta`
--

CREATE TABLE `facturas_venta` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_comprobante_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `obra_id` int(11) DEFAULT NULL,
  `punto_venta` int(11) NOT NULL,
  `nro_factura` int(11) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `neto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `centro_costo_id` int(11) NOT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'DEBE',
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas_venta`
--

INSERT INTO `facturas_venta` (`id`, `fecha`, `tipo_comprobante_id`, `cliente_id`, `obra_id`, `punto_venta`, `nro_factura`, `fecha_vencimiento`, `detalle`, `neto`, `iva`, `total`, `observaciones`, `centro_costo_id`, `archivo`, `estado`, `usuario_id`) VALUES
(9, '2026-09-03', 1, 7, 9, 2, 483, '2026-10-26', 'MANT. ACUED. DESVÍO ARIJÓN; PROG.1000 VA SA PEREIRA CERTIFICADO N°9-SEGÚN OC 4500062956', 1819098.28, 382010.64, 2201108.92, '', 2, 'FAC_VTA_1788452011_1102.pdf', 'DEBE', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `id` int(11) NOT NULL,
  `centro_costo_id` int(11) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `subcategoria_id` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `tipo_comprobante_id` int(50) DEFAULT NULL,
  `numero_comprobante` varchar(50) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `medio_pago_id` int(50) DEFAULT NULL,
  `caja_id` int(11) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `neto` decimal(15,2) DEFAULT NULL,
  `iva` decimal(15,2) DEFAULT NULL,
  `ret_iibb` decimal(15,2) DEFAULT NULL,
  `otros_tributos` decimal(15,2) DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `obra_id` int(11) DEFAULT NULL,
  `vehiculo_id` int(11) DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `estado_validacion` enum('APROBADO','PENDIENTE') NOT NULL DEFAULT 'APROBADO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`id`, `centro_costo_id`, `categoria_id`, `subcategoria_id`, `fecha`, `tipo_comprobante_id`, `numero_comprobante`, `proveedor_id`, `medio_pago_id`, `caja_id`, `detalle`, `neto`, `iva`, `ret_iibb`, `otros_tributos`, `total`, `usuario_id`, `obra_id`, `vehiculo_id`, `archivo`, `estado_validacion`) VALUES
(108, 2, 1, 1, '2026-09-03', 1, '001-000256', 2, 1, 2, 'COMPRA DE COMBUSTIBLE', 210000.00, 44100.00, 0.00, 0.00, 254100.00, 3, 9, NULL, NULL, 'APROBADO'),
(109, 3, 1, 1, '2026-09-05', 1, '10100', 2, 1, 2, 'TEST', 100000.00, 21000.00, 0.00, 0.00, 121000.00, 3, 9, 2, '1788643096_2548.jpg', 'APROBADO'),
(110, 4, 1, 1, '2026-09-07', 1, '111', 6, 1, 2, 'JJHJH', 1000.00, 210.00, 0.00, 0.00, 1210.00, 3, 9, 2, '1788798839_5385.pdf', 'APROBADO'),
(112, 3, 3, 7, '2026-09-15', 1, '0001-1012000', 6, 2, 5, 'DSADAS', 10000.00, 2100.00, 0.00, 0.00, 12100.00, 3, 9, NULL, NULL, 'APROBADO'),
(113, 3, 3, 7, '2026-09-11', 1, '12151', 2, 5, 2, 'DSADASDSA', 1000000.00, 210000.00, 0.00, 0.00, 1210000.00, 3, 9, NULL, NULL, 'APROBADO'),
(115, 4, 3, 7, '2026-09-14', 1, '12121', 6, 1, 5, 'SDAD', 100000.00, 0.00, 0.00, 0.00, 100000.00, 7, 9, 1, NULL, 'APROBADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medios_pago`
--

CREATE TABLE `medios_pago` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `medios_pago`
--

INSERT INTO `medios_pago` (`id`, `nombre`, `usuario_id`) VALUES
(1, 'BANCO SANTA FE CUENTA ', NULL),
(2, 'BANCO SANTANDER', NULL),
(3, 'TARJETA SANTANDER', NULL),
(4, 'EFECTIVO', NULL),
(5, 'TRANSFERENCIA BANCARIA', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_caja`
--

CREATE TABLE `movimientos_caja` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `caja_id` int(11) NOT NULL,
  `tipo` enum('INGRESO','EGRESO','TRANSFERENCIA') DEFAULT NULL,
  `concepto` varchar(255) NOT NULL,
  `comprobante` varchar(100) DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `importe` decimal(12,2) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `origen` varchar(30) DEFAULT 'MANUAL',
  `referencia_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_caja`
--

INSERT INTO `movimientos_caja` (`id`, `fecha`, `caja_id`, `tipo`, `concepto`, `comprobante`, `archivo`, `importe`, `observaciones`, `usuario_id`, `created_at`, `updated_at`, `origen`, `referencia_id`) VALUES
(78, '2026-09-03', 2, 'INGRESO', 'DEPOSITO INICIAL', '2545', NULL, 1000000.00, 'ORIGEN SANTANDER YAIR', 3, '2026-09-03 16:10:22', NULL, 'MANUAL', NULL),
(79, '2026-09-03', 3, 'INGRESO', 'DEPOSITO INICIAL', '0125', NULL, 1000000.00, 'YAIR', 3, '2026-09-03 16:10:48', NULL, 'MANUAL', NULL),
(80, '2026-09-03', 2, 'EGRESO', 'GASTO #108', '001-000256', NULL, 254100.00, NULL, 3, '2026-09-03 16:15:23', NULL, 'GASTO', 108),
(81, '2026-09-05', 2, 'EGRESO', 'GASTO #109', '10100', NULL, 121000.00, NULL, 3, '2026-09-05 21:05:58', NULL, 'GASTO', 109),
(82, '2026-09-07', 2, 'EGRESO', 'GASTO #110', '111', NULL, 1210.00, NULL, 3, '2026-09-07 16:33:59', NULL, 'GASTO', 110),
(84, '2026-09-15', 5, 'EGRESO', 'GASTO #112', '0001-1012000', NULL, 12100.00, NULL, 3, '2026-09-08 19:07:26', NULL, 'GASTO', 112),
(85, '2026-09-11', 2, 'EGRESO', 'GASTO #113', '12151', NULL, 1210000.00, NULL, 3, '2026-09-11 16:55:46', NULL, 'GASTO', 113),
(87, '2026-09-14', 5, 'EGRESO', 'GASTO #115', '12121', NULL, 100000.00, NULL, 7, '2026-09-14 15:34:57', NULL, 'GASTO', 115);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `obras`
--

CREATE TABLE `obras` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `responsable` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `nro_oc` varchar(50) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `tipo_obra` enum('PUBLICA','PRIVADA','PARTICULAR') NOT NULL DEFAULT 'PRIVADA',
  `detalle` text DEFAULT NULL,
  `presupuesto_archivo` varchar(255) DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'ACTIVA',
  `facturacion` enum('Por Cobrar','Pagadas') DEFAULT 'Por Cobrar',
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `obras`
--

INSERT INTO `obras` (`id`, `nombre`, `cliente_id`, `responsable`, `direccion`, `nro_oc`, `fecha_inicio`, `fecha_fin`, `tipo_obra`, `detalle`, `presupuesto_archivo`, `estado`, `facturacion`, `usuario_id`) VALUES
(9, 'MODULAR', 7, 'VAULET NAHUEL', 'ITUIZANGO 1501', 'OC-4500004565', '2026-08-03', '2026-08-28', 'PUBLICA', 'HOLA MUNDO', '9/presupuestos/presupuesto_6a70a1aa4aa3d.pdf', 'ACTIVA', 'Por Cobrar', 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `obra_archivos`
--

CREATE TABLE `obra_archivos` (
  `id` int(11) NOT NULL,
  `obra_id` int(11) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `obra_archivos`
--

INSERT INTO `obra_archivos` (`id`, `obra_id`, `archivo`, `nombre_original`, `fecha_subida`) VALUES
(16, 9, '9/repositorio/doc_6a70a1aa4c3a1_0.pdf', 'boletaPago.pdf', '2026-08-03 14:11:54'),
(19, 9, '9/repositorio/doc_6a7e1ca5bd847_0.pdf', 'ACTA-Ministerio.pdf', '2026-08-13 19:36:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `id` int(11) NOT NULL,
  `clasificacion` enum('ADMINISTRATIVO','OPERATIVO','MAQUINISTA','OBRAS / CAMPO') NOT NULL DEFAULT 'OPERATIVO',
  `cuil` varchar(20) DEFAULT NULL,
  `apellido` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `puesto` varchar(100) DEFAULT NULL,
  `tipo_licencia` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `fecha_baja` date DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `domicilio` varchar(255) DEFAULT NULL,
  `contacto_emergencia` varchar(255) DEFAULT NULL,
  `estado` enum('ACTIVO','LICENCIA','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `situacion_laboral` enum('REGISTRADO','NO REGISTRADO','MONOTRIBUTO','REG. OTRO CUIT','OTROS') DEFAULT 'REGISTRADO',
  `procedencia` varchar(150) DEFAULT NULL,
  `fecha_alta_ieric` date DEFAULT NULL,
  `fecha_alta_arca` date DEFAULT NULL,
  `convenio_aplicable` varchar(150) DEFAULT NULL,
  `puesto_arca` varchar(150) DEFAULT NULL,
  `seguro_acc` varchar(150) DEFAULT NULL,
  `seguro_acc_desc` varchar(255) DEFAULT NULL,
  `svo` varchar(150) DEFAULT NULL,
  `cond_pago` varchar(100) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `cbu` varchar(50) DEFAULT NULL,
  `cta_cese` varchar(50) DEFAULT NULL,
  `calzado_talle` varchar(10) DEFAULT NULL,
  `pantalon_talle` varchar(10) DEFAULT NULL,
  `camisa_talle` varchar(10) DEFAULT NULL,
  `vencimiento_preocupacional` date DEFAULT NULL,
  `vencimiento_carnet_conducir` date DEFAULT NULL,
  `vencimiento_art` date DEFAULT NULL,
  `obra_social` varchar(100) DEFAULT NULL,
  `art_compañia` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `productores_seguro` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id`, `clasificacion`, `cuil`, `apellido`, `nombre`, `puesto`, `tipo_licencia`, `fecha_nacimiento`, `fecha_ingreso`, `fecha_baja`, `telefono`, `email`, `domicilio`, `contacto_emergencia`, `estado`, `situacion_laboral`, `procedencia`, `fecha_alta_ieric`, `fecha_alta_arca`, `convenio_aplicable`, `puesto_arca`, `seguro_acc`, `seguro_acc_desc`, `svo`, `cond_pago`, `banco`, `cbu`, `cta_cese`, `calzado_talle`, `pantalon_talle`, `camisa_talle`, `vencimiento_preocupacional`, `vencimiento_carnet_conducir`, `vencimiento_art`, `obra_social`, `art_compañia`, `observaciones`, `productores_seguro`, `usuario_id`) VALUES
(1, 'OPERATIVO', '20351207176', 'CORZO', 'YAIR GONZALO', 'PROGRAMADOR', '', '1990-02-20', '2015-06-01', NULL, '3424357046', 'yairgc@outlook.com', 'PASAJE LASSAGA 4850', 'RUBEN/3424357046', 'INACTIVO', 'REGISTRADO', '', NULL, NULL, '', '', '', '', '', '', '', '', '', '44', '46', 'XL', '2026-09-06', '2026-09-25', '2026-09-18', 'OSPAC', 'ANDINA', '', '', 3),
(2, 'ADMINISTRATIVO', '20311111116', 'FERRERO', 'ABI', 'OFICINA LASSAGA', NULL, '1998-09-30', '2020-10-10', NULL, '3424357046', 'abiferrero@gmail.com', 'LASSAGA 3030', '342546565/ROSA', 'INACTIVO', 'REGISTRADO', 'AMIGA SOFIA', '2025-10-10', '2025-10-20', 'UEACARA', 'AYUDANTE ', 'TEST', 'TEST', 'TEST', 'MENSAUL', 'GALICIA', '20252126565656565', '1516565562', '44', '44', '44', '2026-09-23', '2026-09-18', '2026-09-04', 'ROSA', 'TEST', 'TEST', 'DDDESTE', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_archivos`
--

CREATE TABLE `personal_archivos` (
  `id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `tipo_adjunto` enum('DOCUMENTO','FOTO','VARIOS') NOT NULL DEFAULT 'DOCUMENTO',
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal_archivos`
--

INSERT INTO `personal_archivos` (`id`, `personal_id`, `archivo`, `nombre_original`, `tipo_adjunto`, `fecha_subida`) VALUES
(1, 1, '1/adj_6a9d7c295843b.jpg', 'trailer.jpg', 'DOCUMENTO', '2026-09-06 14:43:53'),
(2, 1, '1/adj_6a9d7c304f7d5.jpg', '569124418_4235384476703121_7582342535131890105_n.jpg', 'FOTO', '2026-09-06 14:44:00'),
(3, 2, '2/adj_6a9d8d8631fbd.jpg', 'trailer.jpg', 'DOCUMENTO', '2026-09-06 15:57:58'),
(4, 2, '2/adj_6a9d8d8b4f505.jpg', 'trailer.jpg', 'FOTO', '2026-09-06 15:58:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_movimientos`
--

CREATE TABLE `personal_movimientos` (
  `id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_evento` varchar(100) NOT NULL,
  `detalle` text NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal_movimientos`
--

INSERT INTO `personal_movimientos` (`id`, `personal_id`, `fecha`, `tipo_evento`, `detalle`, `usuario_id`, `fecha_registro`) VALUES
(1, 1, '2026-09-06', 'ENTREGA DE ROPA / EPP', 'SE ENTREGA DOS CAMISAS', 3, '2026-09-06 15:16:54'),
(2, 2, '2026-09-07', 'OTRO', 'BAJA ', 3, '2026-09-07 16:27:17'),
(3, 1, '2026-09-07', 'APERCIBIMIENTO / LLAMADO ATENCIÓN', 'LLEGO TARDE AL TRABAJO', 3, '2026-09-07 19:34:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestos`
--

CREATE TABLE `presupuestos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `fecha` date NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `obra_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `estado` enum('Borrador','Enviado','Aprobado','Rechazado','Archivado') DEFAULT 'Borrador',
  `total_neto` decimal(14,2) DEFAULT 0.00,
  `coeficiente_k_general` decimal(6,4) DEFAULT 1.0000,
  `total_presupuestado` decimal(14,2) DEFAULT 0.00,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuestos`
--

INSERT INTO `presupuestos` (`id`, `codigo`, `fecha`, `cliente_id`, `obra_id`, `titulo`, `estado`, `total_neto`, `coeficiente_k_general`, `total_presupuestado`, `usuario_id`, `created_at`, `updated_at`) VALUES
(2, 'PRE-2026-002', '2026-10-04', 7, NULL, 'PRUEBA TEST', 'Borrador', 2123200.00, 1.0000, 2123200.00, 3, '2026-10-03 23:17:36', '2026-10-03 23:17:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_coeficientes_plantillas`
--

CREATE TABLE `presupuesto_coeficientes_plantillas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `mat_costos_indirectos` decimal(5,2) DEFAULT 10.73,
  `mat_beneficio` decimal(5,2) DEFAULT 20.00,
  `mat_costos_financieros` decimal(5,2) DEFAULT 2.00,
  `mat_iibb` decimal(5,2) DEFAULT 2.00,
  `mat_otros_impuestos` decimal(5,2) DEFAULT 35.00,
  `mat_iva` decimal(5,2) DEFAULT 21.00,
  `mat_k_resultante` decimal(6,4) DEFAULT 1.7100,
  `mo_costos_indirectos` decimal(5,2) DEFAULT 15.00,
  `mo_beneficio` decimal(5,2) DEFAULT 25.00,
  `mo_costos_financieros` decimal(5,2) DEFAULT 2.00,
  `mo_iibb` decimal(5,2) DEFAULT 2.00,
  `mo_otros_impuestos` decimal(5,2) DEFAULT 35.00,
  `mo_iva` decimal(5,2) DEFAULT 21.00,
  `mo_k_resultante` decimal(6,4) DEFAULT 1.8500,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_coeficientes_plantillas`
--

INSERT INTO `presupuesto_coeficientes_plantillas` (`id`, `nombre`, `descripcion`, `mat_costos_indirectos`, `mat_beneficio`, `mat_costos_financieros`, `mat_iibb`, `mat_otros_impuestos`, `mat_iva`, `mat_k_resultante`, `mo_costos_indirectos`, `mo_beneficio`, `mo_costos_financieros`, `mo_iibb`, `mo_otros_impuestos`, `mo_iva`, `mo_k_resultante`, `activo`, `created_at`) VALUES
(1, 'OBRA PRIVADA', 'Perfil base según cálculo técnico de resumen', 10.73, 20.00, 2.00, 2.00, 1.00, 21.00, 1.6482, 15.00, 25.00, 2.00, 2.00, 35.00, 21.00, 1.8683, 1, '2026-09-12 22:54:53'),
(2, 'OBRA ESPERANZA', 'LOS VALORES DE LOS MATERIALES CUESTAN UN 10% ARRIBA', 0.00, 0.00, 0.00, 0.00, 0.00, 10.00, 1.1000, 15.00, 25.00, 2.00, 2.00, 35.00, 21.00, 1.8683, 1, '2026-09-13 02:35:24'),
(3, 'OBRAS ASSA', '', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 1.0000, 15.00, 35.00, 2.00, 2.00, 35.00, 21.00, 2.0366, 1, '2026-09-17 20:15:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_condiciones_plantilla`
--

CREATE TABLE `presupuesto_condiciones_plantilla` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `orden` int(11) DEFAULT 1,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_condiciones_plantilla`
--

INSERT INTO `presupuesto_condiciones_plantilla` (`id`, `titulo`, `contenido`, `orden`, `activo`) VALUES
(1, 'Validez de la oferta', 'El presente presupuesto tiene una validez de 15 días corridos a partir de la fecha de emisión.', 1, 1),
(2, 'Forma de Pago', '50% de anticipo al firmar la orden de trabajo y el saldo restante contra avance de obra según certificación.', 2, 1),
(3, 'Plazo de Ejecución', 'El tiempo estimado de entrega se computará a partir del cobro del anticipo y la disponibilidad efectiva del sitio de obra.', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_detalles`
--

CREATE TABLE `presupuesto_detalles` (
  `id` int(11) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `item_orden` int(11) NOT NULL DEFAULT 1,
  `descripcion` text NOT NULL,
  `unidad` varchar(30) DEFAULT 'GL',
  `cantidad` decimal(12,2) NOT NULL DEFAULT 1.00,
  `costo_unitario` decimal(15,2) NOT NULL DEFAULT 0.00,
  `costo_subtotal` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_equipos`
--

CREATE TABLE `presupuesto_equipos` (
  `id` int(11) NOT NULL,
  `categoria` enum('MAQUINAS Y HERRAMIENTAS','EQUIPOS','EQUIPAMIENTO DE OBRA') NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `unidad_medida` varchar(50) NOT NULL,
  `precio` decimal(12,2) NOT NULL DEFAULT 0.00,
  `proveedor` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_actualizacion` date NOT NULL,
  `usuario_nombre` varchar(100) DEFAULT 'Sistema',
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_equipos`
--

INSERT INTO `presupuesto_equipos` (`id`, `categoria`, `nombre`, `unidad_medida`, `precio`, `proveedor`, `observaciones`, `fecha_actualizacion`, `usuario_nombre`, `activo`) VALUES
(1, 'MAQUINAS Y HERRAMIENTAS', 'ANDAMIO MEDIO CUERPO (ALTURA APROX. 90 CM)', 'DIA', 2000.00, 'CORZO YAIR GONZALO', 'XXXX', '2026-09-17', 'SOFIA', 1),
(2, 'MAQUINAS Y HERRAMIENTAS', 'ANDAMIO TUBULAR (TIPO SORRENTO) CUERPO COMPLETO ESTÁNDAR', 'DIA', 1250.00, 'CORZO YAIR GONZAO', NULL, '2026-09-16', 'SOFIA', 1),
(3, 'EQUIPOS', 'MINICARGADORA C/BALDE', 'HR', 55000.00, 'CORZO YAIR GONZAO', 'SE COBRAN 2HS FLETE - VOL. 0.25M3', '2026-09-18', 'SOFIA', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_indirectos`
--

CREATE TABLE `presupuesto_indirectos` (
  `id` int(11) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `plantilla_id` int(11) DEFAULT NULL,
  `categoria_codigo` varchar(10) NOT NULL,
  `item_codigo` varchar(10) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `cantidad` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `unidad` varchar(20) DEFAULT 'un',
  `afectacion` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `unidad_frecuencia` varchar(20) DEFAULT 'mes',
  `unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `importe` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_indirectos_categorias`
--

CREATE TABLE `presupuesto_indirectos_categorias` (
  `id` int(11) NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_indirectos_categorias`
--

INSERT INTO `presupuesto_indirectos_categorias` (`id`, `codigo`, `nombre`, `orden`) VALUES
(1, 'A', 'PERSONAL ESPECIALIZADO - GREMIO', 1),
(2, 'B', 'HIGIENE Y SEGURIDAD DE OBRA', 2),
(3, 'C', 'DEL PERSONAL', 3),
(4, 'D', 'INSTALACIONES EN OBRADOR', 4),
(5, 'E', 'MOVILIDAD - FLETES', 5),
(6, 'F', 'COSTO EMPRESA PATRIMONIO', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_indirectos_config`
--

CREATE TABLE `presupuesto_indirectos_config` (
  `id` int(11) NOT NULL,
  `clave` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_indirectos_config`
--

INSERT INTO `presupuesto_indirectos_config` (`id`, `clave`, `valor`) VALUES
(1, 'cant_operarios', '2'),
(2, 'dias_obra', '5');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_indirectos_plantilla`
--

CREATE TABLE `presupuesto_indirectos_plantilla` (
  `id` int(11) NOT NULL,
  `categoria_codigo` varchar(10) NOT NULL,
  `categoria_nombre` varchar(150) NOT NULL,
  `item_codigo` varchar(10) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `cantidad_defecto` decimal(10,4) DEFAULT 1.0000,
  `unidad` varchar(20) DEFAULT 'un',
  `depende_operarios` tinyint(1) DEFAULT 0,
  `depende_tiempo` tinyint(1) NOT NULL DEFAULT 1,
  `afectacion_defecto` decimal(10,4) DEFAULT 1.0000,
  `unitario_defecto` decimal(12,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_indirectos_plantilla`
--

INSERT INTO `presupuesto_indirectos_plantilla` (`id`, `categoria_codigo`, `categoria_nombre`, `item_codigo`, `descripcion`, `observacion`, `cantidad_defecto`, `unidad`, `depende_operarios`, `depende_tiempo`, `afectacion_defecto`, `unitario_defecto`, `activo`, `updated_at`) VALUES
(1, 'A', 'PERSONAL ESPECIALIZADO - GREMIO', '1', 'Administración (Honorarios CPN)', '', 1.0000, 'GL', 0, 0, 0.2500, 10000.00, 1, '2026-09-27 18:31:58'),
(2, 'A', 'PERSONAL ESPECIALIZADO - GREMIO', '2', 'Administración INTERNA DG OBRAS', 'afectación 25%', 1.0000, 'GL', 0, 0, 0.2500, 1200000.00, 1, '2026-09-27 18:32:00'),
(3, 'A', 'PERSONAL ESPECIALIZADO - GREMIO', '3', 'Beneficio/Sueldo Capataz / Puntero', '', 1.0000, 'GL', 0, 0, 0.5000, 850000.00, 1, '2026-09-23 12:59:28'),
(4, 'A', 'PERSONAL ESPECIALIZADO - GREMIO', '4', 'Sueldo Bruto Jefe de Obra', 'Ing. Civil Jr - 1/2 jornal', 1.0000, 'GL', 0, 1, 0.5000, 900000.00, 1, '2026-09-21 23:52:05'),
(5, 'A', 'PERSONAL ESPECIALIZADO - GREMIO', '5', 'Sueldo Personal logística', 'Considera 5 hs/sem', 5.0000, 'GL', 0, 1, 0.0000, 5500.00, 1, '2026-09-21 23:52:05'),
(6, 'B', 'HIGIENE Y SEGURIDAD DE OBRA', '1', 'Programa de Seguridad', '', 1.0000, 'GL', 0, 1, 1.0000, 300000.00, 1, '2026-09-23 12:59:40'),
(7, 'B', 'HIGIENE Y SEGURIDAD DE OBRA', '2', 'Seguimiento semanal', '', 1.0000, 'GL', 0, 1, 0.0000, 85000.00, 1, '2026-09-23 12:59:41'),
(8, 'B', 'HIGIENE Y SEGURIDAD DE OBRA', '3', 'Técnico S&E permanente en obra', 'No se considera', 160.0000, 'GL', 0, 1, 0.5000, 15500.00, 1, '2026-09-23 12:59:42'),
(9, 'C', 'DEL PERSONAL', '1', 'Vianda diaria puesta en obra', '', 2.0000, 'GL', 0, 1, 0.0000, 7500.00, 0, '2026-09-23 12:59:49'),
(10, 'C', 'DEL PERSONAL', '2', 'Agua para consumo dispenser', '2 bidones por semana', 2.0000, 'GL', 0, 1, 0.0000, 55000.00, 0, '2026-09-23 12:59:51'),
(11, 'C', 'DEL PERSONAL', '4', 'Ticket Canasta mensuales', '', 2.0000, 'GL', 0, 1, 0.5000, 25000.00, 1, '2026-09-21 23:52:05'),
(12, 'C', 'DEL PERSONAL', '5', 'Hospedaje de Personal', 'Depto p/ 4 personas', 1.0000, 'GL', 0, 1, 0.0000, 65000.00, 1, '2026-09-21 23:52:05'),
(13, 'D', 'INSTALACIONES EN OBRADOR', '8', 'Casilla Oficina c/ aire', '', 1.0000, 'GL', 0, 1, 0.5000, 165000.00, 1, '2026-09-21 23:52:05'),
(14, 'D', 'INSTALACIONES EN OBRADOR', '8.1', 'Flete oficina x viaje', '', 1.0000, 'GL', 0, 1, 1.0000, 75000.00, 1, '2026-09-21 23:52:05'),
(15, 'E', 'MOVILIDAD - FLETES', '1', 'Movilidad jefe de obra', '30 km/día - 22 días/mes', 720.0000, 'GL', 0, 1, 0.5000, 350.00, 1, '2026-09-21 23:52:05'),
(16, 'E', 'MOVILIDAD - FLETES', '2', 'Movilidad abastecimiento de obra', '10 km/día - 10 días/mes', 200.0000, 'GL', 0, 1, 0.5000, 250.00, 1, '2026-09-21 23:52:05'),
(17, 'E', 'MOVILIDAD - FLETES', '3', 'Fletes varios / traslados equipos', '', 4.0000, 'GL', 0, 1, 0.5000, 25000.00, 1, '2026-09-21 23:52:05'),
(18, 'F', 'COSTO EMPRESA PATRIMONIO', '1.1', 'Alquiler oficina', 'Prorrateado 30%', 0.3000, 'GL', 0, 1, 0.5000, 250000.00, 1, '2026-09-21 23:52:05'),
(19, 'F', 'COSTO EMPRESA PATRIMONIO', '1.2', 'Energía eléctrica', 'Prorrateado 30%', 0.3000, 'GL', 0, 1, 0.5000, 55000.00, 1, '2026-09-21 23:52:05'),
(20, 'F', 'COSTO EMPRESA PATRIMONIO', '1.3', 'Internet', 'Prorrateado 30%', 0.3000, 'GL', 0, 1, 0.5000, 45000.00, 1, '2026-09-21 23:52:05'),
(22, 'A', 'PERSONAL ESPECIALIZADO - GREMIO', '1.2', 'ROPA', 'ZAPATOS OBRA', 1.0000, 'UN', 1, 1, 1.0000, 100000.00, 1, '2026-09-22 00:06:57'),
(23, 'A', 'PERSONAL ESPECIALIZADO - GREMIO', '1.2', 'TEST DIAS OBRA', 'TEST', 1.0000, 'GL', 1, 1, 1.0000, 10000.00, 1, '2026-09-22 23:46:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_materiales`
--

CREATE TABLE `presupuesto_materiales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `peso_cantidad` varchar(50) DEFAULT NULL,
  `precio_bulto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unidad_medida` varchar(20) NOT NULL DEFAULT 'u',
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `proveedor` varchar(100) DEFAULT NULL,
  `fecha_actualizacion` date DEFAULT NULL,
  `usuario_nombre` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_el` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_materiales`
--

INSERT INTO `presupuesto_materiales` (`id`, `nombre`, `peso_cantidad`, `precio_bulto`, `unidad_medida`, `precio_unitario`, `proveedor`, `fecha_actualizacion`, `usuario_nombre`, `activo`, `creado_el`) VALUES
(1, 'TEST (10) / u (10) / u', NULL, 500.00, 'u', 50.00, 'GERMAT SRL', '2026-09-14', 'SOFIA', 0, '2026-09-13 02:14:27'),
(2, 'TEST (10) / u (10) / u', '25', 500.00, 'KG', 50.00, 'GILCOMAT', '2026-09-14', 'SOFIA', 0, '2026-09-14 03:00:53'),
(3, 'TEST (10) / u (10) / u', '25', 500.00, 'm³', 50.00, 'CORZO YAIR GONZALO', '2026-09-14', 'SOFIA', 0, '2026-09-14 03:18:48'),
(4, 'HOLA', 'TE', 500.00, 'TE', 50.00, '-', '2026-09-14', 'SOFIA', 0, '2026-09-14 03:20:00'),
(5, 'TEST (10) / u (10) / u', 'TE', 10.00, 'TE', 1.00, 'TE', '2026-09-14', 'SOFIA', 0, '2026-09-14 03:20:21'),
(6, 'TEST (10) / u (10) / u', '10', 100.00, 'u', 10.00, 'CORZO YAIR GONZAO', '2026-09-14', 'SOFIA', 0, '2026-09-14 14:56:26'),
(7, 'TEST (10) / u (10) / u', '10', 10.00, 'kg', 1.00, 'CORZO YAIR GONZALO', '2026-09-14', 'SOFIA', 0, '2026-09-14 15:00:46'),
(8, 'TEST (10) / u (10) / u', '2', 10.00, 'u', 1.00, 'CORZO YAIR GONZAO', '2026-09-14', 'SOFIA', 0, '2026-09-14 15:39:11'),
(9, 'CEMENTO', '25', 9000.00, 'kg', 360.00, 'CORZO YAIR GONZALO', '2026-09-14', 'SOFIA', 1, '2026-09-14 17:54:36'),
(10, 'CAL HIDRATADA', '25', 8210.00, 'kg', 328.40, 'CORZO YAIR GONZALO', '2026-09-14', 'SOFIA', 1, '2026-09-14 17:54:57'),
(11, 'TES', 'TES', 1000.00, 'KG', 1000.00, '', '2026-09-19', 'SOFIA', 1, '2026-09-16 16:41:04'),
(12, 'PEGAMENTO CERAMICA', '30', 12000.00, 'KG', 400.00, 'CORZO YAIR GONZALO', '2026-09-18', 'SOFIA', 1, '2026-09-18 18:00:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_mo_adjuntos`
--

CREATE TABLE `presupuesto_mo_adjuntos` (
  `id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `fecha_subida` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_mo_adjuntos`
--

INSERT INTO `presupuesto_mo_adjuntos` (`id`, `nombre_archivo`, `ruta_archivo`, `fecha_subida`) VALUES
(1, 'uocra-acuerdo.pdf', '../../uploads/presupuestos/acuerdo_uocra_1789647747.pdf', '2026-09-17 09:22:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_mo_auditoria`
--

CREATE TABLE `presupuesto_mo_auditoria` (
  `id` int(11) NOT NULL,
  `fecha_actualizacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_mo_auditoria`
--

INSERT INTO `presupuesto_mo_auditoria` (`id`, `fecha_actualizacion`) VALUES
(1, '2026-09-18 15:07:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_mo_categorias`
--

CREATE TABLE `presupuesto_mo_categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `valor_hora_basico` decimal(12,2) NOT NULL DEFAULT 0.00,
  `suma_no_remunerativa` decimal(12,2) NOT NULL DEFAULT 0.00,
  `horas_mes` int(11) NOT NULL DEFAULT 160,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_mo_categorias`
--

INSERT INTO `presupuesto_mo_categorias` (`id`, `nombre`, `valor_hora_basico`, `suma_no_remunerativa`, `horas_mes`, `activo`) VALUES
(1, 'OFICIAL ESPECIALIZADO', 8420.00, 0.00, 160, 1),
(2, 'OFICIAL', 6348.00, 0.00, 160, 1),
(3, 'MEDIO OFICIAL', 5850.00, 0.00, 160, 1),
(4, 'AYUDANTE', 5399.00, 0.00, 160, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_mo_configuracion`
--

CREATE TABLE `presupuesto_mo_configuracion` (
  `clave` varchar(50) NOT NULL,
  `valor` decimal(10,4) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_mo_configuracion`
--

INSERT INTO `presupuesto_mo_configuracion` (`clave`, `valor`, `descripcion`) VALUES
('aporte_no_remunerativo', 0.0700, 'Aporte s/ Suma No Remunerativa (%)'),
('art', 0.0700, 'A.R.T. (7%)'),
('capacitacion', 0.0100, 'Fondo Investigación, Cap. y Seg. (1%)'),
('cuss', 0.1700, 'Total C.U.S.S. (17%)'),
('dias_quincena', 11.0000, 'Días hábiles calculados por quincena'),
('extra_gremio', 0.0000, 'Extra / Ayuda Gremio / Otros (%)'),
('factor_bolsillo', 0.4350, 'Factor de cálculo sueldo de bolsillo / empresa'),
('feriados_anuales', 16.0000, 'Cantidad de feriados al año'),
('fodeco', 0.0100, 'FODECO 1% (sobre fondo desempleo)'),
('fondo_desempleo', 0.1200, 'Fondo de Cese Laboral / Desempleo (12%)'),
('ieric', 0.0200, 'IERIC 2% (sobre fondo desempleo)'),
('incidencia_enfermedad', 0.0250, 'Incidencia por enfermedad o accidentes (2.5%)'),
('jubilacion_obrero', 0.1100, 'Jubilación Obrero (%)'),
('obra_social', 0.0600, 'Obra Social (6%)'),
('obra_social_obrero', 0.0300, 'Obra Social Obrero (%)'),
('pami_obrero', 0.0300, 'PAMI / Ley 19032 Obrero (%)'),
('preocupacional_unitario', 95000.0000, 'Costo examen preocupacional'),
('presentismo', 0.2000, 'Asistencia perfecta 20%'),
('sindicato_obrero', 0.0250, 'Aporte Sindical Obrero (%)'),
('vianda_diaria', 7500.0000, 'Valor vianda diaria en obra');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_mo_epp`
--

CREATE TABLE `presupuesto_mo_epp` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `meses_reposicion` int(11) NOT NULL DEFAULT 6
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_mo_epp`
--

INSERT INTO `presupuesto_mo_epp` (`id`, `descripcion`, `precio_unitario`, `cantidad`, `meses_reposicion`) VALUES
(1, 'Zapato de seguridad', 20000.00, 2, 6),
(2, 'Pantalón', 21000.00, 2, 6),
(3, 'Camisa', 22000.00, 2, 6),
(4, 'Guantes moteados', 850.00, 36, 6),
(5, 'Lentes de seguridad', 2500.00, 18, 6),
(6, 'Casco', 8500.00, 1, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_tareas`
--

CREATE TABLE `presupuesto_tareas` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `unidad` varchar(20) NOT NULL DEFAULT 'GL',
  `rendimiento_unidad` decimal(12,2) NOT NULL DEFAULT 1.00,
  `costo_materiales` decimal(15,2) NOT NULL DEFAULT 0.00,
  `costo_mo` decimal(15,2) NOT NULL DEFAULT 0.00,
  `costo_equipos` decimal(15,2) NOT NULL DEFAULT 0.00,
  `costo_subcontratos` decimal(15,2) NOT NULL DEFAULT 0.00,
  `costo_unitario_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `observaciones` text DEFAULT NULL,
  `usuario_nombre` varchar(100) DEFAULT 'Sistema',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `presupuesto_tareas`
--

INSERT INTO `presupuesto_tareas` (`id`, `codigo`, `nombre`, `unidad`, `rendimiento_unidad`, `costo_materiales`, `costo_mo`, `costo_equipos`, `costo_subcontratos`, `costo_unitario_total`, `observaciones`, `usuario_nombre`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, '10', 'TEST', 'M3', 2.00, 656.80, 5399.00, 0.00, 0.00, 6055.80, '', 'YAIR', '2026-09-23 03:09:41', '2026-09-23 03:10:47'),
(2, '1', 'HORMIGON H10', 'M3', 1.00, 760.00, 6348.00, 55000.00, 0.00, 62108.00, 'ES UNA PRUEBA', 'YAIR', '2026-09-23 03:27:03', '2026-09-23 03:27:03'),
(3, 'ELECTRICO', 'TEST', 'M3', 1.00, 760.00, 6348.00, 55000.00, 10000.00, 72108.00, '', 'YAIR', '2026-09-23 03:28:22', '2026-10-02 15:20:59'),
(4, 'ELECTRICO', 'PISO DE HORMIGON', 'M3', 1.00, 4720.00, 63480.00, 55000.00, 2000000.00, 2123200.00, '', 'YAIR', '2026-09-23 13:05:19', '2026-10-02 15:20:50'),
(5, 'MAQ', 'PISO HORMIGON H21', 'M3', 1.00, 760.00, 53990.00, 55000.00, 3000000.00, 3109750.00, 'TESTTTTTTTTTTTTTTTTTTTTTT', 'YAIR', '2026-09-24 01:52:14', '2026-10-02 15:20:41'),
(6, 'MAQ', 'APERTURA DE CAJA PROF. 20 CM.  PARA PAVIMENTO. CON EQUIPO RETROPALA Y CAMIÓN VOLCADOR, RETIRO DE SUELO FUERA DEL PREDIO.', 'M2', 0.80, 100000.00, 105149.60, 1332500.00, 0.00, 1537649.60, '', 'YAIR', '2026-10-02 10:49:15', '2026-10-03 17:17:55'),
(7, 'H010', 'CALZADA DE HORMIGON', 'M3', 1.00, 360.00, 30015.08, 6250.00, 0.00, 36625.08, 'CONSIDERACIONES GENERALES:\n1. LOS PRECIOS ANTERIORES NO INCLUYEN EL IMPUESTO AL VALOR AGREGADO (IVA).-\n2. FORMA DE PAGO:\n• ANTICIPO FINANCIERO 20 % CON LA FIRMA DEL CONTRATO U ORDEN DE COMPRA. (SI HUBIESE) \n• SALDO CON CERTIFICACIONES DE AVANCE DE OBRA CADA 15 DÍAS, PAGADEROS A 60 DÍAS DE FECHA DE FACTURA. \n3. PLAZO DE OBRA ESTIMADO: 5 DÍAS CORRIDOS. \n\n4. RESPONSABILIDADES Y SUMINISTROS POR CUENTA Y CARGO DE LA CONTRATADA:\n• MATERIALES PARA LA CORRECTA EJECUCIÓN DE LOS TRABAJOS.\n• MAQUINAS, HERRAMIENTAS MANUALES Y ELÉCTRICAS, MANO DE OBRA PARA LLEVAR A CABO LOS TRABAJOS MENCIONADOS.\n• PERSONAL DE OBRA: OBREROS ESPECIALIZADOS Y AYUDANTE(S) CON CAPACIDAD Y EXPERIENCIA ACORDES A LAS TAREAS A DESARROLLAR, EN RELACIÓN DE DEPENDENCIA BAJO LOS CONVENIOS COLECTIVOS VIGENTE (UOCRA - MES BASE COTIZACIÓN ENERO). SE ANALIZARÁN LAS ACTUALIZACIONES DEL LOS CERTIFICADOS EN CASO DE FUTUROS AJUSTES SINDICALES PARA LA MANO DE OBRA. SE INCLUYE PROFESIONAL DE ARQUITECTURA MATRICULADO PARA LA DIRECCIÓN Y CONDUCCIÓN DE OBRA.\n• ROPA DE TRABAJO Y ELEMENTOS DE PROTECCIÓN PERSONAL SEGÚN EXIGENCIAS DE ART Y MINISTERIOS DE S&H.\n• HIGIENE & SEGURIDAD: AVISO DE OBRA APROBADO POR LA ART, CON SEGUIMIENTO SEMANAL DE TÉCNICO ESPECIALIZADO.\n\n5. RESPONSABILIDADES Y SUMINISTROS POR CUENTA Y CARGO DE LA CONTRATANTE:\n• ZONA DE OBRA E INMEDIACIONES LIMPIAS, LIBRES OBSTÁCULOS SUPERFICIALES, Y EN CONDICIONES PARA ACCEDER A LA REALIZACIÓN DE LOS TRABAJOS.\n• INFORMACIÓN DE TODA LA INTERFERENCIAS EXISTENTES SUBTERRANEAS,\n• GESTIÓN, PERMISOS Y HABILITACIONES PERTINENTES.\n \n• VALIDEZ DE LA OFERTA: 15 DÍAS CORRIDOS A PARTIR DE LA FECHA DE COTIZACIÓN', 'YAIR', '2026-10-03 19:34:02', '2026-10-03 19:34:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_tarea_detalles`
--

CREATE TABLE `presupuesto_tarea_detalles` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `tipo` enum('MATERIAL','MO','EQUIPO','SUBCONTRATO') NOT NULL,
  `recurso_id` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `unidad` varchar(20) NOT NULL DEFAULT 'UN',
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cantidad` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `observaciones` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `presupuesto_tarea_detalles`
--

INSERT INTO `presupuesto_tarea_detalles` (`id`, `tarea_id`, `tipo`, `recurso_id`, `descripcion`, `unidad`, `precio_unitario`, `cantidad`, `subtotal`, `observaciones`) VALUES
(4, 1, 'MATERIAL', 10, 'CAL HIDRATADA', 'kg', 328.40, 1.0000, 328.40, 'TEST'),
(5, 1, 'MO', 4, 'Ayudante', 'HS', 5399.00, 1.0000, 5399.00, 'TEST'),
(6, 1, 'MATERIAL', 10, 'CAL HIDRATADA', 'kg', 328.40, 1.0000, 328.40, ''),
(7, 2, 'MATERIAL', 9, 'CEMENTO', 'kg', 360.00, 1.0000, 360.00, ''),
(8, 2, 'MATERIAL', 12, 'PEGAMENTO CERAMICA', 'KG', 400.00, 1.0000, 400.00, ''),
(9, 2, 'MO', 2, 'OFICIAL', 'HS', 6348.00, 1.0000, 6348.00, ''),
(10, 2, 'EQUIPO', 3, 'MINICARGADORA C/BALDE', 'HR', 55000.00, 1.0000, 55000.00, ''),
(44, 5, 'MATERIAL', 9, 'CEMENTO', 'kg', 360.00, 1.0000, 360.00, ''),
(45, 5, 'MATERIAL', 12, 'PEGAMENTO CERAMICA', 'KG', 400.00, 1.0000, 400.00, ''),
(46, 5, 'MO', 4, 'AYUDANTE', 'HS', 5399.00, 10.0000, 53990.00, ''),
(47, 5, 'EQUIPO', 3, 'MINICARGADORA C/BALDE', 'HR', 55000.00, 1.0000, 55000.00, ''),
(48, 5, 'SUBCONTRATO', NULL, 'PISO LLANEADO', 'GL', 1000000.00, 3.0000, 3000000.00, 'ESTO LO HACE MONTOTO'),
(49, 4, 'MATERIAL', 9, 'CEMENTO', 'kg', 360.00, 2.0000, 720.00, ''),
(50, 4, 'MATERIAL', 12, 'PEGAMENTO CERAMICA', 'KG', 400.00, 10.0000, 4000.00, ''),
(51, 4, 'MO', 2, 'OFICIAL', 'HS', 6348.00, 10.0000, 63480.00, ''),
(52, 4, 'EQUIPO', 3, 'MINICARGADORA C/BALDE', 'HR', 55000.00, 1.0000, 55000.00, ''),
(53, 4, 'SUBCONTRATO', NULL, 'LLANEADO DE PISO', 'M2', 2000000.00, 1.0000, 2000000.00, ''),
(54, 3, 'MATERIAL', 9, 'CEMENTO', 'kg', 360.00, 1.0000, 360.00, ''),
(55, 3, 'MATERIAL', 12, 'PEGAMENTO CERAMICA', 'KG', 400.00, 1.0000, 400.00, ''),
(56, 3, 'MO', 2, 'OFICIAL', 'HS', 6348.00, 1.0000, 6348.00, ''),
(57, 3, 'EQUIPO', 3, 'MINICARGADORA C/BALDE', 'HR', 55000.00, 1.0000, 55000.00, ''),
(58, 3, 'SUBCONTRATO', NULL, 'TEST 10', 'GL', 1.00, 10000.0000, 10000.00, ''),
(63, 6, 'MATERIAL', 11, 'TES', 'KG', 100000.00, 1.0000, 100000.00, ''),
(64, 6, 'MO', 4, 'AYUDANTE', 'HS', 13143.70, 8.0000, 105149.60, 'ASISTENCIA A MAQUINA'),
(65, 6, 'EQUIPO', 3, 'MINICARGADORA C/BALDE', 'HR', 55000.00, 24.0000, 1320000.00, 'RETRO PALA: APERTURA DE CAJA ESP 20 CM, ANCHO 6 M X 35 M X DIA, CON RETROEPALA CON CARGA DE SUELO.'),
(66, 6, 'EQUIPO', 2, 'ANDAMIO TUBULAR (TIPO SORRENTO) CUERPO COMPLETO ESTÁNDAR', 'DIA', 1250.00, 10.0000, 12500.00, ''),
(67, 7, 'MATERIAL', 9, 'CEMENTO', 'kg', 360.00, 1.0000, 360.00, ''),
(68, 7, 'MO', 2, 'OFICIAL', 'HS', 15007.54, 2.0000, 30015.08, 'EL OFICIAL LO VA A AYUDAR A HACER COSAS'),
(69, 7, 'EQUIPO', 2, 'ANDAMIO TUBULAR (TIPO SORRENTO) CUERPO COMPLETO ESTÁNDAR', 'DIA', 1250.00, 5.0000, 6250.00, 'ESTE ES UN MENSAJE DE MUESTRA ESTE ES UN MENSAJE DE MUESTRAESTE ES UN MENSAJE DE MUESTRAESTE ES UN MENSAJE DE MUESTRAESTE ES UN MENSAJE DE MUESTRAESTE ES UN MENSAJE DE MUESTRAESTE ES UN MENSAJE DE MUESTRA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuesto_versiones`
--

CREATE TABLE `presupuesto_versiones` (
  `id` int(11) NOT NULL,
  `presupuesto_id` int(11) NOT NULL,
  `version_numero` int(11) NOT NULL DEFAULT 1,
  `fecha_version` datetime DEFAULT current_timestamp(),
  `snapshot_json` longtext NOT NULL,
  `comentario` varchar(255) DEFAULT '',
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `presupuesto_versiones`
--

INSERT INTO `presupuesto_versiones` (`id`, `presupuesto_id`, `version_numero`, `fecha_version`, `snapshot_json`, `comentario`, `usuario_id`) VALUES
(2, 2, 1, '2026-10-03 20:17:36', '{\"id\":\"0\",\"codigo\":\"PRE-2026-002\",\"fecha\":\"2026-10-04\",\"cliente_id\":\"7\",\"obra_id\":\"\",\"titulo\":\"PRUEBA TEST\",\"estado\":\"Borrador\",\"coeficiente_k_base\":\"1.0000\",\"coeficiente_k_general\":\"1.0000\",\"condiciones\":\"\",\"total_neto\":2123200,\"total_presupuestado\":2123200,\"rubros\":[{\"numero\":\"1\",\"titulo\":\"PRELIMINARES\",\"items\":[{\"item_num\":\"1.1\",\"tarea_id\":\"4\",\"detalle\":\"PISO DE HORMIGON\",\"unidad\":\"M3\",\"cantidad\":1,\"precio_base\":2123200,\"precio_unitario\":2123200,\"subtotal\":2123200}]}]}', 'Creación inicial versión 1', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `condicion_fiscal` varchar(50) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `cp` varchar(10) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `cbu` varchar(22) DEFAULT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `producto_servicio` varchar(150) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `cuit`, `condicion_fiscal`, `direccion`, `localidad`, `provincia`, `cp`, `whatsapp`, `telefono`, `contacto`, `cbu`, `alias`, `producto_servicio`, `observaciones`, `archivo`, `usuario_id`) VALUES
(2, 'CORZO YAIR GONZALO', '20-35120717-6', 'RESPONSABLE INSCRIPTO', 'PASAJE LASSAGA 4850', 'SANTA FE', 'SANTA FE', '3000', '3424357046', '03424357046', 'YAIR GONZALO', NULL, NULL, 'DE TODO', 'TEST', NULL, 2),
(3, 'ELECTROVOLT SRL', '30-57950617-9', 'RESPONSABLE INSCRIPTO', 'FACUNDO ZUVIRIA 2050', 'SANTA FE', 'SANTA FE', '3000', '3424435456', '', 'GERARDO CONVA', NULL, NULL, 'ELECTRICIDAD', '', NULL, 2),
(4, 'JUAN BLANGINO S.A', '30-71471099-7', 'RESPONSABLE INSCRIPTO', 'GRAL PAZ', 'SANTA FE', 'SANTA FE', '3000', '3425146565', '', 'JUAN ', NULL, NULL, 'LOSETAS', '', NULL, 2),
(6, 'CORZO YAIR GONZAO', '20-35120741-7', 'MONOTRIBUTISTA', 'PASAJE LASSAGA 4850', 'SANTA FE', 'SANTA FE', '3000', '34254545861', '334243526', 'CORZO', '12020000000000000000', '', 'MECANICA GENERAL', '', 'prov_1785792127_806.pdf', 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `retenciones_venta`
--

CREATE TABLE `retenciones_venta` (
  `id` int(11) NOT NULL,
  `factura_id` int(11) NOT NULL,
  `tipo_retencion_id` int(11) NOT NULL,
  `nro_certificado` varchar(50) DEFAULT NULL,
  `importe` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fecha_retencion` date NOT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'admin'),
(4, 'operador'),
(5, 'contador'),
(6, 'arquitecto'),
(7, 'auditor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategorias`
--

CREATE TABLE `subcategorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subcategorias`
--

INSERT INTO `subcategorias` (`id`, `nombre`, `categoria_id`) VALUES
(1, 'VW - DHC376', 1),
(2, 'VW - EMM683', 1),
(4, 'YAIR', 2),
(5, 'ABI', 2),
(6, 'SOFI', 2),
(7, 'ARENA', 3),
(8, 'CEMENTO', 3),
(9, 'PIEDRA', 3),
(10, 'VW - DHC376', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `creador_id` int(11) NOT NULL,
  `asignado_id` int(11) DEFAULT NULL,
  `prioridad` enum('BAJA','MEDIA','ALTA','URGENTE') DEFAULT 'MEDIA',
  `estado` enum('PENDIENTE','EN_PROCESO','REVISION','COMPLETADO') DEFAULT 'PENDIENTE',
  `fecha_limite` date DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ultima_vista_en` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `titulo`, `descripcion`, `creador_id`, `asignado_id`, `prioridad`, `estado`, `fecha_limite`, `creado_en`, `actualizado_en`, `ultima_vista_en`) VALUES
(22, 'REVISAR', '', 3, 7, 'ALTA', 'COMPLETADO', '2026-08-31', '2026-08-31 13:47:16', '2026-09-08 19:51:30', '2026-08-31 10:47:29'),
(24, 'CLOACA MAIPU 230', 'CLOACA QUE HAY QUE REPARAR PARA EL VIERNES', 7, 7, 'ALTA', 'COMPLETADO', '2026-09-11', '2026-09-08 19:48:00', '2026-09-27 18:37:31', '2026-09-11 13:37:58'),
(25, 'TEST', 'DSADAS', 3, 3, 'MEDIA', 'PENDIENTE', '2026-09-08', '2026-09-08 19:51:57', '2026-09-11 16:38:58', '2026-09-11 13:38:45'),
(26, 'GESTIONAR MOROSOS ALIMENTARIOS', 'NECESITO ESTO PARA LA OBRA DE ASSSA', 7, 3, 'ALTA', 'PENDIENTE', '2026-09-11', '2026-09-09 15:35:25', '2026-09-11 16:39:00', '2026-09-09 12:36:17'),
(27, 'VER PRESUPUE', 'TESRADSAD', 6, 7, 'ALTA', 'PENDIENTE', '2026-09-18', '2026-09-10 15:54:46', '2026-09-11 16:39:01', '2026-09-10 12:55:54'),
(28, 'REVISION DE TAREAS FUTURAS', 'TEST TAREA NUEVA DEB', 3, 3, 'ALTA', 'PENDIENTE', '2026-09-21', '2026-09-20 14:25:55', '2026-09-20 14:25:55', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea_adjuntos`
--

CREATE TABLE `tarea_adjuntos` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea_comentarios`
--

CREATE TABLE `tarea_comentarios` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarea_comentarios`
--

INSERT INTO `tarea_comentarios` (`id`, `tarea_id`, `usuario_id`, `comentario`, `fecha_creacion`) VALUES
(23, 26, 7, 'REVISAR FOTO QUE SE VE MAL', '2026-09-09 12:36:06'),
(24, 27, 6, 'OK LA VEO DESPUES', '2026-09-10 12:56:04'),
(25, 24, 3, 'HOLA VER', '2026-09-11 13:38:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_comprobante`
--

CREATE TABLE `tipos_comprobante` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_comprobante`
--

INSERT INTO `tipos_comprobante` (`id`, `nombre`, `usuario_id`) VALUES
(1, 'FACTURA A', NULL),
(11, 'FACTURA B', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_retenciones`
--

CREATE TABLE `tipos_retenciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_retenciones`
--

INSERT INTO `tipos_retenciones` (`id`, `nombre`, `creado_en`) VALUES
(1, 'Ingresos Brutos (IIBB)', '2026-07-31 15:54:35'),
(2, 'Ganancias', '2026-07-31 15:54:35'),
(3, 'IVA', '2026-07-31 15:54:35'),
(4, 'SUSS (Previsional)', '2026-07-31 15:54:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `rol_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `rol_id`) VALUES
(3, 'YAIR', '$2y$10$fDW8HpFLe9vuZWVwWqSgyusV.LpG9vqZ3ZOd0KRT5h418MnUThy56', 1),
(4, 'ABI', '$2y$10$vdIvrh.VrRXh7v8Ym1FJY.r20mcREGY/awblc/2fTsB7Ua7Qmafiq', 5),
(6, 'DIEGO', '$2y$10$82zEmqhamXQ1qAvVr5fYcOM/0EK0Q7FeY4ltCEujaeNJs2KKRhbG.', 4),
(7, 'SOFIA', '$2y$10$AdmcbADBRQWHRsCGh1LyK.AaxNsfk1u7vLswfqeRQoED46Y4bvt4i', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL,
  `clasificacion` enum('VEHICULO','MAQUINARIA','HERRAMIENTA') NOT NULL DEFAULT 'VEHICULO',
  `dominio_patente` varchar(50) DEFAULT NULL,
  `marca` varchar(100) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `anio` int(4) DEFAULT NULL,
  `titular` varchar(150) DEFAULT NULL,
  `km_horas_inicial` decimal(12,2) DEFAULT 0.00,
  `fecha_adquisicion` date DEFAULT NULL,
  `km_horas_actual` decimal(12,2) DEFAULT 0.00,
  `proximo_service` decimal(12,2) DEFAULT 0.00,
  `unidad_medida` enum('KM','HORAS') NOT NULL DEFAULT 'KM',
  `estado` enum('ACTIVO','DESACTIVO','BAJA') NOT NULL DEFAULT 'ACTIVO',
  `descripcion` text DEFAULT NULL,
  `aceite_motor` varchar(100) DEFAULT NULL,
  `cant_aceite_motor` varchar(50) DEFAULT NULL,
  `aceite_caja` varchar(100) DEFAULT NULL,
  `cant_aceite_caja` varchar(50) DEFAULT NULL,
  `aceite_diferencial` varchar(100) DEFAULT NULL,
  `cant_aceite_diferencial` varchar(50) DEFAULT NULL,
  `aceite_hidraulico` varchar(100) DEFAULT NULL,
  `cant_aceite_hidraulico` varchar(50) DEFAULT NULL,
  `filtros_codigos` text DEFAULT NULL,
  `vencimiento_vtv` date DEFAULT NULL,
  `vencimiento_seguro` date DEFAULT NULL,
  `compañia_seguro` varchar(100) DEFAULT NULL,
  `nro_poliza` varchar(100) DEFAULT NULL,
  `factura_compra_archivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id`, `clasificacion`, `dominio_patente`, `marca`, `modelo`, `tipo`, `anio`, `titular`, `km_horas_inicial`, `fecha_adquisicion`, `km_horas_actual`, `proximo_service`, `unidad_medida`, `estado`, `descripcion`, `aceite_motor`, `cant_aceite_motor`, `aceite_caja`, `cant_aceite_caja`, `aceite_diferencial`, `cant_aceite_diferencial`, `aceite_hidraulico`, `cant_aceite_hidraulico`, `filtros_codigos`, `vencimiento_vtv`, `vencimiento_seguro`, `compañia_seguro`, `nro_poliza`, `factura_compra_archivo`, `usuario_id`, `created_at`) VALUES
(1, 'VEHICULO', 'MEZ336', 'CITROEN', 'BERLINGO', 'Camión Chasis', 2013, 'COROZ YAIR GONZALO', 0.00, NULL, 197500.00, 0.00, 'KM', 'ACTIVO', 'TEST', '15W40', '', '80W90', '', '80W90', '', '', '', 'CODIGO ACEITE: 203050\r\nCODIGO AIRE: \r\nSECUNDARIO AIRE: \r\nOTRO FILTRO ', '2026-09-17', '2026-10-23', 'RIVADAVIA', '10101010', NULL, 3, '2026-09-05 15:36:17'),
(2, 'VEHICULO', 'AI093JM', 'REANULT', 'KWID', 'AUTOMÓVIL', 2026, 'CORZO YAIR GONZALO', 0.00, '2026-09-05', 15000.00, 0.00, 'KM', 'ACTIVO', '', '15W40', '5', '80W90', '2', '80W', '3', '', '', 'MOTOR: HASTING 232126\r\nAIRE: 2351162\r\nAIRE SECUNDARIO:\r\n', '2026-09-11', '2026-09-18', 'RIVADAVIA', '462626262', NULL, 3, '2026-09-05 20:43:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculo_archivos`
--

CREATE TABLE `vehiculo_archivos` (
  `id` int(11) NOT NULL,
  `vehiculo_id` int(11) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `tipo_adjunto` enum('DOCUMENTO','FOTO','FACTURA') DEFAULT 'DOCUMENTO',
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculo_archivos`
--

INSERT INTO `vehiculo_archivos` (`id`, `vehiculo_id`, `archivo`, `nombre_original`, `tipo_adjunto`, `fecha_subida`) VALUES
(1, 1, '1/veh_6a9c36f1100f8_0.jpg', '510674347_1233247354931693_3638416914091782073_n.jpg', 'DOCUMENTO', '2026-09-05 15:36:17'),
(2, 1, '1/veh_6a9c36f1116a0_1.jpg', '513756196_3778134268996706_7973020451143995764_n.jpg', 'DOCUMENTO', '2026-09-05 15:36:17'),
(3, 1, '1/veh_6a9c36f111ff7_2.jpg', 'trailer.jpg', 'DOCUMENTO', '2026-09-05 15:36:17'),
(4, 1, '1/adj_6a9c3b3d2f997.pdf', 'titulo.pdf', 'DOCUMENTO', '2026-09-05 15:54:37'),
(5, 1, '1/adj_6a9c3b5a54f3b.jpg', 'trailer.jpg', 'FOTO', '2026-09-05 15:55:06'),
(6, 1, '1/adj_6a9c3b61a4ff0.jpg', '569124418_4235384476703121_7582342535131890105_n.jpg', 'FOTO', '2026-09-05 15:55:13'),
(7, 1, '1/adj_6a9c3b6a3eb4d.jpg', '510674347_1233247354931693_3638416914091782073_n.jpg', 'FOTO', '2026-09-05 15:55:22'),
(8, 2, '2/adj_6a9c7f086372c.pdf', 'titulo.pdf', 'DOCUMENTO', '2026-09-05 20:43:52'),
(9, 2, '2/adj_6a9c7f191840f.jpg', 'trailer.jpg', 'FOTO', '2026-09-05 20:44:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculo_services`
--

CREATE TABLE `vehiculo_services` (
  `id` int(11) NOT NULL,
  `vehiculo_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `lectura_km_horas` decimal(12,2) NOT NULL,
  `realizado_por` varchar(150) DEFAULT NULL,
  `trabajo_realizado` text NOT NULL,
  `costo` decimal(12,2) DEFAULT 0.00,
  `archivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vehiculo_services`
--

INSERT INTO `vehiculo_services` (`id`, `vehiculo_id`, `fecha`, `lectura_km_horas`, `realizado_por`, `trabajo_realizado`, `costo`, `archivo`, `usuario_id`, `created_at`) VALUES
(1, 1, '2026-09-05', 1970.00, 'FERNANDO', 'SE REALIZO CAMBIO DE ACEITE FILTROS Y TODO COMPLETO. ', 5000000.00, NULL, 3, '2026-09-05 15:56:12'),
(2, 2, '2026-09-05', 16000.00, 'GONZALO', 'SE LE HIZO EL SERVICE COMPLETO + DISTRIBUCIÓN', 320000.00, NULL, 3, '2026-09-05 20:49:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vencimientos`
--

CREATE TABLE `vencimientos` (
  `id` int(11) NOT NULL,
  `vencimiento_padre_id` int(11) DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `monto` decimal(12,2) NOT NULL DEFAULT 0.00,
  `nro_cuota` int(11) DEFAULT 1,
  `total_cuotas` int(11) DEFAULT 1,
  `fecha_vencimiento` date NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `subcategoria_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `estado` enum('PENDIENTE','PAGADO','ANULADO') NOT NULL DEFAULT 'PENDIENTE',
  `fecha_pago` datetime DEFAULT NULL,
  `caja_id` int(11) DEFAULT NULL,
  `dias_aviso` int(11) DEFAULT 7,
  `archivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vencimientos`
--

INSERT INTO `vencimientos` (`id`, `vencimiento_padre_id`, `titulo`, `descripcion`, `monto`, `nro_cuota`, `total_cuotas`, `fecha_vencimiento`, `categoria_id`, `subcategoria_id`, `proveedor_id`, `estado`, `fecha_pago`, `caja_id`, `dias_aviso`, `archivo`, `usuario_id`, `created_at`, `updated_at`) VALUES
(34, NULL, 'MES TEST 30 DÍAS', '', 100.00, 1, 1, '2026-09-09', 1, 1, 2, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-01 17:08:15', '2026-09-01 17:08:42'),
(35, NULL, 'DSADSA', '', 1000.00, 1, 1, '2026-09-02', 1, 1, 6, 'PENDIENTE', NULL, NULL, 5, NULL, NULL, '2026-09-01 17:25:49', '2026-09-01 17:26:08'),
(36, NULL, 'TEST OTRO MES', '', 1000.00, 1, 1, '2026-09-01', 3, 7, 2, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-01 17:35:18', '2026-09-01 17:40:32'),
(37, NULL, 'VENCIDIO MAL', '', 10.00, 1, 1, '2026-08-01', 3, 7, 2, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-01 17:43:45', NULL),
(38, NULL, 'GAS OFICINA ZEBALLOS (Cuota 1/12)', 'N° CLIENTE 22222', 2000.00, 1, 12, '2026-09-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL),
(39, 38, 'GAS OFICINA ZEBALLOS (Cuota 2/12)', 'N° CLIENTE 22222', 2000.00, 2, 12, '2026-10-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL),
(40, 38, 'GAS OFICINA ZEBALLOS (Cuota 3/12)', 'N° CLIENTE 22222', 2000.00, 3, 12, '2026-11-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL),
(41, 38, 'GAS OFICINA ZEBALLOS (Cuota 4/12)', 'N° CLIENTE 22222', 2000.00, 4, 12, '2026-12-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL),
(42, 38, 'GAS OFICINA ZEBALLOS (Cuota 5/12)', 'N° CLIENTE 22222', 2000.00, 5, 12, '2027-01-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL),
(43, 38, 'GAS OFICINA ZEBALLOS (Cuota 6/12)', 'N° CLIENTE 22222', 2000.00, 6, 12, '2027-02-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL),
(44, 38, 'GAS OFICINA ZEBALLOS (Cuota 7/12)', 'N° CLIENTE 22222', 2000.00, 7, 12, '2027-03-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL),
(45, 38, 'GAS OFICINA ZEBALLOS (Cuota 8/12)', 'N° CLIENTE 22222', 2000.00, 8, 12, '2027-04-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL),
(46, 38, 'GAS OFICINA ZEBALLOS (Cuota 9/12)', 'N° CLIENTE 22222', 2000.00, 9, 12, '2027-05-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL),
(47, 38, 'GAS OFICINA ZEBALLOS (Cuota 10/12)', 'N° CLIENTE 22222', 2000.00, 10, 12, '2027-06-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL),
(48, 38, 'GAS OFICINA ZEBALLOS (Cuota 11/12)', 'N° CLIENTE 22222', 2000.00, 11, 12, '2027-07-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL),
(49, 38, 'GAS OFICINA ZEBALLOS (Cuota 12/12)', 'N° CLIENTE 22222', 2000.00, 12, 12, '2027-08-17', 1, 2, 6, 'PENDIENTE', NULL, NULL, 7, NULL, NULL, '2026-09-09 15:25:35', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `centros_costos`
--
ALTER TABLE `centros_costos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cheques`
--
ALTER TABLE `cheques`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `facturas_venta`
--
ALTER TABLE `facturas_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_facturas_obras` (`obra_id`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `medios_pago`
--
ALTER TABLE `medios_pago`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `obras`
--
ALTER TABLE `obras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `obra_archivos`
--
ALTER TABLE `obra_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `obra_id` (`obra_id`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `personal_archivos`
--
ALTER TABLE `personal_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`);

--
-- Indices de la tabla `personal_movimientos`
--
ALTER TABLE `personal_movimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`);

--
-- Indices de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `obra_id` (`obra_id`);

--
-- Indices de la tabla `presupuesto_coeficientes_plantillas`
--
ALTER TABLE `presupuesto_coeficientes_plantillas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuesto_condiciones_plantilla`
--
ALTER TABLE `presupuesto_condiciones_plantilla`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuesto_detalles`
--
ALTER TABLE `presupuesto_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_presupuesto_detalles_padre` (`presupuesto_id`);

--
-- Indices de la tabla `presupuesto_equipos`
--
ALTER TABLE `presupuesto_equipos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuesto_indirectos`
--
ALTER TABLE `presupuesto_indirectos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `presupuesto_id` (`presupuesto_id`);

--
-- Indices de la tabla `presupuesto_indirectos_categorias`
--
ALTER TABLE `presupuesto_indirectos_categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_unico` (`codigo`);

--
-- Indices de la tabla `presupuesto_indirectos_config`
--
ALTER TABLE `presupuesto_indirectos_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `presupuesto_indirectos_plantilla`
--
ALTER TABLE `presupuesto_indirectos_plantilla`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuesto_materiales`
--
ALTER TABLE `presupuesto_materiales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuesto_mo_adjuntos`
--
ALTER TABLE `presupuesto_mo_adjuntos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuesto_mo_auditoria`
--
ALTER TABLE `presupuesto_mo_auditoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuesto_mo_categorias`
--
ALTER TABLE `presupuesto_mo_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuesto_mo_configuracion`
--
ALTER TABLE `presupuesto_mo_configuracion`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `presupuesto_mo_epp`
--
ALTER TABLE `presupuesto_mo_epp`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuesto_tareas`
--
ALTER TABLE `presupuesto_tareas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuesto_tarea_detalles`
--
ALTER TABLE `presupuesto_tarea_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tarea_id` (`tarea_id`);

--
-- Indices de la tabla `presupuesto_versiones`
--
ALTER TABLE `presupuesto_versiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `presupuesto_id` (`presupuesto_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `retenciones_venta`
--
ALTER TABLE `retenciones_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `factura_id` (`factura_id`),
  ADD KEY `tipo_retencion_id` (`tipo_retencion_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creador_id` (`creador_id`),
  ADD KEY `asignado_id` (`asignado_id`);

--
-- Indices de la tabla `tarea_adjuntos`
--
ALTER TABLE `tarea_adjuntos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tarea_id` (`tarea_id`);

--
-- Indices de la tabla `tarea_comentarios`
--
ALTER TABLE `tarea_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tarea_id` (`tarea_id`);

--
-- Indices de la tabla `tipos_comprobante`
--
ALTER TABLE `tipos_comprobante`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipos_retenciones`
--
ALTER TABLE `tipos_retenciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `vehiculo_archivos`
--
ALTER TABLE `vehiculo_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehiculo_id` (`vehiculo_id`);

--
-- Indices de la tabla `vehiculo_services`
--
ALTER TABLE `vehiculo_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehiculo_id` (`vehiculo_id`);

--
-- Indices de la tabla `vencimientos`
--
ALTER TABLE `vencimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `caja_id` (`caja_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_vencimientos_padre` (`vencimiento_padre_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cajas`
--
ALTER TABLE `cajas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `centros_costos`
--
ALTER TABLE `centros_costos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cheques`
--
ALTER TABLE `cheques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `facturas_venta`
--
ALTER TABLE `facturas_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT de la tabla `medios_pago`
--
ALTER TABLE `medios_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `movimientos_caja`
--
ALTER TABLE `movimientos_caja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT de la tabla `obras`
--
ALTER TABLE `obras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `obra_archivos`
--
ALTER TABLE `obra_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `personal_archivos`
--
ALTER TABLE `personal_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `personal_movimientos`
--
ALTER TABLE `personal_movimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `presupuesto_coeficientes_plantillas`
--
ALTER TABLE `presupuesto_coeficientes_plantillas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `presupuesto_condiciones_plantilla`
--
ALTER TABLE `presupuesto_condiciones_plantilla`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `presupuesto_detalles`
--
ALTER TABLE `presupuesto_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `presupuesto_equipos`
--
ALTER TABLE `presupuesto_equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `presupuesto_indirectos`
--
ALTER TABLE `presupuesto_indirectos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `presupuesto_indirectos_categorias`
--
ALTER TABLE `presupuesto_indirectos_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `presupuesto_indirectos_config`
--
ALTER TABLE `presupuesto_indirectos_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `presupuesto_indirectos_plantilla`
--
ALTER TABLE `presupuesto_indirectos_plantilla`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `presupuesto_materiales`
--
ALTER TABLE `presupuesto_materiales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `presupuesto_mo_adjuntos`
--
ALTER TABLE `presupuesto_mo_adjuntos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `presupuesto_mo_auditoria`
--
ALTER TABLE `presupuesto_mo_auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `presupuesto_mo_categorias`
--
ALTER TABLE `presupuesto_mo_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `presupuesto_mo_epp`
--
ALTER TABLE `presupuesto_mo_epp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `presupuesto_tareas`
--
ALTER TABLE `presupuesto_tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `presupuesto_tarea_detalles`
--
ALTER TABLE `presupuesto_tarea_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT de la tabla `presupuesto_versiones`
--
ALTER TABLE `presupuesto_versiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `retenciones_venta`
--
ALTER TABLE `retenciones_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `tarea_adjuntos`
--
ALTER TABLE `tarea_adjuntos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tarea_comentarios`
--
ALTER TABLE `tarea_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `tipos_comprobante`
--
ALTER TABLE `tipos_comprobante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `tipos_retenciones`
--
ALTER TABLE `tipos_retenciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vehiculo_archivos`
--
ALTER TABLE `vehiculo_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `vehiculo_services`
--
ALTER TABLE `vehiculo_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vencimientos`
--
ALTER TABLE `vencimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `facturas_venta`
--
ALTER TABLE `facturas_venta`
  ADD CONSTRAINT `FK_facturas_obras` FOREIGN KEY (`obra_id`) REFERENCES `obras` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `obra_archivos`
--
ALTER TABLE `obra_archivos`
  ADD CONSTRAINT `obra_archivos_ibfk_1` FOREIGN KEY (`obra_id`) REFERENCES `obras` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `personal_archivos`
--
ALTER TABLE `personal_archivos`
  ADD CONSTRAINT `fk_personal_archivos` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `personal_movimientos`
--
ALTER TABLE `personal_movimientos`
  ADD CONSTRAINT `fk_personal_mov` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD CONSTRAINT `presupuestos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `presupuestos_ibfk_2` FOREIGN KEY (`obra_id`) REFERENCES `obras` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `presupuesto_detalles`
--
ALTER TABLE `presupuesto_detalles`
  ADD CONSTRAINT `fk_presupuesto_detalles_padre` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuestos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `presupuesto_tarea_detalles`
--
ALTER TABLE `presupuesto_tarea_detalles`
  ADD CONSTRAINT `fk_presupuesto_tarea` FOREIGN KEY (`tarea_id`) REFERENCES `presupuesto_tareas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `presupuesto_versiones`
--
ALTER TABLE `presupuesto_versiones`
  ADD CONSTRAINT `presupuesto_versiones_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuestos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `retenciones_venta`
--
ALTER TABLE `retenciones_venta`
  ADD CONSTRAINT `retenciones_venta_ibfk_1` FOREIGN KEY (`factura_id`) REFERENCES `facturas_venta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `retenciones_venta_ibfk_2` FOREIGN KEY (`tipo_retencion_id`) REFERENCES `tipos_retenciones` (`id`);

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`creador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tareas_ibfk_2` FOREIGN KEY (`asignado_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `tarea_adjuntos`
--
ALTER TABLE `tarea_adjuntos`
  ADD CONSTRAINT `tarea_adjuntos_ibfk_1` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tarea_comentarios`
--
ALTER TABLE `tarea_comentarios`
  ADD CONSTRAINT `tarea_comentarios_ibfk_1` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vehiculo_archivos`
--
ALTER TABLE `vehiculo_archivos`
  ADD CONSTRAINT `vehiculo_archivos_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vehiculo_services`
--
ALTER TABLE `vehiculo_services`
  ADD CONSTRAINT `vehiculo_services_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vencimientos`
--
ALTER TABLE `vencimientos`
  ADD CONSTRAINT `fk_vencimientos_caja` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_vencimientos_padre` FOREIGN KEY (`vencimiento_padre_id`) REFERENCES `vencimientos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vencimientos_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_vencimientos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
