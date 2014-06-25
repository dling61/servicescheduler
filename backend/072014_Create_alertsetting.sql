-- phpMyAdmin SQL Dump
-- version 4.1.4
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 25, 2014 at 07:08 AM
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
-- Table structure for table `alertsetting`
--

CREATE TABLE IF NOT EXISTS `alertsetting` (
  `Id` int(10) NOT NULL,
  `Alert_Name` varchar(100) NOT NULL,
  `Active_Flag` tinyint(4) DEFAULT NULL,
  `Created_DateTime` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `alertsetting`
--

INSERT INTO `alertsetting` (`Id`, `Alert_Name`, `Active_Flag`, `Created_DateTime`) VALUES
(0, 'None', 1, '2014-05-26 18:07:14'),
(1, '5 minutes before', 0, '2014-05-26 18:07:14'),
(2, '15 minutes before', 1, '2014-05-26 18:07:14'),
(3, '30 minutes before', 1, '2014-05-26 18:07:14'),
(4, '1 hour before', 1, '2014-05-26 18:07:14'),
(5, '2 hours before', 1, '2014-05-26 18:07:14'),
(6, '1 day before', 1, '2014-05-26 18:07:14'),
(7, '2 days before', 1, '2014-05-26 18:07:14'),
(8, '3 days before', 1, '2014-05-26 18:07:14'),
(9, '7 days before', 1, '2014-05-26 18:07:14');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
