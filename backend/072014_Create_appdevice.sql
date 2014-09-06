-- phpMyAdmin SQL Dump
-- version 4.1.4
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2014 at 08:40 PM
-- Server version: 5.6.15-log
-- PHP Version: 5.5.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cschedule`
--

-- --------------------------------------------------------

--
-- Table structure for table `appdevice`
--

CREATE TABLE IF NOT EXISTS `appdevice` (
  `Id` int(10) NOT NULL,
  `App_Version` varchar(100) NOT NULL,
  `Enforce_Flag` tinyint(4) DEFAULT '0',
  `OS` varchar(100) NOT NULL,
  `OS_Version` varchar(100) DEFAULT NULL,
  `Message` varchar(500) NOT NULL,
  `Created_DateTime` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `appdevice`
--

INSERT INTO `appdevice` VALUES(0, '1.3.0', 1, 'IOS', '7.0', 'This is a new version. It contains confirmation feature. Please update it', '2014-06-28 00:00:00');
INSERT INTO `appdevice` VALUES(1, '1.3.0', 1, 'ANDROID', '4.1', 'This is an update. Please update it', '2014-06-28 00:00:00');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
