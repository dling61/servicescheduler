
ALTER TABLE userlog ADD Device_Id int(2) NOT NULL DEFAULT 0 AFTER Token;
alter table userlog modify Token varchar(4096);



