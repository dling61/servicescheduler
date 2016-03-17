-- phpMyAdmin SQL Dump
-- version 4.1.4
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2016 at 12:27 AM
-- Server version: 5.6.15-log
-- PHP Version: 5.5.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cschedule2016`
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

-- --------------------------------------------------------

--
-- Table structure for table `baseevent`
--

CREATE TABLE IF NOT EXISTS `baseevent` (
  `BEvent_Id` int(11) NOT NULL,
  `BEvent_Name` varchar(100) NOT NULL,
  `BEvent_StartTime` time NOT NULL,
  `BEvent_EndTime` time DEFAULT NULL,
  `BEvent_Location` varchar(400) NOT NULL,
  `BEvent_Host` varchar(100) DEFAULT NULL,
  `BEvent_Tz_Id` int(3) NOT NULL DEFAULT '0',
  `Is_Deleted` tinyint(1) NOT NULL DEFAULT '0',
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL,
  `Last_Modified` datetime NOT NULL,
  PRIMARY KEY (`BEvent_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `community`
--

CREATE TABLE IF NOT EXISTS `community` (
  `Community_Id` int(11) NOT NULL,
  `Community_name` varchar(100) DEFAULT NULL,
  `Description` varchar(200) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) DEFAULT NULL,
  PRIMARY KEY (`Community_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE IF NOT EXISTS `event` (
  `Event_Id` int(11) NOT NULL DEFAULT '0',
  `Event_Name` varchar(100) DEFAULT NULL,
  `Status` varchar(10) DEFAULT NULL,
  `Community_Id` int(11) NOT NULL,
  `Tz_Id` int(11) DEFAULT NULL,
  `Alert` int(11) DEFAULT NULL,
  `Location` varchar(200) DEFAULT NULL,
  `Host` varchar(200) DEFAULT NULL,
  `BEvent_Id` int(11) DEFAULT NULL,
  `Repeat_Schedule_Id` int(11) NOT NULL,
  `Start_DateTime` int(10) unsigned NOT NULL,
  `End_DateTime` int(10) unsigned NOT NULL,
  `Description` varchar(200) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) DEFAULT NULL,
  PRIMARY KEY (`Event_Id`),
  KEY `Service_Id` (`Community_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE IF NOT EXISTS `member` (
  `Member_Id` int(11) NOT NULL,
  `Member_Email` varchar(100) NOT NULL,
  `Member_Name` varchar(100) NOT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `Is_Registered` tinyint(1) NOT NULL DEFAULT '0',
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL DEFAULT '0',
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) DEFAULT NULL,
  PRIMARY KEY (`Member_Id`),
  KEY `Member_Email` (`Member_Email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `participant`
--

CREATE TABLE IF NOT EXISTS `participant` (
  `Participant_Id` int(11) NOT NULL,
  `Community_Id` int(11) NOT NULL,
  `User_Id` int(11) NOT NULL,
  `User_Role` int(2) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL,
  PRIMARY KEY (`Participant_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `participantgroup`
--

CREATE TABLE IF NOT EXISTS `participantgroup` (
  `PGroup_Id` int(11) NOT NULL,
  `PGroup_Name` varchar(100) NOT NULL,
  `Community_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL,
  PRIMARY KEY (`PGroup_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pushqueue`
--

CREATE TABLE IF NOT EXISTS `pushqueue` (
  `Pushqueue_Id` int(11) NOT NULL AUTO_INCREMENT,
  `Target_Token` varchar(4096) DEFAULT NULL,
  `Device_Id` int(2) NOT NULL DEFAULT '0',
  `Message` varchar(200) NOT NULL,
  `Sent_Time` datetime NOT NULL,
  `Created_Time` datetime NOT NULL,
  PRIMARY KEY (`Pushqueue_Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=125 ;

-- --------------------------------------------------------

--
-- Table structure for table `repeatschedule`
--

CREATE TABLE IF NOT EXISTS `repeatschedule` (
  `RSchedule_Id` int(11) NOT NULL,
  `BEvent_Id` int(11) NOT NULL,
  `Repeat_Interval` varchar(2000) DEFAULT NULL,
  `From_Date` date NOT NULL,
  `To_Date` date NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL DEFAULT '0',
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL,
  `Last_Modified` datetime NOT NULL,
  PRIMARY KEY (`RSchedule_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- Table structure for table `serverlog`
--

CREATE TABLE IF NOT EXISTS `serverlog` (
  `Log_Id` int(11) NOT NULL AUTO_INCREMENT,
  `URL_Resource` varchar(200) NOT NULL,
  `Action` varchar(10) NOT NULL,
  `Body` varchar(2048) NOT NULL,
  `Response` varchar(4096) NOT NULL,
  `Created_DateTime` datetime NOT NULL,
  `Client_Device_Id` int(11) NOT NULL,
  PRIMARY KEY (`Log_Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=58749 ;

-- --------------------------------------------------------

--
-- Table structure for table `task`
--

CREATE TABLE IF NOT EXISTS `task` (
  `Task_Id` int(11) NOT NULL,
  `Task_Name` varchar(200) NOT NULL,
  `Event_Id` int(11) NOT NULL,
  `Alert` int(11) NOT NULL,
  `Repeating` tinyint(1) NOT NULL,
  `Assign_Allowed` int(3) NOT NULL,
  `Assigned_Group` int(3) NOT NULL,
  `Start_DateTime` int(10) NOT NULL,
  `End_DateTime` int(10) NOT NULL,
  `Description` varchar(2000) NOT NULL,
  `DisplayOrder` int(2) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL,
  `Last_Modified` datetime NOT NULL,
  PRIMARY KEY (`Task_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `taskhelper`
--

CREATE TABLE IF NOT EXISTS `taskhelper` (
  `TaskHelper_Id` int(11) NOT NULL,
  `Task_Id` int(11) NOT NULL,
  `User_Id` int(11) NOT NULL,
  `Event_Id` int(11) NOT NULL,
  `Status` varchar(10) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL,
  PRIMARY KEY (`TaskHelper_Id`),
  UNIQUE KEY `gsc` (`Task_Id`,`Event_Id`,`User_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `User_id` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(100) NOT NULL,
  `User_Name` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `Mobile` varchar(20) DEFAULT NULL,
  `Profile` varchar(500) DEFAULT NULL,
  `User_Type` varchar(10) NOT NULL,
  `Verified` tinyint(1) NOT NULL DEFAULT '0',
  `Active` tinyint(1) DEFAULT '1',
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) DEFAULT '0',
  PRIMARY KEY (`User_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=143 ;

-- --------------------------------------------------------

--
-- Table structure for table `userlog`
--

CREATE TABLE IF NOT EXISTS `userlog` (
  `User_Id` int(11) NOT NULL,
  `Udid` varchar(100) NOT NULL,
  `Token` varchar(4096) DEFAULT NULL,
  `Device_Id` int(2) NOT NULL DEFAULT '0',
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Expired_Time` datetime NOT NULL,
  `Logout_Time` datetime NOT NULL,
  UNIQUE KEY `main_index` (`User_Id`,`Udid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
