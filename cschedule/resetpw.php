
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Online Payment for E2WStudy</title>
	<meta name="keywords" content="" />
	<meta name="description" content= " />


	<script type="text/javascript">  
	<!--
	function validate(form) { 
		var e = form.elements; 

		/* Your validation code. */ 
			if(e['password1'].value != e['password2'].value) { 
			alert('Your passwords do not match. Please type more carefully.'); 
			return false; 
		} 
		return true; 
	}
	-->
	</script>
	</head>
	<body>
	  <div align="center">
		<h2>Change Your Password</h2>
		<form action="password.php" method="post" onsubmit="return validate(this);">
			<input type="hidden" name="email" size="50" maxlength="100" value="<?php echo $_GET['email']; ?>" />
			<input type="hidden" name="token" size="100" maxlength="200" value="<?php echo $_GET['sig']; ?>" />
			<p>New Password</p>
			<input type="password" name="password1" size="30" maxlength="100" />
			<p>Confirm New Password</p>
			<input type="password" name="password2" size="30" maxlength="100" />
			<p><input type="submit" name="submit" value="Reset" /></p>
			<input type="hidden" name="submitted" value="TRUE" />
		</form>
		<p style="text-align:center">      Copyright &#169 2013 E2Wstudy, LLC.  All rights reserved. </p>
	 </div>
	</body>
</html>
