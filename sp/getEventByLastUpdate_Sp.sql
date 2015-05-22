  DELIMITER $$
  DROP PROCEDURE IF EXISTS getEventByLastUpdate;
  CREATE PROCEDURE `getEventByLastUpdate`(IN communityid INT, IN p_lastupdate Datetime)
  BEGIN

	
  Drop table if exists tmp_task;
  Create table tmp_task (
  	`taskid` int(11) NOT NULL,
  	`eventid` int(11) NOT NULL,
  	`taskname` varchar(100) NOT NULL,
        `assignallowed` int(3) NOT NULL,
	`assignedgroup` int(3) NOT NULL,
        `description` varchar(2000) NOT NULL
  	) Engine=MyISAM;
  

  -- Event list
  SELECT Schedule_Id eventid, Service_Id communityid, Schedule_Name eventname, Creator_Id creatorid, Description description, FROM_UNIXTIME(Start_Datetime) startdatetime,
          FROM_UNIXTIME(End_Datetime) enddatetime, Alert alert, Tz_Id tzid, Is_Deleted isdeleted, REvent_Id reventid, Location location, Host host, Created_Time createdtime, Last_Modified lastmodified
      FROM schedule where Service_Id = communityid and Last_Modified > p_lastupdate order by schedule.Start_Datetime;
  
  -- create a temp table
  -- task list
  Insert into tmp_task (taskid, eventid, taskname, assignallowed, assignedgroup, description)
  SELECT t.Task_Id taskid, s.Schedule_Id eventid, t.Task_Name taskname, t.Assign_Allowed assignallowed, t.Assigned_Group assignedgroup, t.Description description
      FROM schedule s, task t
      WHERE s.Service_Id = communityid and s.Schedule_Id = t.Schedule_Id and t.Is_Deleted = 0;

  SELECT distinct taskid, eventid, taskname, assignallowed, assignedgroup, description from tmp_task order by eventid;

  -- assignment list
  SELECT ta.Task_Id taskid, ta.Schedule_Id eventid, ta.User_Id userid, u.User_Name username, u.Profile userprofile, ta.Confirm confirm 
     FROM taskassigned ta, user u
      WHERE  ta.Schedule_Id in (select distinct eventid from tmp_task) and ta.Is_Deleted = 0 and ta.User_Id = u.User_Id order by ta.Schedule_Id;

  drop table tmp_task;

  END $$
  DELIMITER ;