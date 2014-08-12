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
		
        $query = " SELECT  t.Id id, t.Tz_Name tzname , t.Display_Name displayname, t.Display_Order displayorder, t.Abbr abbrtzname ".
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
				   $one_arr['abbrtzname'] = $row0['abbrtzname'];
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
	
	Protected function pgetalerts() {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		
        $query = " SELECT  t.Id id, t.Alert_Name aname ".
                 " from alertsetting t ".
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
				   $one_arr['aname'] = $row0['aname'];
				   $services_arr[$i] = $one_arr;
				   $i++;			   
			}   	   
		} // if end
		$return_arr['alerts'] = $services_arr;
         
		$data2 = json_encode($return_arr);
		echo $data2;
      
	    // logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid, $data2);
			
		$data->close();
		mysqli_close($dbc);	
	}
	// get all server settings
	
	Protected function pgetserversetting() {
	
		$return_arr = array();
		$timezones_arr = array();
		$alerts_arr = array();
		$appdevices_arr = array();
		
		// get the list of scheduleid and lastmodified
		$mysql = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		// call a stored procedure to get tnree results
		if ($mysql->multi_query("CALL getServerSetting()")) {
		
		  $h = 0;
		  //loop through three resultsets
          do {
            if ($result = $mysql->use_result())
            {
			    $i = 0;
				$j = 0;
				$k = 0;
                //Loop the two result sets, reading it into an array
                while ($row = $result->fetch_array(MYSQLI_ASSOC))
                {
                    if ($h == 0) {
					   // first resultset --- time zones
					   $one_arr = array();
					   $one_arr['id'] = $row['id'];
					   $one_arr['tzname'] = $row['tzname'];
				       $one_arr['displayname'] = $row['displayname'];
				       $one_arr['displayorder'] = $row['displayorder'];
				       $one_arr['abbrtzname'] = $row['abbrtzname'];

					   $timezones_arr[$i] = $one_arr;
					   $i++;			       
					}
					else  if ($h == 1) {
					   // second resultset  --- Alerts
						$two_arr = array();
						$two_arr['id'] = $row['id'];
						$two_arr['aname'] = $row['aname'];
						
					    $alerts_arr[$j] = $two_arr;
						$j++;
					}
					else {
						// thir resultset  -- App version
						$three_arr = array();
						$three_arr['id'] = $row['id'];
						$three_arr['appversion'] = $row['appversion'];
						$three_arr['enforce'] = $row['enforce'];
						$three_arr['os'] = $row['os'];
						$three_arr['osversion'] = $row['osversion'];
						$three_arr['msg'] = $row['msg'];
						
						$appdevices_arr[$k] = $three_arr;
					    $k++;
					}
                } // while end
				
                // Close the result set
                $result->close();
				$h++;
            }
          } while ($mysql->more_results() == TRUE && $mysql->next_result());
        }
        else
        {
            echo '<strong>Error Message ' . $mysql->error . '</strong></p>';
        }
		
	    $return_arr['timezones'] = $timezones_arr;
		$return_arr['alerts'] = $alerts_arr;
		$return_arr['appversions'] = $appdevices_arr;
         
		$data2 = json_encode($return_arr);
		echo $data2;
		
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid,$data2);
     
		mysqli_close($mysql);		
	}
	
	// GET method here is to handle 1 case to get server setting on 
	//  1. http://[REST_SERVER]/serversetting/
	public function get($request) {
	   
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
	
		reset($request->url_elements);
		
		$this->pgetserversetting();
	
    }

	
	
}
?>
