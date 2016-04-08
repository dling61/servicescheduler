<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');

class BaseEvent Extends Resource
{
	
    public function __construct($request) {
        parent::__construct($request);
		
    }
	protected $lastid;
	
	// create a new baseevent and associated tasks
	// 05/14/2015
	Protected function insert_base_event($event_parms) {
		
		$ownerid = $event_parms['ownerid'];
		$beventid = $event_parms['beventid'];
		$eventname = $event_parms['beventname'];
		$starttime = $event_parms['bstarttime'];
		$endtime = $event_parms['bendtime'];
		//$alert = $event_parms['balert'];
		$tzid = $event_parms['btzid'];
		$location = $event_parms['blocation'];
		$host = $event_parms['bhost'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
	   
	   	$query = "SELECT * FROM baseevent WHERE BEvent_Id = '$beventid'";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
        if (mysqli_num_rows($data) == 0) {
			try {
				// start a transaction
				$queryinsert = "INSERT INTO baseevent ".
									"(BEvent_Id,BEvent_Name,BEvent_StartTime,BEvent_EndTime,BEvent_Location,BEvent_Host, BEvent_Tz_Id, Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
									" values('$beventid','$eventname','$starttime','$endtime','$location', '$host','$tzid',".
									" '$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(),'$ownerid')";
		
				$result = mysqli_query($dbc,$queryinsert);
				if ($result !== TRUE) {
					throw new Exception(mysqli_error($dbc));
				}
			} catch (Exception $e){
				echo $e->getMessage();
				header('HTTP/1.0 202 Can not create an event and its tasks', true, 202);
			}
				
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;	
		}
		else {
				header('HTTP/1.0 203 Base event already exists', true, 203);
		}
		
		mysqli_close($dbc);
	}
	
	/*
	   This is to get a particular event
	 */
	Protected function pgetBaseEvent($beventid) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT BEvent_id beventid, BEvent_Name name, BEvent_StartTime starttime, BEvent_EndTime endtime, BEvent_Location location, ".
		         " BEvent_Host host, BEvent_Tz_Id tzid ".
				 " FROM baseevent WHERE BEvent_Id = '$beventid' and Is_Deleted = 0";
      
		$data = mysqli_query($dbc, $query) or die(mysqli_error());
		if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This base event doesn\'t exist and has been deleted', true, 201);
	    }
		else {
		    $row = mysqli_fetch_array($data);	
			$one_arr = array();
			$one_arr['beventid'] = $row['beventid'];  
			$one_arr['beventname'] = $row['name'];
			$one_arr['bstarttime'] = $row['starttime'];
			$one_arr['bendtime'] = $row['endtime'];
			$one_arr['blocation'] = $row['location'];
			$one_arr['bhost'] = $row['host'];
			$one_arr['btzid'] = $row['tzid'];
					
			$data2 = json_encode($one_arr);
			echo $data2;
		}
		$data->close();
		mysqli_close($dbc);
	}
	
	
	// This is for get event API based on a participant base event
	// First result is for events; Second result is for tasks; Third result is for assignment
	// This is for API 1.5  04/04/2016
	Protected function pgetlastupdate_event($baseeventid, $lastupdatetime) {
	  
		$return_arr = array();
		$event_arr = array();
		$task_arr = array();
		$assignment_arr = array();
		
		$mysql = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		// call a stored procedure 
		if ($mysql->multi_query("CALL getEventFromBaseEventByLastUpdate('$baseeventid', '$lastupdatetime')")) {
	      // this is for the number of resultset 
          $h = 0;
		  //loop through three resultsets
          do {
            if ($result = $mysql->use_result())
            {
			    $i = 0;
				$j = 0;
				$k = 0;
				$l = 0;
				$m = 0;
                //Loop the two result sets, reading it into an array
                while ($row = $result->fetch_array(MYSQLI_ASSOC))
                {
                    if ($h == 0) {
					 
					   $one_arr = array();
					   $one_arr['eventid'] = $row['eventid'];
					   $one_arr['eventname'] = $row['eventname'];
					   $one_arr['desp'] = $row['description'];
					   $one_arr['startdatetime'] = $row['startdatetime'];
					   $one_arr['enddatetime'] = $row['enddatetime'];
					   $one_arr['alert'] = $row['alert'];
					   $one_arr['tzid'] = $row['tzid'];
					   $one_arr['location'] = $row['location'];
					   $one_arr['host']  = $row['host'];
					   $one_arr['status']  = $row['status'];
					   $one_arr['beventid'] = $row['beventid'];
					   $one_arr['task'] = "";
					   $event_arr[$i] = $one_arr;
					   $i++;			   
					}
					else if ($h == 1){
					   // second result set for task 
						$two_arr = array();
						$two_arr['taskid'] = $row['taskid'];
						$two_arr['eventid'] = $row['eventid'];
						$two_arr['taskname'] = $row['taskname'];
						$two_arr['beventid'] = $row['beventid'];
						$two_arr['desp'] = $row['description'];
						$two_arr['assignallowed'] = $row['assignallowed'];
						//$two_arr['assignedgroup'] = $row['assignedgroup'];
					    $two_arr['taskhelper'] = "";
						$task_arr[$k] = $two_arr;
						$k++;
					}
					else if ($h == 2){
						// third result set for taskhelper
						$third_arr = array();
						$third_arr['taskhelperid'] = $row['taskhelperid'];
						$third_arr['taskid'] = $row['taskid'];
						$third_arr['eventid'] = $row['eventid'];
						$third_arr['userid'] = $row['userid'];
						$third_arr['username'] = $row['username'];
						$third_arr['userprofile'] = PROFILE_SERVER .$row['userprofile'];
						$third_arr['status'] = $row['status'];
					    $assignment_arr[$l] = $third_arr;
						$l++;
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
		
		mysqli_close($mysql);	
		$x = 0;
		// Construct a output jason object starting from event, task, and then assignment
		foreach ($event_arr as $svalue) {
			$eventid = $svalue['eventid'];
			$reventid = $svalue['beventid'];
			// go down to task level
			$task_temp = array();
			$i = 0;
			foreach ($task_arr as $mvalue) {
				if ($mvalue['eventid'] == $eventid) {
					// Add the task one by one
					$task_temp_1 = array();
					$task_id = $mvalue['taskid'];
					
					$task_temp_1['taskid'] = $mvalue['taskid'];
					$task_temp_1['taskname'] = $mvalue['taskname'];
					$task_temp_1['desp'] = $mvalue['desp'];
					$task_temp_1['beventid'] = $mvalue['beventid'];
					$task_temp_1['assignallowed'] = $mvalue['assignallowed'];
					//$task_temp_1['assignedgroup'] = $mvalue['assignedgroup'];
				
					$assignment_temp = array();
					$j = 0;
					// go down to the task assignment level
					foreach ($assignment_arr as $avalue) {
						// add eventid because the same taskid will be used for different events
						if ($avalue['taskid'] == $task_id and $avalue['eventid'] == $eventid) {
							$assignment_temp_1 = array();
							
							$assignment_temp_1['taskhelperid'] = $avalue['taskhelperid'];
							$assignment_temp_1['userid'] = $avalue['userid'];
							$assignment_temp_1['username'] = $avalue['username'];
							$assignment_temp_1['userprofile'] = $avalue['userprofile'];
							$assignment_temp_1['status'] = $avalue['status'];
							
							$assignment_temp[$j] = $assignment_temp_1;
							$j++;
						}
					}
					$task_temp_1['taskhelper'] = $assignment_temp;
					//add tasks
					$task_temp[$i] = $task_temp_1;
					$i++;
				}
			}
			//insert task associated with the event into the event_arr TBD
			$svalue['task'] = $task_temp;
			
			// insert into the final array
			$return_arr[$x] = $svalue;
			$x++;	
		} // end of event loop
		unset($svalue);
		
		$data2 = json_encode($return_arr);
		echo $data2;
		
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid,$data2);
	}
	
	/**
	  This is to delete the base event
	**/
	Protected function pdelete($beventid, $ownerid) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT * FROM baseevent WHERE BEvent_Id = '$beventid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
	
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This base event doesn\'t exist and has been deleted', true, 201);
	    }
		else {
			// Base Event exists and go ahead to update it
			// update this schedule by setting the flag Is_Deleted to 1
			$queryupdate = "update baseevent set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
						" where BEvent_Id = '$beventid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 This baseevent can\'t be deleted', true, 202);
				exit;
			}
		}
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		
		$data->close();
		mysqli_close($dbc);
	}
	

	// this is to support patch update on base event
	// 09/25/2015 Dongling API 1.5
	// 
	Protected function partial_update_event($beventid, $bevent_arr) {
		// table for event
		$reventa = array(
			'beventname' => 'BEvent_Name',
			'bstarttime' => 'BEvent_StartTime',
			'bendtime' => 'BEvent_EndTime',
			'btzid' => 'BEvent_Tz_Id',
			'blocation' => 'BEvent_Location',
			'bhost' => 'BEvent_Host'
		);
	
		$ownerid = $bevent_arr['ownerid'];
        $data = array();		
        foreach($bevent_arr as $key => $value) {
			foreach($reventa as $keya => $valuea) {
				if ($key == $keya) {
					$data[$valuea] = $value;
					break;
				}
			}
		}
		
		$where = "BEvent_Id = '$beventid' ";
		$table = "baseevent";
		
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
	
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
       
		
		if ($lastElement == "event") {
			// get the list of events under a base event
			$baseeventif  = $request->url_elements[count($request->url_elements)-2];
			$this->pgetlastupdate_event($baseeventif, $lastupdatetime);
		} 
		else {
			$beventid = $lastElement;
			$this->pgetBaseEvent($beventid);
		}	
    }

	// This is the API for creating a base event for repeating events
	//    POST http://[domain_name]/baseevent
    public function post($request) {
		$parameters1 = array();
		header('Content-Type: application/json; charset=utf8');
		
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "baseevent") {
			// process a single base event
			$this->insert_base_event($request->body_parameters);
		}  
    }
	
	// update an event 
	// 1. PUT http://[api_domain_name]/baseevent/1234
	// 05/25/2015 API 1.5
	public function put($request) {
		$parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$last2Element = $request->url_elements[count($request->url_elements)-2];	
		if ($last2Element == "baseevent") {
			if ($request->body_parameters['event']) {
				foreach($request->body_parameters['event'] as $param_name => $param_value) {
								$parameters1[$param_name] = $param_value;
				}
			}
			// update an event
			$eventid = end($request->url_elements);
			reset($request->url_elements);
			$this->update($eventid,$request->body_parameters['ownerid'], $parameters1);
	    }
    }
	
	/**
	   There is a body element "ownerid" in the DELETE HTTP Method 
	**/
	public function delete($request) {
	     // logic to handle an HTTP DELETE request goes here
		header('Content-Type: application/json; charset=utf8');
		$beventid = end($request->url_elements);
		reset($request->url_elements);
		$ownerid = $request->parameters['ownerid'];
		$this->pdelete($beventid, $ownerid);
    }
	
	
	// partially update event
	// 09/10/2015
	// Only change some information about base event
	public function patch($request) {
	
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		reset($request->url_elements);
		
		if ($last2Element == "baseevent") {
			$bevent_arr = $request->body_parameters;
			$beventid = $lastElement;
			$this->partial_update_event($beventid, $bevent_arr);
		}	
	}
	
}
?>