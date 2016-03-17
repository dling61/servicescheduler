<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');
require_once('class_request.php');

class Creator Extends Resource
{
	protected $action;
	protected $lastid;
	
	public function __construct($request) {
		 parent::__construct($request);
	}	
	
	// this is to register 
	Protected function register($body_parms) {
	    
	    $dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		mysqli_select_db($dbc, DB_NAME);
		
		$email = $body_parms['email'];
		$username = $body_parms['username'];
		$password = $body_parms['password'];
		$mobile = $body_parms['mobile'];
		
		$querysearch = "select User_Id from user where Email='$email'";
		$data = mysqli_query($dbc,$querysearch);
		
		if(!isEmptyString($email) and !isEmptyString($username) and !isEmptyString($password)) {
			//register a user
			if(mysqli_num_rows($data)==0){
			
				$queryinsert = "insert into user(Email,User_Name,Password,User_Type, Mobile,Verified, Created_Time, Last_Modified)
								 values('$email','$username',SHA('$password'),'','$mobile',0, UTC_TIMESTAMP(), UTC_TIMESTAMP())"; 
				
				mysqli_query($dbc,$queryinsert)or die("Error is: \n ".mysqli_error($dbc));		
				
				$data2 = mysqli_query($dbc,$querysearch);			
				$row = mysqli_fetch_array($data2);
				$userid = $row['User_Id'];
				
				// Success
				$data3 = json_encode(array('ownerid'=> $userid));
				echo $data3;
				
				// logserver if debug flag is set to 1
					if (DEBUG_FLAG == 1)
						logserveronce("Register","POST", $email, $data3);
				$data2->close();
			}
			else {
				// there is already registered
				header('X-PHP-Response-Code: 201', true, 201);
				echo json_encode(array('error message'=>'This user is already existing'));
			}
		}
		else {
			// empty or null for one of user name, password and email
			//header('HTTP/1.0 202 User name, password and email might be empty',true, 202);
			header('X-PHP-Response-Code: 202', true, 202);
			//http_response_code(202);
			echo json_encode(array('error message'=>'empty or null value for one of user name, password and or email'));
		}
		
		$data->close();
		mysqli_close($dbc);
	}
	
	Protected function signin($body_parms) {
	  
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		 
		$email = $body_parms['email'];
		$password = $body_parms['password'];
		 
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	  
        $query = "SELECT IFNULL(MAX(u.User_Id ), 0) User_Id, u.User_Name User_Name, u.Profile Profile, u.Mobile Mobile, IFNULL(MAX( s.Community_Id ), 0) Community_Id, IFNULL(MAX(t.Task_Id ), 0) Task_Id, ".
		         " IFNULL(MAX(sc.event_id), 0) Event_Id, IFNULL(MAX(th.TaskHelper_id), 0) TaskHelper_Id, IFNULL(MAX(bs.BEvent_Id), 0) BEvent_Id, IFNULL(MAX(pg.PGroup_id), 0) PGroup_Id, ".
				 " IFNULL(MAX(rs.rschedule_id), 0) RSchedule_Id, IFNULL(MAX(pt.participant_id), 0) Participant_Id FROM user u ".
					"LEFT JOIN community s ON u.User_Id = s.Creator_Id ".
					"LEFT JOIN task t ON u.User_Id = t.Creator_Id ".
					"LEFT JOIN event sc ON u.User_Id = sc.Creator_Id ".
					"LEFT JOIN taskhelper th ON u.User_Id = th.Creator_Id ".
					"LEFT JOIN baseevent bs ON u.User_Id = bs.Creator_Id ".
					"LEFT JOIN participantgroup pg ON u.User_Id = pg.Creator_Id ".
					"LEFT JOIN repeatschedule rs ON u.User_Id = rs.Creator_Id ".
					"LEFT JOIN participant pt ON u.User_Id = pt.Creator_Id ".
					" WHERE u.Email =  '$email' AND u.Password = SHA('$password')";
		
		$data = mysqli_query($dbc, $query);	
        if (mysqli_num_rows($data)==1) {
		    $row = mysqli_fetch_array($data);
			
			$one_arr = array();
			$one_arr['ownerid'] = $row['User_Id'];
			$one_arr['username'] = $row['User_Name'];
			$one_arr['profile'] = PROFILE_SERVER.$row['Profile'];
			$one_arr['mobile'] = $row['Mobile'];
			$one_arr['communityid'] = $row['Community_Id'];
			$one_arr['taskid'] = $row['Task_Id'];
			$one_arr['eventid'] = $row['Event_Id'];
			$one_arr['taskhelperid'] = $row['TaskHelper_Id'];
			$one_arr['baseeventid'] = $row['BEvent_Id'];
			$one_arr['participantgroupid'] = $row['PGroup_Id'];
			$one_arr['repeatscheduleid'] = $row['RSchedule_Id'];
			$one_arr['participantid'] = $row['Participant_Id'];
			
			if ($one_arr['ownerid'] != 0) {
				$data2 = json_encode($one_arr);
				echo $data2;
				
				// logserver if debug flag is set to 1
				if (DEBUG_FLAG == 1)
					logserveronce("Signin","POST", $email, $data2);
			} else { 
				// No much or wrong password 
				header('HTTP/1.0 401 Invalid user name/password', true, 401);
			}
	    }
		else {
			// No match in the user table
			header('HTTP/1.0 401 Invalid user name/password', true, 401);
		
		}
		$data->close();
		mysqli_close($dbc);
	}

