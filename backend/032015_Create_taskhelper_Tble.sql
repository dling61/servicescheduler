

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
  PRIMARY KEY(TaskHelper_Id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE taskhelper ADD UNIQUE gsc (Task_Id, Event_Id, User_Id);
