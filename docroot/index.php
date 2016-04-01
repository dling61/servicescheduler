<?php 
	require_once('constants.php');
    require_once('class_creator.php');
	//require_once('class_members.php');
	//require_once('class_services.php');
	//require_once('class_schedules.php');
	require_once('class_request.php');
	require_once('class_feedback.php');
	require_once('class_serversetting.php');
	//API 1.5
	require_once('class_community.php');
	require_once('class_event.php');
	require_once('class_task.php');
	require_once('class_participant.php');
	require_once('class_participantgroup.php');
	require_once('class_taskhelper.php');
	require_once('class_baseevent.php');
	require_once('class_repeatschedule.php');
	require_once('class_assignmentpool.php');
	require_once('sessions.php');
	
	// 08/16/2013: Moved this to GitHub for source code version control

	//get all the information from http call
	$request = new Request();
	
	// check security code
	checkscode($request, $applications);
	$lastELement = end($request->url_elements);
	reset($request->url_elements);
	$uid = get_session_uid();
	if ($lastELement != "signin" && $uid == LOGIN_SESSION_EXPIRE) {
		header('Content-Type: application/json; charset=utf8');
		header('HTTP/1.0 440 Login Timeout', true, 440);
		exit;
	}
	// TDB:this is the place to get the controller(resource); in the release it should "1" instead of 2 for url_element[x]
	// 
	//  For testing:
	// http://127.0.0.1/cschedule/creator?
	// url_elements[2]
	//  For production or testing environment on hosting service
	// http://servicescheduler.net/creator?
	// url_element1[1]
	$controller_name = ucfirst($request->url_elements[1]);
	
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
    header('Access-Control-Allow-Origin: *');
	
	if (class_exists($controller_name)) {
		$controller = new $controller_name($request);
		$action_name = strtolower($request->action);
		$result = $controller->$action_name($request);
    }
    //header("Access-Control-Allow-Origin: *");
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