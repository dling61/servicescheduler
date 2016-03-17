<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');

class TaskHelper Extends Resource
{
	
    public function __construct($request) {
        parent::__construct($request);
		
    }
	
	protected $lastid;
	
	// This is to delete the taskhelper
	// 09/08/2015 
	Protected function pdelete($taskhelperid, $ownerid) {
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT * FROM taskhelper WHERE TaskHelper_Id = '$taskhelperid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This schedule doesn\'t exist or has been deleted', true, 201);
	    }
		else {
			try {
				// update the task table
				$query2 = "Update taskhelper set Is_Deleted = 1, last_Modified = UTC_TIMESTAMP(), last_modified_id = '$ownerid' ".
							" where TaskHelper_id = '$taskhelperid' ";
				$result1 = mysqli_query($dbc, $query2);
			}
			catch (Exception $e) {
				header('HTTP/1.0 201 Update failed', true, 202);		
			}
		}
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		$data->close();
		mysqli_close($dbc);
	}
	
	
	// to insert a new taskhelper
	// Status --- "A" (Assigned - default); "C" (Confirmed); "D"(Denied)
	// 09/08/2015
	Protected function insert_taskhelper($taskhelperid, $taskhelper) {
		$ownerid = $taskhelper['ownerid'];
		$taskid = $taskhelper['taskid'];
		$userid = $taskhelper['userid'];
		$eventid = $taskhelper['eventid'];
		$status = $taskhelper['status'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		$query = "SELECT TaskHelper_Id FROM taskhelper WHERE TaskHelper_Id = $taskhelperid";
        $data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data) == 1) {
			header('HTTP/1.0 201 This task exists already', true, 201);
	    }
		else {	
			$queryinsert1 = "insert into taskhelper ".
							 "(TaskHelper_Id,Task_Id,User_Id,Event_Id, Status, Creator_Id, Created_Time,Last_Modified, Last_Modified_Id) ".
							 "values('$taskhelperid','$taskid','$userid', '$eventid','$status','$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
							
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
	
	// to update some values of attributes
	// Patch 
	// 09/16/2015
	Protected function partial_update_patch($taskhelperid, $taskhelper_arr) {
		// table for task
		$taskhelpera = array(
			'userid' => 'Task_Id',
			'taskid' => 'User_Id',
			'eventid' => 'Event_Id',
			'status' => 'Status'
		);
	
		$ownerid = $taskhelper_arr['ownerid'];
        $data = array();		
        foreach($taskhelper_arr as $key => $value) {
			foreach($taskhelpera as $keya => $valuea) {
				if ($key == $keya) {
					$data[$valuea] = $value;
					break;
				}
			}
		}
		$where = "TaskHelper_Id = '$taskhelperid' ";
		$table = "taskhelper";
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		try {
			// update the taskhelper table
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

	// TDB
	//  
	public function get($request) {
	
    }

	// partially update task
	// this is to update the status "A" -- assigned; "C" -- Confirmed; "D"  -- Denied
	// http://[Domain_Name]/taskhelper/12323
	// 08/26/2015
	public function patch($request) {
		$parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		reset($request->url_elements);
		
		if ($last2Element == "taskhelper") {
			$taskhelper_arr = $request->body_parameters;
			$taskhelperid = $lastElement;
			$this->partial_update_patch($taskhelperid, $taskhelper_arr);
		}	
	}
	
	// update the assignment associated with a task
	// 1. PUT http://[api_domain_name]/taskhelper/1234
	// TBD
	public function put($request) {
	
		
    }
	
	// Delete a task helper
	// 2. Delete http://[api_domain_name]/taskhelper/1234
	// 09/08/2015 Dongling API 1.5 
	public function delete($request) {
		$taskhelperid = end($request->url_elements);
		reset($request->url_elements);
		$ownerid = $request->parameters['ownerid'];
		$this->pdelete($taskhelperid, $ownerid);
    }

	// Post to create a new taskhelper
	// 1. POST http://[api_domain_name]/taskhelper
	// 09/05/2015 This is a new model to support backbone.js
	public function post($request) {
		$parameters1 = array();
		header('Content-Type: application/json; charset=utf8');
		
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "taskhelper") {
			$taskhelperid = $request->body_parameters['taskhelperid'];
			$this->insert_taskhelper($taskhelperid, $request->body_parameters);
		}  
    }

}
?>