-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-11-2024 a las 06:21:26
-- Versión del servidor: 10.4.27-MariaDB
-- Versión de PHP: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `educacion2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area`
--

CREATE TABLE `area` (
  `ID_Area` int(11) NOT NULL,
  `Area` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones`
--

CREATE TABLE `asignaciones` (
  `ID_Area` int(11) NOT NULL,
  `ID_Asignacion` int(11) NOT NULL,
  `ID_Grado` int(11) NOT NULL,
  `ID_Profesor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bimestres`
--

CREATE TABLE `bimestres` (
  `Id_bimestre` int(5) NOT NULL,
  `Nombre_bimestre` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

CREATE TABLE `calificaciones` (
  `ID_calificacion` int(11) NOT NULL,
  `calificacion_examen` decimal(10,2) DEFAULT NULL,
  `calificacion_parcial` decimal(10,2) DEFAULT NULL,
  `IdEstudiante` int(11) DEFAULT NULL,
  `id_area` int(11) DEFAULT NULL,
  `ID_Bimestre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `circulares`
--

CREATE TABLE `circulares` (
  `ID_circular` int(11) NOT NULL,
  `Contenido` varchar(255) DEFAULT NULL,
  `FechaEnvio` date DEFAULT NULL,
  `IdUsuario` int(11) DEFAULT NULL,
  `Titulo` varchar(255) DEFAULT NULL,
  `Nivel` varchar(50) DEFAULT NULL,
  `Fecha_actividad` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante`
--

CREATE TABLE `estudiante` (
  `ID_Estudiante` int(11) NOT NULL,
  `IDGrado` int(11) DEFAULT NULL,
  `Id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grado`
--

CREATE TABLE `grado` (
  `ID_Grado` int(11) NOT NULL,
  `nivel` varchar(255) DEFAULT NULL,
  `nombre_grado` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_colegiatura`
--

CREATE TABLE `pagos_colegiatura` (
  `ID_pago` int(11) NOT NULL,
  `Estado_pago` int(11) DEFAULT NULL,
  `Fecha_pago` date DEFAULT NULL,
  `IdEstudiante` int(11) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor`
--

CREATE TABLE `profesor` (
  `ID_profesor` int(11) NOT NULL,
  `id_area` int(11) DEFAULT NULL,
  `Id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `punteos`
--

CREATE TABLE `punteos` (
  `id_punteo` int(11) NOT NULL,
  `IdEstudiante` int(11) DEFAULT NULL,
  `id_tarea` int(11) DEFAULT NULL,
  `Puntaje` decimal(10,2) DEFAULT NULL,
  `ID_Bimestre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `IDRol` int(11) NOT NULL,
  `Nombre` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `ID_Tarea` int(11) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `Fecha_entrega` date DEFAULT NULL,
  `id_area` int(11) DEFAULT NULL,
  `id_Grado` int(11) DEFAULT NULL,
  `nombre_tarea` varchar(255) DEFAULT NULL,
  `puntaje_obtener` int(11) DEFAULT NULL,
  `ID_Bimestre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `Id_usuario` int(11) NOT NULL,
  `usuario` text DEFAULT NULL,
  `Nombre` varchar(255) DEFAULT NULL,
  `Clave` varchar(255) DEFAULT NULL,
  `Correo` varchar(255) DEFAULT NULL,
  `estado` varchar(255) DEFAULT NULL,
  `id_rol` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `area`
--
ALTER TABLE `area`
  ADD PRIMARY KEY (`ID_Area`);

--
-- Indices de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD PRIMARY KEY (`ID_Area`,`ID_Grado`,`ID_Profesor`,`ID_Asignacion`);

--
-- Indices de la tabla `bimestres`
--
ALTER TABLE `bimestres`
  ADD PRIMARY KEY (`Id_bimestre`);

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`ID_calificacion`),
  ADD KEY `id_area` (`id_area`),
  ADD KEY `fk_calificaciones_estudiante` (`IdEstudiante`),
  ADD KEY `FK_Bimestres` (`ID_Bimestre`);

--
-- Indices de la tabla `circulares`
--
ALTER TABLE `circulares`
  ADD PRIMARY KEY (`ID_circular`),
  ADD KEY `fk_circulares_usuario` (`IdUsuario`);

--
-- Indices de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD PRIMARY KEY (`ID_Estudiante`),
  ADD KEY `IDGrado` (`IDGrado`),
  ADD KEY `fk_estudiante_usuario` (`Id_usuario`);

--
-- Indices de la tabla `grado`
--
ALTER TABLE `grado`
  ADD PRIMARY KEY (`ID_Grado`);

--
-- Indices de la tabla `pagos_colegiatura`
--
ALTER TABLE `pagos_colegiatura`
  ADD PRIMARY KEY (`ID_pago`),
  ADD KEY `IdEstudiante` (`IdEstudiante`);

--
-- Indices de la tabla `profesor`
--
ALTER TABLE `profesor`
  ADD PRIMARY KEY (`ID_profesor`),
  ADD KEY `id_area` (`id_area`),
  ADD KEY `fk_profesor_usuario` (`Id_usuario`);

--
-- Indices de la tabla `punteos`
--
ALTER TABLE `punteos`
  ADD PRIMARY KEY (`id_punteo`),
  ADD KEY `IdEstudiante` (`IdEstudiante`),
  ADD KEY `fk_punteo_tarea` (`id_tarea`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`IDRol`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`ID_Tarea`),
  ADD KEY `id_area` (`id_area`),
  ADD KEY `id_Grado` (`id_Grado`),
  ADD KEY `FK_Bimestretarea` (`ID_Bimestre`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`Id_usuario`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `ID_calificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD CONSTRAINT `asignaciones_ibfk_1` FOREIGN KEY (`ID_Area`) REFERENCES `area` (`ID_Area`);

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `FK_Bimestres` FOREIGN KEY (`ID_Bimestre`) REFERENCES `bimestres` (`Id_bimestre`),
  ADD CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`id_area`) REFERENCES `area` (`ID_Area`),
  ADD CONSTRAINT `fk_calificaciones_estudiante` FOREIGN KEY (`IdEstudiante`) REFERENCES `estudiante` (`ID_Estudiante`);

--
-- Filtros para la tabla `circulares`
--
ALTER TABLE `circulares`
  ADD CONSTRAINT `fk_circulares_usuario` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Id_usuario`);

--
-- Filtros para la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD CONSTRAINT `estudiante_ibfk_1` FOREIGN KEY (`IDGrado`) REFERENCES `grado` (`ID_Grado`),
  ADD CONSTRAINT `fk_estudiante_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`);

--
-- Filtros para la tabla `pagos_colegiatura`
--
ALTER TABLE `pagos_colegiatura`
  ADD CONSTRAINT `pagos_colegiatura_ibfk_1` FOREIGN KEY (`IdEstudiante`) REFERENCES `estudiante` (`ID_Estudiante`);

--
-- Filtros para la tabla `profesor`
--
ALTER TABLE `profesor`
  ADD CONSTRAINT `fk_profesor_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `usuario` (`Id_usuario`),
  ADD CONSTRAINT `profesor_ibfk_1` FOREIGN KEY (`id_area`) REFERENCES `area` (`ID_Area`);

--
-- Filtros para la tabla `punteos`
--
ALTER TABLE `punteos`
  ADD CONSTRAINT `FK_Bimestre_punteos` FOREIGN KEY (`ID_Bimestre`) REFERENCES `bimestres` (`Id_bimestre`),
  ADD CONSTRAINT `fk_punteo_tarea` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`ID_Tarea`),
  ADD CONSTRAINT `punteos_ibfk_1` FOREIGN KEY (`IdEstudiante`) REFERENCES `estudiante` (`ID_Estudiante`);

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `FK_Bimestretarea` FOREIGN KEY (`ID_Bimestre`) REFERENCES `bimestres` (`Id_bimestre`),
  ADD CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`id_area`) REFERENCES `area` (`ID_Area`),
  ADD CONSTRAINT `tareas_ibfk_2` FOREIGN KEY (`id_Grado`) REFERENCES `grado` (`ID_Grado`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`IDRol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
