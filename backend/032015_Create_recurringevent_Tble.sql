
--  Freq_Type --- How frequently an event occurs 
-- 
--    4 = Daily; 8 = Weekly; 16 = Monthly; 32 = Yearly
--
--  Freq_Interval  --- Days that the event occurs. Depends on the value of freq_type,
--                     The default value is 0, which indicates that freq_interval is unused
--    
--             4(daily)     --- Every freq_interval days
--             8(weekly)    --- Freq_interval is one or more of the following:
--                                   1= Sunday; 2=Monday; 3=Tuesday; 4=Wednesday; 5=Thursday; 6=Friday; 7=Saturday
--                          e.g. 12 = Sunday and Monday
--             16(monthly)  --- On the freq_interval day of the month.
--             32(yearly)   --- On the freq_interval day of the year.
-- 


CREATE TABLE IF NOT EXISTS `recurringevent` (
  `REvent_Id` int(11) NOT NULL,
  `Start_Day` date NOT NULL,
  `End_Day`   date NOT NULL,
  `Freq_Type` int(2) NOT NULL,  
  `Freq_Interval` int(7) NOT NULL,
  `Is_Deleted` tinyint(1) NOT NULL,
  `Creator_Id` int(11) NOT NULL,
  `Created_Time` datetime NOT NULL,
  `Last_Modified` datetime NOT NULL,
  `Last_Modified_Id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
