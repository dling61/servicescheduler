
--
-- Table structure for table `task`
--

CREATE TABLE IF NOT EXISTS `task` (
  `Task_Id` int(11) NOT NULL,
  `Task_Name` varchar(200) NOT NULL,
  `Event_Id` int(11) NOT NULL,
  `Alert` int(11) NOT NULL,
  `Repeating` tinyint(1) NOT NULL,
  `Assign_Allowed` int(3) NOT NULL,
  `Assigned_Group` int(3) NOT NULL,
  `Start_DateTime` int(10) NOT NULL,
  `End_DateTime` int(10) NOT NULL,
  `Description` varchar(2000) NOT NULL,
  `DisplayOrder` int(2) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL,
  `Last_Modified` datetime NOT NULL,
  PRIMARY KEY (Task_Id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
