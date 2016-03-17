-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: 50.63.226.34
-- Generation Time: Feb 23, 2016 at 04:27 PM
-- Server version: 5.0.96
-- PHP Version: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `sscheduler`
--

-- --------------------------------------------------------

--
-- Table structure for table `alertsetting`
--

CREATE TABLE `alertsetting` (
  `Id` int(10) NOT NULL,
  `Alert_Name` varchar(100) NOT NULL,
  `Active_Flag` tinyint(4) default NULL,
  `Created_DateTime` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `appdevice`
--

CREATE TABLE `appdevice` (
  `Id` int(10) NOT NULL,
  `App_Version` varchar(100) NOT NULL,
  `Enforce_Flag` tinyint(4) default '0',
  `OS` varchar(100) NOT NULL,
  `OS_Version` varchar(100) default NULL,
  `Message` varchar(500) NOT NULL,
  `Created_DateTime` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `Member_Id` int(11) NOT NULL,
  `Member_Email` varchar(100) NOT NULL,
  `Member_Name` varchar(100) NOT NULL,
  `mobile_number` varchar(20) default NULL,
  `Is_Registered` tinyint(1) NOT NULL default '0',
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL default '0',
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) default NULL,
  PRIMARY KEY  (`Member_Id`),
  KEY `Member_Email` (`Member_Email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `onduty`
--

CREATE TABLE `onduty` (
  `Service_Id` int(11) NOT NULL,
  `Schedule_Id` int(11) NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `Confirm` int(2) default '0',
  `Is_Deleted` tinyint(1) NOT NULL default '0',
  `Created_Time` datetime NOT NULL,
  `Creator_Id` int(11) default NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) default NULL,
  KEY `Member_Id` (`Member_Id`),
  KEY `Schedule_Id` (`Schedule_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pushqueue`
--

CREATE TABLE `pushqueue` (
  `Pushqueue_Id` int(11) NOT NULL auto_increment,
  `Target_Token` varchar(4096) default NULL,
  `Device_Id` int(2) NOT NULL default '0',
  `Message` varchar(200) NOT NULL,
  `Sent_Time` datetime NOT NULL,
  `Created_Time` datetime NOT NULL,
  PRIMARY KEY  (`Pushqueue_Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=125 ;

-- --------------------------------------------------------

--
-- Table structure for table `resetpassword`
--

CREATE TABLE `resetpassword` (
  `Request_Id` int(11) NOT NULL auto_increment,
  `Email` varchar(100) NOT NULL,
  `Token` int(11) NOT NULL,
  `Is_Done` tinyint(1) NOT NULL,
  `Expired_Time` int(10) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  PRIMARY KEY  (`Request_Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `Schedule_Id` int(11) NOT NULL,
  `Service_Id` int(11) NOT NULL,
  `Tz_Id` int(11) default NULL,
  `Alert` int(11) default NULL,
  `Start_DateTime` int(10) unsigned NOT NULL,
  `End_DateTime` int(10) unsigned NOT NULL,
  `Description` varchar(200) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) default NULL,
  KEY `Service_Id` (`Service_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `serverlog`
--

CREATE TABLE `serverlog` (
  `Log_Id` int(11) NOT NULL auto_increment,
  `URL_Resource` varchar(200) NOT NULL,
  `Action` varchar(10) NOT NULL,
  `Body` varchar(2048) NOT NULL,
  `Response` varchar(4096) NOT NULL,
  `Created_DateTime` datetime NOT NULL,
  `Client_Device_Id` int(11) NOT NULL,
  PRIMARY KEY  (`Log_Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=58597 ;

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `Service_Id` int(11) NOT NULL,
  `Service_Name` varchar(100) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sharedmember`
--

CREATE TABLE `sharedmember` (
  `Service_Id` int(11) NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `Shared_Role` int(2) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `timezonedb`
--

CREATE TABLE `timezonedb` (
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

CREATE TABLE `user` (
  `User_id` int(11) NOT NULL auto_increment,
  `Email` varchar(100) NOT NULL,
  `User_Name` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `Mobile` varchar(20) default NULL,
  `User_Type` varchar(10) NOT NULL,
  `Verified` tinyint(1) NOT NULL default '0',
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  PRIMARY KEY  (`User_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=140 ;

-- --------------------------------------------------------

--
-- Table structure for table `userlog`
--

CREATE TABLE `userlog` (
  `User_Id` int(11) NOT NULL,
  `Udid` varchar(100) NOT NULL,
  `Token` varchar(4096) default NULL,
  `Device_Id` int(2) NOT NULL default '0',
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Expired_Time` datetime NOT NULL,
  `Logout_Time` datetime NOT NULL,
  UNIQUE KEY `main_index` (`User_Id`,`Udid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
