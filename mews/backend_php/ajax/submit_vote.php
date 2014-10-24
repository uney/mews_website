<?php
header ( "Content-Type:text/html; charset=utf-8" );
error_reporting(E_ALL);
ini_set('display_errors', 1);
/*
 * check if user voted for himself
 * check if user voted before
 * check if user has enough reputation
 * insert action into reputation table
 * update user reputation (giver and receiver)
 * update action number in news table
 */
// define('MYROOT', dirname(dirname(__FILE__)));
//define('MYROOT', dirname(dirname(__FILE__))); 
require_once (dirname (dirname (__FILE__)).DIRECTORY_SEPARATOR."config.php");
//require_once("Connect.php") or die("require died");
$dbhost = DB_HOST;
$dbuser = DB_USER;
$dbpass = DB_PASSWORD;
$dbname = DB_NAME;  
$DEBUG_TAG = "ajax/submit_vote";
try {
    $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
    $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch ( PDOException $e ) {
    msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
}    

if(isset($_POST['action'])&&isset($_POST['type'])){
	$rep_type;
	$number_type;
	$no_of_vote = $_POST['no_of_vote'];
	if( $_POST['vote_giver_id'] != $_POST['vote_receiver_id']){
		$rep_type = $_POST['type'];


        msg_log ( $DEBUG_TAG.'Check vote action: ' . $_POST['action']);
        msg_log ( $DEBUG_TAG.'Check vote type: ' . $_POST['type']);
        msg_log ( $DEBUG_TAG.'Check vote news_comment_id: ' . $_POST['news_comment_id']);
        msg_log ( $DEBUG_TAG.'Check vote no_of_vote: ' . $_POST['no_of_vote']);
        msg_log ( $DEBUG_TAG.'Check vote vote_receiver_id: ' . $_POST['vote_receiver_id']);
        msg_log ( $DEBUG_TAG.'Check vote vote_giver_id: ' . $_POST['vote_giver_id']);

	    $point_to_receiver = 0;
		$point_to_giver = 0;
		$giver_point = 0;
		$report_spam = false;
		$number_type = "";
		if($_POST['action']=="news_upvote"){
			$point_to_receiver = 5;
			$number_type = "news_no_useful";
		}
		if($_POST['action']=="news_downvote"){
			$point_to_receiver = -4;
			$point_to_giver = -2;
			$number_type = "news_no_rubbish";
		}
		if($_POST['action']=="news_spam"){
		   	$report_spam = true;
			$number_type = "news_no_spam";
		}
		if($_POST['action']=="comment_upvote"){
			$point_to_receiver = 3;
			$number_type = "comment_no_useful";
		}
		if($_POST['action']=="comment_downvote"){
			$point_to_receiver = -2;
			$point_to_giver = -1;
			$number_type = "comment_no_rubbish";
		}
		if($_POST['action']=="comment_spam"){
			$report_spam = true;
			$number_type = "comment_no_spam";
		}

        if($report_spam){
        	$stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_SPAM." WHERE spam_reporter_id =? AND spam_news_comment_id =? AND spam_type =?");
			$stmt->bindValue(1, $_POST['vote_giver_id'], PDO::PARAM_STR);
			$stmt->bindValue(2, $_POST['news_comment_id'], PDO::PARAM_STR);
			$stmt->bindValue(3, $rep_type, PDO::PARAM_STR);
			try{
				$stmt->execute();
			}catch(Exception $e){
				msg_log($DEBUG_TAG." : ".$e);
			}
			
            if($stmt->rowCount()<1){
                 $stmt = $dbc->prepare("INSERT INTO ".DB_TABLE_SPAM." (`spam_news_comment_id`,
				`spam_reporter_id`,
				`spam_date_time`,
				`spam_type`) VALUES (?,?,NOW(),?)");
                 
                try{
					$stmt->execute ( array (
					    $_POST['news_comment_id'],
					    $_POST['vote_giver_id'],
					    $rep_type
		 	        ));
				}catch(Exception $e){
					msg_log($DEBUG_TAG." : ".$e);
				}
			
            }else{
            	$dbc=null;
            	echo 3;
            	die();
            }

		}
		/**end of spam reporting**/
		/**end of spam reporting**/
		/**end of spam reporting**/
		/**end of spam reporting**/
		/**end of spam reporting**/

		$stmt = $dbc->prepare("SELECT * FROM ".DB_TABLE_REPUTATION." WHERE rep_news_comment_id =? AND rep_giver =? AND rep_type =?");
		$stmt->bindValue(1, $_POST['news_comment_id'], PDO::PARAM_STR);
		$stmt->bindValue(2, $_POST['vote_giver_id'], PDO::PARAM_STR);
		$stmt->bindValue(3, $rep_type, PDO::PARAM_STR);
		try{
    		$stmt->execute();
		}catch ( PDOException $e ) {
            msg_log ( $DEBUG_TAG.'Check user voted failed: ' . $e->getMessage () );
        }   
		if($stmt->rowCount()>0){
			$dbc = null;
			echo 2; // voted before
			die();
		}
		else{

			if($point_to_giver < 0){
				// update user reputation
				$stmt = $dbc->prepare("SELECT user_reputation FROM ".DB_TABLE_USER." WHERE user_id =?");
				$stmt->bindValue(1, $_POST['vote_giver_id'], PDO::PARAM_STR);
				$stmt->execute();
                $row = $stmt->fetch ( PDO::FETCH_ASSOC );
            	$giver_point = (int)$row['user_reputation'];
			}


			if($report_spam==false){
                $stmt = $dbc->prepare("SELECT user_reputation FROM ".DB_TABLE_USER." WHERE user_id =?");
		    	$stmt->bindValue(1, $_POST['vote_receiver_id'], PDO::PARAM_STR);
		    	$stmt->execute();
                $row = $stmt->fetch ( PDO::FETCH_ASSOC );
                $receiver_point = (int)$row['user_reputation'];
            }

            if($giver_point >= (-$point_to_giver)){
            	$stmt = $dbc->prepare("INSERT INTO ".DB_TABLE_REPUTATION." (`rep_news_comment_id`,
				`rep_giver`,
				`rep_receiver`,
				`rep_action`,
				`rep_type`,
				`rep_giver_point`,
				`rep_receiver_point`,
				`rep_date_time`) VALUES (?,?,?,?,?,?,?,NOW())");
				try {
				$result = $stmt->execute ( array (
					$_POST['news_comment_id'],
					$_POST['vote_giver_id'],
					$_POST['vote_receiver_id'],
					$_POST['action'],
					$rep_type,
					$point_to_giver,
					$point_to_receiver
		 	    ));
		 	    } catch ( PDOException $e ) {
                    msg_log ( $DEBUG_TAG.'insert to reputation failed: ' . $e->getMessage () );
                }

		 	    $receiver_point = $receiver_point+$point_to_receiver;
		 	    $giver_point = $giver_point+$point_to_giver;

		 	    //UPDATE giver and receiver points'
		 	    //To have better efficiency, check if giver point should change before updating
		 	    if($point_to_receiver !=0 ){
		 	    	$stmt = $dbc->prepare("UPDATE ".DB_TABLE_USER." SET user_reputation =? WHERE user_id =?");
		 	    	try {
						$result = $stmt->execute ( array (
						$receiver_point,
						$_POST['vote_receiver_id']
		 	   		));
		 	    	} catch ( PDOException $e ) {
                    	msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
                	}
		 	    }
		 	    

		 	    if($point_to_giver != 0){
		 	    	$stmt = $dbc->prepare("UPDATE ".DB_TABLE_USER." SET user_reputation =? WHERE user_id =?");
		 	    	$result = $stmt->execute( array(
		 	    		$giver_point,
		 	    		$_POST['vote_giver_id']
		 	    	));
		 	    }
		 	    if($rep_type == "news"){
		 	    	$table = DB_TABLE_NEWS;
		 	    	$column_name = "news_id";
		 	    }else{
		 	    	$table = DB_TABLE_COMMENT;
		 	    	$column_name = "comment_id";
		 	    }

		 	    //udpate news or comment table
		 	    $stmt = $dbc->prepare("UPDATE ".$table." SET ".$number_type." =? WHERE ".$column_name." =?");
		 	    try{
		 	    $result = $stmt->execute( array(
		 	    		$no_of_vote,
		 	    		$_POST['news_comment_id']
		 	    	));
		 	    } catch ( PDOException $e ) {
                    msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
                    msg_log ( $DEBUG_TAG." : UPDATE ".$table." SET ".$number_type." =? WHERE ".$column_name." =?");
                }

            }
            else{
            	$dbc=null;
                echo 4; // not enough point
           	    die();
            }
            $dbc=null;
            echo 0; // success
            die();
		}
	}
	else{
		$dbc = null;
		echo 1; // self voting
		die();
	}
}
else{
	echo 5; // error
	$dbc = null;
	die();
}


/*
* end of DB
*/
$dbc = null;

?>