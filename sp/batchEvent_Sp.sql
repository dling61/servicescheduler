DROP FUNCTION IF EXISTS SPLIT_STRING;
DELIMITER $
CREATE FUNCTION `SPLIT_STRING`( s VARCHAR(1024) , del CHAR(1) , i INT) RETURNS varchar(1024) CHARSET utf8
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


DROP PROCEDURE IF EXISTS `SPLIT_REPEAT_SCHEDULE`;
DELIMITER $
CREATE PROCEDURE `SPLIT_REPEAT_SCHEDULE`( s VARCHAR(1024))
BEGIN
DECLARE n,j INT ;
DECLARE sub_string, rKey, rValue varchar(1024);
DECLARE sub_freq, sub_inter, sub_by_day varchar(500);
	CREATE TEMPORARY TABLE temp_repeate_setting(freq varchar(500), inter varchar(500), by_day varchar(500));
    SET n = LENGTH(s) - LENGTH(REPLACE(s, ';', '')) + 1;
    SET j = 0;
    WHILE n > j DO
		SET j = j + 1;
        SET sub_string = SPLIT_STRING(s,';',j);
		SET rKey = SPLIT_STRING(sub_string,'=',1);
        SET rValue = SPLIT_STRING(sub_string,'=',2);
        if rKey = 'Freq' then
			SET sub_freq = rValue;
		elseif rKey = 'interval' then
			SET sub_inter = rValue;
		elseif rKey = 'BYDAY' then
			SET sub_by_day = rValue;
		end if;
    END WHILE;
    insert into temp_repeate_setting values(sub_freq, sub_inter, sub_by_day);
END$
DELIMITER ;

DROP function IF EXISTS getNextDateOfWeekDay;
DELIMITER $

CREATE DEFINER=`Test`@`localhost` FUNCTION `getNextDateOfWeekDay`(startDate Date, weekDay varchar(10)) RETURNS date
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

DROP PROCEDURE IF EXISTS `GENERATE_EVENTS`;
DELIMITER $

CREATE PROCEDURE `GENERATE_EVENTS`(BASE_EVENT_ID INT,
							REPEATSCHEDULE_ID INT,
                            service_id INT,
                            owner_id INT,
                            init_event_id INT,
                            out last_event_id INT)
BEGIN
Declare repeat_setting varchar(500);
Declare startTime, endTime time;
Declare fromDate, toDate date;
DECLARE n, i int;
DECLARE BY_Day varchar(500);
DECLARE LOOP_int int;
DECLARE LOOP_Date date;
DECLARE temp_event_id int;
DECLARE eventName, status, ServiceId, tz_id, location, host,
 REventId, creatorId, lastModifiedId varchar(500);

SELECT 
    Repeat_Interval, From_Date, To_Date
INTO repeat_setting , fromDate , toDate FROM
    repeatschedule
WHERE
    RSchedule_Id = repeatschedule_id;
SELECT 
    REvent_StartTime, REvent_EndTime
INTO startTime , endTime FROM
    baseevent
WHERE
    REvent_Id = base_event_id;

drop table if exists temp_repeate_setting;
call SPLIT_REPEAT_SCHEDULE(repeat_setting);


if now() > fromDate then# check start time
	SET fromDate = now();
end if;

SELECT 
    t.BY_DAY, t.inter
INTO by_day , i FROM
    temp_REPEATE_SETTING t;#should only have one row, more setting to be configured

SET n = LENGTH(by_day) - LENGTH(REPLACE(by_day, ',', '')) + 1;
SET LOOP_int = 0;
SET i = ifnull(i,1);
SET i = i * 7;

SET temp_event_id = init_event_id;

#data transfer
Select REvent_Name, REvent_Tz_Id, REvent_Location, REvent_Host, REvent_Id, Creator_Id 
into   eventName,   tz_id       , location,        host ,      REventId,  creatorId 
from baseevent
where Revent_id = BASE_EVENT_ID;



while LOOP_int < n DO
	SET LOOP_DATE = getNextDateOfWeekDay(fromDate, SPLIT_STRING(by_day,',',LOOP_int+1));
	While LOOP_DATE < toDate DO
		insert into event(
        Event_Id,Event_Name,Service_Id,
        Tz_Id,Location,`Host`,
        REvent_Id,Repeat_schedule_ID, 
        Start_DateTime,End_DateTime,description, 
        Creator_Id, is_deleted,Created_Time,Last_Modified,Last_Modified_Id) 
        values (temp_event_id,eventName,service_id,
				tz_id,location,host,
                REventId,REPEATSCHEDULE_ID,
                addtime(LOOP_DATE, startTime), addtime(LOOP_DATE , endTime),'',
                creatorId,0,now(),now(),owner_id);
        SET LOOP_DATE = DATE_ADD(LOOP_DATE, interval i day);
        SET temp_event_id = temp_event_id + 1;
    END while;
    Set LOOP_int = LOOP_int + 1;
END while;
SET last_event_id = temp_event_id - 1;
END$ DELIMITER ;
