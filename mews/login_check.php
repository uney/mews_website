<?php
    $pagename=strtolower(basename($_SERVER['REQUEST_URI']));
    $pagename_array = explode("?", $pagename);
    $pagename = $pagename_array[0];
    $pagename_sub = "";
    if(count($pagename_array)>1){
        $pagename_sub = $pagename_array[1];
    }

    if(isset($_SESSION['user_id'])){

    }
    else{
        if(isset($_COOKIE['user_id'])){
            try {
                //$stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_USER ."WHERE user_id = ?"); 
                $stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_USER ." WHERE user_id =?"); 
                $stmt->bindValue ( 1, $_COOKIE['user_id'], PDO::PARAM_INT );
                $stmt->execute ();
                msg_log ( $DEBUG_TAG. ": Check login" );
            } catch ( Exception $e ) {
                msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
            }
            if (($stmt->rowCount ()) > 0) {
                // logged in before
                while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['username'] = $row['user_name'];
                    $_SESSION['user_is_admin'] = $row['user_is_admin'];
                    $_SESSION['user_is_special'] = $row['user_is_special'];
                    $_SESSION['logged_in'] = true;
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
            }
            
        }    
    }
    if(isset($_COOKIE['user_id_2'])&&!isset($_SESSION['user_id'])){
        try {
            //$stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_USER ."WHERE user_id = ?"); 
            $stmt = $dbc->prepare ( "SELECT * FROM ".DB_TABLE_USER ." WHERE user_id =?"); 
            $stmt->bindValue ( 1, $_COOKIE['user_id_2'], PDO::PARAM_INT );
            $stmt->execute ();
            msg_log ( $DEBUG_TAG. ": Check login" );
        } catch ( Exception $e ) {
            msg_log ( $DEBUG_TAG. ": " . $e->getMessage () );
        }
        if (($stmt->rowCount ()) > 0) {
            // logged in before
            while ( $row = $stmt->fetch ( PDO::FETCH_ASSOC ) ) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['user_name'];
                $_SESSION['user_is_admin'] = $row['user_is_admin'];
                $_SESSION['user_is_special'] = $row['user_is_special'];
                $_SESSION['logged_in'] = true;
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
        }
        
    }
    if(!isset($_COOKIE['tutorial_done'])&&$pagename!="primary.php"){
        header('Refresh: 0; URL=tutorial.php');
    }
?>
<script>
var user_id = <?php 
    if(isset($_SESSION['user_id'])){
        echo "'".$_SESSION['user_id']."'";
    }else{
        echo "'0'";
    }
    ?>;
if( user_id != "0"){
    createCookie("user_id", user_id, 36500);
    createCookie("ihatecookies", user_id, 36500);
}
function createCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/ ;domain=.mews.mobi";
}
</script>