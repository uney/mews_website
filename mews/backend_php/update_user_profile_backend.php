<?php 
    session_start(); //Start the session
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once (dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true){
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

    $DEBUG_TAG = "upload_news_backend";
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASSWORD;
    $dbname = DB_NAME;
    $MAXFILESIZE = GW_MAXPROFILESIZE;    
    $UPLOADPATH = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.GW_USER_IMAGEPATH;
    /*
     * PDO Settings         
     */

    $user_id = $_SESSION['user_id'];
    $user_name = "";
    $user_info = "";
    $user_pic_name = "";
    try {
        $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
        $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch ( PDOException $e ) {
        msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
    }
    $user_info = $_POST ['user_info'];
    $no_profile_pic = $_POST ['no_profile_pic'];
    $user_name = $_POST ['user_name'];


    $user_pic = $_FILES ['user_pic'] ['name'];
    $user_pic_type = $_FILES ['user_pic'] ['type'];
    $user_pic_size = $_FILES ['user_pic'] ['size'];
    $user_pic_name = "";
        msg_log($DEBUG_TAG.", user_pic: ".$user_pic);
        msg_log($DEBUG_TAG.", user_pic_type: ".$user_pic_type);

    // Image handling
    if (is_uploaded_file ( $_FILES ['user_pic'] ['tmp_name'] ) && 
        ($user_pic_type == 'image/jpeg' || $user_pic_type == 'image/jpg' || $user_pic_type == 'image/png')&&
        $no_profile_pic==0) {
        $ext = pathinfo($_FILES ['user_pic'] ['tmp_name'] , PATHINFO_EXTENSION);
        $info = new SplFileInfo($user_pic);
        
        $target = $UPLOADPATH . $user_id . "." . $info->getExtension();
        msg_log($DEBUG_TAG.", is upload file: ".$target);
        
        if (move_uploaded_file ( $_FILES ['user_pic'] ['tmp_name'], $target )) {
            $user_pic_name =  $user_id . "." . $info->getExtension();
            if(filesize($target)>$MAXFILESIZE){
                compress_image($target, $target, 60);
            } 
        }else{
            if(isset($_SESSION['user_pic'])){
                $user_pic_name = $_SESSION['user_pic'];
            }else{
                $user_pic_name = "";
            }
            
        }
        msg_log($DEBUG_TAG.", is upload file: ".$user_pic_name);
        msg_log($DEBUG_TAG.", _SESSION[user_pic]: ".$_SESSION['user_pic']);

    } 
    else if($no_profile_pic==0) {
        $user_pic_name = $_SESSION['user_pic'];
        msg_log($DEBUG_TAG.", is not upload new file: ".$user_pic_name);
    }
    else {
        $user_pic_name = "";
        msg_log($DEBUG_TAG.", is not upload new file: ".$user_pic_name);
    }

    if($user_name!=""&&strlen($user_name)>1){


        //NOW() can be used in datetime column
        $stmt = $dbc->prepare ( "UPDATE ".DB_TABLE_USER." SET user_pic =?, user_name =?, user_info =? WHERE user_id =?" );
            
        try{
            $result = $stmt->execute( array(
                    $user_pic_name,
                    $user_name,
                    $user_info,
                    $_SESSION['user_id']
                ));
            $_SESSION['username'] = $user_name;
        } 
        catch ( PDOException $e ) {
                msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
        }
        if($_POST['apply_for_special']==1&&isset($_POST['user_special_intro'])&&isset($_POST['user_special_ability'])){
            $stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_SPECIAL_PENDING." WHERE pending_user_id =?");
            $stmt->bindValue(1, $_SESSION["user_id"], PDO::PARAM_STR);
            $stmt->execute();
            if($stmt->rowCount()>0||strlen($_POST['user_special_intro'])<1){

            }else{
                $stmt = $dbc->prepare("INSERT INTO ".DB_TABLE_SPECIAL_PENDING." (`pending_user_id`, `pending_special_ability`, `pending_intro`, `pending_date_time`) VALUES (?, ?, ?, NOW())");
                try {
                    $result = $stmt->execute ( array (
                    $_SESSION['user_id'],
                    $_POST['user_special_ability'],
                    $_POST['user_special_intro']));
                } catch ( Exception $e ) {
                    msg_log ( $DEBUG_TAG.' Insert failed: ' . $e->getMessage () );
                }

                $stmt = $dbc->prepare ( "UPDATE ".DB_TABLE_USER." SET `user_is_special` =2, `user_is_special` =? WHERE `user_id` =?" );
                try{
                    $result = $stmt->execute( array(
                        $_POST['user_special_intro'],
                        $_SESSION['user_id']
                    ));
                } 
                catch ( PDOException $e ) {
                    msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
                }
            }
        }
    }


    

    //TODO REDIRECT TO THAT NEWS
    header('Location: ' . HOME_PAGE);


?>
