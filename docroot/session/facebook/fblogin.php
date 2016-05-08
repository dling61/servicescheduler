<?php
	require_once '../../config.php';
	require_once  TOP_RESTFUL_DIR. '/facebook-php-sdk-v4-5.0.0/src/Facebook/autoload.php';

	session_start();
	$fb = new Facebook\Facebook([
		'app_id' => '1735092366702517', // Replace {app-id} with your app id
		'app_secret' => '4aef69709f9069bd7f132e39db96dfcc',
		'default_graph_version' => 'v2.5',
	]);

	$helper = $fb->getRedirectLoginHelper();

	$permissions = ['email']; // Optional permissions
	// define a callback URL for Facebook to call
	//$loginUrl = $helper->getLoginUrl('http://fb.local/fb-callback.php', $permissions);
	$loginUrl = $helper->getLoginUrl(FACEBOOK_CALLBACK, $permissions);
	
	$fb_target = str_replace('&amp;', '&', $loginUrl);
	header('Location: ' . $fb_target);
	// echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
?>