-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: 04.12.2025 klo 08:24
-- Palvelimen versio: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `verkkokauppa`
--

-- --------------------------------------------------------

--
-- Rakenne taululle `vkauppa_admin`
--

DROP TABLE IF EXISTS `vkauppa_admin`;
CREATE TABLE IF NOT EXISTS `vkauppa_admin` (
  `adminid` int NOT NULL AUTO_INCREMENT,
  `kayttajanimi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `salasana` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`adminid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `vkauppa_admin`
--

INSERT INTO `vkauppa_admin` (`adminid`, `kayttajanimi`, `salasana`) VALUES
(1, 'admin', '$2y$10$kG3OuCSWky0FjSW3CiqsuOzHm2faFBqnOdykceEXyPb9WtSyTRUdS');

-- --------------------------------------------------------

--
-- Rakenne taululle `vkauppa_asiakas`
--

DROP TABLE IF EXISTS `vkauppa_asiakas`;
CREATE TABLE IF NOT EXISTS `vkauppa_asiakas` (
  `asiakasid` int NOT NULL AUTO_INCREMENT,
  `etunimi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `sukunimi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `puhelinnumero` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `sahkoposti` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `salasana` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`asiakasid`),
  UNIQUE KEY `sahkoposti` (`sahkoposti`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `vkauppa_asiakas`
--

INSERT INTO `vkauppa_asiakas` (`asiakasid`, `etunimi`, `sukunimi`, `puhelinnumero`, `sahkoposti`, `salasana`) VALUES
(12, 'rik', '123', '123', 'w@w', '$2y$10$b.cp9UkjPveMVpVm1nI3AOjM01T2AMlzOHhABVkqmqSWoSTuwJxOq'),
(13, 'riki', 'kire', '12376', 'r@r', '$2y$10$9Kys2.6vp1ScY2zqhmNoreHU8mL7EQnMP.r87z01RDRivR7NNWhj2'),
(14, 'Maija', 'Mallikas', '123123', 'Mallikas.Maija@gmail.com', '$2y$10$u29vUCGL.gM7GB7JG6ULzOrBBBfsYWtlKKARnGdRxKv4LVQtxbJ1K'),
(15, '4', '4', '4', '4@4', '$2y$10$H/6F4kIe.IdmGLcvdNOlseu.dE775dNI687pYzuBV9t5Z718HrkoW');

-- --------------------------------------------------------

--
-- Rakenne taululle `vkauppa_kori_tuotteet`
--

DROP TABLE IF EXISTS `vkauppa_kori_tuotteet`;
CREATE TABLE IF NOT EXISTS `vkauppa_kori_tuotteet` (
  `kori_tuotteetid` int NOT NULL AUTO_INCREMENT,
  `ostoskoriid` int NOT NULL,
  `tuoteid` int NOT NULL,
  `maara` int NOT NULL,
  `hinta` decimal(10,2) NOT NULL,
  PRIMARY KEY (`kori_tuotteetid`),
  KEY `ostoskoriid` (`ostoskoriid`),
  KEY `tuoteid` (`tuoteid`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `vkauppa_kori_tuotteet`
--

INSERT INTO `vkauppa_kori_tuotteet` (`kori_tuotteetid`, `ostoskoriid`, `tuoteid`, `maara`, `hinta`) VALUES
(4, 17, 1, 1, 3.50),
(5, 17, 3, 1, 12.50),
(6, 18, 1, 1, 3.50),
(7, 18, 4, 1, 4.20),
(8, 18, 6, 1, 1.50),
(9, 18, 8, 2, 3.00),
(10, 19, 1, 1, 3.50),
(11, 19, 6, 1, 1.50),
(12, 20, 4, 3, 4.20),
(13, 21, 3, 1, 12.50),
(14, 22, 1, 1, 3.50),
(15, 23, 1, 3, 3.50),
(16, 23, 3, 1, 12.50),
(17, 24, 6, 2, 1.50),
(18, 25, 1, 1, 3.50),
(19, 26, 1, 1, 3.50),
(20, 27, 1, 1, 3.50),
(21, 28, 1, 1, 3.50),
(22, 29, 1, 1, 3.50),
(23, 30, 1, 1, 3.50),
(24, 31, 1, 1, 3.50),
(25, 32, 1, 1, 3.50),
(26, 33, 1, 1, 3.50),
(27, 34, 1, 1, 3.50),
(28, 35, 1, 1, 3.50),
(29, 36, 1, 1, 3.50),
(30, 37, 1, 1, 3.50),
(31, 38, 1, 1, 3.50),
(32, 39, 1, 1, 3.50),
(33, 40, 1, 1, 3.50),
(34, 41, 1, 1, 3.50),
(35, 42, 1, 1, 3.50),
(36, 43, 1, 1, 3.50),
(37, 44, 1, 1, 3.50),
(38, 45, 1, 1, 3.50),
(39, 46, 1, 1, 3.50),
(40, 47, 1, 1, 3.50),
(41, 48, 1, 1, 3.50),
(42, 49, 1, 1, 3.50),
(43, 49, 6, 1, 1.50),
(44, 50, 6, 1, 1.50),
(45, 51, 6, 1, 1.50),
(46, 52, 6, 1, 1.50),
(47, 53, 1, 1, 3.50),
(48, 54, 6, 1, 1.50),
(49, 55, 6, 1, 1.50),
(50, 56, 6, 1, 1.50),
(51, 57, 1, 1, 3.50),
(52, 58, 1, 1, 3.50),
(53, 59, 1, 1, 3.50),
(54, 60, 1, 1, 3.50),
(55, 61, 1, 1, 3.50),
(56, 62, 1, 1, 3.50),
(57, 63, 1, 1, 3.50),
(58, 64, 1, 1, 3.50),
(59, 65, 1, 1, 3.50),
(60, 66, 6, 1, 1.50),
(61, 67, 6, 1, 1.50),
(62, 68, 6, 1, 1.50),
(63, 69, 6, 1, 1.50),
(64, 70, 1, 1, 3.50),
(65, 71, 1, 1, 3.50),
(66, 72, 1, 1, 3.50),
(67, 73, 1, 1, 3.50),
(68, 74, 6, 1, 1.50),
(69, 75, 1, 1, 3.50),
(70, 76, 6, 1, 1.50),
(71, 77, 6, 1, 1.50),
(72, 78, 6, 1, 1.50),
(73, 79, 6, 1, 1.50),
(74, 79, 1, 3, 3.50),
(77, 96, 7, 2, 0.99),
(78, 96, 4, 2, 4.20),
(79, 97, 8, 2, 3.00),
(80, 97, 4, 2, 4.20),
(81, 98, 8, 2, 3.00),
(82, 98, 4, 2, 4.20),
(83, 99, 6, 2, 1.50),
(84, 99, 8, 2, 3.00),
(85, 100, 4, 2, 4.20),
(86, 100, 7, 2, 0.99);

-- --------------------------------------------------------

--
-- Rakenne taululle `vkauppa_kuljetus`
--

DROP TABLE IF EXISTS `vkauppa_kuljetus`;
CREATE TABLE IF NOT EXISTS `vkauppa_kuljetus` (
  `kuljetusid` int NOT NULL AUTO_INCREMENT,
  `kuljettajaid` int DEFAULT NULL,
  `tilausid` int NOT NULL,
  `katuosoite` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `tarkennus` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lng` double DEFAULT NULL,
  PRIMARY KEY (`kuljetusid`),
  KEY `tilausid` (`tilausid`),
  KEY `fk_kuljetus_kuljettaja` (`kuljettajaid`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `vkauppa_kuljetus`
--

INSERT INTO `vkauppa_kuljetus` (`kuljetusid`, `kuljettajaid`, `tilausid`, `katuosoite`, `tarkennus`, `lat`, `lng`) VALUES
(12, 4, 62, 'Taivaanpankontie 10g, Kuopio, FI', 'Kolmannes kerros ovi numero 15', 62.89893559999999, 27.6415744),
(19, 4, 69, 'Taivaanpankontie 10g, Kuopio, FI', 'Kolmannes kerros ovi numero 15', 62.89893559999999, 27.6415744),
(20, 4, 70, 'Retkeiliäntie, Kuopio, FI', 'Kolmannes kerros ovi numero 15', 62.9035179, 27.6316273),
(21, 4, 71, 'Taivaanpankontie 10g, Kuopio, FI', 'Kolmannes kerros ovi numero 15', 62.89893559999999, 27.6415744),
(22, 4, 72, 'Taivaanpankontie 10g, Kuopio, FI', 'Kolmannes kerros ovi numero 15', 62.89893559999999, 27.6415744),
(23, NULL, 74, 'Testikatu 5, Kuopio, FI', '1', NULL, NULL),
(24, NULL, 75, '1, kuopio, suomi', '1', NULL, NULL),
(25, NULL, 76, '1, kuopio, suomi', '1', NULL, NULL),
(26, NULL, 78, 'Aarteenetsijänkuja 5, kuopio, suomi', '1', NULL, NULL);

-- --------------------------------------------------------

--
-- Rakenne taululle `vkauppa_nouto`
--

DROP TABLE IF EXISTS `vkauppa_nouto`;
CREATE TABLE IF NOT EXISTS `vkauppa_nouto` (
  `noutoid` int NOT NULL AUTO_INCREMENT,
  `tilausid` int NOT NULL,
  `nouto_koodi` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`noutoid`),
  UNIQUE KEY `nouto_koodi` (`nouto_koodi`),
  KEY `tilausid` (`tilausid`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `vkauppa_nouto`
--

INSERT INTO `vkauppa_nouto` (`noutoid`, `tilausid`, `nouto_koodi`) VALUES
(6, 10, 'LQ2R9H'),
(7, 12, 'ZKF7NE'),
(8, 13, 'K726CR'),
(9, 14, 'FCXD49'),
(10, 15, 'BWGD5K'),
(11, 17, '8Q4TXW'),
(12, 21, '86YGX9'),
(13, 22, 'PFGK5Z'),
(14, 23, '3LKD9B'),
(15, 24, 'ST5FQH'),
(16, 25, 'HWJMQA'),
(17, 26, 'LTXR4K'),
(18, 27, 'GJ3FUR'),
(19, 28, 'VJZ4C7'),
(20, 29, 'X4FEBZ'),
(21, 30, 'A8Y34F'),
(22, 31, '7YU9D2'),
(23, 32, '8BVNXG'),
(24, 33, 'WL842P'),
(25, 34, '4C8T7P'),
(26, 35, 'WBX4VP'),
(27, 36, 'ZTFLPD'),
(28, 37, 'J3HUN7'),
(29, 38, 'FN57AC'),
(30, 39, 'H6BRFC'),
(31, 40, 'UAK349'),
(32, 41, '43PGME'),
(33, 42, '76PDEL'),
(34, 43, '78E9FZ'),
(35, 44, '7ZFH2N'),
(36, 45, 'MT4B5L'),
(37, 46, 'VT4378'),
(38, 47, '3QFNZB'),
(39, 48, 'QLRKSA'),
(40, 49, '7HNR62'),
(41, 50, 'GU39YK'),
(42, 51, '35NDP4'),
(43, 52, '6B3HKE'),
(44, 53, 'SKQBTE'),
(45, 54, '8J2MZN'),
(46, 55, '3RV42E'),
(47, 73, '9BEJFZ'),
(48, 77, 'L6TWMA');

-- --------------------------------------------------------

--
-- Rakenne taululle `vkauppa_ostoskori`
--

DROP TABLE IF EXISTS `vkauppa_ostoskori`;
CREATE TABLE IF NOT EXISTS `vkauppa_ostoskori` (
  `ostoskoriid` int NOT NULL AUTO_INCREMENT,
  `asiakasid` int NOT NULL,
  `tila` enum('kaytossa','maksettu','luovutettu') COLLATE utf8mb4_general_ci NOT NULL,
  `viimeksi_paivitetty` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ostoskoriid`),
  KEY `asiakasid` (`asiakasid`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `vkauppa_ostoskori`
--

INSERT INTO `vkauppa_ostoskori` (`ostoskoriid`, `asiakasid`, `tila`, `viimeksi_paivitetty`) VALUES
(17, 14, 'maksettu', '2025-12-01 08:40:30'),
(18, 14, 'maksettu', '2025-12-01 09:19:53'),
(19, 14, 'maksettu', '2025-12-01 09:27:58'),
(20, 14, 'maksettu', '2025-12-01 09:30:12'),
(21, 14, 'maksettu', '2025-12-01 10:55:49'),
(22, 14, 'maksettu', '2025-12-01 11:27:22'),
(23, 14, 'maksettu', '2025-12-01 11:39:32'),
(24, 14, 'maksettu', '2025-12-01 11:44:07'),
(25, 14, 'maksettu', '2025-12-02 07:00:17'),
(26, 14, 'maksettu', '2025-12-02 07:08:01'),
(27, 14, 'maksettu', '2025-12-02 09:49:40'),
(28, 14, 'maksettu', '2025-12-02 10:09:49'),
(29, 14, 'maksettu', '2025-12-02 10:23:54'),
(30, 14, 'maksettu', '2025-12-02 10:29:09'),
(31, 14, 'maksettu', '2025-12-02 10:30:15'),
(32, 14, 'maksettu', '2025-12-02 10:31:42'),
(33, 14, 'maksettu', '2025-12-02 10:45:31'),
(34, 14, 'maksettu', '2025-12-02 10:48:26'),
(35, 14, 'maksettu', '2025-12-02 10:50:18'),
(36, 14, 'maksettu', '2025-12-02 10:55:35'),
(37, 14, 'maksettu', '2025-12-02 10:58:04'),
(38, 14, 'maksettu', '2025-12-02 11:02:15'),
(39, 14, 'maksettu', '2025-12-02 11:27:58'),
(40, 14, 'maksettu', '2025-12-02 11:32:15'),
(41, 14, 'maksettu', '2025-12-02 11:32:57'),
(42, 14, 'maksettu', '2025-12-02 11:36:46'),
(43, 14, 'maksettu', '2025-12-02 11:39:24'),
(44, 14, 'maksettu', '2025-12-02 11:57:50'),
(45, 14, 'maksettu', '2025-12-02 11:59:53'),
(46, 14, 'maksettu', '2025-12-02 12:10:07'),
(47, 14, 'maksettu', '2025-12-02 12:14:59'),
(48, 14, 'maksettu', '2025-12-02 12:16:14'),
(49, 14, 'maksettu', '2025-12-02 12:18:22'),
(50, 14, 'maksettu', '2025-12-02 12:19:08'),
(51, 14, 'maksettu', '2025-12-02 12:22:22'),
(52, 14, 'maksettu', '2025-12-02 12:23:12'),
(53, 14, 'maksettu', '2025-12-02 12:32:31'),
(54, 14, 'maksettu', '2025-12-02 12:33:01'),
(55, 14, 'maksettu', '2025-12-02 12:33:28'),
(56, 14, 'maksettu', '2025-12-02 12:33:38'),
(57, 14, 'maksettu', '2025-12-02 12:40:37'),
(58, 14, 'maksettu', '2025-12-02 12:50:14'),
(59, 14, 'maksettu', '2025-12-02 12:54:05'),
(60, 14, 'maksettu', '2025-12-02 12:59:58'),
(61, 14, 'maksettu', '2025-12-03 07:46:05'),
(62, 14, 'maksettu', '2025-12-03 07:46:17'),
(63, 14, 'maksettu', '2025-12-03 08:04:19'),
(64, 14, 'maksettu', '2025-12-03 08:06:27'),
(65, 14, 'maksettu', '2025-12-03 08:09:10'),
(66, 14, 'maksettu', '2025-12-03 08:09:55'),
(67, 14, 'maksettu', '2025-12-03 08:12:20'),
(68, 14, 'maksettu', '2025-12-03 08:19:55'),
(69, 14, 'maksettu', '2025-12-03 08:49:40'),
(70, 14, 'maksettu', '2025-12-03 08:58:23'),
(71, 14, 'maksettu', '2025-12-03 08:58:54'),
(72, 14, 'maksettu', '2025-12-03 09:14:38'),
(73, 14, 'maksettu', '2025-12-03 09:16:31'),
(74, 14, 'maksettu', '2025-12-03 09:19:10'),
(75, 14, 'maksettu', '2025-12-03 09:31:39'),
(76, 14, 'maksettu', '2025-12-03 09:46:52'),
(77, 14, 'maksettu', '2025-12-03 09:57:56'),
(78, 14, 'maksettu', '2025-12-03 10:07:53'),
(79, 14, 'maksettu', '2025-12-03 10:14:33'),
(96, 15, 'maksettu', '2025-12-03 10:26:31'),
(97, 15, 'maksettu', '2025-12-03 10:45:45'),
(98, 15, 'maksettu', '2025-12-03 12:16:56'),
(99, 15, 'maksettu', '2025-12-03 12:53:59'),
(100, 15, 'maksettu', '2025-12-04 08:17:25');

-- --------------------------------------------------------

--
-- Rakenne taululle `vkauppa_tilaus`
--

DROP TABLE IF EXISTS `vkauppa_tilaus`;
CREATE TABLE IF NOT EXISTS `vkauppa_tilaus` (
  `tilausid` int NOT NULL AUTO_INCREMENT,
  `ostoskoriid` int NOT NULL,
  `tilaus_hinta` decimal(10,2) NOT NULL,
  `tila` enum('tilattu','pakkauksessa','odottaa_kuljetusta','odottaa_keraamista','kuljetuksessa','odottaa_noutamista','suoritettu','peruttu') COLLATE utf8mb4_general_ci DEFAULT 'tilattu',
  `pakkaajaid` int DEFAULT NULL,
  `tilaus_tyyli` enum('nouto','kuljetus') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tilattu_aika` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cancel_deadline` int DEFAULT NULL,
  PRIMARY KEY (`tilausid`),
  KEY `ostoskoriid` (`ostoskoriid`),
  KEY `fk_pakkaaja` (`pakkaajaid`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `vkauppa_tilaus`
--

INSERT INTO `vkauppa_tilaus` (`tilausid`, `ostoskoriid`, `tilaus_hinta`, `tila`, `pakkaajaid`, `tilaus_tyyli`, `tilattu_aika`, `cancel_deadline`) VALUES
(10, 17, 16.00, 'suoritettu', 2, 'nouto', '2025-12-02 07:05:54', NULL),
(11, 18, 15.20, 'suoritettu', 2, 'kuljetus', '2025-12-02 06:59:59', NULL),
(12, 19, 5.00, 'suoritettu', 2, 'nouto', '2025-12-02 07:05:45', NULL),
(13, 20, 12.60, 'suoritettu', 2, 'nouto', '2025-12-01 10:19:21', NULL),
(14, 21, 12.50, 'suoritettu', 2, 'nouto', '2025-12-02 07:05:38', NULL),
(15, 22, 3.50, 'suoritettu', 2, 'nouto', '2025-12-02 07:05:32', NULL),
(16, 23, 23.00, 'suoritettu', 2, 'kuljetus', '2025-12-02 07:07:42', NULL),
(17, 24, 3.00, 'suoritettu', 2, 'nouto', '2025-12-02 07:05:21', NULL),
(18, 25, 3.50, 'suoritettu', 2, 'kuljetus', '2025-12-02 07:07:34', NULL),
(19, 25, 3.50, 'suoritettu', 2, 'kuljetus', '2025-12-02 07:06:58', NULL),
(20, 26, 3.50, 'suoritettu', 2, 'kuljetus', '2025-12-02 07:08:42', NULL),
(21, 27, 3.50, 'suoritettu', 2, 'nouto', '2025-12-02 10:01:23', NULL),
(22, 28, 3.50, 'peruttu', NULL, 'nouto', '2025-12-02 10:19:05', NULL),
(23, 29, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 10:28:59', NULL),
(24, 30, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 10:30:12', NULL),
(25, 31, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 10:31:27', NULL),
(26, 32, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 10:32:31', NULL),
(27, 33, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:02:13', NULL),
(28, 34, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:02:10', NULL),
(29, 35, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:02:09', NULL),
(30, 36, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:02:08', NULL),
(31, 37, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:02:05', NULL),
(32, 38, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:26:00', 1764843498),
(33, 39, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:32:07', 1764844099),
(34, 40, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:32:48', 1764844368),
(35, 41, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:36:35', 1764844526),
(36, 42, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:39:21', 1764844636),
(37, 43, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:57:24', 1764844784),
(38, 44, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 11:58:22', 1764676702),
(39, 45, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 12:32:20', 1764846012),
(40, 46, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 12:32:18', 1764850300),
(41, 47, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 12:32:15', 1764851510),
(42, 48, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 12:32:20', 1764851510),
(43, 49, 5.00, 'peruttu', 2, 'nouto', '2025-12-02 12:32:21', 1764851510),
(44, 50, 1.50, 'peruttu', 2, 'nouto', '2025-12-02 12:32:24', 1764851510),
(45, 51, 1.50, 'peruttu', 2, 'nouto', '2025-12-02 12:32:25', 1764851525),
(46, 52, 1.50, 'peruttu', 2, 'nouto', '2025-12-02 12:32:27', 1764851510),
(47, 53, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 12:35:45', 1764851572),
(48, 54, 1.50, 'peruttu', 2, 'nouto', '2025-12-02 12:35:44', 1764851603),
(49, 55, 1.50, 'peruttu', 2, 'nouto', '2025-12-02 12:35:41', 1764851644),
(50, 56, 1.50, 'peruttu', 2, 'nouto', '2025-12-02 12:35:43', 1764851644),
(51, 57, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 12:50:12', 1764852070),
(52, 58, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 13:00:44', 1764852625),
(53, 59, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 12:59:56', 1764853116),
(54, 60, 3.50, 'peruttu', 2, 'nouto', '2025-12-02 13:00:45', 1764853231),
(55, 61, 3.50, 'peruttu', 2, 'nouto', '2025-12-03 08:58:19', 1764920832),
(56, 62, 3.50, 'suoritettu', 2, 'kuljetus', '2025-12-03 08:39:25', NULL),
(57, 63, 3.50, 'peruttu', NULL, 'kuljetus', '2025-12-03 08:58:17', NULL),
(58, 64, 3.50, 'peruttu', NULL, 'kuljetus', '2025-12-03 08:58:16', NULL),
(59, 65, 3.50, 'peruttu', NULL, 'kuljetus', '2025-12-03 08:58:21', NULL),
(60, 66, 1.50, 'peruttu', NULL, 'kuljetus', '2025-12-03 08:58:14', NULL),
(61, 67, 1.50, 'peruttu', NULL, 'kuljetus', '2025-12-03 08:58:12', NULL),
(62, 68, 1.50, 'suoritettu', 2, 'kuljetus', '2025-12-03 08:57:11', NULL),
(63, 69, 1.50, 'suoritettu', 2, 'kuljetus', '2025-12-03 08:57:59', NULL),
(64, 70, 3.50, 'peruttu', 2, 'kuljetus', '2025-12-03 09:31:37', NULL),
(65, 71, 3.50, 'peruttu', 2, 'kuljetus', '2025-12-03 09:31:37', NULL),
(66, 72, 3.50, 'peruttu', 2, 'kuljetus', '2025-12-03 09:31:35', NULL),
(67, 73, 3.50, 'peruttu', 2, 'kuljetus', '2025-12-03 09:31:34', NULL),
(68, 74, 1.50, 'peruttu', 2, 'kuljetus', '2025-12-03 09:31:32', NULL),
(69, 75, 3.50, 'suoritettu', 2, 'kuljetus', '2025-12-03 10:02:27', NULL),
(70, 76, 1.50, 'suoritettu', 2, 'kuljetus', '2025-12-03 10:04:05', NULL),
(71, 77, 1.50, 'suoritettu', 2, 'kuljetus', '2025-12-03 10:05:50', NULL),
(72, 78, 1.50, 'suoritettu', 2, 'kuljetus', '2025-12-03 10:14:20', NULL),
(73, 79, 12.00, 'suoritettu', 2, 'nouto', '2025-12-03 10:16:02', 1764929705),
(74, 96, 10.38, 'pakkauksessa', 2, 'kuljetus', '2025-12-04 07:55:13', NULL),
(75, 97, 14.40, 'tilattu', NULL, 'kuljetus', '2025-12-03 10:47:26', NULL),
(76, 98, 14.40, 'tilattu', NULL, 'kuljetus', '2025-12-03 12:52:42', NULL),
(77, 99, 9.00, 'suoritettu', NULL, 'nouto', '2025-12-04 08:11:09', NULL),
(78, 100, 10.38, 'tilattu', NULL, 'kuljetus', '2025-12-04 08:18:08', NULL);

-- --------------------------------------------------------

--
-- Rakenne taululle `vkauppa_tuotteet`
--

DROP TABLE IF EXISTS `vkauppa_tuotteet`;
CREATE TABLE IF NOT EXISTS `vkauppa_tuotteet` (
  `tuoteid` int NOT NULL AUTO_INCREMENT,
  `nimi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `hinta` decimal(10,2) NOT NULL,
  `kuvaus` varchar(4000) COLLATE utf8mb4_general_ci NOT NULL,
  `kuva` varchar(2048) COLLATE utf8mb4_general_ci NOT NULL,
  `varastossa` int NOT NULL,
  `luokka` enum('keitot','pasta-ja-riisi','liha-ja-kala','kasvikset-ja-salaatit','leivat-ja-pullat','jalkiruoat','valmiit-välipalat','juomat','leivonta-ja-jauhot','maitotuotteet-ja-kanamunat','mausteet-ja-suola','öljyt-ja-rasvat','viljat-ja-pavut','sokeri-ja-makeutus') COLLATE utf8mb4_general_ci NOT NULL,
  `aktiivinen` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`tuoteid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `vkauppa_tuotteet`
--

INSERT INTO `vkauppa_tuotteet` (`tuoteid`, `nimi`, `hinta`, `kuvaus`, `kuva`, `varastossa`, `luokka`, `aktiivinen`) VALUES
(1, 'Hernekeitto', 3.50, 'Perinteinen suomalainen hernekeitto', 'images/hernekeitto.png', 0, 'keitot', 1),
(2, 'Spaghetti Bolognese', 7.90, 'Pastaa ja jauhelihakastiketta', 'images/spaghetti.jpg', 14, 'pasta-ja-riisi', 1),
(3, 'Lohi file', 12.50, 'Tuore lohifilee', 'images/lohi.jpg', 0, 'liha-ja-kala', 1),
(4, 'Salaattisekoitus', 4.20, 'Tuoretta salaattia ja vihanneksia', 'images/salaatti.jpg', 13, 'kasvikset-ja-salaatit', 1),
(5, 'Suklaaleivos', 2.50, 'Makea suklaaleivos', 'images/suklaaleivos.jpg', 29, 'leivat-ja-pullat', 1),
(6, 'Maitorahka', 1.50, 'Proteiinipitoinen maitotuote', 'images/maitorahka.jpg', 34, 'maitotuotteet-ja-kanamunat', 1),
(7, 'Sokeri', 0.99, 'Valkoinen ruokosokeri', 'images/sokeri.jpg', 36, 'sokeri-ja-makeutus', 1),
(8, 'Porkkana (kg)', 3.00, 'porkkana', 'images/porkkana.png', 37, 'kasvikset-ja-salaatit', 1);

-- --------------------------------------------------------

--
-- Rakenne taululle `vkauppa_tyontekija`
--

DROP TABLE IF EXISTS `vkauppa_tyontekija`;
CREATE TABLE IF NOT EXISTS `vkauppa_tyontekija` (
  `tyontekijaid` int NOT NULL AUTO_INCREMENT,
  `kayttajanimi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `salasana` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `rooli` enum('Pakkaaja','Kuljettaja','Nouto_tyontekija') COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`tyontekijaid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `vkauppa_tyontekija`
--

INSERT INTO `vkauppa_tyontekija` (`tyontekijaid`, `kayttajanimi`, `salasana`, `rooli`) VALUES
(1, 'rike', '$2y$10$mRXzvpPpiUQMbhsvdb/DR.5NuvnL00du2PCGfxJCdzixxEDOpWCJi', 'Kuljettaja'),
(2, 'test', '$2y$10$hPfs/ut3221d3DGc5UPa6OGxyWahCAVri4JNh2Uc4I5Cd5buJjftC', 'Pakkaaja'),
(3, 'rik', '$2y$10$Y8S.OydDGRUuSUv44SBVAevItdH4f.LjsGxfTaCZn.EjL1rSmz5sm', 'Nouto_tyontekija'),
(4, 'erik', '$2y$10$dp.uQm88NIx7e8i3ZEoJrezASjyJDuxlS8OW5CnSXQ2xrJqZ8lG2q', 'Kuljettaja');

--
-- Rajoitteet vedostauluille
--

--
-- Rajoitteet taululle `vkauppa_kori_tuotteet`
--
ALTER TABLE `vkauppa_kori_tuotteet`
  ADD CONSTRAINT `vkauppa_kori_tuotteet_ibfk_1` FOREIGN KEY (`ostoskoriid`) REFERENCES `vkauppa_ostoskori` (`ostoskoriid`),
  ADD CONSTRAINT `vkauppa_kori_tuotteet_ibfk_2` FOREIGN KEY (`tuoteid`) REFERENCES `vkauppa_tuotteet` (`tuoteid`);

--
-- Rajoitteet taululle `vkauppa_kuljetus`
--
ALTER TABLE `vkauppa_kuljetus`
  ADD CONSTRAINT `fk_kuljetus_kuljettaja` FOREIGN KEY (`kuljettajaid`) REFERENCES `vkauppa_tyontekija` (`tyontekijaid`) ON UPDATE CASCADE,
  ADD CONSTRAINT `vkauppa_kuljetus_ibfk_1` FOREIGN KEY (`tilausid`) REFERENCES `vkauppa_tilaus` (`tilausid`);

--
-- Rajoitteet taululle `vkauppa_nouto`
--
ALTER TABLE `vkauppa_nouto`
  ADD CONSTRAINT `vkauppa_nouto_ibfk_1` FOREIGN KEY (`tilausid`) REFERENCES `vkauppa_tilaus` (`tilausid`);

--
-- Rajoitteet taululle `vkauppa_ostoskori`
--
ALTER TABLE `vkauppa_ostoskori`
  ADD CONSTRAINT `vkauppa_ostoskori_ibfk_1` FOREIGN KEY (`asiakasid`) REFERENCES `vkauppa_asiakas` (`asiakasid`);

--
-- Rajoitteet taululle `vkauppa_tilaus`
--
ALTER TABLE `vkauppa_tilaus`
  ADD CONSTRAINT `fk_pakkaaja` FOREIGN KEY (`pakkaajaid`) REFERENCES `vkauppa_tyontekija` (`tyontekijaid`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `vkauppa_tilaus_ibfk_1` FOREIGN KEY (`ostoskoriid`) REFERENCES `vkauppa_ostoskori` (`ostoskoriid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
