<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once (dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR."backend_php".DIRECTORY_SEPARATOR."config.php");

    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true&& $_SESSION['user_is_admin'] == 1){
        //do nothing if the user is logged in
    }else{
        $url = REDIRECT_PAGE;
        header('Location: '.$url);
    }
    $url = "http://wwww.mews.mobi/super_johnny_and_joyce_dont_hack_please/admin_list.php?admin_action=";
    $DEBUG_TAG = "admin_action_update";
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
    if(isset($_GET['from'])){
        $url .= $_GET['from'];
    }
    if(isset($_GET['from'])&&isset($_GET['action'])&&(isset($_GET['news_id'])||isset($_GET['pending_id']))&&isset($_GET['user_id'])){        
        if($_GET['action']=="add_hot"){
            $mysql_stmt = "UPDATE ".DB_TABLE_NEWS." SET `news_is_hot` =1 WHERE `news_id` =?";
        }else if($_GET['action']=="block"){
            $mysql_stmt = "UPDATE ".DB_TABLE_NEWS." SET `news_deleted` =1 WHERE `news_id` =?";
        }else if($_GET['action']=="cancel"){
            $mysql_stmt = "UPDATE ".DB_TABLE_NEWS." SET `news_never_spam` =1 WHERE `news_id` =?";
        }else if($_GET['action']=="deny_application"){
            $mysql_stmt = "UPDATE ".DB_TABLE_SPECIAL_PENDING." SET `pending_result` =2 WHERE `pending_id` =?";
        }else if($_GET['action']=="approve_application"){
            $mysql_stmt = "UPDATE ".DB_TABLE_SPECIAL_PENDING." SET `pending_result` =1 WHERE `pending_id` =?";
        }
    }else if(isset($_GET['from'])&&isset($_GET['action'])&&isset($_GET['user_ip'])){
        if($_GET['action']=="block_user"){
            $mysql_stmt = "SELECT * FROM ".DB_TABLE_REPUTATION." WHERE `rep_giver_ip` =?";
        }else if($_GET['action']=="cancel_alert"){
            $mysql_stmt = "UPDATE ".DB_TABLE_REPUTATION." SET `rep_never_spam` =1 WHERE `rep_giver_ip` =?";
        }
    }
    else{
        $url = REDIRECT_PAGE;
        header('Location: '.$url);
    }
    $stmt = $dbc->prepare ( $mysql_stmt );
    if($_GET['action']=="add_hot"||$_GET['action']=="block"||$_GET['action']=="cancel"){
        try{
            $result = $stmt->execute( array(
                $_GET['news_id']
            ));
        } 
        catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }
        $stmt = $dbc->prepare ("SELECT * FROM ".DB_TABLE_USER." WHERE `user_id` =?");
        $stmt->bindValue(1, $_GET['user_id'], PDO::PARAM_STR);
        try{
            $stmt->execute();
        }
        catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        
            $user_reputation = (int)$row['user_reputation'];
            if($_GET['action']=="add_hot"){
                $user_reputation = $user_reputation+20;
            }else if($_GET['action']=="block"){
                $user_reputation = $user_reputation-20;
            }else if($_GET['action']=="cancel"){
                $user_reputation = $user_reputation;
            }
            $stmt2 = $dbc->prepare("UPDATE ".DB_TABLE_USER." SET `user_reputation` =? WHERE `user_id` =?");
            $stmt2->bindValue(1, $user_reputation, PDO::PARAM_STR);
            $stmt2->bindValue(2, $_GET['user_id'], PDO::PARAM_STR);
            try{
                $stmt2->execute();
            }
            catch ( PDOException $e ) {
                msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
            }
        }
    }
    else if($_GET['action']=="approve_application"||$_GET['action']=="deny_application"){
        try{
            $result = $stmt->execute( array(
                $_GET['pending_id']
            ));
        } 
        catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }
        $user_is_special = 0; 
        $user_special_intro = "";
        $pending_result = 0;
        $stmt = $dbc->prepare ("SELECT * FROM ".DB_TABLE_SPECIAL_PENDING." WHERE `pending_id` =?");
        $stmt->bindValue(1, $_GET['pending_id'], PDO::PARAM_STR);
        try{
            $stmt->execute();
        }
        catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
            $user_special_intro = $row['pending_intro'];
        }
        if($_GET['action']=="deny_application"){
            $user_special_intro = "";
            $user_is_special = 0;     
            $pending_result = 1;  
            $msg_content = "對不起，你的申請未被接納";     
        }
        if($_GET['action']=="approve_application"){
            $user_is_special = 1;     
            $pending_result = 2;       
            $msg_content = "<p>你已成功申請成為專場會員，現在你可以在發表報導時選擇發表專場報道</p>".
                           "<p>所有的專場報導都會出現在分類報導的首頁，但卻不會出現在最熱新聞列表</p>".
                           "<img class='tutorial_img' src='images/tutorial/blog_tutorial.png'></img>";     
        }
        $stmt = $dbc->prepare("UPDATE ".DB_TABLE_USER." SET `user_is_special` =?, `user_special_intro` =? WHERE `user_id` =?");            
        $stmt->bindValue(1, $user_is_special, PDO::PARAM_STR);
        $stmt->bindValue(2, $user_special_intro, PDO::PARAM_STR);
        $stmt->bindValue(3, $_GET['user_id'], PDO::PARAM_STR);
        try{
            $stmt->execute();
        }
        catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }
        $stmt = $dbc->prepare("UPDATE ".DB_TABLE_SPECIAL_PENDING." SET `pending_result` =? WHERE `pending_id` =?");            
        $stmt->bindValue(1, $pending_result, PDO::PARAM_STR);
        $stmt->bindValue(2, $_GET['pending_id'], PDO::PARAM_STR);
        try{
            $stmt->execute();
        }
        catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }

        $mysql_stmt2 = "INSERT INTO ".DB_TABLE_MSG." (`msg_from`, `msg_to`, `msg_content`, `msg_date_time`) VALUES (?, ?, ?, NOW())";
        $stmt = $dbc->prepare($mysql_stmt2);
        $stmt->bindValue(1, "admin", PDO::PARAM_STR);
        $stmt->bindValue(2, $_GET['user_id'], PDO::PARAM_STR);
        $stmt->bindValue(3, $msg_content, PDO::PARAM_STR);
        try{
            $stmt->execute();
        }
        catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }
    }else if($_GET['action']=="block_user"||$_GET['action']=="cancel_alert"){
        try{
            $result = $stmt->execute( array(
                $_GET['user_ip']
            ));
        } 
        catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }
        if($_GET['action']=="block_user"){
            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                $stmt2 = $dbc->prepare("UPDATE ".DB_TABLE_USER." SET `user_blocked` =1 WHERE `user_id` =?");
                $stmt2->bindValue(1,$row['rep_giver'],PDO::PARAM_STR);
                $stmt2->execute();
            }
            $stmt3 = $dbc->prepare("UPDATE ".DB_TABLE_REPUTATION." SET `rep_never_spam` =1 WHERE `rep_giver_ip` =?");
            $stmt3->bindValue(1,$_GET['user_ip'],PDO::PARAM_STR);
            $stmt3->execute();
        }else if($_GET['action']=="warning_msg"){
        }

    }



    

    //TODO REDIRECT TO THAT NEWS
    header('Location: ' . $url);


?>
