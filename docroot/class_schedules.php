<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');

class Schedules Extends Resource
{
	
    public function __construct($request) {
        parent::__construct($request);
		
    }
	protected $lastid;
	// create a new schedule
	Protected function insert($ownerid, $serviceid, $schedule_parms) {
	    $members = array();													
		
		$scheduleid = $schedule_parms['scheduleid'];
		$description = $schedule_parms['desp'];
		$startdatetime = $schedule_parms['startdatetime'];
		$enddatetime = $schedule_parms['enddatetime'];
		$alert = $schedule_parms['alert'];
		$tzid = $schedule_parms['tzid'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		
		$query = "SELECT Schedule_Id FROM schedule WHERE Schedule_Id = $scheduleid";
        $data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data)==1) {
		    // service already exists
			header('HTTP/1.0 201 This schedule exists already', true, 201);
			exit;
	    }
		else {
			// two steps commit
			mysqli_autocommit($dbc, FALSE);
			// Insert this schedule if no exists
			$queryinsert = "INSERT INTO schedule ".
								"(Schedule_Id,Service_Id,Start_DateTime,End_DateTime,Description,Alert,Tz_Id,Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
								" values('$scheduleid','$serviceid',UNIX_TIMESTAMP('$startdatetime'),UNIX_TIMESTAMP('$enddatetime'),'$description','$alert','$tzid', ".
								" '$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(),'$ownerid')";
			
			$result = mysqli_query($dbc,$queryinsert) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 Cannot insert schedule', true, 202);
				exit;
			}
			else {
				// go ahead to insert members in the onduty table
				// 07/2014 add confirm
				if ($schedule_parms['members']) {
				    foreach($schedule_parms['members'] as $member) {
					    $memberid = $member['memberid'];
						$confirm = $member['confirm'];
					  
						$queryinsert1 = "insert into onduty".
								 "(Service_Id, Schedule_Id,Member_Id,Confirm, Creator_Id, Created_Time,Last_Modified, Last_Modified_Id) ".
								 "values('$serviceid','$scheduleid','$memberid','$confirm','$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
						$result = mysqli_query($dbc,$queryinsert1);
						if ($result !== TRUE) {
							mysqli_rollback($dbc);
							header('HTTP/1.0 202 Cannot insert into onduty', true, 203);
							exit;
						}
					}
				}
			    mysqli_commit($dbc);
			}	
		}
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		$data->close();
		mysqli_close($dbc);
	}
	
	// this is to update schedule and member assigment 
	// 12/04/2013  dding  --- don't update the last_modified_id due to the lack of this information
	// 06/22/2014  dding  --- add alert/Tz_Id
	Protected function update($serviceid, $scheduleid, $ownerid, $schedule_parms) {
		$members = array();													
		
		$description = $schedule_parms['desp'];
		$startdatetime = $schedule_parms['startdatetime'];
		$enddatetime = $schedule_parms['enddatetime'];
		$alert = $schedule_parms['alert'];
		$tzid = $schedule_parms['tzid'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM schedule WHERE Schedule_Id = '$scheduleid'";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
        if (mysqli_num_rows($data)==1) {
		    // Serchdule exists and go ahead to update it
			// two steps commit
			mysqli_autocommit($dbc, FALSE);
		
			$queryupdate = "update schedule set ".
						"Service_Id = '$serviceid', Description = '$description', Start_DateTime = UNIX_TIMESTAMP('$startdatetime'), ".
						" End_DateTime = UNIX_TIMESTAMP('$enddatetime'), Alert = '$alert', Tz_Id = '$tzid', Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
						" where Schedule_Id = '$scheduleid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 201 Failed to update', true, 201);
				exit;
			}
			else {
				// first to delete the existing relationship
				$querydelete = "delete FROM onduty WHERE Schedule_Id = '$scheduleid' and Service_Id = '$serviceid'";
				
				$result = mysqli_query($dbc,$querydelete) or die("Error is: \n ".mysqli_error($dbc));
				if ($result !== TRUE) {
					// if error, roll back transaction
					mysqli_rollback($dbc);
					header('HTTP/1.0 201 Failed to delete the existing onduty', true, 201);
					exit;
				}
				//go ahead to insert members in the onduty table
				// 07/2014: Add confirmation in the onduty
				if ($schedule_parms['members']) {
					foreach($schedule_parms['members'] as $member) {
					    $memberid = $member['memberid'];
						$confirm = $member['confirm'];
					  
						$queryinsert1 = "insert into onduty".
								 "(Service_Id, Schedule_Id,Member_Id,Confirm, Creator_Id, Created_Time,Last_Modified, Last_Modified_Id) ".
								 "values('$serviceid','$scheduleid','$memberid','$confirm','$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
						$result = mysqli_query($dbc,$queryinsert1);
						if ($result !== TRUE) {
							mysqli_rollback($dbc);
							header('HTTP/1.0 202 Cannot insert into onduty', true, 203);
							exit;
						}
					}
				}
			    mysqli_commit($dbc);
				$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
				echo $data2;
			}		
		}
		else {
			header('HTTP/1.0 202 This schedule doesn\'t exist', true, 202);
		}
		$data->close();
		mysqli_close($dbc);
	}
	
	//
	//  This is to update the confirm status for a member
	//  http://[domain name]/schedules/1234/onduty/1111
	//
	Protected function update_confirm($scheduleid, $memberid, $schedule_parms) {
		$ownerid = $schedule_parms['ownerid'];
		$confirm = $schedule_parms['confirm'];
	    
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM onduty WHERE Member_id = '$memberid' and Is_Deleted = 0 and schedule_id = '$scheduleid' ";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
        if (mysqli_num_rows($data)==1) {
			// first to check if the ownerid is equal to the corresponding memberid
			$query1 = "SELECT m.* FROM member m, user u where u.Email = m.Member_Email and u.User_ID = '$ownerid' and m.Member_id = '$memberid' ";
			$result = mysqli_query($dbc, $query1);
		
			if (mysqli_num_rows($result)==1) {
				$query2 = "Update onduty set confirm = '$confirm', last_Modified = UTC_TIMESTAMP(), last_modified_id = '$ownerid' ".
					" where Member_id = '$memberid' and schedule_id = '$scheduleid'";
				$result1 = mysqli_query($dbc, $query2) or die("Error is: \n ".mysqli_error($dbc));
			    
				if ($result1 !== TRUE) {
					// fail to update confirm
					header('HTTP/1.0 201 Fail to update confirm on onduty', true, 203);
					exit;
				}
			}
			else {
				header('HTTP/1.0 202 user does not allow to update confirm', true, 202);
				exit;
			}
		}
		else {
			header('HTTP/1.0 202 This assignment doesn\'t exist', true, 201);
			exit;
		}
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		$data->close();
		mysqli_close($dbc);
	}
	
	/**
	  This is to delete the schedule
	**/
	Protected function pdelete($scheduleid, $ownerid) {
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT * FROM schedule WHERE Schedule_Id = '$scheduleid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This schedule doesn\'t exist and has been deleted', true, 201);
	    }
		else {
			// Serchdule exists and go ahead to update it
			// two steps commit
			mysqli_autocommit($dbc, FALSE);
			// update this schedule by setting the flag Is_Deleted to 1
			$queryupdate = "update schedule set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
						" where Schedule_Id = '$scheduleid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 This schedule can\'t be deleted', true, 202);
				exit;
			}
			else {
				// first to delete the existing relationship
				$querydelete = "update onduty set ".
				               " Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
							   " WHERE Schedule_Id = '$scheduleid'";
				
				$result = mysqli_query($dbc,$querydelete) or die("Error is: \n ".mysqli_error($dbc));
				if ($result !== TRUE) {
					// if error, roll back transaction
					mysqli_rollback($dbc);
					header('HTTP/1.0 204 Failed to delete onduty', true, 204);
					exit;
				}
				mysqli_commit($dbc);
				$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
				echo $data2;
			}	
		}
		$data->close();
		mysqli_close($dbc);
	}
	
	/**
	    This method is for retrieving all schedules associated with the device owner assigned to it
		This API is deprecated because sharedmember concept is used in 1.2.0
	**/
	Protected function pgetlastupdate($ownerid, $lastupdatetime) {
		$return_arr = array();
		$delschedule_arr = array();
		$schedules_arr = array();
		$members_att = array();
		
		// get the list of scheduleid and lastmodified
		$mysql = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		// call a stored procedure to get the schedules to be returned to caller
		if ($mysql->multi_query("CALL getScheduleByLastUpdate('$ownerid', '$lastupdatetime')")) {
	   
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
						$odisdeleted = $row['odisdeleted'];
						// if it's deleted, just add it to "deletedschedules"
						if ($isdeleted == 1 || $odisdeleted == 1) {
							$delschedule_arr[$j] = $row['scheduleid'];
							$j++;
						}
						else {
						   $one_arr = array();
						   $one_arr['scheduleid'] = $row['scheduleid'];
						   $one_arr['serviceid'] = $row['serviceid'];
						   $one_arr['desp'] = $row['description'];
						   $one_arr['creatorid'] = $row['creatorid'];
						   $one_arr['startdatetime'] = $row['starttime'];
						   $one_arr['enddatetime'] = $row['endtime'];
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
	

	/***
	   Followings are the functions called from index.php
	***/	
    public function get($request) {   
        $ownerid = $request->parameters['ownerid'];
		$lastupdatetime = urldecode($request->parameters['lastupdatetime']);
		
		header('Content-Type: application/json; charset=utf8');
		$this->pgetlastupdate($ownerid, $lastupdatetime);
    }

	// This is the API to create a new schedule in the server
    public function post($request) {
		$parameters1 = array();
		
		if ($request->body_parameters['schedules']) {
			foreach($request->body_parameters['schedules'] as $param_name => $param_value) {
				$parameters1[$param_name] = $param_value;				
			}
		}
		
		header('Content-Type: application/json; charset=utf8');
		// process them to insert into the schedule table and onDuty
		$this->insert($request->body_parameters['ownerid'], $request->body_parameters['serviceid'], $parameters1);
	    
    }
	
	// update a schedule with the schedule Id and update confirm status
	// 1. PUT http://[api_domain_name]/schedules/1234
	// 2. PUT http://[api_domain_name]/schedules/1234/onduty/1111
	public function put($request) {
		$parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		
		if ($last2Element == "schedules") {
			if ($request->body_parameters['schedules']) {
				foreach($request->body_parameters['schedules'] as $param_name => $param_value) {
								$parameters1[$param_name] = $param_value;
				}
			}
			// update a schedule
			$scheduleid = end($request->url_elements);
			reset($request->url_elements);
			$this->update($request->body_parameters['serviceid'], $scheduleid, $request->body_parameters['ownerid'], $parameters1);
	    }
		else if ($last2Element == "onduty") {
			//get memberid and scheduleid
			$memberid = end($request->url_elements);
			reset($request->url_elements);
			$scheduleid = $request->url_elements[count($request->url_elements)-3];
			$this->update_confirm($scheduleid, $memberid, $request->body_parameters);
		}	
    }
	
	/**
	   There is a body element "ownerid" in the DELETE HTTP Method 
	**/
	public function delete($request) {
	     // logic to handle an HTTP DELETE request goes here
		header('Content-Type: application/json; charset=utf8');
		$scheduleid = end($request->url_elements);
		reset($request->url_elements);
		$ownerid = $request->parameters['ownerid'];
		$this->pdelete($scheduleid, $ownerid);
    }


}
?>