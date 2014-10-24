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
	$DEBUG_TAG = "ajax/follow";
	try {
	    $dbc = new PDO ( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array (
	    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) );
	    $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch ( PDOException $e ) {
	    msg_log ( $DEBUG_TAG.'Connection failed: ' . $e->getMessage () );
	}    
	if(isset($_POST['follow_action'])&&isset($_POST['follow_following'])&&isset($_POST['follow_follower'])&&isset($_POST['follow_type'])){
		if($_POST['follow_following']==$_POST['follow_follower']){
			echo 2;
			$dbc=null;
			die();
		}

		$mysql_stmt = "";
		$follow_type = $_POST['follow_type'];
		$follow_follower = $_POST['follow_follower'];
		$follow_following = $_POST['follow_following'];
		$follow_action = $_POST['follow_action'];
		$mysql_stmt = "SELECT * FROM ".DB_TABLE_FOLLOW." WHERE `follow_type` =? AND `follow_following` =? AND `follow_follower` =?";
		$stmt = $dbc->prepare($mysql_stmt);
		$stmt->bindValue(1, $follow_type, PDO::PARAM_STR);
		$stmt->bindValue(2, $follow_following, PDO::PARAM_STR);
		$stmt->bindValue(3, $follow_follower, PDO::PARAM_STR);
		try{
			$stmt->execute();
		}
		catch(Exception $e){
			msg_log($DEBUG_TAG." : ".$e);
		}
		$add_before = false;
		if($stmt->rowCount()>0){
			$add_before = true;
		}
		if($_POST['follow_action']=="follow"&&$add_before==false){
			$mysql_stmt = "INSERT INTO ".DB_TABLE_FOLLOW." (
				`follow_type`,
				`follow_following`,
				`follow_follower`,
				`follow_date_time`,
				`follow_last_seen`) VALUES (?,?,?,NOW(),NOW())";
		}
		else if($_POST['follow_action']=="unfollow"&&$add_before==true){
			$mysql_stmt = "DELETE FROM ".DB_TABLE_FOLLOW." WHERE `follow_type` =? AND `follow_following` =? AND `follow_follower` =?";
		}
		else{
			echo 5;
			$dbc=null;
			die();
		}
		$stmt = $dbc->prepare($mysql_stmt);
		$stmt->bindValue(1, $follow_type, PDO::PARAM_STR);
		$stmt->bindValue(2, $follow_following, PDO::PARAM_STR);
		$stmt->bindValue(3, $follow_follower, PDO::PARAM_STR);
		try{
			$stmt->execute();
			if($follow_type == "user"){
				$user_no_follower = 0;
				$mysql_stmt = "SELECT `user_no_follower` FROM ".DB_TABLE_USER." WHERE `user_id` =?";
				$stmt = $dbc->prepare($mysql_stmt);
				$stmt->bindValue(1, $follow_following, PDO::PARAM_STR);
				$stmt->execute();
				while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
					$user_no_follower = (int)$row['user_no_follower'];
				}
				$mysql_stmt = "UPDATE ".DB_TABLE_USER." SET `user_no_follower` =? WHERE `user_id` =?";
				$stmt = $dbc->prepare($mysql_stmt);
				if($follow_action=="follow"){
					$user_no_follower = $user_no_follower+1;
				}else if($follow_action=="unfollow"){
					$user_no_follower = $user_no_follower-1;
				}
				$stmt->bindValue(1, $user_no_follower, PDO::PARAM_STR);
				$stmt->bindValue(2, $follow_following, PDO::PARAM_STR);
				$stmt->execute();
			}else if($follow_type == "tag"){
				$tag_no_follower = 0;
				$mysql_stmt = "SELECT `tag_no_follower` FROM ".DB_TABLE_TAG." WHERE `tag_name` =?";
				$stmt = $dbc->prepare($mysql_stmt);
				$stmt->bindValue(1, $follow_following, PDO::PARAM_STR);
				$stmt->execute();
				while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
					$tag_no_follower = (int)$row['tag_no_follower'];
				}
				$mysql_stmt = "UPDATE ".DB_TABLE_TAG." SET `tag_no_follower` =? WHERE `tag_name` =?";
				$stmt = $dbc->prepare($mysql_stmt);
				if($follow_action=="follow"){
					$tag_no_follower = $tag_no_follower+1;
				}else if($follow_action=="unfollow"){
					$tag_no_follower = $tag_no_follower-1;
				}
				$stmt->bindValue(1, $tag_no_follower, PDO::PARAM_STR);
				$stmt->bindValue(2, $follow_following, PDO::PARAM_STR);
				$stmt->execute();
			}else if($follow_type == "location"){
				
			}
			echo 1;
			$dbc=null;
			die();
		}
		catch(Exception $e){
			msg_log($DEBUG_TAG." : ".$e);
			echo 5;
			$dbc=null;
			die();
		}
	}
	else if(isset($_POST['follow_follower'])&&isset($_POST['follow_type'])&&isset($_POST['mysql_stmt'])){
		//part for fetching following tab
		//presented by button
		//<a class="grey-button follow" id="unfollow" onClick="followUser(this.id)">取消關注&nbsp;&nbsp;</a><span class="large-icon fa  fa-minus-circle"></span>    
		$display_html = "";
		$query_mysql_stmt = "SELECT * FROM ".DB_TABLE_FOLLOW." WHERE `follow_follower` =? AND `follow_type` =? ORDER BY `follow_last_seen` ACS";
		$stmt = $dbc->prepare($_POST['mysql_stmt']);
		$stmt->bindValue(1, $_POST['follow_follower'], PDO::PARAM_STR);
		$stmt->bindValue(2, $_POST['follow_type'], PDO::PARAM_STR);
		try{
    		$stmt->execute();
    	}
    	catch(Exception $e){
    		msg_log($DEBUG_TAG." : ".$e);
    	}
    	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
    		if($_POST['follow_type']=="tag"){
    			$mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE (`news_tag_1` =? OR `news_tag_2` =? OR `news_tag_3` =? OR `news_tag_4` =?) AND `news_post_date_time` >?";
				$stmt2 = $dbc->prepare($mysql_stmt);
				$stmt2->bindValue(1, $row['follow_following'],PDO::PARAM_STR);
				$stmt2->bindValue(2, $row['follow_following'],PDO::PARAM_STR);
				$stmt2->bindValue(3, $row['follow_following'],PDO::PARAM_STR);
				$stmt2->bindValue(4, $row['follow_following'],PDO::PARAM_STR);
				$stmt2->bindValue(5, $row['follow_last_seen'],PDO::PARAM_STR);
	    		try{
		    		$stmt2->execute();
		    	}
		    	catch(Exception $e){
		    		msg_log($DEBUG_TAG." : ".$e);
		    	}
	    		$loop_counter = 0;
	    		$unread_no = 0;
	    		
	    		while($row2=$stmt2->fetch(PDO::FETCH_ASSOC)){
	    			if($loop_counter<1){
	        			$unread_no = $stmt2->rowCount();
	    			}
	    		}
	    		if($loop_counter<1){
	    		    $display_html .= "<a class='green-button follow' href='follow.php?follow_type=tag&follow_following=".$row['follow_following']."'>#".$row['follow_following']." (".$unread_no.")</a><a id = 'unfollow' class='large-icon fa  fa-minus-circle' onClick='follow(".$row['follow_last_seen'].", ".'"'."unfollow".'", '.'"'."tag".'", '.'"'.$row['follow_following'].'"'.")'></a>";
	    		    $loop_counter++;
	    		}
    		}
    		else if($_POST['follow_type']=="user"){
    			$mysql_stmt = "SELECT * FROM ".DB_TABLE_NEWS." WHERE `news_author_id` =? AND `news_post_date_time` >?";
    			$stmt2 = $dbc->prepare($mysql_stmt);
    			$stmt2->bindValue(1, $row['follow_following'],PDO::PARAM_STR);
    			$stmt2->bindValue(2, $row['follow_last_seen'],PDO::PARAM_STR);
    			try{
		    		$stmt2->execute();
		    	}
		    	catch(Exception $e){
		    		msg_log($DEBUG_TAG." : ".$e);
		    	}
        		
        		$loop_counter = 0;
        		$unread_no = 0;
        		$display_name = "";
        		$news_author_id  = $row['follow_following'];
        		$stmt2_counter = 0;
        		while($row2=$stmt2->fetch(PDO::FETCH_ASSOC)){
        			if($stmt2_counter<1){
	    				$unread_no = $stmt2->rowCount();
	    				$stmt2_counter++;
        			}
        		}

        		
    			
        		if($loop_counter<1){
        			$stmt3 = $dbc->prepare("SELECT `user_name` FROM ".DB_TABLE_USER." WHERE `user_id` =?");
    				$stmt3->bindValue(1, $row['follow_following'],PDO::PARAM_STR);
        			try{
			    		$stmt3->execute();
			    	}
			    	catch(Exception $e){
			    		msg_log($DEBUG_TAG." : ".$e);
			    	}
	    			while($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)){
	    				$display_name = $row3['user_name'];
	    			}
        			$display_html .= "<a class='blue-button follow' href='follow.php?follow_type=user&follow_following=".$news_author_id ."'>".$display_name." (".$unread_no.")</a><a id = '".$news_author_id."' class='large-icon fa  fa-minus-circle' onClick='follow(this.id, ".'"'."unfollow".'", '.'"'."user".'", '.'"'.$news_author_id.'"'.")'></a>";
    		        $loop_counter++;
	    		}
    		}
    	}
    	echo $display_html;
	}


?>