<?php

require_once('constants.php');

function send_mail_godaddy($to, $subject, $body)
// send an email to some one from godaddy
{
	$headers= "From: noreply@cschedule.org\r\n";
	$headers.= "Reply-To:noreply@cschedule.org\r\n";
	$headers.= "Return-Path:noreply@cschedule.org\r\n";
	$headers.= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	$headers.= "CC: info@cschedule.org";
	mail($to,$subject,$body,$headers);
}

function logserver($resource, $method, $body)
{
	//$timestamp = date('Y-m-d H:i:s');
	$lastid = 0;
	$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	$queryinsert1 = "insert into serverlog".
						 "(URL_Resource, Action,Body,Created_DateTime) ".
						 "values('$resource','$method','$body', UTC_TIMESTAMP())";
	$result = mysqli_query($dbc,$queryinsert1) or die("Error is: \n ".mysqli_error($dbc));
	if ($result !== TRUE) {
	  // if error, roll back transaction
		header("HTTP/1.1 501 Internal Server Error");	
	}
	$lastid = mysqli_insert_id($dbc);
	mysqli_close($dbc);
	return $lastid;
}
	
 function logserver_response($lastid, $response)
{
    $in_response = (strlen($response) > 4096) ? substr($response,0,4096): $response;
	$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	 
	$queryupdate = "update serverlog set ".
						 "Response = '$in_response' ".
						 "where Log_Id = '$lastid'";
	$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
	if ($result !== TRUE) {
	  // if error, roll back transaction
		header("HTTP/1.1 501 Internal Server Error");	
	}
	
	mysqli_close($dbc);
}
 
 function logserveronce($resource, $method, $body, $response)
{
	//$timestamp = date('Y-m-d H:i:s');
	$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	$queryinsert1 = "insert into serverlog".
						 "(URL_Resource, Action,Body,Response,Created_DateTime) ".
						 "values('$resource','$method','$body','$response', UTC_TIMESTAMP())";
	$result = mysqli_query($dbc,$queryinsert1) or die("Error is: \n ".mysqli_error($dbc));
	if ($result !== TRUE) {
	  // if error, roll back transaction
		header("HTTP/1.1 501 Internal Server Error");	
	}
	$lastid = mysqli_insert_id($dbc);
	mysqli_close($dbc);
}
 
 
 function set_cookies()
 // set the cookies for paypal returned PDT
 {
	setcookie('user_id', $_SESSION['user_id'], time() + (60 * 60 * 24 ));    // expires in one day
    setcookie('firstname', $_SESSION['firstname'], time() + (60 * 60 * 24 ));  // expires in one day
	setcookie('usertype', $_SESSION['usertype'], time() + (60 * 60 * 24 ));  // expires in one day
 }
 
 function get_utc_time()
 // a common code for all registered users
 {
   $utc_str = gmdate("Y-m-d H:i:s", time());
   $utc = strtotime($utc_str);
   echo $utc_str;
 }
 
 // send an email to the request for resetting password
 function resetpw_email($email, $token) {
    
	$mail_Body  = '<html>
	<body bgcolor="" topmargin="25">
	<br>
	We have received a password change request for your CSchedule account: '. $email . '.<br>
	<br>
	<br>
	If you made this request, then please click on the link below.
	<br>
	<br>
     <a href="http://'.REST_SERVER.'/cschedule/resetpw.php?email='.$email.'&sig='.$token.'">Reset Password</a><br>
	<br>
	<br>
	This link will work for 2 hours or until you reset your password.<br>
	<br>
    If you did not ask to change your password, then please ignore this email. Another user may <br>
	have entered your username by mistake. No changes will be made to your account.
    <br>
	Thank You,
	<br>
	<br>	
	CSchedule Team<br>
	</body></html>';

	$mail_Subject = "CSchedule password change request";
    $mail_To = $email;	
	// mail to customer to inform the payment status
	send_mail_godaddy($mail_To, $mail_Subject, $mail_Body);
 
 }
 
?>