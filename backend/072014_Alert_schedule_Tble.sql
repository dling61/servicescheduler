
ALTER TABLE schedule ADD Abbr_Tzname varchar(100) AFTER Service_Id;

ALTER TABLE schedule ADD Alert int(11) AFTER Abbr_Tzname;



