-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 24, 2024 at 11:16 AM
-- Server version: 8.0.28
-- PHP Version: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `easyparking`
--
CREATE DATABASE IF NOT EXISTS `easyparking` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `easyparking`;

-- --------------------------------------------------------

--
-- Table structure for table `checkins`
--

DROP TABLE IF EXISTS `checkins`;
CREATE TABLE IF NOT EXISTS `checkins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `checkInTime` datetime NOT NULL,
  `intendedDuration` int NOT NULL,
  `checkOutTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `location` (`location`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `checkins`
--

INSERT INTO `checkins` (`id`, `username`, `location`, `checkInTime`, `intendedDuration`, `checkOutTime`) VALUES
(10, 'ken', 'Central Station', '2024-05-23 06:19:19', 3, '2024-05-23 09:19:19'),
(13, 'kay', 'Central Station', '2024-05-23 22:50:58', 5, '2024-05-24 03:50:58'),
(16, 'misthy', 'Central Station', '2024-05-24 05:05:25', 4, '2024-05-24 09:05:25'),
(17, 'minh', 'Central Station', '2024-05-24 05:05:45', 4, '2024-05-24 09:05:45'),
(18, 'chi', 'Central Station', '2024-05-24 06:35:05', 5, '2024-05-24 11:35:05'),
(19, 'misthy', 'Bankstown', '2024-05-24 06:54:48', 10, '2024-05-24 16:54:48'),
(21, 'ken', 'Cabra', '2024-05-24 09:12:53', 2, '2024-05-24 11:12:53'),
(22, 'misthy', 'Cabra', '2024-05-24 09:19:29', 3, '2024-05-24 12:19:29'),
(23, 'chi', 'Wollongong Central', '2024-05-24 10:23:54', 5, '2024-05-24 15:23:54'),
(24, 'kay', 'Cabra', '2024-05-24 10:24:49', 13, '2024-05-24 23:24:49');

-- --------------------------------------------------------

--
-- Table structure for table `parkinglocations`
--

DROP TABLE IF EXISTS `parkinglocations`;
CREATE TABLE IF NOT EXISTS `parkinglocations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `location` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `capacity` int NOT NULL,
  `costPH` decimal(4,2) NOT NULL,
  `lateCheckoutCostPH` decimal(4,2) NOT NULL,
  `availableSpaces` int NOT NULL,
  PRIMARY KEY (`location`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `parkinglocations`
--

INSERT INTO `parkinglocations` (`id`, `location`, `description`, `capacity`, `costPH`, `lateCheckoutCostPH`, `availableSpaces`) VALUES
(6, 'Bankstown', 'Biggest Parking Station In Bankstown', 500, 5.00, 2.00, 149),
(7, 'Cabra', 'A private carpark in Cabramatta', 30, 3.00, 2.00, 12),
(5, 'Central Station', 'Parking slots next to Central Station, Sydney', 200, 2.00, 3.00, 66),
(9, 'Erskinville', 'Ersk Park Location', 17, 3.00, 3.00, 40),
(2, 'Fairy Meadow', 'Parking station next to Fairy Meadow Railway Station', 10, 2.00, 2.00, 1),
(4, 'Marrickville', 'Parking station in Marrickville, Sydney', 50, 4.00, 3.00, 6),
(3, 'North Wollongong', 'Public parking station near North Wollongong Beach', 30, 2.00, 1.00, 5),
(1, 'Wollongong Central', 'A parking station near Wollongong Central Shopping Mall', 100, 3.00, 2.00, 53);

-- --------------------------------------------------------

--
-- Table structure for table `pastcheckouts`
--

DROP TABLE IF EXISTS `pastcheckouts`;
CREATE TABLE IF NOT EXISTS `pastcheckouts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `checkInTime` datetime NOT NULL,
  `checkOutTime` datetime NOT NULL,
  `intendedDuration` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pastcheckouts`
--

INSERT INTO `pastcheckouts` (`id`, `username`, `location`, `checkInTime`, `checkOutTime`, `intendedDuration`) VALUES
(1, 'ken', 'Fairy Meadow', '2024-05-22 18:58:00', '2024-05-22 18:58:30', 2),
(2, 'ken', 'Wollongong Central', '2024-05-22 18:57:55', '2024-05-22 19:10:04', 10),
(3, 'ken', 'Wollongong Central', '2024-05-23 05:30:47', '2024-05-23 05:31:44', 3),
(4, 'ken', 'North Wollongong', '2024-05-23 05:31:23', '2024-05-23 05:31:52', 3),
(5, 'ken', 'Marrickville', '2024-05-22 18:58:12', '2024-05-23 06:08:31', 3),
(6, 'ken', 'Central Station', '2024-05-22 19:09:28', '2024-05-23 06:08:36', 10),
(7, 'kay', 'Wollongong Central', '2024-05-23 08:51:32', '2024-05-23 22:51:03', 3),
(8, 'ken', 'Marrickville', '2024-05-23 06:19:35', '2024-05-24 07:14:23', 3),
(9, 'ken', 'Wollongong Central', '2024-05-23 06:17:37', '2024-05-24 07:14:26', 1),
(10, 'ken', 'Erskinville', '2024-05-24 07:14:18', '2024-05-24 07:14:47', 5),
(11, 'kay', 'Wollongong Central', '2024-05-23 23:25:29', '2024-05-24 10:24:54', 2),
(12, 'kay', 'Marrickville', '2024-05-23 23:56:13', '2024-05-24 10:25:04', 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` enum('Administrator','User') NOT NULL,
  PRIMARY KEY (`username`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `surname`, `phone`, `email`, `password`, `type`) VALUES
(1, 'binh', 'Binh', 'Nguyen', '0123456789', 'binhnguyen@gmail.com', '8bc5f33900a297aaff746ec84e4800f8', 'Administrator'),
(5, 'chi', 'Chi', 'Pham', '0567891234', 'chipham@gmail.com', '48a9921d18a959fa82cd6c86fad52031', 'Administrator'),
(2, 'kay', 'Kay', 'Tran', '0234567891', 'kaytran@gmail.com', '48bd34ce11591a5011cec9a3bbc27f70', 'User'),
(3, 'ken', 'Ken', 'Nguyen', '0345678912', 'kennguyen@gmail.com', 'd6172cee82aa0cd1e610aacab528f240', 'User'),
(7, 'mike', 'Mike', 'Vu', '0789123456', 'mikevu@gmail.com', '50c927e5f17adb9341381991ac2a999f', 'Administrator'),
(6, 'minh', 'Minh', 'Vu', '0678912345', 'minhvu@gmail.com', '616a1287fd70fd0e5feecef121abb685', 'User'),
(4, 'misthy', 'Misthy', 'Nguyen', '0456789123', 'misthynguyen@gmail.com', '510d6d37d93d93605e1cca0d91403615', 'User');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `checkins`
--
ALTER TABLE `checkins`
  ADD CONSTRAINT `checkins_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`),
  ADD CONSTRAINT `checkins_ibfk_2` FOREIGN KEY (`location`) REFERENCES `parkinglocations` (`location`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
