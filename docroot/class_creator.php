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
		//$memberid = $body_parms['memberid'];
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
	  
        $query = "SELECT IFNULL(MAX(u.User_Id ), 0) User_Id, u.User_Name User_Name, IFNULL(MAX( s.service_id ), 0) Service_Id, IFNULL(MAX(m.member_id ), 0) Member_Id, IFNULL(MAX(sc.schedule_id), 0) Schedule_Id FROM user u ".
					"LEFT JOIN service s ON u.User_Id = s.Creator_Id ".
					"LEFT JOIN member m ON u.User_Id = m.Creator_Id ".
					"LEFT JOIN schedule sc ON u.User_Id = sc.Creator_Id ".
					" WHERE u.Email =  '$email' AND u.Password = SHA('$password')";
		$data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data)==1) {
		    $row = mysqli_fetch_array($data);
			
			$one_arr = array();
			$one_arr['ownerid'] = $row['User_Id'];
			$one_arr['username'] = $row['User_Name'];
			$one_arr['serviceid'] = $row['Service_Id'];
			$one_arr['memberid'] = $row['Member_Id'];
			$one_arr['scheduleid'] = $row['Schedule_Id'];
			
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
			header('HTTP/1.0 201 Email doesn’t exist', true, 201);
		
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
	
	// this is to retrieve the latest service/member/schedule IDs from the server
	Protected function pgetlastId($ownerid) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		 
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	  
        $query = "SELECT IFNULL(MAX( s.service_id ), 0) Service_Id, IFNULL(MAX(m.member_id ), 0) Member_Id, IFNULL(MAX(sc.schedule_id), 0) Schedule_Id FROM user u ".
					"LEFT JOIN service s ON u.User_Id = s.Creator_Id ".
					"LEFT JOIN member m ON u.User_Id = m.Creator_Id ".
					"LEFT JOIN schedule sc ON u.User_Id = sc.Creator_Id ".
					" WHERE u.User_Id =  '$ownerid'";
		$data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data)==1) {
		    $row = mysqli_fetch_array($data);
			
			$one_arr = array();
			$one_arr['serviceid'] = $row['Service_Id'];
			$one_arr['memberid'] = $row['Member_Id'];
			$one_arr['scheduleid'] = $row['Schedule_Id'];
			
			
			$data2 = json_encode($one_arr);
			echo $data2;
				
			// logserver if debug flag is set to 1
			if (DEBUG_FLAG == 1)
					logserveronce("GETLASTID","GET", $ownerid, $data2);
	    }
		else {
			// No match in the user table
			header('HTTP/1.0 401 Error in getting last IDs', true, 401);
		}
		$data->close();
		mysqli_close($dbc);
	}
	
	// this is the API to get the latest service/member/schedule ID
	// GET http://[domain name]/creator/1234
	// "1234" is the caller's ownerid
    public function get($request) {
	   
	    $lastElement = end($request->url_elements);
		reset($request->url_elements);
		
        $ownerid = $lastElement;
	    
		header('Content-Type: application/json; charset=utf8');
		$this->pgetlastId($ownerid);
    }

	// This is the API to register a user in the servre and login in
    public function post($request) {
		header('Content-Type: application/json; charset=utf8');
	    if ($request->parameters['action'] == 'register') {
			$this->register($request->body_parameters);
		}
		else if ($request->parameters['action'] == 'signin') {
		    $this->signin($request->body_parameters);
		} 
		else if ($request->parameters['action'] == 'resetpw') {
		    $this->resetpw($request->body_parameters);
		} 
		else if ($request->parameters['action'] == 'settoken') {
		    $this->settoken($request->body_parameters);
		} 
		else if ($request->parameters['action'] == 'setpassword') {
		    $this->setpassword($request->body_parameters);
		} 
    }

}
?>
