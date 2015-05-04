  DELIMITER $$
  DROP PROCEDURE IF EXISTS getPGroupByLastUpdate;
  CREATE PROCEDURE `getPGroupByLastUpdate`(IN serviceid INT, IN p_lastupdate Datetime)
  BEGIN
  

  -- Participant group list
  SELECT PGroup_Id pgroupid, PGroup_Name pgroupname, Is_Deleted isdeleted
      FROM participantgroup where Service_Id = serviceid and Last_Modified > p_lastupdate;
  

  -- User list
  SELECT gp.PGroup_Id pgroupid, gp.User_Id userid
      FROM participantgroup pg, groupparticipant gp
      WHERE pg.PGroup_Id = gp.PGroup_Id and pg.Service_Id = serviceid and pg.Is_Deleted = 0;
 
  END $$
  DELIMITER ;