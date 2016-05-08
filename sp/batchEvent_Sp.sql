DROP FUNCTION IF EXISTS SPLIT_STRING;
DELIMITER $
CREATE FUNCTION `SPLIT_STRING`( s VARCHAR(1024) , del CHAR(1) , i INT) 
RETURNS varchar(1024) CHARSET utf8
    DETERMINISTIC
BEGIN
	DECLARE n INT ;
	-- get max number of items
			SET n = LENGTH(s) - LENGTH(REPLACE(s, del, '')) + 1;
	IF i > n THEN
				RETURN NULL ;
			ELSE
				RETURN TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(s, del, i) , del , -1 )) ;        
			END IF;
END
$
DELIMITER ;

-- this is to split repeat schedule string E.g. BYDAY=Mon, FRi
DROP PROCEDURE IF EXISTS `SPLIT_REPEAT_SCHEDULE`;
DELIMITER $
CREATE PROCEDURE `SPLIT_REPEAT_SCHEDULE`( s VARCHAR(1024))
BEGIN
	DECLARE n,j INT ;
	DECLARE sub_string, rKey, rValue varchar(1024);
	DECLARE sub_freq, sub_inter, sub_by_day varchar(500);
		CREATE TEMPORARY TABLE IF NOT EXISTS temp_repeat_setting(freq varchar(500), inter varchar(500), by_day varchar(500));
		SET n = LENGTH(s) - LENGTH(REPLACE(s, ';', '')) + 1;
		SET j = 0;
		WHILE n > j DO
			SET j = j + 1;
			SET sub_string = SPLIT_STRING(s,';',j);
			SET rKey = SPLIT_STRING(sub_string,'=',1);
			SET rValue = SPLIT_STRING(sub_string,'=',2);
			if UPPER(rKey) = 'FREQ' then
				SET sub_freq = rValue;
			elseif UPPER(rKey) = 'interval' then
				SET sub_inter = rValue;
			elseif UPPER(rKey) = 'BYDAY' then
				SET sub_by_day = rValue;
			end if;
		END WHILE;
		insert into temp_repeat_setting values(sub_freq, sub_inter, sub_by_day);
END$
DELIMITER ;

-- utility function
DROP function IF EXISTS getNextDateOfWeekDay;
DELIMITER $
CREATE FUNCTION `getNextDateOfWeekDay`(startDate Date, weekDay varchar(10)) RETURNS date
    DETERMINISTIC
BEGIN
	DECLARE weekInt int;
	DECLARE startDayOfWeek int;
	DECLARE delta int;

	case LOWER(weekDay)
	 When 'sun' Then
	  SET weekInt = 1;
	When 'mon' Then
	  SET weekInt = 2;
	When 'tue' Then
	 SET weekInt = 3;
	When 'wen' Then
	 SET weekInt = 4;
	When 'thu' Then
	 Set weekInt = 5;
	When 'fri' Then
	 Set weekInt = 6;
	when 'sat' Then
	 Set weekInt = 7;
	end case;

	SET startDayOfWeek = DAYOFWEEK(startDate);
	SET delta = (7 + weekInt - startDayOfWeek) % 7;

	return Date_add(startDate, interval delta day);

END$
DELIMITER ;

DROP PROCEDURE IF EXISTS `_GENERATE_EVENTS`;
DELIMITER $
CREATE PROCEDURE `_GENERATE_EVENTS`(bevent_id INT,
							#rschedule_id INT,
                            #community_id INT,
                            owner_id INT,
                            init_event_id INT,
                            out last_event_id INT)
