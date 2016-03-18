ALTER TABLE event Change Schedule_Id Event_Id int(11);

ALTER TABLE event ADD  Event_Name varchar(100) AFTER Event_Id;

ALTER TABLE event ADD  Status varchar(10) AFTER Event_Name;

ALTER TABLE event ADD  Location varchar(200) AFTER Alert;

ALTER TABLE event ADD  Host varchar(200) AFTER Location;

ALTER TABLE event ADD  REvent_Id int(11) AFTER Host;

ALTER TABLE event ADD  Primary Key (Event_Id);

ALTER TABLE event ENGINE=InnoDB;













