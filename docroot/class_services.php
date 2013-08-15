<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');
require_once('class_request.php');

class Services Extends Resource
{
	
   public function __construct() {
        parent::__construct();
	}	
	
	protected $lastid;
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
								"(Service_Id,Service_Name,Description,SRepeat,Start_Datetime,End_Datetime,UTC_Off,Alert,Creator_Id,Is_Deleted,Created_Time,Last_Modified)".
								" values('$serviceid','$servicename','$description','$srepeat',UNIX_TIMESTAMP('$startdatetime'),UNIX_TIMESTAMP('$enddatetime'),'$utcoff','$alert','$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP())";
			
			$result = mysqli_query($dbc,$queryinsert) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 202 This service can\' be added', true, 202);
				Exit;
			}
			
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
						"Service_Name = '$servicename', Description = '$description', SRepeat = '$repeat', Start_Datetime = UNIX_TIMESTAMP('$startdatetime'), End_Datetime = UNIX_TIMESTAMP('$enddatetime'), Last_Modified = UTC_TIMESTAMP() ".
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
	
	// delete a service
	Protected function pdelete($serviceid) {
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
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP() ".
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
							" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP() ".
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
						$querydelete = "update onduty set ".
							" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP() ".
							" where Service_Id = '$serviceid'";
					
						$result = mysqli_query($dbc,$querydelete) or die("Error is: \n ".mysqli_error($dbc));
						if ($result !== TRUE) {
							// if error, roll back transaction
							mysqli_rollback($dbc);
							header('HTTP/1.0 204 Failed to delete onduty', true, 204);
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
	
	// delete a frind shared with a service
	Protected function pdelete_shareservices($serviceid, $email) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT * FROM shareservice WHERE Service_Id = '$serviceid' and Email = '$email' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This sharedwith email doesn\'t exist', true, 201);
			exit;
	    }
		else {
			// Delete this shared email by setting the flag Is_Deleted to 1
			$update = "update shareservice set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP() ".
						" where Service_Id = '$serviceid' and Email = '$email'";
			$result = mysqli_query($dbc,$update) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				header('HTTP/1.0 204 Failed to delete shareservice', true, 204);
				exit;
			}
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;
		}
	}
	
	Protected function pgetlastupdate($ownerid, $lastupdatetime) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
	
		// call a stored procedure to get the services to be returned to caller
		$data = mysqli_query($dbc, "CALL getServiceByLastUpdate('$ownerid', '$lastupdatetime')") or die("Error is: \n ".mysqli_error($dbc));
		
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
				   $one_arr['alert'] = $row0['alert'];
				   $one_arr['creatorid'] = $row0['creatorid'];
				   $one_arr['createdtime'] = $row0['createdtime'];
				   $one_arr['lastmodified'] = $row0['lastmodified'];
				   
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
	
	// This is for shareservice API to share a service with some emails
	Protected function insert_shareservices($serviceid, $ownerid, $sharedwith_param) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		//go ahead to insert email in the shareservice table
		// TBD: don't handle the case in which a deleted email is being added back again.
		$email = $sharedwith_param;
		
		$query = "SELECT * FROM shareservice WHERE Service_Id = '$serviceid' and Email = '$email'";
		$data = mysqli_query($dbc, $query) or die(mysqli_error());
	
		if (mysqli_num_rows($data)==1) {
			header('HTTP/1.0 201 This service exists already', true, 201);
		}
		$queryinsert1 = "insert into shareservice".
				 " (Service_Id, Email,Is_Deleted,Creator_Id,Created_Time, Last_Modified) ".
				 "values('$serviceid','$email',0,'$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
		$result = mysqli_query($dbc,$queryinsert1);
		if ($result !== TRUE) {
			header('HTTP/1.0 203 failed to inert email', true, 203);
			echo mysqli_error($dbc);
			exit;							
		}
		mysqli_free_result($data);
		
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
        echo $data2;			
		
		mysqli_close($dbc);
	}
	
	// This is the GET calls to get the list of services and list of emails shared on the service
	//  1. http://servicescheduler.net/service?ownerid=12143&lastupdatetime=121333000
	//  2. http://servicescheduler.net/service/1234/shareservice?ownerid=12434&lastupdatetime=121443232
	public function get($request) {
        $ownerid = $request->parameters['ownerid'];
		$lastupdatetime = urldecode($request->parameters['lastupdatetime']);
	    
		header('Content-Type: application/json; charset=utf8');
		$this->pgetlastupdate($ownerid, $lastupdatetime);
    }

	// This is the POST call to add a new service and share a service with a email
	//   1. POST http://servicescheduler.net/services
	//   2. POST http://servicescheduler.net/services/1234/shareservice
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
		} else if ($lastElement == "shareservices") {
		    $serviceid  = $request->url_elements[count($request->url_elements)-2];
			 //share service method
			$this->insert_shareservices($serviceid, $request->body_parameters['ownerid'], $request->body_parameters['sharedwith']);
		}
	    
    }
	
	// update a service with the service Id
	// 1. PUT http://servicescheduler.net/services/1234
	public function put($request) {
        $parameters1 = array();
		
		if ($request->body_parameters['services']) {
			foreach($request->body_parameters['services'] as $param_name => $param_value) {
							$parameters1[$param_name] = $param_value;
			}
		}

		header('Content-Type: application/json; charset=utf8');
		//handle different resources
		$serviceid = end($request->url_elements);
		reset($request->url_elements);
		$this->update($serviceid, $request->body_parameters['ownerid'], $parameters1);
	    
    }
	
	
	// This is the DELETE call to delete a service and delete an email from the email list shared on the service
	//   1. DELETE http://servicescheduler.net/service
	//   2. DELETE http://servicescheduler.net/service/1234/shareservice
	public function delete($request) {
		$last2Element = $request->url_elements[count($request->url_elements)-2];
	    
		header('Content-Type: application/json; charset=utf8');
		if ($last2Element == "services") {
			// Delete service
			$serviceid = end($request->url_elements);
			reset($request->url_elements);
			$this->pdelete($serviceid);
		}
		else if ($last2Element == "shareservices") {
			// Delete a friend email from shareservice
		    $serviceid = $request->url_elements[count($request->url_elements)-3];
			$email =  end($request->url_elements);
			reset($request->url_elements);
			$this->pdelete_shareservices($serviceid, $email);
			
		}
		
    }

}
?>
