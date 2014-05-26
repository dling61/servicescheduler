-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: 68.178.216.120
-- Generation Time: May 25, 2014 at 09:53 PM
-- Server version: 5.0.96
-- PHP Version: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `testscheduler2`
--

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
  `Target_Token` varchar(100) NOT NULL,
  `Message` varchar(200) NOT NULL,
  `Sent_Time` datetime NOT NULL,
  `Created_Time` datetime NOT NULL,
  PRIMARY KEY  (`Pushqueue_Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `Schedule_Id` int(11) NOT NULL,
  `Service_Id` int(11) NOT NULL,
  `Start_DateTime` int(10) unsigned NOT NULL,
  `End_DateTime` int(10) unsigned NOT NULL,
  `Description` varchar(1000) NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=57217 ;

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `Service_Id` int(11) NOT NULL,
  `Service_Name` varchar(100) NOT NULL,
  `Description` varchar(2000) NOT NULL,
  `SRepeat` int(11) NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL,
  `From_Date` date NOT NULL,
  `To_Date` date NOT NULL,
  `Start_Datetime` int(10) NOT NULL,
  `End_Datetime` int(10) NOT NULL,
  `UTC_Off` int(10) NOT NULL,
  `Alert` int(11) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tmp_alert`
--

CREATE TABLE `tmp_alert` (
  `Schedule_Id` int(11) NOT NULL,
  `Service_Id` int(11) NOT NULL,
  `Service_Name` varchar(100) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `SDescription` varchar(200) NOT NULL,
  `SUTC_Off` int(10) NOT NULL,
  `Start_Datetime` int(10) NOT NULL,
  `Cur_Datetime` int(10) NOT NULL,
  `Alert_Setting` int(11) NOT NULL,
  `Alert` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  PRIMARY KEY  (`User_id`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=369 ;

-- --------------------------------------------------------

--
-- Table structure for table `userlog`
--

CREATE TABLE `userlog` (
  `User_Id` int(11) NOT NULL,
  `Udid` varchar(100) NOT NULL,
  `Token` varchar(100) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Expired_Time` datetime NOT NULL,
  `Logout_Time` datetime NOT NULL,
  UNIQUE KEY `main_index` (`User_Id`,`Udid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
