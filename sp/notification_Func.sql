
DELIMITER $$
 
DROP FUNCTION IF EXISTS `notification_message`$$
 
CREATE FUNCTION `notification_message`(
			sname VARCHAR(100), 
			stime DATETIME
		) RETURNS VARCHAR(1000)
    DETERMINISTIC
BEGIN
	-- Function logic here
	DECLARE message DEFAULT NULL;
	IF (a+b) > 100 THEN
		SET run = 1;
	ELSE
		SET run = 2;
	END IF;
	RETURN run;
	
END$$
 
DELIMITER ;