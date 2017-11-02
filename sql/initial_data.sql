-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: localhost    Database: host1563047
-- ------------------------------------------------------
-- Server version	5.7.18-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `application_property`
--

LOCK TABLES `application_property` WRITE;
/*!40000 ALTER TABLE `application_property` DISABLE KEYS */;
INSERT INTO `application_property` VALUES (20,'PHONE_NUMBER_BUSY_INTERVAL_SEC','300','Период действия подстановки номера (сек)',NULL),(21,'RESTORE_TOKEN_EXPIRE_INTERVAL_SEC','600','Срок действия ссылки на восстановление пароля (сек)',NULL),(22,'RESTORE_PWD_TOKEN_EXPIRE_INTERVAL_SEC','180','Срок действия страницы восстановления пароля (сек)',NULL),(23,'MAIL_SMTP_HOST','mail.allostat.ru',' Почта - DNS-имя сервера',NULL),(24,'MAIL_SMTP_PORT','25','Почта - Порт для отправки',NULL),(25,'MAIL_SMTP_USER','support@allostat.ru','Почта - учетная запись для отправки',NULL),(26,'MAIL_SMTP_PWD','2890217','Почта - пароль учетной записи для отправки',NULL),(27,'CALLS_PAGE_IDLE_TO_ALIVE_DELAY','5','Задержка по истечении периода неактивности (перед выдачей номера)',NULL),(28,'SERVER_TIMEZONE','Europe/Moscow','Временная зона на сервере',NULL);
/*!40000 ALTER TABLE `application_property` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `balance_operation`
--

LOCK TABLES `balance_operation` WRITE;
/*!40000 ALTER TABLE `balance_operation` DISABLE KEYS */;
/*!40000 ALTER TABLE `balance_operation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `call_event`
--

LOCK TABLES `call_event` WRITE;
/*!40000 ALTER TABLE `call_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `call_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `call_object`
--

LOCK TABLES `call_object` WRITE;
/*!40000 ALTER TABLE `call_object` DISABLE KEYS */;
INSERT INTO `call_object` VALUES (88,'518716035.1497954490','2017-09-08 16:39:37',2,NULL,1,5,'2017-09-08 16:39:56','0',NULL,'http://mock.allostat.ru/?utm_medium=cpc'),(89,'2054873143.1503050103','2017-09-08 16:56:12',1,NULL,1,4,NULL,'1503579264230695872',NULL,'http://mock.allostat.ru/?utm_medium=cpc'),(90,'2054873143.1503050103','2017-09-08 17:07:59',1,NULL,1,4,NULL,'1503579264230695872',NULL,'http://mock.allostat.ru/?utm_medium=cpc'),(91,'2054873143.1503050103','2017-09-12 15:56:43',1,NULL,1,4,NULL,'1503579264230695872',NULL,'http://mock.allostat.ru/'),(99,'2054873143.1503050103','2017-09-12 16:01:56',1,NULL,1,4,NULL,'1503579264230695872',NULL,'http://mock.allostat.ru/'),(110,'518716035.1497954490','2017-09-12 16:10:21',1,NULL,1,-1,NULL,'1504780217411174507',NULL,'http://mock.allostat.ru/'),(112,'518716035.1497954490','2017-09-12 16:10:28',1,NULL,1,-1,NULL,'1504780217411174507',NULL,'http://mock.allostat.ru/'),(114,'518716035.1497954490','2017-09-12 16:10:58',1,NULL,1,-1,NULL,'1504780217411174507',NULL,'http://mock.allostat.ru/'),(116,'518716035.1497954490','2017-09-12 16:11:49',1,NULL,1,-1,NULL,'1504780217411174507',NULL,'http://mock.allostat.ru/'),(117,'518716035.1497954490','2017-09-12 16:18:56',1,NULL,1,4,NULL,'1504780217411174507',NULL,'http://mock.allostat.ru/'),(119,'2054873143.1503050103','2017-09-12 16:19:15',1,NULL,1,-1,NULL,'1503579264230695872',NULL,'http://mock.allostat.ru/'),(125,'2054873143.1503050103','2017-09-12 16:30:24',1,NULL,1,4,NULL,'1503579264230695872',NULL,'http://mock.allostat.ru/'),(127,'518716035.1497954490','2017-09-12 16:30:35',1,NULL,1,-1,NULL,'1504780217411174507',NULL,'http://mock.allostat.ru/'),(128,'2054873143.1503050103','2017-09-12 17:03:45',1,NULL,1,4,NULL,'1503579264230695872',NULL,'http://mock.allostat.ru/'),(129,'2054873143.1503050103','2017-09-12 17:51:47',2,NULL,1,4,'2017-09-12 18:50:47','1503579264230695872',NULL,'http://mock.allostat.ru/'),(130,'518716035.1497954490','2017-09-13 12:37:18',1,NULL,1,4,NULL,'1504780217411174507',NULL,'http://mock.allostat.ru/'),(139,'2054873143.1503050103','2017-09-13 12:43:35',1,NULL,1,4,NULL,'1503579264230695872',NULL,'http://mock.allostat.ru/'),(144,'2054873143.1503050103','2017-09-13 12:52:55',1,NULL,1,4,NULL,'1503579264230695872',NULL,'http://mock.allostat.ru/'),(146,'518716035.1497954490','2017-09-13 12:57:56',-1,NULL,1,NULL,NULL,'1504780217411174507',NULL,'http://mock.allostat.ru/'),(149,'518716035.1497954490','2017-09-13 13:16:46',1,NULL,1,4,NULL,'1504780217411174507',NULL,'http://mock.allostat.ru/'),(150,'2054873143.1503050103','2017-09-13 13:17:09',-1,NULL,1,NULL,NULL,'1503579264230695872',NULL,'http://mock.allostat.ru/'),(151,'2054873143.1503050103','2017-09-13 17:35:12',1,NULL,1,4,NULL,'1503579264230695872',NULL,'http://mock.allostat.ru/'),(152,'1907797710.1505318042','2017-09-13 18:54:19',1,NULL,1,4,NULL,'1505318042257230772',NULL,'http://mock.allostat.ru/'),(153,'518716035.1497954490','2017-09-13 18:57:50',-1,NULL,1,NULL,NULL,'1504780217411174507',NULL,'http://mock.allostat.ru/');
/*!40000 ALTER TABLE `call_object` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `call_type`
--

LOCK TABLES `call_type` WRITE;
/*!40000 ALTER TABLE `call_type` DISABLE KEYS */;
INSERT INTO `call_type` VALUES (-1,'NoFreeNumber',NULL),(1,'Initial','none'),(2,'Call',NULL),(3,'NoCall',NULL);
/*!40000 ALTER TABLE `call_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `customer`
--

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
INSERT INTO `customer` VALUES (1,'cat_dz@mail.ru','$2y$10$hZWAoDrSAfJZadQRUMnMreYhTHzIJII4jm9kywLIifKzTb8acDtiu',NULL,NULL,'2017-06-23 15:56:16',NULL,NULL,'UA-91379404-1','+74956666666','allostat.ru','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhbGxvc3RhdC5ydSIsImF1ZCI6ImNhdF9kekBtYWlsLnJ1IiwiaWF0IjoxNTAxNzgyMTYwLCJuYmYiOjE1MDE3ODIxNjAsImV4cCI6MTUzMzMzOTA4Nn0.Qd2u_AQpoC0Mm8h8C5DJWDpT075MOFC4Q-eWICMCwtw','e76ff32f-eb19-453e-aad3-da66444ee545',3,'45660411','AQAAAAAAWLB_AAR96QG0sP6CNkfFiDdOF_dChHU','1:wsM0APwNHsYypbO4:NN1E-WLoAK_Rtu1aMXCGogdyIiRI2yOlw2Bj9dnLGlkuWN32x9oj:rP7XRWKp_w3LNo1KG3ZK4Q',1536845545,999,18000,72000,1,NULL,0.00),(113,'escuchado@yandex.ru','$2y$10$LHfk14rUuwGKLwphHZmp1OXAgNiKAGmPeZm5QWDKQUPU3Iuifnc2C',NULL,NULL,NULL,NULL,NULL,'GA-123123',NULL,'site.ru','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzaXRlLnJ1IiwiYXVkIjoiZXNjdWNoYWRvQHlhbmRleC5ydSIsImlhdCI6MTUwMTY3MDM2NCwibmJmIjoxNTAxNjcwMzY0LCJleHAiOjE1MzMyMjcyOTB9.qGdPXPnbBlwlIOXB1qGLBbpQXblFNnOyH3ALjIGFEsQ','12646aa0-b578-4b7b-bc0c-c832c5194146',NULL,'45875643','AQAAAAAAWLB_AAR96Z9e-Yz93Uwfvz9U-V3DktU','1:8pLw6ZnpccZ6TXHZ:6jLJvC_mARGjVNv_HWFK_laWCbis5PbFl130kgAR7Zb9PuvGoRDK:iVBK6kncX0cbG1etyAihJQ',1536676109,NULL,NULL,NULL,NULL,NULL,0.00);
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `number_pool`
--

LOCK TABLES `number_pool` WRITE;
/*!40000 ALTER TABLE `number_pool` DISABLE KEYS */;
INSERT INTO `number_pool` VALUES (2,113,'+7 495 111 11 11','','1970-01-01 01:00:00'),(4,1,'+7 495 123 44 44','','2017-09-13 18:59:19');
/*!40000 ALTER TABLE `number_pool` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `order`
--

LOCK TABLES `order` WRITE;
/*!40000 ALTER TABLE `order` DISABLE KEYS */;
/*!40000 ALTER TABLE `order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `supp_req_status`
--

LOCK TABLES `supp_req_status` WRITE;
/*!40000 ALTER TABLE `supp_req_status` DISABLE KEYS */;
INSERT INTO `supp_req_status` VALUES (1,'Registered','Заявка принята');
/*!40000 ALTER TABLE `supp_req_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `support_request`
--

LOCK TABLES `support_request` WRITE;
/*!40000 ALTER TABLE `support_request` DISABLE KEYS */;
INSERT INTO `support_request` VALUES (6,1,'qewrtwsgh',1,'2017-06-22 12:08:20',NULL,NULL),(7,1,'qewrtwsgh',1,'2017-06-22 12:29:39',NULL,NULL),(8,1,'qewrtwsgh',1,'2017-06-22 12:29:54',NULL,NULL),(9,1,'qewrtwsgh',1,'2017-06-22 12:34:04',NULL,NULL),(10,1,'qewrtwsgh',1,'2017-06-22 12:40:48',NULL,NULL),(11,1,'qewrtwsgh',1,'2017-06-22 12:41:31',NULL,NULL),(16,1,'qewrtwsgh',1,'2017-06-22 12:48:32',NULL,NULL),(17,1,'qewrtwsgh',1,'2017-06-22 12:57:59',NULL,NULL),(18,1,'qewrtwsgh',1,'2017-06-22 13:00:34',NULL,NULL),(19,1,'qewrtwsgh',1,'2017-06-22 13:01:19',NULL,NULL),(20,1,'qewrtwsgh',1,'2017-06-22 13:01:27',NULL,NULL),(21,1,'qewrtwsgh',1,'2017-06-22 13:01:54',NULL,NULL),(22,1,'qewrtwsgh',1,'2017-06-22 13:03:19',NULL,NULL),(23,1,'qewrtwsgh',1,'2017-06-22 13:04:03',NULL,NULL),(26,1,'qewrtwsgh',1,'2017-06-22 13:07:22',NULL,NULL),(27,1,'qewrtwsgh',1,'2017-06-22 13:10:32',NULL,NULL),(28,1,'qewrtwsgh',1,'2017-06-22 13:11:03',NULL,NULL),(29,1,'qewrtwsgh',1,'2017-06-22 13:12:10',NULL,NULL),(30,1,'!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!sdsdfsdf',1,'2017-06-22 13:15:40',NULL,NULL),(31,1,'sdfgsdfg',1,'2017-06-22 14:03:38',NULL,NULL),(32,1,'sdfsdf',1,'2017-06-22 14:59:10',NULL,NULL),(35,1,'rghdfghdfgj',1,'2017-06-23 12:03:54',NULL,NULL),(36,1,'14546546',1,'2017-06-23 12:05:37',NULL,NULL),(37,1,'121546548',1,'2017-06-23 12:06:46',NULL,NULL),(38,1,'797979',1,'2017-06-23 12:07:39',NULL,NULL),(39,1,'12121',1,'2017-06-23 12:09:17',NULL,NULL),(40,1,'xddddd',1,'2017-06-23 12:12:35',NULL,NULL),(41,1,'yuyuyuyuy',1,'2017-06-23 12:13:14',NULL,NULL),(42,1,'111111111111111111111111111',1,'2017-06-23 12:13:52',NULL,NULL);
/*!40000 ALTER TABLE `support_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tariff`
--

LOCK TABLES `tariff` WRITE;
/*!40000 ALTER TABLE `tariff` DISABLE KEYS */;
/*!40000 ALTER TABLE `tariff` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-11-02 17:03:11
