
ALTER TABLE schedule ADD Schedule_Name varchar(100) AFTER Schedule_Id;

ALTER TABLE schedule ADD  Location varchar(200) AFTER Alert;

ALTER TABLE schedule ADD  Host varchar(200) AFTER Location;

ALTER TABLE schedule ADD  REvent_Id int(11) AFTER Host;

ALTER TABLE schedule ENGINE=InnoDB;











