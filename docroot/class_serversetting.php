<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');
require_once('class_request.php');

class Serversetting Extends Resource
{
	
   public function __construct($request) {
        parent::__construct($request);
	}	
	
	//protected $lastid;
	
	Protected function pgetltimezones() {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		
        $query = " SELECT  t.Id id, t.Tz_Name tzname , t.Display_Name displayname, t.Display_Order displayorder ".
                 " from timezonedb t ".
                 " where t.Active_Flag = 1";
		
		$data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
		$return_arr = array();
		$serviceid_arr = array();
		$services_arr = array();
		
		if(mysqli_num_rows($data) > 0) {
			$i = 0;
			$j = 0;
			while($row0 = mysqli_fetch_array($data)){
				   $one_arr = array();
				   $one_arr['id'] = $row0['id'];
				   $one_arr['tzname'] = $row0['tzname'];
				   $one_arr['displayname'] = $row0['displayname'];
				   $one_arr['displayorder'] = $row0['displayorder'];
				   $services_arr[$i] = $one_arr;
				   $i++;			   
			}   	   
		} // if end
		$return_arr['timezones'] = $services_arr;
         
		$data2 = json_encode($return_arr);
		echo $data2;
      
	    // logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid, $data2);
			
		$data->close();
		mysqli_close($dbc);	
	}
	
	
	// GET method here is to handle 2 cases to get server setting on 
	//  1. http://[REST_SERVER]/serversetting/timezones
	//  2. http://[REST_SERVER]/serversetting/alerts
	
	public function get($request) {
	   
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
	
		reset($request->url_elements);
		if ($lastElement == "timezones") {
			// get the list of time zones
			$this->pgetltimezones();
		}
		else if ($lastElement == "alerts") {
			// handle sharedmembers
			$this->pgetalerts();
		} 
    }

	
	
}
?>
