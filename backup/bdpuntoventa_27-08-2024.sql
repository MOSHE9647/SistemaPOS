-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Servidor: bdpbhgi0jbzwpoftwisg-mysql.services.clever-cloud.com
-- Tiempo de generación: 27-08-2024 a las 14:28:59
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
  `categorianombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `categoriaestado` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcodigobarras`
--

CREATE TABLE `tbcodigobarras` (
  `codigobarrasid` int NOT NULL,
  `codigobarrasnumero` int NOT NULL,
  `codigobarrasfechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codigobarrasfechamodificacion` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `codigobarrasestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbcodigobarras`
--

INSERT INTO `tbcodigobarras` (`codigobarrasid`, `codigobarrasnumero`, `codigobarrasestado`) VALUES
(1, 1000100011, 1),
(2, 2000200022, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcompraproducto`
--

CREATE TABLE `tbcompraproducto` (
  `compraproductoid` int NOT NULL,
  `compraproductocantidad` int NOT NULL,
  `compraproductoproveedorid` int NOT NULL,
  `compraproductofechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `compraproductoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbdireccion`
--

CREATE TABLE `tbdireccion` (
  `direccionid` int NOT NULL,
  `direccionprovincia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccioncanton` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `direcciondistrito` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccionbarrio` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccionsennas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `direcciondistancia` decimal(5,2) NOT NULL,
  `direccionestado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbdireccion`
--

INSERT INTO `tbdireccion` (`direccionid`, `direccionprovincia`, `direccioncanton`, `direcciondistrito`, `direccionbarrio`, `direccionsennas`, `direcciondistancia`, `direccionestado`) VALUES
(1, 'Provincia A', 'Canton A', 'Distrito A', 'Barrio A', 'Señas A', '1.00', 1),
(2, 'Provincia B', 'Canton B', 'Distrito B', 'Barrio B', 'Señas B', '2.50', 1),
(3, 'Provincia C', 'Canton C', 'Distrito C', 'Barrio C', 'Señas C', '3.75', 1),
(4, 'Provincia D', 'Canton D', 'Distrito D', 'Barrio D', 'Señas D', '1.26', 1),
(5, 'Provincia E', 'Canton E', 'Distrito E', 'Barrio E', 'Señas E', '2.00', 1),
(6, 'San José', 'Goicoechea', 'Purral', 'Kurú', 'Alameda 6', '6.50', 0),
(7, 'Heredia', 'Sarapiquí', 'Horquetas', 'Urb. Miraflores', 'Casa #37, detrás del Bar Kikes', '4.00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbimpuesto`
--

CREATE TABLE `tbimpuesto` (
  `impuestoid` int NOT NULL,
  `impuestonombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `impuestovalor` decimal(5,2) NOT NULL,
  `impuestodescripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `impuestoestado` tinyint(1) NOT NULL,
  `impuestofechavigencia` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbimpuesto`
--

INSERT INTO `tbimpuesto` (`impuestoid`, `impuestonombre`, `impuestovalor`, `impuestodescripcion`, `impuestoestado`) VALUES
(1, 'IVA', '13.00', 'Impuesto al Valor Agregado', 1),
(5, 'IRF', '12.00', 'Impuesto al Regalo Fraterno', 1),
(6, 'IMP', '33.00', 'Impuesto al Mejor Personaje', 1),
(7, 'IJP', '20.00', 'Impuesto al Jugador Preferido', 1),
(8, 'ILP', '13.00', 'prueba2', 0),
(9, 'PKF', '15.00', 'prueba3', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblote`
--

CREATE TABLE `tblote` (
  `loteid` int NOT NULL,
  `lotecodigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `compraid` int NOT NULL,
  `productoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `lotefechavencimiento` date NOT NULL,
  `loteestado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tblote`
--

INSERT INTO `tblote` (`loteid`, `lotecodigo`, `compraid`, `productoid`, `proveedorid`, `lotefechavencimiento`, `loteestado`) VALUES
(1, '1', 1, 1, 1, '2024-08-23', 1),
(2, '2', 2, 2, 2, '2024-08-27', 1),
(3, '3', 3, 2, 3, '2024-08-08', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproducto`
--

CREATE TABLE `tbproducto` (
  `productoid` int NOT NULL,
  `productonombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `productopreciocompra` decimal(10,2) NOT NULL,
  `productoporcentajeganancia` decimal(10,2) NOT NULL,
  `productodescripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `productocodigobarrasid` int DEFAULT NULL,
  `productofoto` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `productoestado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbproducto`
--

INSERT INTO `tbproducto` (`productoid`, `productonombre`, `productopreciocompra`, `productoporcentajeganancia`, `productodescripcion`, `productocodigobarrasid`, `productofoto`, `productoestado`) VALUES
(1, 'BIG COLA', '1200.00', '5.00', 'Refresco de 3L', 1, '/mnt/c/Users/isaac/OneDrive - Universidad Nacional de Costa Rica/2024/II CICLO 2024/Paradigmas de Programación/Proyecto/SistemaPOS/service/../view/img/productos/0001/0001/0001/0001000100011.png', 1),
(2, 'Pepsi', '1500.00', '0.00', 'Refresco de 3L', 0, '', 1),
(3, 'Ginger Ale', '1300.00', '0.00', 'Refresco de 3L', 0, '', 1),
(4, 'Ginger-Ale', '800.00', '0.00', 'Refresco de 600ml', 0, '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproductocategoria`
--

CREATE TABLE `tbproductocategoria` (
  `productosubcategoriaid` int NOT NULL,
  `productoid` int NOT NULL,
  `categoriaid` int NOT NULL,
  `productocategoriaestado` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproductosubcategoria`
--

CREATE TABLE `tbproductosubcategoria` (
  `productosubcategoriaid` int NOT NULL,
  `productoid` int NOT NULL,
  `subcategoriaid` int NOT NULL,
  `productocategoriaestado` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbproductosubcategoria`
--

INSERT INTO `tbproductosubcategoria` (`productosubcategoriaid`, `productoid`, `subcategoriaid`, `productocategoriaestado`) VALUES
(1, 1, 2, 1),
(2, 2, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedor`
--

CREATE TABLE `tbproveedor` (
  `proveedorid` int NOT NULL,
  `proveedornombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proveedoremail` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proveedortipo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `proveedorestado` tinyint(1) NOT NULL,
  `proveedorfecharegistro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbproveedor`
--

INSERT INTO `tbproveedor` (`proveedorid`, `proveedornombre`, `proveedoremail`, `proveedortipo`, `proveedorestado`) VALUES
(1, 'Proveedor A', 'proveedora@example.com', 'Tipo 1', 0),
(2, 'Proveedor B', 'proveedorb@example.com', 'Tipo 2', 0),
(3, 'Proveedor C', 'proveedorc@example.com', 'Tipo 1', 0),
(4, 'Proveedor D', 'proveedord@example.com', 'Tipo 3', 0),
(5, 'Proveedor E', 'proveedore@example.com', 'Tipo 2', 0),
(6, 'Proveedor F', 'proveedorf@example.com', 'Tipo 4', 0),
(7, 'Proveedor G', 'proveedorg@example.com', 'Tipo 5', 1),
(8, 'Proveedor H', 'proveedorh@example.com', 'Tipo 6', 0),
(9, 'Proveedor I', 'proveedori@example.com', 'Tipo 7', 0),
(10, 'Proveedor J', 'proveedorj@example.com', 'Tipo 9', 0),
(11, 'Proveedor K', 'proveedork@example.com', 'Tipo 1', 0),
(12, 'D', 'proveedor@example.com', '1', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedordireccion`
--

CREATE TABLE `tbproveedordireccion` (
  `proveedordireccionid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `direccionid` int NOT NULL,
  `proveedordireccionestado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbproveedordireccion`
--

INSERT INTO `tbproveedordireccion` (`proveedordireccionid`, `proveedorid`, `direccionid`, `proveedordireccionestado`) VALUES
(1, 1, 1, 0),
(2, 2, 2, 1),
(3, 3, 3, 0),
(4, 4, 4, 1),
(5, 5, 5, 0),
(6, 1, 6, 0),
(7, 1, 6, 1),
(8, 1, 1, 0),
(9, 1, 1, 0),
(10, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedorproducto`
--

CREATE TABLE `tbproveedorproducto` (
  `provedorproductoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `productoid` int NOT NULL,
  `proveedorproductoestado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbsubcategoria`
--

CREATE TABLE `tbsubcategoria` (
  `tbsubcategoriaid` int NOT NULL,
  `tbsubcategorianombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tbsubcategoriaestado` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbsubcategoria`
--

INSERT INTO `tbsubcategoria` (`tbsubcategoriaid`, `tbsubcategorianombre`, `tbsubcategoriaestado`) VALUES
(1, 'Libros', 1),
(2, 'Hojas', 1),
(3, 'Helados', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbtelefono`
--

CREATE TABLE `tbtelefono` (
  `telefonoid` int NOT NULL,
  `telefonoproveedorid` int NOT NULL,
  `telefonofechacreacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `telefonotipo` varchar(10) NOT NULL,
  `telefonoextension` varchar(10) DEFAULT NULL,
  `telefonocodigopais` varchar(5) NOT NULL,
  `telefononumero` varchar(20) NOT NULL,
  `telefonoestado` tinyint NOT NULL DEFAULT '1'
) ;

--
-- Volcado de datos para la tabla `tbtelefono`
--

INSERT INTO `tbtelefono` (`telefonoid`, `telefonoproveedorid`, `telefonotipo`, `telefonoextension`, `telefonocodigopais`, `telefononumero`, `telefonoestado`) VALUES
(1, 2, 'Móvil', 'Extension', '+506', '6421 2950', 1),
(2, 2, 'Fax', '1234', '+506', '6397 3489', 1),
(3, 2, 'Fax', 'Extension', '+505', '6397 9999', 0),
(4, 7, 'Móvil', '321', '+502', '6666 1111', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbtipocompra`
--

CREATE TABLE `tbtipocompra` (
  `tipocompraid` int NOT NULL,
  `tipocomprafechacreacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipocomprafechamodificacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tipocompracompraproductoid` int NOT NULL,
  `tipocompradescripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tipocompranombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipocompratasainteres` decimal(10,2) NOT NULL,
  `tipocompraplazos` int NOT NULL,
  `tipocomprameses` int NOT NULL,
  `tipocompraestado` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Indices de la tabla `tbcompraproducto`
--
ALTER TABLE `tbcompraproducto`
  ADD PRIMARY KEY (`compraproductoid`);

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
  ADD PRIMARY KEY (`productosubcategoriaid`);

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
-- Indices de la tabla `tbsubcategoria`
--
ALTER TABLE `tbsubcategoria`
  ADD PRIMARY KEY (`tbsubcategoriaid`);

--
-- Indices de la tabla `tbtelefono`
--
ALTER TABLE `tbtelefono`
  ADD PRIMARY KEY (`telefonoid`);

--
-- Indices de la tabla `tbtipocompra`
--
ALTER TABLE `tbtipocompra`
  ADD PRIMARY KEY (`tipocompraid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
