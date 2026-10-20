-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: waste_recycle
-- ------------------------------------------------------
-- Server version	9.2.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `month` varchar(20) NOT NULL,
  `year` int NOT NULL,
  `tombo_no` varchar(50) DEFAULT NULL,
  `production_estimation` decimal(10,2) DEFAULT '0.00',
  `working_days` int DEFAULT '0',
  `record_date` date DEFAULT NULL,
  `from_production_theoretical` decimal(10,2) DEFAULT '0.00',
  `from_production_actual` decimal(10,2) DEFAULT '0.00',
  `balance_waste_before_crush` decimal(10,2) DEFAULT '0.00',
  `crushing_process_theoretical` decimal(10,2) DEFAULT '0.00',
  `crushing_process_actual` decimal(10,2) DEFAULT '0.00',
  `balance_waste_after_crush` decimal(10,2) DEFAULT '0.00',
  `in_theoretical` decimal(10,2) DEFAULT '0.00',
  `theoretical_powder` decimal(10,2) DEFAULT '0.00',
  `in_actual` decimal(10,2) DEFAULT '0.00',
  `actual_powder` decimal(10,2) DEFAULT '0.00',
  `out_qty` decimal(10,2) DEFAULT '0.00',
  `phoenix` decimal(10,2) DEFAULT '0.00',
  `daiko` decimal(10,2) DEFAULT '0.00',
  `balance_powder` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ave_1` double DEFAULT '0',
  `ave_2` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'',0,'',0.00,0,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'2026-09-22 01:18:22',0,0),(2,'dec 26',0,'',0.00,25,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'2026-09-23 00:50:47',0,0),(3,'September',2026,'12456',0.00,25,NULL,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,'2026-09-23 06:04:42',0,0);
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operator`
--

DROP TABLE IF EXISTS `operator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operator` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `shift` varchar(50) DEFAULT NULL,
  `output_powder` decimal(10,2) DEFAULT NULL,
  `qty_scarp` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operator`
--

LOCK TABLES `operator` WRITE;
/*!40000 ALTER TABLE `operator` DISABLE KEYS */;
INSERT INTO `operator` VALUES (1,'2026-09-17','dee','Morning',45.00,35.00);
/*!40000 ALTER TABLE `operator` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-24 13:33:34
