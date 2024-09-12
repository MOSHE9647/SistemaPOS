-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 11-09-2024 a las 21:05:36
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

DROP DATABASE IF EXISTS `bdpuntoventa`;
CREATE DATABASE `bdpuntoventa` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `bdpuntoventa`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcategoria`
--

DROP TABLE IF EXISTS `tbcategoria`;
CREATE TABLE IF NOT EXISTS `tbcategoria` (
  `categoriaid` int NOT NULL,
  `categorianombre` varchar(100) NOT NULL,
  `categoriadescripcion` text,
  `categoriaestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`categoriaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Estructura de tabla para la tabla `tbcodigobarras`
--

DROP TABLE IF EXISTS `tbcodigobarras`;
CREATE TABLE IF NOT EXISTS `tbcodigobarras` (
  `codigobarrasid` int NOT NULL,
  `codigobarrasnumero` varchar(100) NOT NULL,
  `codigobarrasfechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codigobarrasfechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `codigobarrasestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`codigobarrasid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcompra`
--

DROP TABLE IF EXISTS `tbcompra`;
CREATE TABLE IF NOT EXISTS `tbcompra` (
  `compraid` int NOT NULL,
  `compranumerofactura` varchar(100) NOT NULL,
  `compramontobruto` decimal(10,2) NOT NULL,
  `compramontoneto` decimal(10,2) NOT NULL,
  `compratipopago` varchar(50) NOT NULL,
  `compraproveedorid` int NOT NULL,
  `comprafechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comprafechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `compraestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`compraid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcompradetalle`
--

DROP TABLE IF EXISTS `tbcompradetalle`;
CREATE TABLE IF NOT EXISTS `tbcompradetalle` (
  `compradetalleid` int NOT NULL,
  `compradetallecompraid` int NOT NULL,
  `compradetalleloteid` int NOT NULL,
  `compradetalleproductoid` int NOT NULL,
  `compradetalleprecioproducto` decimal(10,2) NOT NULL,
  `compradetallecantidad` int NOT NULL,
  `compradetallefechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `compradetallefechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `compradetalleestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`compradetalleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcuentaporpagar`
--

