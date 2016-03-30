  
  DROP PROCEDURE IF EXISTS getBaseEventByLastUpdate;
  DELIMITER $$
  CREATE PROCEDURE `getBaseEventByLastUpdate`(IN communityid INT, IN p_lastupdate Datetime)
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
  

  -- base event list  
  SELECT distinct bs.BEvent_Id beventid, bs.BEvent_Name beventname, bs.BEvent_StartTime starttime, bs.BEvent_EndTime endtime, bs.BEvent_Location location,
		bs.BEvent_Host host, bs.BEvent_Tz_Id tzid,
		bs.Is_Deleted isdeleted, bs.Created_Time createdtime, bs.Last_Modified lastmodified
	FROM baseevent bs 
	where bs.Community_Id = communityid and bs.Last_Modified > p_lastupdate and bs.Is_Deleted = 0 order by bs.BEvent_Id;
  
  -- create a temp table for repeating task list
  Insert into tmp_task (taskid, taskname, eventid, beventid, assignallowed, assignedgroup, description)
  SELECT t.Task_Id taskid, t.Task_Name taskname, t.Event_Id eventid, t.BEvent_Id beventid, t.Assign_Allowed assignallowed, t.Assigned_Group assignedgroup, t.Description description
      FROM task t
      WHERE t.BEvent_Id in (select distinct t.BEvent_id from baseevent where Community_Id = communityid) and t.Is_Deleted = 0;

  SELECT distinct taskid, taskname, beventid, assignallowed, assignedgroup, description from tmp_task order by beventid;

  -- create a assignment pool list
  SELECT ap.Task_Id taskid, ap.User_Id userid, u.User_Name username, u.Profile userprofile 
     FROM assignmentpool ap, user u
      WHERE  ap.Task_Id in (select distinct taskid from tmp_task) and ap.Is_Deleted = 0 and ap.User_Id = u.User_Id order by ap.Task_Id;
	
  drop table tmp_task;
 
  END $$
  DELIMITER ;