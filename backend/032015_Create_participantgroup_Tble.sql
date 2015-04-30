

CREATE TABLE IF NOT EXISTS `participantgroup` (
  `PGroup_Id` int(11) NOT NULL,
  `PGroup_Name` varchar(100) NOT NULL,
  `Service_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
