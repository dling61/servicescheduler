
CREATE TABLE IF NOT EXISTS `sharedmember` (
  `Service_Id` int(11) NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `Access_Control` int(2) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
