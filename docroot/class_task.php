<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');

class Task Extends Resource
{
	
    public function __construct($request) {
        parent::__construct($request);
		
    }
	
	protected $lastid;
	
	// create a new event TBD
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
	
	// this is to update tasks
	// There are multiple tasks with the same task ID in the "task" table but with different schedule IDs
	// It would update all tasks with the taskid supplied
	// 05/25/2015 Dongling API 1.5
	Protected function update_task($taskid, $task_arr) {
		
		$ownerid = $task_arr['ownerid'];
		$taskname = $task_arr['taskname'];
		$desp = $task_arr['desp'];
		$repeating = $task_arr['repeating'];
		$assignallowed = $task_arr['assignallowed'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		try {
			// update the task table
			$query2 = "Update task set Task_Name = '$taskname', Description = '$desp', Repeating = '$repeating', Assign_Allowed = '$assignallowed', last_Modified = UTC_TIMESTAMP(), last_modified_id = '$ownerid' ".
						" where Task_id = '$taskid' ";
			$result1 = mysqli_query($dbc, $query2);
			
		}
		catch (Exception $e) {
			header('HTTP/1.0 201 Update failed', true, 202);		
		}
		
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		mysqli_close($dbc);
	}
	
	// this is to support patch update 
	// There are multiple tasks with the same task ID in the "task" table but with different schedule IDs
	// It would update all tasks with the taskid supplied
	// 05/25/2015 Dongling API 1.5
	Protected function partial_update_task($taskid, $task_arr) {
		// table for task
		$taska = array(
			'taskname' => 'Task_Name',
			'desp' => 'Description',
			'repeating' => 'Repeating',
			'assignallowed' => 'Assign_Allowed',
			'repeating' => 'Repeating'
		);
	
		$ownerid = $task_arr['ownerid'];
	
        $data = array();		
        foreach($task_arr as $key => $value) {
			foreach($taska as $keya => $valuea) {
				if ($key == $keya) {
					$data[$valuea] = $value;
					break;
				}
			}
		}
		$where = "Task_Id = '$taskid' ";
		$table = "task";
		
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
	
	// This is to add participant to task
	// 07/24/2015
	Protected function add_assignment($taskid, $ownerid, $userid, $eventid){  
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		try {
			$query = "SELECT Is_Deleted isdeleted FROM taskassigned WHERE Task_Id = '$taskid' and User_Id = '$userid' and Schedule_Id = '$eventid' ";
			$data = mysqli_query($dbc, $query);
			$result = mysqli_fetch_assoc($data);
			if (mysqli_num_rows($data) == 1) {
				// One is deleted
				$queryinsert1 = "update taskassigned ".
								" SET Is_Deleted = 0, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
								" Where Task_Id = '$taskid' and User_Id = '$userid' and Schedule_Id = '$eventid' ";
			} else {
				//insert each user into the table taskassignment
				$queryinsert1 = "INSERT INTO taskassigned ".
								"(Task_Id,User_Id,Schedule_Id, Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
								" values('$taskid','$userid', '$eventid', '$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(), '$ownerid')";
			}	
			$result = mysqli_query($dbc,$queryinsert1); 
			if ($result !== TRUE) {
					throw new Exception(mysqli_error($dbc));
			}	
		}
		catch (Exception $e) {
				header('HTTP/1.0 202 Can not add participant to the task', true, 201);
		}
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		mysqli_close($dbc);
		
    }	
	
	// To create a task
	// 08/30/2015 
	Protected function insert_task($eventid, $task) {
		$ownerid = $task['ownerid'];
		$taskid = $task['taskid'];
		$desp =   $task['desp'];
		$taskname = $task['taskname'];
		$repeating = $task['repeating'];
		$assignallowed = $task['assignallowed'];
		$assignedgroupid = $task['assignedgroupid'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		$query = "SELECT Task_Id FROM task WHERE Task_Id = $taskid";
        $data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data) == 1) {
			header('HTTP/1.0 201 This task exists already', true, 201);
			exit;
	    }
		else {	
			$queryinsert1 = "insert into task ".
							 "(Task_Id,Task_Name,Event_Id,Repeating,Assign_Allowed,Assigned_Group, Description, Creator_Id, Created_Time,Last_Modified, Last_Modified_Id) ".
							 "values('$taskid','$taskname','$eventid', '$repeating','$assignallowed','$assignedgroupid','$desp','$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
						
			$result = mysqli_query($dbc,$queryinsert1);
			if ($result !== TRUE) {
				header('HTTP/1.0 202 Can not add a task', true, 202);
				exit;
			}
		}	
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
        echo $data2;		
		mysqli_close($dbc);
	}
	
	//
	//  This is to update the assignment, add or delete participants
	//  05/23/2015 Dongling API 1.5
	//
	Protected function update_assignment($taskid, $eventid, $ownerid, $add_arr, $delete_arr) {    
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		try {
			mysqli_autocommit($dbc, FALSE);
		
			if ($add_arr != null) {
				foreach($add_arr as $user) {
					$query = "SELECT Is_Deleted isdeleted FROM taskassigned WHERE Task_Id = '$taskid' and User_Id = '$user' and Schedule_Id = '$eventid' ";
					$data = mysqli_query($dbc, $query);
					$result = mysqli_fetch_assoc($data);
					if (mysqli_num_rows($data) == 1 and $result['isdeleted'] == 1) {
						// One is deleted
						$queryinsert1 = "update taskassigned ".
										" SET Is_Deleted = 0, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
										" Where Task_Id = '$taskid' and User_Id = '$user' and Schedule_Id = '$eventid' ";
					} else {
						//insert each user into the table taskassignment
						$queryinsert1 = "INSERT INTO taskassigned ".
										"(Task_Id,User_Id,Schedule_Id, Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
										" values('$taskid','$user', '$eventid', '$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(), '$ownerid')";
                    }	
					$result = mysqli_query($dbc,$queryinsert1); 
					if ($result !== TRUE) {
							throw new Exception(mysqli_error($dbc));
					}	
				}
			};
			
			if ($delete_arr != null) {
				foreach($delete_arr as $user) {
					$query = "SELECT Is_Deleted isdeleted FROM taskassigned WHERE Task_Id = '$taskid' and User_Id = '$user' and Schedule_Id = '$eventid' ";
					$data = mysqli_query($dbc, $query);
					$result = mysqli_fetch_assoc($data);
					if (mysqli_num_rows($data) == 1 and $result['isdeleted'] == 0) {
						// One is deleted
						$queryinsert2 = "UPDATE taskassigned SET Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
										" WHERE Task_Id = '$taskid' and User_Id = '$user' and Schedule_Id = '$eventid' ";
					 
						$result = mysqli_query($dbc,$queryinsert2);
						if ($result !== TRUE) {
								throw new Exception(mysqli_error($dbc));
						}
					}
				}
			};
			// commit to get everything done
			mysqli_commit($dbc);
		}
		catch (Exception $e) {
				mysqli_rollback($dbc);
				mysqli_autocommit($dbc, TRUE);
				header('HTTP/1.0 202 Can not update assignments', true, 202);
		}
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		//$data->close();
		mysqli_close($dbc);
	}
	
	//
	//  This is to update the assignmentpool.
	//  When adding a participant group, it needs to use "Gxxx"; when adding a participant, it needs to use "xxx".
	//  05/23/2015 Dongling API 1.5
	//
	Protected function update_assignmentpool($taskid, $ownerid, $add_arr, $delete_arr) {    
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		try {
			mysqli_autocommit($dbc, FALSE);
			$query = "";
			$queryinsert1 = "";
			$g = 0;
			if ($add_arr != null) {
				
				foreach($add_arr as $user) {
			        $g = 0;
					if (substr($user, 0,1) == "G") {
						$user = substr($user, 1);
						$g = 1;
			        }
					
					if ($g == 0) 
						$query = "SELECT Is_Deleted isdeleted FROM assignmentpool WHERE Task_Id = '$taskid' and User_Id = '$user'";
					else
						$query = "SELECT Is_Deleted isdeleted FROM assignmentpool WHERE Task_Id = '$taskid' and PGroup_Id = '$user'";
					
					$data = mysqli_query($dbc, $query);
					$result = mysqli_fetch_assoc($data);
					if (mysqli_num_rows($data) == 1 and $result['isdeleted'] == 1) {
						// One is deleted
						if ($g == 0) {
							$queryinsert1 = "update assignmentpool ".
											" SET Is_Deleted = 0, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
											" Where Task_Id = '$taskid' and User_Id = '$user' and Schedule_Id = '$eventid' ";
						} 
						else {
							$queryinsert1 = "update assignmentpool ".
											" SET Is_Deleted = 0, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
											" Where Task_Id = '$taskid' and PGroup_Id = '$user' and Schedule_Id = '$eventid' ";
						}
					} else {
						//insert each user into the table assignmentpool
						if ($g == 0) {
							$queryinsert1 = "INSERT INTO assignmentpool ".
										"(Task_Id,User_Id, Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
										" values('$taskid','$user', '$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(), '$ownerid')";	
						}
						else
							$queryinsert1 = "INSERT INTO assignmentpool ".
											"(Task_Id,PGroup_Id, Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
											" values('$taskid','$user', '$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(), '$ownerid')";
					}	
					$result = mysqli_query($dbc,$queryinsert1); 
					if ($result !== TRUE) {
							throw new Exception(mysqli_error($dbc));
					}	
				}
			};
			
			if ($delete_arr != null) {
				foreach($delete_arr as $user) {
					$g = 0;
					if (substr($user, 0,1) == "G") {
						$user = substr($user, 1);
						$g = 1;
			        }
				
					if ($g == 0) 
						$query = "SELECT Is_Deleted isdeleted FROM assignmentpool WHERE Task_Id = '$taskid' and User_Id = '$user'";
					else
						$query = "SELECT Is_Deleted isdeleted FROM assignmentpool WHERE Task_Id = '$taskid' and PGroup_Id = '$user'";
					
					$data = mysqli_query($dbc, $query);
					$result = mysqli_fetch_assoc($data);
					if (mysqli_num_rows($data) == 1 and $result['isdeleted'] == 0) {
						// One is deleted
						if ($g == 0)
							$queryinsert2 = "UPDATE assignmentpool SET Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
										" WHERE Task_Id = '$taskid' and User_Id = '$user' ";
						else
							$queryinsert2 = "UPDATE assignmentpool SET Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
										" WHERE Task_Id = '$taskid' and PGroup_Id = '$user' ";
						
						$result = mysqli_query($dbc,$queryinsert2);
						if ($result !== TRUE) {
							throw new Exception(mysqli_error($dbc));
						}	
					} 	
				}
			};
			// commit to get everything done
			mysqli_commit($dbc);
		}
		catch (Exception $e) {
				mysqli_rollback($dbc);
				mysqli_autocommit($dbc, TRUE);
				header('HTTP/1.0 202 Can not update assignments', true, 202);
		}
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		//$data->close();
		mysqli_close($dbc);
	}
	
	/**
	  This is to delete the task
	  It can delete one time task or repeating task depending on task Id
	  TBD: Can't handle the case in which only deleting one task associated with one event when that task is a repeating task
	**/
	Protected function pdelete($taskid, $ownerid) {
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT * FROM task WHERE Task_Id = '$taskid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This task doesn\'t exist and has been deleted', true, 201);
	    }
		else {
			// task exists and go ahead to update it
			// two steps commit
			mysqli_autocommit($dbc, FALSE);
			// update this task by setting the flag Is_Deleted to 1
			$queryupdate = "update task set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
						" where Task_Id = '$taskid'";
					
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 This task can\'t be deleted', true, 202);
				exit;
			}
			else {
				// first to delete the taskhelper associated with that task
				$querydelete = "update taskhelper set ".
				               " Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
							   " WHERE Task_Id = '$taskid'";
		
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
	    This method is to retrieve assignment pool
		05/20/2015 --- Client needs to make this call to get the assignment pool info after retrieving event info.
	**/
	Protected function pgetlastupdate_ap($taskid, $ownerid, $lastupdatetime) {
		$return_arr = array();
		$delpgroup_arr = array();
		$delparticipant_arr = array();
		$pgroup_arr = array();
		$gparticipant_arr = array();
		$participant_arr = array();
		
		$mysql = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		// call a stored procedure to get the schedules to be returned to caller
		if ($mysql->multi_query("CALL getAssignmentPoolByLastUpdate('$taskid', '$lastupdatetime')")) {
          $h = 0;
		  //loop through two result sets
          do {
            if ($result = $mysql->use_result())
            {
			    $i = 0;
				$j = 0;
				$k = 0;
				$l = 0;
				$m = 0;
                //Loop the three result sets. First is Pgroup; second is the group member; third is individual participants
                while ($row = $result->fetch_array(MYSQLI_ASSOC))
                {
					// first result set -- participant group
                    if ($h == 0) {
					   // first resultset 
						$isdeleted = $row['pgisdeleted'];
						// if it's deleted, just add it to "delpgroup"
						if ($isdeleted == 1) {
							$delpgroup_arr[$j] = $row['pgroupid'];
							$j++;
						}
						else {
						   $one_arr = array();
						   $one_arr['pgroupid'] = $row['pgroupid'];
						   $one_arr['pgroupname'] = $row['pgroupname']; 
						   $one_arr['member'] = "";
						   $pgroup_arr[$i] = $one_arr;
						   $i++;			   
						}     
					}
					else if ($h == 1) {
					   // second resultset --- individual participants
						$two_arr = array();
						$two_arr['pgroupid'] = $row['pgroupid'];
						$two_arr['userid'] = $row['userid'];
						$two_arr['username'] = $row['username'];
						$two_arr['userprofile'] = $row['userprofile'];
						$gparticipant_arr[$k] = $two_arr;
						$k++;
					}
					else  {
					   // third resultset individual participants
					   // check if a participant is deleted
					   $isdeleted = $row['isdeleted'];
					   if ($isdeleted == 1) {
							$delparticipant_arr[$l] = $row['userud'];
							$l++;
					   }
					   else {
							$three_arr = array();
							$three_arr['userid'] = $row['userid'];
							$three_arr['username'] = $row['username'];
							$three_arr['userprofile'] = $row['userprofile'];
							$participant_arr[$m] = $three_arr;
							$m++;
					   }
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
        //Construct output json object
	    //$delschedule_str = array();
		//$delschedule_str = implode(",", $delpgroup_arr); // concat to the string seprating by 
        $pgroup_final = array();		
		foreach ($pgroup_arr as &$svalue) {
			$pgroupid = $svalue['pgroupid'];
			// get the list of memberid associated with the schedule ID
			$member_temp = array();
			$j = 0;
			foreach ($gparticipant_arr as $mvalue) {
				$member_temp_1 = array();
				if ($mvalue['pgroupid'] == $pgroupid) {
					$member_temp_1['userid'] = $mvalue['userid'];
					$member_temp_1['username'] = $mvalue['username'];
					$member_temp_1['userprofile'] = PROFILE_SERVER .$mvalue['userprofile'];
					$member_temp[$j] = $member_temp_1;
					$j++;
				}
			}
			//insert members associated with the schedule into the pgroup_arr TBD
			$svalue['member'] = $member_temp;
		}
		unset($svalue);
		
	    //$return_arr['deletedschedules'] = $delschedule_str;
		//$return_arr['deletedschedules'] = $delpgroup_arr;
		$return_arr['apgroup'] = $pgroup_arr;
		$return_arr['apparticipant'] = $participant_arr;
         
		$data2 = json_encode($return_arr);
		echo $data2;
		
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid,$data2);
     
		mysqli_close($mysql);	
	}

	// GET method here is to handle the case to retrieve all participants or participant groups assigned to a task
	//  1. http://[REST_SERVER]/task/1234/assignmentpool?ownerid=2222&lastupdatetime=121333000

	public function get($request) {
        $ownerid = $request->parameters['ownerid'];
		$lastupdatetime = urldecode($request->parameters['lastupdatetime']);
	   
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "assignmentpool") {
			$taskid  = $request->url_elements[count($request->url_elements)-2];
			$this->pgetlastupdate_ap($taskid, $ownerid, $lastupdatetime);
		}
	
    }

	// Assign a participant to the task
	// 1. POST http://[api_domain_name]/task/1234/assignment
	// 2. POST http://[api_domain_name]/task
	// 03/09/2016
    public function post($request) {
		$parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		reset($request->url_elements);
		
		if ($lastElement == "assignment") {
			$ownerid = $request->body_parameters['ownerid'];
			$userid = $request->body_parameters['id'];
			$eventid = $request->body_parameters['eventid'];
			
			$taskid  = $request->url_elements[count($request->url_elements)-2];
			$this->add_assignment($taskid, $ownerid, $userid, $eventid);
		} 
		else if ($lastElement == "task") {
			// a flat property to create a task
			$eventid = $request->body_parameters['eventid'];
			$this->insert_task($eventid, $request->body_parameters);	
		}
    }
	
	// partially update task
	// 08/26/2015
	public function patch($request) {
		$parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		reset($request->url_elements);
		
		if ($last2Element == "task") {
			$task_arr = $request->body_parameters;
			$taskid = $lastElement;
			$this->partial_update_task($taskid, $task_arr);
		}	
	}
	
	// update the assignment associated with a task
	// 1. PUT http://[api_domain_name]/task/1234/assignment
	// 2. PUT http://[api_domain_name]/task/1234
	// 3. PUT http://[api_domain_name]/task/1234/assignmentpool
	// 05/20/2015 Dongling API 1.5
	public function put($request) {
		$parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		reset($request->url_elements);
		
		if ($lastElement == "assignment") {
			/*** This is to add more than participant or delete more than participant
			$ownerid = $request->body_parameters['ownerid'];
			$eventid = $request->body_parameters['eventid'];
			$add_arr = $request->body_parameters['add'];
			$delete_arr = $request->body_parameters['delete'];
			
			$taskid  = $request->url_elements[count($request->url_elements)-2];
			$this->update_assignment($taskid, $eventid, $ownerid, $add_arr, $delete_arr);
			****/
			$ownerid = $request->body_parameters['ownerid'];
			$userid = $request->body_parameters['id'];
			$eventid = $request->body_parameters['eventid'];
			
			$taskid  = $request->url_elements[count($request->url_elements)-2];
			$this->add_assignment($taskid, $ownerid, $userid, $eventid);
		}
		else if ($lastElement == "assignmentpool") {
			$ownerid = $request->body_parameters['ownerid'];
			$add_arr = $request->body_parameters['add'];
			$delete_arr = $request->body_parameters['delete'];
			
			$taskid  = $request->url_elements[count($request->url_elements)-2];
			$this->update_assignmentpool($taskid, $ownerid, $add_arr, $delete_arr);
		}
		else if ($last2Element == "task") {
			$task_arr = $request->body_parameters;
			$taskid = $lastElement;
			
			$this->update_task($taskid, $task_arr);
		}
    }
	
	// Delete a task or remove a participant from a task
	// 1. DELETE http://[api_domain_name]/task/1234/assignment
	// 2. Delete http://[api_domain_name]/task/1234
	// 07/24/2015 Dongling API 1.5   TBD
	public function delete($request) {
		
		$taskid = end($request->url_elements);
		reset($request->url_elements);
		//$ownerid = $request->body_parameters['ownerid'];
		$ownerid = $request->parameters['ownerid'];
		$this->pdelete($taskid, $ownerid);
    }
}
?>