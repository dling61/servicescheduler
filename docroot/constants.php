<?php
    define('DB_NAME', 'duplicate');
    define('DB_USER', 'root');
    define('DB_PASS', 'first1mysql');
    define('DB_SERVER', '127.0.0.1');
	define('DEBUG_FLAG', '0');
	
	define('PUSH_MODE', 'development');
	define('LOG_LOCATION', 'log');
	//define('REST_SERVER', 'appitest2.servicescheduler.net');
	define('REST_SERVER', '127.0.0.1');
	define('FEEDBACK_EMAIL', 'michaelrobtemp@gmail.com');

	// new constant for session
	define('SESSION_UID', 'uid');
	define('SESSION_PREFERENCE', 'preference_id');
	define('SESSION_REMEMBER', 'remember_me');
	define('SESSION_TIMESTAMP', 'last_activity');

	define('REMEMBER_ME_YES', '1');
	define('REMEMBER_ME_NO', '0');
	define('LONG_SESSION', '5184000');
	define('NORMAL_SESSION', '1800');
	
	define('LOGIN_SESSION_EXPIRE', '-1');
	
	
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