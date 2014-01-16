
ALTER TABLE service ADD Start_Datetime INT(11) AFTER To_Date;

ALTER TABLE service ADD End_Datetime INT(11) AFTER Start_Datetime;

ALTER TABLE service ADD Last_Modified_Id INT(11) AFTER Last_Modified;