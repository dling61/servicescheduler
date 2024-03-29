  DELIMITER $$
  DROP PROCEDURE IF EXISTS getScheduleByLastUpdate;
  CREATE PROCEDURE `getScheduleByLastUpdate`(IN serviceid INT, IN p_lastupdate Datetime)
  BEGIN
  

  -- Schedule list
  SELECT Schedule_Id scheduleid, Service_Id serviceid, Creator_Id creatorid,Description description, FROM_UNIXTIME(Start_Datetime) startdatetime,
          FROM_UNIXTIME(End_Datetime) enddatetime, Alert alert, Tz_Id tzid, Is_Deleted isdeleted, 0, Created_Time createdtime, Last_Modified lastmodified
      FROM schedule where Service_Id = serviceid and Last_Modified > p_lastupdate;
  

  -- Member list
  SELECT s.Schedule_Id scheduleid, o.Member_Id memberid, o.Confirm confirm
      FROM schedule s, onduty o
      WHERE s.Service_Id = serviceid and s.Schedule_Id = o.Schedule_Id and s.Is_Deleted = 0;
 
  END $$
  DELIMITER ;