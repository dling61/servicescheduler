<?php

	require_once('constants.php');
	
	/**
	* Start the login session
	* When a user log in with correct username & password pair, this function should be called.
	*
	* @author     Long Zhen
	* @version    0.1
	*/
	function session_init($uid, $remember_me)
	{
		if ($remember_me) {
			// remember me, therefore, two month cookie & two month TTL 
			ini_set('session.gc_maxlifetime', LONG_SESSION);
			session_set_cookie_params(LONG_SESSION);
			session_start();
		} else {
			// no remember me, session cookie & 30 min TTL
			ini_set('session.gc_maxlifetime', NORMAL_SESSION);
			session_start();
		}

		$_SESSION[SESSION_UID] = $uid;
		$_SESSION[SESSION_PREFERENCE] = uniqid();
		$_SESSION[SESSION_REMEMBER] = $remember_me;
		$_SESSION[SESSION_TIMESTAMP] = time();
	}

	/**
	* Check login session and return the user id
	* Before any user request is executed, this function should be called to validate the session.
	* If no valid session information is found, LOGIN_SESSION_EXPIRE is returned.
	* After calling this function, all the session variables can be accessed directly.
	* Session variable keys are the following:
	* 	SESSION_UID
	* 	SESSION_PREFERENCE
	*	SESSION_REMEMBER
	*	SESSION_TIMESTAMP
	*
	* @author     Long Zhen
	* @version    0.1
	*/
	function get_session_uid()
	{
		session_start();
		if (isset($_SESSION[SESSION_UID])) {
		    // session exist, not expire, operation target legal
		    if ($_SESSION[SESSION_REMEMBER]) {
		    	ini_set('session.gc_maxlifetime', LONG_SESSION);
		    	session_set_cookie_params(LONG_SESSION);
		    } else {
		    	ini_set('session.gc_maxlifetime', NORMAL_SESSION);
		    }
		    
			// This is to control whether to regenerate ID
		    session_regenerate_id(false);
		    $_SESSION[SESSION_TIMESTAMP] = time();

		    return $_SESSION[SESSION_UID];
		}

		return LOGIN_SESSION_EXPIRE;
	}

	/**
	* Destroy the login session 
	* When a user log out, call this function to clear all the data related to this session.
	*
	* @author     Long Zhen
	* @version    0.1
	*/
	function session_end()
	{
		session_start();
		session_unset();
    	session_destroy();
	}
?>