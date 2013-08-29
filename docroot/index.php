<?php 
	require_once('constants.php');
    require_once('class_creator.php');
	require_once('class_members.php');
	require_once('class_services.php');
	require_once('class_schedules.php');
	require_once('class_request.php');
	
	// 08/16/2013: Moved this to GitHub for source code version control
	
	//Define our id-key pairs
	$applications = array(
	    // for IOS
		'APP001' => '28e336ac6c9423d946ba02dddd6a2632', //randomly generated app key 
		// for Andriod
		'APP002' => '28e336ac6c9423d946ba02d19c6a2632', //randomly generated app key 
		// for Web App
		'APP003' => '28e336ac6c9423d946ba02d19c6a2632', //randomly generated app key 
	);

	//get all the information from http call
	$request = new Request();

	// TDB:this is the place to get the controller(resource); in the release it should "1" instead of 2 for url_element[x]
	// 
	//  For testing:
	// http://127.0.0.1/cschedule/creator?
	// url_elements[2]
	//  For production or testing environment on hosting service
	// http://servicescheduler.net/creator?
	// url_element1[1]
	$controller_name = ucfirst($request->url_elements[2]);
	
	if (class_exists($controller_name)) {
		$controller = new $controller_name();
		$action_name = strtolower($request->action);
		$result = $controller->$action_name($request);
    }

	
?>