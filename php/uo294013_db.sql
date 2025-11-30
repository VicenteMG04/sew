-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-11-2025 a las 10:16:44
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `uo294013_db`
--
DROP DATABASE IF EXISTS `uo294013_db`;

CREATE DATABASE IF NOT EXISTS `uo294013_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `uo294013_db`;
DROP TABLE IF EXISTS `observaciones`;
DROP TABLE IF EXISTS `resultado`;
DROP TABLE IF EXISTS `usuario`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `observaciones`
--

CREATE TABLE `observaciones` (
  `id_usuario` int(11) NOT NULL COMMENT 'Código de identificación del usuario que realiza la prueba',
  `comentarios` text NOT NULL COMMENT 'Comentarios por parte del facilitador acerca de la realización de la prueba del usuario identificado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Observaciones de las pruebas realizadas por el usuario';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultado`
--

CREATE TABLE `resultado` (
  `id_usuario` int(11) NOT NULL COMMENT 'Código de identificación del usuario que realiza la prueba',
  `dispositivo` varchar(20) NOT NULL COMMENT 'Dispositivo utilizado por el usuario para realizar la prueba',
  `tiempo` time NOT NULL COMMENT 'Tiempo empleado por el usuario para realizar la prueba (formato HH:MM:SS.mmm)',
  `completada` tinyint(1) NOT NULL COMMENT 'Si el usuario ha completado la prueba (True) o no ha podido finalizarla (False)',
  `comentarios` text NOT NULL COMMENT 'Comentarios por parte del usuario que realiza la prueba',
  `propuestas` text NOT NULL COMMENT 'Propuestas de mejora por parte del usuario que realiza la prueba',
  `valoracion` int(2) NOT NULL COMMENT 'Valoración de la aplicación por parte del usuario (nota 0-10)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Datos obtenidos de la ejecución de las pruebas de usabilidad';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL COMMENT 'Código de identificación del usuario que realiza la prueba',
  `profesion` varchar(50) NOT NULL COMMENT 'Profesión del usuario que realiza la prueba',
  `edad` int(3) NOT NULL COMMENT 'Edad del usuario que realiza la prueba',
  `genero` varchar(20) NOT NULL COMMENT 'Género del usuario que realiza la prueba',
  `pericia` int(2) NOT NULL COMMENT 'Pericia informática del usuario que realiza la prueba'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Datos de los usuarios que realizan la prueba';

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `observaciones`
--
ALTER TABLE `observaciones`
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD PRIMARY KEY(`id_usuario`, `dispositivo`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `observaciones`
--
ALTER TABLE `observaciones`
  ADD CONSTRAINT `observaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD CONSTRAINT `resultado_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
