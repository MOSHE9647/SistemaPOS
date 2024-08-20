-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 19-08-2024 a las 21:19:55
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
(1, 'Provincia A', 'Canton A', 'Distrito A', 'Barrio A', 'Señas A', 1.00, 1),
(2, 'Provincia B', 'Canton B', 'Distrito B', 'Barrio B', 'Señas B', 2.50, 1),
(3, 'Provincia C', 'Canton C', 'Distrito C', 'Barrio C', 'Señas C', 3.75, 1),
(4, 'Provincia D', 'Canton D', 'Distrito D', 'Barrio D', 'Señas D', 1.26, 1),
(5, 'Provincia E', 'Canton E', 'Distrito E', 'Barrio E', 'Señas E', 2.00, 1),
(6, 'San José', 'Goicoechea', 'Purral', 'Kurú', 'Alameda 6', 6.50, 0),
(7, 'Heredia', 'Sarapiquí', 'Horquetas', 'Urb. Miraflores', 'Casa #37, detrás del Bar Kikes', 4.00, 0);

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

INSERT INTO `tbimpuesto` (`impuestoid`, `impuestonombre`, `impuestovalor`, `impuestodescripcion`, `impuestoestado`, `impuestofechavigencia`) VALUES
(1, 'IVA', 13.00, 'Impuesto al Valor Agregado', 1, '2024-08-01 06:00:00'),
(5, 'IRF', 12.00, 'Impuesto al Regalo Fraterno', 1, '2024-07-15 06:00:00'),
(6, 'IMP', 33.00, 'Impuesto al Mejor Personaje', 1, '2024-08-10 06:00:00'),
(7, 'IJP', 20.00, 'Impuesto al Jugador Preferido', 1, '2024-08-20 00:25:30'),
(8, 'ILP', 13.00, 'prueba2', 0, '2024-08-20 01:27:42'),
(9, 'PKF', 15.00, 'prueba3', 0, '2024-08-20 01:27:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproducto`
--

CREATE TABLE `tbproducto` (
  `productoid` int NOT NULL,
  `productonombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `productopreciounitario` decimal(10,2) NOT NULL,
  `productocantidad` int NOT NULL,
  `productofechaadquisicion` datetime NOT NULL,
  `productodescripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `productocodigobarras` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `productoestado` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbproducto`
--

INSERT INTO `tbproducto` (`productoid`, `productonombre`, `productopreciounitario`, `productocantidad`, `productofechaadquisicion`, `productodescripcion`, `productocodigobarras`, `productoestado`) VALUES
(1, 'coca-cola', 1200.00, 30, '2024-08-11 21:46:52', 'coca-cola de 2.5L', '1234567890123', 1),
(2, 'pepsi', 1500.00, 40, '2023-08-10 00:00:00', 'refresco de 3L sin azucar', '1234567890124', 1),
(3, 'Ginger Ale', 1300.00, 20, '2024-08-13 00:00:00', 'Refresco de 3L', '1234567890125', 0);

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

INSERT INTO `tbproveedor` (`proveedorid`, `proveedornombre`, `proveedoremail`, `proveedortipo`, `proveedorestado`, `proveedorfecharegistro`) VALUES
(1, 'Proveedor A', 'proveedora@example.com', 'Tipo 1', 1, '2024-01-01 10:00:00'),
(2, 'Proveedor B', 'proveedorb@example.com', 'Tipo 2', 1, '2024-02-01 11:00:00'),
(3, 'Proveedor C', 'proveedorc@example.com', 'Tipo 1', 0, '2024-03-01 12:00:00'),
(4, 'Proveedor D', 'proveedord@example.com', 'Tipo 3', 1, '2024-04-01 13:00:00'),
(5, 'Proveedor E', 'proveedore@example.com', 'Tipo 2', 0, '2024-05-01 14:00:00'),
(6, 'Proveedor F', 'proveedorf@example.com', 'Tipo 4', 1, '2024-08-12 06:00:00'),
(7, 'Proveedor G', 'proveedorg@example.com', 'Tipo 5', 1, '2024-08-12 06:00:00'),
(8, 'Proveedor H', 'proveedorh@example.com', 'Tipo 6', 1, '2024-08-12 06:00:00'),
(9, 'Proveedor I', 'proveedori@example.com', 'Tipo 7', 1, '2024-08-12 06:00:00'),
(10, 'Proveedor J', 'proveedorj@example.com', 'Tipo 9', 0, '2024-08-12 06:00:00');

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
-- Estructura de tabla para la tabla `tbproveedortelefono`
--

CREATE TABLE `tbproveedortelefono` (
  `proveedortelefonoid` int NOT NULL,
  `proveedorid` int NOT NULL,
  `proveedortelefono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proveedortelefonoestado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbproveedortelefono`
--

INSERT INTO `tbproveedortelefono` (`proveedortelefonoid`, `proveedorid`, `proveedortelefono`, `proveedortelefonoestado`) VALUES
(1, 9, '+506 6421 2950', 0),
(2, 9, '+506 6397 3487', 1),
(3, 8, '+506 6421 2951', 1),
(4, 8, '+506 6421 2952', 0);

--
-- Índices para tablas volcadas
--

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
-- Indices de la tabla `tbproducto`
--
ALTER TABLE `tbproducto`
  ADD PRIMARY KEY (`productoid`),
  ADD UNIQUE KEY `productocodigobarras` (`productocodigobarras`);

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
-- Indices de la tabla `tbproveedortelefono`
--
ALTER TABLE `tbproveedortelefono`
  ADD PRIMARY KEY (`proveedortelefonoid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
