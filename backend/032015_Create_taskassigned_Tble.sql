

CREATE TABLE IF NOT EXISTS `taskassigned` (
  `Task_Id` int(11) NOT NULL,
  `User_Id` int(11) NOT NULL,
  `Schedule_Id` int(11) NOT NULL,
  `Confirm` tinyint(1) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
