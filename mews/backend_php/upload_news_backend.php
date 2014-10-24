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
    $stmt = $dbc->prepare("SELECT user_reputation FROM ".DB_TABLE_USER." WHERE user_id =?");
    $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch ( PDO::FETCH_ASSOC );
    $user_reputation = (int)$row['user_reputation'];

    if($user_reputation<1||$stmt->rowCount()<1){
        header('Location: '.NEWS_FORM);
        $dbc=null;
        die();
    }



    $news_title = $_POST ['news_title'];
    if(isset($_POST ['news_date'])){
        $news_date = $_POST ['news_date'];    
    }
    if(isset($_POST ['news_time'])){
        $news_time = $_POST ['news_time'];    
    }
    if(isset($_POST ['news_address'])&&strlen($_POST ['news_address'])>1&&isset($_POST ['latlng'])){
        $news_address = $_POST ['news_address'];
        $news_latlng = $_POST ['latlng']; 
        $news_latlng = trim($news_latlng, '()');
        $news_latlng_array = explode(",", $news_latlng);
        $news_lat = $news_latlng_array[0];
        $news_lng = $news_latlng_array[1];
    }
    
    $news_type = $_POST ['news_type'];
    if($news_type == "news_blog"){
        if($_SESSION['user_is_special']!=1){
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $stmt = $dbc->prepare("INSERT INTO db_table_hack (`hack_date_time`, `hack_ip`, `hack_facebook_id`) VALUES (NOW(), ?, ?)");
            try {
                $result = $stmt->execute ( array ($ip, $_SESSION["facebook_id"]));
            }catch (Exception $e){
                msg_log($DEBUG_TAG." : ".$e);
            }
            $dbc=null;
            die("檢查到惡意攻擊動作！你的ip地址已被記錄");
        }
    }
    if(isset($_POST ['news_tags'])){
        $news_tags = $_POST ['news_tags'];
        $news_tags_array = explode(",", $news_tags);
        for($i=0;$i<4;$i++){
            if($i+1>count($news_tags_array)){
                $news_tags_array[$i]="";
            }
        }
    }
    $news_content = $_POST ['news_content'];

    $news_img_id = uniqid ();
    $news_img = $_FILES ['news_img'] ['name'];
    $news_img_type = $_FILES ['news_img'] ['type'];
    $news_img_size = $_FILES ['news_img'] ['size'];
    $news_img_name = "";

    // Image handling
    if (is_uploaded_file ( $_FILES ['news_img'] ['tmp_name'] ) && ($news_img_type == 'image/jpeg' || $news_img_type == 'image/jpg' || $news_img_type == 'image/png') ) {
//        
//        msg_log($DEBUG_TAG."MAXFILESIZE: ".$MAXFILESIZE);
//        msg_log($DEBUG_TAG."MAXFILESIZE: ".($news_img_size > $MAXFILESIZE));
//        if($news_img_size > $MAXFILESIZE){
//            $im = imagecreatefromjpeg($_FILES['news_img']['tmp_name']);
//            imagejpeg($im, $_FILES['news_img']['tmp_name'], 60); 
//        }
        $target = $UPLOADPATH . $news_img_id . $news_img;
        if (move_uploaded_file ( $_FILES ['news_img'] ['tmp_name'], $target )) {
            $news_img_name =  $news_img_id.$news_img;
        }   

        if(filesize($target)>$MAXFILESIZE){
            compress_image($target, $target, 60);
        } 

        $thumbnail_img = compress_image($target, dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.GW_THUMBNAIL_IMAGEPATH.$news_img_id.$news_img, 60);
    } else {
        $target = "";
        $thumbnail_img = "";
    }

    for($i=0;$i<4;$i++){
        if($news_tags_array[$i] != ""){
            $stmt = $dbc->prepare ("SELECT `tag_total_no` FROM ".DB_TABLE_TAG." WHERE tag_name =?");
            $stmt->bindValue(1, $news_tags_array[$i], PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch ( PDO::FETCH_ASSOC );
            if($stmt->rowCount()==0){
                $stmt = $dbc->prepare ("INSERT INTO ".DB_TABLE_TAG."(`tag_name`, `tag_date_time`, `tag_last_add`) VALUE (?, NOW(), NOW())");
                try {
                    $result = $stmt->execute( array($news_tags_array[$i]));
                } catch ( Exception $e ) {
                    msg_log ( $DEBUG_TAG.'Insert failed: ' . $e->getMessage () );
                }    
            }
            else{
                $tag_total_no = (int)$row['tag_total_no'];
                $stmt = $dbc->prepare ("UPDATE ".DB_TABLE_TAG." SET tag_total_no =?, tag_last_add =NOW() WHERE tag_name =?");
                try {
                    $result = $stmt->execute( array(
                        $tag_total_no+1,
                        $news_tags_array[$i]
                    ));
                } catch ( Exception $e ) {
                    msg_log ( $DEBUG_TAG.'Insert failed: ' . $e->getMessage () );
                }    
            }
        }
    }
    //NOW() can be used in datetime column
    $stmt = $dbc->prepare ( "INSERT INTO ".DB_TABLE_NEWS."(`news_title`,
            `news_post_date_time`,
            `news_date`,
            `news_time`,
            `news_author_id`,
            `news_content`,
            `news_address`,
            `news_lat`,
            `news_lng`,
            `news_pic1`,
            `news_thumbnail`,
            `news_type`,
            `news_tag_1`,
            `news_tag_2`,
            `news_tag_3`,
            `news_tag_4`) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" );
            
    try {
        $result = $stmt->execute ( array (
            $news_title,
            $news_date,
            $news_time,
            $news_author_id,
            $news_content,
            $news_address,
            $news_lat,
            $news_lng,
            $news_img_name,
            $news_img_name,
            $news_type,
            $news_tags_array[0],
            $news_tags_array[1],
            $news_tags_array[2],
            $news_tags_array[3]
        ) );
    } catch ( Exception $e ) {
        msg_log ( $DEBUG_TAG.'Insert failed: ' . $e->getMessage () );
    }   
    $stmt = $dbc->prepare("UPDATE ".DB_TABLE_USER." SET user_reputation =? WHERE user_id =?");
    $result = $stmt->execute( array(
        $user_reputation-1,
        $_SESSION['user_id']
    ));

    $stmt = $dbc->prepare("SELECT `news_id` FROM ".DB_TABLE_NEWS." WHERE `news_title` =? AND `news_author_id` =?");
    $stmt->bindValue(1, $news_title, PDO::PARAM_STR);
    $stmt->bindValue(2, $news_author_id, PDO::PARAM_STR);
    $stmt->execute();
    $news_id = "";
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $news_id = $row['news_id'];
    }
    
    $dbc=null;
    //TODO REDIRECT TO THAT NEWS
    header('Location: ' . NEWS_PRIMARY."?news_id=".$news_id);


?>
