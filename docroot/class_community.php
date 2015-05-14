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
	
	// create a new service and insert the creator to the participant table
	Protected function insert($ownerid, $community_parms) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
		 
		$communityid = $community_parms['communityid'];
		$communityname = $community_parms['communityname'];
		$description = $community_parms['desp'];
	   
		$query = "SELECT Service_Id FROM service WHERE Service_Id = $communityid";
        $data = mysqli_query($dbc, $query);
		
        if (mysqli_num_rows($data)==1) {
		    // community already exists
			header('HTTP/1.0 201 This community exists already', true, 201);
	    }
		else {
			// Insert this community if no exists
			$queryinsert = "INSERT INTO service ".
								"(Service_Id,Service_Name,Description,Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
								" values('$communityid','$communityname','$description','$ownerid','0',UTC_TIMESTAMP(),UTC_TIMESTAMP(), '$ownerid')";
			
			$result = mysqli_query($dbc,$queryinsert) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				header('HTTP/1.0 202 This community can not be added', true, 202);
				Exit;
			}
			
			// insert the creator to the community if it doesn't exist
			$this->insert_creator($ownerid, $communityid);
			
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
            echo $data2;			
		}
		$data->close();
		mysqli_close($dbc);
	}

	Protected function update($communityid, $ownerid, $community_parms) {
		$communityname = $community_parms['communityname'];
		$description= $community_parms['desp'];
		
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		$query = "SELECT * FROM service WHERE Service_Id = '$communityid'";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
	
        if (mysqli_num_rows($data)==1) {
		    // community exists and go ahead to update it
			$queryupdate = "update service set ".
						"Service_Name = '$communityname', Description = '$description', Last_Modified = UTC_TIMESTAMP(),Last_Modified_Id = '$ownerid' ".
						"where Service_Id = '$communityid'";
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
		$query = "SELECT * FROM participant WHERE Service_Id = '$communityid' and User_Id = '$userid' and Is_Deleted = 0 ";
        $data = mysqli_query($dbc, $query) or die("Error is: \n ".mysqli_error($dbc));
		
        if (mysqli_num_rows($data)==1) {
		    // shared member exists and go ahead to update it
			$queryupdate = "update participant set ".
						"User_Role = '$userrole', Last_Modified = UTC_TIMESTAMP, Last_Modified_Id = '$ownerid'".
						"WHERE Service_Id = '$communityid' and User_Id = '$userid'";
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
	Protected function pdelete($communityid, $ownerid) {
	
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		// only owner can delete a community
		$query = "SELECT * FROM service WHERE Service_Id = '$communityid' and Creator_id = '$ownerid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This community doesn\'t exist or has been deleted', true, 201);
			exit;
	    }
		else {
			// community exists and go ahead to update it
			// two steps commit
			mysqli_autocommit($dbc, FALSE);
	
			// Delete this community by setting the flag Is_Deleted to 1
			$queryupdate = "update service set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid'".
						" where Service_Id = '$communityid'";
			$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				// if error, roll back transaction
				mysqli_rollback($dbc);
				header('HTTP/1.0 202 This community can\'t be deleted', true, 202);
				exit;
			}
			else {
			    // first to check if there are some schedules associated with this community
				$query = "SELECT * FROM schedule WHERE Service_Id = '$communityid' and Is_Deleted = 0";
				$data = mysqli_query($dbc, $query) or die(mysqli_error());
				if (mysqli_num_rows($data) != 0) {
					// second to delete the existing relationship
					$queryupdate = "update schedule set ".
							" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
							" where Service_Id = '$communityid'";
			
					$result = mysqli_query($dbc,$queryupdate) or die("Error is: \n ".mysqli_error($dbc));
					if ($result !== TRUE) {
						// if error, roll back transaction
						mysqli_rollback($dbc);
						header('HTTP/1.0 203 Failed to delete schedule', true, 203);
						exit;
					}
				}
				
				// always delete sharedmembers since at least the creator is on the sharedmember table   
				$querydelete = "update sharedmember set ".
					" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
					" where Service_Id = '$communityid'";
				$result = mysqli_query($dbc,$querydelete) or die("Error is: \n ".mysqli_error($dbc));
				if ($result !== TRUE) {
					// if error, roll back transaction
					mysqli_rollback($dbc);
					header('HTTP/1.0 204 Failed to delete sharedrole in sharedmember', true, 204);
					exit;
				}
				mysqli_commit($dbc);
										
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
		
		$query = "SELECT * FROM participant WHERE Service_Id = '$communityid' and User_Id = '$userid' and Is_Deleted = 0";
        $data = mysqli_query($dbc, $query) or die(mysqli_error());
		
        if (mysqli_num_rows($data)==0) {
			header('HTTP/1.0 201 This participant doesn\'t shared with the community', true, 201);
			exit;
	    }
		else {
			// Delete this participant by setting the flag Is_Deleted to 1
			$update = "update participant set ".
						" Is_Deleted = 1, Last_Modified = UTC_TIMESTAMP(), Last_Modified_Id = '$ownerid' ".
						" where Service_Id = '$communityid' and User_Id = '$userid'";
			$result = mysqli_query($dbc,$update) or die("Error is: \n ".mysqli_error($dbc));
			if ($result !== TRUE) {
				header('HTTP/1.0 204 Failed to delete shared member', true, 204);
				exit;
			}
			$data2 = json_encode(array('lastmodified'=> gmdate("Y-m-d H:i:s", time())));
			echo $data2;
		}
	}
	
	// This is a function to get the latest Ids for this user
	// So this user can continue to generate valid Ids
	// Not being used:. This function was moved to the login API (03/29/2015)
	Protected function pgetlastupdate($ownerid, $lastupdatetime) {
		$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
	
		$query = " SELECT distinct s.Service_Id serviceid ,s.Service_Name servicename,s.Description descp ".
                 " ,s.Creator_Id creatorid, ".
                 " if (s.Is_Deleted = 1 or o.Is_Deleted = 1, 1, 0) isdeleted,s.Created_Time createdtime,s.Last_Modified lastmodified,o.User_Role userrole ".
                 " from service s, participant o ".
                 " where s.Service_Id = o.Service_Id ".
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
				 $serviceid_arr[$j] = $row0['serviceid'];
				 $j++;
			   }
			   else {
				   $one_arr = array();
				   $one_arr['communityid'] = $row0['serviceid'];
				   $one_arr['communityname'] = $row0['servicename'];
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
		$return_arr['deletedcmty'] = $serviceid_arr;
		$return_arr['community'] = $services_arr;
         
		$data2 = json_encode($return_arr);
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
		$query = "SELECT sm.Is_Deleted isdeleted, sm.User_Id userid, m.Email useremail, m.User_Name username, m.Mobile mobilenumber, ".
		    " sm.Creator_Id creatorid, sm.Service_Id communityid, sm.Created_Time createdtime, sm.Last_Modified lastmodified, sm.User_Role userrole ".
 		    " FROM participant sm, user m ".
			"where sm.User_Id = m.User_Id and sm.Service_Id = '$communityid' and ".
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
				  
				   $one_arr['userid'] = $row0['userid'];
				   $one_arr['mail'] = $row0['useremail'];
				   $one_arr['name'] = $row0['username'];
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
		$return_arr['deletedparticipant'] = $memberid_arr;
		$return_arr['participant'] = $members_arr;
         
		$data2 = json_encode($return_arr);
		echo $data2;
		
		// logserver if debug flag is set to 1
		if (DEBUG_FLAG == 1)
		    logserver_response($this->lastid,$data2);
      
		$data->close();
		mysqli_close($dbc);	
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
			
			// get the list of memberid and confirm associated with the schedule ID
			//$members_str = '';
			$user_str = array();
			$i = 0;
			foreach ($user_att as $mvalue) {
			  if ($mvalue['participantgroupid'] == $pgroupid) {
		
				$user_str[$i] = $mvalue['userid'];
				$i++;
			  }
			}
			//insert members associated with the schedule into the schedules_arr TBD
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
	// This is for API 1.5  04/27/2015
	Protected function pgetlastupdate_sh($communityid, $lastupdatetime) {
	    
		$return_arr = array();
		$delevent_arr = array();
		$event_arr = array();
		$task_arr = array();
		$assignment_arr = array();
		$final_event_arr = array();
		
		$mysql = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		
		// call a stored procedure 
		if ($mysql->multi_query("CALL getEventByLastUpdate('$communityid', '$lastupdatetime')")) {
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
                //Loop the two result sets, reading it into an array
                while ($row = $result->fetch_array(MYSQLI_ASSOC))
                {
                    if ($h == 0) {
					   // first resultset 
						$isdeleted = $row['isdeleted'];
						
						// if it's deleted, just add it to "deletedevent"
						if ($isdeleted == 1) {
							$delevent_arr[$j] = $row['eventid'];
							$j++;
						}
						else {
						   $one_arr = array();
						   $one_arr['eventid'] = $row['eventid'];
						   $one_arr['eventname'] = $row['eventname'];
						   $one_arr['desp'] = $row['description'];
						   $one_arr['startdatetime'] = $row['startdatetime'];
						   $one_arr['enddatetime'] = $row['enddatetime'];
						   $one_arr['alert'] = $row['alert'];
						   $one_arr['tzid'] = $row['tzid'];
						   $one_arr['reventid'] = $row['reventid'];
						   $one_arr['task'] = "";
						   $event_arr[$i] = $one_arr;
						   $i++;			   
						}     
					}
					else if ($h == 1){
					   // second result set for task 
						$two_arr = array();
						$two_arr['taskid'] = $row['taskid'];
						$two_arr['eventid'] = $row['eventid'];
						$two_arr['taskname'] = $row['taskname'];
						$two_arr['desp'] = $row['description'];
						$two_arr['assignallowed'] = $row['assignallowed'];
						$two_arr['assignedgroup'] = $row['assignedgroup'];
					    $two_arr['assignment'] = "";
						$task_arr[$k] = $two_arr;
						$k++;
					}
					else {
						// third result set for assignment
						$third_arr = array();
						$third_arr['taskid'] = $row['taskid'];
						$third_arr['eventid'] = $row['eventid'];
						$third_arr['userid'] = $row['userid'];
						$third_arr['username'] = $row['username'];
						$third_arr['confirm'] = $row['confirm'];
					    $assignment_arr[$l] = $third_arr;
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
	    $delevent_str = array();
		$delevent_str = implode(",", $delevent_arr); // concatenate to the string seprating by ,
		
		// Construct a output jason object starting from event, task, and then assignment
		foreach ($event_arr as $svalue) {
			$eventid = $svalue['eventid'];
			$reventid = $svalue['reventid'];
			// go down to task level
			$task_temp = array();
			$i = 0;
			foreach ($task_arr as $mvalue) {
				if ($mvalue['eventid'] == $eventid) {
					// Add the task one by one
					$task_temp_1 = array();
					$task_id = $mvalue['taskid'];
					
					$task_temp_1['taskid'] = $mvalue['taskid'];
					$task_temp_1['taskname'] = $mvalue['taskname'];
					$task_temp_1['desp'] = $mvalue['desp'];
					$task_temp_1['assignallowed'] = $mvalue['assignallowed'];
					$task_temp_1['assignedgroup'] = $mvalue['assignedgroup'];
				
					$assignment_temp = array();
					$j = 0;
					// go down to the task assignment level
					foreach ($assignment_arr as $avalue) {
						if ($avalue['taskid'] == $task_id) {
							$assignment_temp_1 = array();
							
							$assignment_temp_1['userid'] = $avalue['userid'];
							$assignment_temp_1['username'] = $avalue['username'];
							$assignment_temp_1['confirm'] = $avalue['confirm'];
							
							$assignment_temp[$j] = $assignment_temp_1;
							$j++;
						}
					}
					$task_temp_1['assignment'] = $assignment_temp;
					//add tasks
					$task_temp[$i] = $task_temp_1;
					$i++;
				}
			}
			//insert task associated with the event into the event_arr TBD
			$svalue['task'] = $task_temp;
			
			// insert into the final array
			if (!$reventid or $reventid != 0 ) {
				if (array_key_exists($reventid, $final_event_arr)) {
					$c = count($final_event_arr[$reventid]);
					$final_event_arr[$reventid][$c] = $svalue;
				}
				else 
				    $final_event_arr[$reventid][0] = $svalue;
			}
			else {
				$final_event_arr[$eventid][0] = $svalue;
			}
			
		} // end of event loop
		unset($svalue);
		
		// finally get this output
		$return_arr['deletedevent'] = $delevent_arr;
		$return_arr['event'] = $final_event_arr;
         
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
		$userid = $body_param['userid'];
		$userrole = $body_param['userrole'];
		
		$query = "SELECT Is_Deleted isdeleted FROM participant WHERE Service_Id = '$serviceid' and User_Id = '$userid' LIMIT 1";
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
						 " (Service_Id, User_Id, User_Role, Is_Deleted,Creator_Id,Created_Time, Last_Modified, Last_Modified_Id) ".
						 "values('$serviceid','$userid', '$userrole',0,'$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
			}
			else if ($result['isdeleted'] == 1) {
				$queryinsert1 = "update participant ".
						 " set Is_Deleted = 0, User_Role = '$userrole', Last_Modified_Id = '$ownerid', Last_Modified = UTC_TIMESTAMP ".
						 " where Service_Id = '$serviceid' and Member_Id = '$userid' ";
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
									"(PGroup_Id,PGroup_Name,Service_Id,Creator_Id,Is_Deleted,Created_Time,Last_Modified, Last_Modified_Id)".
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
	
	// GET method here is to handle 3 cases
	//  1. http://[REST_SERVER]/community/1234/participantgroup?ownerid=2222&lastupdatetime=121333000
	//  2. http://[REST_SERVER]/community/1234/participant?ownerid=12434&lastupdatetime=121443232
	//  3. http://[REST_SERVER]/community/1234/event?ownerid=11122@lastupdatetime=12121
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
			$serviceid  = $request->url_elements[count($request->url_elements)-2];
			$this->pgetlastupdate_sm($serviceid, $ownerid, $lastupdatetime);
		} if ($lastElement == "event") {
			$communityid  = $request->url_elements[count($request->url_elements)-2];
			$this->pgetlastupdate_sh($communityid, $lastupdatetime);
		}
    }

	// This is the POST call to add a new community, participant, or participant group
	//   1. POST http://servicescheduler.net/community
	//   2. POST http://servicescheduler.net/community/1234/participant
	//   3. POST http://[domain name]/community/1234/participantgroup
    public function post($request) {
		$parameters1 = array();
        
		header('Content-Type: application/json; charset=utf8');
		// handle different resources
		$lastElement = end($request->url_elements);
		reset($request->url_elements);
		if ($lastElement == "community") {
			if ($request->body_parameters['community']) {
				foreach($request->body_parameters['community'] as $param_name => $param_value) {
							$parameters1[$param_name] = $param_value;
				}
			}
			$this->insert($request->body_parameters['ownerid'], $parameters1);
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
		}
		
    }
	
	// update a service with the service Id and update a role shared with activity
	// 1. PUT http://[api_domain_name]/community/1234
	// 2. PUT http://[api_domain_name]/community/1234/participant/1111
	public function put($request) {
        $parameters1 = array();
		
		header('Content-Type: application/json; charset=utf8');
		$last2Element = $request->url_elements[count($request->url_elements)-2];
		
		if ($last2Element == "community") {
			if ($request->body_parameters['community']) {
				foreach($request->body_parameters['community'] as $param_name => $param_value) {
								$parameters1[$param_name] = $param_value;
				}
			}
			
			//handle different resources
			$communityid = end($request->url_elements);
			reset($request->url_elements);
			$this->update($communityid, $request->body_parameters['ownerid'], $parameters1);
	    }
		else if ($last2Element == "participant") {
			//get userid and communityid 
			$userid = end($request->url_elements);
			reset($request->url_elements);
			$communityid = $request->url_elements[count($request->url_elements)-3];
			$this->update_sm($communityid, $userid, $request->body_parameters);
		
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
	
	// Insert a participant if it doesn't exist in the community 
	Protected function insert_creator($ownerid, $communityid) {
	
	   $dbc1 = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME)or die('Database Error 2!');
       $query1 = "SELECT Email, User_Name, Mobile FROM user WHERE User_Id = $ownerid";
	   
	   $data1 = mysqli_query($dbc1, $query1);
		
		if (mysqli_num_rows($data1)==1) {
			$row1 = mysqli_fetch_array($data1);
			
			$email = $row1['Email'];
			$membername = $row1['User_Name'];
			$mobile = $row1['Mobile'];
			
			$queryinsert1 = "insert into participant".
						 " (Service_Id, User_Id, User_Role, Is_Deleted,Creator_Id,Created_Time, Last_Modified, Last_Modified_Id) ".
						 "values('$communityid','$ownerid', 0,0,'$ownerid', UTC_TIMESTAMP(), UTC_TIMESTAMP(), '$ownerid')";
			$result1 = mysqli_query($dbc1,$queryinsert1) or die("Error is: \n ".mysqli_error($dbc));
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
