  DELIMITER $$
  DROP PROCEDURE IF EXISTS getScheduleByLastUpdate;
  CREATE PROCEDURE `getScheduleByLastUpdate`(IN ownerid INT, IN p_lastupdate Datetime)
  BEGIN
  
Drop table if exists tmp_schedule;
    Create table tmp_schedule (
  `Schedule_Id` int(11) NOT NULL,
  `Service_Id` int(11) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Start_Datetime` int(10) NOT NULL,
  `End_Datetime` int(10) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `OD_Is_Deleted` tinyint(1) not null,
  `InputId` tinyint(1) not null) Engine=MyISAM;
  
  
  Insert into tmp_schedule (Schedule_Id,Service_Id,Description,Creator_Id,Start_Datetime,End_Datetime,Is_Deleted,Created_Time,Last_Modified,OD_Is_Deleted, InputId)
  SELECT distinct s.Schedule_Id, s.Service_Id, s.Description, s.Creator_Id, s.Start_Datetime, s.End_Datetime,s.Is_Deleted,s.Created_Time,s.Last_Modified,s.Is_Deleted,0 
  from onduty o, schedule s where o.Service_Id = s.Service_Id 
  and s.Last_Modified > p_lastupdate
  and o.Service_Id in 
  (select distinct Service_Id from onduty where member_id in 
  ((select member_id from member 
     where Member_Email = (select Email from user where User_Id = ownerid) and member.Is_Deleted = 0
  ))
  );
  
  -- schedule list
  IF p_lastupdate !="0000-00-00 00:00:00" THEN
  -- follow updates
   (SELECT tmp.Schedule_Id scheduleid,tmp.Service_Id serviceid, tmp.Creator_Id creatorid, tmp.Description description,FROM_UNIXTIME(tmp.Start_Datetime) starttime,
      FROM_UNIXTIME(tmp.End_Datetime) endtime,tmp.Is_Deleted isdeleted,tmp.OD_Is_Deleted odisdeleted,tmp.Created_Time createdtime,tmp.Last_Modified lastmodified 
      FROM tmp_schedule tmp)
	UNION
	(SELECT Schedule_Id scheduleid, Service_Id serviceid, Creator_Id creatorid,Description description, FROM_UNIXTIME(Start_Datetime),
          FROM_UNIXTIME(End_Datetime), Is_Deleted isdeleted, 0, Created_Time createdtime, Last_Modified lastmodified
      FROM schedule where Creator_Id = ownerId and Last_Modified > p_lastupdate);
  ELSE 
  -- first update
    (SELECT tmp.Schedule_Id scheduleid,tmp.Service_Id serviceid, tmp.Creator_Id creatorid, tmp.Description description,FROM_UNIXTIME(tmp.Start_Datetime) starttime,
      FROM_UNIXTIME(tmp.End_Datetime) endtime,tmp.Is_Deleted isdeleted,tmp.OD_Is_Deleted odisdeleted,tmp.Created_Time createdtime,tmp.Last_Modified lastmodified 
      FROM tmp_schedule tmp)
    UNION
    (SELECT Schedule_Id scheduleid, Service_Id serviceid, Creator_Id creatorid,Description description, FROM_UNIXTIME(Start_Datetime),
          FROM_UNIXTIME(End_Datetime), Is_Deleted isdeleted, 0, Created_Time createdtime, Last_Modified lastmodified
      FROM schedule where Creator_Id = ownerId and Is_Deleted = 0);
  END IF;

  -- Member list
  IF p_lastupdate !="0000-00-00 00:00:00" THEN
  -- following updates
    (SELECT tmp.Schedule_Id scheduleid, ot.Member_Id memberid 
      FROM tmp_schedule tmp,onduty ot
        WHERE  (tmp.Is_Deleted = 0 or tmp.OD_Is_Deleted = 0)
        and tmp.Schedule_Id = ot.Schedule_Id)
	UNION
	(SELECT s.Schedule_Id scheduleid, o.Member_Id memberid
      FROM schedule s, onduty o
      WHERE s.Creator_Id = ownerid and s.Schedule_Id = o.Schedule_Id and s.Last_Modified > p_lastupdate);   
  ELSE 
  -- first update
    (SELECT tmp.Schedule_Id scheduleid, ot.Member_Id memberid 
      FROM tmp_schedule tmp,onduty ot
        WHERE  (tmp.Is_Deleted = 0 or tmp.OD_Is_Deleted = 0)
        and tmp.Schedule_Id = ot.Schedule_Id) 
    UNION
    (SELECT s.Schedule_Id scheduleid, o.Member_Id memberid
      FROM schedule s, onduty o
      WHERE s.Creator_Id = ownerid and s.Schedule_Id = o.Schedule_Id and s.Is_Deleted = 0);
  END IF;

  DROP TABLE  tmp_schedule;
  END $$
  DELIMITER ;