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
	
	// create a new schedule and associated tasks
	// 05/14/2015
	Protected function insert_base_event($event_parms) {
		
		$ownerid = $event_parms['ownerid'];
		$reventid = $event_parms['reventid'];
		$eventname = $event_parms['eventname'];
		$starttime = $event_parms['starttime'];
		$endtime = $event_parms['endtime'];
		//$alert = $event_parms['alert'];
		$tzid = $event_parms['tzid'];
		$location = $event_parms['location'];
		$host = $event_parms['host'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
	   
	   	$query = "SELECT * FROM baseschedule WHERE REvent_Id = '$reventid'";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
        if (mysqli_num_rows($data) == 0) {
			try {
				// start a transaction
				$queryinsert = "INSERT INTO baseschedule ".
									"(REvent_Id,REvent_Name,REvent_StartTime,REvent_EndTime,REvent_Location,REvent_Host, REvent_Tz_Id, Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
									" values('$reventid','$eventname','$starttime','$endtime','$location', '$host','$tzid',".
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
	Protected function pgetBaseEvent($reventid) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT REvent_Name name, REvent_StartTime starttime, REvent_EndTime endtime, REvent_Location location, ".
		         " REvent_Host host, REvent_Tz_Id tzid ".
				 " FROM baseschedule WHERE REvent_Id = '$reventid' and Is_Deleted = 0";
      
		$data = mysqli_query($dbc, $query) or die(mysqli_error());
		if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This base event doesn\'t exist and has been deleted', true, 201);
	    }
		else {
		    $row = mysqli_fetch_array($data);	
			$one_arr = array();
				  
			$one_arr['name'] = $row['name'];
			$one_arr['starttime'] = $row['starttime'];
			$one_arr['endtime'] = $row['endtime'];
			$one_arr['location'] = $row['location'];
			$one_arr['host'] = $row['host'];
			$one_arr['tzid'] = $row['tzid'];
					
			$data2 = json_encode($one_arr);
			echo $data2;
		}
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
	

	// this is to support patch update on base event
	// 09/25/2015 Dongling API 1.5
	// 
	Protected function partial_update_event($reventid, $revent_arr) {
		// table for event
		$reventa = array(
			'eventname' => 'REvent_Name',
			'starttime' => 'REvent_StartTime',
			'endtime' => 'REvent_EndTime',
			'tzid' => 'REvent_Tz_Id',
			'location' => 'REvent_Location',
			'host' => 'REvent_Host'
		);
	
		$ownerid = $revent_arr['ownerid'];
        $data = array();		
        foreach($revent_arr as $key => $value) {
			foreach($reventa as $keya => $valuea) {
				if ($key == $keya) {
					$data[$valuea] = $value;
					break;
				}
			}
		}
		
		$where = "REvent_Id = '$reventid' ";
		$table = "baseschedule";
		
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
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
        $reventid = $lastElement;
		header('Content-Type: application/json; charset=utf8');
		
		$this->pgetBaseEvent($reventid);
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
		if ($last2Element == "event") {
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
		$scheduleid = end($request->url_elements);
		reset($request->url_elements);
		$ownerid = $request->parameters['ownerid'];
		$this->pdelete($scheduleid, $ownerid);
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
			$revent_arr = $request->body_parameters;
			$reventid = $lastElement;
			$this->partial_update_event($reventid, $revent_arr);
		}	
	}
	
}
?>