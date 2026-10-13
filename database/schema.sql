/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: LOGITRACK
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-6 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `Aseguradora`
--

DROP TABLE IF EXISTS `Aseguradora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Aseguradora` (
  `id_aseguradora` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `telefono_emergencia` varchar(20) DEFAULT NULL,
  `web` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_aseguradora`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Aseguradora`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Chofer`
--

DROP TABLE IF EXISTS `Chofer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Chofer` (
  `id_chofer` int(11) NOT NULL AUTO_INCREMENT,
  `legajo` varchar(20) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL DEFAULT '',
  `localidad` varchar(100) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `tipo_licencia` varchar(10) NOT NULL DEFAULT '',
  `id_sucursal` int(11) NOT NULL,
  PRIMARY KEY (`id_chofer`),
  UNIQUE KEY `legajo` (`legajo`),
  UNIQUE KEY `dni` (`dni`),
  KEY `fk_cho_suc` (`id_sucursal`),
  CONSTRAINT `fk_cho_suc` FOREIGN KEY (`id_sucursal`) REFERENCES `Sucursal` (`id_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=152 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Chofer`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Cliente`
--

DROP TABLE IF EXISTS `Cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Cliente` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_documento` varchar(10) NOT NULL,
  `documento` varchar(25) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `id_localidad` int(11) NOT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `documento` (`documento`),
  KEY `fk_cli_loc` (`id_localidad`),
  KEY `idx_cliente_documento` (`documento`),
  CONSTRAINT `fk_cli_loc` FOREIGN KEY (`id_localidad`) REFERENCES `Localidad` (`id_localidad`)
) ENGINE=InnoDB AUTO_INCREMENT=808 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Cliente`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Empleado`
--

DROP TABLE IF EXISTS `Empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Empleado` (
  `id_empleado` int(11) NOT NULL AUTO_INCREMENT,
  `legajo` varchar(50) DEFAULT NULL,
  `dni` varchar(20) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `localidad` varchar(20) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` varchar(20) DEFAULT NULL,
  `id_sucursal` int(11) NOT NULL,
  PRIMARY KEY (`id_empleado`),
  KEY `fk_emp_suc` (`id_sucursal`),
  CONSTRAINT `fk_emp_suc` FOREIGN KEY (`id_sucursal`) REFERENCES `Sucursal` (`id_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Empleado`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Envio`
--

DROP TABLE IF EXISTS `Envio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Envio` (
  `id_envio` int(11) NOT NULL AUTO_INCREMENT,
  `nro_tracking` varchar(50) NOT NULL,
  `id_remitente` int(11) NOT NULL,
  `id_destinatario` int(11) NOT NULL,
  `id_suc_origen` int(11) NOT NULL,
  `id_suc_destino` int(11) NOT NULL,
  `id_tipo_cont` int(11) NOT NULL,
  `peso_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `peso_real` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_envio`),
  UNIQUE KEY `nro_tracking` (`nro_tracking`),
  KEY `fk_env_rem` (`id_remitente`),
  KEY `fk_env_des` (`id_destinatario`),
  KEY `fk_env_suc_orig` (`id_suc_origen`),
  KEY `fk_env_suc_dest` (`id_suc_destino`),
  KEY `fk_env_cont` (`id_tipo_cont`),
  KEY `idx_envio_tracking` (`nro_tracking`),
  CONSTRAINT `fk_env_cont` FOREIGN KEY (`id_tipo_cont`) REFERENCES `Tipo_Contenido` (`id_tipo_cont`),
  CONSTRAINT `fk_env_des` FOREIGN KEY (`id_destinatario`) REFERENCES `Cliente` (`id_cliente`),
  CONSTRAINT `fk_env_rem` FOREIGN KEY (`id_remitente`) REFERENCES `Cliente` (`id_cliente`),
  CONSTRAINT `fk_env_suc_dest` FOREIGN KEY (`id_suc_destino`) REFERENCES `Sucursal` (`id_sucursal`),
  CONSTRAINT `fk_env_suc_orig` FOREIGN KEY (`id_suc_origen`) REFERENCES `Sucursal` (`id_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=2009 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Envio`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Estado_Catalogo`
--

DROP TABLE IF EXISTS `Estado_Catalogo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Estado_Catalogo` (
  `id_estado` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_estado` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Estado_Catalogo`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Historial_Estado`
--

DROP TABLE IF EXISTS `Historial_Estado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Historial_Estado` (
  `id_hist` int(11) NOT NULL AUTO_INCREMENT,
  `id_envio` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `id_sucursal_actual` int(11) DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_hist`),
  KEY `fk_hist_env` (`id_envio`),
  KEY `fk_hist_est` (`id_estado`),
  KEY `fk_hist_suc` (`id_sucursal_actual`),
  CONSTRAINT `fk_hist_env` FOREIGN KEY (`id_envio`) REFERENCES `Envio` (`id_envio`) ON DELETE CASCADE,
  CONSTRAINT `fk_hist_est` FOREIGN KEY (`id_estado`) REFERENCES `Estado_Catalogo` (`id_estado`),
  CONSTRAINT `fk_hist_suc` FOREIGN KEY (`id_sucursal_actual`) REFERENCES `Sucursal` (`id_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=6963 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Historial_Estado`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER TRG_Historial_NuevoEstado_Devuelto
AFTER INSERT ON Historial_Estado
FOR EACH ROW
BEGIN
    -- Si el empleado inserta un registro declarando que el paquete está Devuelto (ID 6)
    IF NEW.id_estado = 6 THEN
        -- El trigger viaja a la tabla Envio y le invierte el destino para que vuelva al origen
        UPDATE Envio 
        SET id_suc_destino = id_suc_origen 
        WHERE id_envio = NEW.id_envio;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `Incidente`
--

DROP TABLE IF EXISTS `Incidente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Incidente` (
  `id_incidente` int(11) NOT NULL AUTO_INCREMENT,
  `id_viaje` int(11) NOT NULL,
  `tipo_incidente` varchar(50) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_incidente`),
  KEY `fk_inc_viaje` (`id_viaje`),
  CONSTRAINT `fk_inc_viaje` FOREIGN KEY (`id_viaje`) REFERENCES `Viaje` (`id_viaje`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Incidente`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 */ /*!50003 TRIGGER trg_incidente_demora
AFTER INSERT ON Incidente
FOR EACH ROW
BEGIN
    INSERT INTO Historial_Estado (id_envio, id_estado, fecha_hora)
    SELECT
        ve.id_envio,
        3,
        NOW()
    FROM Viaje_Envio ve
    WHERE ve.id_viaje = NEW.id_viaje;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `Localidad`
--

DROP TABLE IF EXISTS `Localidad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Localidad` (
  `id_localidad` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  PRIMARY KEY (`id_localidad`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Localidad`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Rol`
--

DROP TABLE IF EXISTS `Rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Rol` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Rol`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Seguro_Envio`
--

DROP TABLE IF EXISTS `Seguro_Envio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Seguro_Envio` (
  `id_seguro` int(11) NOT NULL AUTO_INCREMENT,
  `id_envio` int(11) NOT NULL,
  `id_aseguradora` int(11) NOT NULL,
  `nro_poliza` varchar(50) DEFAULT NULL,
  `monto_asegurado` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id_seguro`),
  UNIQUE KEY `nro_poliza` (`nro_poliza`),
  KEY `fk_seg_env` (`id_envio`),
  KEY `fk_seg_aseg` (`id_aseguradora`),
  CONSTRAINT `fk_seg_aseg` FOREIGN KEY (`id_aseguradora`) REFERENCES `Aseguradora` (`id_aseguradora`),
  CONSTRAINT `fk_seg_env` FOREIGN KEY (`id_envio`) REFERENCES `Envio` (`id_envio`)
) ENGINE=InnoDB AUTO_INCREMENT=501 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Seguro_Envio`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Sucursal`
--

DROP TABLE IF EXISTS `Sucursal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Sucursal` (
  `id_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `id_localidad` int(11) NOT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id_sucursal`),
  KEY `fk_suc_loc` (`id_localidad`),
  CONSTRAINT `fk_suc_loc` FOREIGN KEY (`id_localidad`) REFERENCES `Localidad` (`id_localidad`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Sucursal`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Tipo_Contenido`
--

DROP TABLE IF EXISTS `Tipo_Contenido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Tipo_Contenido` (
  `id_tipo_cont` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `requiere_seguro` tinyint(1) DEFAULT 0,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_tipo_cont`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Tipo_Contenido`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Tipo_Vehiculo`
--

DROP TABLE IF EXISTS `Tipo_Vehiculo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Tipo_Vehiculo` (
  `id_tipo_veh` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `capacidad_kg_max` decimal(10,2) NOT NULL,
  `requiere_licencia_especial` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_tipo_veh`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Tipo_Vehiculo`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Trazabilidad_GPS`
--

DROP TABLE IF EXISTS `Trazabilidad_GPS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Trazabilidad_GPS` (
  `id_gps` int(11) NOT NULL AUTO_INCREMENT,
  `id_viaje` int(11) NOT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `velocidad_kmh` int(11) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_gps`),
  KEY `fk_gps_viaje` (`id_viaje`),
  CONSTRAINT `fk_gps_viaje` FOREIGN KEY (`id_viaje`) REFERENCES `Viaje` (`id_viaje`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2501 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Trazabilidad_GPS`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Usuario`
--

DROP TABLE IF EXISTS `Usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `id_chofer` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_empleado` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_usu_rol` (`id_rol`),
  KEY `fk_usu_cho` (`id_chofer`),
  KEY `fk_usu_cli` (`id_cliente`),
  KEY `fk_usu_emp` (`id_empleado`),
  CONSTRAINT `fk_usu_cho` FOREIGN KEY (`id_chofer`) REFERENCES `Chofer` (`id_chofer`),
  CONSTRAINT `fk_usu_cli` FOREIGN KEY (`id_cliente`) REFERENCES `Cliente` (`id_cliente`),
  CONSTRAINT `fk_usu_emp` FOREIGN KEY (`id_empleado`) REFERENCES `Empleado` (`id_empleado`),
  CONSTRAINT `fk_usu_rol` FOREIGN KEY (`id_rol`) REFERENCES `Rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=277 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Usuario`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Temporary table structure for view `VW_Viajes_Activos`
--

DROP TABLE IF EXISTS `VW_Viajes_Activos`;
/*!50001 DROP VIEW IF EXISTS `VW_Viajes_Activos`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `VW_Viajes_Activos` AS SELECT
 1 AS `id_viaje`,
  1 AS `estado_viaje`,
  1 AS `patente_vehiculo`,
  1 AS `nombre_chofer`,
  1 AS `apellido_chofer` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `Vehiculo`
--

DROP TABLE IF EXISTS `Vehiculo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Vehiculo` (
  `patente` varchar(15) NOT NULL,
  `id_tipo_veh` int(11) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(50) NOT NULL DEFAULT '',
  `id_aseguradora` int(11) DEFAULT NULL,
  `estado_vehiculo` varchar(30) DEFAULT 'Activo',
  `id_sucursal` int(11) NOT NULL,
  PRIMARY KEY (`patente`),
  KEY `fk_veh_tipo` (`id_tipo_veh`),
  KEY `fk_veh_suc` (`id_sucursal`),
  KEY `fk_vehiculo_aseguradora` (`id_aseguradora`),
  CONSTRAINT `fk_veh_suc` FOREIGN KEY (`id_sucursal`) REFERENCES `Sucursal` (`id_sucursal`),
  CONSTRAINT `fk_veh_tipo` FOREIGN KEY (`id_tipo_veh`) REFERENCES `Tipo_Vehiculo` (`id_tipo_veh`),
  CONSTRAINT `fk_vehiculo_aseguradora` FOREIGN KEY (`id_aseguradora`) REFERENCES `Aseguradora` (`id_aseguradora`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Vehiculo`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Viaje`
--

DROP TABLE IF EXISTS `Viaje`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Viaje` (
  `id_viaje` int(11) NOT NULL AUTO_INCREMENT,
  `patente` varchar(15) NOT NULL,
  `id_chofer` int(11) NOT NULL,
  `id_suc_origen` int(11) DEFAULT NULL,
  `id_suc_destino` int(11) DEFAULT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `estado_viaje` varchar(20) DEFAULT 'Planificado',
  PRIMARY KEY (`id_viaje`),
  KEY `fk_via_veh` (`patente`),
  KEY `fk_via_cho` (`id_chofer`),
  CONSTRAINT `fk_via_cho` FOREIGN KEY (`id_chofer`) REFERENCES `Chofer` (`id_chofer`),
  CONSTRAINT `fk_via_veh` FOREIGN KEY (`patente`) REFERENCES `Vehiculo` (`patente`)
) ENGINE=InnoDB AUTO_INCREMENT=508 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Viaje`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `Viaje_Envio`
--

DROP TABLE IF EXISTS `Viaje_Envio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `Viaje_Envio` (
  `id_viaje` int(11) NOT NULL,
  `id_envio` int(11) NOT NULL,
  PRIMARY KEY (`id_viaje`,`id_envio`),
  KEY `fk_ve_envio` (`id_envio`),
  CONSTRAINT `fk_ve_envio` FOREIGN KEY (`id_envio`) REFERENCES `Envio` (`id_envio`) ON DELETE CASCADE,
  CONSTRAINT `fk_ve_viaje` FOREIGN KEY (`id_viaje`) REFERENCES `Viaje` (`id_viaje`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Viaje_Envio`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Dumping events for database 'LOGITRACK'
--

--
-- Dumping routines for database 'LOGITRACK'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_Finalizar_Operatoria_Viaje` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
DELIMITER ;;
CREATE  PROCEDURE `SP_Finalizar_Operatoria_Viaje`(IN p_id_viaje INT)
BEGIN
    -- Declaramos el manejo de errores: si algo falla, hacemos ROLLBACK
    DECLARE exit handler for sqlexception
    BEGIN
        ROLLBACK;
    END;

    -- Iniciamos la transacción segura
    START TRANSACTION;

    -- 1. Marcamos el viaje como finalizado (Asumiendo que 'Finalizado' es tu estado de cierre)
    UPDATE Viaje 
    SET estado_viaje = 'Finalizado' 
    WHERE id_viaje = p_id_viaje;

    -- 2. Liberamos al chofer asociado a ese viaje (Asumiendo que tenés un campo de disponibilidad/estado)
    UPDATE Chofer c
    INNER JOIN Viaje v ON c.id_chofer = v.id_chofer
    SET c.activo = 1 -- O el estado que uses para 'Libre'
    WHERE v.id_viaje = p_id_viaje;

    -- 3. Liberamos el vehículo
    UPDATE Vehiculo ve
    INNER JOIN Viaje v ON ve.id_vehiculo = v.id_vehiculo
    SET ve.estado = 'Disponible' -- O el estado que uses en tu tabla Vehiculo
    WHERE v.id_viaje = p_id_viaje;

    -- Si todo salió bien, confirmamos los cambios en disco
    COMMIT;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `VW_Viajes_Activos`
--

/*!50001 DROP VIEW IF EXISTS `VW_Viajes_Activos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013  SQL SECURITY DEFINER */
/*!50001 VIEW `VW_Viajes_Activos` AS select `v`.`id_viaje` AS `id_viaje`,`v`.`estado_viaje` AS `estado_viaje`,`v`.`patente` AS `patente_vehiculo`,`c`.`nombre` AS `nombre_chofer`,`c`.`apellido` AS `apellido_chofer` from ((`Viaje` `v` join `Chofer` `c` on(`v`.`id_chofer` = `c`.`id_chofer`)) left join `Vehiculo` `ve` on(`v`.`patente` = `ve`.`patente`)) where `v`.`estado_viaje` = 'En camino' */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-06-30 15:24:42
