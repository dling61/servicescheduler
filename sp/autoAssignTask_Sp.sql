 
  DROP PROCEDURE IF EXISTS autoAssignTask;
  DELIMITER $$
  CREATE PROCEDURE `autoAssignTask`(IN baseEventId INT, IN ownerId INT, IN taskArray VARCHAR(255), IN initTaskHelperId INT, OUT lastTaskHelperId INT)
  BEGIN
  
	DECLARE strLen    			INT DEFAULT 0;
	DECLARE subStrLen 			INT DEFAULT 0;
	DECLARE taskId    			INT DEFAULT 0;
	DECLARE assignAllowed		INT DEFAULT 0;
	DECLARE lastTaskHelperId 	INT DEFAULT 0;
    
    	-- task assignment 
	DECLARE v_event1 int;
	DECLARE i int;
	DECLARE j int;
	DECLARE maxId int;
	DECLARE no_more_rows1 boolean;
	DECLARE userid int;
    
	-- get the list of events tied to a task
	DECLARE cursor1 cursor for
			SELECT Event_Id 
			FROM event 
			WHERE Refer_Id = baseEventId and event.Start_DateTime >= UNIX_TIMESTAMP(NOW());
            
	DECLARE continue handler for not found set no_more_rows1 := TRUE;

	IF taskArray IS NULL THEN
		SET taskArray = '';
	END IF;
	
	SET lastTaskHelperId = initTaskHelperId;
	
	CREATE TEMPORARY TABLE IF NOT EXISTS 
	tempAssignmentPool(
		  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		  `User_Id` int(11) DEFAULT NULL
	);
	
    -- loop through the list of tasks
	loop_tasks:
		LOOP
			SET strLen = CHAR_LENGTH(taskArray);
			SET taskId = SUBSTRING_INDEX(taskArray, ',', 1);
			
			SELECT Assign_Allowed into assignAllowed from task where Task_Id = taskId and Is_Deleted = 0;
			
			-- Get the users from the assignment pool table associated with a task
            insert into tempAssignmentPool(User_Id) select a.User_Id from assignmentpool a 
			where a.Task_Id = taskId and a.Is_Deleted = 0 order by a.AssignmentPool_Id desc;
			
			set j = 1;
			select max(Id) into maxId from tempAssignmentPool;
			
            open cursor1;
            loop_event: loop
				fetch cursor1
				into  v_event1;
				if no_more_rows1 then
					close cursor1;
					leave loop_event;                   
				end if;
				-- process event
				set i = 1;
				while i <= assignAllowed Do
				    select User_Id into userid from tempAssignmentPool where Id = j;
					-- check if a record with the given Id already exists
					IF (select count(*) from taskhelper where TaskHelper_Id = lastTaskHelperId)  = 0 THEN
						insert into taskhelper(TaskHelper_Id,Task_Id,User_Id,Event_Id, Status, Creator_Id, Created_Time,Last_Modified, Last_Modified_Id) 
						values(lastTaskHelperId, taskId, userid, v_event1, 'P', ownerid, UTC_TIMESTAMP(), UTC_TIMESTAMP(), ownerid);
						set lastTaskHelperId =  lastTaskHelperId + 1;
                    END IF;
					set i = i + 1;
					set j = j + 1;
					-- circle the tempAssignmentPool to assign the users 
					if j > maxId then
					  set j = 1;
					end if;
				end while;
            end loop loop_event;
			-- go to next task
			SET subStrLen = CHAR_LENGTH(SUBSTRING_INDEX(taskArray, ',', 1)) + 2;
			SET taskArray = MID(taskArray, subStrLen, strLen);
            -- refresh the temp table
            truncate tempAssignmentPool;
			IF taskArray = '' THEN
				leave loop_tasks;
			END IF;
	END LOOP loop_tasks;
    
  END $$
  DELIMITER ;