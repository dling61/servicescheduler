

CREATE TABLE IF NOT EXISTS `taskhelper` (
  `TaskHelper_Id` int(11) NOT NULL,
  `Task_Id` int(11) NOT NULL,
  `User_Id` int(11) NOT NULL,
  `Schedule_Id` int(11) NOT NULL,
  `Status` varchar(10) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE taskhelper ADD PRIMARY KEY(TaskHelper_Id);

ALTER TABLE taskhelper ADD UNIQUE gsc (Task_Id, Schedule_Id, User_Id);
