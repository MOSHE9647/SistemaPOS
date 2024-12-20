-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 08-10-2024 a las 15:53:00
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
  `categorianombre` varchar(100) NOT NULL,
  `categoriadescripcion` text,
  `categoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(10, 'Audio & Headphones', 'Audio y auriculares', 1),

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcliente`
--

CREATE TABLE `tbcliente` (
  `clienteid` int NOT NULL,
  `clientenombre` varchar(100) DEFAULT NULL,
  `clientetelefonoid` int NOT NULL,
  `clientefechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clientefechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `clienteestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbcliente`
--

INSERT INTO `tbcliente` (`clienteid`, `clientenombre`, `clientetelefonoid`, `clientefechacreacion`, `clientefechamodificacion`, `clienteestado`) VALUES
(1, 'Prueba', 1, '2024-09-22 16:17:10', '2024-09-24 00:17:47', 0),
(2, 'Pancho Escamilla', 2, '2024-09-22 17:41:52', '2024-09-23 21:37:08', 1),
(3, 'Isaac Herrera', 8, '2024-09-23 23:00:13', '2024-09-24 00:17:38', 1),
(4, 'Cliente 2', 9, '2024-09-24 00:18:05', '2024-10-03 00:52:09', 1),
(5, 'Prueba 1', 10, '2024-10-02 23:06:32', '2024-10-03 00:52:39', 1),
(6, 'Cliente 3', 11, '2024-10-03 00:56:06', '2024-10-03 00:56:06', 1),
(7, 'Cliente 4', 12, '2024-10-03 00:58:07', '2024-10-03 01:02:02', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcodigobarras`
--

CREATE TABLE `tbcodigobarras` (
  `codigobarrasid` int NOT NULL,
  `codigobarrasnumero` varchar(100) NOT NULL,
  `codigobarrasfechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codigobarrasfechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `codigobarrasestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbcodigobarras`
--

INSERT INTO `tbcodigobarras` (`codigobarrasid`, `codigobarrasnumero`, `codigobarrasfechacreacion`, `codigobarrasfechamodificacion`, `codigobarrasestado`) VALUES
(1, '1234567890128', '2024-10-03 22:13:55', '2024-10-06 15:44:25', 0),
(2, '1234567890123', '2024-10-03 22:22:48', '2024-10-03 22:23:14', 0),
(3, '1236547890321', '2024-10-04 06:52:12', '2024-10-04 06:52:12', 1),
(4, '7847084292012', '2024-10-04 07:28:57', '2024-10-06 14:38:47', 0),
(5, '5824693680586', '2024-10-06 14:53:24', '2024-10-06 14:53:24', 1),
(6, '6017266234999', '2024-10-06 14:59:31', '2024-10-06 14:59:31', 1),
(7, '2020076144390', '2024-10-06 15:35:23', '2024-10-06 18:49:21', 1),
(8, '4317402010985', '2024-10-06 19:15:28', '2024-10-06 19:35:40', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcompra`
--

CREATE TABLE `tbcompra` (
  `compraid` int NOT NULL,
  `compranumerofactura` varchar(100) NOT NULL,
  `compramontobruto` decimal(10,2) NOT NULL,
  `compramontoneto` decimal(10,2) NOT NULL,
  `compratipopago` varchar(50) NOT NULL,
  `compraproveedorid` int NOT NULL,
  `comprafechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comprafechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `compraestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `cuentaporpagarestadocuenta` varchar(50) NOT NULL DEFAULT 'Pendiente',
  `cuentaporpagarnotas` text,
  `cuentaporpagarestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbdireccion`
--

CREATE TABLE `tbdireccion` (
  `direccionid` int NOT NULL,
  `direccionprovincia` varchar(100) NOT NULL,
  `direccioncanton` varchar(100) NOT NULL,
  `direcciondistrito` varchar(100) NOT NULL,
  `direccionbarrio` varchar(100) DEFAULT '',
  `direccionsennas` text,
  `direcciondistancia` decimal(5,2) NOT NULL,
  `direccionestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbdireccion`
--

INSERT INTO `tbdireccion` (`direccionid`, `direccionprovincia`, `direccioncanton`, `direcciondistrito`, `direccionbarrio`, `direccionsennas`, `direcciondistancia`, `direccionestado`) VALUES
(1, 'Heredia', 'Sarapiquí', 'Horquetas', 'Urb Miraflores', 'Casa #37', 3.00, 1),
(2, 'Alajuela', 'Zarcero', 'Guadalupe', 'Escalante', 'Casa #26', 20.00, 1),
(3, 'Alajuela', 'San Carlos', 'Aguas Zarcas', 'Cascadia', '', 20.00, 0),
(4, 'Guanacaste', 'Abangares', 'Sierra', 'Sierra', 'Casa #32', 5.00, 1),
(5, 'Heredia', 'Santo Domingo', 'Para', '', '', 20.59, 1),
(6, 'CARTAGO', 'TURRIALBA', 'TURRIALBA', '', '', 15.00, 0),
(7, 'HEREDIA', 'SAN PABLO', 'SAN PABLO', '', '', 20.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbimpuesto`
--

CREATE TABLE `tbimpuesto` (
  `impuestoid` int NOT NULL,
  `impuestonombre` varchar(100) NOT NULL,
  `impuestovalor` decimal(5,2) NOT NULL,
  `impuestodescripcion` text,
  `impuestofechainiciovigencia` date NOT NULL,
  `impuestofechafinvigencia` date NOT NULL,
  `impuestoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Estructura de tabla para la tabla `tblote`
--

CREATE TABLE `tblote` (
  `loteid` int NOT NULL,
  `lotecodigo` varchar(100) NOT NULL,
  `lotefechavencimiento` date NOT NULL,
  `loteestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Estructura de tabla para la tabla `tbmarca`
--

CREATE TABLE `tbmarca` (
  `marcaid` int NOT NULL,
  `marcanombre` varchar(100) NOT NULL,
  `marcadescripcion` text,
  `marcaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `presentacionnombre` varchar(100) NOT NULL,
  `presentaciondescripcion` text,
  `presentacionestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `productocodigobarrasid` int NOT NULL,
  `productonombre` varchar(100) NOT NULL,
  `productopreciocompra` decimal(10,2) NOT NULL,
  `productoporcentajeganancia` decimal(10,2) NOT NULL,
  `productodescripcion` text,
  `productocategoriaid` int NOT NULL,
  `productosubcategoriaid` int NOT NULL,
  `productomarcaid` int NOT NULL,
  `productopresentacionid` int NOT NULL,
  `productoimagen` text,
  `productoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbproducto`
--

INSERT INTO `tbproducto` (`productoid`, `productocodigobarrasid`, `productonombre`, `productopreciocompra`, `productoporcentajeganancia`, `productodescripcion`, `productocategoriaid`, `productosubcategoriaid`, `productomarcaid`, `productopresentacionid`, `productoimagen`, `productoestado`) VALUES
(1, 1, 'Producto Ejemplo', 50.00, 25.00, 'Este es un producto de ejemplo con descripción opcional.', 4, 3, 1, 1, '/../view/static/img/product.png', 0),
(2, 3, 'PRUEBA', 5900.00, 50.00, 'Descripción de Prueba', 5, 2, 1, 1, '/../view/static/img/productos/0005/0002/2_PRUEBA.png', 1),
(3, 6, 'PRUEBA 2', 1500.00, 5.00, '', 3, 1, 1, 1, '/../view/static/img/product.png', 1),
(4, 7, 'PRUEBA 3', 2800.00, 5.00, '', 3, 1, 1, 1, '/../view/static/img/productos/0003/0001/4_PRUEBA_3.png', 1),
(5, 8, 'PRUEBA 4', 9700.00, 20.00, '', 3, 1, 1, 1, '/../view/static/img/productos/0003/0001/5_PRUEBA_4.jpg', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedor`
--

CREATE TABLE `tbproveedor` (
  `proveedorid` int NOT NULL,
  `proveedornombre` varchar(100) NOT NULL,
  `proveedoremail` varchar(100) NOT NULL,
  `proveedorcategoriaid` int NOT NULL,
  `proveedorfechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proveedorfechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `proveedorestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbproveedor`
--

INSERT INTO `tbproveedor` (`proveedorid`, `proveedornombre`, `proveedoremail`, `proveedorcategoriaid`, `proveedorfechacreacion`, `proveedorfechamodificacion`, `proveedorestado`) VALUES
(1, 'Proveedor 1', 'proveedor1@ejemplo.com', 5, '2024-09-15 18:07:39', '2024-09-17 09:12:53', 1),
(2, 'Proveedor 2', 'proveedor2@gmail.com', 4, '2024-09-17 09:10:20', '2024-09-17 09:18:21', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedordireccion`
--

CREATE TABLE `tbproveedordireccion` (
  `proveedordireccionid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `direccionid` int NOT NULL,
  `proveedordireccionestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbproveedordireccion`
--

INSERT INTO `tbproveedordireccion` (`proveedordireccionid`, `proveedorid`, `direccionid`, `proveedordireccionestado`) VALUES
(1, 1, 7, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedorproducto`
--

CREATE TABLE `tbproveedorproducto` (
  `proveedorproductoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `productoid` int NOT NULL,
  `proveedorproductoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedortelefono`
--

CREATE TABLE `tbproveedortelefono` (
  `proveedortelefonoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `telefonoid` int NOT NULL,
  `proveedortelefonoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbproveedortelefono`
--

INSERT INTO `tbproveedortelefono` (`proveedortelefonoid`, `proveedorid`, `telefonoid`, `proveedortelefonoestado`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 1, 5, 1),
(5, 1, 6, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbrolusuario`
--

CREATE TABLE `tbrolusuario` (
  `rolusuarioid` int NOT NULL,
  `rolusuarionombre` varchar(100) NOT NULL,
  `rolusuariodescripcion` text,
  `rolusuarioestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbrolusuario`
--

INSERT INTO `tbrolusuario` (`rolusuarioid`, `rolusuarionombre`, `rolusuariodescripcion`, `rolusuarioestado`) VALUES
(1, 'Administrador(a)', 'Usuario administrador del local', 1),
(2, 'Dependiente', 'Trabajador encargado de manejar el local', 1),
(3, 'Cajero(a)', 'Dependiente encargado de la caja', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbsubcategoria`
--

CREATE TABLE `tbsubcategoria` (
  `subcategoriaid` int NOT NULL,
  `subcategoriacategoriaid` int NOT NULL,
  `subcategorianombre` varchar(100) NOT NULL,
  `subcategoriadescripcion` text,
  `subcategoriaestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbsubcategoria`
--

INSERT INTO `tbsubcategoria` (`subcategoriaid`, `subcategoriacategoriaid`, `subcategorianombre`, `subcategoriadescripcion`, `subcategoriaestado`) VALUES
(1, 3, 'Mobile', '', 1),
(2, 5, 'Photo', '', 1),
(3, 4, 'PRUEBA', 'Subcategoria de prueba', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbtelefono`
--

CREATE TABLE `tbtelefono` (
  `telefonoid` int NOT NULL,
  `telefonotipo` varchar(50) NOT NULL,
  `telefonocodigopais` varchar(10) NOT NULL,
  `telefononumero` varchar(20) NOT NULL,
  `telefonoextension` varchar(10) DEFAULT NULL,
  `telefonofechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `telefonofechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `telefonoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbtelefono`
--

INSERT INTO `tbtelefono` (`telefonoid`, `telefonotipo`, `telefonocodigopais`, `telefononumero`, `telefonoextension`, `telefonofechacreacion`, `telefonofechamodificacion`, `telefonoestado`) VALUES
(1, 'Móvil', '+1-809', '257 998 5247', '', '2024-09-07 22:11:19', '2024-09-24 00:17:47', 0),
(2, 'Móvil', '+505', '9728 6416', '', '2024-09-07 22:11:51', '2024-09-23 21:37:09', 1),
(3, 'Móvil', '+593', '65 588 4412', '', '2024-09-10 09:21:51', '2024-09-10 09:21:51', 1),
(4, 'Móvil', '+506', '6421 2950', '', '2024-09-11 20:28:23', '2024-09-16 19:47:06', 1),
(5, 'Móvil', '+502', '5972 3158', '', '2024-09-14 17:08:22', '2024-09-14 17:08:22', 1),
(6, 'Móvil', '+51', '5679 8524', '', '2024-09-14 17:48:35', '2024-09-14 17:48:35', 1),
(7, 'Móvil', '+595', '9654 7820', '', '2024-09-16 19:43:25', '2024-09-16 19:43:25', 1),
(8, 'Móvil', '+54', '1234 5678', '', '2024-09-23 23:00:12', '2024-09-23 23:00:26', 1),
(9, 'Móvil', '+507', '7309 8254', '', '2024-09-24 00:18:04', '2024-10-03 00:50:36', 1),
(10, 'Móvil', '+503', '9758 8749', '', '2024-10-02 23:05:58', '2024-10-03 00:11:58', 1),
(11, 'Fijo', '+506', '2764 8088', '', '2024-10-03 00:56:06', '2024-10-03 00:56:06', 1),
(12, 'Fijo', '+506', '2764 8089', '', '2024-10-03 00:58:07', '2024-10-03 01:02:02', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbusuario`
--

CREATE TABLE `tbusuario` (
  `usuarioid` int NOT NULL,
  `usuarionombre` varchar(100) NOT NULL,
  `usuarioapellido1` varchar(100) NOT NULL,
  `usuarioapellido2` varchar(100) NOT NULL,
  `usuariorolusuarioid` int NOT NULL,
  `usuarioemail` varchar(100) NOT NULL,
  `usuariopassword` varchar(255) NOT NULL,
  `usuariofechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuariofechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usuarioestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbusuario`
--

INSERT INTO `tbusuario` (`usuarioid`, `usuarionombre`, `usuarioapellido1`, `usuarioapellido2`, `usuariorolusuarioid`, `usuarioemail`, `usuariopassword`, `usuariofechacreacion`, `usuariofechamodificacion`, `usuarioestado`) VALUES
(1, 'Isaac', 'Herrera', 'Pastrana', 2, 'isaacmhp2001@gmail.com', '$2y$10$WIq4w2R83lzCkfa9L3NaK.9lZs.OyELxxAC/sqU3Rl4sxzlJboxgm', '2024-09-15 23:17:43', '2024-09-23 02:04:08', 1),
(2, 'Admin', 'Adminson', 'Adminsen', 1, 'admin@admin.com', '$2y$10$HzXMgCvzRJdx1k9dPniUvuZectQkf4UV6wYt4M5lOk.4vi9kqEXoC', '2024-09-16 17:49:31', '2024-10-03 20:22:29', 1),
(3, 'Prueba', 'Primero', 'Segundo', 3, 'cajero@gmail.com', '$2y$10$jXNkOrLG49.wJdSf3DDrWuOfden5BzWj8NkU9I15OxoYYNUbwGa1.', '2024-10-03 20:23:56', '2024-10-03 20:47:29', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbusuariotelefono`
--

CREATE TABLE `tbusuariotelefono` (
  `usuariotelefonoid` int NOT NULL,
  `usuarioid` int NOT NULL,
  `telefonoid` int NOT NULL,
  `usuariotelefonoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbusuariotelefono`
--

INSERT INTO `tbusuariotelefono` (`usuariotelefonoid`, `usuarioid`, `telefonoid`, `usuariotelefonoestado`) VALUES
(1, 1, 4, 1);

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
-- Indices de la tabla `tbusuariotelefono`
--
ALTER TABLE `tbusuariotelefono`
  ADD PRIMARY KEY (`usuariotelefonoid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