DROP TABLE IF EXISTS `tbcuentaporpagar`;
CREATE TABLE IF NOT EXISTS `tbcuentaporpagar` (
  `cuentaporpagarid` int NOT NULL,
  `cuentaporpagarcompradetalleid` int NOT NULL,
  `cuentaporpagarfechavencimiento` date NOT NULL,
  `cuentaporpagarmontototal` decimal(10,2) NOT NULL,
  `cuentaporpagarmontopagado` decimal(10,2) NOT NULL,
  `cuentaporpagarfechapago` date NOT NULL,
  `cuentaporpagarestadocuenta` varchar(50) NOT NULL DEFAULT 'Pendiente',
  `cuentaporpagarnotas` text,
  `cuentaporpagarestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`cuentaporpagarid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbdireccion`
--

DROP TABLE IF EXISTS `tbdireccion`;
CREATE TABLE IF NOT EXISTS `tbdireccion` (
  `direccionid` int NOT NULL,
  `direccionprovincia` varchar(100) NOT NULL,
  `direccioncanton` varchar(100) NOT NULL,
  `direcciondistrito` varchar(100) NOT NULL,
  `direccionbarrio` varchar(100) DEFAULT '',
  `direccionsennas` text,
  `direcciondistancia` decimal(5,2) NOT NULL,
  `direccionestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`direccionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `tbdireccion`
--

INSERT INTO `tbdireccion` (`direccionid`, `direccionprovincia`, `direccioncanton`, `direcciondistrito`, `direccionbarrio`, `direccionsennas`, `direcciondistancia`, `direccionestado`) VALUES
(1, 'Heredia', 'Sarapiquí', 'Horquetas', 'Urb Miraflores', 'Casa #37', 3.00, 1),
(2, 'Alajuela', 'Zarcero', 'Guadalupe', 'Escalante', 'Casa #26', 20.00, 1),
(3, 'Alajuela', 'San Carlos', 'Aguas Zarcas', 'Cascadia', '', 20.00, 0),
(4, 'Guanacaste', 'Abangares', 'Sierra', 'Sierra', 'Casa #32', 5.00, 1),
(5, 'Heredia', 'Santo Domingo', 'Para', '', '', 20.59, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbimpuesto`
--

DROP TABLE IF EXISTS `tbimpuesto`;
CREATE TABLE IF NOT EXISTS `tbimpuesto` (
  `impuestoid` int NOT NULL,
  `impuestonombre` varchar(100) NOT NULL,
  `impuestovalor` decimal(5,2) NOT NULL,
  `impuestodescripcion` text,
  `impuestofechainiciovigencia` date NOT NULL,
  `impuestofechafinvigencia` date NOT NULL,
  `impuestoestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`impuestoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblote`
--

DROP TABLE IF EXISTS `tblote`;
CREATE TABLE IF NOT EXISTS `tblote` (
  `loteid` int NOT NULL,
  `lotecodigo` varchar(100) NOT NULL,
  `lotefechavencimiento` date NOT NULL,
  `loteestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`loteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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

DROP TABLE IF EXISTS `tbmarca`;
CREATE TABLE IF NOT EXISTS `tbmarca` (
  `marcaid` int NOT NULL,
  `marcanombre` varchar(100) NOT NULL,
  `marcadescripcion` text,
  `marcaestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`marcaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbpresentacion`
--

DROP TABLE IF EXISTS `tbpresentacion`;
CREATE TABLE IF NOT EXISTS `tbpresentacion` (
  `presentacionid` int NOT NULL,
  `presentacionnombre` varchar(100) NOT NULL,
  `presentaciondescripcion` text,
  `presentacionestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`presentacionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproducto`
--

DROP TABLE IF EXISTS `tbproducto`;
CREATE TABLE IF NOT EXISTS `tbproducto` (
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
  `productoestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`productoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedor`
--

DROP TABLE IF EXISTS `tbproveedor`;
CREATE TABLE IF NOT EXISTS `tbproveedor` (
  `proveedorid` int NOT NULL,
  `proveedornombre` varchar(100) NOT NULL,
  `proveedoremail` varchar(100) NOT NULL,
  `proveedorcategoriaid` int NOT NULL,
  `proveedorfechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proveedorfechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `proveedorestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`proveedorid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedordireccion`
--

DROP TABLE IF EXISTS `tbproveedordireccion`;
CREATE TABLE IF NOT EXISTS `tbproveedordireccion` (
  `proveedordireccionid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `direccionid` int NOT NULL,
  `proveedordireccionestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`proveedordireccionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedorproducto`
--

DROP TABLE IF EXISTS `tbproveedorproducto`;
CREATE TABLE IF NOT EXISTS `tbproveedorproducto` (
  `proveedorproductoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `productoid` int NOT NULL,
  `proveedorproductoestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`proveedorproductoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedortelefono`
--

DROP TABLE IF EXISTS `tbproveedortelefono`;
CREATE TABLE IF NOT EXISTS `tbproveedortelefono` (
  `proveedortelefonoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `telefonoid` int NOT NULL,
  `proveedortelefonoestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`proveedortelefonoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbrolusuario`
--

DROP TABLE IF EXISTS `tbrolusuario`;
CREATE TABLE IF NOT EXISTS `tbrolusuario` (
  `rolusuarioid` int NOT NULL,
  `rolusuarionombre` varchar(100) NOT NULL,
  `rolusuariodescripcion` text,
  `rolusuarioestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`rolusuarioid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbsubcategoria`
--

DROP TABLE IF EXISTS `tbsubcategoria`;
CREATE TABLE IF NOT EXISTS `tbsubcategoria` (
  `subcategoriaid` int NOT NULL,
  `subcategoriacategoriaid` int NOT NULL,
  `subcategorianombre` varchar(100) NOT NULL,
  `subcategoriadescripcion` text,
  `subcategoriaestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`subcategoriaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbtelefono`
--

DROP TABLE IF EXISTS `tbtelefono`;
CREATE TABLE IF NOT EXISTS `tbtelefono` (
  `telefonoid` int NOT NULL,
  `telefonotipo` varchar(50) NOT NULL,
  `telefonocodigopais` varchar(10) NOT NULL,
  `telefononumero` varchar(20) NOT NULL,
  `telefonoextension` varchar(10) DEFAULT NULL,
  `telefonofechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `telefonofechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `telefonoestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`telefonoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `tbtelefono`
--

INSERT INTO `tbtelefono` (`telefonoid`, `telefonotipo`, `telefonocodigopais`, `telefononumero`, `telefonoextension`, `telefonofechacreacion`, `telefonofechamodificacion`, `telefonoestado`) VALUES
(1, 'Móvil', '+1-809', '257 998 5247', '', '2024-09-07 22:11:19', '2024-09-07 22:11:19', 1),
(2, 'Móvil', '+503', '9728 6416', '', '2024-09-07 22:11:51', '2024-09-07 22:12:25', 0),
(3, 'Móvil', '+593', '65 588 4412', '', '2024-09-10 09:21:51', '2024-09-10 09:21:51', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbusuario`
--

DROP TABLE IF EXISTS `tbusuario`;
CREATE TABLE IF NOT EXISTS `tbusuario` (
  `usuarioid` int NOT NULL,
  `usuarionombre` varchar(100) NOT NULL,
  `usuarioapellido1` varchar(100) NOT NULL,
  `usuarioapellido2` varchar(100) NOT NULL,
  `usuariorolusuarioid` int NOT NULL,
  `usuarioemail` varchar(100) NOT NULL,
  `usuariopassword` varchar(255) NOT NULL,
  `usuariofechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuariofechamodificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usuarioestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`usuarioid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbusuariotelefono`
--

DROP TABLE IF EXISTS `tbusuariotelefono`;
CREATE TABLE IF NOT EXISTS `tbusuariotelefono` (
  `usuariotelefonoid` int NOT NULL,
  `usuarioid` int NOT NULL,
  `telefonoid` int NOT NULL,
  `usuariotelefonoestado` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`usuariotelefonoid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
