-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 02-08-2024 a las 22:22:04
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
-- Estructura de tabla para la tabla `tbimpuesto`
--
-- Creación: 02-08-2024 a las 01:09:44
-- Última actualización: 02-08-2024 a las 01:28:25
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

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tbimpuesto`
--
ALTER TABLE `tbimpuesto`
  ADD PRIMARY KEY (`impuestoid`);
COMMIT;

CREATE TABLE `tbproveedor` (
  `proveedorid` int NOT NULL,
  `proveedornombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proveedoremail` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proveedortipo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `proveedorestado` tinyint(1) NOT NULL,
  `proveedorfecharegistro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`proveedorid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tbproveedor`
  ADD PRIMARY KEY (`proveedorid`);
COMMIT;

INSERT INTO `tbproveedor` (`proveedorid`, `proveedornombre`, `proveedortipo`, `proveedoremail`, `proveedorestado`, `proveedorfecharegistro`) VALUES
(1, 'Proveedor A', 'Tipo 1', 'proveedora@example.com', 1, '2024-01-01 10:00:00'),
(2, 'Proveedor B', 'Tipo 2', 'proveedorb@example.com', 1, '2024-02-01 11:00:00'),
(3, 'Proveedor C', 'Tipo 1', 'proveedorc@example.com', 0, '2024-03-01 12:00:00'),
(4, 'Proveedor D', 'Tipo 3', 'proveedord@example.com', 1, '2024-04-01 13:00:00'),
(5, 'Proveedor E', 'Tipo 2', 'proveedore@example.com', 0, '2024-05-01 14:00:00');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
