-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 07-09-2024 a las 15:36:46
-- Versión del servidor: 8.0.39-0ubuntu0.22.04.1
-- Versión de PHP: 8.1.2-1ubuntu2.18

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

DROP TABLE IF EXISTS `tbcategoria`;
CREATE TABLE `tbcategoria` (
  `categoriaid` int NOT NULL,
  `categorianombre` varchar(100) NOT NULL,
  `categoriadescripcion` text,
  `categoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcodigobarras`
--

DROP TABLE IF EXISTS `tbcodigobarras`;
CREATE TABLE `tbcodigobarras` (
  `codigobarrasid` int NOT NULL,
  `codigobarrasnumero` int NOT NULL,
  `codigobarrasfechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codigobarrasfechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `codigobarrasestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcompra`
--

DROP TABLE IF EXISTS `tbcompra`;
CREATE TABLE `tbcompra` (
  `compraid` int NOT NULL,
  `compranumerofactura` varchar(255) NOT NULL,
  `compramontobruto` decimal(10,2) NOT NULL,
  `compramontoneto` decimal(10,2) NOT NULL,
  `compratipopago` varchar(255) NOT NULL,
  `compraproveedorid` int NOT NULL,
  `comprafechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comprafechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `compraestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcompradetalle`
--

DROP TABLE IF EXISTS `tbcompradetalle`;
CREATE TABLE `tbcompradetalle` (
  `compradetalleid` int NOT NULL,
  `compradetallecompraid` int NOT NULL,
  `compradetalleloteid` int NOT NULL,
  `compradetalleproductoid` int NOT NULL,
  `compradetalleprecioproducto` decimal(10,2) NOT NULL,
  `compradetallecantidad` int NOT NULL,
  `compradetallefechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `compradetallefechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `compradetalleestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcuentaporpagar`
--

DROP TABLE IF EXISTS `tbcuentaporpagar`;
CREATE TABLE `tbcuentaporpagar` (
  `cuentaporpagarid` int NOT NULL,
  `cuentaporpagarcompradetalleid` int NOT NULL,
  `cuentaporpagarfechavencimiento` date NOT NULL,
  `cuentaporpagarmontototal` decimal(10,2) NOT NULL,
  `cuentaporpagarmontopagado` decimal(10,2) NOT NULL,
  `cuentaporpagarfechapago` date NOT NULL,
  `cuentaporpagarnotas` text,
  `cuentaporpagarestadocuenta` varchar(255) NOT NULL DEFAULT 'Pendiente',
  `cuentaporpagarestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbdireccion`
--

DROP TABLE IF EXISTS `tbdireccion`;
CREATE TABLE `tbdireccion` (
  `direccionid` int NOT NULL,
  `direccionprovincia` varchar(100) NOT NULL,
  `direccioncanton` varchar(100) NOT NULL,
  `direcciondistrito` varchar(100) NOT NULL,
  `direccionbarrio` varchar(100) DEFAULT NULL,
  `direccionsennas` text,
  `direcciondistancia` decimal(5,2) NOT NULL,
  `direccionestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbimpuesto`
--

DROP TABLE IF EXISTS `tbimpuesto`;
CREATE TABLE `tbimpuesto` (
  `impuestoid` int NOT NULL,
  `impuestonombre` varchar(100) NOT NULL,
  `impuestovalor` decimal(5,2) NOT NULL,
  `impuestodescripcion` text,
  `impuestofechavigencia` date NOT NULL,
  `impuestoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `tbimpuesto`
--

INSERT INTO `tbimpuesto` (`impuestoid`, `impuestonombre`, `impuestovalor`, `impuestodescripcion`, `impuestofechavigencia`, `impuestoestado`) VALUES
(1, 'IVA', 13.00, '', '2024-08-01', 1),
(2, 'IFJ', 10.00, '', '2024-08-20', 0),
(3, 'IFK', 13.00, '', '2024-08-20', 0),
(4, 'IJK', 20.00, '', '2024-09-04', 0),
(5, 'MELI956124', 100.00, '.', '2024-09-03', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblote`
--

DROP TABLE IF EXISTS `tblote`;
CREATE TABLE `tblote` (
  `loteid` int NOT NULL,
  `lotecodigo` varchar(50) NOT NULL,
  `lotefechavencimiento` date NOT NULL,
  `loteestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `tblote`
--

INSERT INTO `tblote` (`loteid`, `lotecodigo`, `lotefechavencimiento`, `loteestado`) VALUES
(1, 'prueba123', '2024-09-15', 1),
(2, 'comoarroz', '2024-09-14', 0),
(3, '1111', '2024-09-14', 0),
(4, '11111', '2024-09-05', 1),
(5, 'qwe', '2024-09-06', 1),
(6, 'viaje', '2024-09-28', 1),
(7, 'hola', '2024-09-06', 1),
(8, 'holhola', '2024-09-06', 1),
(9, 'FKJ123', '2024-09-30', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproducto`
--

DROP TABLE IF EXISTS `tbproducto`;
CREATE TABLE `tbproducto` (
  `productoid` int NOT NULL,
  `productonombre` varchar(100) NOT NULL,
  `productopreciocompra` decimal(10,2) NOT NULL,
  `productoporcentajeganancia` decimal(10,2) NOT NULL,
  `productodescripcion` text,
  `productocodigobarrasid` int NOT NULL,
  `productoimagen` text,
  `productoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproductocategoria`
--

DROP TABLE IF EXISTS `tbproductocategoria`;
CREATE TABLE `tbproductocategoria` (
  `productocategoriaid` int NOT NULL,
  `productoid` int NOT NULL,
  `categoriaid` int NOT NULL,
  `productocategoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproductosubcategoria`
--

DROP TABLE IF EXISTS `tbproductosubcategoria`;
CREATE TABLE `tbproductosubcategoria` (
  `productosubcategoriaid` int NOT NULL,
  `productoid` int NOT NULL,
  `subcategoriaid` int NOT NULL,
  `productosubcategoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedor`
--

DROP TABLE IF EXISTS `tbproveedor`;
CREATE TABLE `tbproveedor` (
  `proveedorid` int NOT NULL,
  `proveedornombre` varchar(100) NOT NULL,
  `proveedoremail` varchar(100) NOT NULL,
  `proveedorfecharegistro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proveedorestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `tbproveedor`
--

INSERT INTO `tbproveedor` (`proveedorid`, `proveedornombre`, `proveedoremail`, `proveedorfecharegistro`, `proveedorestado`) VALUES
(1, 'Proveedor A', 'proveedorA@example.com', '2024-09-06 18:11:45', 1),
(2, 'Proveedor B', 'proveedorB@example.com', '2024-09-06 18:11:45', 1),
(3, 'Proveedor C', 'proveedorC@example.com', '2024-09-06 18:11:45', 1),
(4, 'Proveedor D', 'proveedorD@example.com', '2024-09-06 18:11:45', 1),
(5, 'Proveedor E', 'proveedorE@example.com', '2024-09-06 18:11:45', 1),
(6, 'Proveedor F', 'proveedorF@example.com', '2024-09-06 18:11:45', 1),
(7, 'Proveedor G', 'proveedorG@example.com', '2024-09-06 18:11:45', 1),
(8, 'Proveedor H', 'proveedorH@example.com', '2024-09-06 18:11:45', 1),
(9, 'Proveedor I', 'proveedorI@example.com', '2024-09-06 18:11:45', 1),
(10, 'Proveedor J', 'proveedorJ@example.com', '2024-09-06 18:11:45', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedorcategoria`
--

DROP TABLE IF EXISTS `tbproveedorcategoria`;
CREATE TABLE `tbproveedorcategoria` (
  `proveedorcategoriaid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `categoriaid` int NOT NULL,
  `proveedorcategoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedordireccion`
--

DROP TABLE IF EXISTS `tbproveedordireccion`;
CREATE TABLE `tbproveedordireccion` (
  `proveedordireccionid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `direccionid` int NOT NULL,
  `proveedordireccionestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedorproducto`
--

DROP TABLE IF EXISTS `tbproveedorproducto`;
CREATE TABLE `tbproveedorproducto` (
  `provedorproductoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `productoid` int NOT NULL,
  `proveedorproductoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedortelefono`
--

DROP TABLE IF EXISTS `tbproveedortelefono`;
CREATE TABLE `tbproveedortelefono` (
  `proveedortelefonoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `telefonoid` int NOT NULL,
  `proveedortelefonoestado` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `tbproveedortelefono`
--

INSERT INTO `tbproveedortelefono` (`proveedortelefonoid`, `proveedorid`, `telefonoid`, `proveedortelefonoestado`) VALUES
(1, 1, 1, 0),
(2, 2, 2, 0),
(3, 2, 3, 0),
(4, 2, 4, 1),
(5, 5, 5, 1),
(6, 6, 6, 1),
(7, 7, 7, 1),
(8, 8, 8, 1),
(9, 9, 9, 1),
(10, 10, 10, 1),
(11, 10, 11, 0),
(12, 10, 12, 1),
(13, 10, 13, 0),
(14, 4, 13, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbrol`
--

DROP TABLE IF EXISTS `tbrol`;
CREATE TABLE `tbrol` (
  `rolid` int NOT NULL,
  `rolnombre` varchar(255) NOT NULL,
  `roldescripcion` varchar(255) DEFAULT NULL,
  `rolestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbsubcategoria`
--

DROP TABLE IF EXISTS `tbsubcategoria`;
CREATE TABLE `tbsubcategoria` (
  `subcategoriaid` int NOT NULL,
  `subcategorianombre` varchar(100) NOT NULL,
  `subcategoriadescripcion` text,
  `subcategoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbtelefono`
--

DROP TABLE IF EXISTS `tbtelefono`;
CREATE TABLE `tbtelefono` (
  `telefonoid` int NOT NULL,
  `telefonotipo` varchar(50) NOT NULL,
  `telefonocodigopais` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `telefononumero` varchar(20) NOT NULL,
  `telefonoextension` varchar(10) DEFAULT NULL,
  `telefonofechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `telefonofechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `telefonoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `tbtelefono`
--

INSERT INTO `tbtelefono` (`telefonoid`, `telefonotipo`, `telefonocodigopais`, `telefononumero`, `telefonoextension`, `telefonofechacreacion`, `telefonofechamodificacion`, `telefonoestado`) VALUES
(1, 'Fax', '+505', '6421 2950', '', '2024-09-05 00:32:16', '2024-09-05 01:31:35', 0),
(2, 'Móvil', '+506', '2764 4311', '', '2024-09-05 00:41:25', '2024-09-05 00:42:33', 0),
(3, 'Móvil', '+506', '6397 3489', '', '2024-09-05 01:31:05', '2024-09-06 19:34:23', 0),
(4, 'Fijo', '+502', '5550 1039', '124', '2024-09-06 18:12:58', '2024-09-07 03:21:39', 1),
(5, 'Móvil', '+1-809', '555 010 4972', '', '2024-09-06 18:12:58', '2024-09-07 03:25:09', 1),
(6, 'Fijo', '+53', '555 0105 9657', '125', '2024-09-06 18:12:58', '2024-09-07 03:30:08', 1),
(7, 'Móvil', '+57', '555 010 6974', '', '2024-09-06 18:12:58', '2024-09-07 03:30:24', 1),
(8, 'Fijo', '+52', '55 5010 7973', '126', '2024-09-06 18:12:58', '2024-09-07 03:30:44', 1),
(9, 'Móvil', '+58', '5550 1087', '', '2024-09-06 18:12:58', '2024-09-07 03:31:03', 1),
(10, 'Fijo', '+506', '5550 1099', '127', '2024-09-06 18:12:58', '2024-09-07 03:21:13', 1),
(11, 'Móvil', '+506', '6397 3489', '', '2024-09-06 19:33:30', '2024-09-06 23:19:12', 0),
(12, 'Móvil', '+506', '8888 8888', '', '2024-09-07 02:35:16', '2024-09-07 02:35:16', 1),
(13, 'Móvil', '+506', '8881 8881', '', '2024-09-07 02:55:59', '2024-09-07 03:31:35', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbusuario`
--

DROP TABLE IF EXISTS `tbusuario`;
CREATE TABLE `tbusuario` (
  `usuarioid` int NOT NULL,
  `usuarionombre` varchar(255) NOT NULL,
  `usuarioprimerapellido` varchar(255) NOT NULL,
  `usuariosegundoapellido` varchar(255) NOT NULL,
  `usuariorolid` int NOT NULL,
  `usuarioemail` varchar(255) NOT NULL,
  `usuariopassword` varchar(255) NOT NULL,
  `usuarionickname` varchar(255) NOT NULL,
  `usuariofechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuariofechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usuarioestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tbcategoria`
--
ALTER TABLE `tbcategoria`
  ADD PRIMARY KEY (`categoriaid`);

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
-- Indices de la tabla `tbcuentaporpagar`
--
ALTER TABLE `tbcuentaporpagar`
  ADD PRIMARY KEY (`cuentaporpagarid`);

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
-- Indices de la tabla `tblote`
--
ALTER TABLE `tblote`
  ADD PRIMARY KEY (`loteid`);

--
-- Indices de la tabla `tbproducto`
--
ALTER TABLE `tbproducto`
  ADD PRIMARY KEY (`productoid`);

--
-- Indices de la tabla `tbproductocategoria`
--
ALTER TABLE `tbproductocategoria`
  ADD PRIMARY KEY (`productocategoriaid`);

--
-- Indices de la tabla `tbproductosubcategoria`
--
ALTER TABLE `tbproductosubcategoria`
  ADD PRIMARY KEY (`productosubcategoriaid`);

--
-- Indices de la tabla `tbproveedor`
--
ALTER TABLE `tbproveedor`
  ADD PRIMARY KEY (`proveedorid`);

--
-- Indices de la tabla `tbproveedorcategoria`
--
ALTER TABLE `tbproveedorcategoria`
  ADD PRIMARY KEY (`proveedorcategoriaid`);

--
-- Indices de la tabla `tbproveedordireccion`
--
ALTER TABLE `tbproveedordireccion`
  ADD PRIMARY KEY (`proveedordireccionid`);

--
-- Indices de la tabla `tbproveedorproducto`
--
ALTER TABLE `tbproveedorproducto`
  ADD PRIMARY KEY (`provedorproductoid`);

--
-- Indices de la tabla `tbproveedortelefono`
--
ALTER TABLE `tbproveedortelefono`
  ADD PRIMARY KEY (`proveedortelefonoid`);

--
-- Indices de la tabla `tbrol`
--
ALTER TABLE `tbrol`
  ADD PRIMARY KEY (`rolid`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
