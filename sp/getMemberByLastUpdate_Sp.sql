  DELIMITER $$
  DROP PROCEDURE IF EXISTS getMemberByLastUpdate;
  CREATE PROCEDURE `getMemberByLastUpdate`(IN ownerid INT, IN p_lastupdate Datetime)
  BEGIN
  
  
-- drop the temporary table if exists
  Drop table if exists tmp_member;
  -- create the temporary table, here you could use Engine=Memory
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
  
  
-- insert into the temporary table with a valid flag = 0 (false)    
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
      FROM tmp_member tmp);
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
  END $$
  DELIMITER ;
