-- MySQL dump 10.13  Distrib 9.0.1, for macos14.4 (arm64)
--
-- Host: 127.0.0.1    Database: musicHeart
-- ------------------------------------------------------
-- Server version	8.3.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
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
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20241022133758','2024-10-22 13:38:07',127),('DoctrineMigrations\\Version20241022144155','2024-10-22 14:42:02',17),('DoctrineMigrations\\Version20241023081847','2024-10-23 08:18:54',32),('DoctrineMigrations\\Version20241023090044','2024-10-23 09:00:49',87),('DoctrineMigrations\\Version20241023090934','2024-10-23 09:09:39',61),('DoctrineMigrations\\Version20241023144305','2024-10-23 14:43:13',46),('DoctrineMigrations\\Version20241024090316','2024-10-24 09:03:22',27),('DoctrineMigrations\\Version20241024111538','2024-10-24 11:15:43',72),('DoctrineMigrations\\Version20241025074826','2024-10-25 07:48:33',26),('DoctrineMigrations\\Version20241025090718','2024-10-25 09:07:23',71),('DoctrineMigrations\\Version20241029143804','2024-10-29 14:38:12',26);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game`
--

DROP TABLE IF EXISTS `game`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `game` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int DEFAULT NULL,
  `end_date` datetime NOT NULL,
  `result_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_232B318C642B8210` (`admin_id`),
  CONSTRAINT `FK_232B318C642B8210` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game`
--

LOCK TABLES `game` WRITE;
/*!40000 ALTER TABLE `game` DISABLE KEYS */;
/*!40000 ALTER TABLE `game` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guess`
--

DROP TABLE IF EXISTS `guess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guess` (
  `id` int NOT NULL AUTO_INCREMENT,
  `game_id_id` int DEFAULT NULL,
  `music_url_id_id` int DEFAULT NULL,
  `guessed_participant_id_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_32D30F964D77E7D8` (`game_id_id`),
  KEY `IDX_32D30F96682A6781` (`music_url_id_id`),
  KEY `IDX_32D30F96ED76AEDB` (`guessed_participant_id_id`),
  CONSTRAINT `FK_32D30F964D77E7D8` FOREIGN KEY (`game_id_id`) REFERENCES `game` (`id`),
  CONSTRAINT `FK_32D30F96682A6781` FOREIGN KEY (`music_url_id_id`) REFERENCES `participation` (`id`),
  CONSTRAINT `FK_32D30F96ED76AEDB` FOREIGN KEY (`guessed_participant_id_id`) REFERENCES `participation` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guess`
--

LOCK TABLES `guess` WRITE;
/*!40000 ALTER TABLE `guess` DISABLE KEYS */;
/*!40000 ALTER TABLE `guess` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messenger_messages`
--

LOCK TABLES `messenger_messages` WRITE;
/*!40000 ALTER TABLE `messenger_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messenger_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participant`
--

DROP TABLE IF EXISTS `participant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participant` (
  `id` int NOT NULL AUTO_INCREMENT,
  `participation_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_id` int DEFAULT NULL,
  `patient_id` int DEFAULT NULL,
  `roles` json NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D79F6B116ACE3B73` (`participation_id`),
  KEY `IDX_D79F6B11642B8210` (`admin_id`),
  KEY `IDX_D79F6B116B899279` (`patient_id`),
  CONSTRAINT `FK_D79F6B11642B8210` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`),
  CONSTRAINT `FK_D79F6B116ACE3B73` FOREIGN KEY (`participation_id`) REFERENCES `participation` (`id`),
  CONSTRAINT `FK_D79F6B116B899279` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participant`
--

LOCK TABLES `participant` WRITE;
/*!40000 ALTER TABLE `participant` DISABLE KEYS */;
/*!40000 ALTER TABLE `participant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participation`
--

DROP TABLE IF EXISTS `participation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `game_id` int DEFAULT NULL,
  `music_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `support_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `participant_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AB55E24F9D1C3019` (`participant_id`),
  KEY `IDX_AB55E24FE48FD905` (`game_id`),
  CONSTRAINT `FK_AB55E24F9D1C3019` FOREIGN KEY (`participant_id`) REFERENCES `participant` (`id`),
  CONSTRAINT `FK_AB55E24FE48FD905` FOREIGN KEY (`game_id`) REFERENCES `game` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participation`
--

LOCK TABLES `participation` WRITE;
/*!40000 ALTER TABLE `participation` DISABLE KEYS */;
/*!40000 ALTER TABLE `participation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient`
--

DROP TABLE IF EXISTS `patient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1ADAD7EB642B8210` (`admin_id`),
  CONSTRAINT `FK_1ADAD7EB642B8210` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient`
--

LOCK TABLES `patient` WRITE;
/*!40000 ALTER TABLE `patient` DISABLE KEYS */;
/*!40000 ALTER TABLE `patient` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spotify_session`
--

DROP TABLE IF EXISTS `spotify_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spotify_session` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `participant_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4E1CF753613FECDF` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spotify_session`
--

LOCK TABLES `spotify_session` WRITE;
/*!40000 ALTER TABLE `spotify_session` DISABLE KEYS */;
INSERT INTO `spotify_session` VALUES (1,'spotify_671b4d56a20751.58391325',9),(2,'spotify_671b4d56c3bcd3.44593178',9),(3,'spotify_671b4dad9de475.21946397',9),(4,'spotify_671b4df5630663.61810319',9),(5,'spotify_671b4df58dbf12.60989790',9),(6,'spotify_671b4e370f2bc6.03178752',9),(7,'spotify_671b4e373dca12.87449431',9),(8,'spotify_671b4eb2b2fda8.51154726',9),(9,'spotify_671b4eb2d86c24.74885746',9),(10,'spotify_671b4eccc47fd1.58294988',9),(11,'spotify_671b4ecceac077.19161579',9),(12,'spotify_671b4ed64699b5.57669783',9),(13,'spotify_671b4ed66d4681.19427740',9),(14,'spotify_671b69dd77e247.42634704',10),(15,'spotify_671b69ddca27e9.19206744',10),(16,'spotify_67209cf96d4161.03949831',1),(17,'spotify_67209cf9d8dd50.35975303',1),(18,'spotify_6720f00c621ec6.92327903',2),(19,'spotify_6720f00ca999d3.43962694',2),(20,'spotify_6720f39f794826.12841097',1),(21,'spotify_6720f39fc0e434.96856273',1),(22,'spotify_6720f4a0a0c114.88518881',1),(23,'spotify_6720f4a0c10046.75515601',1),(24,'spotify_6720f7b934a496.01006486',1),(25,'spotify_6720f7b99bc184.10032373',1),(26,'spotify_6720fe93e05409.07500616',4),(27,'spotify_6720fe94226a28.71453396',4),(28,'spotify_67210e83877cf0.17651137',1),(29,'spotify_67210e83e446f9.98997390',1),(30,'spotify_672228cca9dbb2.25645770',2),(31,'spotify_672228cd063089.59778599',2),(32,'spotify_67235dd881a138.06586149',3),(33,'spotify_67235dd8db6fc9.30246501',3),(34,'spotify_67235e1e3e9c57.64737764',4),(35,'spotify_67235e1e5cbe79.05816556',4);
/*!40000 ALTER TABLE `spotify_session` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-10-31 16:11:54
