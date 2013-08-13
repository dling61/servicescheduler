-- phpMyAdmin SQL Dump
-- version 3.3.8
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 04, 2013 at 08:39 PM
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
-- Table structure for table `pushqueue`
--

CREATE TABLE IF NOT EXISTS `pushqueue` (
  `Pushqueue_Id` int(11) NOT NULL AUTO_INCREMENT,
  `Target_Token` varchar(100) NOT NULL,
  `Message` varchar(200) NOT NULL,
  `Sent_Time` datetime NOT NULL,
  `Created_Time` datetime NOT NULL,
  PRIMARY KEY (`Pushqueue_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

