<?php
    define('DB_NAME', 'cschedule');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_SERVER', '127.0.0.1');
	define('DEBUG_FLAG', '1');
	
	define('PUSH_MODE', 'development');
	define('LOG_LOCATION', 'log');
	//define('REST_SERVER', 'appitest2.servicescheduler.net');
	define('REST_SERVER', '127.0.0.1:8090');
	define('FEEDBACK_EMAIL', 'ding.dongling@gmail.com');
	
	//Define our id-key pairs
	$applications = array(
	    // for IOS
		'IOS' => '28e336ac6c9423d946ba02dddd6a2632', //randomly generated app key 
		// for Andriod
		'ANDROID' => '28e336ac6c9423d946ba02d19c6a2633', //randomly generated app key 
		// for Web App
		'WEB' => '28e336ac6c9423d946ba02d19c6a2634', //randomly generated app key 
	);
?>