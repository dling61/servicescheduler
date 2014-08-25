<?php
class Applens
{
    private $fp;
	private $server;
	private $certificate;
	private $passphrase;
		

	function __construct($config)
	{
		$this->fp = null;
		$this->server = $config['server'];
		$this->certificate = $config['certificate'];
		$this->passphrase = $config['passphrase'];

	}
	
	// This is the main loop for this script. It polls the database for new
	// messages, sends them to APNS, sleeps for a few seconds, and repeats this
	// forever (or until a fatal error occurs and the script exits).
	function start()
	{
		writeToLog('Connecting to ' . $this->server);
		if ($this->connectToAPNS())
		{	
			$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
			mysqli_select_db($dbc, DB_NAME);
			// Only pick up the items with the device Id = 0 (Apple NS)
			$queryqueue = "SELECT Pushqueue_Id,Target_Token,Message FROM pushqueue WHERE Sent_Time is Null and Device_Id = 0";
			$data = mysqli_query($dbc,$queryqueue);
			
			if ($data) {
				$num_rows = mysqli_num_rows($data);
			  
				$number = 0;
				while ($row = mysqli_fetch_array($data))  
				{		
						// Gather the $row values into local variables 
						$queueid = $row["Pushqueue_Id"];
						$token	 = $row["Target_Token"];
						$message = $row["Message"];
						// Create the payload body
						$body['aps'] = array(
										'alert' => $message,
										'sound' => 'default'
									);

						// Encode the payload as JSON
						$payload = json_encode($body);
						if ($this->sendNotification($queueid, $token, $payload))
						{
							$stmt = "UPDATE pushqueue SET Sent_Time = NOW() WHERE Pushqueue_Id = '$queueid'";
							mysqli_query($dbc,$stmt);
							$number++;
						}
						else  // failed to deliver
						{
							$this->reconnectToAPNS();
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
			$this->disconnectFromAPNS();
		}	
	}

	// Opens an SSL/TLS connection to Apple's Push Notification Service (APNS).
	// Returns TRUE on success, FALSE on failure.
	function connectToAPNS()
	{
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $this->certificate);
		stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);

		$this->fp = stream_socket_client(
			'ssl://' . $this->server, $err, $errstr, 60,
			STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		if (!$this->fp)
		{
			writeToLog("Failed to connect: $err $errstr");
			return FALSE;
		}

		writeToLog('Connection OK');
		return TRUE;
	}

	// Drops the connection to the APNS server.
	function disconnectFromAPNS()
	{
		fclose($this->fp);
		$this->fp = NULL;
	}

	// Attempts to reconnect to Apple's Push Notification Service. Exits with
	// an error if the connection cannot be re-established after 3 attempts.
	function reconnectToAPNS()
	{
		$this->disconnectFromAPNS();
	
		$attempt = 1;
	
		while (true)
		{
			writeToLog('Reconnecting to ' . $this->server . ", attempt $attempt");

			if ($this->connectToAPNS())
				return;

			if ($attempt++ > 3)
				fatalError('Could not reconnect after 3 attempts');

			sleep(60);
		}
	}

	// Sends a notification to the APNS server. Returns FALSE if the connection
	// appears to be broken, TRUE otherwise.
	function sendNotification($messageId, $deviceToken, $payload)
	{
		if (strlen($deviceToken) != 64)
		{
			writeToLog("Message $messageId has invalid device token");
			return TRUE;
		}

		if (strlen($payload) < 10)
		{
			writeToLog("Message $messageId has invalid payload");
			return TRUE;
		}

		writeToLog("Sending message $messageId to '$deviceToken', payload: '$payload'");

		if (!$this->fp)
		{
			writeToLog('No connection to APNS');
			return FALSE;
		}

		// The simple format
		$msg = chr(0)                       // command (1 byte)
			 . pack('n', 32)                // token length (2 bytes)
			 . pack('H*', $deviceToken)     // device token (32 bytes)
			 . pack('n', strlen($payload))  // payload length (2 bytes)
			 . $payload;                    // the JSON payload

	
		// The enhanced notification format
		//$msg = chr(1)                       // command (1 byte)
		//	 . pack('N', $messageId)        // identifier (4 bytes)
		//	 . pack('N', time() + 86400)    // expire after 1 day (4 bytes)
		//	 . pack('n', 32)                // token length (2 bytes)
		//	 . pack('H*', $deviceToken)     // device token (32 bytes)
		//	 . pack('n', strlen($payload))  // payload length (2 bytes)
		//	 . $payload;                    // the JSON payload

		$result = @fwrite($this->fp, $msg, strlen($msg));

		if (!$result)
		{
			writeToLog('Message not delivered');
			return FALSE;
		}

		writeToLog('Message successfully delivered');
		return TRUE;
	}

}
?>