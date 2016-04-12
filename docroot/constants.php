<?php
    require_once('config.php');
	
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
	
?>