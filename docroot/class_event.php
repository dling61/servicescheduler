<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');

class Event Extends Resource
{
	
    public function __construct($request) {
        parent::__construct($request);
		
    }
	protected $lastid;
	
	// create a single event without tasks or one of repeating events
	// If creating a repeating event, "beventid" needs to be passed in by client. Otherwise, it's 0 for "beventid" for a single event
	// 10/16/2015
	Protected function insert_single_event($event_parms) {
	
		$ownerid = $event_parms['ownerid'];
		$communityid = $event_parms['communityid'];
		$eventid = $event_parms['eventid'];
		$eventname = $event_parms['eventname'];
		$description = $event_parms['desp'];
		$startdatetime = $event_parms['startdatetime'];
		$enddatetime = $event_parms['enddatetime'];
		$alert = $event_parms['alert'];
		$tzid = $event_parms['tzid'];
		$location = $event_parms['location'];
		$host = $event_parms['host'];
		$status = $event_parms['status'];
		$repeatscheduleid = $event_parms['repeatscheduleid'];
		$beventid =  $event_parms['beventid'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!'); 
	 
		$query = "SELECT Event_Id FROM event WHERE Event_Id = $eventid";
        $data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data) == 1) {
			header('HTTP/1.0 201 This event exists already', true, 201);
	    }
		else {	
			// case #1 Repeating events -- first repeating event
			// Create a base event in the baseschedule and the first table in the event table
			$queryinsert = "INSERT INTO event ".
									"(Event_Id,Event_Name,Community_Id,Start_DateTime,End_DateTime,Description,Alert,Tz_Id,Location, Host, Status, Repeat_Schedule_Id,BEvent_Id, Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
									" values('$eventid','$eventname','$communityid',UNIX_TIMESTAMP('$startdatetime'),UNIX_TIMESTAMP('$enddatetime'),'$description','$alert','$tzid', '$location', '$host', '$status','$repeatscheduleid', '$beventid',".
									" '$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(),'$ownerid')";
			
			$result = mysqli_query($dbc,$queryinsert);
			if ($result !== TRUE) {
					header('HTTP/1.0 202 Can not create a single event', true, 202);
					exit;
			}   
		}
		
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		//$data->close();
		mysqli_close($dbc);
	}
	
	// create a new event and associated tasks
	// 05/14/2015  -- TBD
	Protected function insert($ownerid, $communityid, $event_parms) {
	    $task = array();
		
		$eventid = $event_parms['eventid'];
		$eventname = $event_parms['eventname'];
		$description = $event_parms['desp'];
		$startdatetime = $event_parms['startdatetime'];
		$enddatetime = $event_parms['enddatetime'];
		$alert = $event_parms['alert'];
		$tzid = $event_parms['tzid'];
		$location = $event_parms['location'];
		$host = $event_parms['host'];
		$beventid =  $event_parms['beventid'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
	   
	    try {
			// start a transaction
			mysqli_autocommit($dbc, FALSE);
			// Insert this event if no exists
			$queryinsert = "INSERT INTO event ".
								"(Event_Id,Event_Name,Community_Id,Start_DateTime,End_DateTime,Description,Alert,Tz_Id,Location, Host, BEvent_Id, Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
								" values('$eventid','$eventname','$communityid',UNIX_TIMESTAMP('$startdatetime'),UNIX_TIMESTAMP('$enddatetime'),'$description','$alert','$tzid', '$location', '$host', '$beventid',".
								" '$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(),'$ownerid')";
			
			$result = mysqli_query($dbc,$queryinsert);
			if ($result !== TRUE) {
				throw new Exception(mysqli_error($dbc));
			}
			
			// go ahead to insert tasks and assignment into event and taskassigned tables (API 1.5)
			if ($event_parms['task']) {
				// get the task
				foreach($event_parms['task'] as $task) {
					$taskid = $task['taskid'];
				    $desp =   $task['desp'];
					$taskname = $task['taskname'];
					$assignallowed = $task['assignallowed'];
					$assignedgroupid = $task['assignedgroupid'];
				  
					$queryinsert1 = "insert into task ".
							 "(Task_Id,Task_Name,Event_Id,Assign_Allowed,Assigned_Group, Description, Creator_Id, Created_Time,Last_Modified, Last_Modified_Id) ".
							 "values('$taskid','$taskname','$eventid','$assignallowed','$assignedgroupid','$desp','$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
							
					$result = mysqli_query($dbc,$queryinsert1);
					if ($result !== TRUE) {
						throw new Exception(mysqli_error($dbc));
					}
					
					foreach($task['assignment'] as $assigned_id) {
						// insert into the table "taskassigned"
						$queryinsert2 = "insert into taskassigned".
							"(Task_Id,User_Id,Event_Id,Confirm,Is_Deleted,Creator_Id, Created_Time,Last_Modified, Last_Modified_Id) ".
							 "values('$taskid','$assigned_id','$eventid','0','0','$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
							 
						$result = mysqli_query($dbc,$queryinsert2);
						if ($result !== TRUE) {
							throw new Exception(mysqli_error($dbc));
						}
					}
				
				}
				mysqli_commit($dbc);
			}
		} catch (Exception $e){
			mysqli_rollback($dbc);
			mysqli_autocommit($dbc, TRUE);
			header('HTTP/1.0 202 Can not create an event and its tasks', true, 202);
		}
		
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		//$data->close();
		mysqli_close($dbc);
	}
	
	// this is to insert one task 
	// 05/14/2015
	Protected function insert_task($eventid, $task) {
		$ownerid = $task['ownerid'];
		$taskid = $task['taskid'];
		$desp =   $task['desp'];
		$taskname = $task['taskname'];
		$assignallowed = $task['assignallowed'];
		$assignedgroupid = $task['assignedgroupid'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		$query = "SELECT Task_Id FROM task WHERE Task_Id = $taskid";
        $data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data) == 1) {
			header('HTTP/1.0 201 This task exists already', true, 201);
	    }
		else {	
			$queryinsert1 = "insert into task ".
							 "(Task_Id,Task_Name,Event_Id,Assign_Allowed,Assigned_Group, Description, Creator_Id, Created_Time,Last_Modified, Last_Modified_Id) ".
							 "values('$taskid','$taskname','$eventid','$assignallowed','$assignedgroupid','$desp','$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
							
			$result = mysqli_query($dbc,$queryinsert1);
			if ($result !== TRUE) {
				header('HTTP/1.0 202 Can not add a task', true, 202);
			}
		}	
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
        echo $data2;		
		mysqli_close($dbc);
	}
	
	// this is to insert a list of tasks 
	// 05/14/2015
	Protected function insert_tasks($ownerid, $eventid, $task_arr) {
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		try {
			// start a transaction
			mysqli_autocommit($dbc, FALSE);
			// go ahead to insert tasks and assignment into event and taskassigned tables (API 1.5)
			foreach($task_arr as $task) {
				$taskid = $task['taskid'];
				$desp =   $task['desp'];
				$taskname = $task['taskname'];
				$assignallowed = $task['assignallowed'];
				$assignedgroupid = $task['assignedgroupid'];
			  
				$queryinsert1 = "insert into task ".
						 "(Task_Id,Task_Name,Event_Id,Assign_Allowed,Assigned_Group, Description, Creator_Id, Created_Time,Last_Modified, Last_Modified_Id) ".
						 "values('$taskid','$taskname','$eventid','$assignallowed','$assignedgroupid','$desp','$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
						
				$result = mysqli_query($dbc,$queryinsert1);
				if ($result !== TRUE) {
					throw new Exception(mysqli_error($dbc));
				}
				
				foreach($task['assignment'] as $assigned_id) {
					// insert into the table "taskassigned"
					$queryinsert2 = "insert into taskassigned".
						"(Task_Id,User_Id,Event_Id,Confirm,Is_Deleted,Creator_Id, Created_Time,Last_Modified, Last_Modified_Id) ".
						 "values('$taskid','$assigned_id','$eventid','0','0','$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
						 
					$result = mysqli_query($dbc,$queryinsert2);
					if ($result !== TRUE) {
						throw new Exception(mysqli_error($dbc));
					}
				}
			}
			mysqli_commit($dbc);	
		} catch (Exception $e){
			mysqli_rollback($dbc);
			mysqli_autocommit($dbc, TRUE);
			header('HTTP/1.0 202 Can not create a task and its assignment', true, 202);
		}
		
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		//$data->close();
		mysqli_close($dbc);
	}
	
	// this is to update event only 
	// This API doesn't update associated task
	// 12/28/2015
	Protected function update($eventid, $ownerid, $event_parms) {	
	
		$tzid = $event_parms['tzid'];
		$eventname = $event_parms['eventname'];
		$desp = $event_parms['desp'];
		$alert = $event_parms['alert'];
		$location = $event_parms['location'];
		$host = $event_parms['host'];
		$startdatetime = $event_parms['startdatetime'];
		$enddatetime = $event_parms['enddatetime'];
		$status = $event_parms['status'];
		$repeatscheduleid = $event_parms['repeatscheduleid'];
		$beventid = $event_parms['beventid'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM event WHERE Event_Id = '$eventid'";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
        if (mysqli_num_rows($data)==1) {
		    // event exists and go ahead to update it
			$queryupdate = "Update event set Tz_id = $tzid, Event_Name = '$eventname', Description = '$desp', Alert = $alert, ".
                           " Location = '$location', Host = '$host', Start_DateTime = UNIX_TIMESTAMP('$startdatetime'), End_DateTime = UNIX_TIMESTAMP('$enddatetime'),"	.
                           " Repeat_Schedule_Id = '$repeatscheduleid', Status = '$status', BEvent_id = $beventid ";					   
			$queryupdate = $queryupdate . ", Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = " . $ownerid . " Where Event_Id = " .$eventid .";";
		    
			try {
				$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			}
			catch (exception $e) {
				header('HTTP/1.0 204 updating event failed', true, 204);
				exit;
			}
			
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;		
		}
		else {
			header('HTTP/1.0 202 This event doesn\'t exist', true, 202);
		}
		
		$data->close();
		mysqli_close($dbc);
	}
	
	//
	//  This is to update the confirm status for a member
	//  http://[domain name]/schedules/1234/onduty/1111
	//
	Protected function update_confirm($eventid, $memberid, $schedule_parms) {
		$ownerid = $schedule_parms['ownerid'];
		$confirm = $schedule_parms['confirm'];
	    
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM onduty WHERE Member_id = '$memberid' and Is_Deleted = 0 and event_Id = '$eventid' ";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
        if (mysqli_num_rows($data)==1) {
			// first to check if the ownerid is equal to the corresponding memberid
			$query1 = "SELECT m.* FROM member m, user u where u.Email = m.Member_Email and u.User_ID = '$ownerid' and m.Member_id = '$memberid' ";
			$result = mysqli_query($dbc, $query1);
		
			if (mysqli_num_rows($result)==1) {
				$query2 = "Update onduty set confirm = '$confirm', last_Modified = UTC_TIMESTAMP(), last_modified_id = '$ownerid' ".
					" where Member_id = '$memberid' and event_Id = '$eventid'";
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
	  This is to delete the event
	**/
	Protected function pdelete($eventid, $ownerid) {
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT * FROM event WHERE Event_Id = '$eventid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This event doesn\'t exist and has been deleted', true, 201);
	    }
		else {
			// Event exists and go ahead to update it
			// two steps commit
			mysqli_autocommit($dbc, FALSE);
			// update this event by setting the flag Is_Deleted to 1
			$queryupdate = "update event set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
						" where Event_Id = '$eventid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 This Event can\'t be deleted', true, 202);
				exit;
			}
			else {
				// first to delete the existing relationship
				$querydelete = "update onduty set ".
				               " Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
							   " WHERE Event_Id = '$eventid'";
				
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
	    This method is for retrieving all events associated with the device owner assigned to it
		This API is deprecated because sharedmember concept is used in 1.2.0
	**/
	Protected function pgetlastupdate($ownerid, $lastupdatetime) {
		$return_arr = array();
		$delschedule_arr = array();
		$schedules_arr = array();
		$members_att = array();
		
		// get the list of eventid and lastmodified
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
							$delschedule_arr[$j] = $row['eventid'];
							$j++;
						}
						else {
						   $one_arr = array();
						   $one_arr['eventid'] = $row['eventid'];
						   $one_arr['communityid'] = $row['communityid'];
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
						$two_arr['eventid'] = $row['eventid'];
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
			$eventid = $svalue['eventid'];
			
			// get the list of memberid associated with the Event ID
			$members_str = '';
			$i = 0;
			foreach ($members_att as $mvalue) {
			  if ($mvalue['eventid'] == $eventid) {
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
			//insert members associated with the Event into the schedules_arr TBD
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
	
	
	// this is to support patch update 
	// 09/10/2015 Dongling API 1.5
	Protected function partial_update_event($eventid, $event_arr) {
		// table for event
		$eventa = array(
			'eventname' => 'Event_Name',
			'desp' => 'Description',
			'startdatetime' => 'Start_DateTime',
			'enddatetime' => 'End_DateTime',
			'tzid' => 'Tz_Id',
			'alert' => 'Alert',
			'location' => 'Location',
			'host' => 'Host',
			'beventid' => 'BEvent_id',
			'status' => 'Status',
			'repeatscheduleid' => 'Repeat_Schedule_Id'
		);
	
		$ownerid = $event_arr['ownerid'];
        $data = array();		
        foreach($event_arr as $key => $value) {
			foreach($eventa as $keya => $valuea) {
				if ($key == $keya) {
					$data[$valuea] = $value;
					break;
				}
			}
		}
		$where = "Event_Id = '$eventid' ";
		$table = "event";
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		try {
			// update the task table
			$query2 = build_sql_update($table, $data, $where, $ownerid);
			$result1 = mysqli_query($dbc, $query2);
			
		}
		catch (Exception $e) {
			header('HTTP/1.0 201 Update failed', true, 202);		
		}
		
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		mysqli_close($dbc);
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

	// This is the API 
	// 1. to create a new event and possible tasks in the server  [04/23/2015]
	//    POST http://[domain_name]/event
	// 2. to create a task under an event
	//    POST http://[domain_name]/event/1234/task
	// 3  to create multiple tasks and assignments
	//    POST http://[domain_name]/event/1234/taskm
    public function post($request) {
		$parameters1 = array();
		header('Content-Type: application/json; charset=utf8');
		
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "event") {
			// process a single event
			$this->insert_single_event($request->body_parameters);
		}  
		/** this is a URL to insert a task and associated tasks
		/***
		if ($lastElement == "event") {
			foreach($request->body_parameters['event'] as $param_name => $param_value) {
				$parameters1[$param_name] = $param_value;				
			}
			// ADD tasks, process them to insert into the event table and task and assign task
			$this->insert($request->body_parameters['ownerid'], $request->body_parameters['communityid'], $parameters1);
		}  
		// to add multiple tasks
		else if ($lastElement == "tasks") {
			$eventid  = $request->url_elements[count($request->url_elements)-2];
			// ADD tasks, process them to insert into the task table and assign task
			$this->insert_tasks($request->body_parameters['ownerid'],$eventid , $request->body_parameters['task']);
		} if ($lastElement == "task")  {
			$eventid  = $request->url_elements[count($request->url_elements)-2];
			$this->insert_task($eventid, $request->body_parameters);
		} 
		***/
    }
	
	// update an event 
	// 1. PUT http://[api_domain_name]/event/1234
	// 05/25/2015 API 1.5
	public function put($request) {
		$parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$last2Element = $request->url_elements[count($request->url_elements)-2];	
		if ($last2Element == "event") {
			// update an event
			$ownerid = $request->body_parameters['ownerid'];
			$eventid = end($request->url_elements);
			reset($request->url_elements);
			$this->update($eventid, $ownerid, $request->body_parameters);
	    }
    }
	
	/**
	   There is a body element "ownerid" in the DELETE HTTP Method 
	**/
	public function delete($request) {
	     // logic to handle an HTTP DELETE request goes here
		header('Content-Type: application/json; charset=utf8');
		$eventid = end($request->url_elements);
		reset($request->url_elements);
		$ownerid = $request->parameters['ownerid'];
		$this->pdelete($eventid, $ownerid);
    }
	
	
	// partially update event
	// 09/10/2015
	// 1. to update one or more attributes  [12/24/2015]
	//    PATCH http://[domain_name]/event/222112
	public function patch($request) {
		//$parameters1 = array();
	
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		reset($request->url_elements);
		
		if ($last2Element == "event") {
			$event_arr = $request->body_parameters;
			$eventid = $lastElement;
			$this->partial_update_event($eventid, $event_arr);
		}	
	}
	


}
?>