	// This is to process a reset password request 
	Protected function resetpw($body_parms) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		 
		$email = $body_parms['email'];
		 
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	  
        $query = "SELECT User_Id FROM user WHERE Email = '$email'";
					
		$data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data) == 1) {
			// logserver if debug flag is set to 1
			if (DEBUG_FLAG == 1)
					logserveronce("Resetpw","POST", $email, "");
			$token = rand(10000, 10000000);
			
			//check to see if there is an existing request
			$query = "select Email from resetpassword where Email = '$email'";
			$data = mysqli_query($dbc,$query) or die("Error is: \n ".mysqli_error($dbc));
			
			if (mysqli_num_rows($data) == 1) {
			
				$resetupdate = "update resetpassword set Token = '$token', Last_Modified = NOW(), Is_Done = 0, Expired_Time = (UNIX_TIMESTAMP(UTC_TIMESTAMP()) + 720) where Email = '$email'";
				$result = mysqli_query($dbc,$resetupdate) or die("Error is: \n ".mysqli_error($dbc));
				if ($result !== TRUE) {
					// if error, roll back transaction
					header('HTTP/1.0 202 The password can not be updated', true, 201);
				}
			}
			else {
				// create a new entry
				$resetquery = "insert resetpassword(Email,Token,Is_Done,Expired_Time,Created_Time,Last_Modified) 
								values('$email','$token',0,UNIX_TIMESTAMP(UTC_TIMESTAMP()) + 720,NOW(), NOW())";
								
				$result = mysqli_query($dbc,$resetquery) or die("Error is: \n ".mysqli_error($dbc));
				if ($result !== TRUE) {
					// if error, roll back transaction
					header('HTTP/1.0 202 The password can not be updated', true, 201);
				}
			}
			
			resetpw_email($email, $token);
	    }
		else {
			// No match in the user table
			header('HTTP/1.0 201 Email doesn�t exist', true, 201);
		
		}
		$data->close();
		mysqli_close($dbc);
		
	}
	// this is to set token in the server for push notification
	// 07/04/2014 dding:  Add deviceid to record different token
	Protected function settoken($body_parms) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		 
		$userid = $body_parms['userid'];
		$token = $body_parms['token'];
		$udid = $body_parms['udid'];
		// record the device ID. It is set by the parameter "d" in the every call
		$deviceid = $this->deviceid;
		 
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	  
        $query = "SELECT token FROM userlog WHERE Udid = '$udid'";
					
		$data = mysqli_query($dbc, $query);
		
		if (DEBUG_FLAG == 1)
			logserveronce("Settoken","POST", 'DeviceID: '.$deviceid.' '.'UserID: '.$userid.' '.'Udid: '.$udid.' '.'Token: '.$token, "");
		
        if (mysqli_num_rows($data) == 1) {
			//update the token
			$updatetoken = "update userlog set Token = '$token', User_Id = '$userid', Device_Id = '$deviceid', Last_Modified = NOW() WHERE Udid = '$udid'"; 
							
			$result = mysqli_query($dbc,$updatetoken) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 201 The token can not be updated', true, 201);
			}
	    }
		else {
			// insert the token
			$settoken = "insert userlog(User_Id,Udid,Token,Device_Id,Created_Time,Last_Modified) 
			                values('$userid','$udid','$token','$deviceid',NOW(),NOW())";
			$result = mysqli_query($dbc,$settoken) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 202 The token can not be inserted', true, 202);
			}
		}
		$data->close();
		mysqli_close($dbc);
		
	}
	
	// A user can type in a password twice from App or web site to reset the password
	// This is to reset the password entered from a user
	// parameters are passed in instead of body_params
	Protected function setpassword($body_parms) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		 
		$email = $body_parms['email'];
		$token = $body_parms['token'];
		$password = $body_parms['password'];
		 
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	  
        $query = "SELECT Expired_Time FROM resetpassword WHERE Email = '$email' and Token = '$token' and Is_Done = 0";
					
		$data = mysqli_query($dbc, $query);
		
		if (DEBUG_FLAG == 1)
			logserveronce("Setpassword","POST", 'Email: '.$email.' '.'Token: '.$token, "");
		
        if (mysqli_num_rows($data) == 1) {
			// TDB -- check if the time is more than 2 hours
			
			//update the password
			$updatepw = "update user set Password = SHA('$password'),Last_Modified = NOW() WHERE Email = '$email'"; 
						
			$result = mysqli_query($dbc,$updatepw) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 201 Update password failed', true, 201);
				$data2 = json_encode(array('code'=> 201, 'message' => 'Update password failed'));
			    echo $data2;
				exit;
			}
			
			$updatequery = "update resetpassword set Is_Done = 1, Last_Modified = NOW() WHERE Email = '$email' and Token = '$token'"; 
			
			$result = mysqli_query($dbc,$updatequery) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 201 Update password failed', true, 201);
				$data2 = json_encode(array('code'=> 201, 'message' => 'Update password reset table failed'));
			    echo $data2;
				exit;
			}	
		}
		else {
				header('HTTP/1.0 201 Update password failed', true, 201);
				$data2 = json_encode(array('code'=> 201, 'message' => 'No password found'));
			    echo $data2;
				exit;
		}
		
		$data->close();
		mysqli_close($dbc);
		
	}
	
	// this is to invite a user 
	Protected function invite($body_parms) {
	    
	    $dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		mysqli_select_db($dbc, DB_NAME);
		
		$email = $body_parms['email'];
		$username = $body_parms['username'];
		$mobile = $body_parms['mobile'];
		
		$querysearch = "select User_Id from user where Email='$email'";
		$data = mysqli_query($dbc,$querysearch);
		
		if(!isEmptyString($email)) {
			//invite a participant
			if(mysqli_num_rows($data)==0){
			
				$queryinsert = "insert into user(Email,User_Name,Password,User_Type, Mobile, Profile, Verified, Active, Created_Time, Last_Modified)
								 values('$email','$username','','','$mobile','default-profile-pic.png',0, 0, UTC_TIMESTAMP(), UTC_TIMESTAMP())"; 
				
				mysqli_query($dbc,$queryinsert)or die("Error is: \n ".mysqli_error($dbc));		
				
				$data2 = mysqli_query($dbc,$querysearch);			
				$row = mysqli_fetch_array($data2);
				$userid = $row['User_Id'];
				
				// Success
				$data3 = json_encode(array('userid'=> $userid));
				echo $data3;
				
				// logserver if debug flag is set to 1
					if (DEBUG_FLAG == 1)
						logserveronce("Register","POST", $email, $data3);
				$data2->close();
			}
			else {
				// there is already registered
				header('X-PHP-Response-Code: 201', true, 201);
				echo json_encode(array('error message'=>'This user is already existing'));
			}
		}
		else {
			// empty or null for one of user name, password and email
			//header('HTTP/1.0 202 User name, password and email might be empty',true, 202);
			header('X-PHP-Response-Code: 202', true, 202);
			//http_response_code(202);
			echo json_encode(array('error message'=>'empty or null value for one of user name, password and or email'));
		}
		
		$data->close();
		mysqli_close($dbc);
	}
	
	// this is to handle image upload_image
	// It needs to handle the image saving and resize
	// 06/26/2015 
	Protected function upload_image($body_parms) {
		//$allow = array("jpg", "jpeg", "gif", "png");
		$ownerid = $body_parms['ownerid'];
		$extension = $body_parms['extension'];
		$imagedata = $body_parms['data'];
		$data = base64_decode($imagedata);
      
		$im = imagecreatefromstring($data);
		if ($im !== false &!empty($extension) & !empty($ownerid)) {
			// $file_location = 'c:\localweb' . '\\';
			$fileName = FILE_LOCATION . $ownerid . '.' . $extension;
		    //imagepng($im, FILE_LOCATION . $ownerid . '.' . $extension, 0, NULL);
			//$tmpFile = FILE_LOCATION . $ownerid;
			imagepng($im, $fileName, 0, NULL);
			// frees image from memory
			imagedestroy($im);
		}
		else {
			header('HTTP/1.0 202 Error in images', true, 202);
			echo json_encode(array('error message'=>'there is an issue on image upload'));
		}		
	}
	
	// this is to retrieve the latest community/member/event IDs from the server
	// 05/07/2015
	// NOT Used any more 09/16/2015 and a sign in API would return those IDs
	Protected function pgetlastId($ownerid) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		 
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	  
        $query = "SELECT IFNULL(MAX( s.Community_id ), 0) Community_Id, IFNULL(MAX(t.Task_Id ), 0) Task_Id, IFNULL(MAX(sc.event_id), 0) Event_Id, IFNULL(MAX(pg.PGroup_id), 0) PGroup_Id FROM user u ".
					"LEFT JOIN community s ON u.User_Id = s.Creator_Id ".
					"LEFT JOIN task t ON u.User_Id = t.Creator_Id ".
					"LEFT JOIN event sc ON u.User_Id = sc.Creator_Id ".
					"LEFT JOIN participantgroup pg ON u.User_Id = pg.Creator_Id ".
					" WHERE u.User_Id =  '$ownerid'";
		$data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data)==1) {
		    $row = mysqli_fetch_array($data);
			
			$one_arr = array();
			$one_arr['communityid'] = $row['Community_Id'];
			$one_arr['taskid'] = $row['Task_Id'];
			$one_arr['eventid'] = $row['Event_Id'];
			$one_arr['participantgroupid'] = $row['PGroup_Id'];
			
			$data2 = json_encode($one_arr);
			echo $data2;
				
			// logserver if debug flag is set to 1
			if (DEBUG_FLAG == 1)
					logserveronce("GETLASTID","GET", $ownerid, $data2);
	    }
		else {
			// No match in the user table
			header('HTTP/1.0 401 Error in getting the latest IDs', true, 201);
		}
		$data->close();
		mysqli_close($dbc);
	}
	
	// this is to retrieve a user
	Protected function pgetUser($ownerid, $email, $username, $mobile) {
	    
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        $query = "select User_Id userid, User_Name username, Email email, Mobile mobile, Profile profile FROM user u";
		
		if ($email) {
			$query = $query." where u.Email = '$email'";
		}
		else if($username) {
			$query = $query." where u.User_Name like '%$username%' ";
		}
		else if($mobile)	{
			$query = $query." where u.Mobile = '$mobile'";
		}
		else {
			header('HTTP/1.0 402 No input value is provided', true, 402);
			return;
		}
	   
		$data = mysqli_query($dbc, $query);
		
		$return_arr = array();
		$user_arr = array();
		
        if (mysqli_num_rows($data) >= 1) {
			$i = 0;
			
			while($row0 = mysqli_fetch_array($data)){
				   $one_arr = array();
				   $one_arr['id'] = $row0['userid'];
				   $one_arr['username'] = $row0['username'];
				   $one_arr['email'] = $row0['email'];
				   $one_arr['mobile'] = $row0['mobile'];
				   $one_arr['profile'] = PROFILE_SERVER.$row0['profile'];
				   $user_arr[$i] = $one_arr;
				   $i++;			   
			}   
			$return_arr = $user_arr;
			$data2 = json_encode($return_arr);
			echo $data2;
			
			if (DEBUG_FLAG == 1)
					logserveronce("Search","GET", $ownerid, $data2);
	    }
		else {
			// No match in the user table
			header('HTTP/1.0 401 user not found', true, 401);
		}		
		$data->close();
		mysqli_close($dbc);
	}
	
	// update creator's profile 
	// 08/06/2015  
	Protected function update($userid, $creator_parms) {
		$ownerid = $creator_parms['ownerid'];
		$username = $creator_parms['username'];
		$mobile= $creator_parms['mobile'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM user WHERE User_Id = '$userid'";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
	
        if (mysqli_num_rows($data)==1) {
		    // user exists and go ahead to update it
			$queryupdate = "update user set ".
						"User_Name = '$username', Mobile = '$mobile', Last_Modified = UTC_TIMESTAMP(),Last_Modified_Id = '$ownerid' ".
						"where User_Id = '$userid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 201 This user can not be updated', true, 201);
				exit;
			}
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;	
		}
		else {
			header('HTTP/1.0 202 This member doesn\'t exist', true, 202);
		}
		$data->close();
		mysqli_close($dbc);
	}
	
	// API can do two things:
	//   1. GET http://[domain name]/creator/1234  "1234" is the owner id. Get the latest IDs generated by this user (Not use any more)
	//   2. GET http://[domain name]/creator       Search a user by "email", "name", or "Mobile"
	//  03/07/2016  --- /creator/1234 is no longer used; search is in TBD
    public function get($request) {
	   
	    $lastElement = end($request->url_elements);
		reset($request->url_elements);
		
        $ownerid = $lastElement; 
		header('Content-Type: application/json; charset=utf8');
		if ($lastElement == "creator") {
			$email = $request->parameters['email'];
			$username = $request->parameters['username'];
			$mobile = $request->parameters['mobile'];
			$ownerid = $request->parameters['ownerid'];
			$this->pgetUser($ownerid, $email,$username,$mobile);
		}
		else {
			$this->pgetlastId($ownerid);
		}
    }

	// This is the API to register a user in the serve and login in and more ....
	// 07/23/2015 changed action parameter to the last element of URL to accommodate backbone.js  
	// /creator/register; /creator/signin; ......
    public function post($request) {
		header('Content-Type: application/json; charset=utf8');
		
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == 'register') {
			$this->register($request->body_parameters);
		}
		else if ($lastElement == 'signin') {
		    $this->signin($request->body_parameters);
		} 
		else if ($lastElement == 'resetpw') {
		    $this->resetpw($request->body_parameters);
		} 
		else if ($lastElement == 'settoken') {
		    $this->settoken($request->body_parameters);
		} 
		else if ($lastElement == 'setpassword') {
		    $this->setpassword($request->body_parameters);
		} 
		else if ($lastElement == 'invite') {
		    $this->invite($request->body_parameters);
		} 
		else if ($lastElement == 'upload'){
			$this->upload_image($request->body_parameters);
		}
    }

	// this the cases to handle the following cases
	// 1. PUT http://[domain name]/creator/1234   - update the user name and/or mobile
	public function put($request) {
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		reset($request->url_elements);
		
		if ($last2Element == "creator") {
			//this is the case #1
			$userid = end($request->url_elements);
			reset($request->url_elements);
			$this->update($userid, $request->body_parameters);
	    }
		
		
		
	}
}
?>
