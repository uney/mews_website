<?php 

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $hacking_detected = false;
    if($_SESSION['user_is_admin']==1){
        $stmt = $dbc->prepare("SELECT `user_id` FROM ".DB_TABLE_USER." WHERE user_id =? AND user_is_admin =1");
        $stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_STR);
        $stmt->execute();
        if($stmt->rowCount()<1){
            $hacking_detected = true;
        }
    }
    else{
        $hacking_detected = true;
    }
    if($hacking_detected){
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
   
?>