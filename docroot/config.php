<?php


    /**
	 *    Database Connection
	 *    TBD: Change them for the environment
	 */
    //define('DB_NAME', 'cschedule2016');
    //define('DB_USER', 'root');
    //define('DB_PASS', '');
    //define('DB_SERVER', '127.0.0.1');

    define('DB_NAME', 'stage');
    define('DB_USER', 'production0324');
    define('DB_PASS', 'Fdy-tNK-twJ-c79');
    define('DB_SERVER', 'cscheduledb.cosvtcebil3t.us-west-2.rds.amazonaws.com');
	
	define('DEBUG_FLAG', '0');
	
	/**
	 *   Profile Image Location
	 *   TBD: Change them for the environment
	 */
	define ('PROFILE_SERVER', 'http://test.cschedule.com/profile/');
	define ('FILE_LOCATION', '../cscheduletest/profile/');
	
	/**
	 *  Web Client Site Address
	 *  TBD: Change them for the environment
	 */
	define('WEB_SERVER', 'http://www.cschedule.org');
	
	/**
	 *   Top Level Directory
	 *   TBD: Change it for the environment
	 */
	 define('TOP_DIR', '/var/www/html/');
	 
	 /**
	  *   Facebook Integration
	  *   TBD: Change it for the environment
	  */
	 define('FACEBOOK_CALLBACK', '/var/www/html/api/session/facebook/fbcallback.php');
	 
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