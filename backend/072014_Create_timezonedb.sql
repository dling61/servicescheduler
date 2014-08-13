-- phpMyAdmin SQL Dump
-- version 4.1.4
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2014 at 06:18 AM
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
-- Table structure for table `timezonedb`
--

CREATE TABLE IF NOT EXISTS `timezonedb` (
  `Id` int(11) NOT NULL,
  `Tz_Name` varchar(100) NOT NULL,
  `Display_Name` varchar(500) NOT NULL,
  `Display_Order` int(11) NOT NULL,
  `Active_Flag` tinyint(4) NOT NULL,
  `Created_DateTime` datetime NOT NULL,
  `Effective_Date` date NOT NULL,
  `End_Date` date NOT NULL,
  `Abbr` varchar(10) NOT NULL,
  UNIQUE KEY `id_index` (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `timezonedb`
--

INSERT INTO `timezonedb` (`Id`, `Tz_Name`, `Display_Name`, `Display_Order`, `Active_Flag`, `Created_DateTime`, `Effective_Date`, `End_Date`, `Abbr`) VALUES
(1, 'Pacific/Honolulu', '(UTC-10:00) Hawaii', 5, 1, '2014-05-25 21:17:10', '0000-00-00', '0000-00-00', 'HAST'),
(2, 'America/Anchorage', '(UTC-10:00) Alaska', 6, 1, '2014-05-25 21:15:46', '0000-00-00', '0000-00-00', 'AKST'),
(3, 'America/Los_Angeles', '(UTC-08:00) Pacific Time (US & Canada)', 1, 1, '2014-05-25 21:15:46', '0000-00-00', '0000-00-00', 'PST'),
(4, 'America/Phoenix', '(UTC-07:00) Mountain Time (US & Canada)', 2, 1, '2014-05-25 21:15:46', '0000-00-00', '0000-00-00', 'MST'),
(5, 'America/Chicago', '(UTC-06:00) Central Time (US & Canada)', 3, 1, '2014-05-25 21:15:46', '0000-00-00', '0000-00-00', 'CST'),
(6, 'America/New_York', '(UTC-05:00) Eastern Time', 4, 1, '2014-05-25 21:15:46', '0000-00-00', '0000-00-00', 'EST');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
