<?php

    /**
	 *    Database Connection
	 *    TBD: Change them for the environment
	 */
    define('DB_NAME', 'cschedule2016');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_SERVER', '127.0.0.1');
    define('DEBUG_FLAG', '0');
	
	
	/**
	 *   Profile Image Location
	 *   TBD: Change them for the environment
	 */
	define ('PROFILE_SERVER', 'http://test.cschedule.com/profile/');
	define ('FILE_LOCATION', '../cscheduletest/profile/');
	
	/**
	 *    Session management
	 */
	define('SESSION_UID', 'uid');
	define('SESSION_PREFERENCE', 'preference_id');
	define('SESSION_REMEMBER', 'remember_me');
	define('SESSION_TIMESTAMP', 'last_activity');

	define('REMEMBER_ME_YES', '1');
	define('REMEMBER_ME_NO', '0');
	define('LONG_SESSION', '5184000');
	define('NORMAL_SESSION', '1800');
	
	define('LOGIN_SESSION_EXPIRE', '-1');
	// TBD: Change them for the environment
	define('CLIENT_SERVER', 'http://www.cschedule.org');
	
	/**
	 *  IOS push notification 
	 */
	define('PUSH_MODE', 'development');

	/**
	 *   Misc configuration
	 */
	//define('REST_SERVER', 'appitest2.servicescheduler.net');
	define('REST_SERVER', '127.0.0.1');
	define('FEEDBACK_EMAIL', 'ding.dongling@gmail.com');
	define('LOG_LOCATION', 'log');
	
	/**
	 *    Security Keys
	 */
	$applications = array(
	    // for IOS
		'IOS' => '28e336ac6c9423d946ba02dddd6a2632', //randomly generated app key 
		// for Andriod
		'ANDROID' => '28e336ac6c9423d946ba02d19c6a2633', //randomly generated app key 
		// for Web App
		'WEB' => '28e336ac6c9423d946ba02d19c6a2634', //randomly generated app key 
	);
?>