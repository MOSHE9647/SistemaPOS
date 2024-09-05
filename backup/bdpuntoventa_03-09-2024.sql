-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Servidor: bdpbhgi0jbzwpoftwisg-mysql.services.clever-cloud.com
-- Tiempo de generación: 05-09-2024 a las 07:03:55
-- Versión del servidor: 8.0.22-13
-- Versión de PHP: 7.0.33-0ubuntu0.16.04.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bdpbhgi0jbzwpoftwisg`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcategoria`
--

CREATE TABLE `tbcategoria` (
  `categoriaid` int NOT NULL,
  `categorianombre` varchar(100) NOT NULL,
  `categoriadescripcion` text,
  `categoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcodigobarras`
--

CREATE TABLE `tbcodigobarras` (
  `codigobarrasid` int NOT NULL,
  `codigobarrasnumero` int NOT NULL,
  `codigobarrasfechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codigobarrasfechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `codigobarrasestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcompra`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcompradetalle`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcuentaporpagar`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbdireccion`
--

CREATE TABLE `tbdireccion` (
  `direccionid` int NOT NULL,
  `direccionprovincia` varchar(100) NOT NULL,
  `direccioncanton` varchar(100) NOT NULL,
  `direcciondistrito` varchar(100) NOT NULL,
  `direccionbarrio` varchar(100) DEFAULT NULL,
  `direccionsennas` text,
  `direcciondistancia` decimal(5,2) NOT NULL,
  `direccionestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbimpuesto`
--

CREATE TABLE `tbimpuesto` (
  `impuestoid` int NOT NULL,
  `impuestonombre` varchar(100) NOT NULL,
  `impuestovalor` decimal(5,2) NOT NULL,
  `impuestodescripcion` text,
  `impuestofechavigencia` date NOT NULL,
  `impuestoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbimpuesto`
--

INSERT INTO `tbimpuesto` (`impuestoid`, `impuestonombre`, `impuestovalor`, `impuestodescripcion`, `impuestofechavigencia`, `impuestoestado`) VALUES
(1, 'IVA', '15.00', 'Impuesto al Valor Agregado', '2024-09-02', 0),
(2, 'IVA', '13.00', 'Impuesto al Valor Agregado', '2024-09-02', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblote`
--

CREATE TABLE `tblote` (
  `loteid` int NOT NULL,
  `lotecodigo` varchar(50) NOT NULL,
  `lotefechavencimiento` date NOT NULL,
  `loteestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tblote`
--

INSERT INTO `tblote` (`loteid`, `lotecodigo`, `lotefechavencimiento`, `loteestado`) VALUES
(1, 'prueba123', '2024-09-15', 1),
(2, 'comoarroz', '2024-09-14', 0),
(3, '1111', '2024-09-14', 1),
(4, '11111', '2024-09-05', 1),
(5, 'qwe', '2024-09-06', 1),
(6, 'viaje', '2024-09-28', 1),
(7, 'hola', '2024-09-06', 1),
(8, 'holhola', '2024-09-06', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproducto`
--

CREATE TABLE `tbproducto` (
  `productoid` int NOT NULL,
  `productonombre` varchar(100) NOT NULL,
  `productopreciocompra` decimal(10,2) NOT NULL,
  `productoporcentajeganancia` decimal(10,2) NOT NULL,
  `productodescripcion` text,
  `productocodigobarrasid` int NOT NULL,
  `productoimagen` text,
  `productoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproductocategoria`
--

CREATE TABLE `tbproductocategoria` (
  `productocategoriaid` int NOT NULL,
  `productoid` int NOT NULL,
  `categoriaid` int NOT NULL,
  `productocategoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproductosubcategoria`
--

CREATE TABLE `tbproductosubcategoria` (
  `productosubcategoriaid` int NOT NULL,
  `productoid` int NOT NULL,
  `subcategoriaid` int NOT NULL,
  `productosubcategoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedor`
--

CREATE TABLE `tbproveedor` (
  `proveedorid` int NOT NULL,
  `proveedornombre` varchar(100) NOT NULL,
  `proveedoremail` varchar(100) NOT NULL,
  `proveedorfecharegistro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proveedorestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedorcategoria`
--

CREATE TABLE `tbproveedorcategoria` (
  `proveedorcategoriaid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `categoriaid` int NOT NULL,
  `proveedorcategoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedordireccion`
--

CREATE TABLE `tbproveedordireccion` (
  `proveedordireccionid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `direccionid` int NOT NULL,
  `proveedordireccionestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedorproducto`
--

CREATE TABLE `tbproveedorproducto` (
  `provedorproductoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `productoid` int NOT NULL,
  `proveedorproductoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbrol`
--

CREATE TABLE `tbrol` (
  `rolid` int NOT NULL,
  `rolnombre` varchar(255) NOT NULL,
  `roldescripcion` varchar(255) DEFAULT NULL,
  `rolestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbsubcategoria`
--

CREATE TABLE `tbsubcategoria` (
  `subcategoriaid` int NOT NULL,
  `subcategorianombre` varchar(100) NOT NULL,
  `subcategoriadescripcion` text,
  `subcategoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbtelefono`
--

CREATE TABLE `tbtelefono` (
  `telefonoid` int NOT NULL,
  `telefonoproveedorid` int NOT NULL,
  `telefonotipo` varchar(50) NOT NULL,
  `telefonocodigopais` varchar(5) NOT NULL,
  `telefononumero` varchar(20) NOT NULL,
  `telefonoextension` varchar(10) DEFAULT NULL,
  `telefonofechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `telefonofechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `telefonoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbusuario`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
