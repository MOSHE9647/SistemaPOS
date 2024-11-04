-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 04-11-2024 a las 18:54:24
-- Versión del servidor: 8.0.39-0ubuntu0.24.04.2
-- Versión de PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bdpuntoventa`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcategoria`
--

CREATE TABLE `tbcategoria` (
  `categoriaid` int NOT NULL,
  `categorianombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoriadescripcion` text COLLATE utf8mb4_unicode_ci,
  `categoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbcategoria`
--

INSERT INTO `tbcategoria` (`categoriaid`, `categorianombre`, `categoriadescripcion`, `categoriaestado`) VALUES
(1, 'Electronics', 'Todo tipo de productos electrónicos', 1),
(2, 'Computers & Accessories', 'Computadoras y accesorios', 1),
(3, 'Mobile Devices', 'Dispositivos móviles y accesorios', 1),
(4, 'Wearable Technology', 'Tecnología ponible', 1),
(5, 'Photography & Video', 'Fotografía y video', 1),
(6, 'Printers & Scanners', 'Impresoras y escáneres', 0),
(7, 'Monitors & Displays', 'Monitores y pantallas', 0),
(8, 'Input Devices', 'Dispositivos de entrada', 1),
(9, 'Home Appliances', 'Electrodomésticos para el hogar', 1),
(10, 'Audio & Headphones', 'Audio y auriculares', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcliente`
--

CREATE TABLE `tbcliente` (
  `clienteid` int NOT NULL,
  `telefonoid` int NOT NULL,
  `clientenombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'No Definido',
  `clientealias` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'No Definido',
  `clientefechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clientefechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `clienteestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbcliente`
--

INSERT INTO `tbcliente` (`clienteid`, `telefonoid`, `clientenombre`, `clientealias`, `clientefechacreacion`, `clientefechamodificacion`, `clienteestado`) VALUES
(1, 7, 'Isaac', 'No Definido', '2024-10-14 19:35:30', '2024-10-14 19:39:36', 1),
(2, 9, 'Jason', 'Json', '2024-10-31 14:01:22', '2024-10-31 14:01:22', 1),
(3, 10, 'Maikel', 'No Definido', '2024-10-31 14:01:47', '2024-10-31 14:01:47', 1),
(4, 11, 'Ninguno', 'No Definido', '2024-11-01 19:20:14', '2024-11-01 19:20:14', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcodigobarras`
--

CREATE TABLE `tbcodigobarras` (
  `codigobarrasid` int NOT NULL,
  `codigobarrasnumero` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigobarrasestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbcodigobarras`
--

INSERT INTO `tbcodigobarras` (`codigobarrasid`, `codigobarrasnumero`, `codigobarrasestado`) VALUES
(1, '7945982662925', 1),
(2, '6236446542321', 1),
(3, '0119936861002', 1),
(4, '6031139632675', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcompra`
--

CREATE TABLE `tbcompra` (
  `compraid` int NOT NULL,
  `clienteid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `compranumerofactura` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `compramoneda` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `compramontobruto` decimal(10,2) NOT NULL,
  `compramontoneto` decimal(10,2) NOT NULL,
  `compramontoimpuesto` decimal(10,2) NOT NULL,
  `compracondicioncompra` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `compratipopago` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comprafechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comprafechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `compraestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcompradetalle`
--

CREATE TABLE `tbcompradetalle` (
  `compradetalleid` int NOT NULL,
  `compraid` int NOT NULL,
  `productoid` int NOT NULL,
  `compradetalleprecio` decimal(10,2) NOT NULL,
  `compradetallecantidad` int NOT NULL,
  `compradetalleestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcompraporpagar`
--

CREATE TABLE `tbcompraporpagar` (
  `compraporpagarid` int NOT NULL,
  `compraid` int NOT NULL,
  `compraporpagarfechavencimiento` date NOT NULL,
  `compraporpagarcancelado` tinyint NOT NULL DEFAULT (_utf8mb4'0'),
  `compraporpagarnotas` text COLLATE utf8mb4_unicode_ci,
  `compraporpagarestado` tinyint NOT NULL DEFAULT (_utf8mb4'1')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbdireccion`
--

CREATE TABLE `tbdireccion` (
  `direccionid` int NOT NULL,
  `direccionprovincia` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccioncanton` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direcciondistrito` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccionbarrio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `direccionsennas` text COLLATE utf8mb4_unicode_ci,
  `direcciondistancia` decimal(5,2) NOT NULL,
  `direccionestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbdireccion`
--

INSERT INTO `tbdireccion` (`direccionid`, `direccionprovincia`, `direccioncanton`, `direcciondistrito`, `direccionbarrio`, `direccionsennas`, `direcciondistancia`, `direccionestado`) VALUES
(1, 'SAN JOSE', 'CENTRAL', 'CARMEN', 'URB. MIRAFLORES', '200 METROS NORTE DE LA IGLESIA', 1.20, 1),
(2, 'SAN JOSE', 'CENTRAL', 'CARMEN', 'URBANIZACION MIRAFLORES', 'AL FRENTE DEL PARQUE', 1.50, 1),
(3, 'SAN JOSE', 'CENTRAL', 'CARMEN', 'URBA MIRAFLORES', 'DE LA ESQUINA 100 METROS AL ESTE', 1.30, 1),
(4, 'SAN JOSE', 'CENTRAL', 'HATILLO', 'LOS HATILLOS', 'DE LA ESCUELA 200 METROS AL SUR', 2.00, 1),
(5, 'SAN JOSE', 'CENTRAL', 'HATILLO', 'HATILLO 2', 'CERCA DEL SUPERMERCADO', 1.80, 1),
(6, 'HEREDIA', 'HEREDIA', 'HEREDIA', 'BARRIO FÁTIMA', '200 METROS ESTE DEL ESTADIO', 3.00, 1),
(7, 'HEREDIA', 'SAN PABLO', 'SAN PABLO', 'Urb Miraflores', '', 20.00, 1),
(8, 'HEREDIA', 'HEREDIA', 'HEREDIA', 'B. FATIMA', 'DIAGONAL A LA IGLESIA', 3.10, 1),
(9, 'ALAJUELA', 'ALAJUELA', 'SAN JOSE', 'RESIDENCIAL MONTECARLO', '100 METROS NORTE DEL PARQUE', 2.50, 1),
(10, 'ALAJUELA', 'ALAJUELA', 'SAN JOSE', 'RESID. MONTECARLO', 'FRENTE A LA ESCUELA', 2.45, 1),
(11, 'CARTAGO', 'CARTAGO', 'OROSI', 'CENTRO', 'DE LA IGLESIA 300 METROS OESTE', 5.00, 1),
(12, 'SAN JOSÉ', 'CENTRAL', 'CARMEN', '', '', 10.00, 1),
(13, 'CARTAGO', 'JIMÉNEZ', 'PEJIBAYE', 'NINGUNO', 'NINGUNA', 20.00, 1),
(14, 'PUNTARENAS', 'QUEPOS', 'NARANJITO', 'NINGUNO', 'NINGUNA', 10.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbimpuesto`
--

CREATE TABLE `tbimpuesto` (
  `impuestoid` int NOT NULL,
  `impuestonombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `impuestovalor` decimal(5,2) NOT NULL,
  `impuestodescripcion` text COLLATE utf8mb4_unicode_ci,
  `impuestofechainiciovigencia` date NOT NULL,
  `impuestofechafinvigencia` date NOT NULL,
  `impuestoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbimpuesto`
--

INSERT INTO `tbimpuesto` (`impuestoid`, `impuestonombre`, `impuestovalor`, `impuestodescripcion`, `impuestofechainiciovigencia`, `impuestofechafinvigencia`, `impuestoestado`) VALUES
(1, 'IVA', 13.00, 'Impuesto al Valor Agregado', '2024-09-15', '2024-09-30', 1),
(2, 'IVM', 20.00, 'Impuesto al Valor Monetario', '2024-09-14', '2024-11-21', 1),
(3, 'IMP', 13.20, 'Impuesto al Mejor Personaje', '2024-09-14', '2024-12-05', 1),
(4, 'IMJ', 23.55, 'Impuesto al Mejor Jugador', '2024-09-14', '2025-04-25', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbmarca`
--

CREATE TABLE `tbmarca` (
  `marcaid` int NOT NULL,
  `marcanombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `marcadescripcion` text COLLATE utf8mb4_unicode_ci,
  `marcaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbmarca`
--

INSERT INTO `tbmarca` (`marcaid`, `marcanombre`, `marcadescripcion`, `marcaestado`) VALUES
(1, 'DOS PINOS', 'Empresa de productos lácteos', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbpresentacion`
--

CREATE TABLE `tbpresentacion` (
  `presentacionid` int NOT NULL,
  `presentacionnombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `presentaciondescripcion` text COLLATE utf8mb4_unicode_ci,
  `presentacionestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbpresentacion`
--

INSERT INTO `tbpresentacion` (`presentacionid`, `presentacionnombre`, `presentaciondescripcion`, `presentacionestado`) VALUES
(1, '2.5ML', 'Empaque de 2.5ml', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproducto`
--

CREATE TABLE `tbproducto` (
  `productoid` int NOT NULL,
  `codigobarrasid` int NOT NULL,
  `categoriaid` int NOT NULL,
  `subcategoriaid` int NOT NULL,
  `marcaid` int NOT NULL,
  `presentacionid` int NOT NULL,
  `productonombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `productocantidad` int NOT NULL,
  `productopreciocompra` decimal(10,2) NOT NULL,
  `productoporcentajeganancia` decimal(10,2) NOT NULL,
  `productodescripcion` text COLLATE utf8mb4_unicode_ci,
  `productoimagen` text COLLATE utf8mb4_unicode_ci,
  `productofechavencimiento` date DEFAULT NULL,
  `productoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbproducto`
--

INSERT INTO `tbproducto` (`productoid`, `codigobarrasid`, `categoriaid`, `subcategoriaid`, `marcaid`, `presentacionid`, `productonombre`, `productocantidad`, `productopreciocompra`, `productoporcentajeganancia`, `productodescripcion`, `productoimagen`, `productofechavencimiento`, `productoestado`) VALUES
(1, 1, 4, 3, 1, 1, 'PRUEBA 1', 0, 1600.00, 10.00, '', '/view/static/img/productos/0004/0003/1_PRUEBA_1.webp', '2024-10-19', 1),
(2, 2, 3, 1, 1, 1, 'BIG COLA', 20, 1500.00, 20.00, '', '/view/static/img/productos/0003/0001/2_BIG_COLA.webp', '2024-10-30', 1),
(3, 3, 5, 2, 1, 1, 'EJEMPLO', 30, 1600.00, 20.00, '', '/view/static/img/product.webp', '2024-11-10', 1),
(4, 4, 5, 2, 1, 1, 'COCA COLA', 30, 2300.00, 20.00, '', '/view/static/img/product.webp', '2024-10-30', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedor`
--

CREATE TABLE `tbproveedor` (
  `proveedorid` int NOT NULL,
  `categoriaid` int NOT NULL,
  `proveedornombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proveedoremail` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proveedorfechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proveedorfechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `proveedorestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbproveedor`
--

INSERT INTO `tbproveedor` (`proveedorid`, `categoriaid`, `proveedornombre`, `proveedoremail`, `proveedorfechacreacion`, `proveedorfechamodificacion`, `proveedorestado`) VALUES
(1, 5, 'Proveedor 1', 'proveedor1@ejemplo.com', '2024-09-15 18:07:39', '2024-09-17 09:12:53', 1),
(2, 4, 'Proveedor 2', 'proveedor2@gmail.com', '2024-09-17 09:10:20', '2024-09-17 09:18:21', 1),
(3, 4, 'PROVEEDOR DE PRUEBA', 'proveedorprueba@ejemplo.com', '2024-10-20 16:39:03', '2024-10-20 17:48:46', 1),
(4, 1, 'PROVEEDOR 3', 'proveedor3@ejemplo.com', '2024-11-03 19:18:28', '2024-11-03 19:18:28', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedordireccion`
--

CREATE TABLE `tbproveedordireccion` (
  `proveedordireccionid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `direccionid` int NOT NULL,
  `proveedordireccionestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbproveedordireccion`
--

INSERT INTO `tbproveedordireccion` (`proveedordireccionid`, `proveedorid`, `direccionid`, `proveedordireccionestado`) VALUES
(1, 1, 7, 1),
(2, 3, 12, 1),
(3, 4, 2, 1),
(4, 4, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedorproducto`
--

CREATE TABLE `tbproveedorproducto` (
  `proveedorproductoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `productoid` int NOT NULL,
  `proveedorproductoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedortelefono`
--

CREATE TABLE `tbproveedortelefono` (
  `proveedortelefonoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `telefonoid` int NOT NULL,
  `proveedortelefonoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbproveedortelefono`
--

INSERT INTO `tbproveedortelefono` (`proveedortelefonoid`, `proveedorid`, `telefonoid`, `proveedortelefonoestado`) VALUES
(1, 1, 1, 0),
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 1, 5, 1),
(5, 1, 6, 1),
(6, 3, 8, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbrolusuario`
--

CREATE TABLE `tbrolusuario` (
  `rolusuarioid` int NOT NULL,
  `rolusuarionombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rolusuariodescripcion` text COLLATE utf8mb4_unicode_ci,
  `rolusuarioestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbrolusuario`
--

INSERT INTO `tbrolusuario` (`rolusuarioid`, `rolusuarionombre`, `rolusuariodescripcion`, `rolusuarioestado`) VALUES
(1, 'Administrador(a)', 'Usuario administrador del local', 1),
(2, 'Dependiente', 'Trabajador encargado de manejar el local', 1),
(3, 'Cajero(a)', 'Dependiente encargado de la caja', 1),
(4, 'Prueba', 'Descripción de prueba', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbsubcategoria`
--

CREATE TABLE `tbsubcategoria` (
  `subcategoriaid` int NOT NULL,
  `categoriaid` int NOT NULL,
  `subcategorianombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subcategoriadescripcion` text COLLATE utf8mb4_unicode_ci,
  `subcategoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbsubcategoria`
--

INSERT INTO `tbsubcategoria` (`subcategoriaid`, `categoriaid`, `subcategorianombre`, `subcategoriadescripcion`, `subcategoriaestado`) VALUES
(1, 3, 'Mobile', '', 1),
(2, 5, 'Photo', '', 1),
(3, 4, 'PRUEBA', 'Subcategoria de prueba', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbtelefono`
--

CREATE TABLE `tbtelefono` (
  `telefonoid` int NOT NULL,
  `telefonotipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefonocodigopais` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefononumero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefonoextension` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefonoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbtelefono`
--

INSERT INTO `tbtelefono` (`telefonoid`, `telefonotipo`, `telefonocodigopais`, `telefononumero`, `telefonoextension`, `telefonoestado`) VALUES
(1, 'Móvil', '+1-809', '257 998 5247', '', 0),
(2, 'Móvil', '+505', '9728 6416', '', 1),
(3, 'Móvil', '+593', '65 588 4412', '', 1),
(5, 'Móvil', '+502', '5972 3158', '', 1),
(6, 'Móvil', '+51', '5679 8524', '', 1),
(7, 'Móvil', '+506', '6421 2950', '', 1),
(8, 'Móvil', '+58', '3970 4685', '', 1),
(9, 'Móvil', '+506', '4731 8950', '', 1),
(10, 'Móvil', '+54', '1234 5678', '', 1),
(11, 'Móvil', '+53', '349 7028 4680', '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbusuario`
--

CREATE TABLE `tbusuario` (
  `usuarioid` int NOT NULL,
  `rolusuarioid` int NOT NULL,
  `usuarionombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuarioapellido1` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuarioapellido2` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuarioemail` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuariopassword` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuariofechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuariofechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usuarioestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbusuario`
--

INSERT INTO `tbusuario` (`usuarioid`, `rolusuarioid`, `usuarionombre`, `usuarioapellido1`, `usuarioapellido2`, `usuarioemail`, `usuariopassword`, `usuariofechacreacion`, `usuariofechamodificacion`, `usuarioestado`) VALUES
(1, 2, 'Isaac', 'Herrera', 'Pastrana', 'isaacmhp2001@gmail.com', '$2y$10$WIq4w2R83lzCkfa9L3NaK.9lZs.OyELxxAC/sqU3Rl4sxzlJboxgm', '2024-09-15 23:17:43', '2024-10-09 20:08:09', 0),
(2, 1, 'Admin', 'Adminson', 'Adminsen', 'admin@admin.com', '$2y$10$HzXMgCvzRJdx1k9dPniUvuZectQkf4UV6wYt4M5lOk.4vi9kqEXoC', '2024-09-16 17:49:31', '2024-10-03 20:22:29', 1),
(3, 3, 'Prueba', 'Primero', 'Segundo', 'cajero@gmail.com', '$2y$10$jXNkOrLG49.wJdSf3DDrWuOfden5BzWj8NkU9I15OxoYYNUbwGa1.', '2024-10-03 20:23:56', '2024-10-03 20:47:29', 0),
(4, 2, 'Dependiente', 'Dependant', 'Dependansen', 'dependiente@dependiente.com', '$2y$10$.Zv7MIUusPu0or0vhVOTEOLGzDO3YB05z/HezBf1HVMY32pl9lGei', '2024-10-09 20:09:02', '2024-10-09 20:09:02', 1),
(5, 4, 'Cajero', 'Cajerson', 'Cajersen', 'cajero@cajero.com', '$2y$10$bbRGwU2vAzUg.Qmeex1b..7ppgkAWI.qOqUKwPfj4iES7G2pwRbsu', '2024-10-09 20:09:46', '2024-10-09 20:09:46', 1),
(6, 1, 'Natalia', 'Ortiz', 'Martinez', 'natortiz@ejemplo.com', '$2y$10$FNEa.x2Nm82JBI3TzHp6rO9wLCeGU9yYmKpxjT.LjdjJ/F9kU49k6', '2024-10-26 19:12:11', '2024-10-31 13:59:46', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbventa`
--

CREATE TABLE `tbventa` (
  `ventaid` int NOT NULL,
  `clienteid` int NOT NULL,
  `ventanumerofactura` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ventamoneda` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ventamontobruto` decimal(10,2) NOT NULL,
  `ventamontoneto` decimal(10,2) NOT NULL,
  `ventamontoimpuesto` decimal(10,2) NOT NULL,
  `ventacondicionventa` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Crédito o Contado',
  `ventatipopago` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Efectivo, Tarjeta, Sinpe',
  `ventafechacreacion` datetime NOT NULL DEFAULT (now()),
  `ventafechamodificacion` datetime NOT NULL DEFAULT (now()),
  `ventaestado` tinyint NOT NULL DEFAULT (_utf8mb4'1')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbventa`
--

INSERT INTO `tbventa` (`ventaid`, `clienteid`, `ventanumerofactura`, `ventamoneda`, `ventamontobruto`, `ventamontoneto`, `ventamontoimpuesto`, `ventacondicionventa`, `ventatipopago`, `ventafechacreacion`, `ventafechamodificacion`, `ventaestado`) VALUES
(1, 1, '921547364850236', 'CRC', 1500.00, 1695.00, 195.00, 'CREDITO', 'EFECTIVO', '2024-10-29 00:00:00', '2024-11-03 00:00:00', 1),
(2, 1, '921547364850237', 'CRC', 3100.00, 3503.00, 403.00, 'CREDITO', 'EFECTIVO', '2024-10-29 00:00:00', '2024-11-03 00:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbventadetalle`
--

CREATE TABLE `tbventadetalle` (
  `ventadetalleid` int NOT NULL,
  `ventaid` int NOT NULL,
  `ventadetalleprecio` decimal(10,2) NOT NULL,
  `ventadetallecantidad` int NOT NULL,
  `ventadetalleestado` tinyint NOT NULL DEFAULT (_utf8mb4'1')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbventadetalleproducto`
--

CREATE TABLE `tbventadetalleproducto` (
  `ventadetalleproductoid` int NOT NULL,
  `ventadetalleid` int NOT NULL,
  `productoid` int NOT NULL,
  `ventadetalleproductoestado` tinyint NOT NULL DEFAULT '1',
  `ventadetalleproductocantidad` int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0000000001'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla intermedia para los porductos de cada venta';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbventaporcobrar`
--

CREATE TABLE `tbventaporcobrar` (
  `ventaporcobrarid` int NOT NULL,
  `ventaid` int NOT NULL,
  `ventaporcobrarfechavencimiento` date NOT NULL,
  `ventaporcobrarcancelado` tinyint NOT NULL DEFAULT (_utf8mb4'0'),
  `ventaporcobrarnotas` text COLLATE utf8mb4_unicode_ci,
  `ventaporcobrarestado` tinyint NOT NULL DEFAULT (_utf8mb4'1')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbventaporcobrar`
--

INSERT INTO `tbventaporcobrar` (`ventaporcobrarid`, `ventaid`, `ventaporcobrarfechavencimiento`, `ventaporcobrarcancelado`, `ventaporcobrarnotas`, `ventaporcobrarestado`) VALUES
(1, 1, '2024-11-20', 0, '', 1),
(2, 2, '2024-11-25', 0, ' ', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tbcategoria`
--
ALTER TABLE `tbcategoria`
  ADD PRIMARY KEY (`categoriaid`);

--
-- Indices de la tabla `tbcliente`
--
ALTER TABLE `tbcliente`
  ADD PRIMARY KEY (`clienteid`);

--
-- Indices de la tabla `tbcodigobarras`
--
ALTER TABLE `tbcodigobarras`
  ADD PRIMARY KEY (`codigobarrasid`);

--
-- Indices de la tabla `tbcompra`
--
ALTER TABLE `tbcompra`
  ADD PRIMARY KEY (`compraid`);

--
-- Indices de la tabla `tbcompradetalle`
--
ALTER TABLE `tbcompradetalle`
  ADD PRIMARY KEY (`compradetalleid`);

--
-- Indices de la tabla `tbcompraporpagar`
--
ALTER TABLE `tbcompraporpagar`
  ADD PRIMARY KEY (`compraporpagarid`);

--
-- Indices de la tabla `tbdireccion`
--
ALTER TABLE `tbdireccion`
  ADD PRIMARY KEY (`direccionid`);

--
-- Indices de la tabla `tbimpuesto`
--
ALTER TABLE `tbimpuesto`
  ADD PRIMARY KEY (`impuestoid`);

--
-- Indices de la tabla `tbmarca`
--
ALTER TABLE `tbmarca`
  ADD PRIMARY KEY (`marcaid`);

--
-- Indices de la tabla `tbpresentacion`
--
ALTER TABLE `tbpresentacion`
  ADD PRIMARY KEY (`presentacionid`);

--
-- Indices de la tabla `tbproducto`
--
ALTER TABLE `tbproducto`
  ADD PRIMARY KEY (`productoid`);

--
-- Indices de la tabla `tbproveedor`
--
ALTER TABLE `tbproveedor`
  ADD PRIMARY KEY (`proveedorid`);

--
-- Indices de la tabla `tbproveedordireccion`
--
ALTER TABLE `tbproveedordireccion`
  ADD PRIMARY KEY (`proveedordireccionid`);

--
-- Indices de la tabla `tbproveedorproducto`
--
ALTER TABLE `tbproveedorproducto`
  ADD PRIMARY KEY (`proveedorproductoid`);

--
-- Indices de la tabla `tbproveedortelefono`
--
ALTER TABLE `tbproveedortelefono`
  ADD PRIMARY KEY (`proveedortelefonoid`);

--
-- Indices de la tabla `tbrolusuario`
--
ALTER TABLE `tbrolusuario`
  ADD PRIMARY KEY (`rolusuarioid`);

--
-- Indices de la tabla `tbsubcategoria`
--
ALTER TABLE `tbsubcategoria`
  ADD PRIMARY KEY (`subcategoriaid`);

--
-- Indices de la tabla `tbtelefono`
--
ALTER TABLE `tbtelefono`
  ADD PRIMARY KEY (`telefonoid`);

--
-- Indices de la tabla `tbusuario`
--
ALTER TABLE `tbusuario`
  ADD PRIMARY KEY (`usuarioid`);

--
-- Indices de la tabla `tbventa`
--
ALTER TABLE `tbventa`
  ADD PRIMARY KEY (`ventaid`);

--
-- Indices de la tabla `tbventadetalle`
--
ALTER TABLE `tbventadetalle`
  ADD PRIMARY KEY (`ventadetalleid`);

--
-- Indices de la tabla `tbventadetalleproducto`
--
ALTER TABLE `tbventadetalleproducto`
  ADD PRIMARY KEY (`ventadetalleproductoid`);

--
-- Indices de la tabla `tbventaporcobrar`
--
ALTER TABLE `tbventaporcobrar`
  ADD PRIMARY KEY (`ventaporcobrarid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
