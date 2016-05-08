<?php
    require_once __DIR__ . '/facebook-php-sdk-v4-5.0.0/src/Facebook/autoload.php';
    
    session_start();
    date_default_timezone_set('America/Los_Angeles');

    $fb = new Facebook\Facebook([
      'app_id' => '1735092366702517', // Replace {app-id} with your app id
      'app_secret' => '4aef69709f9069bd7f132e39db96dfcc',
      'default_graph_version' => 'v2.5',
      ]);
    $helper = $fb->getRedirectLoginHelper();

    try {
      $accessToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    if (!isset($accessToken)) {
      if ($helper->getError()) {
        header('HTTP/1.0 401 Unauthorized');
        echo "Error: " . $helper->getError() . "\n";
        echo "Error Code: " . $helper->getErrorCode() . "\n";
        echo "Error Reason: " . $helper->getErrorReason() . "\n";
        echo "Error Description: " . $helper->getErrorDescription() . "\n";
      } else {
        header('HTTP/1.0 400 Bad Request');
        echo 'Bad request';
      }
      exit;
    }

    // Logged in
    // echo '<h3>Access Token</h3>';
    // var_dump($accessToken->getValue());

    // The OAuth 2.0 client handler helps us manage access tokens
    $oAuth2Client = $fb->getOAuth2Client();
    // Get the access token metadata from /debug_token
    $tokenMetadata = $oAuth2Client->debugToken($accessToken);
    // echo '<h3>Metadata</h3>';
    // var_dump($tokenMetadata);

    try {
        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId('808572112598548'); // Replace {app-id} with your app id
        $tokenMetadata->validateExpiration();
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        header('HTTP/1.0 400 Bad Request');
        echo 'Validation Failed';
        exit;
    }

    // If you know the user ID this access token belongs to, you can validate it here
    //$tokenMetadata->validateUserId('123');
    // $tokenMetadata->validateExpiration();
    if (!$accessToken->isLongLived()) {
      // Exchanges a short-lived access token for a long-lived one
      try {
        $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
      } catch (Facebook\Exceptions\FacebookSDKException $e) {
        echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
        exit;
      }
      // echo '<h3>Long-lived</h3>';
      // var_dump($accessToken->getValue());
    }


    //-------------At this point, the valid long term token is gotten. CSchedule log in process start------------
    // TODO: there is no remember me flag for facebook log in user. How to deal with it? long session? or short session & check facebook cookie every 30 min

    // $fbApp = new Facebook\FacebookApp('808572112598548', '40f5656571a336e4816cc1188ce202d1');
    // ['fields' => 'email, xxx, yyy ...'] use this format to get whatever is needed
    // The following is what can be retrived 
    // public profile:
    // id
    // name
    // first_name
    // last_name
    // age_range
    // link
    // gender
    // locale
    // picture
    // timezone
    // updated_time
    // verified
    // +++ 
    // email

    $request = new Facebook\FacebookRequest($fbApp, $accessToken->getValue(), 'GET', '/me', ['fields' => 'email, picture']);

    // Send the request to Graph
    try {
      $response = $fb->getClient()->sendRequest($request);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    $graphNode = $response->getGraphNode();

    // access member like this : $graphNode['picture'];
    // TODO: facebook picture includes two part: is_silhouette flag & url
    // TODO: I do not think we need to store picture, we can reply on the url to use up-to-date face profile image
    // use email to check whether it is a come-back user or new user 
    // TODO: new => retrive necessary information to fill our database 
    // TODO: new & old => set session variables FIXIT remember_me flag!!! gc_maxlifetime has to be updated!!!

    // User is logged in with a long-lived access token.
    // You can redirect them to a members-only page.
    //header('Location: https://example.com/members.php');
	
?>