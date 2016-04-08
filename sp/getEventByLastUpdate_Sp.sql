 
  DROP PROCEDURE IF EXISTS getEventByLastUpdate;
  DELIMITER $$
  CREATE PROCEDURE `getEventByLastUpdate`(IN communityid INT, IN p_lastupdate Datetime)
  BEGIN

	
  Drop table if exists tmp_task;
  Create table tmp_task (
  	`taskid` int(11) NOT NULL,
  	`eventid` int(11) NOT NULL,
  	`taskname` varchar(100) NOT NULL,
	`beventid` int(11) NOT NULL,
    `assignallowed` int(3) NOT NULL,
	`assignedgroup` int(3) NOT NULL,
    `description` varchar(2000) NOT NULL
  	) Engine=MyISAM;
  

  -- Event list
  SELECT Event_Id eventid, Community_Id communityid, Event_Name eventname, Creator_Id creatorid, Description description, FROM_UNIXTIME(Start_Datetime) startdatetime,
          FROM_UNIXTIME(End_Datetime) enddatetime, Alert alert, Tz_Id tzid, Status status, Is_Deleted isdeleted, BEvent_Id beventid, Location location, Host host, Created_Time createdtime, Last_Modified lastmodified
      FROM event where Community_Id = communityid and Last_Modified > p_lastupdate and Is_Deleted = 0 order by event.Start_Datetime;
  
  -- create a temp table
  -- task list
  Insert into tmp_task (taskid, eventid, taskname, beventid, assignallowed, assignedgroup, description)
  SELECT t.Task_Id taskid, s.Event_Id eventid, t.Task_Name taskname, t.BEvent_Id beventid, t.Assign_Allowed assignallowed, t.Assigned_Group assignedgroup, t.Description description
      FROM event s, task t
      WHERE s.Community_Id = communityid and s.Event_Id = t.Event_Id and t.Is_Deleted = 0;

  SELECT distinct taskid, eventid, taskname, beventid, assignallowed, assignedgroup, description from tmp_task order by eventid;

  -- taskhelper list
  SELECT ta.TaskHelper_Id taskhelperid, ta.Task_Id taskid, ta.Event_Id eventid, ta.User_Id userid, u.User_Name username, u.Profile userprofile, ta.Status status 
     FROM taskhelper ta, user u
      WHERE  ta.Event_Id in (select distinct eventid from tmp_task) and ta.Is_Deleted = 0 and ta.User_Id = u.User_Id order by ta.Event_Id;
	
  -- base event list  
  SELECT distinct bs.BEvent_Id beventid, bs.BEvent_Name beventname, bs.BEvent_StartTime starttime, bs.BEvent_EndTime endtime, bs.BEvent_Location location,
		bs.BEvent_Host host, bs.BEvent_Tz_Id tzid,
		bs.Is_Deleted isdeleted, bs.Created_Time createdtime, bs.Last_Modified lastmodified
	FROM baseevent bs LEFT JOIN event sc
	ON bs.BEvent_Id = sc.BEvent_Id and sc.Community_Id = communityid and bs.Last_Modified > p_lastupdate and bs.Is_Deleted = 0 order by bs.BEvent_Id;

  drop table tmp_task;

  END $$
  DELIMITER ;