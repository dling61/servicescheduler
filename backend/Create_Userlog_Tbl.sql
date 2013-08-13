CREATE TABLE IF NOT EXISTS `userlog` (
  `User_Id` int(11) NOT NULL,
  `Udid` varchar(100) NOT NULL,
  `Token` varchar(100) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Expired_Time` datetime NOT NULL,
  `Logout_Time` datetime NOT NULL,
  UNIQUE KEY `main_index` (`User_Id`,`Udid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;