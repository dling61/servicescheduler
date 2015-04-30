<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');

class Participant Extends Resource
{
	
    public function __construct($request) {
        parent::__construct($request);
		
    }
	protected $lastid;
	
	Protected function insert($ownerid, $member_parms) {
	
		$email = $member_parms['email'];
		$username = $member_parms['name'];
		$mobile = $member_parms['mobile'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	  
		$query = "SELECT User_Id FROM user WHERE  Email = '$email'";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data) >= 1) {
			header('HTTP/1.0 201 This user already exists', true, 201);
	    }
		else {
			// Insert this member if no exists
			$queryinsert = "insert into user(Email,User_Name,Password,User_Type, Mobile,Verified, Created_Time, Last_Modified)
								 values('$email','$username','','','$mobile',0, UTC_TIMESTAMP(), UTC_TIMESTAMP())"; 
				
			$result = mysqli_query($dbc,$queryinsert); 
			if ($result != TRUE) {
				// throw the error 201 and return to client
				header('HTTP/1.0 401 member id exists', true, 401);
				die();
			}
			$data2 = mysqli_query($dbc,$querysearch);			
			$row = mysqli_fetch_array($data2);
			$userid = $row['User_Id'];
				
			$data3 = json_encode(array('userid'=> $userid));
			echo $data3;
			
		}
		$data->close();
		mysqli_close($dbc);
	}

	Protected function update($memberid, $ownerid, $member_parms) {
		$membername = $member_parms['membername'];
		$email= $member_parms['email'];
		$mobile = $member_parms['mobile'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM member WHERE Member_Id = '$memberid'";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data) == 1) {
		    // Member exists and go ahead to update it
		
			$timestamp = date('Y-m-d H:i:s');
			$queryupdate = "update member set ".
						"Member_Name = '$membername', Member_Email = '$email', Mobile_Number = '$mobile', Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
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
	
	Protected function pdelete($memberid) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
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
		//$data = mysqli_query($dbc, "CALL getMemberByLastUpdate('$ownerid', '$lastupdatetime')") or die("Error is: \n ".mysqli_error($dbc));
		$query = " SELECT Member_Id memberid, Member_Email memberemail, Member_Name membername, Mobile_Number mobilenumber, Creator_Id creatorid, ".
		         " Created_Time createdtime, Is_Deleted isdeleted, Last_Modified lastmodified FROM member ".
				 " WHERE Creator_Id = '$ownerid' and Last_Modified > '$lastupdatetime' ".
				 " and Member_Id != 'ownerid'*10000";
		$data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
		$return_arr = array();
		$memberid_arr = array();
		$members_arr = array();
		
		if(mysqli_num_rows($data) > 0) {
			$i = 0;
			$j = 0;
			while($row0 = mysqli_fetch_array($data)){
			   $isdeleted = $row0['isdeleted'];
				// if it's deleted, just add it to "deletedmembers"
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
	
	// this is the API to get the list of members
	// http://servicescheduler.net/member?ownerid=12143&lastupdatetime=121333000
	public function get($request) {
        $ownerid = $request->parameters['ownerid'];
		$lastupdatetime = urldecode($request->parameters['lastupdatetime']);
		
		header('Content-Type: application/json; charset=utf8');
		$this->pgetlastupdate($ownerid, $lastupdatetime);
    }

	// This is the API to add a new participant to the server if it doesn't exist
	// POST http://servicescheduler.net/participant
    public function post($request) {
		$parameters1 = array();
      
		if ($request->body_parameters ['participant']) {
			foreach($request->body_parameters['participant'] as $param_name => $param_value) {
					$parameters1[$param_name] = $param_value;
			}
		}
		header('Content-Type: application/json; charset=utf8');
		$this->insert($request->body_parameters['ownerid'], $parameters1);  
    }
	
	// method to update a member
	// PUT http://servicescheduler.net/members/15433312
	public function put($request) {
		$parameters1 = array();
			
		if ($request->body_parameters['members']) {
			foreach($request->body_parameters['members'] as $param_name => $param_value) {
							$parameters1[$param_name] = $param_value;
			}
		}
        $memberid = end($request->url_elements);
		reset($request->url_elements);
		header('Content-Type: application/json; charset=utf8');
		$this->update($memberid, $request->body_parameters['ownerid'], $parameters1);
    }
	
	/**
	   There is no body in the DELETE HTTP Method
	**/
	public function delete($request) {
		 // logic to handle an HTTP PUT request goes here
		$memberid = end($request->url_elements);
		reset($request->url_elements);
		header('Content-Type: application/json; charset=utf8');
		$this->pdelete($memberid);
    }

}
?>