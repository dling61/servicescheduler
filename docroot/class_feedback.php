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
			$to = FEEDBACK_EMAIL;
			
			send_mail_godaddy($to, $subject, $feedback);
	    }
	
		$data->close();
		mysqli_close($dbc);
	
    }
	
    // This is the API to send the feedback to the server
	// POST http://servicescheduler.net/feedback
    public function post($request) {
		header('Content-Type: application/json; charset=utf8');
		$this->sendFeedback($request->body_parameters['ownerid'], $request->body_parameters['feedback']);  
    }
	

}
?>