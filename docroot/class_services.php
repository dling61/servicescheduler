<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');
require_once('class_request.php');

class Services Extends Resource
{
	
   public function __construct($request) {
        parent::__construct($request);
	}	
	
	//protected $lastid;
	
	// Insert a member if it doesn't exist and then a shared member with the creator role
	Protected function insert_creator($ownerid, $serviceid) {
	
	   $dbc1 = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
       $query1 = "SELECT Email, User_Name, Mobile FROM user WHERE User_Id = $ownerid";
	   
	   $data1 = mysqli_query($dbc1, $query1);
		
		if (mysqli_num_rows($data1)==1) {
			$row1 = mysqli_fetch_array($data1);
			
			$email = $row1['Email'];
			$membername = $row1['User_Name'];
			$mobile = $row1['Mobile'];
			
			$memberid = $ownerid*10000;
			
			// Insert this service if no exists
			$queryinsert = "insert ignore into member(Member_Id,Member_Email,Member_Name,Mobile_Number,Is_Registered,Creator_Id, Created_Time, Last_Modified)
			                 values('$memberid','$email','$membername','$mobile',0, '$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP())";

			$result = mysqli_query($dbc1,$queryinsert) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 202 Can not add the creator member', true, 202);
				Exit;
			}
			
			$queryinsert1 = "insert into sharedmember".
						 " (Service_Id, Member_Id, Shared_Role, Is_Deleted,Creator_Id,Created_Time, Last_Modified, Last_Modified_Id) ".
						 "values('$serviceid','$memberid', 0,0,'$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
			$result1 = mysqli_query($dbc1,$queryinsert1) or die("Error is: \n ".mysqli_error($dbc));
			if ($result1 !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 202 Can not add the creator member', true, 202);
				Exit;
			}
		}
		$data1->close();
		mysqli_close($dbc1);
	
	}
	
	
	// create a new service
	Protected function insert($ownerid, $service_parms) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		 
		$serviceid = $service_parms['serviceid'];
		$servicename = $service_parms['servicename'];
		$description = $service_parms['desp'];
		$srepeat = $service_parms['repeat'];
		$startdatetime = $service_parms['startdatetime'];
		$enddatetime = $service_parms['enddatetime'];
		$utcoff = $service_parms['utcoff'];
		$alert = $service_parms['alert'];
	  
