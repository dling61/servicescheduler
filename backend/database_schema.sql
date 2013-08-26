-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: 50.63.108.35
-- Generation Time: Aug 26, 2013 at 01:26 PM
-- Server version: 5.0.96
-- PHP Version: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: ``
--

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
CREATE TABLE IF NOT EXISTS `member` (
  `Member_Id` int(11) NOT NULL,
  `Member_Email` varchar(100) NOT NULL,
  `Member_Name` varchar(100) NOT NULL,
  `Mobile_Number` int(11) NOT NULL,
  `Is_Registered` tinyint(1) NOT NULL default '0',
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL default '0',
  `Last_Modified` datetime NOT NULL,
  PRIMARY KEY  (`Member_Id`),
  KEY `Member_Email` (`Member_Email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `onduty`
--

DROP TABLE IF EXISTS `onduty`;
CREATE TABLE IF NOT EXISTS `onduty` (
  `Service_Id` int(11) NOT NULL,
  `Schedule_Id` int(11) NOT NULL,
  `Member_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL default '0',
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  KEY `Member_Id` (`Member_Id`),
  KEY `Schedule_Id` (`Schedule_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pushqueue`
--

DROP TABLE IF EXISTS `pushqueue`;
CREATE TABLE IF NOT EXISTS `pushqueue` (
  `Pushqueue_Id` int(11) NOT NULL auto_increment,
  `Target_Token` varchar(100) NOT NULL,
  `Message` varchar(200) NOT NULL,
  `Sent_Time` datetime NOT NULL,
  `Created_Time` datetime NOT NULL,
  PRIMARY KEY  (`Pushqueue_Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `resetpassword`
--

DROP TABLE IF EXISTS `resetpassword`;
CREATE TABLE IF NOT EXISTS `resetpassword` (
  `Request_Id` int(11) NOT NULL auto_increment,
  `Email` varchar(100) NOT NULL,
  `Token` int(11) NOT NULL,
  `Is_Done` tinyint(1) NOT NULL,
  `Expired_Time` int(10) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  PRIMARY KEY  (`Request_Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
CREATE TABLE IF NOT EXISTS `schedule` (
  `Schedule_Id` int(11) NOT NULL,
  `Service_Id` int(11) NOT NULL,
  `Start_DateTime` int(10) unsigned NOT NULL,
  `End_DateTime` int(10) unsigned NOT NULL,
  `Description` varchar(200) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  KEY `Service_Id` (`Service_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `serverlog`
--

DROP TABLE IF EXISTS `serverlog`;
CREATE TABLE IF NOT EXISTS `serverlog` (
  `Log_Id` int(11) NOT NULL auto_increment,
  `URL_Resource` varchar(200) NOT NULL,
  `Action` varchar(10) NOT NULL,
  `Body` varchar(2048) NOT NULL,
  `Response` varchar(4096) NOT NULL,
  `Created_DateTime` datetime NOT NULL,
  `Client_Device_Id` int(11) NOT NULL,
  PRIMARY KEY  (`Log_Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12301 ;

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

DROP TABLE IF EXISTS `service`;
CREATE TABLE IF NOT EXISTS `service` (
  `Service_Id` int(11) NOT NULL,
  `Service_Name` varchar(100) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `SRepeat` int(11) NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL,
  `From_Date` date NOT NULL,
  `To_Date` date NOT NULL,
  `Start_Datetime` int(10) NOT NULL,
  `End_Datetime` int(10) NOT NULL,
  `UTC_Off` int(10) NOT NULL,
  `Alert` int(11) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tmp_alert`
--

DROP TABLE IF EXISTS `tmp_alert`;
CREATE TABLE IF NOT EXISTS `tmp_alert` (
  `Schedule_Id` int(11) NOT NULL,
  `Service_Id` int(11) NOT NULL,
  `Service_Name` varchar(100) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `SDescription` varchar(200) NOT NULL,
  `SUTC_Off` int(10) NOT NULL,
  `Start_Datetime` int(10) NOT NULL,
  `Cur_Datetime` int(10) NOT NULL,
  `Alert_Setting` int(11) NOT NULL,
  `Alert` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `User_id` int(11) NOT NULL auto_increment,
  `Email` varchar(100) NOT NULL,
  `User_Name` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `Mobile` int(11) NOT NULL,
  `User_Type` varchar(10) NOT NULL,
  `Verified` tinyint(1) NOT NULL default '0',
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  PRIMARY KEY  (`User_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=82 ;

-- --------------------------------------------------------

--
-- Table structure for table `userlog`
--

DROP TABLE IF EXISTS `userlog`;
CREATE TABLE IF NOT EXISTS `userlog` (
  `User_Id` int(11) NOT NULL,
  `Udid` varchar(100) NOT NULL,
  `Token` varchar(100) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Expired_Time` datetime NOT NULL,
  `Logout_Time` datetime NOT NULL,
  UNIQUE KEY `main_index` (`User_Id`,`Udid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `emailAlert`$$
CREATE DEFINER=`testscheduler1`@`%` PROCEDURE `emailAlert`()
BEGIN
  

  Drop table if exists tmp_alert;
    Create table tmp_alert (
  `Schedule_Id` int(11) NOT NULL,
  `Service_Id` int(11) NOT NULL,
  `Service_Name` varchar(100) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `SDescription` varchar(200) NOT NULL,
  `SUTC_Off` int(10) NOT NULL,
  `Start_Datetime` INT(10) NOT NULL,
  `Cur_Datetime` INT(10) NOT NULL,
  `Alert_Setting` int(11) NOT NULL,
  `Alert` int(11) NOT NULL
  ) Engine=MyISAM;
  

 Insert into tmp_alert (Schedule_Id,Service_Id,Service_Name,Description,SDescription,SUTC_Off,Start_Datetime,Cur_Datetime,Alert_Setting,Alert)
  SELECT distinct sc.Schedule_Id,s.Service_Id,s.Service_Name,s.Description,sc.Description,s.UTC_Off,sc.Start_Datetime,UNIX_TIMESTAMP(UTC_TIMESTAMP()),s.Alert,0
  from service s, schedule sc 
  where s.Service_Id = sc.Service_Id
   and (sc.Start_Datetime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) <= 172800 
   and (sc.Start_Datetime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > 0
   and (sc.Is_Deleted = 0)
   and (s.Is_Deleted = 0)
   and (s.Alert != 0)
   and (s.Alert != 1);
   
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
                IF (sr = 2) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (900 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (900 - 300)) THEN                  update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
        ELSEIF (sr = 3) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (1800 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (1800 - 300)) THEN
          update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
        ELSEIF (sr = 4) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (3600 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (3600 - 300)) THEN
          update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
        ELSEIF (sr = 5) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (7200 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (7200 - 300)) THEN
          update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
        ELSEIF (sr = 6) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (86400 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (86400 - 300)) THEN             update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
        ELSEIF (sr = 7) AND ((sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) < (172800 + 300) and (sdtime - UNIX_TIMESTAMP(UTC_TIMESTAMP())) > (172800 - 300)) THEN           update tmp_alert lu set lu.Alert = 1  where lu.Schedule_id = sid;
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
	  DECLARE temp VARCHAR(100);
	  
      DECLARE cur2 CURSOR FOR SELECT distinct us.User_Id,tmp.Service_Name,FROM_UNIXTIME(tmp.Start_Datetime + tmp.SUTC_Off)
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
        				SELECT Token into temp FROM userlog WHERE User_Id = userid;
		
		if (temp is not NULL) then
			insert into pushqueue (Target_Token, Message, Created_Time)
			values (temp, sname, NOW());
		end if;
      END LOOP;
      CLOSE cur2;
      SET SQL_SAFE_UPDATES=1;
  END;
	
    SELECT distinct tmp.Service_Name servicename,tmp.Description descp,tmp.SDescription sdescp,FROM_UNIXTIME(tmp.Start_Datetime + tmp.SUTC_Off) starttime,tmp.Cur_Datetime curtime,
         mb.Member_Email memail, mb.Member_Name mname, us.User_Name uname, us.Email uemail, us.Mobile umobile, tmp.Alert_setting alertsetting
      FROM tmp_alert tmp, onduty dt, member mb, user us
      WHERE tmp.Alert = 1
            and dt.Schedule_Id = tmp.Schedule_Id
            and dt.Member_Id = mb.Member_Id
			and us.User_Id = (select Creator_Id from service where Service_Id = tmp.Service_Id)
			order by tmp.Service_Id;

    END$$

DROP PROCEDURE IF EXISTS `getMemberByLastUpdate`$$
CREATE DEFINER=`testscheduler1`@`%` PROCEDURE `getMemberByLastUpdate`(IN ownerid INT, IN p_lastupdate Datetime)
BEGIN
  
  
  Drop table if exists tmp_member;
    Create table tmp_member (
  `Member_Id` int(11) NOT NULL,
  `Member_Email` varchar(100) NOT NULL,
  `Member_Name` varchar(100) NOT NULL,
  `Mobile_Number` int(11) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Valid` bit(1) not null,
  `InputId` bit(1) not null) Engine=MyISAM;
  
  
  Insert into tmp_member (Member_Id,Member_Email,Member_Name,Mobile_Number,Creator_Id,Is_Deleted,Created_Time,Last_Modified,Valid, InputId)
  SELECT distinct m.Member_Id, m.Member_Email, m.Member_Name, m.Mobile_Number, m.Creator_Id,m.Is_Deleted,m.Created_Time,m.Last_Modified,0,0 
  from onduty o, member m 
  where o.Member_Id = m.Member_Id 
  and m.Last_Modified > p_lastupdate
  and o.Service_Id in 
  (select distinct Service_Id from onduty where member_id in 
  (select member_id from member 
     where Member_Email = (select Email from user where User_Id = ownerid) 
  )
  );

 IF p_lastupdate !="0000-00-00 00:00:00" THEN
     (SELECT tmp.Member_Id memberid,tmp.Member_Email memberemail,tmp.Member_Name membername,
          tmp.Mobile_Number mobilenumber,tmp.Is_Deleted isdeleted,tmp.Creator_Id creatorid,
          tmp.Created_Time createdtime,tmp.Last_Modified lastmodified 
      FROM tmp_member tmp)
	UNION
	(SELECT Member_Id memberid, Member_Email memberemail, Member_Name membername, Mobile_Number mobilenumber,
      Is_Deleted isdeleted, Creator_Id creatorid, Created_Time createdtime, Last_Modified lastmodified
      From member where Creator_Id = ownerid and Last_Modified > p_lastupdate);
  ELSE 
      (SELECT tmp.Member_Id memberid,tmp.Member_Email memberemail,tmp.Member_Name membername,tmp.Mobile_Number mobilenumber,
      tmp.Is_Deleted isdeleted,tmp.Creator_Id creatorid,tmp.Created_Time createdtime,tmp.Last_Modified lastmodified 
      FROM tmp_member tmp)
    UNION
    (SELECT Member_Id memberid, Member_Email memberemail, Member_Name membername, Mobile_Number mobilenumber,
      Is_Deleted isdeleted, Creator_Id creatorid, Created_Time createdtime, Last_Modified lastmodified
      From member where Creator_Id = ownerid and Is_Deleted = 0);
  END IF;


  DROP TABLE  tmp_member;
  END$$

DROP PROCEDURE IF EXISTS `getScheduleByLastUpdate`$$
CREATE DEFINER=`testscheduler1`@`%` PROCEDURE `getScheduleByLastUpdate`(IN ownerid INT, IN p_lastupdate Datetime)
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
  
    IF p_lastupdate !="0000-00-00 00:00:00" THEN
     (SELECT tmp.Schedule_Id scheduleid,tmp.Service_Id serviceid, tmp.Creator_Id creatorid, tmp.Description description,FROM_UNIXTIME(tmp.Start_Datetime) starttime,
      FROM_UNIXTIME(tmp.End_Datetime) endtime,tmp.Is_Deleted isdeleted,tmp.OD_Is_Deleted odisdeleted,tmp.Created_Time createdtime,tmp.Last_Modified lastmodified 
      FROM tmp_schedule tmp)
	UNION
	(SELECT Schedule_Id scheduleid, Service_Id serviceid, Creator_Id creatorid,Description description, FROM_UNIXTIME(Start_Datetime),
          FROM_UNIXTIME(End_Datetime), Is_Deleted isdeleted, 0, Created_Time createdtime, Last_Modified lastmodified
      FROM schedule where Creator_Id = ownerId and Last_Modified > p_lastupdate);
  ELSE 
      (SELECT tmp.Schedule_Id scheduleid,tmp.Service_Id serviceid, tmp.Creator_Id creatorid, tmp.Description description,FROM_UNIXTIME(tmp.Start_Datetime) starttime,
      FROM_UNIXTIME(tmp.End_Datetime) endtime,tmp.Is_Deleted isdeleted,tmp.OD_Is_Deleted odisdeleted,tmp.Created_Time createdtime,tmp.Last_Modified lastmodified 
      FROM tmp_schedule tmp)
    UNION
    (SELECT Schedule_Id scheduleid, Service_Id serviceid, Creator_Id creatorid,Description description, FROM_UNIXTIME(Start_Datetime),
          FROM_UNIXTIME(End_Datetime), Is_Deleted isdeleted, 0, Created_Time createdtime, Last_Modified lastmodified
      FROM schedule where Creator_Id = ownerId and Is_Deleted = 0);
  END IF;

    IF p_lastupdate !="0000-00-00 00:00:00" THEN
      (SELECT tmp.Schedule_Id scheduleid, ot.Member_Id memberid 
      FROM tmp_schedule tmp,onduty ot
        WHERE  (tmp.Is_Deleted = 0 or tmp.OD_Is_Deleted = 0)
        and tmp.Schedule_Id = ot.Schedule_Id)
	UNION
	(SELECT s.Schedule_Id scheduleid, o.Member_Id memberid
      FROM schedule s, onduty o
      WHERE s.Creator_Id = ownerid and s.Schedule_Id = o.Schedule_Id and s.Last_Modified > p_lastupdate);   
  ELSE 
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
  END$$

DROP PROCEDURE IF EXISTS `getServiceByLastUpdate`$$
CREATE DEFINER=`testscheduler1`@`%` PROCEDURE `getServiceByLastUpdate`(IN ownerid INT, IN p_lastupdate Datetime)
BEGIN
  
  
 Drop table if exists tmp_service;
    Create table tmp_service (
  `Service_Id` int(11) NOT NULL,
  `Service_Name` varchar(100) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `SRepeat` int(11) NOT NULL,
  `Start_Datetime` int(10) NOT NULL,
  `End_Datetime` int(10) NOT NULL,
  `Alert` int(11) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Valid` bit(1) not null,
  `InputId` bit(1) not null) Engine=MyISAM;
  

  Insert into tmp_service (Service_Id,Service_Name,Description,SRepeat,Start_Datetime,End_Datetime,Alert,Creator_Id,Is_Deleted,Created_Time,Last_Modified,Valid, InputId)
  SELECT distinct s.Service_Id,s.Service_Name,s.Description,s.SRepeat,s.Start_Datetime,s.End_Datetime,s.Alert,s.Creator_Id,s.Is_Deleted,s.Created_Time,s.Last_Modified,0,0 
  from service s, onduty o, member m 
  where s.Service_Id = o.Service_Id 
       and o.Member_Id = m.Member_Id 
       and m.Member_Email = (select Email from user where User_Id = ownerid) 
       and ((o.Last_Modified > p_lastupdate) or (s.Last_Modified > p_lastupdate));

  
  IF p_lastupdate !="0000-00-00 00:00:00" THEN
      (SELECT tmp.Service_Id serviceid,tmp.Service_Name servicename,tmp.Description descp,
      tmp.SRepeat srepeat,FROM_UNIXTIME(tmp.Start_Datetime) startdatetime,FROM_UNIXTIME(tmp.End_Datetime) enddatetime,tmp.Alert alert,tmp.Creator_Id creatorid,tmp.Is_Deleted isdeleted,
      tmp.Created_Time createdtime,tmp.Last_Modified lastmodified 
      FROM tmp_service tmp)
	 UNION
	 (SELECT Service_Id serviceid, Service_Name servicename, Description descp, SRepeat srepeat,
      FROM_UNIXTIME(Start_Datetime) startdatetime, FROM_UNIXTIME(End_Datetime) enddatetime, Alert alert,
      Creator_Id creatorid, Is_Deleted isdeleted, Created_Time createdtime, Last_Modified lastmodified
      From service where Creator_Id = ownerId and Last_Modified > p_lastupdate);
  ELSE
       (SELECT tmp.Service_Id serviceid,tmp.Service_Name servicename,tmp.Description descp,
      tmp.SRepeat srepeat,FROM_UNIXTIME(tmp.Start_Datetime) startdatetime,FROM_UNIXTIME(tmp.End_Datetime) enddatetime,tmp.Alert alert,tmp.Creator_Id creatorid,tmp.Is_Deleted isdeleted,
      tmp.Created_Time createdtime,tmp.Last_Modified lastmodified 
      FROM tmp_service tmp)
    UNION
    (SELECT Service_Id serviceid, Service_Name servicename, Description descp, SRepeat srepeat,
      FROM_UNIXTIME(Start_Datetime) startdatetime, FROM_UNIXTIME(End_Datetime) enddatetime, Alert alert,
      Creator_Id creatorid, Is_Deleted isdeleted, Created_Time createdtime, Last_Modified lastmodified
      From service where Creator_Id = ownerId and Is_Deleted = 0);
  END If;

  
   DROP TABLE  tmp_service;
   END$$

DELIMITER ;
