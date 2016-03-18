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
	
	/*
	 * Insert a user into a community
	 * 11/06/2015 to support backbone 
	 * If the participant doesn't exist, insert it to the participant table
	 */
	Protected function insert_participant($participantid, $body_param) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		
		$ownerid = $body_param['ownerid'];
		$communityid = $body_param['communityid'];
		$profile = $body_param['profile'];
		$userid = $body_param['userid'];
		$username = $body_param['username'];
		$mobile = $body_param['mobile'];
		$userrole = $body_param['userrole'];
		
		$query = "SELECT Is_Deleted isdeleted FROM participant WHERE Community_Id = '$communityid' and User_Id = '$userid' LIMIT 1";
		$data = mysqli_query($dbc, $query) or die(mysqli_error());
	    $result = mysqli_fetch_assoc($data);
		if (mysqli_num_rows($data)== 1 and $result['isdeleted'] == 0) {
			header('HTTP/1.0 201 This participant exists already', true, 201);
			$data2 = json_encode(array('code'=> 201, 'message' => 'This participant exists already'));
			echo $data2;
			exit;	
		}
		else {
		    if (mysqli_num_rows($data)== 0) {
				$queryinsert1 = "insert into participant".
						 " (Participant_Id, Community_Id, User_Id, User_Role, Is_Deleted,Creator_Id,Created_Time, Last_Modified, Last_Modified_Id) ".
						 "values('$participantid', '$communityid','$userid', '$userrole',0,'$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
			}
			else if ($result['isdeleted'] == 1) {
				$queryinsert1 = "update participant ".
						 " set Is_Deleted = 0, User_Role = '$userrole', Last_Modified_Id = '$ownerid', Last_Modified = UTC_TIMESTAMP ".
						 " where Community_Id = '$communityid' and User_Id = '$userid' ";
			}
			
			$result = mysqli_query($dbc,$queryinsert1);
			if ($result !== TRUE) {
				header('HTTP/1.0 203 failed to inert shared members', true, 203);
				$data2 = json_encode(array('code'=> 203, 'message' => 'failed to inert participant'));
				echo $data2;
				exit;							
			}
			
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;			
		}
		
		mysqli_free_result($data);
		mysqli_close($dbc);
	}
	
	
	/*
	 *  This is to modify a participant including user's name, mobile but not email(it's unique) and they are all part of user object
	 *  We deal with two tables: user and participant
	 *  11/06/2015
	 */
	Protected function update_participant($participantid, $ownerid, $participant_parms) {
		$username = $participant_parms['username'];
		$communityid = $participant_parms['communityid'];
		//$profile= $participant_parms['profile'];
		$mobile = $participant_parms['mobile'];
		$userid = $participant_parms['userid'];
		$userrole = $participant_parms['userrole'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM participant WHERE Participant_Id = '$participantid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data) == 1) {
		    // Participant exists and go ahead to update it
			// two steps commit
			mysqli_autocommit($dbc, FALSE);
			// update this user in the user table
			$queryupdate = "update user set ".
						" User_Name = '$username', Mobile = '$mobile', Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
						" where User_Id = '$userid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 This user can\'t be changed', true, 202);
				exit;
			}
			else {
				// second to change the user role in the participant table
				$querydelete = "update participant set ".
				               " User_Role = '$userrole', Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid', ".
							   " Community_Id = '$communityid', User_Id = '$userid' ".
							   " WHERE Participant_Id = '$participantid' ";
				
				$result = mysqli_query($dbc,$querydelete) or die("Error is: \n ".mysqli_error($dbc));
				if ($result !== TRUE) {
					// if error, roll back transaction
					mysqli_rollback($dbc);
					header('HTTP/1.0 204 Failed to update the participant', true, 204);
					exit;
				}
				mysqli_commit($dbc);
				$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
				echo $data2;
			}	
		}
		else {
			header('HTTP/1.0 202 This participant doesn\'t exist', true, 202);
		}
		$data->close();
		mysqli_close($dbc);
	}
	/**
	 *   This is to support PATCH HTTP Method
	 */
	Protected function partial_update_participant($participantid, $participant_arr) {
		// table for participant, which including user information
		$usera = array(
			'username' => 'User_Name',
			'mobile' => 'Mobile',
			'profile' => 'Profile'
		);
		
		$participanta = array(
			'userrole' => 'User_Role',
		);
		
	    $ownerid = $participant_arr['ownerid'];
		// TBD: do we need to have this anyway???
		$userid  = $participant_arr['userid'];
		// for user table
        $udata = array();		
        foreach($participant_arr as $key => $value) {
			foreach($usera as $keya => $valuea) {
				if ($key == $keya) {
					$udata[$valuea] = $value;
					break;
				}
			}
		}
		$uwhere = "User_Id = '$userid' ";
		$utable = "user";
		// for participant table
		$pdata = array();		
        foreach($participant_arr as $key => $value) {
			foreach($participanta as $keya => $valuea) {
				if ($key == $keya) {
					$pdata[$valuea] = $value;
					break;
				}
			}
		}
		$pwhere = "Participant_Id = '$participantid' ";
		$ptable = "participant";
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		mysqli_autocommit($dbc, FALSE);
		
		if ($udata != null) {
			// update the user table
			$query2 = build_sql_update($utable, $udata, $uwhere, $ownerid);
			$result1 = mysqli_query($dbc, $query2);
			
			if ($result1 !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 Can not update user table', true, 202);
				exit;
			}
		}
		
		if ($pdata != null) {
			// update the participant table
			$query2 = build_sql_update($ptable, $pdata, $pwhere, $ownerid);
			$result1 = mysqli_query($dbc, $query2);
			
			if ($result1 !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 203 Can not update participant table', true, 203);
				exit;
			}
		}
		mysqli_commit($dbc);
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		mysqli_close($dbc);
	}
	
	Protected function pdelete($participantid) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM participant WHERE Participant_Id = '$participantid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This participant doesn\'t exist', true, 201);
	    }
		else {
			// Delete this participant by setting the flag Is_Deleted to 1
			$queryupdate = "update participant set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP() ".
						" where Participant_Id = '$participantid'";
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
    /**
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
	
	**/
	// this is to support backbone 
    // POST http://[domain name]/participant	
	public function post($request) {
		$parameters1 = array();
		header('Content-Type: application/json; charset=utf8');
		
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "participant") {
			$participantid = $request->body_parameters['participantid'];
			$this->insert_participant($participantid, $request->body_parameters);
		}  
    }
	
	// method to update a participant in the community
	// Participant is the same as user except it has userrole 
	// PUT http://[Domain Name]/participant/123
	// 11/06/2015
	public function put($request) {
		
        $participantid = end($request->url_elements);
		reset($request->url_elements);
		header('Content-Type: application/json; charset=utf8');
		$this->update_participant($participantid, $request->body_parameters['ownerid'], $request->body_parameters);
    }
	
	// partially update participant's profile and role
	// 11/01/2015
	public function patch($request) {
	
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		reset($request->url_elements);
		
		if ($last2Element == "participant") {
			$participant_arr = $request->body_parameters;
			$participantid = $lastElement;
			$this->partial_update_participant($participantid, $participant_arr);
		}	
	}
	
	/**
	  * It just deletes a participant from a community
	**/
	public function delete($request) {
		 // logic to handle an HTTP PUT request goes here
		$participantid = end($request->url_elements);
		reset($request->url_elements);
		header('Content-Type: application/json; charset=utf8');
		$this->pdelete($participantid);
    }

}
?>