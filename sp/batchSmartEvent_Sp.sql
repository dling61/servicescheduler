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

DROP PROCEDURE IF EXISTS `_GENERATE_SMART_EVENTS`;
DELIMITER $
CREATE PROCEDURE `_GENERATE_SMART_EVENTS`(base_event_id INT,
                            community_id INT,
                            owner_id INT,
                            init_event_id INT,
                            out last_event_id INT)
BEGIN
	DECLARE repeat_setting varchar(500);
	DECLARE startTime, endTime TIME;
	DECLARE fromDate, toDate date;
	DECLARE n, i int;
	DECLARE BY_Day varchar(500);
	DECLARE LOOP_int int;
	DECLARE LOOP_Date date;
	DECLARE temp_event_id int;
	DECLARE eventName, status, communityId, tz_id, location, host,
	 BEventId, creatorId, lastModifiedId varchar(500);

	SELECT 
		Repeat_Interval, From_Date, To_Date
	INTO repeat_setting , fromDate , toDate FROM
		event
	WHERE
		Event_Id = base_event_id;
	
	SELECT 
		DATE_FORMAT(FROM_UNIXTIME(Start_DateTime),'%H:%i:%s') TIMEONLY,  
		DATE_FORMAT(FROM_UNIXTIME(End_DateTime),'%H:%i:%s') TIMEONLY
	INTO startTime, endTime FROM 
	   event
	WHERE
		Event_Id = base_event_id LIMIT 1;
	
	call SPLIT_REPEAT_SCHEDULE(repeat_setting);


	if now() > fromDate then -- check start time
		SET fromDate = now();
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
	-- Select BEvent_Name, BEvent_Tz_Id, BEvent_Location, BEvent_Host, BEvent_Id, Creator_Id 
	-- into eventName, tz_id, location, host, BEventId, creatorId 
	-- from event
	-- where Event_id = base_event_id LIMIT 1;

	while LOOP_int < n DO
		SET LOOP_DATE = getNextDateOfWeekDay(fromDate, SPLIT_STRING(by_day,',',LOOP_int+1));
		While LOOP_DATE < toDate DO
			insert into tempEvent(
			Event_Id,Event_Name,Community_Id,
			Tz_Id,Location,`Host`, 
			Start_DateTime,End_DateTime,description, 
			Repeat_Interval, From_Date, To_Date, Refer_Id,
			is_deleted, Creator_Id,Created_Time,Last_Modified,Last_Modified_Id) 
			values (temp_event_id,'',community_id,
					'','','',
					UNIX_TIMESTAMP(addtime(LOOP_DATE, startTime)), UNIX_TIMESTAMP(addtime(LOOP_DATE , endTime)),'',
					'','','', base_event_id,
					0, owner_id, now(),now(),owner_id);
			SET LOOP_DATE = DATE_ADD(LOOP_DATE, interval i day);
			SET temp_event_id = temp_event_id + 1;
		END while;
		Set LOOP_int = LOOP_int + 1;
	END while;
	SET last_event_id = temp_event_id - 1;
END$ 
DELIMITER ;

DROP PROCEDURE IF EXISTS `GENERATE_SMART_EVENTS`;
DELIMITER $
CREATE PROCEDURE `GENERATE_SMART_EVENTS`(
                            base_event_id INT,
                            community_id INT,
                            owner_id INT,
                            init_event_id INT,
                            out last_event_id INT)
BEGIN
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
	  `Start_DateTime` int(10) NOT NULL,
	  `End_DateTime` int(10) NOT NULL,
	  `Description` varchar(200) NOT NULL,
	  `Repeat_Interval` varchar(500) NOT NULL,
	  `From_Date` date NOT NULL,
	  `To_Date` date NOT NULL,
	  `Refer_Id` int(11) NOT NULL,
	  `Is_Deleted` tinyint(1) NOT NULL,
	  `Creator_Id` int(11) NOT NULL,
	  `Created_Time` datetime NOT NULL,
	  `Last_Modified` datetime NOT NULL,
	  `Last_Modified_Id` int(11) DEFAULT NULL,
	  PRIMARY KEY (`Event_Id`),
	  KEY `Community_Id` (`Community_Id`)
	);
	call _GENERATE_SMART_EVENTS(base_event_id, community_id, owner_id,init_event_id,last_event_id);
	
	insert into event select * from tempEvent;
	commit;
	drop table if exists tempEvent;
	drop table if exists temp_repeat_setting;
END$ 
DELIMITER ;

DROP PROCEDURE IF EXISTS `UPDATE_EVENTS`;
DELIMITER $
CREATE PROCEDURE `UPDATE_EVENTS`(bevent_id INT,
							rschedule_id INT,
                            community_id INT,
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

	call _GENERATE_EVENTS(bevent_id, rschedule_id,
								community_id, owner_id,
								init_event_id,
								last_event_id);
								
	SET @row_number:=0;
	insert into tempOldEvent select @row_number := @row_number + 1, e.* from event e where e.Repeat_Schedule_id = repeatschedule_ID and Is_Deleted = 0 and Start_DateTime > now() order by Start_DateTime;

	SET @row_number:=0;
	insert into tempEventEdit select @row_number := @row_number + 1, e.* from tempEvent e where e.Repeat_Schedule_id = repeatschedule_ID and Is_Deleted = 0 order by Start_DateTime;

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
END$ 
DELIMITER ;


