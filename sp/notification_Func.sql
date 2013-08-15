
DELIMITER $$
 
DROP FUNCTION IF EXISTS `notification_message`$$
 
CREATE FUNCTION `notification_message`(
			sname VARCHAR(100), 
			stime DATETIME
		) RETURNS VARCHAR(200)
    DETERMINISTIC
BEGIN
	-- Function logic here
	DECLARE message VARCHAR(200);
	SET message = CONCAT(sname,' will occur at ',stime);
        return message;
	
END$$
 
DELIMITER ;