BEGIN
	DECLARE repeat_setting varchar(500);
	DECLARE start_time, end_time TIME;
	DECLARE from_date, to_date date;
	DECLARE n, i int;
	DECLARE BY_Day varchar(500);
	DECLARE LOOP_int int;
	DECLARE LOOP_Date date;
	DECLARE temp_event_id, community_id int;
	DECLARE event_name, status, tz_id, location, host,
	 BEventId, creator_id, lastModifiedId varchar(500);


	SELECT 
		be.Repeat_Interval, be.From_Date, be.To_Date
	INTO repeat_setting , from_date , to_date FROM
		baseevent be
	WHERE
		BEvent_Id = bevent_id LIMIT 1;


	SELECT 
		BEvent_StartTime, BEvent_EndTime
	INTO start_time , end_time FROM
		baseevent
	WHERE
		BEvent_Id = bevent_id LIMIT 1;
	
	call SPLIT_REPEAT_SCHEDULE(repeat_setting);


	if now() > from_date then -- check start time
		SET from_date = now();
	end if;

	SELECT 
		rs.BY_DAY, rs.inter
	INTO by_day , i FROM
		temp_repeat_setting rs LIMIT 1; -- should only have one row, more setting to be configured

	SET n = LENGTH(by_day) - LENGTH(REPLACE(by_day, ',', '')) + 1;
	SET LOOP_int = 0;
	SET i = ifnull(i,1);
	SET i = i * 7;

	SET temp_event_id = init_event_id;

	-- data transfer
	Select be.BEvent_Name, be.BEvent_Tz_Id, be.BEvent_Location, be.BEvent_Host, be.BEvent_Id, be.Creator_Id, be.Community_ID
	into event_name, tz_id, location, host, BEventId, creator_id, community_id
	from baseevent be
	where BEvent_id = bevent_id LIMIT 1;

	while LOOP_int < n DO
		SET LOOP_DATE = getNextDateOfWeekDay(from_date, SPLIT_STRING(by_day,',',LOOP_int+1));
		While LOOP_DATE < to_date DO
			insert into tempEvent(
			Event_Id,Event_Name,Community_Id,
			Tz_Id,Location,`Host`,
			BEvent_Id,RSchedule_Id, 
			Start_DateTime,End_DateTime,description, 
			is_deleted, Creator_Id,Created_Time,Last_Modified,Last_Modified_Id) 
			values (temp_event_id,event_name,community_id,
					tz_id,location,host,
					BEventId,0,
					UNIX_TIMESTAMP(addtime(LOOP_DATE, start_time)), UNIX_TIMESTAMP(addtime(LOOP_DATE , end_time)),'',
					0, owner_id, now(),now(),owner_id);
			SET LOOP_DATE = DATE_ADD(LOOP_DATE, interval i day);
			SET temp_event_id = temp_event_id + 1;
		END while;
		Set LOOP_int = LOOP_int + 1;
	END while;
	SET last_event_id = temp_event_id - 1;
END$ DELIMITER ;

DROP PROCEDURE IF EXISTS `GENERATE_EVENTS`;
DELIMITER $

CREATE PROCEDURE `GENERATE_EVENTS`(bevent_id INT,
							#rschedule_id INT,
                            #community_id INT,
                            owner_id INT,
                            init_event_id INT,
                            out last_event_id INT)
BEGIN
	drop table if exists tempEvent;-- in case there's error happended last thme we called this function
	drop table if exists temp_repeat_setting;
    CREATE TEMPORARY TABLE IF NOT EXISTS 
	tempEvent(
	  `Event_Id` int(11) NOT NULL DEFAULT '0',
	  `Event_Name` varchar(100) DEFAULT NULL,
	  `Status` varchar(10) DEFAULT NULL,
	  `Community_Id` int(11) NOT NULL,
	  `Tz_Id` int(11) DEFAULT NULL,
	  `Alert` int(11) DEFAULT NULL,
	  `Location` varchar(200) DEFAULT NULL,
	  `Host` varchar(200) DEFAULT NULL,
	  `BEvent_Id` int(11) DEFAULT NULL,
	  `RSchedule_Id` int(11) NOT NULL,
	  `Start_DateTime` int(10) NOT NULL,
	  `End_DateTime` int(10) NOT NULL,
	  `Description` varchar(200) NOT NULL,
	  `Is_Deleted` tinyint(1) NOT NULL,
	  `Creator_Id` int(11) NOT NULL,
	  `Created_Time` datetime NOT NULL,
	  `Last_Modified` datetime NOT NULL,
	  `Last_Modified_Id` int(11) DEFAULT NULL,
	  PRIMARY KEY (`Event_Id`),
	  KEY `Community_Id` (`Community_Id`)
	);
	call _GENERATE_EVENTS(bevent_id, #rschedule_id,
								#community_id,
                                owner_id,
								init_event_id,
								last_event_id);
	insert into event select * from tempEvent;
	commit;
	drop table if exists tempEvent;
	drop table if exists temp_repeat_setting;