		$query = "SELECT Service_Id FROM service WHERE Service_Id = $serviceid";
        $data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data)==1) {
		    // service already exists
			header('HTTP/1.0 201 This service exists already', true, 201);
	    }
		else {
			// Insert this service if no exists
			$queryinsert = "INSERT INTO service ".
								"(Service_Id,Service_Name,Description,SRepeat,Start_Datetime,End_Datetime,UTC_Off,Alert,Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
								" values('$serviceid','$servicename','$description','$srepeat',UNIX_TIMESTAMP('$startdatetime'),UNIX_TIMESTAMP('$enddatetime'),'$utcoff','$alert','$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(), '$ownerid')";
			
			$result = mysqli_query($dbc,$queryinsert) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 202 This service can\' be added', true, 202);
				Exit;
			}
			
			// insert a shared member and member if it doesn't exist
			$this->insert_creator($ownerid, $serviceid);
			
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		    //echo json_encode($data2);
            echo $data2;			
		}
		$data->close();
		mysqli_close($dbc);
	}

	Protected function update($serviceid, $ownerid, $service_parms) {
		$servicename = $service_parms['servicename'];
		$description= $service_parms['desp'];
		$repeat = $service_parms['repeat'];
		$startdatetime = $service_parms['startdatetime'];
		$enddatetime = $service_parms['enddatetime'];
		// Don't need to update it
		//$utcoff = $service_parms['utcoff'];
		$alert = $service_parms['alert'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM service WHERE Service_Id = '$serviceid'";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
        if (mysqli_num_rows($data)==1) {
		    // Service exists and go ahead to update it
			$queryupdate = "update service set ".
						"Service_Name = '$servicename', Description = '$description', SRepeat = '$repeat', Start_Datetime = UNIX_TIMESTAMP('$startdatetime'), End_Datetime = UNIX_TIMESTAMP('$enddatetime'), Last_Modified = UTC_TIMESTAMP(),Last_Modified_Id = '$ownerid' ".
						" , Alert = '$alert' where Service_Id = '$serviceid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 201 This member can not be updated', true, 201);
				exit;
			}
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			//echo json_encode($data2);
			echo $data2;
			
			
		}
		else {
			header('HTTP/1.0 202 This member doesn\'t exist', true, 202);
		}
		$data->close();
		mysqli_close($dbc);
	}
	
	// This is to update the role for the activity shared with a member
	Protected function update_sm($serviceid, $memberid, $body_parms) {
		$ownerid = $body_parms['ownerid'];
		$sharedrole= $body_parms['sharedrole'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM sharedmember WHERE Service_Id = '$serviceid' and Member_Id = '$memberid' and Is_Deleted = 0 ";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
        if (mysqli_num_rows($data)==1) {
		    // shared member exists and go ahead to update it
			$queryupdate = "update sharedmember set ".
						"Shared_Role = '$sharedrole', Last_Modified = UTC_TIMESTAMP, Last_Modified_Id = '$ownerid'".
						"WHERE Service_Id = '$serviceid' and Member_Id = '$memberid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 201 This shared member can not be updated', true, 201);
				exit;
			}
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			//echo json_encode($data2);
			echo $data2;	
		}
		else {
			header('HTTP/1.0 202 This shared member doesn\'t exist', true, 202);
			$data2 = json_encode(array('code'=> 202, 'message' => 'This shared member doesn\'t exist'));
			echo $data2;
		}
		
		$data->close();
		mysqli_close($dbc);	
	}
	
	// delete a service
	Protected function pdelete($serviceid, $ownerid) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT * FROM service WHERE Service_Id = '$serviceid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This service doesn\'t exist or has been deleted', true, 201);
			exit;
	    }
		else {
			// Service exists and go ahead to update it
			// two steps commit
			mysqli_autocommit($dbc, FALSE);
	
			// Delete this service by setting the flag Is_Deleted to 1
			$queryupdate = "update service set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid'".
						" where Service_Id = '$serviceid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 This service can\'t be deleted', true, 202);
				exit;
			}
			else {
			    // first to check if there are some schedules associated with this service
				$query = "SELECT * FROM schedule WHERE Service_Id = '$serviceid' and Is_Deleted = 0";
				$data = mysqli_query($dbc, $query) or die(mysqli_error());
				if (mysqli_num_rows($data) != 0) {
					// second to delete the existing relationship
					$queryupdate = "update schedule set ".
							" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
							" where Service_Id = '$serviceid'";
			
					$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
					if ($result !== TRUE) {
						// if error, roll back transaction
						mysqli_rollback($dbc);
						header('HTTP/1.0 203 Failed to delete schedule', true, 203);
						exit;
					}
					else {   
						// update the existing relationship
						//$querydelete = "update onduty set ".
						//	" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
						//	" where Service_Id = '$serviceid'";
						
						$querydelete = "update sharedmember set ".
							" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
							" where Service_Id = '$serviceid'";
						$result = mysqli_query($dbc,$querydelete) or die("Error is: \n ".mysqli_error($dbc));
						if ($result !== TRUE) {
							// if error, roll back transaction
							mysqli_rollback($dbc);
							header('HTTP/1.0 204 Failed to delete sharedrole in sharedmember', true, 204);
							exit;
						}
						mysqli_commit($dbc);
					}
				}					
			}	
		}
		
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		$data->close();
		mysqli_close($dbc);
	}
	
	// remove a sharing from a member with a service
	Protected function pdelete_sharedmembers($serviceid, $memberid, $ownerid) {
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT * FROM sharedmember WHERE Service_Id = '$serviceid' and Member_Id = '$memberid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This member doesn\'t shared with the service', true, 201);
			exit;
	    }
		else {
			// Delete this shared member by setting the flag Is_Deleted to 1
			$update = "update sharedmember set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
						" where Service_Id = '$serviceid' and Member_Id = '$memberid'";
			$result = mysqli_query($dbc,$update) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				header('HTTP/1.0 204 Failed to delete shared member', true, 204);
				exit;
			}
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;
		}
	}
	
	Protected function pgetlastupdate($ownerid, $lastupdatetime) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		/**
		$query = "(SELECT Service_Id serviceid,Service_Name servicename,Description descp, ".
				 " SRepeat srepeat,FROM_UNIXTIME(Start_Datetime) startdatetime,FROM_UNIXTIME(End_Datetime) enddatetime, Alert alert, Creator_Id creatorid,Is_Deleted isdeleted,".
                 " Created_Time createdtime,Last_Modified lastmodified, 0 as sharedrole ".
				 " FROM service where creator_id = '$ownerid' and Last_Modified > '$lastupdatetime') ".
                 " UNION ".
                 " (SELECT distinct s.Service_Id serviceid ,s.Service_Name servicename,s.Description descp, ".
                 " s.SRepeat srepeat,FROM_UNIXTIME(Start_Datetime) startdatetime, FROM_UNIXTIME(End_Datetime) enddatetime,s.Alert alert,s.Creator_Id creatorid, ".
                 " o.Is_Deleted isdeleted,s.Created_Time createdtime,s.Last_Modified lastmodified,o.Shared_Role sharedrole ".
                 " from service s, sharedmember o, member m ".
                 " where s.Service_Id = o.Service_Id ".
                 " and o.Member_Id = m.Member_Id ".
                 " and m.Member_Email = (select Email from user where User_Id = '$ownerid') ". 
                 " and ((o.Last_Modified > '$lastupdatetime') or (s.Last_Modified > '$lastupdatetime')))";
		**/
        $query = " SELECT distinct s.Service_Id serviceid ,s.Service_Name servicename,s.Description descp, ".
                 " s.SRepeat srepeat,FROM_UNIXTIME(Start_Datetime) startdatetime, FROM_UNIXTIME(End_Datetime) enddatetime,s.UTC_Off utcoff,s.Alert alert,s.Creator_Id creatorid, ".
                 " if (s.Is_Deleted = 1 or o.Is_Deleted = 1, 1, 0) isdeleted,s.Created_Time createdtime,s.Last_Modified lastmodified,o.Shared_Role sharedrole ".
                 " from service s, sharedmember o, member m ".
                 " where s.Service_Id = o.Service_Id ".
                 " and o.Member_Id = m.Member_Id ".
                 " and m.Member_Email = (select Email from user where User_Id = '$ownerid') ". 
                 " and ((o.Last_Modified > '$lastupdatetime') or (s.Last_Modified > '$lastupdatetime'))";
		
		$data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
		$return_arr = array();
		$serviceid_arr = array();
		$services_arr = array();
		
		if(mysqli_num_rows($data) > 0) {
			$i = 0;
			$j = 0;
			while($row0 = mysqli_fetch_array($data)){
			   $isdeleted = $row0['isdeleted'];
				// if it's deleted, just add it to "dservices"
			   if ($isdeleted == 1) {
				 $serviceid_arr[$j] = $row0['serviceid'];
				 $j++;
			   }
			   else {
				   $one_arr = array();
				   $one_arr['serviceid'] = $row0['serviceid'];
				   $one_arr['servicename'] = $row0['servicename'];
				   $one_arr['desp'] = $row0['descp'];
				   $one_arr['repeat'] = $row0['srepeat'];
				   //$one_arr['starttime'] = $row0['starttime'];
				   //$one_arr['endtime'] = $row0['endtime'];
				   //$one_arr['fromdate'] = $row0['fromdate'];
				   //$one_arr['todate'] = $row0['todate'];
				   $one_arr['startdatetime'] = $row0['startdatetime'];
				   $one_arr['enddatetime'] = $row0['enddatetime'];
				   $one_arr['utcoff'] = $row0['utcoff'];
				   $one_arr['alert'] = $row0['alert'];
				   $one_arr['creatorid'] = $row0['creatorid'];
				   $one_arr['createdtime'] = $row0['createdtime'];
				   $one_arr['lastmodified'] = $row0['lastmodified'];
				   $one_arr['sharedrole'] = $row0['sharedrole'];
				   
				   $services_arr[$i] = $one_arr;
				   $i++;			   
			   }   
			   
			} // while end
		} // if end
		$return_arr['deletedservices'] = $serviceid_arr;
		$return_arr['services'] = $services_arr;
         
		$data2 = json_encode($return_arr);
		echo $data2;
      
	    // logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid, $data2);
			
		$data->close();
		mysqli_close($dbc);	
	}
	
	// This is for sharedmember API to get the latest shared members
	Protected function pgetlastupdate_sm($serviceid, $ownerid, $lastupdatetime) {
	    
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
	
		// call a stored procedure to get the services to be returned to caller
		$query = "SELECT sm.Is_Deleted isdeleted, sm.Member_Id memberid, m.Member_Email memberemail, m.Member_Name membername, m.Mobile_Number mobilenumber, ".
		    " sm.Creator_Id creatorid, sm.Service_Id serviceid, sm.Created_Time createdtime, sm.Last_Modified lastmodified, sm.Shared_Role sharedrole ".
 		    " FROM sharedmember sm, member m where sm.Member_Id = m.Member_Id and sm.Service_Id = '$serviceid' and ".
     		"(sm.Last_Modified > '$lastupdatetime' or m.Last_Modified > '$lastupdatetime')";
			
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc)); 
		
		$return_arr = array();
		$memberid_arr = array();
		$members_arr = array();
		
		if(mysqli_num_rows($data) > 0) {
			$i = 0;
			$j = 0;
			while($row0 = mysqli_fetch_array($data)){
			   $isdeleted = $row0['isdeleted'];
				// if it's deleted, just add it to "deletedsmembers"
			   if ($isdeleted == 1) {
				 $memberid_arr[$j] = $row0['memberid'];
				 $j++;
			   }
			   else {
				   $one_arr = array();
				  
				   $one_arr['memberid'] = $row0['memberid'];
				   $one_arr['memberemail'] = $row0['memberemail'];
				   $one_arr['membername'] = $row0['membername'];
				   $one_arr['mobilenumber'] = $row0['mobilenumber'];
				   $one_arr['creatorid'] = $row0['creatorid'];
				   $one_arr['serviceid'] = $row0['serviceid'];
				   $one_arr['createdtime'] = $row0['createdtime'];
				   $one_arr['lastmodified'] = $row0['lastmodified'];
				   $one_arr['sharedrole'] = $row0['sharedrole'];
				   
				   $members_arr[$i] = $one_arr;
				   $i++;			   
			   }   
			   
			} // while end
		} // if end
		$return_arr['deletedsmembers'] = $memberid_arr;
		$return_arr['sharedmembers'] = $members_arr;
         
		$data2 = json_encode($return_arr);
		echo $data2;
		
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid,$data2);
      
		$data->close();
		mysqli_close($dbc);	
	}
	
	
	// This is for get schedules API to get the latest schedules and members assigned to schedules
	Protected function pgetlastupdate_sh($serviceid, $lastupdatetime) {
	    
		$return_arr = array();
		$delschedule_arr = array();
		$schedules_arr = array();
		$members_att = array();
		
		// get the list of scheduleid and lastmodified
		$mysql = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		// call a stored procedure to get the schedules to be returned to caller
		if ($mysql->multi_query("CALL getScheduleByLastUpdate('$serviceid', '$lastupdatetime')")) {
	   
          $h = 0;
		  //loop through twp resultsets
          do {
            if ($result = $mysql->use_result())
            {
			    $i = 0;
				$j = 0;
				$k = 0;
                //Loop the two result sets, reading it into an array
                while ($row = $result->fetch_array(MYSQLI_ASSOC))
                {
                    if ($h == 0) {
					   // first resultset 
						$isdeleted = $row['isdeleted'];
						
						// if it's deleted, just add it to "deletedschedules"
						if ($isdeleted == 1) {
							$delschedule_arr[$j] = $row['scheduleid'];
							$j++;
						}
						else {
						   $one_arr = array();
						   $one_arr['scheduleid'] = $row['scheduleid'];
						   $one_arr['serviceid'] = $row['serviceid'];
						   $one_arr['desp'] = $row['description'];
						   $one_arr['creatorid'] = $row['creatorid'];
						   $one_arr['startdatetime'] = $row['startdatetime'];
						   $one_arr['enddatetime'] = $row['enddatetime'];
						   $one_arr['members'] = "";
						   $one_arr['createdtime'] = $row['createdtime'];
						   $one_arr['lastmodified'] = $row['lastmodified'];
						   
						   $schedules_arr[$i] = $one_arr;
						   $i++;			   
						}     
					}
					else {
					   // second resultset 
						$two_arr = array();
						$two_arr['scheduleid'] = $row['scheduleid'];
						$two_arr['memberid'] = $row['memberid'];
						
					    $members_att[$k] = $two_arr;
						$k++;
					}
                } // while end
				
                // Close the result set
                $result->close();
				$h++;
            }
          } while ($mysql->more_results() == TRUE && $mysql->next_result());
        }
        else
        {
            echo '<strong>Error Message ' . $mysql->error . '</strong></p>';
        }
		
	    //$delschedule_str = array();
		//$delschedule_str = implode(",", $delschedule_arr); // concat to the string seprating by ,
		
		foreach ($schedules_arr as &$svalue) {
			$scheduleid = $svalue['scheduleid'];
			
			// get the list of memberid associated with the schedule ID
			$members_str = '';
			$i = 0;
			foreach ($members_att as $mvalue) {
			  if ($mvalue['scheduleid'] == $scheduleid) {
				if($i==0)
				{
					$members_str.= $mvalue['memberid'];
				}
				else
				{
					$members_str .=",".$mvalue['memberid'];
				}
				$i++;
			  }
			}
			//insert members associated with the schedule into the schedules_arr TBD
			$svalue['members'] = $members_str;
		}
		unset($svalue);
		
	    //$return_arr['deletedschedules'] = $delschedule_str;
		$return_arr['deletedschedules'] = $delschedule_arr;
		$return_arr['schedules'] = $schedules_arr;
         
		$data2 = json_encode($return_arr);
		echo $data2;
		
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid,$data2);
     
		mysqli_close($mysql);	
	}
	
	// This is for sharedmember API to share a service with a member
	// [DD] 10/10/2013  --- Reshare a member after it was removed(unshared) before.
	Protected function insert_sharedmembers($serviceid, $body_param) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		
		$ownerid = $body_param['ownerid'];
		$memberid = $body_param['memberid'];
		$sharedrole = $body_param['sharedrole'];
		
		$query = "SELECT Is_Deleted isdeleted FROM sharedmember WHERE Service_Id = '$serviceid' and Member_Id = '$memberid' LIMIT 1";
		$data = mysqli_query($dbc, $query) or die(mysqli_error());
	    $result = mysqli_fetch_assoc($data);
		if (mysqli_num_rows($data)== 1 and $result['isdeleted'] == 0) {
			header('HTTP/1.0 201 This shared member exists already', true, 201);
			$data2 = json_encode(array('code'=> 201, 'message' => 'This shared member exists already'));
			echo $data2;
			exit;	
		}
		else {
		    if (mysqli_num_rows($data)== 0) {
				$queryinsert1 = "insert into sharedmember".
						 " (Service_Id, Member_Id, Shared_Role, Is_Deleted,Creator_Id,Created_Time, Last_Modified, Last_Modified_Id) ".
						 "values('$serviceid','$memberid', '$sharedrole',0,'$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
			}
			else if ($result['isdeleted'] == 1) {
				$queryinsert1 = "update sharedmember ".
						 " set Is_Deleted = 0, Shared_Role = '$sharedrole', Last_Modified_Id = '$ownerid', Last_Modified = UTC_TIMESTAMP ".
						 " where Service_Id = '$serviceid' and Member_Id = '$memberid' ";
			}
			$result = mysqli_query($dbc,$queryinsert1);
			if ($result !== TRUE) {
				header('HTTP/1.0 203 failed to inert shared members', true, 203);
				$data2 = json_encode(array('code'=> 203, 'message' => 'failed to inert shared members'));
				echo $data2;
				exit;							
			}
			
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;			
		}
		mysqli_free_result($data);
		mysqli_close($dbc);
	}
	
	// GET method here is to handle 3 cases
	//  1. http://[REST_SERVER]/services?ownerid=12143&lastupdatetime=121333000
	//  2. http://[REST_SERVER]/services/1234/sharedmembers?ownerid=12434&lastupdatetime=121443232
	//  3. http://[REST_SERVER]/services/1234/schedules?ownerid=11122@lastupdatetime=12121
	public function get($request) {
        $ownerid = $request->parameters['ownerid'];
		$lastupdatetime = urldecode($request->parameters['lastupdatetime']);
	   
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "services") {
			$this->pgetlastupdate($ownerid, $lastupdatetime);
		}
		else if ($lastElement == "sharedmembers") {
			// handle sharedmembers
			$serviceid  = $request->url_elements[count($request->url_elements)-2];
			$this->pgetlastupdate_sm($serviceid, $ownerid, $lastupdatetime);
		} if ($lastElement == "schedules") {
			$serviceid  = $request->url_elements[count($request->url_elements)-2];
			$this->pgetlastupdate_sh($serviceid, $lastupdatetime);
		}
    }

	// This is the POST call to add a new service and share a service with a email
	//   1. POST http://servicescheduler.net/services
	//   2. POST http://servicescheduler.net/services/1234/sharedmembers
    public function post($request) {
		$parameters1 = array();
        
		header('Content-Type: application/json; charset=utf8');
		// handle different resources
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "services") {
			if ($request->body_parameters['services']) {
				foreach($request->body_parameters['services'] as $param_name => $param_value) {
							$parameters1[$param_name] = $param_value;
				}
			}
			$this->insert($request->body_parameters['ownerid'], $parameters1);
		} else if ($lastElement == "sharedmembers") {
		    $serviceid  = $request->url_elements[count($request->url_elements)-2];
			 //share member method
			$this->insert_sharedmembers($serviceid, $request->body_parameters);
		}   
    }
	
	// update a service with the service Id and update a role shared with activity
	// 1. PUT http://[api_domain_name]/services/1234
	// 2. PUT http://[api_domain_name]/services/1234/sharedmembers/1111
	public function put($request) {
        $parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		
		if ($last2Element == "services") {
			if ($request->body_parameters['services']) {
				foreach($request->body_parameters['services'] as $param_name => $param_value) {
								$parameters1[$param_name] = $param_value;
				}
			}

			//handle different resources
			$serviceid = end($request->url_elements);
			reset($request->url_elements);
			$this->update($serviceid, $request->body_parameters['ownerid'], $parameters1);
	    }
		else if ($last2Element == "sharedmembers") {
			//get memberid and serviceid 
			$memberid = end($request->url_elements);
			reset($request->url_elements);
			$serviceid = $request->url_elements[count($request->url_elements)-3];
			$this->update_sm($serviceid, $memberid, $request->body_parameters);
		
		}
    }
	
	
	// This is the DELETE call to delete a service and delete a shared member from the service
	// It can have message body for Delete (based on the latest HTTP specs)
	//   1. DELETE http://servicescheduler.net/service
	//   2. DELETE http://servicescheduler.net/service/1234/sharedmembers
	public function delete($request) {
		$last2Element = $request->url_elements[count($request->url_elements)-2];
	    
		header('Content-Type: application/json; charset=utf8');
		if ($last2Element == "services") {
			// Delete service
			$serviceid = end($request->url_elements);
			reset($request->url_elements);
			$ownerid = $request->parameters['ownerid'];
			$this->pdelete($serviceid, $ownerid);
		}
		else if ($last2Element == "sharedmembers") {
			// Delete a member email from sharedmembers
		    $serviceid = $request->url_elements[count($request->url_elements)-3];
			$memberid =  end($request->url_elements);
			reset($request->url_elements);
			$ownerid = $request->parameters['ownerid'];
			$this->pdelete_sharedmembers($serviceid, $memberid, $ownerid);
		}
		
    }

}
?>
