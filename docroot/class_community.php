<?php

require_once('class_resource.php');
require_once('constants.php');
require_once('common_fn.php');
require_once('class_request.php');

class Community Extends Resource
{
	
   public function __construct($request) {
        parent::__construct($request);
	}	
	
	//protected $lastid;
	
	// create a new community and insert the creator to the participant table
	// 7/17/2015 --- Change the request body to one list of elements
	Protected function insert($community_parms) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		$ownerid = $community_parms['ownerid'];
		$communityid = $community_parms['communityid'];
		$communityname = $community_parms['communityname'];
		$description = $community_parms['desp'];
		// participantid -- this is needed to insert a participant into the participant table as the default participant
	    $participantid = $community_parms['participantid'];
		
		$query = "SELECT Community_Id FROM community WHERE Community_Id = $communityid";
        $data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data)==1) {
		    // community already exists
			header('HTTP/1.0 201 This community exists already', true, 201);
			exit;
	    }
		else {
			// Insert this community if no exists
			$queryinsert = "INSERT INTO community ".
								"(Community_Id,Community_Name,Description,Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
								" values('$communityid','$communityname','$description','$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(), '$ownerid')";
			
			$result = mysqli_query($dbc,$queryinsert) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 202 This community can not be added', true, 202);
				Exit;
			}
			
			// insert the creator to the community if it doesn't exist
			$this->insert_creator($ownerid, $communityid, $participantid);
			
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
            echo $data2;			
		}
		$data->close();
		mysqli_close($dbc);
	}

	// 07/18/2015 Change the request body to one list of elements
	Protected function update($communityid, $community_parms) {
		$ownerid = $community_parms['ownerid'];
		$communityname = $community_parms['communityname'];
		$description= $community_parms['desp'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM community WHERE Community_Id = '$communityid'";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
	
        if (mysqli_num_rows($data)==1) {
		    // community exists and go ahead to update it
			$queryupdate = "update community set ".
						"Community_Name = '$communityname', Description = '$description', Last_Modified = UTC_TIMESTAMP(),Last_Modified_Id = '$ownerid' ".
						"where Community_Id = '$communityid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 201 This member can not be updated', true, 201);
				exit;
			}
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			//echo json_encode($data2);
			echo $data2;
			
			
		}
		else {
			header('HTTP/1.0 202 This member doesn\'t exist', true, 202);
		}
		$data->close();
		mysqli_close($dbc);
	}
	
	// This is to update the role of a participant in the community
	Protected function update_sm($communityid, $userid, $body_parms) {
		$ownerid = $body_parms['ownerid'];
		$userrole= $body_parms['userrole'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM participant WHERE Community_Id = '$communityid' and User_Id = '$userid' and Is_Deleted = 0 ";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
        if (mysqli_num_rows($data)==1) {
		    // shared member exists and go ahead to update it
			$queryupdate = "update participant set ".
						"User_Role = '$userrole', Last_Modified = UTC_TIMESTAMP, Last_Modified_Id = '$ownerid'".
						"WHERE Community_Id = '$communityid' and User_Id = '$userid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 201 This shared member can not be updated', true, 201);
				exit;
			}
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;	
		}
		else {
			header('HTTP/1.0 202 This shared member doesn\'t exist', true, 202);
			$data2 = json_encode(array('code'=> 202, 'message' => 'This shared member doesn\'t exist'));
			echo $data2;
		}
		
		$data->close();
		mysqli_close($dbc);	
	}
	
	// delete a community
	// 03/05/2016  We only soft delete a community without touching event/task/participants
	Protected function pdelete($communityid, $ownerid) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		// only owner can delete a community
		$query = "SELECT * FROM community WHERE Community_Id = '$communityid' and Creator_id = '$ownerid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This community doesn\'t exist or has been deleted', true, 201);
			exit;
	    }
		else {
		
			// Delete this community by setting the flag Is_Deleted to 1
			$queryupdate = "update community set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid'".
						" where Community_Id = '$communityid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 This community can\'t be deleted', true, 202);
				exit;
			}		   
		}
		
		$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
		echo $data2;
		$data->close();
		mysqli_close($dbc);
	}
	
	// remove a sharing from a member with a community
	Protected function pdelete_participant($communityid, $userid, $ownerid) {
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		$query = "SELECT * FROM participant WHERE Community_Id = '$communityid' and User_Id = '$userid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This participant doesn\'t shared with the community', true, 201);
			exit;
	    }
		else {
			// Delete this participant by setting the flag Is_Deleted to 1
			$update = "update participant set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
						" where Community_Id = '$communityid' and User_Id = '$userid'";
			$result = mysqli_query($dbc,$update) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				header('HTTP/1.0 204 Failed to delete shared member', true, 204);
				exit;
			}
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;
		}
	}
	
	// This is a function to get the communities a user is participating 
	//  03/05/2016 Move the highest mark IDs to the login function
	Protected function pgetlastupdate($ownerid, $lastupdatetime) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
	    /**
		$query = " SELECT distinct s.Community_Id serviceid ,s.Community_Name servicename,s.Description descp ".
                 " ,s.Creator_Id creatorid, ".
                 " if (s.Is_Deleted = 1 or o.Is_Deleted = 1, 1, 0) isdeleted,s.Created_Time createdtime,s.Last_Modified lastmodified,o.User_Role userrole ".
                 " from community s, participant o ".
                 " where s.Community_Id = o.Community_Id ".
                 " and o.User_Id = '$ownerid' ". 
                 " and ((o.Last_Modified > '$lastupdatetime') or (s.Last_Modified > '$lastupdatetime'))";
		***/
		$query = " SELECT distinct s.Community_Id communityid ,s.Community_Name communityname,s.Description descp ".
                 " ,s.Creator_Id creatorid, ".
                 " if (s.Is_Deleted = 1 or o.Is_Deleted = 1, 1, 0) isdeleted,s.Created_Time createdtime,s.Last_Modified lastmodified,o.User_Role userrole ".
                 " from community s, participant o ".
                 " where s.Community_Id = o.Community_Id ".
                 " and o.User_Id = '$ownerid' ". 
                 " and ((o.Last_Modified > '$lastupdatetime') or (s.Last_Modified > '$lastupdatetime'))";
		
		$data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
		$return_arr = array();
		$serviceid_arr = array();
		$services_arr = array();
		
		if(mysqli_num_rows($data) > 0) {
			$i = 0;
			$j = 0;
			while($row0 = mysqli_fetch_array($data)){
			   $isdeleted = $row0['isdeleted'];
				// if it's deleted, just add it to "dservices"
			   if ($isdeleted == 1) {
				 $serviceid_arr[$j] = $row0['communityid'];
				 $j++;
			   }
			   else {
				   $one_arr = array();
				   $one_arr['id'] = $row0['communityid'];
				   $one_arr['communityname'] = $row0['communityname'];
				   $one_arr['desp'] = $row0['descp'];
				   $one_arr['creatorid'] = $row0['creatorid'];
				   $one_arr['createdtime'] = $row0['createdtime'];
				   $one_arr['lastmodified'] = $row0['lastmodified'];
				   $one_arr['userrole'] = $row0['userrole'];
				   
				   $services_arr[$i] = $one_arr;
				   $i++;			   
			   }   
			   
			} // while end
		} // if end
		//$return_arr['deletedcmty'] = $serviceid_arr;
		//$return_arr['community'] = $services_arr;
         
		$data2 = json_encode($services_arr);
		echo $data2;
      
	    // logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid, $data2);
			
		$data->close();
		mysqli_close($dbc);	
	}
	
	// This is to get list of participants
	// API 1.5 04/20/2015
	Protected function pgetlastupdate_sm($communityid, $ownerid, $lastupdatetime) {
	    
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
	
		// call a stored procedure to get the services to be returned to caller
		$query = "SELECT sm.Is_Deleted isdeleted, sm.User_Id userid, m.Email useremail, m.User_Name username, m.Mobile mobilenumber, m.Profile userprofile, ".
		    " sm.Creator_Id creatorid, sm.Community_Id communityid, sm.Created_Time createdtime, sm.Last_Modified lastmodified, sm.User_Role userrole ".
 		    " FROM participant sm, user m ".
			"where sm.User_Id = m.User_Id and sm.Community_Id = '$communityid' and ".
     		"(sm.Last_Modified > '$lastupdatetime' or m.Last_Modified > '$lastupdatetime')";
		
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc)); 
		
		$return_arr = array();
		$memberid_arr = array();
		$members_arr = array();
		
		if(mysqli_num_rows($data) > 0) {
			$i = 0;
			$j = 0;
	
			while($row0 = mysqli_fetch_array($data)){
			   $isdeleted = $row0['isdeleted'];
				// if it's deleted, just add it to "deletedparticipant"
			   if ($isdeleted == 1) {
				 $memberid_arr[$j] = $row0['userid'];
				 $j++;
			   }
			   else {
				   $one_arr = array();
				  
				   $one_arr['id'] = $row0['userid'];
				   $one_arr['email'] = $row0['useremail'];
				   $one_arr['name'] = $row0['username'];
				   $one_arr['profile'] = PROFILE_SERVER.$row0['userprofile'];
				   $one_arr['mobile'] = $row0['mobilenumber'];
				   $one_arr['creatorid'] = $row0['creatorid'];
				   $one_arr['communityid'] = $row0['communityid'];
				   $one_arr['createdtime'] = $row0['createdtime'];
				   $one_arr['lastmodified'] = $row0['lastmodified'];
				   $one_arr['userrole'] = $row0['userrole'];
				   
				   $members_arr[$i] = $one_arr;
				   $i++;			   
			   }   
			   
			} // while end
		} // if end
		// TBD: remove the "deleted" since backbone can't handle this
		//$return_arr['deletedparticipant'] = $memberid_arr;
		//$return_arr['participant'] = $members_arr;
        //$data2 = json_encode($return_arr);
		$data2 = json_encode($members_arr);
		echo $data2;
		
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid,$data2);
      
		$data->close();
		mysqli_close($dbc);	
	}
	
	// This is to get list of base event and repeating tasks and assignment pools
	// API 1.5 03/25/2016
	Protected function pgetlastupdate_baseevent($communityid, $ownerid, $lastupdatetime) {
	    
		$return_arr = array();
		//$delpgroup_arr = array();
		$baseevent_arr = array();
		$task_arr = array();
		$assignmentpool_arr = array();
		$repeatschedule_arr = array();
		
		$mysql = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		// call a stored procedure to get the list of base events, their repeating tasks and assignment pools
		if ($mysql->multi_query("CALL getBaseEventByLastUpdate('$communityid', '$lastupdatetime')")) {
	   
        $h = 0;
		//loop through two resultsets
          do {
            if ($result = $mysql->use_result())
            {
			    $i = 0;
				$j = 0;
				$k = 0;
				$l = 0;
                //Loop the two result sets, reading it into an array
                while ($row = $result->fetch_array(MYSQLI_ASSOC))
                {
                    if ($h == 0) {
					   // first resultset base event	
					   $one_arr = array();
					   $one_arr['beventid'] = $row['beventid'];
					   $one_arr['beventname'] = $row['beventname'];
					   $one_arr['bstarttime'] = $row['starttime'];
					   $one_arr['bendtime'] = $row['endtime'];
					   $one_arr['blocation'] = $row['location'];
					   $one_arr['bhost'] = $row['host'];
					   $one_arr['tzid'] = $row['tzid'];
					   $one_arr['balert'] = $row['alert'];
					   $one_arr['bstatus'] = $row['status'];
					   $one_arr['bdesp'] = $row['desp'];
					   $one_arr['repeatinterval'] = $row['repeatinterval'];
					   $one_arr['fromdate'] = $row['fromdate'];
					   $one_arr['todate'] = $row['todate'];
					   $one_arr['task'] = "";
					   
					   $baseevent_arr[$i] = $one_arr;
					   $i++;			   
					   
					}
					else if ($h == 1) {
					   // second resultset -- task assocaited with those base event
						$two_arr = array();
						$two_arr['taskid'] = $row['taskid'];
						$two_arr['taskname'] = $row['taskname'];
						$two_arr['beventid'] = $row['beventid'];
						$two_arr['assignallowed'] = $row['assignallowed'];
						//$two_arr['assigngroupid'] = $row['assigngroupid'];
						$two_arr['description'] = $row['description'];
						$two_arr['assignmentpool'] = "";
					    
					    $task_arr[$j] = $two_arr;
						$j++;
					}
					else if ($h == 2) {
						// third resultset -- the users are in the assignment pool
						$third_arr = array();
						$third_arr['taskid'] = $row['taskid'];
						$third_arr['userid'] = $row['userid'];
						$third_arr['username'] = $row['username'];
						$third_arr['userprofile'] = PROFILE_SERVER.$row['userprofile'];
						//$third_arr['status'] = $row['status'];
		
					    $assignmentpool_arr[$k] = $third_arr;
						$k++;	
					}
					else if ($h == 3) {
						// third resultset -- the users are in the assignment pool
						$fourth_arr = array();
						$fourth_arr['rscheduleid'] = $row['rscheduleid'];
						$fourth_arr['repeatinterval'] = $row['repeatinterval'];
						$fourth_arr['fromdate'] = $row['fromdate'];
						$fourth_arr['todate'] = $row['todate'];
						$fourth_arr['beventid'] = $row['beventid'];
						
					    $repeatschedule_arr[$l] = $fourth_arr;
						$l++;	
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
	    mysqli_close($mysql);
		$x = 0;
		// Construct a output jason object starting from base event, task, and then assignment pool
		foreach ($baseevent_arr as $svalue) {
			$beventid = $svalue['beventid'];
			// go down to task level
			$task_temp = array();
			$i = 0;
			foreach ($task_arr as $mvalue) {
				if ($mvalue['beventid'] == $beventid) {
					// Add the task one by one
					$task_temp_1 = array();
					$task_id = $mvalue['taskid'];
					$task_temp_1['taskid'] = $mvalue['taskid'];
					$task_temp_1['taskname'] = $mvalue['taskname'];
					$task_temp_1['desp'] = $mvalue['description'];
					$task_temp_1['assignallowed'] = $mvalue['assignallowed'];
					//$task_temp_1['assignedgroup'] = $mvalue['assignedgroup'];
				    
					$assignmentpool_temp = array();
					$j = 0;
					// go down to the assignment pool level
					foreach ($assignmentpool_arr as $avalue) {
						// add eventid because the same taskid will be used for different events
						if ($avalue['taskid'] == $task_id) {
							$assignmentpool_temp_1 = array();
							
							$assignmentpool_temp_1['taskid'] = $avalue['taskid'];
							$assignmentpool_temp_1['userid'] = $avalue['userid'];
							$assignmentpool_temp_1['username'] = $avalue['username'];
							$assignmentpool_temp_1['userprofile'] = $avalue['userprofile'];
							//$assignmentpool_temp_1['status'] = $avalue['status'];
						
							$assignmentpool_temp[$j] = $assignmentpool_temp_1;
							$j++;
						}
					}
					//insert assignment pool into
					$task_temp_1['assignmentpool'] = $assignmentpool_temp;
					$task_temp[$i] = $task_temp_1;
					$i++;
				}
			}
			//insert task associated with the base event into the baseevent_arr
			$svalue['task'] = $task_temp;
		
			// go down to the repeat interval
			foreach ($repeatschedule_arr as $mvalue) {
				if ($mvalue['beventid'] == $beventid) {
			       $repeatschedule_temp_1 = array();
				   $repeatschedule_temp_1['rscheduleid'] =$mvalue['rscheduleid'];
				   $repeatschedule_temp_1['repeatinterval'] = $mvalue['repeatinterval'];
				   $repeatschedule_temp_1['fromdate'] =$mvalue['fromdate'];
				   $repeatschedule_temp_1['todate'] = $mvalue['todate'];
				   
				   $svalue['repeatschedule'] = $repeatschedule_temp_1;
			    }
			}
			
			$return_arr[$x] = $svalue;
			$x++;	
		} // end of base event loop
		unset($svalue);
		
		$data2 = json_encode($return_arr);
		echo $data2;
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid,$data2);	
	}
	
	// This is to get list of participant groups and its users
	// API 1.5 04/20/2015
	Protected function pgetlastupdate_pg($communityid, $ownerid, $lastupdatetime) {
	    
		$return_arr = array();
		$delpgroup_arr = array();
		$pgroup_arr = array();
		$user_att = array();
		
		$mysql = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		// call a stored procedure to get the list of participant groups and their users
		if ($mysql->multi_query("CALL getPGroupByLastUpdate('$communityid', '$lastupdatetime')")) {
	   
          $h = 0;
		  //loop through two resultsets
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
					   // first resultset 
						$isdeleted = $row['isdeleted'];
						
						// if it's deleted, just add it to "deletedschedules"
						if ($isdeleted == 1) {
							$delpgroup_arr[$j] = $row['pgroupid'];
							$j++;
						}
						else {
						   $one_arr = array();
						   $one_arr['participantgroupid'] = $row['pgroupid'];
						   $one_arr['participantgroupname'] = $row['pgroupname'];
						   $one_arr['user'] = "";
						   $pgroup_arr[$i] = $one_arr;
						   $i++;			   
						}     
					}
					else {
					   // second resultset 
						$two_arr = array();
						$two_arr['participantgroupid'] = $row['pgroupid'];
						$two_arr['userid'] = $row['userid'];
					
					    $user_att[$k] = $two_arr;
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
		
	    //$delschedule_str = array();
		//$delschedule_str = implode(",", $delschedule_arr); // concat to the string separating by ,
		
		foreach ($pgroup_arr as &$svalue) {
			$pgroupid = $svalue['participantgroupid'];
			
			// get the list of memberid and confirm associated with the event ID
			//$members_str = '';
			$user_str = array();
			$i = 0;
			foreach ($user_att as $mvalue) {
			  if ($mvalue['participantgroupid'] == $pgroupid) {
		
				$user_str[$i] = $mvalue['userid'];
				$i++;
			  }
			}
			//insert members associated with the event into the schedules_arr TBD
			$svalue['user'] = $user_str;
		}
		unset($svalue);
		
		$return_arr['deleted'] = $delpgroup_arr;
		$return_arr['participantgroup'] = $pgroup_arr;
         
		$data2 = json_encode($return_arr);
		echo $data2;
		
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid,$data2);
     
		mysqli_close($mysql);	
	}
	
	
	// This is for get event API to get the latest events and tasks assigned to an event
	// First result is for events; Second result is for tasks; Third result is for assignment
	// This is for API 1.5  03/23/2016
	Protected function pgetlastupdate_sh($communityid, $startdate, $number) {
		//check if it's valid
		if ($startdate == null or $number == null) {
			header('HTTP/1.0 204 missing start or num parameters', true, 204);
			$data2 = json_encode(array('code'=> 204, 'message' => 'missing start or num parameter'));
			echo $data2;
			exit;
		}
	  
		$return_arr = array();
		$delevent_arr = array();
		$delbevent_arr = array();
		$event_arr = array();
		$assignmentpool_arr = array();
		$task_arr = array();
		$taskhelper_arr = array();
		$final_event_arr = array();
		
		$mysql = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		// call a stored procedure 
		if ($mysql->multi_query("CALL getSmartEventByPage('$communityid', '$startdate', '$number')")) {
	      // this is for the number of resultset 
          $h = 0;
		  //loop through three resultsets
          do {
            if ($result = $mysql->use_result())
            {
			    $i = 0;
				$j = 0;
				$k = 0;
				$l = 0;
				$m = 0;
                //Loop the two result sets, reading it into an array
                while ($row = $result->fetch_array(MYSQLI_ASSOC))
                {
                    if ($h == 0) {
					
						   $one_arr = array();
						   $one_arr['eventid'] = $row['eventid'];
						   $one_arr['eventname'] = $row['eventname'];
						   $one_arr['desp'] = $row['description'];
						   $one_arr['startdatetime'] = $row['startdatetime'];
						   $one_arr['enddatetime'] = $row['enddatetime'];
						   $one_arr['alert'] = $row['alert'];
						   $one_arr['tzid'] = $row['tzid'];
						   $one_arr['location'] = $row['location'];
						   $one_arr['host']  = $row['host'];
						   $one_arr['status']  = $row['status'];
						   $one_arr['referid'] = $row['referid'];
						   $one_arr['repeatinterval'] = $row['repeatinterval'];
						   $one_arr['fromdate'] = $row['fromdate'];
						   $one_arr['todate'] = $row['todate'];
						   
						   $event_arr[$i] = $one_arr;
						   $i++;			   
					}
					else if ($h == 1){
					   // second result set for task 
						$two_arr = array();
						$two_arr['taskid'] = $row['taskid'];
						$two_arr['eventid'] = $row['eventid'];
						$two_arr['taskname'] = $row['taskname'];
						$two_arr['desp'] = $row['description'];
						$two_arr['assignallowed'] = $row['assignallowed'];
						//$two_arr['assignedgroup'] = $row['assignedgroup'];
					   
						$task_arr[$k] = $two_arr;
						$k++;
					}
					else if ($h == 2){
						// third result set for taskhelper
						$third_arr = array();
						$third_arr['taskhelperid'] = $row['taskhelperid'];
						$third_arr['taskid'] = $row['taskid'];
						$third_arr['eventid'] = $row['eventid'];
						$third_arr['userid'] = $row['userid'];
						$third_arr['username'] = $row['username'];
						$third_arr['userprofile'] = PROFILE_SERVER .$row['userprofile'];
						$third_arr['status'] = $row['status'];
						
					    $taskhelper_arr[$l] = $third_arr;
						$l++;
					} if ($h == 3) {
						// fourth result set for assignment pool
						$fourth_arr = array();
						$fourth_arr['assignmentpoolid'] = $row['assignmentpoolid'];
						$fourth_arr['taskid'] = $row['taskid'];
						$fourth_arr['userid'] = $row['userid'];
						$fourth_arr['username'] = $row['username'];
						$fourth_arr['userprofile'] = PROFILE_SERVER .$row['userprofile'];
						
						$assignmentpool_arr[$m] = $fourth_arr;
						$m++;
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
		
		mysqli_close($mysql);	
		
		$return_arr['event'] = $event_arr;
		$return_arr['task'] = $task_arr;
		$return_arr['taskhelper'] = $taskhelper_arr;
		$return_arr['assignmentpool'] = $assignmentpool_arr;
         
		$data2 = json_encode($return_arr);
		echo $data2;
		
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid,$data2);
	}
	
	
	// This is to add a participant to a community (04/29/2015)
	Protected function insert_participant($serviceid, $body_param) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		
		$ownerid = $body_param['ownerid'];
		//$userid = $body_param['userid'];
		$userid = $body_param['id'];
		$userrole = $body_param['userrole'];
		
		$query = "SELECT Is_Deleted isdeleted FROM participant WHERE Community_Id = '$serviceid' and User_Id = '$userid' LIMIT 1";
		$data = mysqli_query($dbc, $query) or die(mysqli_error());
	    $result = mysqli_fetch_assoc($data);
		if (mysqli_num_rows($data)== 1 and $result['isdeleted'] == 0) {
			header('HTTP/1.0 201 This shared member exists already', true, 201);
			$data2 = json_encode(array('code'=> 201, 'message' => 'This participant exists'));
			echo $data2;
			exit;	
		}
		else {
		    if (mysqli_num_rows($data)== 0) {
				$queryinsert1 = "insert into participant".
						 " (Community_Id, User_Id, User_Role, Is_Deleted,Creator_Id,Created_Time, Last_Modified, Last_Modified_Id) ".
						 "values('$serviceid','$userid', '$userrole',0,'$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
			}
			else if ($result['isdeleted'] == 1) {
				$queryinsert1 = "update participant ".
						 " set Is_Deleted = 0, User_Role = '$userrole', Last_Modified_Id = '$ownerid', Last_Modified = UTC_TIMESTAMP ".
						 " where Community_Id = '$serviceid' and User_Id = '$userid' ";
			}
			$result = mysqli_query($dbc,$queryinsert1);
			if ($result !== TRUE) {
				header('HTTP/1.0 203 failed to inert shared members', true, 203);
				$data2 = json_encode(array('code'=> 203, 'message' => 'failed to inert participant'));
				echo $data2;
				exit;							
			}
			
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;			
		}
		mysqli_free_result($data);
		mysqli_close($dbc);
	}
	
	// This is to add a participant to a community (04/29/2015) --- this function is for Backbone code
	// 06/21 to add a user to the user table as well as the participant table
	// step #1:  add this user to the user table and return a userid
	// step #2:  add this user to the participant table and return the user id to a caller
	Protected function insert_participant_one($serviceid, $body_parms) {
		$userid = "";
		// default user role (regular)
		$userrole = 1;
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		mysqli_select_db($dbc, DB_NAME);
		
		$email = $body_parms['email'];
		$username = $body_parms['name'];
		$mobile = $body_parms['mobile'];
		$ownerid = $body_parms['ownerid'];
		
		$querysearch = "select User_Id from user where Email='$email'";
		$data = mysqli_query($dbc,$querysearch);
		
		if(!isEmptyString($email)) {
			if(mysqli_num_rows($data)==0){
				// this user doesn't exist and go ahead
				// turn off auto commit
				mysqli_autocommit($dbc, FALSE);
				try {
					// 1. invite a participant ---- first transaction
					$queryinsert = "insert into user(Email,User_Name,Password,User_Type, Mobile,Profile, Verified, Active, Created_Time, Last_Modified)
									 values('$email','$username','','','$mobile','default-profile-pic.png',0, 0, UTC_TIMESTAMP(), UTC_TIMESTAMP())"; 
					
					mysqli_query($dbc,$queryinsert)or die("Error is: \n ".mysqli_error($dbc));		
					$data2 = mysqli_query($dbc,$querysearch);			
					$row = mysqli_fetch_array($data2);
					$userid = $row['User_Id'];
					
					// logserver if debug flag is set to 1
					if (DEBUG_FLAG == 1)
							logserveronce("Register","POST", $email, "");
					$data2->close();
					// 2. add it to the participant
					$query = "SELECT Is_Deleted isdeleted FROM participant WHERE Community_Id = '$serviceid' and User_Id = '$userid' LIMIT 1";
					$data = mysqli_query($dbc, $query) or die(mysqli_error());
					$result = mysqli_fetch_assoc($data);
					if (mysqli_num_rows($data)== 1 and $result['isdeleted'] == 0) {
						header('HTTP/1.0 201 This shared member exists already', true, 201);
						echo json_encode(array('code'=> 201, 'message' => 'This participant exists'));
					}
					else {
						if (mysqli_num_rows($data)== 0) {
							$queryinsert1 = "insert into participant".
									 " (Community_Id, User_Id, User_Role, Is_Deleted,Creator_Id,Created_Time, Last_Modified, Last_Modified_Id) ".
									 "values('$serviceid','$userid', '$userrole',0,'$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
						}
						else if ($result['isdeleted'] == 1) {
							$queryinsert1 = "update participant ".
									 " set Is_Deleted = 0, User_Role = '$userrole', Last_Modified_Id = '$ownerid', Last_Modified = UTC_TIMESTAMP ".
									 " where Community_Id = '$serviceid' and Member_Id = '$userid' ";
						}
						$result = mysqli_query($dbc,$queryinsert1);
						echo json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time()), 'id' => $userid));			
					}	
				} 
				catch (exception $e) {
					mysqli_rollback($dbc);
					mysqli_autocommit($dbc, TRUE);
					header('HTTP/1.0 203 failed to inert shared members', true, 203);
				}	
			}
			else {
				// there is already registered
				header('X-PHP-Response-Code: 201', true, 201);
				echo json_encode(array('error message'=>'This user is already existing'));
			}
		}
		else {
			// empty or null for one of user name, password and email
			header('X-PHP-Response-Code: 202', true, 202);
			echo json_encode(array('error message'=>'empty or null value for one of user name, password and or email'));
		}
				
		mysqli_free_result($data);
		mysqli_close($dbc);
	}
	
	
	// This is to add a participant group to a community (04/29/2015)
	Protected function insert_participantgroup($ownerid, $serviceid, $body_param) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		 
		$participantgroupid =   $body_param['participantgroupid'];
		$participantgroupname = $body_param['participantgroupname'];
		$user_arr =   $body_param['user'];
	   
		$query = "SELECT PGroup_Id FROM participantgroup WHERE PGroup_Id = $participantgroupid";
        $data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data) == 1) {
		    // community already exists
			header('HTTP/1.0 201 This participant group exists already', true, 201);
	    }
		else {
			try {
				// start a transaction
				mysqli_autocommit($dbc, FALSE);
				// Insert this participant group if no exists
				$queryinsert = "INSERT INTO participantgroup ".
									"(PGroup_Id,PGroup_Name,Community_Id,Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
									" values('$participantgroupid','$participantgroupname','$serviceid','$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(), '$ownerid')";
				
				$result = mysqli_query($dbc,$queryinsert) or die("Error is: \n ".mysqli_error($dbc));
				if ($result !== TRUE) {
					throw new Exception(mysqli_error($dbc));
				}
				
				foreach($user_arr as $user) {
					//insert each user into the table groupparticipant
					$queryinsert1 = "INSERT INTO groupparticipant ".
									"(PGroup_Id,User_Id,Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
									" values('$participantgroupid','$user','$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(), '$ownerid')";
					$result = mysqli_query($dbc,$queryinsert1) or die("Error is: \n ".mysqli_error($dbc));
					if ($result !== TRUE) {
						throw new Exception(mysqli_error($dbc));
					}		
				}
				// commit to get everything done
				mysqli_commit($dbc);
			}
			catch (Exception $e) {
				mysqli_rollback($dbc);
				mysqli_autocommit($dbc, TRUE);
				header('HTTP/1.0 202 Can not create an participant group and its users', true, 202);
				
			}
			
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
            echo $data2;			
		}
		$data->close();
		mysqli_close($dbc);
		
	}
	
	// GET method here is to handle 4 cases
	//  1. http://[REST_SERVER]/community/1234/participantgroup?ownerid=2222&lastupdatetime=121333000
	//  2. http://[REST_SERVER]/community/1234/participant?ownerid=12434&lastupdatetime=121443232
	//  3. http://[REST_SERVER]/community/1234/event?ownerid=11122@lastupdatetime=12121
	//  4. http://[REST_SERVER]/community?ownerid=11122@lastupdatetime=12121
	public function get($request) {
        $ownerid = $request->parameters['ownerid'];
		$lastupdatetime = urldecode($request->parameters['lastupdatetime']);
	    
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "participantgroup") {
			// participant group
			$communityid  = $request->url_elements[count($request->url_elements)-2];
			$this->pgetlastupdate_pg($communityid, $ownerid, $lastupdatetime);
		}
		else if ($lastElement == "participant") {
			// handle participant
			$communityid  = $request->url_elements[count($request->url_elements)-2];
			$this->pgetlastupdate_sm($communityid, $ownerid, $lastupdatetime);
		} 
		else if ($lastElement == "event") {
			$communityid  = $request->url_elements[count($request->url_elements)-2];
			$startdate = $request->parameters['start'];
			$number = $request->parameters['num'];
			$this->pgetlastupdate_sh($communityid, $startdate, $number);
		}
		else if ($lastElement == "community") {
			$this->pgetlastupdate($ownerid, $lastupdatetime);
		}
		if ($lastElement == "baseevent") {
			// handle participant
			$communityid  = $request->url_elements[count($request->url_elements)-2];
			$this->pgetlastupdate_baseevent($communityid, $ownerid, $lastupdatetime);
			
		}
    }

	// This is the POST call to add a new community, participant, or participant group
	//   1. POST http://servicescheduler.net/community
	//   2. POST http://servicescheduler.net/community/1234/participant
	//   3. POST http://[domain name]/community/1234/participantgroup
	//   4. POST http://servicescheduler.net/community/1234/participantone
    public function post($request) {
		$parameters1 = array();
        
		header('Content-Type: application/json; charset=utf8');
		// handle different resources
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "community") {
			/**
			if ($request->body_parameters['community']) {
				foreach($request->body_parameters['community'] as $param_name => $param_value) {
							$parameters1[$param_name] = $param_value;
				}
			}
			$this->insert($request->body_parameters['ownerid'], $parameters1);
			**/
			$this->insert($request->body_parameters);
		} else if ($lastElement == "participant") {
		    $serviceid  = $request->url_elements[count($request->url_elements)-2];
			 //participant method
			$this->insert_participant($serviceid, $request->body_parameters);
		} else if ($lastElement == "participantgroup") {
			$serviceid  = $request->url_elements[count($request->url_elements)-2];
			if ($request->body_parameters['participantgroup']) {
				foreach($request->body_parameters['participantgroup'] as $param_name => $param_value) {
							$parameters1[$param_name] = $param_value;
				}
			}
			 //participant group method
			$this->insert_participantgroup($request->body_parameters['ownerid'], $serviceid, $parameters1);
		} if ($lastElement == "participantone") {
			$serviceid  = $request->url_elements[count($request->url_elements)-2];
			 //participant method
			$this->insert_participant_one($serviceid, $request->body_parameters);
		
		}
		
    }
	
	// update a community with the community Id and update a role shared with activity
	// 1. PUT http://[api_domain_name]/community/1234
	// 2. PUT http://[api_domain_name]/community/1234/participant
	// 3. PUT http://[api_domain_name]/community/1234/participant/1111
	public function put($request) {
        $parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$lastElement = end($request->url_elements);
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		reset($request->url_elements);
		
		if ($last2Element == "community") {
			/**
			if ($request->body_parameters['community']) {
				foreach($request->body_parameters['community'] as $param_name => $param_value) {
								$parameters1[$param_name] = $param_value;
				}
			}
			***/
			//handle different resources
			$communityid = end($request->url_elements);
			reset($request->url_elements);
			$this->update($communityid, $request->body_parameters);
	    }
		else if ($last2Element == "participant") {
			//get userid and communityid 
			$userid = end($request->url_elements);
			reset($request->url_elements);
			$communityid = $request->url_elements[count($request->url_elements)-3];
			$this->update_sm($communityid, $userid, $request->body_parameters);
		
		}
		else {
			// this is the case #2  /community/1234/participant to add participant 
			
			
		}
    }
	
	
	// This is the DELETE call to delete a community or remove a participant from the community
	// It can have message body for Delete (based on the latest HTTP specs)
	//   1. DELETE http://servicescheduler.net/community/1234
	//   2. DELETE http://servicescheduler.net/community/1234/participant/2222
	public function delete($request) {
		$last2Element = $request->url_elements[count($request->url_elements)-2];
	    
		header('Content-Type: application/json; charset=utf8');
		if ($last2Element == "community") {
			// Delete community
			$communityid = end($request->url_elements);
			reset($request->url_elements);
			$ownerid = $request->parameters['ownerid'];
			$this->pdelete($communityid, $ownerid);
		}
		else if ($last2Element == "participant") {
			// Delete a participant from community
		    $communityid = $request->url_elements[count($request->url_elements)-3];
			$userid =  end($request->url_elements);
			reset($request->url_elements);
			$ownerid = $request->parameters['ownerid'];
			$this->pdelete_participant($communityid, $userid, $ownerid);
		}
	}	
    
	public function options($request) {
	}
	
	// Insert a participant if it doesn't exist in the community 
	Protected function insert_creator($ownerid, $communityid, $participantid) {
	
	   $dbc1 = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
       $query1 = "SELECT * FROM user WHERE User_Id = $ownerid";
	   
	   $data1 = mysqli_query($dbc1, $query1);
		
		if (mysqli_num_rows($data1)==1) {
			$row1 = mysqli_fetch_array($data1);
			
			$queryinsert1 = "insert into participant".
						 " (Participant_Id, Community_Id, User_Id, User_Role, Is_Deleted,Creator_Id,Created_Time, Last_Modified, Last_Modified_Id) ".
						 "values('$participantid','$communityid','$ownerid', 0,0,'$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
			$result1 = mysqli_query($dbc1,$queryinsert1) or die("Error is: \n ".mysqli_error($dbc1));
			if ($result1 !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 202 Can not add the creator member', true, 202);
				Exit;
			}
		}
		$data1->close();
		mysqli_close($dbc1);
	
	}
	
}
?>
