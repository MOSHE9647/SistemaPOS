-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 08-09-2024 a las 20:13:08
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

--
-- Volcado de datos para la tabla `tbdireccion`
--

INSERT INTO `tbdireccion` (`direccionid`, `direccionprovincia`, `direccioncanton`, `direcciondistrito`, `direccionbarrio`, `direccionsennas`, `direcciondistancia`, `direccionestado`) VALUES
(1, 'Heredia', 'Sarapiquí', 'Horquetas', 'Urb Miraflores', 'Casa #37', 3.00, 1),
(2, 'Alajuela', 'Zarcero', 'Guadalupe', 'Escalante', 'Casa #26', 20.00, 1),
(3, 'Alajuela', 'San Carlos', 'Aguas Zarcas', 'Cascadia', '', 20.00, 0),
(4, 'Guanacaste', 'Abangares', 'Sierra', 'Sierra', 'Casa #32', 5.00, 1);

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
(5, 'MELI956124', 100.00, '.', '2024-09-03', 0),
(6, 'IRF', 13.00, '', '2024-08-01', 1),
(7, 'IVM', 5.00, '', '2024-09-07', 1),
(8, 'IPJ', 6.00, '', '2024-09-04', 1);

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

--
-- Volcado de datos para la tabla `tbproducto`
--

INSERT INTO `tbproducto` (`productoid`, `productonombre`, `productopreciocompra`, `productoporcentajeganancia`, `productodescripcion`, `productocodigobarrasid`, `productoimagen`, `productoestado`) VALUES
(1, 'Laptop', 800.00, 20.00, 'Laptop de alta gama', 1001, '/images/productos/electronics/1_laptop.jpg', 1),
(2, 'Smartphone', 400.00, 25.00, 'Smartphone con excelente cámara', 1002, '/images/productos/electronics/2_smartphone.jpg', 1),
(3, 'Headphones', 50.00, 30.00, 'Auriculares con cancelación de ruido', 1003, '/images/productos/electronics/3_headphones.jpg', 1),
(4, 'Smartwatch', 150.00, 15.00, 'Reloj inteligente resistente al agua', 1004, '/images/productos/electronics/4_smartwatch.jpg', 1),
(5, 'Tablet', 250.00, 22.00, 'Tablet de alta resolución', 1005, '/images/productos/electronics/5_tablet.jpg', 1),
(6, 'Camera', 600.00, 18.00, 'Cámara digital profesional', 1006, '/images/productos/electronics/6_camera.jpg', 1),
(7, 'Printer', 120.00, 20.00, 'Impresora multifuncional', 1007, '/images/productos/electronics/7_printer.jpg', 1),
(8, 'Monitor', 200.00, 25.00, 'Monitor de 27 pulgadas', 1008, '/images/productos/electronics/8_monitor.jpg', 1),
(9, 'Keyboard', 30.00, 35.00, 'Teclado mecánico retroiluminado', 1009, '/images/productos/electronics/9_keyboard.jpg', 1),
(10, 'Mouse', 20.00, 40.00, 'Mouse ergonómico', 1010, '/images/productos/electronics/10_mouse.jpg', 1);

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

--
-- Volcado de datos para la tabla `tbproductosubcategoria`
--

INSERT INTO `tbproductosubcategoria` (`productosubcategoriaid`, `productoid`, `subcategoriaid`, `productosubcategoriaestado`) VALUES
(1, 1, 6, 1),
(2, 1, 7, 0),
(3, 1, 8, 1);

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

--
-- Volcado de datos para la tabla `tbproveedordireccion`
--

INSERT INTO `tbproveedordireccion` (`proveedordireccionid`, `proveedorid`, `direccionid`, `proveedordireccionestado`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 1, 3, 0),
(4, 1, 4, 1);

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
(1, 1, 1, 1),
(2, 1, 2, 0);

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

--
-- Volcado de datos para la tabla `tbsubcategoria`
--

INSERT INTO `tbsubcategoria` (`subcategoriaid`, `subcategorianombre`, `subcategoriadescripcion`, `subcategoriaestado`) VALUES
(1, 'Electrónica', 'Productos electrónicos', 1),
(2, 'Computadoras', 'Equipos de computación', 1),
(3, 'Accessorios', 'Accesorios de electrónica', 1),
(4, 'Wearables', 'Dispositivos ponibles', 1),
(5, 'Cámaras', 'Cámaras digitales', 0),
(6, 'Impresoras', 'Impresoras y escáneres', 1),
(7, 'Pantallas', 'Monitores y pantallas', 0),
(8, 'Dispositivos de Entrada', 'Dispositivos de entrada', 1),
(9, 'Dispositivos Móviles', 'Dispositivos móviles', 1),
(10, 'Electrodomésticos', 'Electrodomésticos', 1);

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
(1, 'Móvil', '+1-809', '257 998 5247', '', '2024-09-07 22:11:19', '2024-09-07 22:11:19', 1),
(2, 'Móvil', '+503', '9728 6416', '', '2024-09-07 22:11:51', '2024-09-07 22:12:25', 0);

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
