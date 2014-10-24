<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");



    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."HttpClients".DIRECTORY_SEPARATOR."FacebookHttpable.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."HttpClients".DIRECTORY_SEPARATOR."FacebookCurl.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."HttpClients".DIRECTORY_SEPARATOR."FacebookCurlHttpClient.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."Entities".DIRECTORY_SEPARATOR."AccessToken.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."Entities".DIRECTORY_SEPARATOR."SignedRequest.php");

    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookSession.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookRedirectLoginHelper.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookRequest.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookResponse.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookSDKException.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookRequestException.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookAuthorizationException.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."GraphObject.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookServerException.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."GraphSessionInfo.php");


    use Facebook\Entities\AccessToken;
    use Facebook\FacebookSession;
    use Facebook\FacebookRedirectLoginHelper;
    use Facebook\FacebookRequest;
    use Facebook\FacebookResponse;
    use Facebook\FacebookSDKException;
    use Facebook\FacebookRequestException;
    use Facebook\FacebookAuthorizationException;
    use Facebook\GraphObject;    
    use Facebook\FacebookServerException;
    use Facebook\GraphSessionInfo;

    $DEBUG_TAG = "facebook_login";
    $redirectTo = HOME_PAGE;
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASSWORD;
    $dbname = DB_NAME;
    $loginRedirect = LOGIN_PAGE;
    $user_id = "";

    
    if(isset($_GET['from'])){
        if($_GET['from']=="index"){
            $loginRedirect .= "?from=index"; 
            $redirectTo = HOME_PAGE;
        }else if($_GET['from']=="news_form"){
            $loginRedirect .= "?from=news_form"; 
            $redirectTo = NEWS_FORM;
        }else if($_GET['from']=="news_primary"){
            if(isset($_GET['news_id'])){
                $loginRedirect .= "?from=news_primary&news_id=".$_GET['news_id']; 
                $redirectTo = NEWS_PRIMARY."?news_id=".$_GET['news_id'];
            }     
        }
    }
    
    FacebookSession::setDefaultApplication(FACEBOOK_APP_ID,FACEBOOK_APP_SECRET);
    $helper = new FacebookRedirectLoginHelper( $loginRedirect );
    
    // see if a existing session exists
    if (isset($_SESSION) && isset($_SESSION['facebook_token'])) {
        // create new session from saved access_token
        $session = new FacebookSession($_SESSION['facebook_token']);
        // validate the access_token to make sure it's still valid
        try {
            if (!$session->validate()) {
                $session = null;
            }
        } catch (Exception $e) {
            // catch any exceptions
            $session = null;
        }
    } else {
        // no session exists
        try {
            $session = $helper->getSessionFromRedirect();
        } catch (FacebookRequestException $ex) {
            // When Facebook returns an error
        } catch (Exception $ex) {
            // When validation fails or other local issues
            echo $ex->message;
        }
    }


    // see if we have a session
    if (isset($session)) {
        // save the session
        $_SESSION['facebook_token'] = $session->getToken();
        // create a session using saved token or the new one we generated at login
        $session = new FacebookSession($session->getToken());
        // graph api request for user data
        $request = new FacebookRequest($session, 'GET', '/me');
        $response = $request->execute();
        $graphObject = $response->getGraphObject()->asArray();

        $_SESSION['logged_in'] = true;

        $_SESSION['facebook_timeout'] = time();
        $_SESSION['user_is_special'] = 0;
        $_SESSION['username'] = $graphObject['name'];
        $_SESSION['facebook_id'] = $graphObject['id'];
        $_SESSION['user_id'] = md5($_SESSION['facebook_id']);
        $_SESSION['facebook_gender'] = $graphObject['gender'];

        // logout and destroy the session, redirect url must be absolute url
        $linkLogout = $helper->getLogoutUrl($session, HOME_PAGE);
        $_SESSION['facebook_logout_link'] = $linkLogout;
        


        /*
         * Check if user logged in before,
         * . if yes, redirect user to the page before 
         * . if no, save all basic information and redirect user to setting page
         */
        try {
            $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
            $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }
        try {
            //$stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_USER ."WHERE user_id = ?"); 
            $stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_USER ." WHERE user_id =?"); 
            $stmt->bindValue ( 1, $_SESSION['user_id'], PDO::PARAM_INT );
            $stmt->execute ();
        } catch ( Exception $e ) {
            msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
        }
        if (($stmt->rowCount ()) > 0) {
            // logged in before
            while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {
                $_SESSION['username'] = $row['user_name'];
                $_SESSION['user_is_admin'] = $row['user_is_admin'];
                $_SESSION['user_is_special'] = $row['user_is_special'];
                $user_reputation = (int)$row['user_reputation'];
                $user_last_add_point = date($row['user_last_add_point']);
                if($user_last_add_point< date('Y-m-d') ){
                    try {
                        $stmt2 = $dbc->prepare ( "UPDATE ".DB_TABLE_USER ." SET `user_reputation` =?, `user_last_add_point` =NOW() WHERE user_id =?"); 
                        $user_reputation = $user_reputation +1;
                        $stmt2->bindValue ( 1, $user_reputation, PDO::PARAM_INT );
                        $stmt2->bindValue ( 2, $_SESSION['user_id'], PDO::PARAM_INT );
                        $stmt2->execute ();
                    } catch ( Exception $e ) {
                        msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
                    }
                }
            }
            try{
                $stmt3 = $dbc->prepare ( "UPDATE ".DB_TABLE_USER ." SET `user_last_login` =NOW() WHERE user_id =?"); 
                $stmt3->bindValue ( 1, $_SESSION['user_id'], PDO::PARAM_INT );
                $stmt3->execute ();
            } catch ( Exception $e ) {
                msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
            }
            header('Location: '.$redirectTo);
        }else{
            $redirectTo = EDIT_PROFILE;
            msg_log ( $DEBUG_TAG. " Check ROW COUNT: " . $stmt->rowCount ());

            // first login
            // user profile picture handling

            $headers = get_headers('http://graph.facebook.com/'.$_SESSION['facebook_id'].'/picture?type=large',1);
            $profileimage = $headers['Location']; //fb user image URL
            $ch = curl_init($profileimage);
            $fp = fopen(GW_USER_IMAGEPATH.'/'.$_SESSION['user_id'].'.jpg', 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            $stmt = $dbc->prepare ( "INSERT INTO ".DB_TABLE_USER."(
            `user_id`,
            `user_facebook_id`,
            `user_name`,
            `user_gender`,
            `user_pic`,
            `user_reputation`,
            `user_since`,
            `user_last_add_point`, 
            `user_last_login`) VALUES (?,?,?,?,?,?,NOW(),NOW(),NOW())" );
        
            try {
                $result = $stmt->execute ( array (
                    $_SESSION['user_id'],
                    $_SESSION['facebook_id'],
                    $_SESSION['username'],
                    $_SESSION['facebook_gender'],
                    $_SESSION['user_id'].".jpg",
                    1));
            } catch ( Exception $e ) {
                msg_log ( $DEBUG_TAG.' Insert failed: ' . $e->getMessage () );
            }
            //after insert, redirect user to setting page
            //TODO for testing, redirect user to home page
            header('Location: '.$redirectTo);
        }


    } else {
        $params = array(
            'scope' => 'read_stream, friends_likes',
            'redirect_uri' => $redirectTo
        );
        header('Location: ' . $helper->getLoginUrl(array('redirect_uri' => $_SERVER['SCRIPT_URI'],'scope' => 'user_about_me, read_stream, friends_likes')));
    }
 ?>

