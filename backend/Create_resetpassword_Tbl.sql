-- phpMyAdmin SQL Dump
-- version 3.3.8
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2013 at 05:25 AM
-- Server version: 5.1.52
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `servicescheduler`
--

-- --------------------------------------------------------

--
-- Table structure for table `resetpassword`
--

CREATE TABLE IF NOT EXISTS `resetpassword` (
  `Request_Id` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(100) NOT NULL,
  `Token` int(11) NOT NULL,
  `Is_Done` tinyint(1) NOT NULL,
  `Expired_Time` int(10) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  PRIMARY KEY (`Request_Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;
