<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');

class Services Extends Resource
{
	
    public function __construct($params) {
        parent::__construct($params);
		
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

	Protected function update($ownerid, $service_parms) {
		$servicename = $service_parms['servicename'];
		$description= $service_parms['desp'];
		$repeat = $service_parms['repeat'];
		$startdatetime = $service_parms['startdatetime'];
		$enddatetime = $service_parms['enddatetime'];
		// Don't need to update it
		//$utcoff = $service_parms['utcoff'];
		$alert = $service_parms['alert'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	    $serviceid = $this->params[0];
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
	
	
	Protected function pdelete() {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	    $serviceid = $this->params[0];
		
		$query = "SELECT * FROM service WHERE Service_Id = '$serviceid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This service doesn\'t exist', true, 201);
	    }
		else {
			// Serchdule exists and go ahead to update it
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
				// first to delete the existing relationship
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
					$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
					echo $data2;
				}		
			}	
		}
		
		$data->close();
		mysqli_close($dbc);
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
	
	public function get() {
	   
        $ownerid = $this->params['ownerid'];
		$lastupdatetime = urldecode($this->params['lastupdatetime']);
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    $this->lastid = logserver("Sevices", "GET", $ownerid." ".$lastupdatetime);
		header('Content-Type: application/json; charset=utf8');
		$this->pgetlastupdate($ownerid, $lastupdatetime);
    }

	// This is the API to register a user in the servre and login in
    public function post() {
	
		$parameters  = array();
		$parameters1 = array();
        // logic to handle an HTTP GET request goes here
		$body = file_get_contents("php://input");
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver("Services", "POST", $body);
			
		$body_params = json_decode($body);
		
        if($body_params) {
            foreach($body_params as $param_name => $param_value) {
                        $parameters[$param_name] = $param_value;
            }
		}	
		if ($parameters['services']) {
			foreach($parameters['services'] as $param_name => $param_value) {
							$parameters1[$param_name] = $param_value;
			}
		}
		
		header('Content-Type: application/json; charset=utf8');
		$this->insert($parameters['ownerid'], $parameters1);
	    
    }
	
	// update a service with the service Id
	public function put() {
        $parameters = array();
		$parameters1 = array();
        // logic to handle an HTTP PUT request goes here
		$body = file_get_contents("php://input");
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver("Services", "PUT", $body);
			
		$body_params = json_decode($body);
        if($body_params) {
            foreach($body_params as $param_name => $param_value) {
                        $parameters[$param_name] = $param_value;
            }
        }
		
		if ($parameters['services']) {
			foreach($parameters['services'] as $param_name => $param_value) {
							$parameters1[$param_name] = $param_value;
			}
		}
        
		header('Content-Type: application/json; charset=utf8');
		$this->update($parameters['ownerid'], $parameters1);
    }
	
	
	/**
	   There is no body in the DELETE HTTP Method
	**/
	public function delete() {
		 // logic to handle an HTTP PUT request goes here
		$serviceid = $this->params[0];
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver("Services", "DELETE", $serviceid);
		header('Content-Type: application/json; charset=utf8');
		$this->pdelete();
    }

}
?>
