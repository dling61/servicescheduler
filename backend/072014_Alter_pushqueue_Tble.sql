ALTER TABLE pushqueue ADD Device_Id int(2) NOT NULL DEFAULT 0 AFTER Target_Token;
alter table pushqueue modify Target_Token varchar(4096);