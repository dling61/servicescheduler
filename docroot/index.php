<?php 
   
	require_once('constants.php');
    require_once('class_creator.php');
	require_once('class_members.php');
	require_once('class_services.php');
	require_once('class_schedules.php');
    
	// assume autoloader available and configured

	$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
	$query = parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY);
	$action = "";
 
	$path = trim($path, "/");
	echo $path;
	
	// TDB:this is the place to get the resouce; in the release it should "2" instead of 3
	// without "dump"
	@list($dump, $resource, $params) = explode("/", $path, 3);
    
	$resource = ucfirst(strtolower($resource));

	$method = strtolower($_SERVER["REQUEST_METHOD"]);

	$params = !empty($params) ? explode("/", $params) : array();
	
	// deal with creator resource
	if ($resource == "Creator") {
		@list($dump, $action) = explode("=", $query, 2); 
    }
	echo "start";
	echo $resource;
	echo "end";
	if (class_exists($resource)) {
		try {
			// For creator resources
		    if ($resource == "Creator") {
			    echo $resource;
				$resource = new $resource($action);
				$resource->{$method}();
			}
			// For other resources, member, service, schedule
			else {
			    if ($method == "get") {
				    parse_str($query, $out);
					$resource = new $resource($out);
					$resource->{$method}();
				} else {
				    $resource = new $resource($params);
					$resource->{$method}();
				}
			}
		}
		catch (Exception $e) {
			header("HTTP/1.1 500 Internal Server Error");
		}

	}
	else {
		header("HTTP/1.1 404 File Not Found");
	}

?>