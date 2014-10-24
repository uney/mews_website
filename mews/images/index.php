<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."msg_constant.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookSession.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookRedirectLoginHelper.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookRequest.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookResponse.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookSDKException.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookRequestException.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."FacebookAuthorizationException.php");
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."Facebook".DIRECTORY_SEPARATOR."GraphObject.php");

 
    use Facebook\FacebookSession;
    use Facebook\FacebookRedirectLoginHelper;
    use Facebook\FacebookRequest;
    use Facebook\FacebookResponse;
    use Facebook\FacebookSDKException;
    use Facebook\FacebookRequestException;
    use Facebook\FacebookAuthorizationException;
    use Facebook\GraphObject;





    $DEBUG_TAG = "index";
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASSWORD;
    $dbname = DB_NAME;
    $rows = 0;
    $page_offset = 3;
    $item_per_page = 5;
        /*
         * PDO Settings                 
         */
        try {
    	    $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
	    		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
	        $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch ( PDOException $e ) {
	        msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }
        try {
        	$stmt = $dbc->prepare ( "SELECT `news_id` FROM ".DB_TABLE_NEWS." WHERE `news_type` <>'news_blog' AND `news_post_date_time` >DATE_SUB(DATE(NOW()), INTERVAL 15 DAY) LIMIT 45" ); 
	    	$stmt->execute ();
            $rows = $stmt->rowCount();

	    } catch ( Exception $e ) {
	    	msg_log ( $DEBUG_TAG. "pagination: " . $e->getMessage () );
	    }
        //break total records into pages
        $pages = ceil(($rows-$page_offset)/$item_per_