-- MySQL dump 10.13  Distrib 8.4.9, for Linux (x86_64)
--
-- Host: localhost    Database: edibear
-- ------------------------------------------------------
-- Server version	8.4.9

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
-- Table structure for table `ad1_descriptions`
--

DROP TABLE IF EXISTS `ad1_descriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad1_descriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ad1_id` int NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_blog_id` (`ad1_id`),
  CONSTRAINT `FK_ad1_id` FOREIGN KEY (`ad1_id`) REFERENCES `ad1_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad1_descriptions`
--

LOCK TABLES `ad1_descriptions` WRITE;
/*!40000 ALTER TABLE `ad1_descriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad1_descriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad1_details`
--

DROP TABLE IF EXISTS `ad1_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad1_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `adlink` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad1_details`
--

LOCK TABLES `ad1_details` WRITE;
/*!40000 ALTER TABLE `ad1_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad1_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad2_descriptions`
--

DROP TABLE IF EXISTS `ad2_descriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad2_descriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ad2_id` int NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_blog_id` (`ad2_id`),
  CONSTRAINT `FK_ad2_id` FOREIGN KEY (`ad2_id`) REFERENCES `ad2_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad2_descriptions`
--

LOCK TABLES `ad2_descriptions` WRITE;
/*!40000 ALTER TABLE `ad2_descriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad2_descriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ad2_details`
--

DROP TABLE IF EXISTS `ad2_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad2_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `adlink` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ad2_details`
--

LOCK TABLES `ad2_details` WRITE;
/*!40000 ALTER TABLE `ad2_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `ad2_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_descriptions`
--

DROP TABLE IF EXISTS `blog_descriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_descriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `blog_id` int NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL,
  `image_01_caption` varchar(255) DEFAULT NULL,
  `image_02_caption` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_blog_id` (`blog_id`),
  CONSTRAINT `FK_blog_id` FOREIGN KEY (`blog_id`) REFERENCES `blog_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_descriptions`
--

LOCK TABLES `blog_descriptions` WRITE;
/*!40000 ALTER TABLE `blog_descriptions` DISABLE KEYS */;
INSERT INTO `blog_descriptions` VALUES (27,7,'Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius.Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius.Sed pretium est eget accumsan varius. Quisque vitae mi eget arcu eleifend eleifend eget nec dui. Quisque vel maximus lacus. Vestibulum vehicula eget mauris a consectetur. Curabitur ac gravida libero. Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius.','7-1-1.jpg','7-1-2.jpg','Image - 01','Image - 02'),(28,7,'Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius.Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius.Sed pretium est eget accumsan varius. Quisque vitae mi eget arcu eleifend eleifend eget nec dui. Quisque vel maximus lacus. Vestibulum vehicula eget mauris a consectetur. Curabitur ac gravida libero. Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius.','7-2-1.jpg','7-2-2.jpg','Image - 03','Image - 04');
/*!40000 ALTER TABLE `blog_descriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_details`
--

DROP TABLE IF EXISTS `blog_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_details`
--

LOCK TABLES `blog_details` WRITE;
/*!40000 ALTER TABLE `blog_details` DISABLE KEYS */;
INSERT INTO `blog_details` VALUES (1,'||| |||  ||| Educational','Test Blog Title','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi non urna vitae nulla hendrerit vestibulum. Ut ut bibendum massa, at ullamcorper nisl. Cras vel purus consequat, tincidunt mauris id, tristique sapien. Mauris ac felis elementum massa mattis venenatis tristique ac ligula. Aliquam posuere, erat quis consequat dignissim, elit urna cursus dui, ut eleifend diam odio id enim. Sed nec imperdiet ex, scelerisque pharetra dui. Integer vel risus lacinia, tincidunt dolor at, egestas nibh. Cras faucibus tempor turpis, eu aliquam eros lobortis nec.\r\n\r\nPhasellus vel maximus nisl, eu blandit est. Integer eu lorem vulputate, pellentesque nibh eget, convallis urna. Mauris aliquet erat a lobortis pellentesque. Quisque tortor elit, ultricies in ipsum ut, eleifend aliquet magna. Vestibulum consectetur lacinia eros ac iaculis. Curabitur tempor sollicitudin arcu a pellentesque. Pellentesque quis orci sit amet velit fermentum sagittis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras quis diam justo. Vestibulum varius odio non leo varius, at molestie tortor volutpat. Aliquam erat volutpat. Duis dictum eleifend odio.\r\n\r\nDuis id ex ut arcu fermentum maximus in vitae nibh. Donec luctus ligula urna, sed imperdiet enim congue ac. Fusce vehicula est vitae quam luctus, sit amet dignissim mi cursus. Nulla pharetra posuere mauris, ut cursus turpis tempus id. Proin at lacinia urna. Nunc cursus arcu eget porta lobortis. Morbi urna ligula, molestie a lobortis et, dignissim nec felis. In pharetra metus augue, vel vehicula enim rhoncus dignissim. Sed pretium est eget accumsan varius. Quisque vitae mi eget arcu eleifend eleifend eget nec dui. Quisque vel maximus lacus.\r\n\r\nVestibulum vehicula eget mauris a consectetur. Curabitur ac gravida libero. Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Morbi arcu libero, molestie a nulla a, dapibus tristique erat. Suspendisse et enim eget dui ullamcorper ultrices vitae vel eros. Fusce ex nulla, efficitur molestie tincidunt id, pellentesque non leo. Quisque sed purus lorem. Nulla ipsum metus, efficitur a elit at, congue dapibus metus. Sed faucibus lobortis nibh, id fermentum enim bibendum ac.\r\n\r\nFusce eget interdum nunc. Mauris scelerisque tortor eros, eget facilisis odio hendrerit sit amet. Nullam vehicula nisi nibh, a cursus lacus vulputate a. Donec sagittis augue et ante pretium semper. Praesent accumsan, neque vel tristique gravida, ex erat sodales mi, vitae lobortis libero enim non dui. Quisque rutrum ut dui a convallis. Nam consectetur arcu risus, quis lobortis lectus dapibus vitae. Vestibulum pharetra sapien in velit varius, vitae venenatis velit gravida. Praesent tellus justo, vehicula molestie vehicula a, ultricies et eros. Aliquam interdum viverra hendrerit. Etiam nulla nisl, suscipit ut venenatis eget, placerat quis nulla. In interdum, risus accumsan maximus blandit, velit odio aliquam lorem, in sodales odio turpis nec eros. Nulla rutrum turpis tellus, et sagittis dui lacinia sed. Ut commodo iaculis dui, at interdum nisi interdum a. Quisque rutrum eros eget semper pulvinar.','1.JPG','',0,1,'2026-04-20 14:04:16'),(2,'||| |||  ||| Problem Solving','Test Blog Title 2','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi non urna vitae nulla hendrerit vestibulum. Ut ut bibendum massa, at ullamcorper nisl. Cras vel purus consequat, tincidunt mauris id, tristique sapien. Mauris ac felis elementum massa mattis venenatis tristique ac ligula. Aliquam posuere, erat quis consequat dignissim, elit urna cursus dui, ut eleifend diam odio id enim. Sed nec imperdiet ex, scelerisque pharetra dui. Integer vel risus lacinia, tincidunt dolor at, egestas nibh. Cras faucibus tempor turpis, eu aliquam eros lobortis nec. Phasellus vel maximus nisl, eu blandit est. Integer eu lorem vulputate, pellentesque nibh eget, convallis urna. Mauris aliquet erat a lobortis pellentesque. Quisque tortor elit, ultricies in ipsum ut, eleifend aliquet magna. Vestibulum consectetur lacinia eros ac iaculis. Curabitur tempor sollicitudin arcu a pellentesque. Pellentesque quis orci sit amet velit fermentum sagittis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras quis diam justo. Vestibulum varius odio non leo varius, at molestie tortor volutpat. Aliquam erat volutpat. Duis dictum eleifend odio. Duis id ex ut arcu fermentum maximus in vitae nibh. Donec luctus ligula urna, sed imperdiet enim congue ac. Fusce vehicula est vitae quam luctus, sit amet dignissim mi cursus. Nulla pharetra posuere mauris, ut cursus turpis tempus id. Proin at lacinia urna. Nunc cursus arcu eget porta lobortis. Morbi urna ligula, molestie a lobortis et, dignissim nec felis. In pharetra metus augue, vel vehicula enim rhoncus dignissim. Sed pretium est eget accumsan varius. Quisque vitae mi eget arcu eleifend eleifend eget nec dui. Quisque vel maximus lacus. Vestibulum vehicula eget mauris a consectetur. Curabitur ac gravida libero. Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Morbi arcu libero, molestie a nulla a, dapibus tristique erat. Suspendisse et enim eget dui ullamcorper ultrices vitae vel eros. Fusce ex nulla, efficitur molestie tincidunt id, pellentesque non leo. Quisque sed purus lorem. Nulla ipsum metus, efficitur a elit at, congue dapibus metus. Sed faucibus lobortis nibh, id fermentum enim bibendum ac. Fusce eget interdum nunc. Mauris scelerisque tortor eros, eget facilisis odio hendrerit sit amet. Nullam vehicula nisi nibh, a cursus lacus vulputate a. Donec sagittis augue et ante pretium semper. Praesent accumsan, neque vel tristique gravida, ex erat sodales mi, vitae lobortis libero enim non dui. Quisque rutrum ut dui a convallis. Nam consectetur arcu risus, quis lobortis lectus dapibus vitae. Vestibulum pharetra sapien in velit varius, vitae venenatis velit gravida. Praesent tellus justo, vehicula molestie vehicula a, ultricies et eros. Aliquam interdum viverra hendrerit. Etiam nulla nisl, suscipit ut venenatis eget, placerat quis nulla. In interdum, risus accumsan maximus blandit, velit odio aliquam lorem, in sodales odio turpis nec eros. Nulla rutrum turpis tellus, et sagittis dui lacinia sed. Ut commodo iaculis dui, at interdum nisi interdum a. Quisque rutrum eros eget semper pulvinar.','2.jpg','',0,1,'2026-04-20 16:49:30'),(3,'||| |||  ||| Thinking','Test Blog Title 3','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi non urna vitae nulla hendrerit vestibulum. Ut ut bibendum massa, at ullamcorper nisl. Cras vel purus consequat, tincidunt mauris id, tristique sapien. Mauris ac felis elementum massa mattis venenatis tristique ac ligula. Aliquam posuere, erat quis consequat dignissim, elit urna cursus dui, ut eleifend diam odio id enim. Sed nec imperdiet ex, scelerisque pharetra dui. Integer vel risus lacinia, tincidunt dolor at, egestas nibh. Cras faucibus tempor turpis, eu aliquam eros lobortis nec. Phasellus vel maximus nisl, eu blandit est. Integer eu lorem vulputate, pellentesque nibh eget, convallis urna. Mauris aliquet erat a lobortis pellentesque. Quisque tortor elit, ultricies in ipsum ut, eleifend aliquet magna. Vestibulum consectetur lacinia eros ac iaculis. Curabitur tempor sollicitudin arcu a pellentesque. Pellentesque quis orci sit amet velit fermentum sagittis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras quis diam justo. Vestibulum varius odio non leo varius, at molestie tortor volutpat. Aliquam erat volutpat. Duis dictum eleifend odio. Duis id ex ut arcu fermentum maximus in vitae nibh. Donec luctus ligula urna, sed imperdiet enim congue ac. Fusce vehicula est vitae quam luctus, sit amet dignissim mi cursus. Nulla pharetra posuere mauris, ut cursus turpis tempus id. Proin at lacinia urna. Nunc cursus arcu eget porta lobortis. Morbi urna ligula, molestie a lobortis et, dignissim nec felis. In pharetra metus augue, vel vehicula enim rhoncus dignissim. Sed pretium est eget accumsan varius. Quisque vitae mi eget arcu eleifend eleifend eget nec dui. Quisque vel maximus lacus. Vestibulum vehicula eget mauris a consectetur. Curabitur ac gravida libero. Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius. ','1776831879.jpg','',0,1,'2026-04-22 04:24:39'),(4,'||| |||  ||| Creativity','Test Blog Title 4','Quisque tortor elit, ultricies in ipsum ut, eleifend aliquet magna. Vestibulum consectetur lacinia eros ac iaculis. Curabitur tempor sollicitudin arcu a pellentesque. Pellentesque quis orci sit amet velit fermentum sagittis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras quis diam justo. Vestibulum varius odio non leo varius, at molestie tortor volutpat. Aliquam erat volutpat. Duis dictum eleifend odio. Duis id ex ut arcu fermentum maximus in vitae nibh. Donec luctus ligula urna, sed imperdiet enim congue ac. Fusce vehicula est vitae quam luctus, sit amet dignissim mi cursus. Nulla pharetra posuere mauris, ut cursus turpis tempus id. Proin at lacinia urna. Nunc cursus arcu eget porta lobortis. Morbi urna ligula, molestie a lobortis et, dignissim nec felis. In pharetra metus augue, vel vehicula enim rhoncus dignissim. Sed pretium est eget accumsan varius. Quisque vitae mi eget arcu eleifend eleifend eget nec dui. Quisque vel maximus lacus. Vestibulum vehicula eget mauris a consectetur. Curabitur ac gravida libero. Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius.','1776833038.jpg','',0,1,'2026-04-22 04:43:58'),(5,'English ||| Grade 1 ||| Creativity','Test Blog Title 5','Quisque tortor elit, ultricies in ipsum ut, eleifend aliquet magna. Vestibulum consectetur lacinia eros ac iaculis. Curabitur tempor sollicitudin arcu a pellentesque. Pellentesque quis orci sit amet velit fermentum sagittis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras quis diam justo. Vestibulum varius odio non leo varius, at molestie tortor volutpat. Aliquam erat volutpat. Duis dictum eleifend odio. Duis id ex ut arcu fermentum maximus in vitae nibh. Donec luctus ligula urna, sed imperdiet enim congue ac. Fusce vehicula est vitae quam luctus, sit amet dignissim mi cursus. Nulla pharetra posuere mauris, ut cursus turpis tempus id. Proin at lacinia urna. Nunc cursus arcu eget porta lobortis. Morbi urna ligula, molestie a lobortis et, dignissim nec felis. In pharetra metus augue, vel vehicula enim rhoncus dignissim. Sed pretium est eget accumsan varius. Quisque vitae mi eget arcu eleifend eleifend eget nec dui. Quisque vel maximus lacus. Vestibulum vehicula eget mauris a consectetur. Curabitur ac gravida libero. Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius.','1776833059.jpg','',0,1,'2026-04-22 04:44:19'),(7,'English ||| Grade 2 ||| Hand craft','CUTE DOG FACE','Quisque tortor elit, ultricies in ipsum ut, eleifend aliquet magna. Vestibulum consectetur lacinia eros ac iaculis. Curabitur tempor sollicitudin arcu a pellentesque. Pellentesque quis orci sit amet velit fermentum sagittis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras quis diam justo. Vestibulum varius odio non leo varius, at molestie tortor volutpat. Aliquam erat volutpat. Duis dictum eleifend odio. Duis id ex ut arcu fermentum maximus in vitae nibh. Donec luctus ligula urna, sed imperdiet enim congue ac. Fusce vehicula est vitae quam luctus, sit amet dignissim mi cursus. Nulla pharetra posuere mauris, ut cursus turpis tempus id. Proin at lacinia urna. Nunc cursus arcu eget porta lobortis. Morbi urna ligula, molestie a lobortis et, dignissim nec felis. In pharetra metus augue, vel vehicula enim rhoncus dignissim.  <br><br>\r\n\r\nQuisque tortor elit, ultricies in ipsum ut, eleifend aliquet magna. Vestibulum consectetur lacinia eros ac iaculis. Curabitur tempor sollicitudin arcu a pellentesque. Pellentesque quis orci sit amet velit fermentum sagittis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras quis diam justo. Vestibulum varius odio non leo varius, at molestie tortor volutpat. Aliquam erat volutpat. Duis dictum eleifend odio. Duis id ex ut arcu fermentum maximus in vitae nibh. Donec luctus ligula urna, sed imperdiet enim congue ac. Fusce vehicula est vitae quam luctus, sit amet dignissim mi cursus. Nulla pharetra posuere mauris, ut cursus turpis tempus id. Proin at lacinia urna. Nunc cursus arcu eget porta lobortis. Morbi urna ligula, molestie a lobortis et, dignissim nec felis. In pharetra metus augue, vel vehicula enim rhoncus dignissim. ','7.jpg','https://www.youtube.com/watch?v=oNroQ-V0EkA',1,1,'2026-05-04 17:33:54');
/*!40000 ALTER TABLE `blog_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_extra_media`
--

DROP TABLE IF EXISTS `blog_extra_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_extra_media` (
  `id` int NOT NULL AUTO_INCREMENT,
  `blog_id` int NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `media_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'image',
  `path` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_blog_extra_media_blog` (`blog_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_extra_media`
--

LOCK TABLES `blog_extra_media` WRITE;
/*!40000 ALTER TABLE `blog_extra_media` DISABLE KEYS */;
INSERT INTO `blog_extra_media` VALUES (11,7,0,'image','7-extra-1778645017-0.webp','Final');
/*!40000 ALTER TABLE `blog_extra_media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `books_descriptions`
--

DROP TABLE IF EXISTS `books_descriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `books_descriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `books_id` int NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index` (`books_id`),
  CONSTRAINT `FK_books_id` FOREIGN KEY (`books_id`) REFERENCES `books_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `books_descriptions`
--

LOCK TABLES `books_descriptions` WRITE;
/*!40000 ALTER TABLE `books_descriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `books_descriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `books_details`
--

DROP TABLE IF EXISTS `books_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `books_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pdfupload` varchar(20) NOT NULL,
  `download_count` int NOT NULL,
  `language_id` int DEFAULT NULL,
  `grade_id` int DEFAULT NULL,
  `main_cat_id` int DEFAULT NULL,
  `sub_cat_id` int DEFAULT NULL,
  `product_category_id` int DEFAULT NULL COMMENT 'shop category',
  `product_subcategory_id` int DEFAULT NULL COMMENT 'shop subcategory',
  `ws_category_id` int unsigned DEFAULT NULL,
  `ws_subcategory_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_language` (`language_id`),
  KEY `fk_grade` (`grade_id`),
  KEY `fk_main_cat` (`main_cat_id`),
  KEY `fk_sub_cat` (`sub_cat_id`),
  CONSTRAINT `fk_grade` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`),
  CONSTRAINT `fk_language` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `fk_main_cat` FOREIGN KEY (`main_cat_id`) REFERENCES `main_category` (`id`),
  CONSTRAINT `fk_sub_cat` FOREIGN KEY (`sub_cat_id`) REFERENCES `sub_category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `books_details`
--

LOCK TABLES `books_details` WRITE;
/*!40000 ALTER TABLE `books_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `books_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `braveheart_categories`
--

DROP TABLE IF EXISTS `braveheart_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `braveheart_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `braveheart_categories`
--

LOCK TABLES `braveheart_categories` WRITE;
/*!40000 ALTER TABLE `braveheart_categories` DISABLE KEYS */;
INSERT INTO `braveheart_categories` VALUES (1,'Drawing',1,'2026-04-21 07:09:30'),(2,'Singing',1,'2026-05-03 15:14:08');
/*!40000 ALTER TABLE `braveheart_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `braveheart_events`
--

DROP TABLE IF EXISTS `braveheart_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `braveheart_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `main_image` varchar(100) DEFAULT NULL,
  `application_file` varchar(100) DEFAULT NULL,
  `deadline_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `braveheart_events`
--

LOCK TABLES `braveheart_events` WRITE;
/*!40000 ALTER TABLE `braveheart_events` DISABLE KEYS */;
INSERT INTO `braveheart_events` VALUES (1,1,'Sinhala And Tamil New Year Drawing Competition','<p>The Kids Art Competition &ndash; 2026, themed &ldquo;Sinhala &amp; Tamil New Year,&rdquo; was organized to encourage creativity, imagination, and cultural appreciation among young children. The competition provided a joyful platform for preschool and school students aged 2&frac12; to 10 years to express their understanding of New Year traditions, values, and celebrations through art. We sincerely appreciate the support of parents, teachers, and guardians who encouraged children to participate while ensuring that every artwork remained the child&rsquo;s own original creation. This initiative aims to nurture artistic skills, confidence, and cultural awareness while celebrating the true spirit of the Sinhala &amp; Tamil New Year.</p>\r\n\r\n<p>* COMPETITION TITLE - SINHALA &amp; TAMIL NEW YEAR<br />\r\n* COMPETITION PERIOD - 01 APRIL 2026 &ndash; 25 APRIL 2026<br />\r\n* AGE LIMIT - CATEGORY A: 2&frac12; &ndash; 4 YEARS / CATEGORY B: 5 &ndash; 7 YEARS / CATEGORY C: 8 &ndash; 10 YEARS&nbsp;<br />\r\n* MEDIUM - PASTEL / WATER COLOUR / OIL PAINT / POSTER COLOUR&nbsp;<br />\r\n* PAPER SIZE - A4 SIZE&nbsp;<br />\r\n* SUBMISSION DEADLINE - On or before 25 April 2026<br />\r\n* POSTAL ADDRESS - Edibear, No.11, Ideal Complex, Colombo Road, Gampaha.<br />\r\n* PRIZE - 1st Place: Rs. 5,000 Gift Voucher + Certificate &nbsp;/ &nbsp;2nd &amp; 3rd Places : Valuable Certificate + Special Gift</p>\r\n\r\n<p>The following details must be clearly written on the back of the artwork:<br />\r\n01. Child&rsquo;s full name<br />\r\n02. Age &amp; date of birth<br />\r\n03. School / Preschool name<br />\r\n04. Parent / Guardian name<br />\r\n05. Contact number<br />\r\n06. Facebook profile name (for voting)</p>\r\n\r\n<p>Terms &amp; Conditions</p>\r\n\r\n<p>* The competition is open to preschool and school students aged 2&frac12; &ndash; 10 years only.<br />\r\n* Each child may submit only ONE artwork.<br />\r\n* Every artwork must be the child&rsquo;s own original creation, completed without assistance from parents, teachers, or others.<br />\r\n* Digital Paintings, Tracing, copying, AI-generated, or previously published artwork will not be accepted and will result in disqualification.<br />\r\n* Artwork must be related to the Sinhala &amp; Tamil New Year theme.<br />\r\n* Drawings must be done on A4 size paper only using the approved media.<br />\r\n* Paintings must be sent by registered post on or before 25 April 2026.<br />\r\n* The artwork must not be folded or rolled. Use an A4-size envelope for submission.<br />\r\n* The application form must be correctly filled and sent together with the artwork.<br />\r\n* Incomplete applications, late submissions, or failure to follow rules will result in disqualification.<br />\r\n* Prizes Will Be Awarded Per Age Category<br />\r\n* Submitted artworks will NOT be returned.<br />\r\n* By submitting an entry, the participant and parent / guardian grant permission to use the artwork for exhibitions, promotions, publications, and social media with due credit to the child artist. No compensation will be provided.</p>\r\n\r\n<p>Winner Selection &amp; Announcement&nbsp;</p>\r\n\r\n<p>* Winners of the Kids Art Competition will be selected through a Facebook public voting process conducted on the official Facebook page.<br />\r\n* Each submitted artwork will be posted on the official Facebook page.<br />\r\n* Likes and Comments received during the voting period will be counted.<br />\r\n* Shares will not be counted.<br />\r\n* Any use of fake accounts or unfair methods will result in disqualification.<br />\r\n* In case of a tie, the organizers&rsquo; decision will be final.<br />\r\n* Winning children&rsquo;s names, age categories, and artwork images will be published on Official Website Blog &amp; Official Facebook Page<br />\r\n* Winning artworks will be displayed with due credit given to the child artist.<br />\r\n* The decision of the organizers will be final.<br />\r\n&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />\r\n&nbsp; &nbsp;</p>\r\n','1.png','1-application.pdf','2026-05-30',1,'2026-04-21 07:09:30'),(3,2,'Mothers\' Day Singing Competition','<p>Quisque tortor elit, ultricies in ipsum ut, eleifend aliquet magna. Vestibulum consectetur lacinia eros ac iaculis. Curabitur tempor sollicitudin arcu a pellentesque. Pellentesque quis orci sit amet velit fermentum sagittis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras quis diam justo. Vestibulum varius odio non leo varius, at molestie tortor volutpat. Aliquam erat volutpat. Duis dictum eleifend odio. Duis id ex ut arcu fermentum maximus in vitae nibh. Donec luctus ligula urna, sed imperdiet enim congue ac. Fusce vehicula est vitae quam luctus, sit amet dignissim mi cursus. Nulla pharetra posuere mauris, ut cursus turpis tempus id. Proin at lacinia urna. Nunc cursus arcu eget porta lobortis. Morbi urna ligula, molestie a lobortis et, dignissim nec felis. In pharetra metus augue, vel vehicula enim rhoncus dignissim. Sed pretium est eget accumsan varius. Quisque vitae mi eget arcu eleifend eleifend eget nec dui. Quisque vel maximus lacus. Vestibulum vehicula eget mauris a consectetur. Curabitur ac gravida libero. Duis hendrerit volutpat massa, a porttitor risus condimentum ac. Curabitur lobortis nisi at arcu vulputate tincidunt. Nam accumsan turpis vitae nibh dapibus pulvinar. Duis molestie lectus ultrices consectetur varius.</p>\r\n','3.png','3-application.pdf','2026-05-08',1,'2026-05-03 15:14:08');
/*!40000 ALTER TABLE `braveheart_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `braveheart_winners`
--

DROP TABLE IF EXISTS `braveheart_winners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `braveheart_winners` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `image` varchar(100) NOT NULL,
  `position` int DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_braveheart_event` (`event_id`),
  CONSTRAINT `fk_braveheart_event` FOREIGN KEY (`event_id`) REFERENCES `braveheart_events` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `braveheart_winners`
--

LOCK TABLES `braveheart_winners` WRITE;
/*!40000 ALTER TABLE `braveheart_winners` DISABLE KEYS */;
INSERT INTO `braveheart_winners` VALUES (22,1,'First Place - Tharindu Perera','1-winner-1.png',1,1,'2026-05-21 01:41:32'),(23,1,'Second Place - Kavindu Perera','1-winner-2.png',2,1,'2026-05-21 01:41:32'),(24,1,'Third Place -Devin Cooray','1-winner-3.png',3,1,'2026-05-21 01:41:32');
/*!40000 ALTER TABLE `braveheart_winners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carousel`
--

DROP TABLE IF EXISTS `carousel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carousel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL COMMENT 'img, video, main, explore_bg, testimonial_bg, footer_bg, ...',
  `text1` varchar(100) DEFAULT NULL,
  `text2` varchar(100) DEFAULT NULL,
  `src` varchar(100) NOT NULL,
  `display_order` int DEFAULT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carousel`
--

LOCK TABLES `carousel` WRITE;
/*!40000 ALTER TABLE `carousel` DISABLE KEYS */;
INSERT INTO `carousel` VALUES (9,'img','','','Homepage.webp',2,1),(10,'explore_bg_mobile','','','d05e79081e85ead1.webp',NULL,1),(11,'hero_mobile','','','009448ecf9ec9e45.webp',NULL,1);
/*!40000 ALTER TABLE `carousel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `session_id` varchar(128) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_cart_product` (`product_id`),
  KEY `fk_cart_user` (`user_id`),
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `tourists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
INSERT INTO `cart` VALUES (53,1,6,3,NULL,'2026-06-15 06:14:11');
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `edi_bank_details`
--

DROP TABLE IF EXISTS `edi_bank_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `edi_bank_details` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `account_number` varchar(50) NOT NULL DEFAULT '',
  `account_name` varchar(150) NOT NULL DEFAULT '',
  `bank_name` varchar(150) NOT NULL DEFAULT '',
  `branch_name` varchar(150) NOT NULL DEFAULT '',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edi_bank_details`
--

LOCK TABLES `edi_bank_details` WRITE;
/*!40000 ALTER TABLE `edi_bank_details` DISABLE KEYS */;
INSERT INTO `edi_bank_details` VALUES (1,'1000400531','EDIBEAR (PRIVATE) LIMITED','COMMERCIAL BANK','GAMPAHA BRANCH','2026-06-10 10:25:51');
/*!40000 ALTER TABLE `edi_bank_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `edi_shipping_districts`
--

DROP TABLE IF EXISTS `edi_shipping_districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `edi_shipping_districts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fee_lkr` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Added on top of weight-tier fee; match is case-insensitive on name',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edi_shipping_districts`
--

LOCK TABLES `edi_shipping_districts` WRITE;
/*!40000 ALTER TABLE `edi_shipping_districts` DISABLE KEYS */;
INSERT INTO `edi_shipping_districts` VALUES (1,'Colombo',300.00),(2,'Gampaha',300.00),(3,'Kandy',350.00),(4,'Other / not listed',0.00);
/*!40000 ALTER TABLE `edi_shipping_districts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `edi_shipping_weight_tiers`
--

DROP TABLE IF EXISTS `edi_shipping_weight_tiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `edi_shipping_weight_tiers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `max_weight_kg` decimal(12,4) DEFAULT NULL COMMENT 'Cart total kg up to and including this; NULL = unlimited (catch-all)',
  `fee_lkr` decimal(12,2) NOT NULL DEFAULT '0.00',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_sort` (`sort_order`),
  KEY `idx_max` (`max_weight_kg`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edi_shipping_weight_tiers`
--

LOCK TABLES `edi_shipping_weight_tiers` WRITE;
/*!40000 ALTER TABLE `edi_shipping_weight_tiers` DISABLE KEYS */;
INSERT INTO `edi_shipping_weight_tiers` VALUES (1,1.0000,0.00,0),(2,2.0000,50.00,0),(3,5.0000,100.00,0);
/*!40000 ALTER TABLE `edi_shipping_weight_tiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `edi_vouchers`
--

DROP TABLE IF EXISTS `edi_vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `edi_vouchers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `min_order_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `max_uses` int unsigned NOT NULL DEFAULT '0' COMMENT '0 = unlimited',
  `used_count` int unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=active, 0=inactive',
  `starts_at` date DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_voucher_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `edi_vouchers`
--

LOCK TABLES `edi_vouchers` WRITE;
/*!40000 ALTER TABLE `edi_vouchers` DISABLE KEYS */;
INSERT INTO `edi_vouchers` VALUES (1,'10OFF','10% off for customers','percentage',10.00,1000.00,1,0,1,'2026-06-09','2026-06-12','2026-06-10 14:06:16');
/*!40000 ALTER TABLE `edi_vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grades` (
  `id` int NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grades`
--

LOCK TABLES `grades` WRITE;
/*!40000 ALTER TABLE `grades` DISABLE KEYS */;
INSERT INTO `grades` VALUES (1,'Pre School'),(2,'Grade 1'),(3,'Grade 2'),(4,'Grade 3'),(5,'Grade 4'),(6,'Grade 5');
/*!40000 ALTER TABLE `grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `homework_descriptions`
--

DROP TABLE IF EXISTS `homework_descriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `homework_descriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `homework_id` int NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index` (`homework_id`),
  CONSTRAINT `FK_homework_id` FOREIGN KEY (`homework_id`) REFERENCES `homework_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `homework_descriptions`
--

LOCK TABLES `homework_descriptions` WRITE;
/*!40000 ALTER TABLE `homework_descriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `homework_descriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `homework_details`
--

DROP TABLE IF EXISTS `homework_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `homework_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pdfupload` varchar(20) NOT NULL,
  `download_count` int NOT NULL,
  `language_id` int DEFAULT NULL,
  `grade_id` int DEFAULT NULL,
  `main_cat_id` int DEFAULT NULL,
  `sub_cat_id` int DEFAULT NULL,
  `product_category_id` int DEFAULT NULL COMMENT 'shop category',
  `product_subcategory_id` int DEFAULT NULL COMMENT 'shop subcategory',
  `ws_category_id` int unsigned DEFAULT NULL,
  `ws_subcategory_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `homework_details`
--

LOCK TABLES `homework_details` WRITE;
/*!40000 ALTER TABLE `homework_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `homework_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` int NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'Sinhala'),(2,'English'),(3,'Tamil');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `main_category`
--

DROP TABLE IF EXISTS `main_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `main_category` (
  `id` int NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `main_category`
--

LOCK TABLES `main_category` WRITE;
/*!40000 ALTER TABLE `main_category` DISABLE KEYS */;
INSERT INTO `main_category` VALUES (1,'Books','');
/*!40000 ALTER TABLE `main_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter`
--

DROP TABLE IF EXISTS `newsletter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter` (
  `email_addr` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter`
--

LOCK TABLES `newsletter` WRITE;
/*!40000 ALTER TABLE `newsletter` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,6,1,'Coloring Book for Kids',1,665.00,665.00),(2,6,2,'Animal Coloring Book for Kids',3,500.00,1500.00),(3,7,6,'Sinhala Hodiya',1,1500.00,1500.00),(4,7,5,'Wooden Multipurpose Educational Toy',3,2999.00,8997.00),(5,8,6,'Sinhala Hodiya',1,1500.00,1500.00),(6,9,6,'Sinhala Hodiya',2,1500.00,3000.00),(7,9,5,'Wooden Multipurpose Educational Toy',1,2999.00,2999.00),(8,9,4,'3D Kids School Backpack ',1,4499.10,4499.10),(9,9,3,' Lunch Box',1,522.50,522.50),(10,9,2,'Sinhala Alphabet',1,450.00,450.00),(11,9,1,'Coloring Book',1,665.00,665.00),(12,10,6,'Sinhala Hodiya',1,1500.00,1500.00),(13,11,6,'Sinhala Hodiya',2,1500.00,3000.00),(14,12,5,'Wooden Multipurpose Educational Toy',1,2999.00,2999.00),(15,13,6,'Sinhala Hodiya',5,1500.00,7500.00),(16,13,4,'3D Kids School Backpack ',5,4499.10,22495.50),(17,14,6,'Sinhala Hodiya',1,1500.00,1500.00),(18,15,4,'3D Kids School Backpack ',3,4499.10,13497.30),(19,16,3,' Lunch Box',1,522.50,522.50),(20,17,3,' Lunch Box',1,522.50,522.50),(21,18,4,'3D Kids School Backpack ',2,4499.10,8998.20),(22,19,6,'Sinhala Hodiya',1,1500.00,1500.00),(24,20,3,' Lunch Box',1,522.50,522.50),(26,22,5,'Wooden Multipurpose Educational Toy',1,2999.00,2999.00),(29,25,6,'Sinhala Hodiya',1,1500.00,1500.00),(31,27,6,'Sinhala Hodiya',1,1500.00,1500.00),(33,29,5,'Wooden Multipurpose Educational Toy',1,2999.00,2999.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_number` varchar(32) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `address_line` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `district` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `payment_method` enum('cod','bank_transfer','card') NOT NULL,
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `order_status` varchar(50) NOT NULL DEFAULT 'Order Placed',
  `subtotal` decimal(10,2) NOT NULL,
  `shipping` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL,
  `voucher_code` varchar(50) DEFAULT NULL,
  `voucher_discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'C#260420173334','1','test','Cooray','','233/15A','Wattala','11300','gampaha','rangaa.groovymark@gmail.com','0700000000','cod','pending','Order Placed',1830.00,450.00,2280.00,'2026-04-20 17:33:34',NULL,0.00),(2,'B#260421142011','1','test','Cooray','','233/15A','Wattala','11300','gampaha','brmcooray@gmail.com','0700000000','bank_transfer','pending','Order Placed',665.00,450.00,1115.00,'2026-04-21 14:20:11',NULL,0.00),(3,'B#260421142943','1','test','Cooray','','233/15A','Wattala','11300','gampaha','brmcooray@gmail.com','0700000000','bank_transfer','pending','Completed',665.00,450.00,1115.00,'2026-04-21 14:29:43',NULL,0.00),(4,'B#260421143657','1','Tedtt','Keeu','','Hos','Gampaha','11300','Gampaha','test@gmail.com','0700000000','bank_transfer','pending','Order Placed',500.00,450.00,950.00,'2026-04-21 14:36:57',NULL,0.00),(5,'C#260501164753','1','Fast','Ad','fastad','20 7','Gampaha','11000','Gampaha','fastadlanka@gmail.com','0724761762','cod','paid','Completed',1830.00,450.00,2280.00,'2026-05-01 16:47:53',NULL,0.00),(6,'C#260503193604','1','test','Cooray','','233/15A','Wattala','11300','Gampaha','brmcooray@gmail.com','0700000000','cod','paid','Completed',2165.00,600.00,2765.00,'2026-05-03 19:36:04',NULL,0.00),(7,'C#260518011828','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Gampaha','fastadlanka@gmail.com','0724761762','cod','pending','Failed',10497.00,750.00,11247.00,'2026-05-18 01:18:28',NULL,0.00),(8,'C#260518105513','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Kandy','fastadlanka@gmail.com','0724761762','cod','pending','Return',1500.00,650.00,2150.00,'2026-05-18 10:55:13',NULL,0.00),(9,'B#260518110925','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Kandy','fastadlanka@gmail.com','0724761762','bank_transfer','paid','Completed',12135.60,400.00,12535.60,'2026-05-18 11:09:25',NULL,0.00),(10,'B#260518111223','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Kandy','fastadlanka@gmail.com','0724761762','bank_transfer','paid','Completed',1500.00,350.00,1850.00,'2026-05-18 11:12:23',NULL,0.00),(11,'C#260521020428','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Gampaha','fastadlanka@gmail.com','0724761762','cod','pending','Order Placed',3000.00,300.00,3300.00,'2026-05-21 02:04:28',NULL,0.00),(12,'B#260521020715','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Gampaha','fastadlanka@gmail.com','0724761762','bank_transfer','pending','Order Placed',2999.00,300.00,3299.00,'2026-05-21 02:07:15',NULL,0.00),(13,'B#260521034819','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Kandy','fastadlanka@gmail.com','0724761762','bank_transfer','pending','Order Placed',29995.50,350.00,30345.50,'2026-05-21 03:48:19',NULL,0.00),(14,'B#260523013023','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Kandy','fastadlanka@gmail.com','0724761762','bank_transfer','pending','Order Placed',1500.00,350.00,1850.00,'2026-05-23 01:30:23',NULL,0.00),(15,'B#260610100826','1','test','Cooray','','233/15A','Wattala','11300','Colombo','rangaa.groovymark@gmail.com','0700000000','bank_transfer','pending','Order Placed',13497.30,300.00,13797.30,'2026-06-10 10:08:26',NULL,0.00),(16,'B#260610104956','1','test','Cooray','','233/15A','Wattala','11300','Gampaha','rangaa.groovymark@gmail.com','0700000000','bank_transfer','pending','Order Placed',522.50,300.00,822.50,'2026-06-10 10:49:56',NULL,0.00),(17,'B#260610105501','1','test','Cooray','','233/15A','Wattala','11300','Gampaha','rangaa.groovymark@gmail.com','0700000000','bank_transfer','pending','Order Placed',522.50,300.00,822.50,'2026-06-10 10:55:01',NULL,0.00),(18,'B#260611104409','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Colombo','fastadlanka@gmail.com','0724761762','bank_transfer','pending','Order Placed',8998.20,300.00,9298.20,'2026-06-11 10:44:09',NULL,0.00),(19,'C#260612024645','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Colombo','fastadlanka@gmail.com','0724761762','cod','pending','Order Placed',1699.99,300.00,1999.99,'2026-06-12 02:46:45',NULL,0.00),(20,'B#260612024841','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Colombo','fastadlanka@gmail.com','0724761762','bank_transfer','pending','Order Placed',522.50,300.00,822.50,'2026-06-12 02:48:41',NULL,0.00),(21,'B#260612073943','1','test','Cooray','','233/15A','Wattala','11300','Kandy','rangaa.groovymark@gmail.com','0700000000','bank_transfer','pending','Order Placed',199.99,350.00,549.99,'2026-06-12 07:39:43',NULL,0.00),(22,'C#260612074044','1','Ranga','Cooray','','233/15A','Wattala','11300','Gampaha','rangaa.groovymark@gmail.com','0700000000','cod','pending','Order Placed',2999.00,300.00,3299.00,'2026-06-12 07:40:44',NULL,0.00),(23,'C#260612074732','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Gampaha','fastadlanka@gmail.com','0724761762','cod','pending','Order Placed',199.99,300.00,499.99,'2026-06-12 07:47:32',NULL,0.00),(24,'B#260613011339','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Gampaha','fastadlanka@gmail.com','0724761762','bank_transfer','pending','Order Placed',199.99,300.00,499.99,'2026-06-13 01:13:39',NULL,0.00),(25,'C#260613011419','1','Kanthi','Ekanayake','fastad','20 7','Gampaha','11000','Gampaha','fastadlanka@gmail.com','0724761762','cod','pending','Order Placed',1500.00,300.00,1800.00,'2026-06-13 01:14:19',NULL,0.00),(26,'C#260615052452','1','Kanthi','Ekanayake','Kanthi Ekanayake','20 7','Gampaha','11000','Gampaha','fastadlanka@gmail.com','0724761762','cod','pending','Order Placed',199.99,300.00,499.99,'2026-06-15 05:24:52',NULL,0.00),(27,'B#260615052724','1','Kanthi','Ekanayake','Kanthi Ekanayake','20 7','Gampaha','11000','Colombo','fastadlanka@gmail.com','0724761762','bank_transfer','pending','Order Placed',1500.00,300.00,1800.00,'2026-06-15 05:27:24',NULL,0.00),(28,'C#260615053402','1','Kanthi','Ekanayake','Kanthi Ekanayake','20 7','Gampaha','11000','Colombo','fastadlanka@gmail.com','0724761762','cod','pending','Order Placed',199.99,300.00,499.99,'2026-06-15 05:34:02',NULL,0.00),(29,'C#260615053453','1','Kanthi','Ekanayake','Kanthi Ekanayake','20 7','Gampaha','11000','Colombo','fastadlanka@gmail.com','0724761762','cod','pending','Order Placed',2999.00,300.00,3299.00,'2026-06-15 05:34:53',NULL,0.00);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pdf_descriptions`
--

DROP TABLE IF EXISTS `pdf_descriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_descriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pdf_id` int NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image_01` varchar(20) NOT NULL,
  `image_02` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_pdf_id` (`pdf_id`),
  CONSTRAINT `FK_pdf_id` FOREIGN KEY (`pdf_id`) REFERENCES `pdf_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pdf_descriptions`
--

LOCK TABLES `pdf_descriptions` WRITE;
/*!40000 ALTER TABLE `pdf_descriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `pdf_descriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pdf_details`
--

DROP TABLE IF EXISTS `pdf_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdf_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `image` varchar(20) NOT NULL,
  `video` varchar(100) NOT NULL,
  `video_status` int NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pdfupload` varchar(100) NOT NULL,
  `download_count` int NOT NULL,
  `language_id` int DEFAULT NULL,
  `grade_id` int DEFAULT NULL,
  `main_cat_id` int DEFAULT NULL,
  `sub_cat_id` int DEFAULT NULL,
  `product_category_id` int DEFAULT NULL COMMENT 'shop category',
  `product_subcategory_id` int DEFAULT NULL COMMENT 'shop subcategory',
  `pdf_original_name` text,
  `ws_category_id` int unsigned DEFAULT NULL,
  `ws_subcategory_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pdf_details`
--

LOCK TABLES `pdf_details` WRITE;
/*!40000 ALTER TABLE `pdf_details` DISABLE KEYS */;
INSERT INTO `pdf_details` VALUES (1,'Dog','Seven','test coloring pages pdf','1.JPG','',0,1,'2026-04-27 16:31:29','1.pdf',1,2,2,NULL,NULL,2,NULL,NULL,3,5),(2,'Dinasour','Dinasour Coloring Page','Color the Dinasour','1777439265.JPG','',0,1,'2026-04-29 05:07:45','1777439265_pdf.pdf',2,2,1,NULL,NULL,2,NULL,NULL,1,3),(3,'Frog','Frog Coloring Page','Color the Frog','1777439387.JPG','',0,1,'2026-04-29 05:09:47','1777439387_pdf.pdf',0,2,1,NULL,NULL,2,NULL,NULL,1,3),(4,'Dog','Dog Coloring Page ','Dog coloring page','1777520563.jpeg','',0,1,'2026-04-30 03:42:43','1777520563_pdf.pdf',1,1,1,NULL,NULL,2,NULL,NULL,1,3),(5,'Owl','Owl Coloring Page','Color the Owl','1777903890.JPG','',0,1,'2026-05-04 14:11:30','1777903890_pdf.pdf',0,2,1,NULL,NULL,2,NULL,NULL,1,7),(6,'Cat','Cat Coloring Page  ','Cat Coloring Page  ','1778552617.JPG','',0,1,'2026-05-12 02:23:37','1778552617_pdf.pdf',1,2,1,NULL,NULL,7,8,NULL,1,3),(7,'Dog ','Dog Coloring Page  ','Dog Coloring Page  ','1778552787.JPG','',0,1,'2026-05-12 02:26:27','1778552787_pdf.pdf',0,2,1,NULL,NULL,7,8,NULL,1,3),(8,'Cow ','Cow Coloring Page  ','Cow Coloring Page  ','1778552908.JPG','',0,1,'2026-05-12 02:28:28','1778552908_pdf.pdf',0,2,1,NULL,NULL,7,8,NULL,1,3),(9,'Lion ','Lion Coloring Page  ','Lion Coloring Page  ','1778553044.JPG','',0,1,'2026-05-12 02:30:44','1778553044_pdf.pdf',1,2,1,NULL,NULL,7,8,NULL,1,3),(10,'Elephant ','Elephant Coloring Page  ','Elephant Coloring Page  ','1778553263.JPG','',0,1,'2026-05-12 02:34:23','1778553263_pdf.pdf',1,2,1,NULL,NULL,7,8,NULL,1,3),(12,'Deer ','Deer Coloring Page  ','Deer Coloring Page  ','1778553726.JPG','',0,1,'2026-05-12 02:42:06','1778553726_pdf.pdf',0,2,1,NULL,NULL,7,8,NULL,1,3),(13,'Pig','Pig Coloring Page  ','Pig Coloring Page ','1778553946.JPG','',0,1,'2026-05-12 02:45:46','1778553946_pdf.pdf',0,2,1,NULL,NULL,7,8,NULL,1,3),(14,'Giraffe','Giraffe Coloring Page  ','Giraffe Coloring Page  ','1778554067.jpg','',0,1,'2026-05-12 02:47:47','1778554067_pdf.pdf',2,2,1,NULL,NULL,7,8,NULL,1,3),(15,'Hen ','Hen Coloring Page  ','Hen Coloring Page  ','1778554183.JPG','',0,1,'2026-05-12 02:49:43','1778554183_pdf.pdf',0,2,1,NULL,NULL,7,9,NULL,1,7),(16,'Owl ','Owl  Coloring Page  ','Owl  Coloring Page  ','1778554799.JPG','',0,1,'2026-05-12 02:59:59','16.pdf',5,2,1,NULL,NULL,7,9,'Owl Coloring.pdf',1,7),(17,'Penguin','Penguin Coloring Page  ','Penguin Coloring Page  ','1778584488.JPG','',0,1,'2026-05-12 11:14:48','1778584488_pdf.pdf',0,2,1,NULL,NULL,7,9,'Penguin.pdf',1,7),(18,'Dragon ','Dragon  Coloring Page  ','Dragon  Coloring Page  ','1778584903.JPG','',0,1,'2026-05-12 11:21:43','1778584903_pdf.pdf',0,2,1,NULL,NULL,7,9,'Dragon.pdf',1,3),(19,'Rabbit ','Rabbit Coloring Page  ','Rabbit Coloring Page  ','1778585037.JPG','',0,1,'2026-05-12 11:23:57','1778585037_pdf.pdf',1,2,1,NULL,NULL,7,8,'Rabbit.pdf',1,3),(20,'Butterfly ','Butterfly  Coloring Page  ','Butterfly  Coloring Page  ','1778585135.JPG','',0,1,'2026-05-12 11:25:35','1778585135_pdf.pdf',0,2,1,NULL,NULL,7,9,'Butterfly.pdf',1,3),(21,'Dinosaurs ','Dinosaurs Coloring Page  ','Dinosaurs Coloring Page  ','1778585352.JPG','',0,1,'2026-05-12 11:29:12','1778585352_pdf.pdf',1,2,1,NULL,NULL,7,8,'Dinosaur.pdf',1,3),(22,'Flower ','Flower  Coloring Page  ','Flower  Coloring Page  ','1778585429.JPG','',0,1,'2026-05-12 11:30:29','1778585429_pdf.pdf',1,2,1,NULL,NULL,7,8,'Flower.pdf',1,4),(23,'Flower ','Flower Garden Coloring Page  ','Flower Garden Coloring Page  ','1778585610.JPG','',0,1,'2026-05-12 11:33:30','1778585610_pdf.pdf',1,2,1,NULL,NULL,7,NULL,'Flower Garden.pdf',1,2),(24,'Fox ','Fox ','Fox Dot to Dot sheet ','1781502150.jpg','',0,1,'2026-06-15 05:42:30','1781502150_pdf.pdf',0,2,6,1,1,NULL,NULL,'Fox dot to dot worksheet.pdf',4,12);
/*!40000 ALTER TABLE `pdf_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES (1,'Stickers','',1,'2026-04-20 13:33:52'),(2,'Books','',1,'2026-04-27 16:29:00'),(5,'School Essentials','',1,'2026-05-04 14:22:04'),(6,'Toys','',1,'2026-05-10 06:21:33');
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_review`
--

DROP TABLE IF EXISTS `product_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_review` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint NOT NULL DEFAULT '0',
  `review` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_review_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_review`
--

LOCK TABLES `product_review` WRITE;
/*!40000 ALTER TABLE `product_review` DISABLE KEYS */;
INSERT INTO `product_review` VALUES (1,2,'Amanda','amandalakshani699@gmail.com',2,'','2026-04-30 07:10:00'),(2,1,'fastad','fastadlanka@gmail.com',5,'Good product','2026-05-16 03:58:26'),(3,2,'fatad','fastadlanka@gmail.com',5,'orem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur fermentum varius dolor nec rutrum. Phasellus consequat dapibus finibus. Lorem ipsum dolor sit amet, consectetur adipiscing elit.','2026-05-18 09:36:33');
/*!40000 ALTER TABLE `product_review` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_subcategories`
--

DROP TABLE IF EXISTS `product_subcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_subcategories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_category_id` int NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_psc_product_category` (`product_category_id`),
  CONSTRAINT `fk_psc_product_categories` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_subcategories`
--

LOCK TABLES `product_subcategories` WRITE;
/*!40000 ALTER TABLE `product_subcategories` DISABLE KEYS */;
INSERT INTO `product_subcategories` VALUES (1,1,'Coloring Books','','2026-04-20 13:55:05'),(2,1,'Dot-To-Dot','','2026-04-20 13:55:31'),(5,5,'Lunch Box','','2026-05-04 14:22:29'),(6,5,'School Bags','','2026-05-10 05:56:49'),(7,6,'Educational Toys','','2026-05-10 06:21:50'),(10,2,'Alphabet','','2026-05-16 02:59:55');
/*!40000 ALTER TABLE `product_subcategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `sub_category_id` int DEFAULT NULL,
  `product_subcategory_id` int DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT '0.00',
  `discounted_price` decimal(10,2) DEFAULT '0.00',
  `age_group` varchar(50) DEFAULT NULL,
  `description` text,
  `language` varchar(50) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `isbn` varchar(64) DEFAULT NULL,
  `weight` varchar(64) DEFAULT NULL,
  `weight_kg` decimal(12,4) DEFAULT NULL COMMENT 'Unit weight in kg for shipping',
  `gallery_images` text,
  `options_extra` text,
  PRIMARY KEY (`id`),
  KEY `fk_product_category` (`category_id`),
  KEY `fk_product_subcategory` (`sub_category_id`),
  KEY `fk_products_product_subcategory` (`product_subcategory_id`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_subcategory` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_category` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_products_product_subcategory` FOREIGN KEY (`product_subcategory_id`) REFERENCES `product_subcategories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,2,NULL,NULL,'Edibear','Coloring Book',700.00,5.00,665.00,'Grade 1','Duis id suscipit risus. Proin vel diam ipsum. Proin tincidunt sed libero id interdum. Suspendisse sollicitudin odio ut dui auctor placerat. Fusce in ex at lacus placerat suscipit at nec nulla. Integer vitae ultricies risus, ut mattis nulla.','English','Ediber ',20,'4.jpg',1,'2026-04-20 13:56:45','124576B44562','0.1 kg',0.1000,NULL,NULL),(2,1,NULL,1,'Muthu Akura ','Sinhala Alphabet',450.00,0.00,0.00,'Pre School','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur fermentum varius dolor nec rutrum. Phasellus consequat dapibus finibus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam odio turpis, lacinia vitae iaculis eget, ultricies vel felis. Nullam molestie volutpat diam vitae feugiat. Phasellus hendrerit, ligula nec porta finibus, mi ipsum rutrum sem, at fermentum augue erat vel dolor.','Sinhala','V.V. Pathmaseeli ',25,'2.jpg',1,'2026-04-20 14:03:31','124576B44562','0.25 kg',0.2500,NULL,NULL),(3,5,NULL,5,'Atlas',' Lunch Box',550.00,5.00,522.50,'Grade 1','Quisque tortor elit, ultricies in ipsum ut, eleifend aliquet magna. Vestibulum consectetur lacinia eros ac iaculis. Curabitur tempor sollicitudin arcu a pellentesque. Pellentesque quis orci sit amet velit fermentum sagittis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras quis diam justo. Vestibulum varius odio non leo varius, at molestie tortor volutpat. Aliquam erat volutpat. Duis dictum eleifend odio. Duis id ex ut arcu fermentum maximus in vitae nibh.','Sinhala','',18,'Atlus Lunch Box_001.webp',1,'2026-05-04 14:24:18','','0.4998 kg',0.4998,NULL,NULL),(4,5,NULL,6,'Atlas','3D Kids School Backpack ',4999.00,10.00,4499.10,'Grade 1','Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s,3D Kids School Backpack Pink Labubu With Son Large 17H X 13L X 7W','','',10,'Backpack.jpg',1,'2026-05-10 06:18:47','','',NULL,NULL,NULL),(5,6,NULL,7,'Edibear','Wooden Multipurpose Educational Toy',2999.00,0.00,0.00,'Grade 3','Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.','','',50,'toy.jpg',1,'2026-05-10 06:22:38','','0.8 kg',0.8000,NULL,NULL),(6,2,NULL,10,'Susara','Sinhala Hodiya',1500.00,0.00,0.00,'Pre School','','Sinhala','Susara ',25,'Sihalahodiya_2048x.webp',1,'2026-05-16 03:03:42','151235789','0.1 kg',0.1000,'[\"WhatsApp_Image_2026-04-02_at_11_12_34_AM_044461b3.jpeg\",\"WhatsApp_Image_2026-04-02_at_11_12_47_AM__1__aa0d516b.jpeg\",\"WhatsApp_Image_2026-04-02_at_11_12_47_AM_e3763669.jpeg\"]',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sub_category`
--

DROP TABLE IF EXISTS `sub_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sub_category` (
  `id` int NOT NULL,
  `main_cat_id` int DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_main_category` (`main_cat_id`),
  CONSTRAINT `fk_main_category` FOREIGN KEY (`main_cat_id`) REFERENCES `main_category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sub_category`
--

LOCK TABLES `sub_category` WRITE;
/*!40000 ALTER TABLE `sub_category` DISABLE KEYS */;
INSERT INTO `sub_category` VALUES (1,1,'Coloring Books','');
/*!40000 ALTER TABLE `sub_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testimonials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `ratings` int NOT NULL,
  `one_word` varchar(50) NOT NULL,
  `review` varchar(500) NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_tourist_id` (`user_id`),
  CONSTRAINT `FK_tourist_id` FOREIGN KEY (`user_id`) REFERENCES `tourists` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (2,2,'Test',5,'Great!!','Don’t miss a single adventure! Sign up for Edi’s newsletter to get new worksheets, ‘Brave Heart’ challenge.',1,'2026-04-21 06:56:57'),(3,3,'John',5,'Very Useful','Explore The Honey Market,where we gathered the world\'s top educational and entertainment products under one roof.',1,'2026-04-21 06:57:43'),(4,5,'John',5,'Very Useful','Edibear is a world where education meets adventure! From magical resources in our explorer training camp to the \'Brave Heart\' challenge',1,'2026-05-03 14:52:34');
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials_images`
--

DROP TABLE IF EXISTS `testimonials_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testimonials_images` (
  `testimonial_id` int NOT NULL,
  `image` varchar(20) NOT NULL,
  KEY `FK_testimonial_image_id` (`testimonial_id`),
  CONSTRAINT `FK_testimonial_image_id` FOREIGN KEY (`testimonial_id`) REFERENCES `testimonials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials_images`
--

LOCK TABLES `testimonials_images` WRITE;
/*!40000 ALTER TABLE `testimonials_images` DISABLE KEYS */;
INSERT INTO `testimonials_images` VALUES (2,'cb89ab386eed.png'),(3,'9fbd5244de7a.png'),(4,'02078449d47c.png');
/*!40000 ALTER TABLE `testimonials_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tour_day_details`
--

DROP TABLE IF EXISTS `tour_day_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tour_day_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tour_id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `accommodation` varchar(50) NOT NULL,
  `room` varchar(20) NOT NULL,
  `meal_plan` varchar(20) NOT NULL,
  `travel_time` varchar(50) NOT NULL,
  `image_name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK1_tour_id` (`tour_id`),
  CONSTRAINT `FK1_tour_id` FOREIGN KEY (`tour_id`) REFERENCES `tour_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tour_day_details`
--

LOCK TABLES `tour_day_details` WRITE;
/*!40000 ALTER TABLE `tour_day_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `tour_day_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tour_details`
--

DROP TABLE IF EXISTS `tour_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tour_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `image_name` varchar(20) DEFAULT NULL,
  `duration` varchar(50) NOT NULL,
  `tour_group` varchar(50) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `guide` varchar(50) NOT NULL,
  `pickup_drop` varchar(50) NOT NULL,
  `hotel_type` varchar(50) NOT NULL,
  `description` varchar(10000) NOT NULL,
  `arrival_departure_location` varchar(100) NOT NULL,
  `depature_time` varchar(50) NOT NULL,
  `meal_plan` varchar(50) NOT NULL,
  `bed_room` varchar(50) NOT NULL,
  `services_included` text NOT NULL,
  `services_excluded` text NOT NULL,
  `map` varchar(1000) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tour_details`
--

LOCK TABLES `tour_details` WRITE;
/*!40000 ALTER TABLE `tour_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `tour_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tour_sub_images`
--

DROP TABLE IF EXISTS `tour_sub_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tour_sub_images` (
  `tour_id` int NOT NULL,
  `image_name` varchar(10) NOT NULL,
  KEY `FK_tour_id` (`tour_id`),
  CONSTRAINT `FK_tour_id` FOREIGN KEY (`tour_id`) REFERENCES `tour_details` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tour_sub_images`
--

LOCK TABLES `tour_sub_images` WRITE;
/*!40000 ALTER TABLE `tour_sub_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `tour_sub_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tourists`
--

DROP TABLE IF EXISTS `tourists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tourists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(191) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `profile_pic` varchar(20) DEFAULT NULL,
  `password` varchar(1000) NOT NULL,
  `email` varchar(191) NOT NULL,
  `country` varchar(100) NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `delete_status` int DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `password_reset_token` varchar(64) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tourists`
--

LOCK TABLES `tourists` WRITE;
/*!40000 ALTER TABLE `tourists` DISABLE KEYS */;
INSERT INTO `tourists` VALUES (1,'test@gmail.com','test','default.jpg','pass$2y$10$7kZGUlAO3Ujvn4e2sx/Y8eGQK6GqBZwi2UeW1qZJEyOF4ogCaAMYm','test@gmail.com','Sri Lanka',1,0,'2026-04-20 15:54:42',NULL,NULL),(2,'u0e9ada6c','Test','default.jpg','pass$2y$10$CG8H9CUE3icKbaIaGSF.Euszlm0VZy0Ddyt94fXVGqrN9iHm7vUQW','t0e9ada@g.t','Sri Lanka',1,0,'2026-04-21 06:56:57',NULL,NULL),(3,'u989c256d','John','default.jpg','pass$2y$10$PdTs2NBEjOOPN4hgl8ZUOe1ea1WcciYJkkePga9/xtgDjSiZGPfK2','t989c25@g.t','USA',1,0,'2026-04-21 06:57:43',NULL,NULL),(4,'rangaa.groovymark@gmail.com','Ranga Cooray','default.jpg','pass$2y$10$9O7aKsetnhfSqB.hAz6FAupVowIu/luuhmhf6UPBG0bjYfu0iOXCS','rangaa.groovymark@gmail.com','Sri Lanka',1,0,'2026-05-01 07:24:41','480126a603920368a23f7a26ae2b180ac7eb5b18d9ac2f207e73ff31bbe8a709','2026-05-01 08:25:34'),(5,'u239b8f48','John','default.jpg','pass$2y$10$0ZrysIOIu7lu3tFkHvtsROhRkMfNsPcILl8RbUZTpj8vWPYfIBK3G','t239b8f@g.t','USA',1,0,'2026-05-03 14:52:34',NULL,NULL);
/*!40000 ALTER TABLE `tourists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_table`
--

DROP TABLE IF EXISTS `user_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_table` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `login_email` varchar(100) NOT NULL,
  `password` varchar(10000) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `admin_role` varchar(32) NOT NULL DEFAULT 'administrator',
  `city_country` varchar(100) NOT NULL DEFAULT '',
  `admin_status` tinyint(1) NOT NULL DEFAULT '1',
  `profile_pic` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `register_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delete_status` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_table`
--

LOCK TABLES `user_table` WRITE;
/*!40000 ALTER TABLE `user_table` DISABLE KEYS */;
INSERT INTO `user_table` VALUES (1,'Ranga','Cooray','rangaa.groovymark@gmail.com','pass$2y$10$oXSho3hNoy8DMYdJBMOw7ubMvRpp/OorotPf/Y/jEd5uyn86QLriS','0700000000','administrator','',1,'default.jpg','2026-04-20 12:43:19',0),(2,'Thilina','Sampath','tsranasingha@gmail.com','pass$2y$10$DzVr0RUYl.PBIWDIKoK9mu83IZFEae/ojLj59jLhaPsttN.CLgNiu','0724761762','administrator','',1,'default.jpg','2026-04-24 06:06:04',0);
/*!40000 ALTER TABLE `user_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ws_categories`
--

DROP TABLE IF EXISTS `ws_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ws_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ws_categories`
--

LOCK TABLES `ws_categories` WRITE;
/*!40000 ALTER TABLE `ws_categories` DISABLE KEYS */;
INSERT INTO `ws_categories` VALUES (1,'Coloring Pages',1,'2026-05-12 12:56:50'),(2,'Tracing',2,'2026-05-12 12:57:05'),(4,'Dot - to - Dot',3,'2026-06-15 05:38:59');
/*!40000 ALTER TABLE `ws_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ws_products`
--

DROP TABLE IF EXISTS `ws_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ws_products` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int unsigned NOT NULL,
  `subcategory_id` int unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ws_products_category` (`category_id`),
  KEY `idx_ws_products_subcategory` (`subcategory_id`),
  CONSTRAINT `fk_ws_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `ws_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_ws_prod_sub` FOREIGN KEY (`subcategory_id`) REFERENCES `ws_subcategories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ws_products`
--

LOCK TABLES `ws_products` WRITE;
/*!40000 ALTER TABLE `ws_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `ws_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ws_subcategories`
--

DROP TABLE IF EXISTS `ws_subcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ws_subcategories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ws_subcategories_category` (`category_id`),
  CONSTRAINT `fk_ws_sub_cat` FOREIGN KEY (`category_id`) REFERENCES `ws_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ws_subcategories`
--

LOCK TABLES `ws_subcategories` WRITE;
/*!40000 ALTER TABLE `ws_subcategories` DISABLE KEYS */;
INSERT INTO `ws_subcategories` VALUES (1,1,'Animals',1,'2026-05-12 12:57:18'),(2,1,'Flowers',2,'2026-05-12 12:57:26'),(3,1,'Vehicles',4,'2026-05-12 12:57:36'),(4,1,'Nature',5,'2026-05-12 13:26:34'),(7,1,'Shapes',3,'2026-05-12 13:49:24'),(8,1,'Letters',6,'2026-05-13 02:01:39'),(9,1,'Numbers',7,'2026-05-13 02:01:55'),(10,2,'Letters',1,'2026-05-13 02:16:19'),(11,2,'Numbers',2,'2026-05-13 02:16:35'),(12,4,'Animals',1,'2026-06-15 05:40:09');
/*!40000 ALTER TABLE `ws_subcategories` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-15 17:08:21
