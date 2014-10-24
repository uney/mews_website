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
    $MAXFILESIZE = GW_MAXIMAGESIZE;    
    $UPLOADPATH = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.GW_IMAGEPATH;
    /*
     * PDO Settings         
     */

    $comment_news_comment_id = "";
    $comment_type = "";
    $comment_author_id = $_SESSION['user_id'];
    $comment_content = "";

    try {
        $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
        $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch ( PDOException $e ) {
        msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
    }
    if(isset($_POST ['comment_news_comment_id'])){
        $comment_news_comment_id = $_POST ['comment_news_comment_id'];    
    }
    if(isset($_POST ['comment_type'])){
        $comment_type = $_POST ['comment_type'];    
    }
    if(isset($_POST ['comment_content'])){
        $comment_content = $_POST ['comment_content'];    
    }


    //NOW() can be used in datetime column
    $stmt = $dbc->prepare ( "INSERT INTO ".DB_TABLE_COMMENT."(`comment_news_comment_id`,
            `comment_author_id`,
            `comment_type`,
            `comment_content`,
            `comment_date_time`) VALUES (?, ?, ?, ?,  NOW())" );
            
    try {
        $result = $stmt->execute ( array (
            $comment_news_comment_id,
            $comment_author_id,
            $comment_type,
            $comment_content
        ) );
    } catch ( Exception $e ) {
        msg_log ( $DEBUG_TAG.'Insert failed: ' . $e->getMessage () );
    }   

    if($comment_type=="news"){
        $stmt = $dbc->prepare("SELECT `news_no_comment` FROM ".DB_TABLE_NEWS." WHERE news_id =?");
        $stmt->bindValue(1, $comment_news_comment_id, PDO::PARAM_STR);        
        try {
            $stmt->execute();
            $row = $stmt->fetch ( PDO::FETCH_ASSOC );
            $news_no_comment = (int)$row['news_no_comment'];
            $stmt = $dbc->prepare("UPDATE ".DB_TABLE_NEWS." SET `news_no_comment` =? WHERE news_id =?");
            $stmt->bindValue(1, $news_no_comment+1, PDO::PARAM_STR);        
            $stmt->bindValue(2, $comment_news_comment_id, PDO::PARAM_STR);
            try{
                $stmt->execute(); 
            }
            catch ( Exception $e ) {
                msg_log ( $DEBUG_TAG.'UPDATE failed: ' . $e->getMessage () );
            }   
                 
        } catch ( Exception $e ) {
            msg_log ( $DEBUG_TAG.'Select failed: ' . $e->getMessage () );
        }   
    }
    


    //TODO REDIRECT TO THAT NEWS


?>
