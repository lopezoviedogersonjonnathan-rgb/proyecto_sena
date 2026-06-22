-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 20-06-2026 a las 18:25:42
-- Versión del servidor: 11.4.10-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `trueque_match_db`
--
CREATE DATABASE IF NOT EXISTS `trueque_match_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `trueque_match_db`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

CREATE TABLE `administrador` (
  `id_admin` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `clave_acceso` varchar(255) NOT NULL,
  `nivel_acceso` varchar(50) NOT NULL DEFAULT 'moderador',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`id_admin`, `nombre`, `correo`, `clave_acceso`, `nivel_acceso`, `fecha_registro`, `activo`) VALUES
(1, 'Arnaldo Montiel', 'arnaldo@trueque.com', '$2b$10$adm..', 'super_admin', '2026-06-04 23:12:51', 1),
(2, 'Sandra López', 'sandra@trueque.com', '$2b$10$san..', 'moderador', '2026-06-04 23:12:51', 1),
(3, 'Miguel Herrera', 'miguel@trueque.com', '$2b$10$mig..', 'moderador', '2026-06-04 23:12:51', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluacion`
--

CREATE TABLE `evaluacion` (
  `id_evaluacion` int(11) NOT NULL,
  `puntaje` int(1) NOT NULL,
  `comentario` text DEFAULT NULL,
  `fecha_evaluacion` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL,
  `id_trueque` int(11) NOT NULL
) ;

--
-- Volcado de datos para la tabla `evaluacion`
--

