<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');

class AssignmentPool Extends Resource
{
	
    public function __construct($request) {
        parent::__construct($request);
		
    }
	
	protected $lastid;
	
	// This is to delete the AssignmentPool
	// 09/08/2015 
	Protected function pdelete($AssignmentPoolid, $ownerid) {
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT * FROM assignmentpool WHERE AssignmentPool_Id = '$AssignmentPoolid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This schedule doesn\'t exist or has been deleted', true, 201);
	    }
		else {
			try {
				// update the task table
				$query2 = "Update assignmentpool set Is_Deleted = 1, last_Modified = UTC_TIMESTAMP(), last_modified_id = '$ownerid' ".
							" where AssignmentPool_id = '$AssignmentPoolid' ";
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
	
	
	// to insert a new AssignmentPool
	// 03/16/2016
	Protected function insert_AssignmentPool($AssignmentPoolid, $AssignmentPool) {
		$ownerid = $AssignmentPool['ownerid'];
		$taskid = $AssignmentPool['taskid'];
		$userid = $AssignmentPool['userid'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		$query = "SELECT AssignmentPool_Id FROM assignmentpool WHERE AssignmentPool_Id = $AssignmentPoolid";
        $data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data) == 1) {
			header('HTTP/1.0 201 This task exists already', true, 201);
	    }
		else {	
			$queryinsert1 = "insert into AssignmentPool ".
							 "(AssignmentPool_Id,Task_Id,User_Id, Is_Deleted, Creator_Id, Created_Time,Last_Modified, Last_Modified_Id) ".
							 "values('$AssignmentPoolid','$taskid','$userid', 0, '$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
							
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
	Protected function partial_update_patch($AssignmentPoolid, $AssignmentPool_arr) {
		// table for task
		$AssignmentPoola = array(
			'userid' => 'Task_Id',
			'taskid' => 'User_Id'
		);
	
		$ownerid = $AssignmentPool_arr['ownerid'];
        $data = array();		
        foreach($AssignmentPool_arr as $key => $value) {
			foreach($AssignmentPoola as $keya => $valuea) {
				if ($key == $keya) {
					$data[$valuea] = $value;
					break;
				}
			}
		}
		$where = "AssignmentPool_Id = '$AssignmentPoolid' ";
		$table = "assignmentpool";
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		try {
			// update the AssignmentPool table
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
	// http://[Domain_Name]/AssignmentPool/12323
	// 08/26/2015
	public function patch($request) {
		$parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		reset($request->url_elements);
		
		if ($last2Element == "assignmentpool") {
			$AssignmentPool_arr = $request->body_parameters;
			$AssignmentPoolid = $lastElement;
			$this->partial_update_patch($AssignmentPoolid, $AssignmentPool_arr);
		}	
	}
	
	// update the assignment associated with a task
	// 1. PUT http://[api_domain_name]/AssignmentPool/1234
	// TBD
	public function put($request) {
		
    }
	
	// Delete a task helper
	// 2. Delete http://[api_domain_name]/assignmentpool/1234
	// 09/08/2015 Dongling API 1.5 
	public function delete($request) {
		$AssignmentPoolid = end($request->url_elements);
		reset($request->url_elements);
		$ownerid = $request->parameters['ownerid'];
		$this->pdelete($AssignmentPoolid, $ownerid);
    }

	// Post to create a new AssignmentPool
	// 1. POST http://[api_domain_name]/AssignmentPool
	// 09/05/2015 This is a new model to support backbone.js
	public function post($request) {
		$parameters1 = array();
		header('Content-Type: application/json; charset=utf8');
		
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "assignmentpool") {
			$AssignmentPoolid = $request->body_parameters['assignmentpoolid'];
			$this->insert_AssignmentPool($AssignmentPoolid, $request->body_parameters);
		}  
    }

}
?>