<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');

class RepeatSchedule Extends Resource
{
	
    public function __construct($request) {
        parent::__construct($request);
		
    }
	protected $lastid;
	
	// create a new repeat schedule
	// 02/25/2016
	Protected function insert_repeat_schedule($schedule_parms) {
		
		$ownerid = $schedule_parms['ownerid'];
		$rscheduleid = $schedule_parms['rscheduleid'];
		$repeatinterval = $schedule_parms['repeatinterval'];
		$fromdate = $schedule_parms['fromdate'];
		$todate = $schedule_parms['todate'];
		$beventid = $schedule_parms['beventid'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
	   
	   	$query = "SELECT * FROM repeatschedule WHERE RSchedule_Id = '$rscheduleid'";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
        if (mysqli_num_rows($data) == 0) {
			try {
				// start a transaction
				$queryinsert = "INSERT INTO repeatschedule ".
									"(RSchedule_Id,BEvent_Id,From_Date,To_Date,Repeat_Interval,Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
									" values('$rscheduleid','$beventid','$fromdate','$todate','$repeatinterval',".
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
				header('HTTP/1.0 203 repeat schedule already exists', true, 203);
		}
		
		mysqli_close($dbc);
	}
	
	/*
	   This is to get a particular repeat schedule
	 */
	Protected function pgetrepeatschedule($rscheduleid) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT RSchedule_Id rscheduleid, Repeat_Interval repeatinterval, From_Date fromdate, To_Date todate, BEvent_Id beventid ".
				 " FROM repeatschedule WHERE RSchedule_Id = '$rscheduleid' and Is_Deleted = 0";
      
		$data = mysqli_query($dbc, $query) or die(mysqli_error());
		if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This repeat schedule doesn\'t exist and has been deleted', true, 201);
	    }
		else {
		    $row = mysqli_fetch_array($data);	
			$one_arr = array();
			
			$one_arr['rscheduleid'] = $row['rscheduleid'];
			$one_arr['repeatinterval'] = $row['repeatinterval'];
			$one_arr['fromdate'] = $row['fromdate'];
			$one_arr['todate'] = $row['todate'];
			$one_arr['beventid'] = $row['beventid'];
					
			$data2 = json_encode($one_arr);
			echo $data2;
		}
		$data->close();
		mysqli_close($dbc);
	}
	
	// this is to update repeat schedule
	Protected function update_repeat_schedule($rscheduleid, $rschedule_arr) {
		
		$ownerid = $rschedule_arr['ownerid'];
		$beventid = $rschedule_arr['beventid'];
		$repeatinterval = $rschedule_arr['repeatinterval'];
		$fromdate = $rschedule_arr['fromdate'];
		$todate = $rschedule_arr['todate'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		try {
			// update the repeat schedule table
			$query2 = "Update repeatschedule set BEvent_Id = '$beventid', Repeat_Interval = '$repeatinterval', From_Date = '$fromdate', To_Date = '$todate', ".
			          " last_Modified = UTC_TIMESTAMP(), last_modified_id = '$ownerid' ".
						" where RSchedule_id = '$rscheduleid' ";
			$result1 = mysqli_query($dbc, $query2);
			
		}
		catch (Exception $e) {
			header('HTTP/1.0 201 Update failed', true, 202);		
		}
		
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		mysqli_close($dbc);
	}
	
	/**
	  This is to delete the schedule
	**/
	Protected function pdelete($rscheduleid, $ownerid) {
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT * FROM repeatschedule WHERE RSchedule_Id = '$rscheduleid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This schedule doesn\'t exist and has been deleted', true, 201);
	    }
		else {
			// Delete this participant by setting the flag Is_Deleted to 1
			$queryupdate = "update repeatschedule set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP() ".
						" where RSchedule_Id = '$rscheduleid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 This member can\'t be deleted', true, 202);
				exit;
			}
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;
			
		}
		$data->close();
		mysqli_close($dbc);
	}
	

	// this is to support patch update on repeat schedule
	// 03/01/2016 Dongling API 1.5
	// 
	Protected function partial_update_schedule($rscheduleid, $rschedule_arr) {
		// table for event
		$rschedulea = array(
			'repeatinterval' => 'Repeat_Interval',
			'fromdate' => 'From_Date',
			'todate' => 'To_Date'
		);
	
		$ownerid = $rschedule_arr['ownerid'];
        $data = array();		
        foreach($rschedule_arr as $key => $value) {
			foreach($rschedulea as $keya => $valuea) {
				if ($key == $keya) {
					$data[$valuea] = $value;
					break;
				}
			}
		}
		
		$where = "RSchedule_Id = '$rscheduleid' ";
		$table = "repeatschedule";
		
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
        $rscheduleid = $lastElement;
		header('Content-Type: application/json; charset=utf8');
		
		$this->pgetrepeatschedule($rscheduleid);
    }

	// This is the API for creating a repeat schedule for repeating events
	//    POST http://[domain_name]/repeatschedule
    public function post($request) {
		$parameters1 = array();
		header('Content-Type: application/json; charset=utf8');
		
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "repeatschedule") {
			// process a single repeat schedule
			$this->insert_repeat_schedule($request->body_parameters);
		}  
    }
	
	// update an event 
	// 1. PUT http://[api_domain_name]/repeatschedule/1234
	// 05/25/2015 API 1.5
	public function put($request) {
		$parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];	
		if ($last2Element == "repeatschedule") {
			$rschedule_arr = $request->body_parameters;
			$rscheduleid = $lastElement;
			$this->update_repeat_schedule($rscheduleid, $rschedule_arr);
	    }
    }
	
	/**
	   There is a body element "ownerid" in the DELETE HTTP Method 
	**/
	public function delete($request) {
	     // logic to handle an HTTP DELETE request goes here
		header('Content-Type: application/json; charset=utf8');
		$rscheduleid = end($request->url_elements);
		reset($request->url_elements);
		$ownerid = $request->parameters['ownerid'];
		$this->pdelete($rscheduleid, $ownerid);
    }
	
	
	// partially update repeat schedule
	// 02/10/2016
	// Only change some information about repeat schedule
	public function patch($request) {
	
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		reset($request->url_elements);
		if ($last2Element == "repeatschedule") {
			$rschedule_arr = $request->body_parameters;
			$rscheduleid = $lastElement;
			$this->partial_update_schedule($rscheduleid, $rschedule_arr);
		}	
	}
	
}
?>