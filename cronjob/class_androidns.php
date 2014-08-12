<?php
require_once('nsconfig.php');
require_once('constants.php');

class Androidns
{
		
	function __construct()
	{
	}
	
	// This is the main loop for this script. It polls the database for new
	// messages, sends them to APNS, sleeps for a few seconds, and repeats this
	// forever (or until a fatal error occurs and the script exits).
	function start()
	{
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		mysqli_select_db($dbc, DB_NAME);
		// Only pick up the item with the device id = 1 (Google NS Service)	
		$queryqueue = "SELECT Pushqueue_Id,Target_Token,Message FROM pushqueue WHERE Sent_Time is Null and Device_Id = 1";
		$data = mysqli_query($dbc,$queryqueue);
		
		if ($data) {
			$num_rows = mysqli_num_rows($data);
		  
			$number = 0;
			while ($row = mysqli_fetch_array($data))  
			{		
					// Gather the $row values into local variables 
					$queueid = $row["Pushqueue_Id"];
					$registration_id = $row["Target_Token"];
					$message = $row["Message"];
			
					if ($this->sendNotification($queueid, $registration_id, $payload))
					{
						$stmt = "UPDATE pushqueue SET Sent_Time = NOW() WHERE Pushqueue_Id = '$queueid'";
						mysqli_query($dbc,$stmt);
						$number++;
					}
					else {
					   writeToLog('Queueid: ' . $queueid . 'failed');
					}
				
					// send 20 messages and take a rest
					if ($number == 20)
					{
						sleep(5);
						$number = 0;
					}
					else 
						$number++;	
			}
		
			// Free the result set  
			mysqli_free_result($data);
		}
		mysqli_close($dbc);
			
	}

	// Sends a notification to the APNS server. Returns FALSE if the connection
	// appears to be broken, TRUE otherwise.
	function sendNotification($queueid, $registration_id, $message)
	{
	   // Set POST variables
       $url = ANDROID_NS_SERVER_URL;
	   // TBD: to use more options
	   //$msg = array(
	   //	'message' 		=> 'here is a message. message',
	   //	'title'			=> 'This is a title. title',
		//	'subtitle'		=> 'This is a subtitle. subtitle',
		//	'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
		//	'vibrate'	=> 1,
		//	'sound'		=> 1
		//);
	   
 
        $fields = array(
            'registration_ids' => $registatoin_id,
            'data' => $message,
        );
 
        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
		writeToLog('Connecting to Android: ' . $url);
		writeToLog('QueueId: ' . $queueid);
		try {
			// Open connection
			$ch = curl_init();
	 
			// Set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);
	 
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	 
			// Disabling SSL Certificate support temporarly
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	 
			// Execute post
			$result = curl_exec($ch);
			if ($result['curl_error'])    throw new Exception($result['curl_error']);
			if ($result['http_code']!='200')    throw new Exception("HTTP Code = ".$result['http_code']);
			if (!$result['body'])        throw new Exception("Body of file is empty");
		}
		catch (Exception $e)
			 writeToLog('Android Message not delivered');
			 writeToLog($e->getMessage());
			 curl_close($ch);
			 return FALSE;
		}
		
		// Close connection
        curl_close($ch);
		writeToLog('Android Message successfully delivered');
		return TRUE;	
	}

}
?>