  DELIMITER $$
  DROP PROCEDURE IF EXISTS getServiceByLastUpdate;
  CREATE PROCEDURE `getServiceByLastUpdate`(IN ownerid INT, IN p_lastupdate Datetime)
  BEGIN
  
  
 Drop table if exists tmp_service;
    Create table tmp_service (
  `Service_Id` int(11) NOT NULL,
  `Service_Name` varchar(100) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `SRepeat` int(11) NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL,
  `From_Date` date NOT NULL,
  `To_Date` date NOT NULL,
  `Alert` int(11) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Valid` bit(1) not null,
  `InputId` bit(1) not null) Engine=MyISAM;
  

  Insert into tmp_service (Service_Id,Service_Name,Description,SRepeat,Start_Time,End_Time,From_Date,To_Date,Alert,Creator_Id,Is_Deleted,Created_Time,Last_Modified,Valid, InputId)
  SELECT distinct s.Service_Id,s.Service_Name,s.Description,s.SRepeat,s.Start_Time,s.End_Time,s.From_Date,s.To_Date,s.Alert,s.Creator_Id,s.Is_Deleted,s.Created_Time,s.Last_Modified,0,0 
  from service s, onduty o, member m 
  where s.Service_Id = o.Service_Id 
       and o.Member_Id = m.Member_Id 
       and m.Member_Email = (select Email from user where User_Id = ownerid) 
       and ((o.Last_Modified > p_lastupdate) or (s.Last_Modified > p_lastupdate));

  
    IF p_lastupdate !="0000-00-00 00:00:00" THEN
    SELECT tmp.Service_Id serviceid,tmp.Service_Name servicename,tmp.Description descp,
      tmp.SRepeat srepeat,tmp.Start_Time starttime,tmp.End_Time endtime,tmp.From_Date fromdate,
      tmp.To_Date todate,tmp.Alert alert,tmp.Creator_Id creatorid,tmp.Is_Deleted isdeleted,
      tmp.Created_Time createdtime,tmp.Last_Modified lastmodified 
      FROM tmp_service tmp;
  ELSE
    (SELECT tmp.Service_Id serviceid,tmp.Service_Name servicename,tmp.Description descp,
      tmp.SRepeat srepeat,tmp.Start_Time starttime,tmp.End_Time endtime,tmp.From_Date fromdate,
      tmp.To_Date todate,tmp.Alert alert,tmp.Creator_Id creatorid,tmp.Is_Deleted isdeleted,
      tmp.Created_Time createdtime,tmp.Last_Modified lastmodified 
      FROM tmp_service tmp)
    UNION
    (SELECT Service_Id serviceid, Service_Name servicename, Description descp, SRepeat srepeat,
      Start_Time starttime, End_Time endtime, From_Date fromdate, To_Date todate, Alert alert,
      Creator_Id creatorid, Is_Deleted isdeleted, Created_Time createdtime, Last_Modified lastmodified
      From service where Creator_Id = ownerId and Is_Deleted = 0);
  END If;

  
   DROP TABLE  tmp_service;
   END $$
   DELIMITER ;
