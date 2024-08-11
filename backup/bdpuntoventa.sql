-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 10-08-2024 a las 00:58:31
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
(1, 'Provincia A', 'Canton A', 'Distrito A', 'Barrio A', 'Señas A', '1.00', 1),
(2, 'Provincia B', 'Canton B', 'Distrito B', 'Barrio B', 'Señas B', '2.50', 1),
(3, 'Provincia C', 'Canton C', 'Distrito C', 'Barrio C', 'Señas C', '3.75', 1),
(4, 'Provincia D', 'Canton D', 'Distrito D', 'Barrio D', 'Señas D', '1.25', 1),
(5, 'Provincia E', 'Canton E', 'Distrito E', 'Barrio E', 'Señas E', '2.00', 1);

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
  `impuestofechavigencia` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbimpuesto`
--

INSERT INTO `tbimpuesto` (`impuestoid`, `impuestonombre`, `impuestovalor`, `impuestodescripcion`, `impuestoestado`, `impuestofechavigencia`) VALUES
(1, 'IVA', '13.00', 'Impuesto al Valor Agregado', 1, '2024-08-01 06:00:00'),
(2, 'IRF', '12.00', 'q', 0, '2024-08-01 06:00:00'),
(3, 'IRF', '12.00', 'qw', 0, '2024-08-02 06:00:00'),
(4, 'IRF', '12.00', 'Prueba', 0, '2024-08-02 06:00:00'),
(5, 'IRF', '12.00', 'Impuesto al Regalo Fraterno', 1, '2024-07-15 06:00:00');

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
(5, 'Proveedor E', 'proveedore@example.com', 'Tipo 2', 0, '2024-05-01 14:00:00');

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
(1, 1, 1, 1),
(2, 2, 2, 1),
(3, 3, 3, 0),
(4, 4, 4, 1),
(5, 5, 5, 0);

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
-- Indices de la tabla `tbproveedor`
--
ALTER TABLE `tbproveedor`
  ADD PRIMARY KEY (`proveedorid`);

--
-- Indices de la tabla `tbproveedordireccion`
--
ALTER TABLE `tbproveedordireccion`
  ADD PRIMARY KEY (`proveedordireccionid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
