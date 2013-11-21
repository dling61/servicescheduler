<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');
require_once('class_request.php');

//
// this is to send feedback to the CSchedule
// 11/20/2013 dding
class Feedback Extends Resource
{
  	public function __construct($request) {
		 parent::__construct($request);
	}	
	
	
	/**
	    This method is to send out the feedback to the CSchedule
	**/
	Protected function sendFeedback($ownerid, $feedback) {
	    
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		 
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	  
        $query = "Select Email from user where user_id = '$ownerid' ";
		$data = mysqli_query($dbc, $query);
		
		$subject = "Feedback for CSchedule from ";
		
        if (mysqli_num_rows($data)==1) {
		    $row = mysqli_fetch_array($data);
			$email = $row['Email'];
			
			$subject = $subject.'$email';
			$to = "ding.dongling@gmail.com";
			
			send_mail_godaddy($to, $subject, $feedback)
	    }
	
	
    }
	
    // This is the API to send the feedback to the server
	// POST http://servicescheduler.net/feedback
    public function post($request) {
		$parameters1 = array();
      
		if ($request->body_parameters ['members']) {
			foreach($request->body_parameters['members'] as $param_name => $param_value) {
					$parameters1[$param_name] = $param_value;
			}
		}
		header('Content-Type: application/json; charset=utf8');
		$this->sendFeedback($request->body_parameters['ownerid'], $request->body_parameters['feedback']);  
    }
	

}
?>