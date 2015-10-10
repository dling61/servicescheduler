
--
-- Table structure for table `baseschedule`
--

CREATE TABLE IF NOT EXISTS `baseschedule` (
  `REvent_Id` int(11) NOT NULL,
  `REvent_Name` varchar(100) NOT NULL,
  `REvent_StartTime` time NOT NULL,
  `REvent_EndTime` time DEFAULT NULL,
  `REvent_Location` varchar(200) NOT NULL,
  `REvent_Host` varchar(100) DEFAULT NULL,
  `REvent_Tz_Id` int(3) NOT NULL DEFAULT '0',
  `Frequency` varchar(10) DEFAULT NULL,
  `RepeatInterval` int(5) DEFAULT NULL,
  `From` date NOT NULL,
  `To` date NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL DEFAULT '0',
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL,
  `Last_Modified` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
