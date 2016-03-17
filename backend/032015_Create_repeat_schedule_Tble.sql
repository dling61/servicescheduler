
--
-- Table structure for table `repeatschedule`
--

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
  PRIMARY KEY (`RSchedule_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


