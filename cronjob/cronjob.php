<?php
	// server different from the dev environemnt
	require_once('constants.php');
	require_once('class_applens.php');
	require_once('class_androidns.php');

	// send an email to some one from godaddy
	function send_mail_godaddy($to, $subject, $body)
	{
		$headers= "From: noreply@cschedule.org\r\n";
		$headers.= "Reply-To:noreply@cschedule.org\r\n";
		$headers.= "Return-Path:noreply@cschedule.org\r\n";
		$headers.= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		mail($to,$subject,$body,$headers);
	}
	
	// This section is for push notification
	$config = array(
	// These are the settings for development mode
	'development' => array(

		// The APNS server that we will use
		'server' => 'gateway.sandbox.push.apple.com:2195',

		// The SSL certificate that allows us to connect to the APNS servers
		'certificate' => 'ck.pem',
		'passphrase' => '12345678@X',

		// Name and path of our log file
		'logfile' => '../'.LOG_LOCATION.'/pushns.log'
		),

	// These are the settings for production mode
	'production' => array(

		// The APNS server that we will use
		'server' => 'gateway.push.apple.com:2195',

		// The SSL certificate that allows us to connect to the APNS servers
		'certificate' => 'ck_production.pem',
		'passphrase' => '12345678@X',

		// Name and path of our log file
		'logfile' => '../'.LOG_LOCATION.'/pushns.log'
		)
	);

	function writeToLog($message)
	{
		global $config;
		if ($fp = fopen($config['logfile'], 'at'))
		{
			fwrite($fp, date('c') . ' ' . $message . PHP_EOL);
			fclose($fp);
		}
	}

	function fatalError($message)
	{
		global $config;
		writeToLog('Exiting with fatal error: ' . $message);
		exit;
	}
   
	// main path
    // Script starts from here
    // 1. Send emails
    // 2. Send notifications
    $mode = PUSH_MODE;
    $config = $config[$mode];
    writeToLog("Start Job");
	$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die('Database Error 2!');
	// call a stored procedure to get the list of members to notify
	$data = mysqli_query($dbc, "CALL emailAlert()") or die("Error is: \n ".mysqli_error($dbc));
	writeToLog("Start sending email"); 
    $number = mysqli_num_rows($data);
    writeToLog("Start numner $number"); 
	if(mysqli_num_rows($data) > 0) {
        //writeToLog("Number of emails = "); 
		while($row0 = mysqli_fetch_array($data)){
		    $member_email = $row0['memail'];
			$member_name = $row0['mname'];
			$service_name = $row0['servicename'];
			$start_datetime = $row0['starttime'];
			$tzname = $row0['tzname'];
			$abbr = $row0['abbr'];
			$description = $row0['descp'];
			$sdescription = $row0['sdescp'];
			$user_name = $row0['uname'];
			$user_email = $row0['uemail'];
			$user_mobile = $row0['umobile'];
			$alertsetting = $row0['alertsetting'];
			
			// convert start datetime to the local date time
			$dateObj = new DateTime($start_datetime, new DateTimeZone('UTC'));
			$dateObj->setTimezone(new DateTimeZone($tzname)); 
			
			$mail_To = $member_email;
			$mail_Body  = '<html>
			<body bgcolor="" topmargin="25">
			Hi ' . $member_name . ',<br>
			<br>
			Your activity '. $service_name . ' is scheduled to occur at ' . $dateObj->format('m-d-Y g:i A') . '(' . $abbr . ')' . ' .<br>
			<br>
			' . $sdescription . '
			<br>
			<br>
			If you have any questions, please contact your activity organizer ' . $user_name . ' at ' . $user_email . ' or phone ' . $user_mobile . ' .<br>
            <br>
			<br>
			To view all schedules for this activity or create your own activity schedules, please use CSchedule for <a href="https://itunes.apple.com/us/app/cschedule/id596231825?mt=8">IPhone</a>
			or <a href="https://play.google.com/store/apps/details?id=com.e2wstudy.cschedule">Android</a>
			<br>
			You can also go to the CSchedule web site http://www.cschedule.com<br> 
			<br>
			<br>
			Thank You,
			<br>
			<br>	
			CSchedule Team<br>
			</body></html>';
			
			$mail_Subject = "Your scheduled activity $service_name is coming up";					
			// mail to customer to inform the payment status
			send_mail_godaddy($mail_To, $mail_Subject, $mail_Body);
			writeToLog("Sent Email to $member_email");     
		} // while end
	} // if end
	  
	$data->close();
	mysqli_close($dbc);	
  
	// this is for Push Notification on both IPhone and Android
	try
	{
		//ini_set('display_errors', 'off');
		//$mode = PUSH_MODE;
		//$config = $config[$mode];
		writeToLog("IOS Push script started ($mode mode)");
		$obj = new Applens($config);
		$obj->start();
		
		writeToLog("Android Push script started");
		$android_obj = new Androidns();
		$android_obj->start();
		
		writeToLog("Finished");
	}
	catch (Exception $e)
	{
		fatalError($e);
	}
    
 ?>
 