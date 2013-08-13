<?php # Script 7.8 - password.php
	// This page lets a user change their password.
	require_once('../apitest/constants.php');
	//require_once('../constants.php');

	// Check if the form has been submitted.
	if (isset($_POST['submitted'])) {
		$errors = array(); // Initialize error array.
		
		// Check for an email address.
		if (empty($_POST['email'])) {
			$errors[] = 'You forgot to enter your email address.';
		} else {
			$e = $_POST['email'];
		}
		
		if (empty($_POST['token'])) {
			$errors[] = 'You forgot to enter your email address.';
		} else {
			$t = $_POST['token'];
		}
		
		// Check for an existing password.
		if (empty($_POST['password1'])) {
			$errors[] = 'You forgot to enter your existing password.';
		} else { 
			$p = $_POST['password1'];
		}
		
		if (empty($errors)) { // If everythings OK.
		
			$dbc = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
	  
			$query = "SELECT token FROM resetpassword WHERE Email = '$e' and Token = '$t' and Is_Done = 0";
					
			$data = mysqli_query($dbc, $query);
		
			if (mysqli_num_rows($data) == 1) {
				//update the token
				$updatepw = "update user set Password = SHA('$p'),Last_Modified = NOW() WHERE Email = '$e'"; 
							
				$result = mysqli_query($dbc,$updatepw) or die("Error is: \n ".mysqli_error($dbc));
				if ($result !== TRUE) {
					// if error, roll back transaction
					header('HTTP/1.0 201 Update password failed', true, 201);
				}
				
				$updatequery = "update resetpassword set Is_Done = 1 WHERE Email = '$e' and Token = '$t'"; 
				
				$result = mysqli_query($dbc,$updatequery) or die("Error is: \n ".mysqli_error($dbc));
				if ($result !== TRUE) {
					// if error, roll back transaction
					header('HTTP/1.0 201 Update password failed', true, 201);
				}
				
			}
			else {
				header('HTTP/1.0 201 Update password failed', true, 201);
			}
			$data->close();
			mysqli_close($dbc);
	
		} 
		else {
			header('HTTP/1.0 201 Update password failed', true, 201);
		} //Error
		
		echo "<script language=\"javascript\">
		location.href=\"http://www.cschedule.org\";
		</script>";
		exit;
	}
?>