-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 04-08-2024 a las 19:44:54
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
  `direccionprovincia` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccioncanton` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direcciondistrito` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccionbarrio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccionsennas` text COLLATE utf8mb4_unicode_ci,
  `direcciondistancia` decimal(5,2) NOT NULL,
  `direccionestado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbdireccion`
--

INSERT INTO `tbdireccion` (`direccionid`, `direccionprovincia`, `direccioncanton`, `direcciondistrito`, `direccionbarrio`, `direccionsennas`, `direcciondistancia`, `direccionestado`) VALUES
(1, 'Heredia', 'Sarapiquí', 'Horquetas', 'Horquetas', '200m sur', '25.00', 1),
(2, 'Cartago', 'Oreamuno', 'Cot', 'San Juan', '100m oeste', '10.00', 1),
(3, 'San José', 'Desamparados', 'San Miguel', 'Centro', '200m este', '5.00', 1),
(4, 'Alajuela', 'San Carlos', 'La Fortuna', 'Arenal', '300m sur', '15.00', 1),
(5, 'Heredia', 'Barva', 'San Pedro', 'Barrio La Palma', '150m norte', '20.00', 1),
(6, 'Guanacaste', 'Liberia', 'San José', 'El Roble', '250m este', '12.00', 1),
(7, 'Puntarenas', 'Esparza', 'San Juan', 'Playa Hermosa', '350m oeste', '18.00', 1),
(8, 'San José', 'Moravia', 'La Trinidad', 'Barrio La Cruz', '400m sur', '8.00', 1),
(9, 'Cartago', 'La Unión', 'San Diego', 'Rincón de La Vieja', '600m este', '22.00', 1),
(10, 'Alajuela', 'Guatuso', 'San Rafael', 'Aguas Claras', '120m norte', '9.00', 1);

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
(5, 'IRF', '12.00', 'Impuesto al Regalo Fraterno', 1, '2024-07-15 06:00:00'),
(6, 'IJF', '10.00', 'Impuesto al Jugador Favorito', 1, '2024-08-03 06:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproveedor`
--

CREATE TABLE `tbproveedor` (
  `proveedorid` int NOT NULL,
  `proveedorestado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbproveedor`
--

INSERT INTO `tbproveedor` (`proveedorid`, `proveedorestado`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1);

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
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 1, 4, 1),
(5, 1, 5, 1);

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
