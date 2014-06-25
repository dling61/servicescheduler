<?php 
	require_once('constants.php');
    require_once('class_creator.php');
	require_once('class_members.php');
	require_once('class_services.php');
	require_once('class_schedules.php');
	require_once('class_request.php');
	require_once('class_feedback.php');
	require_once('class_serversetting.php');
	
	// 08/16/2013: Moved this to GitHub for source code version control
	
	//Define our id-key pairs
	$applications = array(
	    // for IOS
		'IOS' => '28e336ac6c9423d946ba02dddd6a2632', //randomly generated app key 
		// for Andriod
		'ANDROID' => '28e336ac6c9423d946ba02d19c6a2633', //randomly generated app key 
		// for Web App
		'WEB' => '28e336ac6c9423d946ba02d19c6a2634', //randomly generated app key 
	);

	//get all the information from http call
	$request = new Request();
	
	// check security code
	checkscode($request, $applications);

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
		$controller = new $controller_name($request);
		$action_name = strtolower($request->action);
		$result = $controller->$action_name($request);
    }

	// d --- device; sc -- security code
	function checkscode($request, $applications) {
	    if (isset($request->parameters['d']) and isset($request->parameters['sc'])) {
			$_device = $request->parameters['d'];
			$_scode =  $request->parameters['sc'];
			
			if ($applications[$_device] != $_scode) {
				header('Content-Type: application/json; charset=utf8');
				header('HTTP/1.0 204 Error in security code', true, 206);
				exit;
			}
		}
		else {
			header('Content-Type: application/json; charset=utf8');
			header('HTTP/1.0 204 Error in security code', true, 206);
			exit;
		}
	}
	
?>