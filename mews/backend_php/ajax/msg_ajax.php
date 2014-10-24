<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once (dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.php");

    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
        //do nothing if the user is logged in
    }else{
        $url = REDIRECT_PAGE;
        die();
    }
    // echo phpinfo();
    // ini_set('display_errors',1);
    // echo var_dump($_FILES);
    /**
     * msg_log function for error log
     */
    //echo dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."php".DIRECTORY_SEPARATOR."config.php";

    $DEBUG_TAG = "somment_form_submit";
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASSWORD;
    $dbname = DB_NAME;

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

    if($_POST['action']=="read"){
        $stmt = $dbc->prepare("UPDATE ".DB_TABLE_MSG." SET `msg_read` =1 WHERE `msg_id` =?");
        $stmt->bindValue(1, $_POST['msg_id'], PDO::PARAM_STR);        
        try {
            $stmt->execute();   
        } catch ( Exception $e ) {
            msg_log ( $DEBUG_TAG.'Select failed: ' . $e->getMessage () );
        }   
    }
    if($_POST['action']=="first_login"){
        $stmt = $dbc->prepare("UPDATE ".DB_TABLE_USER." SET `user_first_login` =0 WHERE `user_id` =?");
        $stmt->bindValue(1, $_POST['user_id'], PDO::PARAM_STR);        
        try {
            $stmt->execute();   
        } catch ( Exception $e ) {
            msg_log ( $DEBUG_TAG.'Select failed: ' . $e->getMessage () );
        }   
    }
    
    $dbc=null;


    //TODO REDIRECT TO THAT NEWS


?>
