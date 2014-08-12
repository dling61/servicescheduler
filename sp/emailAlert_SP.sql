  DELIMITER $$
  DROP PROCEDURE IF EXISTS emailAlert;
  CREATE PROCEDURE `emailAlert`()
  BEGIN
  

 Drop table if exists tmp_alert;
    Create table tmp_alert (
  `Schedule_Id` int(11) NOT NULL,
  `Service_Id` int(11) NOT NULL,
  `Service_Name` varchar(100) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `SDescription` varchar(200) NOT NULL,
  `Tz_Name` varchar(100) NOT NULL,
  `Abbr`    varchar(100) NOT NULL,
  `Start_Datetime` INT(10) NOT NULL,
  `Cur_Datetime` INT(10) NOT NULL,
  `Alert_Setting` int(11) NOT NULL,
  `Alert` int(11) NOT NULL
  ) Engine=MyISAM;

 Insert into tmp_alert 
   (
		Schedule_Id,
		Service_Id,
		Service_Name,
		Description,
		SDescription,
		Tz_Name,
		Abbr,
		Start_Datetime,
		Cur_Datetime,
		Alert_Setting,
		Alert
	)
  SELECT distinct 
        sc.Schedule_Id,
		s.Service_Id,
		s.Service_Name,
		s.Description,
		sc.Description,
		tz.Tz_Name, 
		tz.abbr,
		sc.Start_Datetime,
		UNIX_TIMESTAMP(UTC_TIMESTAMP()),
		sc.Alert,
		0
  from service s, schedule sc, timezonedb tz
  where s.Service_Id = sc.Service_Id
   and tz.Id = sc.Tz_Id
   and (sc.Start_Datetime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) <= 172800 
   and (sc.Start_Datetime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > 0
   and (sc.Is_Deleted = 0)
   and (s.Is_Deleted = 0)
   and (sc.Alert != 0)
   and (sc.Alert != 1);
   
  BEGIN 
      DECLARE done INT DEFAULT FALSE;
      DECLARE sid,sdtime,sr INT(11);
      DECLARE cur1 CURSOR FOR SELECT Schedule_Id, Start_Datetime, Alert_Setting FROM tmp_alert;
      DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
      
      SET SQL_SAFE_UPDATES=0;
      OPEN cur1;
      read_loop: LOOP
        FETCH cur1 INTO sid, sdtime, sr;
        IF done THEN
          LEAVE read_loop;
        END IF;
        IF (sr = 2) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (900 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (900 - 300)) THEN                  
			update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
        ELSEIF (sr = 3) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (1800 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (1800 - 300)) THEN
			update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
        ELSEIF (sr = 4) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (3600 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (3600 - 300)) THEN
			update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
        ELSEIF (sr = 5) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (7200 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (7200 - 300)) THEN
			update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
        ELSEIF (sr = 6) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (86400 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (86400 - 300)) THEN             
			update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
        ELSEIF (sr = 7) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (172800 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (172800 - 300)) THEN           
			update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
        END IF;
      END LOOP;
      CLOSE cur1;
      SET SQL_SAFE_UPDATES=1;
  END;
  
    BEGIN 
      DECLARE done2 INT DEFAULT FALSE;
      DECLARE userid INT(11);
	  DECLARE sname VARCHAR(100);
	  DECLARE stime DATETIME;
	  DECLARE temp_token VARCHAR(100);
	  DECLARE temp_deviceid INT(2);
	  -- Remove the standard time off set 
      DECLARE cur2 CURSOR FOR SELECT distinct us.User_Id,tmp.Service_Name,FROM_UNIXTIME(tmp.Start_Datetime)
		FROM tmp_alert tmp, onduty dt, member mb, user us
		WHERE tmp.Alert = 1
            and dt.Schedule_Id = tmp.Schedule_Id
            and dt.Member_Id = mb.Member_Id
			and us.User_Id = (select Creator_Id from service where Service_Id = tmp.Service_Id);
			
      DECLARE CONTINUE HANDLER FOR NOT FOUND SET done2 = TRUE;
      
      SET SQL_SAFE_UPDATES=0;
      OPEN cur2;
      insert_loop: LOOP
        FETCH cur2 INTO userid, sname, stime;
        IF done2 THEN
          LEAVE insert_loop;
        END IF;
        		SELECT Token, Device_Id into temp_token, temp_deviceid FROM userlog WHERE User_Id = userid  ORDER BY Last_Modified DESC 
LIMIT 1;	
		if (temp_token is not NULL) then
			insert into pushqueue (Target_Token, Device_Id, Message, Created_Time)
			values (temp_token, temp_deviceid, CONCAT(sname,' will occur at ',stime), NOW());
		end if;
      END LOOP;
      CLOSE cur2;
      SET SQL_SAFE_UPDATES=1;
  END;
	-- Remove the standard time offset and directly return UTC for start time
    SELECT distinct tmp.Service_Name servicename,tmp.Description descp,tmp.SDescription sdescp,FROM_UNIXTIME(tmp.Start_Datetime) starttime,tmp.Cur_Datetime curtime,
         mb.Member_Email memail, mb.Member_Name mname, us.User_Name uname, us.Email uemail, us.Mobile umobile, tmp.Alert_setting alertsetting
      FROM tmp_alert tmp, onduty dt, member mb, user us
      WHERE tmp.Alert = 1
            and dt.Schedule_Id = tmp.Schedule_Id
            and dt.Member_Id = mb.Member_Id
			and us.User_Id = (select Creator_Id from service where Service_Id = tmp.Service_Id)
			order by tmp.Service_Id;

  DROP TABLE  tmp_alert;
  END $$
  DELIMITER ;
