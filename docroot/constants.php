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
	// define a profile image directory
	define ('PROFILE_SERVER', 'http://127.0.0.1/filesvc/');
	define ('FILE_LOCATION', 'c:\\localweb\\');
	
	
	/*** Test Environment 
    define('DB_NAME', 'testscheduler1');
    define('DB_USER', 'testscheduler1');
    define('DB_PASS', 'Scheduler2012#');
    define('DB_SERVER', 'testscheduler1.db.9936855.hostedresource.com');
	define('DEBUG_FLAG', '1');
	
	define('PUSH_MODE', 'development');
	define('LOG_LOCATION', 'log');
	define('REST_SERVER', 'appitest1.servicescheduler.net');

	define('FEEDBACK_EMAIL', 'ding.dongling@gmail.com');
	// define a profile image directory
	define ('PROFILE_SERVER', 'http://test.cschedule.com/profile/');
	define ('FILE_LOCATION', '../cscheduletest/profile/');
	****/
	
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