END$ DELIMITER ;

DROP PROCEDURE IF EXISTS `UPDATE_EVENTS`;
DELIMITER $

CREATE DEFINER=`root`@`localhost` PROCEDURE `UPDATE_EVENTS`(bevent_id INT,
							#rschedule_id INT,
                            #community_id INT,
                            owner_id INT,
                            init_event_id INT,
                            out last_event_id INT)
BEGIN
	DECLARE oldCount, newCount int;
	drop table if exists tempEvent;
	drop table if exists tempOldEvent;
	drop table if exists tempEventEdit;
	create TEMPORARY table tempEvent(
	  `Event_Id` int(11) NOT NULL DEFAULT '0',
	  `Event_Name` varchar(100) DEFAULT NULL,
	  `Status` varchar(10) DEFAULT NULL,
	  `Community_Id` int(11) NOT NULL,
	  `Tz_Id` int(11) DEFAULT NULL,
	  `Alert` int(11) DEFAULT NULL,
	  `Location` varchar(200) DEFAULT NULL,
	  `Host` varchar(200) DEFAULT NULL,
	  `BEvent_Id` int(11) DEFAULT NULL,
	  `RSchedule_Id` int(11) NOT NULL,
	  `Start_DateTime` int(10) NOT NULL,
	  `End_DateTime` int(10) NOT NULL,
	  `Description` varchar(200) NOT NULL,
	  `Is_Deleted` tinyint(1) NOT NULL,
	  `Creator_Id` int(11) NOT NULL,
	  `Created_Time` datetime NOT NULL,
	  `Last_Modified` datetime NOT NULL,
	  `Last_Modified_Id` int(11) DEFAULT NULL,
	  PRIMARY KEY (`Event_Id`),
	  KEY `Community_Id` (`Community_Id`)
	);

	create TEMPORARY table tempEventEdit(
	  `temp_Id`  int(11),
	  `Event_Id` int(11) NOT NULL DEFAULT '0',
	  `Event_Name` varchar(100) DEFAULT NULL,
	  `Status` varchar(10) DEFAULT NULL,
	  `Community_Id` int(11) NOT NULL,
	  `Tz_Id` int(11) DEFAULT NULL,
	  `Alert` int(11) DEFAULT NULL,
	  `Location` varchar(200) DEFAULT NULL,
	  `Host` varchar(200) DEFAULT NULL,
	  `BEvent_Id` int(11) DEFAULT NULL,
	  `RSchedule_Id` int(11) NOT NULL,
	  `Start_DateTime` int(10) NOT NULL,
	  `End_DateTime` int(10) NOT NULL,
	  `Description` varchar(200) NOT NULL,
	  `Creator_Id` int(11) NOT NULL,
	  `Is_Deleted` tinyint(1) NOT NULL,
	  `Created_Time` datetime NOT NULL,
	  `Last_Modified` datetime NOT NULL,
	  `Last_Modified_Id` int(11) DEFAULT NULL,
	  KEY `Community_Id` (`Community_Id`)
	);

	create TEMPORARY table tempOldEvent(
	  `temp_Id`  int(11),
	  `Event_Id` int(11) NOT NULL DEFAULT '0',
	  `Event_Name` varchar(100) DEFAULT NULL,
	  `Status` varchar(10) DEFAULT NULL,
	  `Community_Id` int(11) NOT NULL,
	  `Tz_Id` int(11) DEFAULT NULL,
	  `Alert` int(11) DEFAULT NULL,
	  `Location` varchar(200) DEFAULT NULL,
	  `Host` varchar(200) DEFAULT NULL,
	  `BEvent_Id` int(11) DEFAULT NULL,
	  `RSchedule_Id` int(11) NOT NULL,
	  `Start_DateTime` int(10) NOT NULL,
	  `End_DateTime` int(10) NOT NULL,
	  `Description` varchar(200) NOT NULL,
	  `Creator_Id` int(11) NOT NULL,
	  `Is_Deleted` tinyint(1) NOT NULL,
	  `Created_Time` datetime NOT NULL,
	  `Last_Modified` datetime NOT NULL,
	  `Last_Modified_Id` int(11) DEFAULT NULL,
	  KEY `Community_Id` (`Community_Id`)
	);

	call _GENERATE_EVENTS(bevent_id, #rschedule_id,
								0, owner_id,
								init_event_id,
								last_event_id);
								
	SET @row_number:=0;
	insert into tempOldEvent select @row_number := @row_number + 1, e.* from event e where e.bevent_id = bevent_id and Is_Deleted = 0 and Start_DateTime > now() order by Start_DateTime;

	SET @row_number:=0;
	insert into tempEventEdit select @row_number := @row_number + 1, e.* from tempEvent e where e.bevent_id = bevent_id and Is_Deleted = 0 order by Start_DateTime;

	update tempEventEdit SET Event_id = -1;
	update tempOldEvent set Is_Deleted = 1;

	update tempEventEdit e, tempOldEvent oe
	Set 
	oe.`temp_Id` = -1,
	e.`Event_Id` = oe.`Event_Id`,
	e.`Event_Name` = oe.`Event_Name`,
	e.`Status` = oe.`Status`,
	e.`Community_Id` = oe.`Community_Id`,
	e.`Tz_Id` = oe.`Tz_Id`,
	e.`Alert` = oe.`Alert`,
	e.`Location` = oe.`Location`,
	e.`Host` = oe.`Host`,
	e.`BEvent_Id` = oe.`BEvent_Id`,
	e.`RSchedule_Id` = oe.`RSchedule_Id`,
	e.`Start_DateTime` = oe.`Start_DateTime`,
	e.`End_DateTime` = oe.`End_DateTime`,
	e.`Description` = oe.`Description`,
	e.`Creator_Id` = oe.`Creator_Id`,
	e.`Created_Time` = oe.`Created_Time`,
	e.`Last_Modified` = oe.`Last_Modified`,
	e.`Last_Modified_Id` = oe.`Last_Modified_Id` 
	where e.Start_DateTime = oe.Start_DateTime and e.End_DateTime = oe.End_DateTime;

	select count(*) into oldCount from tempOldEvent;
	select count(*) into newCount from tempEvent;

	if oldCount = newCount then
	SET @row_number:=0;
	SET @minEventID:=-1;
	-- update event e, tempEventEdit te set e.Start_DateTime = te.Start_DateTime, e.End_DateTime = te.End_DateTime where e.Event_id = te.Event_id;
	else
	SET @row_id = init_event_id -1 ;
	update tempEventEdit set Event_Id = @row_id  where Event_Id = -1 and @row_id := @row_id + 1; #generate new id for new events
	insert into tempEventEdit select * from tempOldEvent where temp_id <> -1;
	-- replace event select * from tempOldEvent;
	end if;
	-- drop table if exists tempEvent;
	-- drop table if exists tempOldEvent;
END$ DELIMITER ;

