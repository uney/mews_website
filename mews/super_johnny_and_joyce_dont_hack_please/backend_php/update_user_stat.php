<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    //ini_set('display_errors', 1);
    require_once (dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");

    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true && $_SESSION['user_is_admin'] == 1){
        //do nothing if the user is logged in
    }else{
        $url = REDIRECT_PAGE;
        header('Location: '.$url);
    }

    // echo phpinfo();
    // ini_set('display_errors',1);
    // echo var_dump($_FILES);
    /**
     * msg_log function for error log
     */
    //echo dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."php".DIRECTORY_SEPARATOR."config.php";

    $DEBUG_TAG = "update_user_stat";
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASSWORD;
    $dbname = DB_NAME;
    $MAXFILESIZE = GW_MAXIMAGESIZE;    
    $UPLOADPATH = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.GW_IMAGEPATH;
    /*
     * PDO Settings         
     */

    $news_title = "";
    $news_date = "";
    $news_time = "";
    $news_author_id = $_SESSION['user_id'];
    $news_content = "";
    $news_address = "";
    $news_lat = 0;
    $news_lng = 0;
    $news_img_name = "";
    $news_img_name = "";
    $news_type = "";
    $news_tags_array = array("","","","");
    try {
        $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
        $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch ( PDOException $e ) {
        msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
    }
    include(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."admin_check.php");



    $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_USER);
    try{
        $stmt->execute();
    }catch(Exception $e){
        msg_log($DEBUG_TAG." : ".$e);
    }
    while($row = $stmt->fetch ( PDO::FETCH_ASSOC )){
        $user_post=0;
        $user_up=0;
        $user_down=0;
        $user_read=0;

        $stmt2 = $dbc->prepare("SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_author_id` =?");
        $stmt2->bindValue(1, $row['user_id'], PDO::PARAM_STR);
        try{
            $stmt2->execute();
        }catch(Exception $e){
            msg_log($DEBUG_TAG." : ".$e);
        }
        while($row2 = $stmt2->fetch ( PDO::FETCH_ASSOC )){
            $user_read = $user_read+$row2['news_no_read'];
            $user_post = $user_post+1;
            $user_up = $user_up+$row2['news_no_useful'];
            $user_down = $user_down+$row2['news_no_rubbish'];
        }
        $stmt2 = $dbc->prepare("SELECT * FROM ".DB_TABLE_COMMENT." WHERE `comment_author_id` =?");
        $stmt2->bindValue(1, $row['user_id'], PDO::PARAM_STR);
        try{
            $stmt2->execute();
        }catch(Exception $e){
            msg_log($DEBUG_TAG." : ".$e);
        }
        while($row2 = $stmt2->fetch ( PDO::FETCH_ASSOC )){
            $user_up = $user_up+$row2['comment_no_useful'];
            $user_down = $user_down+$row2['comment_no_rubbish'];
        }
        $stmt2 = $dbc->prepare("UPDATE ".DB_TABLE_USER." SET `user_post` =?, `user_read` =?, `user_up` =?, `user_down` =? WHERE `user_id` =?");
        try{
            $result = $stmt2->execute(array(
                    $user_post,
                    $user_read,
                    $user_up,
                    $user_down,
                    $row['user_id']));
        }catch(Exception $e){
            msg_log($DEBUG_TAG." : ".$e);
        }
    }
    

    $dbc=null;
    //TODO REDIRECT TO THAT NEWS
    header('Location: http://www.mews.mobi/super_johnny_and_joyce_dont_hack_please/admin_panel.php');


?>