INSERT INTO `evaluacion` (`id_evaluacion`, `puntaje`, `comentario`, `fecha_evaluacion`, `id_usuario`, `id_trueque`) VALUES
(1, 5, 'Excelente persona, muy puntual y cumplida', '2026-06-04 23:12:51', 1, 1),
(2, 5, 'Bici en perfecto estado, tal como la describió', '2026-06-04 23:12:51', 2, 1),
(3, 4, 'Buena comunicación, artículo en buen estado', '2026-06-04 23:12:51', 4, 3),
(4, 3, 'Tardó en responder, pero cumplió al final', '2026-06-04 23:12:51', 3, 3),
(5, 5, 'Excelente intercambio con Gerson', '2026-06-07 04:24:37', 1, 1),
(6, 4, 'Muy buena experiencia', '2026-06-07 04:24:37', 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('info','alerta','sistema','trueque') NOT NULL DEFAULT 'info',
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL,
  `id_trueque` int(11) DEFAULT NULL,
  `id_oferta` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificacion`
--

INSERT INTO `notificacion` (`id_notificacion`, `mensaje`, `tipo`, `leida`, `fecha_envio`, `id_usuario`, `id_trueque`, `id_oferta`) VALUES
(1, 'Carlos quiere intercambiar tu bicicleta', 'alerta', 1, '2026-06-04 23:12:51', 1, NULL, 1),
(2, 'Tu trueque #1 fue completado exitosamente', 'trueque', 1, '2026-06-04 23:12:51', 1, 1, NULL),
(3, 'Recibiste una evaluación de 5 estrellas', 'info', 1, '2026-06-04 23:12:51', 2, 1, NULL),
(4, 'Sofía propone intercambio por tu teclado', 'alerta', 0, '2026-06-04 23:12:51', 1, NULL, 3),
(5, 'Tu oferta ha sido guardada como favorita', 'info', 0, '2026-06-04 23:12:51', 4, NULL, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oferta`
--

CREATE TABLE `oferta` (
  `id_oferta` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text NOT NULL,
  `categoria` enum('producto','servicio','conocimiento','experiencia') NOT NULL,
  `estado` enum('activa','inactiva','intercambiada') NOT NULL DEFAULT 'activa',
  `imagen_url` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `valor_estimado` decimal(10,2) DEFAULT NULL,
  `fecha_publicacion` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `oferta`
--

INSERT INTO `oferta` (`id_oferta`, `titulo`, `descripcion`, `categoria`, `estado`, `imagen_url`, `ciudad`, `valor_estimado`, `fecha_publicacion`, `id_usuario`) VALUES
(1, 'Bicicleta MTB talla M', 'Bici en buen estado, frenos de disco', 'producto', 'activa', NULL, 'Bogotá', 350000.00, '2026-06-04 23:12:51', 6),
(2, 'Clases de inglés 2h/semana', 'Profesora certificada, nivel básico a intermedio', 'conocimiento', 'activa', NULL, 'Medellín', 80000.00, '2026-06-04 23:12:51', 2),
(3, 'Teclado mecánico RGB', 'Teclado gaming casi nuevo', 'producto', 'intercambiada', NULL, 'Cali', 220000.00, '2026-06-04 23:12:51', 3),
(4, 'Tour fotográfico urbano 3h', 'Recorrido con fotógrafo profesional', 'experiencia', 'activa', NULL, 'Bogotá', 150000.00, '2026-06-04 23:12:51', 6),
(5, 'Guitarra acústica Yamaha', 'En perfecto estado con estuche', 'producto', 'activa', NULL, 'Barranquilla', 400000.00, '2026-06-04 23:12:51', 6),
(21, 'Curso de guitarra', '15 Claces para principiantes', 'servicio', 'activa', NULL, 'Bogotá', 200000.00, '2026-06-14 21:26:31', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte`
--

CREATE TABLE `reporte` (
  `id_reporte` int(11) NOT NULL,
  `motivo` enum('fraude','incumplimiento','comportamiento','otro') NOT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('pendiente','revisado','resuelto') NOT NULL DEFAULT 'pendiente',
  `fecha_reporte` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_resolucion` datetime DEFAULT NULL,
  `id_usuario_reporta` int(11) NOT NULL,
  `id_usuario_reportado` int(11) DEFAULT NULL,
  `id_oferta` int(11) DEFAULT NULL,
  `id_trueque` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reporte`
--

INSERT INTO `reporte` (`id_reporte`, `motivo`, `descripcion`, `estado`, `fecha_reporte`, `fecha_resolucion`, `id_usuario_reporta`, `id_usuario_reportado`, `id_oferta`, `id_trueque`) VALUES
(1, 'incumplimiento', 'No se presentó al lugar acordado', 'resuelto', '2026-06-04 23:12:51', NULL, 1, 2, NULL, 1),
(2, 'fraude', 'La oferta no correspondía a la foto', 'pendiente', '2026-06-04 23:12:51', NULL, 3, NULL, 3, NULL),
(3, 'comportamiento', 'Lenguaje agresivo en el chat', 'pendiente', '2026-06-04 23:12:51', NULL, 2, 4, NULL, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud`
--

CREATE TABLE `solicitud` (
  `id_solicitud` int(11) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `estado` enum('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_respuesta` datetime DEFAULT NULL,
  `id_usuario_solicita` int(11) NOT NULL,
  `id_usuario_recibe` int(11) NOT NULL,
  `id_oferta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitud`
--

INSERT INTO `solicitud` (`id_solicitud`, `mensaje`, `estado`, `fecha_solicitud`, `fecha_respuesta`, `id_usuario_solicita`, `id_usuario_recibe`, `id_oferta`) VALUES
(1, 'Hola, me interesa tu bici. Tengo clases de inglés para ofrecer.', 'aceptada', '2026-06-04 23:12:51', NULL, 2, 1, 1),
(2, '¿Aceptas mi tour fotográfico a cambio del teclado?', 'pendiente', '2026-06-04 23:12:51', NULL, 1, 3, 3),
(3, 'Puedo ofrecerte mi teclado mecánico por la guitarra.', 'aceptada', '2026-06-04 23:12:51', NULL, 3, 4, 5),
(4, 'No tengo artículos físicos, solo servicios de diseño.', 'rechazada', '2026-06-04 23:12:51', NULL, 5, 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_usuario`
--

CREATE TABLE `tipo_usuario` (
  `id_tipo_usuario` int(20) NOT NULL,
  `nombre_tipo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_usuario`
--

INSERT INTO `tipo_usuario` (`id_tipo_usuario`, `nombre_tipo`, `descripcion`, `fecha_creacion`) VALUES
(1, 'estandar', 'Usuario que publica y propone trueques', '2026-06-04 23:12:51'),
(2, 'administrador', 'Usuario con acceso al panel de control', '2026-06-04 23:12:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trueque`
--

CREATE TABLE `trueque` (
  `id_trueque` int(11) NOT NULL,
  `estado` enum('pendiente','aceptado','completado','cancelado') NOT NULL DEFAULT 'pendiente',
  `fecha_propuesta` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_trueque` datetime DEFAULT NULL,
  `descripcion_acuerdo` text DEFAULT NULL,
  `id_usuario_propone` int(11) NOT NULL,
  `id_oferta_propone` int(11) NOT NULL,
  `id_usuario_recibe` int(11) NOT NULL,
  `id_oferta_recibe` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `trueque`
--

INSERT INTO `trueque` (`id_trueque`, `estado`, `fecha_propuesta`, `fecha_trueque`, `descripcion_acuerdo`, `id_usuario_propone`, `id_oferta_propone`, `id_usuario_recibe`, `id_oferta_recibe`) VALUES
(1, 'completado', '2026-06-04 23:12:51', '2025-02-25 10:00:00', 'Intercambio en el parque El Virrey a las 10am', 6, 1, 2, 2),
(2, 'pendiente', '2026-06-04 23:12:51', NULL, 'Por acordar lugar y hora', 6, 3, 1, 4),
(3, 'aceptado', '2026-06-04 23:12:51', '2025-03-25 15:00:00', 'Nos vemos en el centro comercial', 4, 5, 6, 3),
(4, 'cancelado', '2026-06-04 23:12:51', NULL, 'El usuario no respondió', 2, 2, 6, 1),
(5, 'pendiente', '2026-06-04 23:12:51', NULL, 'Esperando confirmación', 1, 4, 4, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `clave_acceso` varchar(255) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `reputacion` decimal(3,2) NOT NULL DEFAULT 0.00,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `id_tipo_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `correo`, `clave_acceso`, `telefono`, `ciudad`, `foto_perfil`, `reputacion`, `fecha_registro`, `activo`, `id_tipo_usuario`) VALUES
(1, 'Laura Gómez', 'laura@correo.com', '$2b$10$xH...', '3001234567', 'Bogotá', NULL, 4.80, '2026-06-04 23:12:51', 1, 1),
(2, 'Carlos Rueda', 'carlos@correo.com', '$2b$10$aK...', '3109876543', 'Medellín', NULL, 4.60, '2026-06-04 23:12:51', 1, 1),
(3, 'Sofía Martínez', 'sofia@correo.com', '$2b$10$pL...', '3201122334', 'Cali', NULL, 4.90, '2026-06-04 23:12:51', 1, 1),
(4, 'Jhon Pérez', 'jhon@correo.com', '$2b$10$mQ...', '3154455667', 'Barranquilla', NULL, 3.50, '2026-06-04 23:12:51', 1, 1),
(5, 'Arnaldo Montiel', 'arnaldo@trueque.com', '$2b$10$adm..', '3008887766', 'Bogotá', NULL, 5.00, '2026-06-04 23:12:51', 1, 2),
(6, 'Gerson Lopez Oviedo', 'lopezoviedogersonjonnathan@gmail.com', '$2y$10$u8G3PQsFaGMo7RdBQfSiEuEFN7JnBqSjkNEhf3mJIhBJMDHdM2Kly', NULL, 'Bogotá', NULL, 0.00, '2026-06-06 23:03:26', 1, 1),
(7, 'Gerson Lopez', 'gerson@trueque.com', '$2y$10$uS2KTBo50eOHiTdzVyWrL.AJM1l58aJfZ3p4nokWOYemUe2hYPlBS', NULL, 'Bogotá', NULL, 0.00, '2026-06-08 00:59:15', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_oferta`
--

CREATE TABLE `usuario_oferta` (
  `id_usuario` int(11) NOT NULL,
  `id_oferta` int(11) NOT NULL,
  `fecha_guardado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario_oferta`
--

INSERT INTO `usuario_oferta` (`id_usuario`, `id_oferta`, `fecha_guardado`) VALUES
(1, 2, '2026-06-04 23:12:51'),
(1, 5, '2026-06-04 23:12:51'),
(2, 4, '2026-06-04 23:12:51'),
(3, 1, '2026-06-04 23:12:51'),
(4, 2, '2026-06-04 23:12:51'),
(6, 3, '2026-06-07 04:26:57');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `uq_correo_admin` (`correo`);

--
-- Indices de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD PRIMARY KEY (`id_evaluacion`),
  ADD KEY `fk_eval_usuario` (`id_usuario`),
  ADD KEY `fk_eval_trueque` (`id_trueque`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `fk_noti_usuario` (`id_usuario`),
  ADD KEY `fk_noti_trueque` (`id_trueque`),
  ADD KEY `fk_noti_oferta` (`id_oferta`);

--
-- Indices de la tabla `oferta`
--
ALTER TABLE `oferta`
  ADD PRIMARY KEY (`id_oferta`),
  ADD KEY `fk_oferta_usuario` (`id_usuario`);

--
-- Indices de la tabla `reporte`
--
ALTER TABLE `reporte`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_rep_ureporta` (`id_usuario_reporta`),
  ADD KEY `fk_rep_ureportado` (`id_usuario_reportado`),
  ADD KEY `fk_rep_trueque` (`id_trueque`),
  ADD KEY `fk_rep_oferta` (`id_oferta`);

--
-- Indices de la tabla `solicitud`
--
ALTER TABLE `solicitud`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `fk_sol_usolicita` (`id_usuario_solicita`),
  ADD KEY `fk_sol_urecibe` (`id_usuario_recibe`),
  ADD KEY `fk_sol_oferta` (`id_oferta`);

--
-- Indices de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  ADD PRIMARY KEY (`id_tipo_usuario`),
  ADD UNIQUE KEY `uq_nombre_tipo` (`nombre_tipo`);

--
-- Indices de la tabla `trueque`
--
ALTER TABLE `trueque`
  ADD PRIMARY KEY (`id_trueque`),
  ADD KEY `fk_trueque_upropone` (`id_usuario_propone`),
  ADD KEY `fk_trueque_urecibe` (`id_usuario_recibe`),
  ADD KEY `fk_trueque_opropone` (`id_oferta_propone`),
  ADD KEY `fk_trueque_orecibe` (`id_oferta_recibe`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uq_correo_usuario` (`correo`),
  ADD KEY `fk_usuario_tipo` (`id_tipo_usuario`);

--
-- Indices de la tabla `usuario_oferta`
--
ALTER TABLE `usuario_oferta`
  ADD PRIMARY KEY (`id_usuario`,`id_oferta`),
  ADD KEY `fk_uo_oferta` (`id_oferta`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  MODIFY `id_evaluacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `oferta`
--
ALTER TABLE `oferta`
  MODIFY `id_oferta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `reporte`
--
ALTER TABLE `reporte`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `solicitud`
--
ALTER TABLE `solicitud`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  MODIFY `id_tipo_usuario` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `trueque`
--
ALTER TABLE `trueque`
  MODIFY `id_trueque` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD CONSTRAINT `fk_eval_trueque` FOREIGN KEY (`id_trueque`) REFERENCES `trueque` (`id_trueque`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_eval_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `fk_noti_oferta` FOREIGN KEY (`id_oferta`) REFERENCES `oferta` (`id_oferta`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_noti_trueque` FOREIGN KEY (`id_trueque`) REFERENCES `trueque` (`id_trueque`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_noti_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `oferta`
--
ALTER TABLE `oferta`
  ADD CONSTRAINT `fk_oferta_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `reporte`
--
ALTER TABLE `reporte`
  ADD CONSTRAINT `fk_rep_oferta` FOREIGN KEY (`id_oferta`) REFERENCES `oferta` (`id_oferta`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rep_trueque` FOREIGN KEY (`id_trueque`) REFERENCES `trueque` (`id_trueque`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rep_ureporta` FOREIGN KEY (`id_usuario_reporta`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rep_ureportado` FOREIGN KEY (`id_usuario_reportado`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitud`
--
ALTER TABLE `solicitud`
  ADD CONSTRAINT `fk_sol_oferta` FOREIGN KEY (`id_oferta`) REFERENCES `oferta` (`id_oferta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sol_urecibe` FOREIGN KEY (`id_usuario_recibe`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sol_usolicita` FOREIGN KEY (`id_usuario_solicita`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `trueque`
--
ALTER TABLE `trueque`
  ADD CONSTRAINT `fk_trueque_opropone` FOREIGN KEY (`id_oferta_propone`) REFERENCES `oferta` (`id_oferta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trueque_orecibe` FOREIGN KEY (`id_oferta_recibe`) REFERENCES `oferta` (`id_oferta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trueque_upropone` FOREIGN KEY (`id_usuario_propone`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trueque_urecibe` FOREIGN KEY (`id_usuario_recibe`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_tipo` FOREIGN KEY (`id_tipo_usuario`) REFERENCES `tipo_usuario` (`id_tipo_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_oferta`
--
ALTER TABLE `usuario_oferta`
  ADD CONSTRAINT `fk_uo_oferta` FOREIGN KEY (`id_oferta`) REFERENCES `oferta` (`id_oferta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_uo_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
