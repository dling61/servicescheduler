
--
-- Table structure for table `baseevent`
--

CREATE TABLE IF NOT EXISTS `baseevent` (
  `REvent_Id` int(11) NOT NULL,
  `REvent_Name` varchar(100) NOT NULL,
  `REvent_StartTime` time NOT NULL,
  `REvent_EndTime` time DEFAULT NULL,
  `REvent_Location` varchar(400) NOT NULL,
  `REvent_Host` varchar(100) DEFAULT NULL,
  `REvent_Tz_Id` int(3) NOT NULL DEFAULT '0',
  `Is_Deleted` tinyint(1) NOT NULL DEFAULT '0',
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL,
  `Last_Modified` datetime NOT NULL,
   PRIMARY KEY (REvent_Id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `repeatschedule` (
  `RSchedule_Id` int(11) NOT NULL,
  `REvent_Id` int(11) NOT NULL,
  `Repeat_Interval` varchar(2000) DEFAULT NULL,
  `From_Date` date NOT NULL,
  `To_Date` date NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL DEFAULT '0',
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL,
  `Last_Modified` datetime NOT NULL,
   PRIMARY KEY (RSchedule_Id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;




