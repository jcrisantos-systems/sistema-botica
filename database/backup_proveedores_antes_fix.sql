-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: botica_db
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ruc` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `razon_social` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `representante` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruc` (`ruc`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'20546781234','Droguería Del Sur S.A.C.',NULL,'987654321','Av. Sur 123, Lima',1),(2,'20123456789','Distribuidora Continental S.A.',NULL,'912345678','Industrial 45, Lima',1),(3,'20456123789','Medifarma Distribuidores',NULL,'998877665','Km 23 Panamericana',1),(4,'20100200301','Distribuidora FarmaOriente S.A.C.','Ing. Ricardo Arana','044-203040','Lima, Santa Anita',1),(5,'20506070802','Global Medicine Per├║','Lic. Carmen Rosa','01-4556677','Av. Iquitos 455, La Victoria',1),(6,'20443322114','Laboratorios Unidos S.A.','Sr. Jorge Valdivia','01-2223344','Chorrillos, Lima',1),(7,'20998877665','Qu├¡mica Suiza S.A.','Central de Pedidos','01-2114000','Av. Paseo de la Rep├║blica',1),(8,'20112233446','Droguer├¡a Los Olivos','Sra. Martha Vilchez','044-506070','Trujillo, El Porvenir',1),(9,'10456789011','Representaciones M├®dicas P&G','Pablo Gonzales','999888777','Calle Real 123, Huancayo',1),(10,'20556677889','Importaciones San Jos├®','Jos├® Santos','01-3334455','Jr. Az├íngaro, Lima',1),(11,'20667788990','Corporaci├│n M├®dica del Norte','Lilian Ruiz','044-445566','Trujillo, Centro',1),(12,'20778899001','Per├║ Farma Log├¡stica','Mario Vargas','01-6667788','Lur├¡n, Almacenes',1),(13,'20889900112','BioTech Soluciones','Dra. Sandra Sol├¡s','977665544','Miraflores, Lima',1);
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-24  2:01:33
