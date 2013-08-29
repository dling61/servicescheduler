<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');

class Members Extends Resource
{
	
    public function __construct($params) {
        parent::__construct($params);
		
    }
	protected $lastid;
	
	// this is test
	
	Protected function insert($ownerid, $member_parms) {
	
		$email = $member_parms['email'];
		$memberid = $member_parms['memberid'];
		$membername = $member_parms['membername'];
		$mobile = $member_parms['mobile'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	  
		$query = "SELECT Member_Email, Member_Id, Member_Name, Mobile_Number, Last_Modified FROM member WHERE Member_Id = '$memberid' or Member_Email = '$email'";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==1) {
			header('HTTP/1.0 201 This member id or email already exists', true, 201);
	    }
		else {
			// Insert this member if no exists
			$queryinsert = "insert into member(Member_Id,Member_Email,Member_Name,Mobile_Number,Is_Registered,Creator_Id, Created_Time, Last_Modified)
			                 values('$memberid','$email','$membername','$mobile',0, '$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP())"; 
			$result = mysqli_query($dbc,$queryinsert) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
			}
			//$result->close();  --- Don't know why it gave error
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;
			
		}
		$data->close();
		mysqli_close($dbc);
	}

	Protected function update($ownerid, $member_parms) {
		$membername = $member_parms['membername'];
		$email= $member_parms['email'];
		$mobile = $member_parms['mobile'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	    $memberid = $this->params[0];
		$query = "SELECT * FROM member WHERE Member_Id = '$memberid'";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==1) {
		    // Member exists and go ahead to update it
		
			$timestamp = date('Y-m-d H:i:s');
			$queryupdate = "update member set ".
						"Member_Name = '$membername', Member_Email = '$email', Mobile_Number = '$mobile', Last_Modified = UTC_TIMESTAMP() ".
						" where Member_Id = '$memberid'";
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
	    $memberid = $this->params[0];
		$query = "SELECT * FROM member WHERE Member_Id = '$memberid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This member doesn\'t exist', true, 201);
	    }
		else {
			// Delete this member by setting the flag Is_Deleted to 1
			$timestamp = date('Y-m-d H:i:s');
			$queryupdate = "update member set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP() ".
						" where Member_Id = '$memberid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 This member can\'t be deleted', true, 202);
				exit;
			}
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		    //echo json_encode($data2);
			echo $data2;
			
		}
		$data->close();
		mysqli_close($dbc);
	}
	
	/**
	    This method is for retrieving all members associated with the device owner including those created by it or assigned to it
	**/
	Protected function pgetlastupdate($ownerid, $lastupdatetime) {
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		// call a stored procedure to get the members to be returned to caller
		$data = mysqli_query($dbc, "CALL getMemberByLastUpdate('$ownerid', '$lastupdatetime')") or die("Error is: \n ".mysqli_error($dbc));
		
		$return_arr = array();
		$memberid_arr = array();
		$members_arr = array();
		
		if(mysqli_num_rows($data) > 0) {
			$i = 0;
			$j = 0;
			while($row0 = mysqli_fetch_array($data)){
			   $isdeleted = $row0['isdeleted'];
				// if it's deleted, just add it to "dservices"
			   if ($isdeleted == 1) {
				 $memberid_arr[$j] = $row0['memberid'];
				 $j++;
			   }
			   else {
				   $one_arr = array();
				  
				   $one_arr['memberid'] = $row0['memberid'];
				   $one_arr['memberemail'] = $row0['memberemail'];
				   $one_arr['membername'] = $row0['membername'];
				   $one_arr['mobilenumber'] = $row0['mobilenumber'];
				   $one_arr['creatorid'] = $row0['creatorid'];
				   $one_arr['createdtime'] = $row0['createdtime'];
				   $one_arr['lastmodified'] = $row0['lastmodified'];
				   
				   $members_arr[$i] = $one_arr;
				   $i++;			   
			   }   
			   
			} // while end
		} // if end
		$return_arr['deletedmembers'] = $memberid_arr;
		$return_arr['members'] = $members_arr;
         
		$data2 = json_encode($return_arr);
		echo $data2;
		
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid,$data2);
      
		$data->close();
		mysqli_close($dbc);	
	}
	
	public function get() {
        $ownerid = $this->params['ownerid'];
		$lastupdatetime = urldecode($this->params['lastupdatetime']);
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    $this->lastid = logserver("Members", "GET", $ownerid." ".$lastupdatetime);
		
		header('Content-Type: application/json; charset=utf8');
		$this->pgetlastupdate($ownerid, $lastupdatetime);
    }

	
	public function put() {
        $parameters = array();
		$parameters1 = array();
        // logic to handle an HTTP PUT request goes here
		$body = file_get_contents("php://input");
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver("Members", "PUT", $body);
			
		$body_params = json_decode($body);
        if($body_params) {
            foreach($body_params as $param_name => $param_value) {
                        $parameters[$param_name] = $param_value;
            }
        }
		
		if ($parameters['members']) {
			foreach($parameters['members'] as $param_name => $param_value) {
							$parameters1[$param_name] = $param_value;
			}
		}
        
		header('Content-Type: application/json; charset=utf8');
		$this->update($parameters['ownerid'], $parameters1);
    }

	// This is the API to add a new member in the server
    public function post() {
	
		$parameters  = array();
		$parameters1 = array();
        // logic to handle an HTTP GET request goes here
		$body = file_get_contents("php://input");
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver("Members", "POST", $body);
			
		$body_params = json_decode($body);
		
        if($body_params) {
            foreach($body_params as $param_name => $param_value) {
                        $parameters[$param_name] = $param_value;
            }
		}	
		if ($parameters['members']) {
			foreach($parameters['members'] as $param_name => $param_value) {
							$parameters1[$param_name] = $param_value;
			}
		}
		
		header('Content-Type: application/json; charset=utf8');
		
		$this->insert($parameters['ownerid'], $parameters1);
	    
    }
	
	/**
	   There is no body in the DELETE HTTP Method
	**/
	public function delete() {
		 // logic to handle an HTTP PUT request goes here
		$memberid = $this->params[0];
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver("Members", "DELETE", $memberid);
		header('Content-Type: application/json; charset=utf8');
		$this->pdelete();
    }

}
?>