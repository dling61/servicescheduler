
  DROP PROCEDURE IF EXISTS getServerSetting;
  DELIMITER $$
  CREATE PROCEDURE `getServerSetting`()
  BEGIN
  

  -- Time Zone list
  SELECT  t.Id id, t.Tz_Name tzname , t.Display_Name displayname, t.Display_Order displayorder, t.Abbr abbrtzname
          FROM timezonedb t
          WHERE t.Active_Flag = 1 order by 1;
  

  -- Alert list
  SELECT  t.Id id, t.Alert_Name aname
          FROM alertsetting t
          WHERE t.Active_Flag = 1 order by 1;

  -- Enforce update
  SELECT a.id id, a.App_Version appversion, a.Enforce_Flag enforce, a.OS os, a.OS_version osversion, a.Message msg
	FROM appdevice a;

 
  END $$
  DELIMITER ;