  DELIMITER $$
  DROP PROCEDURE IF EXISTS getAssignementPoolByLastUpdate;
  CREATE PROCEDURE `getAssignmentPoolByLastUpdate`(IN taskid INT, IN p_lastupdate Datetime)
  BEGIN

	-- Only Participant Group
	SELECT ap.PGroup_Id pgroupid, pg.PGroup_Name pgroupname, pg.Is_Deleted pgisdeleted
		FROM assignmentpool ap, participantgroup pg
		WHERE ap.task_id = taskid and ap.PGroup_Id = pg.PGroup_Id
		and ap.Last_Modified > p_lastupdate;
  
	-- Participant Group and associated participants
	SELECT pg.PGroup_Id pgroupid, us.User_Id userid, us.User_Name username, us.Profile userprofile
		FROM assignmentpool ap, participantgroup pg, groupparticipant gp, user us 
		Where ap.task_id = taskid and ap.PGroup_Id = pg.PGroup_Id and pg.PGroup_Id = gp.PGroup_Id and gp.User_Id = us.User_Id
		and pg.Is_Deleted = 0 and ap.Last_Modified > p_lastupdate;


	-- Individual Participants 
	SELECT us.User_Id userid, us.User_Name username, us.Profile userprofile, ap.Is_Deleted isdeleted
	FROM assignmentpool ap, User us 
	WHERE ap.task_id = taskid and ap.User_Id = us.User_Id and ap.Last_Modified > p_lastupdate;
 
  END $$
  DELIMITER ;