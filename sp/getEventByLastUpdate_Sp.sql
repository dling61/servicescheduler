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
      FROM schedule where Service_Id = communityid and Last_Modified > p_lastupdate and Is_Deleted = 0 order by schedule.Start_Datetime;
  
  -- create a temp table
  -- task list
  Insert into tmp_task (taskid, eventid, taskname, assignallowed, assignedgroup, description)
  SELECT t.Task_Id taskid, s.Schedule_Id eventid, t.Task_Name taskname, t.Assign_Allowed assignallowed, t.Assigned_Group assignedgroup, t.Description description
      FROM schedule s, task t
      WHERE s.Service_Id = communityid and s.Schedule_Id = t.Schedule_Id and t.Is_Deleted = 0;

  SELECT distinct taskid, eventid, taskname, assignallowed, assignedgroup, description from tmp_task order by eventid;

  -- taskhelper list
  SELECT ta.TaskHelper_Id taskhelperid, ta.Task_Id taskid, ta.Schedule_Id eventid, ta.User_Id userid, u.User_Name username, u.Profile userprofile, ta.Status status 
     FROM taskhelper ta, user u
      WHERE  ta.Schedule_Id in (select distinct eventid from tmp_task) and ta.Is_Deleted = 0 and ta.User_Id = u.User_Id order by ta.Schedule_Id;
	  
  SELECT distinct bs.REvent_Id beventid, bs.REvent_Name eventname, bs.REvent_StartTime starttime, bs.REvent_EndTime endtime, bs.REvent_Location location,
		bs.REvent_Host host, bs.REvent_Tz_Id tzid, bs.Frequency frequency, bs.RepeatInterval binterval, bs.From bfrom, bs.To bto,
		bs.Is_Deleted isdeleted, bs.Created_Time createdtime, bs.Last_Modified lastmodified
	FROM baseschedule bs LEFT JOIN schedule sc
	ON bs.REvent_Id = sc.REvent_Id and sc.Service_Id = communityid and bs.Last_Modified > p_lastupdate and bs.Is_Deleted = 0 order by bs.REvent_Id;

  drop table tmp_task;

  END $$
  DELIMITER ;