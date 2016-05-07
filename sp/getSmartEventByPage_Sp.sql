 
  DROP PROCEDURE IF EXISTS getSmartEventByNumberOfEvents;
  DELIMITER $$
  CREATE PROCEDURE `getSmartEventByNumberOfEvents`(IN communityid INT, IN p_from Date, IN numberOfEvents INT)
  BEGIN
  
  DECLARE start_date DATE;
  
  Drop table if exists tmp_event;
  Create table tmp_event (
  	`eventid` int(11) NOT NULL
  	) Engine=MyISAM;
	
  Drop table if exists tmp_task;
  Create table tmp_task (
  	`taskid` int(11) NOT NULL,
  	`eventid` int(11) NOT NULL,
  	`taskname` varchar(100) NOT NULL,
    `assignallowed` int(3) NOT NULL,
	`assignedgroup` int(3) NOT NULL,
    `description` varchar(2000) NOT NULL
  	) Engine=MyISAM;
  
  INSERT into tmp_event (eventid)
  SELECT distinct Event_Id 
     FROM event
     Where Community_Id = communityid
	 and (select FROM_UNIXTIME(Start_Datetime) as start_date) >= p_from 
	 and Is_Deleted = 0 order by event.Start_Datetime LIMIT numberOfEvents;
	 
  -- Event list
  SELECT Event_Id eventid, Community_Id communityid, Event_Name eventname, Creator_Id creatorid, Description description, FROM_UNIXTIME(Start_Datetime) startdatetime,
          FROM_UNIXTIME(End_Datetime) enddatetime, Alert alert, Tz_Id tzid, Status status, Is_Deleted isdeleted, Refer_Id referid, Location location, Host host, 
		  Repeat_Interval repeatinterval, From_Date fromdate, To_Date todate,
		  Created_Time createdtime, Last_Modified lastmodified
      FROM event 
	  where Event_Id in (select eventid from tmp_event);
  
  
  -- task list
  Insert into tmp_task (taskid, eventid, taskname, assignallowed, assignedgroup, description)
  SELECT distinct t.Task_Id taskid, t.Event_Id eventid, t.Task_Name taskname, t.Assign_Allowed assignallowed, t.Assigned_Group assignedgroup, t.Description description
      FROM tmp_event s, task t
      WHERE s.eventId = t.Event_Id and t.Is_Deleted = 0;

  SELECT distinct taskid, eventid, taskname, assignallowed, assignedgroup, description from tmp_task order by eventid;

  -- taskhelper list
  SELECT ta.TaskHelper_Id taskhelperid, ta.Task_Id taskid, ta.Event_Id eventid, ta.User_Id userid, u.User_Name username, u.Profile userprofile, ta.Status status 
     FROM taskhelper ta, user u
      WHERE  ta.Event_Id in (select distinct eventid from tmp_task) and ta.Is_Deleted = 0 and ta.User_Id = u.User_Id order by ta.Event_Id;
	
  -- assignment pool  
  SELECT ap.Assignmentpool_Id assignmentpoolid, ap.Task_Id taskid, ap.User_Id userid, us.User_Name username, us.Profile userprofile,
		ap.Is_Deleted isdeleted, ap.Created_Time createdtime, ap.Last_Modified lastmodified
	FROM tmp_task tt, assignmentpool ap, user us
	Where tt.taskid = ap.Task_Id and us.User_Id = ap.User_Id and ap.Is_Deleted = 0 order by ap.Task_Id;

  drop table tmp_task;
  drop table tmp_event;

  END $$
  DELIMITER ;