-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jun 03, 2026 at 10:42 AM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ikwook_cloud_logiref_equitybank_main`
--

-- --------------------------------------------------------

--
-- Table structure for table `logiref_email_logs`
--

CREATE TABLE `logiref_email_logs` (
  `Id` int(11) NOT NULL,
  `Module_1` varchar(100) DEFAULT NULL,
  `Reference_2` varchar(100) DEFAULT NULL,
  `RecipientEmail_3` varchar(150) NOT NULL,
  `Subject_4` varchar(255) NOT NULL,
  `Message_5` longtext,
  `Provider_6` varchar(100) DEFAULT NULL,
  `ProviderMessageId_7` varchar(150) DEFAULT NULL,
  `Status_8` enum('PENDING','SENT','FAILED','DELIVERED','UNDELIVERED') DEFAULT 'PENDING',
  `ErrorMessage_9` text,
  `ResponsePayload_10` longtext,
  `SentAt_11` datetime DEFAULT NULL,
  `CreatedAt_12` datetime DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt_13` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `PaymentId_14` bigint(20) DEFAULT NULL,
  `NoteNumbers_15` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `logiref_email_logs`
--
ALTER TABLE `logiref_email_logs`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `logiref_email_logs`
--
ALTER TABLE `logiref_email_logs`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
